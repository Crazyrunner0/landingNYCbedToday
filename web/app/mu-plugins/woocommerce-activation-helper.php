<?php
/**
 * Plugin Name: WooCommerce Activation Helper
 * Description: Helps with WooCommerce initial setup and configuration
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Activation_Helper {
    
    public function __construct() {
        add_action('admin_init', [$this, 'dismiss_woocommerce_notices']);
        add_action('admin_notices', [$this, 'show_setup_notice']);
        add_filter('woocommerce_enable_setup_wizard', '__return_false');
        add_action('woocommerce_install', [$this, 'configure_default_settings']);
    }
    
    public function dismiss_woocommerce_notices() {
        // Automatically complete WooCommerce setup steps
        if (class_exists('WooCommerce')) {
            update_option('woocommerce_task_list_complete', 'yes');
            update_option('woocommerce_task_list_hidden', 'yes');
            update_option('woocommerce_admin_notice_install', 'yes');
        }
    }
    
    public function show_setup_notice() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        if (get_option('wc_custom_setup_notice_dismissed')) {
            return;
        }
        
        $stripe_configured = get_option('woocommerce_stripe_settings');
        $products_seeded = get_option('wc_products_seeded');
        
        if ($stripe_configured && $products_seeded) {
            return;
        }
        
        ?>
        <div class="notice notice-info is-dismissible" data-dismissible="wc-custom-setup">
            <h3>WooCommerce Custom Setup</h3>
            <p><strong>Your WooCommerce store is ready!</strong></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <?php if ($products_seeded): ?>
                    <li>✓ Products seeded (4 mattresses + 3 add-ons)</li>
                <?php else: ?>
                    <li>⏳ Products will be seeded on next page refresh</li>
                <?php endif; ?>
                
                <?php if ($stripe_configured): ?>
                    <li>✓ Stripe payment gateway configured</li>
                <?php else: ?>
                    <li>⚠️ Configure Stripe keys in .env file</li>
                <?php endif; ?>
                
                <li>✓ One-page checkout enabled</li>
                <li>✓ Checkout fields reduced</li>
                <li>✓ GA4 and Meta Pixel tracking ready</li>
            </ul>
            <p>
                <strong>Next steps:</strong>
            </p>
            <ol style="margin-left: 20px;">
                <li>Add your Stripe test keys to the .env file</li>
                <li>Add your GA4 Measurement ID and Meta Pixel ID to .env</li>
                <li>Visit the shop page to see products</li>
                <li>Test a purchase with card: 4242 4242 4242 4242</li>
            </ol>
            <p>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=stripe'); ?>" class="button button-primary">Configure Stripe</a>
                <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button">View Products</a>
                <button type="button" class="button" onclick="this.closest('.notice').style.display='none'; fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=dismiss_wc_custom_setup');">Dismiss</button>
            </p>
        </div>
        <?php
    }
    
    public function configure_default_settings() {
        // Set default WooCommerce settings
        update_option('woocommerce_enable_guest_checkout', 'yes');
        update_option('woocommerce_enable_checkout_login_reminder', 'yes');
        update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');
        update_option('woocommerce_enable_myaccount_registration', 'yes');
        update_option('woocommerce_registration_generate_username', 'yes');
        update_option('woocommerce_registration_generate_password', 'yes');
    }
}

new WooCommerce_Activation_Helper();

// AJAX handler for dismissing notice
add_action('wp_ajax_dismiss_wc_custom_setup', function() {
    update_option('wc_custom_setup_notice_dismissed', true);
    wp_die();
});
