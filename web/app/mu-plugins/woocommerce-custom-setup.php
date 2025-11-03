<?php
/**
 * Plugin Name: WooCommerce Custom Setup
 * Description: Configures WooCommerce for one-page checkout with Stripe, seeds products, and adds GA4/Meta tracking
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Custom_Setup {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_init', [$this, 'seed_products_once']);
    }
    
    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // WooCommerce configuration
        add_filter('woocommerce_checkout_fields', [$this, 'reduce_checkout_fields']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_styles']);
        add_action('woocommerce_after_checkout_form', [$this, 'add_sticky_cta']);
        
        // One-page checkout setup
        add_filter('woocommerce_enable_order_notes_field', '__return_false');
        add_filter('woocommerce_checkout_show_terms', '__return_true');
        
        // Stripe configuration
        add_action('woocommerce_init', [$this, 'configure_stripe']);
        
        // Analytics tracking
        add_action('wp_head', [$this, 'add_ga4_tracking'], 1);
        add_action('wp_head', [$this, 'add_meta_pixel_tracking'], 2);
        add_action('woocommerce_after_single_product', [$this, 'track_view_item']);
        add_action('woocommerce_add_to_cart', [$this, 'track_add_to_cart'], 10, 4);
        add_action('woocommerce_checkout_order_processed', [$this, 'track_purchase'], 10, 1);
        add_action('woocommerce_before_checkout_form', [$this, 'track_begin_checkout']);
    }
    
    public function reduce_checkout_fields($fields) {
        // Remove unnecessary billing fields
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_postcode']);
        
        // Remove shipping fields if not needed
        unset($fields['shipping']['shipping_company']);
        unset($fields['shipping']['shipping_address_2']);
        
        // Make phone optional
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['required'] = false;
        }
        
        return $fields;
    }
    
    public function enqueue_custom_styles() {
        if (is_checkout()) {
            wp_add_inline_style('woocommerce-general', '
                .woocommerce-checkout #payment {
                    background: #f8f8f8;
                    padding: 20px;
                    margin: 20px 0;
                }
                .sticky-cta {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: #fff;
                    padding: 15px 20px;
                    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                    z-index: 999;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .sticky-cta button {
                    background: #000;
                    color: #fff;
                    padding: 15px 40px;
                    border: none;
                    border-radius: 4px;
                    font-size: 18px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                .sticky-cta button:hover {
                    background: #333;
                }
                @media (min-width: 768px) {
                    .sticky-cta {
                        display: none;
                    }
                }
            ');
        }
    }
    
    public function add_sticky_cta() {
        ?>
        <div class="sticky-cta">
            <button type="submit" class="button alt" id="sticky-place-order">
                Complete Order
            </button>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#sticky-place-order').on('click', function(e) {
                e.preventDefault();
                $('#place_order').trigger('click');
            });
        });
        </script>
        <?php
    }
    
    public function configure_stripe() {
        $stripe_settings = get_option('woocommerce_stripe_settings', []);
        
        if (empty($stripe_settings) || !isset($stripe_settings['enabled'])) {
            // Try new environment variables first, then fallback to old ones for compatibility
            $publishable_key = env('STRIPE_TEST_PUBLIC_KEY') ?? env('STRIPE_PUBLISHABLE_KEY');
            $secret_key = env('STRIPE_TEST_SECRET_KEY') ?? env('STRIPE_SECRET_KEY');
            
            if ($publishable_key && $secret_key) {
                $stripe_settings = array_merge($stripe_settings, [
                    'enabled' => 'yes',
                    'testmode' => 'yes',
                    'test_publishable_key' => $publishable_key,
                    'test_secret_key' => $secret_key,
                    'payment_request' => 'yes',
                    'payment_request_button_type' => 'buy',
                    'payment_request_button_theme' => 'dark',
                    'title' => 'Credit Card',
                    'description' => 'Pay securely with your credit card.',
                ]);
                
                update_option('woocommerce_stripe_settings', $stripe_settings);
            }
        }
    }
    
    public function seed_products_once() {
        if (get_option('wc_products_seeded')) {
            return;
        }
        
        if (!class_exists('WooCommerce') || !function_exists('wc_get_product')) {
            return;
        }
        
        // Create bed size products
        $bed_sizes = [
            'Twin' => 599.00,
            'Full' => 799.00,
            'Queen' => 999.00,
            'King' => 1299.00,
        ];
        
        foreach ($bed_sizes as $size => $price) {
            $product = new WC_Product_Simple();
            $product->set_name($size . ' Mattress');
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_description('Premium ' . $size . ' size mattress with advanced comfort technology.');
            $product->set_short_description('High-quality ' . $size . ' mattress.');
            $product->set_regular_price($price);
            $product->set_manage_stock(true);
            $product->set_stock_quantity(50);
            $product->set_stock_status('instock');
            $product->save();
        }
        
        // Create add-on category
        $addon_category = wp_insert_term('Add-ons', 'product_cat', [
            'slug' => 'add-ons',
            'description' => 'Additional services and products',
        ]);
        
        $addon_cat_id = is_array($addon_category) ? $addon_category['term_id'] : 0;
        
        // Create add-on products
        $addons = [
            'Old Bed Removal' => [
                'price' => 99.00,
                'description' => 'We will remove and dispose of your old mattress and bed frame.',
            ],
            'Stair Carry Service' => [
                'price' => 49.00,
                'description' => 'Professional service for carrying your mattress up or down stairs.',
            ],
            'Mattress Protector' => [
                'price' => 79.00,
                'description' => 'Waterproof mattress protector to extend the life of your mattress.',
            ],
        ];
        
        foreach ($addons as $name => $details) {
            $product = new WC_Product_Simple();
            $product->set_name($name);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_description($details['description']);
            $product->set_short_description($details['description']);
            $product->set_regular_price($details['price']);
            $product->set_manage_stock(true);
            $product->set_stock_quantity(100);
            $product->set_stock_status('instock');
            
            if ($addon_cat_id) {
                $product->set_category_ids([$addon_cat_id]);
            }
            
            $product->save();
        }
        
        update_option('wc_products_seeded', true);
        
        // Set WooCommerce to use pages
        $this->setup_woocommerce_pages();
    }
    
    private function setup_woocommerce_pages() {
        // Create or update WooCommerce pages
        $pages = [
            'woocommerce_shop_page_id' => 'Shop',
            'woocommerce_cart_page_id' => 'Cart',
            'woocommerce_checkout_page_id' => 'Checkout',
            'woocommerce_myaccount_page_id' => 'My Account',
        ];
        
        foreach ($pages as $option => $page_title) {
            $page_id = get_option($option);
            
            if (!$page_id || !get_post($page_id)) {
                $page_id = wp_insert_post([
                    'post_title' => $page_title,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => 1,
                ]);
                
                if ($page_id && !is_wp_error($page_id)) {
                    update_option($option, $page_id);
                }
            }
        }
        
        // Configure WooCommerce settings
        update_option('woocommerce_store_address', '123 Main Street');
        update_option('woocommerce_store_city', 'New York');
        update_option('woocommerce_default_country', 'US:NY');
        update_option('woocommerce_currency', 'USD');
        update_option('woocommerce_calc_taxes', 'no');
        update_option('woocommerce_enable_guest_checkout', 'yes');
        update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');
    }
    
    public function add_ga4_tracking() {
        $measurement_id = env('GA4_MEASUREMENT_ID');
        
        if (!$measurement_id || $measurement_id === 'G-XXXXXXXXXX') {
            return;
        }
        
        ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($measurement_id); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($measurement_id); ?>', {
                'debug_mode': true
            });
        </script>
        <?php
    }
    
    public function add_meta_pixel_tracking() {
        $pixel_id = env('META_PIXEL_ID');
        
        if (!$pixel_id || $pixel_id === '000000000000000') {
            return;
        }
        
        ?>
        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js($pixel_id); ?>');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"/>
        </noscript>
        <?php
    }
    
    public function track_view_item() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $product_data = [
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'price' => $product->get_price(),
            'currency' => get_woocommerce_currency(),
        ];
        
        ?>
        <script>
            // GA4 - View Item
            if (typeof gtag !== 'undefined') {
                gtag('event', 'view_item', {
                    currency: '<?php echo esc_js($product_data['currency']); ?>',
                    value: <?php echo floatval($product_data['price']); ?>,
                    items: [{
                        item_id: '<?php echo esc_js($product_data['item_id']); ?>',
                        item_name: '<?php echo esc_js($product_data['item_name']); ?>',
                        price: <?php echo floatval($product_data['price']); ?>
                    }]
                });
            }
            
            // Meta Pixel - ViewContent
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', {
                    content_ids: ['<?php echo esc_js($product_data['item_id']); ?>'],
                    content_name: '<?php echo esc_js($product_data['item_name']); ?>',
                    content_type: 'product',
                    value: <?php echo floatval($product_data['price']); ?>,
                    currency: '<?php echo esc_js($product_data['currency']); ?>'
                });
            }
        </script>
        <?php
    }
    
    public function track_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        $product_data = [
            'item_id' => $product->get_id(),
            'item_name' => $product->get_name(),
            'price' => $product->get_price(),
            'quantity' => $quantity,
            'currency' => get_woocommerce_currency(),
        ];
        
        // Store in session to fire on next page load
        WC()->session->set('track_add_to_cart', $product_data);
    }
    
    public function track_begin_checkout() {
        $cart = WC()->cart;
        
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $items = [];
        $total = 0;
        
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $items[] = [
                'item_id' => $product->get_id(),
                'item_name' => $product->get_name(),
                'price' => $product->get_price(),
                'quantity' => $cart_item['quantity'],
            ];
            $total += $product->get_price() * $cart_item['quantity'];
        }
        
        ?>
        <script>
            // GA4 - Begin Checkout
            if (typeof gtag !== 'undefined') {
                gtag('event', 'begin_checkout', {
                    currency: '<?php echo esc_js(get_woocommerce_currency()); ?>',
                    value: <?php echo floatval($total); ?>,
                    items: <?php echo json_encode($items); ?>
                });
            }
            
            // Meta Pixel - InitiateCheckout
            if (typeof fbq !== 'undefined') {
                fbq('track', 'InitiateCheckout', {
                    value: <?php echo floatval($total); ?>,
                    currency: '<?php echo esc_js(get_woocommerce_currency()); ?>',
                    num_items: <?php echo count($items); ?>
                });
            }
        </script>
        <?php
    }
    
    public function track_purchase($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        $items = [];
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product) {
                $items[] = [
                    'item_id' => $product->get_id(),
                    'item_name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'quantity' => $item->get_quantity(),
                ];
            }
        }
        
        $order_data = [
            'transaction_id' => $order->get_order_number(),
            'value' => $order->get_total(),
            'currency' => $order->get_currency(),
            'items' => $items,
        ];
        
        // Store in session to fire on thank you page
        WC()->session->set('track_purchase', $order_data);
    }
}

// Initialize the plugin
WooCommerce_Custom_Setup::get_instance();

// Track add to cart event on page load if stored in session
add_action('wp_footer', function() {
    if (!WC()->session) {
        return;
    }
    
    $add_to_cart_data = WC()->session->get('track_add_to_cart');
    
    if ($add_to_cart_data) {
        ?>
        <script>
            // GA4 - Add to Cart
            if (typeof gtag !== 'undefined') {
                gtag('event', 'add_to_cart', {
                    currency: '<?php echo esc_js($add_to_cart_data['currency']); ?>',
                    value: <?php echo floatval($add_to_cart_data['price'] * $add_to_cart_data['quantity']); ?>,
                    items: [{
                        item_id: '<?php echo esc_js($add_to_cart_data['item_id']); ?>',
                        item_name: '<?php echo esc_js($add_to_cart_data['item_name']); ?>',
                        price: <?php echo floatval($add_to_cart_data['price']); ?>,
                        quantity: <?php echo intval($add_to_cart_data['quantity']); ?>
                    }]
                });
            }
            
            // Meta Pixel - AddToCart
            if (typeof fbq !== 'undefined') {
                fbq('track', 'AddToCart', {
                    content_ids: ['<?php echo esc_js($add_to_cart_data['item_id']); ?>'],
                    content_name: '<?php echo esc_js($add_to_cart_data['item_name']); ?>',
                    content_type: 'product',
                    value: <?php echo floatval($add_to_cart_data['price'] * $add_to_cart_data['quantity']); ?>,
                    currency: '<?php echo esc_js($add_to_cart_data['currency']); ?>'
                });
            }
        </script>
        <?php
        WC()->session->set('track_add_to_cart', null);
    }
    
    $purchase_data = WC()->session->get('track_purchase');
    
    if ($purchase_data && is_order_received_page()) {
        ?>
        <script>
            // GA4 - Purchase
            if (typeof gtag !== 'undefined') {
                gtag('event', 'purchase', {
                    transaction_id: '<?php echo esc_js($purchase_data['transaction_id']); ?>',
                    value: <?php echo floatval($purchase_data['value']); ?>,
                    currency: '<?php echo esc_js($purchase_data['currency']); ?>',
                    items: <?php echo json_encode($purchase_data['items']); ?>
                });
            }
            
            // Meta Pixel - Purchase
            if (typeof fbq !== 'undefined') {
                fbq('track', 'Purchase', {
                    value: <?php echo floatval($purchase_data['value']); ?>,
                    currency: '<?php echo esc_js($purchase_data['currency']); ?>',
                    num_items: <?php echo count($purchase_data['items']); ?>
                });
            }
        </script>
        <?php
        WC()->session->set('track_purchase', null);
    }
});
