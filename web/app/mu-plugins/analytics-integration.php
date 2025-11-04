<?php
/**
 * Analytics Integration
 *
 * Integrates GA4 and Meta Pixel for tracking e-commerce events
 * - GA4: view_item, add_to_cart, begin_checkout, purchase
 * - Meta Pixel: ViewContent, AddToCart, InitiateCheckout, Purchase
 */

defined('ABSPATH') || exit;

class NYCBedToday_Analytics_Integration {
    private $ga4_measurement_id;
    private $meta_pixel_id;
    private $ga4_debug_mode;

    public function __construct() {
        $this->ga4_measurement_id = defined('GA4_MEASUREMENT_ID') ? GA4_MEASUREMENT_ID : '';
        $this->meta_pixel_id = defined('META_PIXEL_ID') ? META_PIXEL_ID : '';
        $this->ga4_debug_mode = defined('GA4_DEBUG_MODE') ? GA4_DEBUG_MODE : false;

        if (!$this->ga4_measurement_id && !$this->meta_pixel_id) {
            return;
        }

        // Load environment variables if needed
        if (!$this->ga4_measurement_id) {
            $this->ga4_measurement_id = getenv('GA4_MEASUREMENT_ID') ?: '';
        }
        if (!$this->meta_pixel_id) {
            $this->meta_pixel_id = getenv('META_PIXEL_ID') ?: '';
        }
        if (!$this->ga4_debug_mode) {
            $this->ga4_debug_mode = getenv('GA4_DEBUG_MODE') ? getenv('GA4_DEBUG_MODE') === 'true' : false;
        }

        // Ensure we're in WooCommerce environment
        if (!class_exists('WooCommerce')) {
            return;
        }

        add_action('wp_head', [$this, 'enqueue_analytics_scripts']);
        
        // Product page events
        add_action('woocommerce_after_single_product', [$this, 'track_view_item']);
        
        // Cart events
        add_action('wp_footer', [$this, 'track_add_to_cart'], 999);
        
        // Checkout events
        add_action('woocommerce_before_checkout_form', [$this, 'track_begin_checkout']);
        
        // Order completion events
        add_action('woocommerce_thankyou', [$this, 'track_purchase']);
    }

    /**
     * Enqueue GA4 and Meta Pixel scripts
     */
    public function enqueue_analytics_scripts() {
        if ($this->ga4_measurement_id) {
            $this->enqueue_ga4_scripts();
        }

        if ($this->meta_pixel_id) {
            $this->enqueue_meta_pixel_scripts();
        }
    }

    /**
     * Enqueue GA4 gtag script
     */
    private function enqueue_ga4_scripts() {
        ?>
        <!-- GA4 Tag -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($this->ga4_measurement_id); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($this->ga4_measurement_id); ?>', {
                'debug_mode': <?php echo $this->ga4_debug_mode ? 'true' : 'false'; ?>,
                'allow_google_signals': true,
                'allow_ad_personalization_signals': true
            });
        </script>
        <?php
    }

    /**
     * Enqueue Meta Pixel script
     */
    private function enqueue_meta_pixel_scripts() {
        ?>
        <!-- Meta Pixel -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js($this->meta_pixel_id); ?>');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($this->meta_pixel_id); ?>&ev=PageView&noscript=1" />
        </noscript>
        <?php
    }

    /**
     * Track product view event
     */
    public function track_view_item() {
        global $product;

        if (!$product) {
            return;
        }

        $product_data = $this->get_product_data($product);

        $this->track_ga4_event('view_item', [
            'items' => [
                [
                    'item_id' => $product_data['item_id'],
                    'item_name' => $product_data['item_name'],
                    'price' => $product_data['price'],
                    'currency' => $product_data['currency'],
                    'item_category' => $product_data['item_category'],
                ]
            ],
            'currency' => $product_data['currency'],
            'value' => $product_data['price'],
        ]);

        $this->track_meta_pixel_event('ViewContent', [
            'content_name' => $product_data['item_name'],
            'content_ids' => [$product_data['item_id']],
            'content_type' => 'product',
            'value' => $product_data['price'],
            'currency' => $product_data['currency'],
        ]);
    }

    /**
     * Track add to cart event
     */
    public function track_add_to_cart() {
        $cart = WC()->cart;

        if (!$cart || $cart->is_empty()) {
            return;
        }

        // Check if this event was already tracked this session
        if (isset($_SESSION['nycbedtoday_add_to_cart_tracked'])) {
            return;
        }

        $items = [];
        $total_value = 0;

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $product_data = $this->get_product_data($product);

            $items[] = [
                'item_id' => $product_data['item_id'],
                'item_name' => $product_data['item_name'],
                'price' => $product_data['price'],
                'quantity' => $cart_item['quantity'],
                'currency' => $product_data['currency'],
                'item_category' => $product_data['item_category'],
            ];

            $total_value += $product_data['price'] * $cart_item['quantity'];
        }

        $this->track_ga4_event('add_to_cart', [
            'items' => $items,
            'currency' => get_woocommerce_currency(),
            'value' => $total_value,
        ]);

        // Track individual products for Meta Pixel
        foreach ($items as $item) {
            $this->track_meta_pixel_event('AddToCart', [
                'content_name' => $item['item_name'],
                'content_ids' => [$item['item_id']],
                'content_type' => 'product',
                'value' => $item['price'],
                'currency' => $item['currency'],
                'quantity' => $item['quantity'],
            ]);
        }

        // Mark as tracked
        $_SESSION['nycbedtoday_add_to_cart_tracked'] = true;
    }

    /**
     * Track begin checkout event
     */
    public function track_begin_checkout() {
        $cart = WC()->cart;

        if (!$cart || $cart->is_empty()) {
            return;
        }

        $items = [];
        $total_value = 0;

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $product_data = $this->get_product_data($product);

            $items[] = [
                'item_id' => $product_data['item_id'],
                'item_name' => $product_data['item_name'],
                'price' => $product_data['price'],
                'quantity' => $cart_item['quantity'],
                'currency' => $product_data['currency'],
                'item_category' => $product_data['item_category'],
            ];

            $total_value += $product_data['price'] * $cart_item['quantity'];
        }

        $this->track_ga4_event('begin_checkout', [
            'items' => $items,
            'currency' => get_woocommerce_currency(),
            'value' => $total_value,
        ]);

        // Track for Meta Pixel
        $this->track_meta_pixel_event('InitiateCheckout', [
            'content_name' => 'Checkout',
            'content_type' => 'product_group',
            'value' => $total_value,
            'currency' => get_woocommerce_currency(),
            'num_items' => $cart->get_cart_contents_count(),
        ]);
    }

    /**
     * Track purchase event
     */
    public function track_purchase($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Don't track if already tracked
        if ($order->get_meta('_nycbedtoday_analytics_tracked')) {
            return;
        }

        $items = [];
        $total_value = $order->get_total();

        foreach ($order->get_items() as $order_item) {
            $product = $order_item->get_product();

            if (!$product) {
                continue;
            }

            $product_data = $this->get_product_data($product);

            $items[] = [
                'item_id' => $product_data['item_id'],
                'item_name' => $product_data['item_name'],
                'price' => $order_item->get_subtotal() / $order_item->get_quantity(),
                'quantity' => $order_item->get_quantity(),
                'currency' => $product_data['currency'],
                'item_category' => $product_data['item_category'],
            ];
        }

        $currency = $order->get_currency();

        // Track GA4 purchase
        $this->track_ga4_event('purchase', [
            'items' => $items,
            'currency' => $currency,
            'value' => $total_value,
            'transaction_id' => $order->get_order_number(),
            'affiliation' => get_bloginfo('name'),
            'tax' => $order->get_total_tax(),
            'shipping' => $order->get_shipping_total(),
        ]);

        // Track Meta Pixel purchase
        $this->track_meta_pixel_event('Purchase', [
            'content_name' => 'Purchase',
            'content_ids' => array_column($items, 'item_id'),
            'content_type' => 'product_group',
            'value' => $total_value,
            'currency' => $currency,
        ]);

        // Mark as tracked
        $order->update_meta_data('_nycbedtoday_analytics_tracked', true);
        $order->save();
    }

    /**
     * Get product data in standardized format
     */
    private function get_product_data($product) {
        $categories = [];
        foreach ($product->get_category_ids() as $category_id) {
            $term = get_term($category_id);
            if ($term) {
                $categories[] = $term->name;
            }
        }

        return [
            'item_id' => (string) $product->get_id(),
            'item_name' => $product->get_name(),
            'price' => (float) $product->get_price(),
            'currency' => get_woocommerce_currency(),
            'item_category' => !empty($categories) ? implode(',', $categories) : 'Uncategorized',
        ];
    }

    /**
     * Track GA4 event
     */
    private function track_ga4_event($event_name, $event_data = []) {
        if (!$this->ga4_measurement_id) {
            return;
        }

        // Store in transient for display in footer
        $events = get_transient('ga4_events') ?: [];
        $events[] = [
            'name' => $event_name,
            'data' => $event_data,
        ];
        set_transient('ga4_events', $events, HOUR_IN_SECONDS);
    }

    /**
     * Track Meta Pixel event
     */
    private function track_meta_pixel_event($event_name, $event_data = []) {
        if (!$this->meta_pixel_id) {
            return;
        }

        // Store in transient for display in footer
        $events = get_transient('meta_pixel_events') ?: [];
        $events[] = [
            'name' => $event_name,
            'data' => $event_data,
        ];
        set_transient('meta_pixel_events', $events, HOUR_IN_SECONDS);
    }
}

// Initialize analytics integration
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        new NYCBedToday_Analytics_Integration();
    }
});
