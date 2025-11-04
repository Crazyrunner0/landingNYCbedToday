<?php
/**
 * Plugin Name: WooCommerce Custom Setup
 * Description: Configures WooCommerce for one-page checkout with Stripe and seeds products.
 * Version: 1.0.0
 * Note: Analytics tracking is now handled by nycbedtoday-analytics plugin
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
        
        // Analytics tracking is now handled by nycbedtoday-analytics plugin
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


}

// Initialize the plugin
WooCommerce_Custom_Setup::get_instance();
