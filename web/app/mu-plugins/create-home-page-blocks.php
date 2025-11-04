<?php
/**
 * Plugin Name: Create Home Page with Gutenberg Blocks
 * Description: Automatically create a home page with landing page blocks on first install
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create home page with landing blocks on first install
 */
function create_home_page_with_blocks() {
    // Check if home page already exists
    $home_page = get_page_by_title('Home');
    if ($home_page) {
        return;
    }

    // Create home page with blocks
    $home_page_content = '<!-- wp:blocksy-child/hero-offer -->
<div class="wp-block-blocksy-child-hero-offer" style="background-color: rgb(37, 99, 235); color: rgb(255, 255, 255); padding: 80px 40px; text-align: center;">
    <div class="hero-offer-content">
        <h1 class="hero-offer-headline">Get Your Perfect Mattress Today</h1>
        <p class="hero-offer-subheadline">Premium comfort starts here</p>
        <p class="hero-offer-description">Find the mattress that\'s right for you with our expert selection and fast delivery.</p>
        <a href="#shop" class="hero-offer-button">Shop Now</a>
    </div>
</div>
<!-- /wp:blocksy-child/hero-offer -->

<!-- wp:blocksy-child/social-proof-strip -->
<div class="wp-block-blocksy-child-social-proof-strip">
    <div class="social-proof-strip">
        <div class="social-proof-items">
            <div class="social-proof-item">
                <span class="social-proof-text">Trusted by 50,000+ customers</span>
            </div>
            <div class="social-proof-item">
                <span class="social-proof-text">4.9/5 stars average rating</span>
            </div>
            <div class="social-proof-item">
                <span class="social-proof-text">Free delivery on all orders</span>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/social-proof-strip -->

<!-- wp:blocksy-child/value-stack -->
<div class="wp-block-blocksy-child-value-stack">
    <div class="value-stack">
        <h2 class="value-stack-title">Why Choose Us</h2>
        <div class="value-stack-items">
            <div class="value-stack-item">
                <h3 class="value-item-title">Premium Quality</h3>
                <p class="value-item-description">Expert-selected mattresses from top brands</p>
            </div>
            <div class="value-stack-item">
                <h3 class="value-item-title">Fast Delivery</h3>
                <p class="value-item-description">Same-day and next-day delivery available</p>
            </div>
            <div class="value-stack-item">
                <h3 class="value-item-title">100% Satisfaction</h3>
                <p class="value-item-description">30-night sleep trial on all mattresses</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/value-stack -->

<!-- wp:blocksy-child/how-it-works -->
<div class="wp-block-blocksy-child-how-it-works">
    <div class="how-it-works">
        <h2 class="how-it-works-title">How It Works</h2>
        <div class="how-it-works-steps">
            <div class="how-it-works-step">
                <div class="step-number">1</div>
                <h3 class="step-title">Browse Selection</h3>
                <p class="step-description">Explore our wide variety of premium mattresses</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">2</div>
                <h3 class="step-title">Select Size & Type</h3>
                <p class="step-description">Choose the perfect mattress for your needs</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Quick Checkout</h3>
                <p class="step-description">Fast and secure payment process</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">4</div>
                <h3 class="step-title">Fast Delivery</h3>
                <p class="step-description">Get your mattress delivered to your door</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/how-it-works -->

<!-- wp:blocksy-child/local-neighborhoods -->
<div class="wp-block-blocksy-child-local-neighborhoods" style="background-color: rgb(248, 250, 252);">
    <div class="local-neighborhoods">
        <h2 class="local-neighborhoods-title">Serving Your Neighborhood</h2>
        <p class="local-neighborhoods-description">We proudly serve customers across multiple neighborhoods and boroughs</p>
        <div class="neighborhoods-grid">
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Manhattan</h3>
                <p class="neighborhood-description">All neighborhoods</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Brooklyn</h3>
                <p class="neighborhood-description">All neighborhoods</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Queens</h3>
                <p class="neighborhood-description">Select areas</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Bronx</h3>
                <p class="neighborhood-description">Select areas</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/local-neighborhoods -->

<!-- wp:blocksy-child/final-cta -->
<div class="wp-block-blocksy-child-final-cta" style="background-color: rgb(15, 23, 42); color: rgb(255, 255, 255); padding: 60px 40px; text-align: center;">
    <div class="final-cta-content">
        <h2 class="final-cta-headline">Ready to Transform Your Sleep?</h2>
        <p class="final-cta-description">Join thousands of satisfied customers who have found their perfect mattress</p>
        <div class="final-cta-buttons">
            <a href="#shop" class="final-cta-button primary">Shop Now</a>
            <a href="#about" class="final-cta-button secondary">Learn More</a>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/final-cta -->';

    $page_id = wp_insert_post([
        'post_type'    => 'page',
        'post_title'   => 'Home',
        'post_content' => $home_page_content,
        'post_status'  => 'publish',
        'post_parent'  => 0,
    ]);

    // Set as front page and use landing page template
    if ($page_id && !is_wp_error($page_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);
        update_post_meta($page_id, '_wp_page_template', 'templates/landing-page.php');
    }
}

// Hook to init to ensure everything is loaded
add_action('wp_loaded', 'create_home_page_with_blocks', 999);
