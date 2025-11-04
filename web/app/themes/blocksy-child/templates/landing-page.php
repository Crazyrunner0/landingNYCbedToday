<?php
/**
 * Template Name: Landing Page
 * Description: High-performance landing page template with blocks, sticky CTA, and anchor navigation
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main-content" class="site-content landing-page-main" role="main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('landing-page-content'); ?>>
            
            <!-- Hero Section with ID for anchor navigation -->
            <section id="hero" class="landing-section hero-section">
                <?php
                the_content();
                ?>
            </section>

            <!-- Product Highlights / Gallery Placeholder -->
            <section id="products" class="landing-section products-section">
                <div class="section-wrapper">
                    <h2 class="section-title">Our Collection</h2>
                    <div class="products-placeholder">
                        <p class="placeholder-text">Browse our premium mattress collection</p>
                        <div class="products-grid">
                            <div class="product-card">
                                <div class="product-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                <h3 class="product-name">Premium Comfort</h3>
                                <p class="product-description">Luxury support for ultimate comfort</p>
                            </div>
                            <div class="product-card">
                                <div class="product-image" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                                <h3 class="product-name">Cloud Luxury</h3>
                                <p class="product-description">Memory foam perfection</p>
                            </div>
                            <div class="product-card">
                                <div class="product-image" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                                <h3 class="product-name">Cool Gel</h3>
                                <p class="product-description">Advanced cooling technology</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Urgency/Cut-off Banner -->
            <section id="urgency" class="landing-section urgency-banner">
                <div class="urgency-content">
                    <div class="urgency-icon">⏰</div>
                    <div class="urgency-text">
                        <h3 class="urgency-title">Limited Time Offer</h3>
                        <p class="urgency-message">Get 30% off all orders placed by midnight tonight. Free delivery included!</p>
                    </div>
                    <button class="urgency-cta" data-scroll="#shop">Claim Offer</button>
                </div>
            </section>

            <!-- Local Neighborhoods Section with Anchor -->
            <section id="neighborhoods" class="landing-section neighborhoods-section">
                <?php
                // Output content if available
                $post_content = get_the_content();
                if (strpos($post_content, 'blocksy-child/local-neighborhoods') !== false) {
                    // Content includes the block, already rendered above
                }
                ?>
            </section>

            <!-- Reviews/Testimonials Placeholder -->
            <section id="reviews" class="landing-section reviews-section">
                <div class="section-wrapper">
                    <h2 class="section-title">Customer Reviews</h2>
                    <div class="reviews-placeholder">
                        <div class="reviews-grid">
                            <div class="review-card">
                                <div class="review-rating">★★★★★</div>
                                <p class="review-text">"Best mattress I've ever owned! The delivery was fast and the quality is outstanding."</p>
                                <p class="review-author">- Sarah M.</p>
                            </div>
                            <div class="review-card">
                                <div class="review-rating">★★★★★</div>
                                <p class="review-text">"Excellent customer service and the mattress is incredibly comfortable. Highly recommended!"</p>
                                <p class="review-author">- John D.</p>
                            </div>
                            <div class="review-card">
                                <div class="review-rating">★★★★★</div>
                                <p class="review-text">"Superior quality at great prices. The 30-night trial gave me confidence in my purchase."</p>
                                <p class="review-author">- Emma T.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Placeholder -->
            <section id="faq" class="landing-section faq-section">
                <div class="section-wrapper">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <div class="faq-placeholder">
                        <div class="faq-item">
                            <button class="faq-question" data-toggle="faq-1">
                                <span class="faq-text">What's the return policy?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div id="faq-1" class="faq-answer" style="display:none;">
                                <p>We offer a 30-night sleep trial on all mattresses. If you're not satisfied, we provide free returns.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question" data-toggle="faq-2">
                                <span class="faq-text">How long does delivery take?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div id="faq-2" class="faq-answer" style="display:none;">
                                <p>We offer same-day and next-day delivery in most areas. Standard delivery is 2-3 business days.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question" data-toggle="faq-3">
                                <span class="faq-text">Is assembly included?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div id="faq-3" class="faq-answer" style="display:none;">
                                <p>Yes! Our delivery team will assemble your mattress at no additional cost. We also handle old mattress removal.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Shop Section with Anchor -->
            <section id="shop" class="landing-section shop-section">
                <div class="section-wrapper">
                    <h2 class="section-title">Ready to Upgrade Your Sleep?</h2>
                    <p class="shop-description">Explore our full collection and find the perfect mattress for your needs.</p>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary btn-lg">Browse All Mattresses</a>
                </div>
            </section>

        </article>

    <?php
    endwhile;
    ?>
</main>

<!-- Sticky CTA (Floating Action Button / Bar) -->
<div class="sticky-cta" id="sticky-cta">
    <div class="sticky-cta-content">
        <div class="sticky-cta-text">
            <p class="sticky-cta-message">Get 30% off - Limited time offer!</p>
        </div>
        <a href="#shop" class="sticky-cta-button" data-scroll="#shop">Shop Now</a>
    </div>
</div>

<!-- Anchor Navigation (Side navigation for smooth scrolling) -->
<nav class="anchor-nav" id="anchor-nav" role="navigation" aria-label="Page Navigation">
    <ul class="anchor-nav-list">
        <li class="anchor-nav-item"><a href="#hero" class="anchor-nav-link" data-scroll>Hero</a></li>
        <li class="anchor-nav-item"><a href="#products" class="anchor-nav-link" data-scroll>Products</a></li>
        <li class="anchor-nav-item"><a href="#neighborhoods" class="anchor-nav-link" data-scroll>Neighborhoods</a></li>
        <li class="anchor-nav-item"><a href="#reviews" class="anchor-nav-link" data-scroll>Reviews</a></li>
        <li class="anchor-nav-item"><a href="#faq" class="anchor-nav-link" data-scroll>FAQ</a></li>
        <li class="anchor-nav-item"><a href="#shop" class="anchor-nav-link" data-scroll>Shop</a></li>
    </ul>
</nav>

<?php
get_footer();
