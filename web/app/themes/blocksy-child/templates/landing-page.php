<?php
/**
 * Template Name: Landing Page
 * Description: High-performance landing page template with minimal assets
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main-content" class="site-content" role="main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('landing-page-content'); ?>>
            
            <?php if (has_post_thumbnail()) : ?>
            <section class="hero-section">
                <div class="container">
                    <?php the_post_thumbnail('full', ['loading' => 'eager', 'fetchpriority' => 'high']); ?>
                    <div class="hero-content">
                        <h1 class="hero-title"><?php the_title(); ?></h1>
                    </div>
                </div>
            </section>
            <?php else : ?>
            <section class="hero-section">
                <div class="container">
                    <h1 class="hero-title"><?php the_title(); ?></h1>
                </div>
            </section>
            <?php endif; ?>

            <section class="content-section">
                <div class="container">
                    <?php
                    the_content();

                    wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'blocksy-child'),
                        'after' => '</div>',
                        'link_before' => '<span class="page-number">',
                        'link_after' => '</span>',
                    ]);
                    ?>
                </div>
            </section>

            <?php
            // CTA section - can be customized via ACF or custom fields
            $cta_title = get_post_meta(get_the_ID(), 'cta_title', true);
            $cta_text = get_post_meta(get_the_ID(), 'cta_text', true);
            $cta_button_text = get_post_meta(get_the_ID(), 'cta_button_text', true);
            $cta_button_link = get_post_meta(get_the_ID(), 'cta_button_link', true);

            if ($cta_title || $cta_text) :
            ?>
            <section class="cta-section">
                <div class="container text-center">
                    <?php if ($cta_title) : ?>
                        <h2><?php echo esc_html($cta_title); ?></h2>
                    <?php endif; ?>
                    
                    <?php if ($cta_text) : ?>
                        <p><?php echo wp_kses_post($cta_text); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($cta_button_text && $cta_button_link) : ?>
                        <a href="<?php echo esc_url($cta_button_link); ?>" 
                           class="cta-button wp-block-button__link">
                            <?php echo esc_html($cta_button_text); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

        </article>

    <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
