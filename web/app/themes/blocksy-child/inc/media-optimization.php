<?php
/**
 * Media Optimization
 * Implements WebP/AVIF conversion pipeline with graceful fallbacks
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter image content to use picture element with WebP/AVIF
 */
function blocksy_child_picture_elements($content) {
    if (is_admin() || is_feed() || wp_doing_ajax()) {
        return $content;
    }

    $content = preg_replace_callback('/<img([^>]*?)src=["\']?([^"\'>\s]+)["\']?([^>]*)>/i', function($matches) {
        $attributes = $matches[1];
        $src = $matches[2];
        $after = $matches[3];
        
        // Skip if already in picture element
        if (strpos($attributes, 'data-no-picture') !== false) {
            return $matches[0];
        }

        // Skip data URIs and external images
        if (strpos($src, 'data:') === 0 || strpos($src, '://') === false || 
            (strpos($src, get_site_url()) === false && strpos($src, get_home_url()) === false)) {
            return $matches[0];
        }

        // Check if image file exists
        if (!file_exists(str_replace(get_site_url(), ABSPATH, $src))) {
            return $matches[0];
        }

        $webp_src = blocksy_child_get_webp_url($src);
        $avif_src = blocksy_child_get_avif_url($src);
        
        if (!$webp_src && !$avif_src) {
            return $matches[0];
        }

        // Build picture element
        $picture = '<picture>';
        
        if ($avif_src) {
            $picture .= '<source srcset="' . esc_url($avif_src) . '" type="image/avif">';
        }
        
        if ($webp_src) {
            $picture .= '<source srcset="' . esc_url($webp_src) . '" type="image/webp">';
        }
        
        // Extract alt text
        $alt_match = [];
        preg_match('/alt=["\']?([^"\'>\s]*)["\']?/i', $attributes, $alt_match);
        $alt = isset($alt_match[1]) ? $alt_match[1] : '';
        
        // Build img tag
        $picture .= '<img src="' . esc_url($src) . '" ' . $attributes . $after;
        $picture .= '</picture>';
        
        return $picture;
    }, $content);
    
    return $content;
}
add_filter('the_content', 'blocksy_child_picture_elements', 5);
add_filter('post_thumbnail_html', 'blocksy_child_picture_elements', 5);

/**
 * Get WebP URL for image (with fallback to original if conversion not available)
 */
function blocksy_child_get_webp_url($image_url) {
    if (empty($image_url)) {
        return false;
    }

    // Convert URL to file path
    $image_path = str_replace(get_site_url(), ABSPATH, $image_url);
    $image_path = str_replace(get_home_url(), ABSPATH, $image_path);
    
    if (!file_exists($image_path)) {
        return false;
    }

    // Check if WebP version exists
    $info = pathinfo($image_path);
    $webp_path = $info['dirname'] . '/' . $info['filename'] . '.webp';
    
    if (file_exists($webp_path)) {
        $webp_url = str_replace(ABSPATH, get_site_url(), $webp_path);
        return $webp_url;
    }

    // Try to create WebP version if GD or Imagick available
    if (function_exists('imagewebp') || extension_loaded('imagick')) {
        $webp_url = blocksy_child_convert_to_webp($image_path);
        if ($webp_url) {
            return $webp_url;
        }
    }

    return false;
}

/**
 * Get AVIF URL for image
 */
function blocksy_child_get_avif_url($image_url) {
    if (empty($image_url)) {
        return false;
    }

    // Convert URL to file path
    $image_path = str_replace(get_site_url(), ABSPATH, $image_url);
    $image_path = str_replace(get_home_url(), ABSPATH, $image_path);
    
    if (!file_exists($image_path)) {
        return false;
    }

    // Check if AVIF version exists
    $info = pathinfo($image_path);
    $avif_path = $info['dirname'] . '/' . $info['filename'] . '.avif';
    
    if (file_exists($avif_path)) {
        $avif_url = str_replace(ABSPATH, get_site_url(), $avif_path);
        return $avif_url;
    }

    // AVIF conversion requires more processing, skip for now
    return false;
}

/**
 * Convert image to WebP using GD or Imagick
 */
function blocksy_child_convert_to_webp($image_path) {
    if (!file_exists($image_path) || !is_readable($image_path)) {
        return false;
    }

    $info = pathinfo($image_path);
    $webp_path = $info['dirname'] . '/' . $info['filename'] . '.webp';

    // Skip if already exists
    if (file_exists($webp_path)) {
        return str_replace(ABSPATH, get_site_url(), $webp_path);
    }

    // Try GD first
    if (function_exists('imagewebp')) {
        $image = null;
        $ext = strtolower($info['extension']);
        
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $image = @imagecreatefromjpeg($image_path);
        } elseif ($ext === 'png') {
            $image = @imagecreatefrompng($image_path);
        } elseif ($ext === 'gif') {
            $image = @imagecreatefromgif($image_path);
        }
        
        if ($image) {
            if (@imagewebp($image, $webp_path, 80)) {
                @imagedestroy($image);
                return str_replace(ABSPATH, get_site_url(), $webp_path);
            }
            @imagedestroy($image);
        }
    }

    // Try Imagick
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick($image_path);
            $imagick->setImageFormat('webp');
            $imagick->setCompressionQuality(80);
            if ($imagick->writeImage($webp_path)) {
                return str_replace(ABSPATH, get_site_url(), $webp_path);
            }
        } catch (Exception $e) {
            // Imagick failed, continue
        }
    }

    return false;
}

/**
 * Add image srcset attribute with density descriptors
 */
function blocksy_child_add_image_srcset($html) {
    if (is_admin() || is_feed() || wp_doing_ajax()) {
        return $html;
    }

    // This is typically handled by WordPress automatically,
    // but we can enhance it here if needed
    return $html;
}

/**
 * Add preload hints for critical images
 */
function blocksy_child_preload_critical_images() {
    if (is_front_page() || is_home()) {
        $post_thumbnail_id = get_post_thumbnail_id();
        if ($post_thumbnail_id) {
            $image_src = wp_get_attachment_image_src($post_thumbnail_id, 'large');
            if ($image_src && !empty($image_src[0])) {
                echo '<link rel="preload" as="image" href="' . esc_url($image_src[0]) . '" imagesrcset="' . esc_attr($image_src[0]) . '">';
            }
        }
    }
}
add_action('wp_head', 'blocksy_child_preload_critical_images', 1);

/**
 * Add Stripe preconnect for checkout
 */
function blocksy_child_stripe_preconnect() {
    ?>
    <link rel="preconnect" href="https://stripe.com" crossorigin>
    <link rel="dns-prefetch" href="https://stripe.com">
    <link rel="preconnect" href="https://q.stripe.com" crossorigin>
    <link rel="dns-prefetch" href="https://q.stripe.com">
    <?php
}
add_action('wp_head', 'blocksy_child_stripe_preconnect', 1);

/**
 * Add analytics preconnect
 */
function blocksy_child_analytics_preconnect() {
    ?>
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <?php
}
add_action('wp_head', 'blocksy_child_analytics_preconnect', 1);

/**
 * Add image lazy loading class to attachments
 */
function blocksy_child_attachment_image_attributes($attr, $attachment, $size) {
    if (!is_admin() && !isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'blocksy_child_attachment_image_attributes', 10, 3);

/**
 * Optimize images on upload (if ImageMagick available)
 * This hook is called after upload to create optimized versions
 */
function blocksy_child_optimize_attachment_on_upload($metadata, $attachment_id) {
    // This would be used to automatically create WebP/AVIF versions on upload
    // For now, we rely on on-demand conversion
    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'blocksy_child_optimize_attachment_on_upload', 10, 2);
