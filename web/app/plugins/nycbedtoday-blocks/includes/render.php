<?php
/**
 * Server-side rendering helpers.
 *
 * @package NYC Bed Today Blocks
 */

defined('ABSPATH') || exit;

/**
 * Renders a block based on its slug.
 *
 * @param string     $slug       Block slug without namespace.
 * @param array      $attributes Block attributes.
 * @param string     $content    Inner blocks content.
 * @param WP_Block   $block      Block instance.
 *
 * @return string
 */
function nycbedtoday_blocks_render($slug, $attributes, $content = '', $block = null)
{
    $defaults = [];
    $definitions = nycbedtoday_blocks_get_definitions();

    if (isset($definitions[$slug])) {
        foreach ($definitions[$slug]['fields'] as $field_name => $field_config) {
            $defaults[$field_name] = isset($field_config['default']) ? $field_config['default'] : '';
        }
    }

    $attributes = wp_parse_args($attributes, $defaults);

    switch ($slug) {
        case 'hero-offer':
            return nycbedtoday_blocks_render_hero_offer($attributes);
        case 'social-proof-strip':
            return nycbedtoday_blocks_render_social_proof($attributes);
        case 'product-picker':
            return nycbedtoday_blocks_render_product_picker($attributes);
        case 'zip-checker':
            return nycbedtoday_blocks_render_zip_checker($attributes);
        case 'time-slots':
            return nycbedtoday_blocks_render_time_slots($attributes);
        case 'value-stack':
            return nycbedtoday_blocks_render_value_stack($attributes);
        case 'how-it-works':
            return nycbedtoday_blocks_render_how_it_works($attributes);
        case 'urgency-counter':
            return nycbedtoday_blocks_render_urgency_counter($attributes);
        case 'local-neighborhoods':
            return nycbedtoday_blocks_render_local_neighborhoods($attributes);
        case 'reviews-carousel':
            return nycbedtoday_blocks_render_reviews($attributes);
        case 'faq':
            return nycbedtoday_blocks_render_faq($attributes);
        case 'final-cta':
            return nycbedtoday_blocks_render_final_cta($attributes);
        default:
            return '';
    }
}

/**
 * Converts textarea style input into HTML paragraphs.
 *
 * @param string $content Raw content.
 *
 * @return string
 */
function nycbedtoday_blocks_format_textarea($content)
{
    $content = trim((string) $content);

    if ('' === $content) {
        return '';
    }

    return wpautop(esc_html($content));
}

/**
 * Renders the Hero Offer block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_hero_offer($attributes)
{
    $styles = [];
    if (!empty($attributes['backgroundImageUrl'])) {
        $styles[] = 'background-image: url(' . esc_url_raw($attributes['backgroundImageUrl']) . ')';
    }

    $style_attr = $styles ? ' style="' . esc_attr(implode('; ', $styles)) . '"' : '';

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-hero-offer"<?php echo $style_attr; ?>>
        <div class="nycbedtoday-hero-offer__content">
            <?php if (!empty($attributes['eyebrow'])) : ?>
                <div class="nycbedtoday-hero-offer__eyebrow"><?php echo esc_html($attributes['eyebrow']); ?></div>
            <?php endif; ?>

            <?php if (!empty($attributes['headline'])) : ?>
                <div class="nycbedtoday-hero-offer__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
            <?php endif; ?>

            <?php if (!empty($attributes['description'])) : ?>
                <div class="nycbedtoday-hero-offer__description"><?php echo wp_kses_post($attributes['description']); ?></div>
            <?php endif; ?>

            <div class="nycbedtoday-hero-offer__actions">
                <?php if (!empty($attributes['primaryButtonLabel'])) : ?>
                    <a class="nycbedtoday-button nycbedtoday-button--primary" href="<?php echo esc_url($attributes['primaryButtonUrl']); ?>">
                        <?php echo esc_html($attributes['primaryButtonLabel']); ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($attributes['secondaryButtonLabel'])) : ?>
                    <a class="nycbedtoday-button nycbedtoday-button--ghost" href="<?php echo esc_url($attributes['secondaryButtonUrl']); ?>">
                        <?php echo esc_html($attributes['secondaryButtonLabel']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Social Proof block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_social_proof($attributes)
{
    $stats = [];
    if (!empty($attributes['statOneValue'])) {
        $stats[] = [
            'label' => $attributes['statOneLabel'],
            'value' => $attributes['statOneValue'],
        ];
    }
    if (!empty($attributes['statTwoValue'])) {
        $stats[] = [
            'label' => $attributes['statTwoLabel'],
            'value' => $attributes['statTwoValue'],
        ];
    }
    if (!empty($attributes['statThreeValue'])) {
        $stats[] = [
            'label' => $attributes['statThreeLabel'],
            'value' => $attributes['statThreeValue'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-social-proof">
        <div class="nycbedtoday-social-proof__copy">
            <?php if (!empty($attributes['headline'])) : ?>
                <div class="nycbedtoday-social-proof__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
            <?php endif; ?>
            <?php if (!empty($attributes['supportingText'])) : ?>
                <p class="nycbedtoday-social-proof__support">
                    <?php echo esc_html($attributes['supportingText']); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if (!empty($stats)) : ?>
            <ul class="nycbedtoday-social-proof__stats">
                <?php foreach ($stats as $stat) : ?>
                    <li class="nycbedtoday-social-proof__stat">
                        <span class="nycbedtoday-social-proof__stat-value"><?php echo esc_html($stat['value']); ?></span>
                        <?php if (!empty($stat['label'])) : ?>
                            <span class="nycbedtoday-social-proof__stat-label"><?php echo esc_html($stat['label']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Product Picker block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_product_picker($attributes)
{
    $products = [];
    if (!empty($attributes['productOneTitle'])) {
        $products[] = [
            'title'       => $attributes['productOneTitle'],
            'description' => $attributes['productOneDescription'],
            'price'       => $attributes['productOnePrice'],
        ];
    }
    if (!empty($attributes['productTwoTitle'])) {
        $products[] = [
            'title'       => $attributes['productTwoTitle'],
            'description' => $attributes['productTwoDescription'],
            'price'       => $attributes['productTwoPrice'],
        ];
    }
    if (!empty($attributes['productThreeTitle'])) {
        $products[] = [
            'title'       => $attributes['productThreeTitle'],
            'description' => $attributes['productThreeDescription'],
            'price'       => $attributes['productThreePrice'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-product-picker">
        <div class="nycbedtoday-product-picker__header">
            <?php if (!empty($attributes['headline'])) : ?>
                <div class="nycbedtoday-product-picker__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
            <?php endif; ?>
            <?php if (!empty($attributes['description'])) : ?>
                <div class="nycbedtoday-product-picker__description"><?php echo wp_kses_post($attributes['description']); ?></div>
            <?php endif; ?>
        </div>
        <?php if (!empty($products)) : ?>
            <div class="nycbedtoday-product-picker__grid">
                <?php foreach ($products as $product) : ?>
                    <article class="nycbedtoday-product-picker__card">
                        <h4 class="nycbedtoday-product-picker__card-title"><?php echo esc_html($product['title']); ?></h4>
                        <?php if (!empty($product['description'])) : ?>
                            <div class="nycbedtoday-product-picker__card-description">
                                <?php echo nycbedtoday_blocks_format_textarea($product['description']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['price'])) : ?>
                            <div class="nycbedtoday-product-picker__card-price"><?php echo esc_html($product['price']); ?></div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the ZIP Checker block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_zip_checker($attributes)
{
    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-zip-checker">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-zip-checker__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['description'])) : ?>
            <div class="nycbedtoday-zip-checker__description"><?php echo wp_kses_post($attributes['description']); ?></div>
        <?php endif; ?>
        <form class="nycbedtoday-zip-checker__form" action="#" method="post">
            <label class="screen-reader-text" for="nycbedtoday-zip-checker-input"><?php esc_html_e('ZIP code', 'nycbedtoday-blocks'); ?></label>
            <input id="nycbedtoday-zip-checker-input" class="nycbedtoday-zip-checker__input" type="text" name="nycbedtoday_zip" placeholder="<?php echo esc_attr($attributes['placeholder']); ?>" />
            <button class="nycbedtoday-button nycbedtoday-button--primary" type="submit"><?php echo esc_html($attributes['buttonLabel']); ?></button>
        </form>
        <div class="nycbedtoday-zip-checker__messages" aria-live="polite">
            <div class="nycbedtoday-zip-checker__message nycbedtoday-zip-checker__message--success"><?php echo nycbedtoday_blocks_format_textarea($attributes['successMessage']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <div class="nycbedtoday-zip-checker__message nycbedtoday-zip-checker__message--error"><?php echo nycbedtoday_blocks_format_textarea($attributes['errorMessage']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Time Slots block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_time_slots($attributes)
{
    $slots = [];
    if (!empty($attributes['slotOneLabel']) || !empty($attributes['slotOneWindow'])) {
        $slots[] = [
            'label'  => $attributes['slotOneLabel'],
            'window' => $attributes['slotOneWindow'],
        ];
    }
    if (!empty($attributes['slotTwoLabel']) || !empty($attributes['slotTwoWindow'])) {
        $slots[] = [
            'label'  => $attributes['slotTwoLabel'],
            'window' => $attributes['slotTwoWindow'],
        ];
    }
    if (!empty($attributes['slotThreeLabel']) || !empty($attributes['slotThreeWindow'])) {
        $slots[] = [
            'label'  => $attributes['slotThreeLabel'],
            'window' => $attributes['slotThreeWindow'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-time-slots">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-time-slots__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['description'])) : ?>
            <div class="nycbedtoday-time-slots__description"><?php echo wp_kses_post($attributes['description']); ?></div>
        <?php endif; ?>
        <?php if (!empty($slots)) : ?>
            <div class="nycbedtoday-time-slots__grid">
                <?php foreach ($slots as $slot) : ?>
                    <div class="nycbedtoday-time-slots__slot">
                        <?php if (!empty($slot['label'])) : ?>
                            <h4 class="nycbedtoday-time-slots__slot-label"><?php echo esc_html($slot['label']); ?></h4>
                        <?php endif; ?>
                        <?php if (!empty($slot['window'])) : ?>
                            <p class="nycbedtoday-time-slots__slot-window"><?php echo esc_html($slot['window']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Value Stack block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_value_stack($attributes)
{
    $points = [];
    if (!empty($attributes['pointOneTitle'])) {
        $points[] = [
            'title'       => $attributes['pointOneTitle'],
            'description' => $attributes['pointOneDescription'],
        ];
    }
    if (!empty($attributes['pointTwoTitle'])) {
        $points[] = [
            'title'       => $attributes['pointTwoTitle'],
            'description' => $attributes['pointTwoDescription'],
        ];
    }
    if (!empty($attributes['pointThreeTitle'])) {
        $points[] = [
            'title'       => $attributes['pointThreeTitle'],
            'description' => $attributes['pointThreeDescription'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-value-stack">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-value-stack__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['description'])) : ?>
            <div class="nycbedtoday-value-stack__description"><?php echo wp_kses_post($attributes['description']); ?></div>
        <?php endif; ?>
        <?php if (!empty($points)) : ?>
            <div class="nycbedtoday-value-stack__list">
                <?php foreach ($points as $point) : ?>
                    <div class="nycbedtoday-value-stack__item">
                        <h4 class="nycbedtoday-value-stack__item-title"><?php echo esc_html($point['title']); ?></h4>
                        <?php if (!empty($point['description'])) : ?>
                            <div class="nycbedtoday-value-stack__item-description">
                                <?php echo nycbedtoday_blocks_format_textarea($point['description']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the How It Works block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_how_it_works($attributes)
{
    $steps = [];
    if (!empty($attributes['stepOneTitle'])) {
        $steps[] = [
            'title'       => $attributes['stepOneTitle'],
            'description' => $attributes['stepOneDescription'],
        ];
    }
    if (!empty($attributes['stepTwoTitle'])) {
        $steps[] = [
            'title'       => $attributes['stepTwoTitle'],
            'description' => $attributes['stepTwoDescription'],
        ];
    }
    if (!empty($attributes['stepThreeTitle'])) {
        $steps[] = [
            'title'       => $attributes['stepThreeTitle'],
            'description' => $attributes['stepThreeDescription'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-how-it-works">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-how-it-works__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($steps)) : ?>
            <ol class="nycbedtoday-how-it-works__steps">
                <?php foreach ($steps as $index => $step) : ?>
                    <li class="nycbedtoday-how-it-works__step">
                        <div class="nycbedtoday-how-it-works__step-number"><?php echo esc_html($index + 1); ?></div>
                        <div class="nycbedtoday-how-it-works__step-content">
                            <h4 class="nycbedtoday-how-it-works__step-title"><?php echo esc_html($step['title']); ?></h4>
                            <?php if (!empty($step['description'])) : ?>
                                <div class="nycbedtoday-how-it-works__step-description">
                                    <?php echo nycbedtoday_blocks_format_textarea($step['description']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Urgency Counter block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_urgency_counter($attributes)
{
    $time_message = '';

    if (!empty($attributes['targetTime'])) {
        try {
            $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');
            $now      = new DateTime('now', $timezone);
            $target   = new DateTime($attributes['targetTime'], $timezone);

            if ($target > $now) {
                $diff = $now->diff($target);
                $parts = [];

                if ($diff->d > 0) {
                    $parts[] = sprintf(_n('%d day', '%d days', $diff->d, 'nycbedtoday-blocks'), $diff->d);
                }
                if ($diff->h > 0) {
                    $parts[] = sprintf(_n('%d hour', '%d hours', $diff->h, 'nycbedtoday-blocks'), $diff->h);
                }
                if ($diff->i > 0 && count($parts) < 2) {
                    $parts[] = sprintf(_n('%d minute', '%d minutes', $diff->i, 'nycbedtoday-blocks'), $diff->i);
                }

                if (!empty($parts)) {
                    $time_message = sprintf(
                        /* translators: %s: human readable time left */
                        __('Book within %s to secure today’s delivery.', 'nycbedtoday-blocks'),
                        implode(' ', $parts)
                    );
                }
            }
        } catch (Exception $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            // Invalid date provided—fall back to default messaging.
        }
    }

    if (empty($time_message) && !empty($attributes['fallbackMessage'])) {
        $time_message = $attributes['fallbackMessage'];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-urgency-counter">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-urgency-counter__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['subheadline'])) : ?>
            <p class="nycbedtoday-urgency-counter__subheadline"><?php echo esc_html($attributes['subheadline']); ?></p>
        <?php endif; ?>
        <?php if (!empty($time_message)) : ?>
            <div class="nycbedtoday-urgency-counter__timer"><?php echo esc_html($time_message); ?></div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Local Neighborhoods block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_local_neighborhoods($attributes)
{
    $items = preg_split("/(\r\n|\r|\n)/", (string) $attributes['neighborhoodList']);
    $items = array_filter(array_map('trim', $items));

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-local-neighborhoods">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-local-neighborhoods__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['description'])) : ?>
            <div class="nycbedtoday-local-neighborhoods__description"><?php echo wp_kses_post($attributes['description']); ?></div>
        <?php endif; ?>
        <?php if (!empty($items)) : ?>
            <ul class="nycbedtoday-local-neighborhoods__list">
                <?php foreach ($items as $item) : ?>
                    <li class="nycbedtoday-local-neighborhoods__list-item"><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Reviews block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_reviews($attributes)
{
    $reviews = [];
    if (!empty($attributes['reviewOneQuote'])) {
        $reviews[] = [
            'quote'       => $attributes['reviewOneQuote'],
            'name'        => $attributes['reviewOneName'],
            'neighborhood'=> $attributes['reviewOneNeighborhood'],
        ];
    }
    if (!empty($attributes['reviewTwoQuote'])) {
        $reviews[] = [
            'quote'       => $attributes['reviewTwoQuote'],
            'name'        => $attributes['reviewTwoName'],
            'neighborhood'=> $attributes['reviewTwoNeighborhood'],
        ];
    }
    if (!empty($attributes['reviewThreeQuote'])) {
        $reviews[] = [
            'quote'       => $attributes['reviewThreeQuote'],
            'name'        => $attributes['reviewThreeName'],
            'neighborhood'=> $attributes['reviewThreeNeighborhood'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-reviews-carousel">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-reviews-carousel__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($reviews)) : ?>
            <div class="nycbedtoday-reviews-carousel__items">
                <?php foreach ($reviews as $review) : ?>
                    <blockquote class="nycbedtoday-reviews-carousel__item">
                        <div class="nycbedtoday-reviews-carousel__quote">
                            <?php echo nycbedtoday_blocks_format_textarea($review['quote']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                        <footer class="nycbedtoday-reviews-carousel__meta">
                            <?php if (!empty($review['name'])) : ?>
                                <span class="nycbedtoday-reviews-carousel__name"><?php echo esc_html($review['name']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($review['neighborhood'])) : ?>
                                <span class="nycbedtoday-reviews-carousel__neighborhood">&middot; <?php echo esc_html($review['neighborhood']); ?></span>
                            <?php endif; ?>
                        </footer>
                    </blockquote>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the FAQ block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_faq($attributes)
{
    $faq_items = [];
    if (!empty($attributes['faqOneQuestion'])) {
        $faq_items[] = [
            'question' => $attributes['faqOneQuestion'],
            'answer'   => $attributes['faqOneAnswer'],
        ];
    }
    if (!empty($attributes['faqTwoQuestion'])) {
        $faq_items[] = [
            'question' => $attributes['faqTwoQuestion'],
            'answer'   => $attributes['faqTwoAnswer'],
        ];
    }
    if (!empty($attributes['faqThreeQuestion'])) {
        $faq_items[] = [
            'question' => $attributes['faqThreeQuestion'],
            'answer'   => $attributes['faqThreeAnswer'],
        ];
    }

    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-faq">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-faq__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($faq_items)) : ?>
            <div class="nycbedtoday-faq__items">
                <?php foreach ($faq_items as $item) : ?>
                    <details class="nycbedtoday-faq__item">
                        <summary class="nycbedtoday-faq__question"><?php echo esc_html($item['question']); ?></summary>
                        <div class="nycbedtoday-faq__answer"><?php echo nycbedtoday_blocks_format_textarea($item['answer']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Renders the Final CTA block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function nycbedtoday_blocks_render_final_cta($attributes)
{
    ob_start();
    ?>
    <section class="nycbedtoday-block nycbedtoday-final-cta">
        <?php if (!empty($attributes['headline'])) : ?>
            <div class="nycbedtoday-final-cta__headline"><?php echo wp_kses_post($attributes['headline']); ?></div>
        <?php endif; ?>
        <?php if (!empty($attributes['description'])) : ?>
            <div class="nycbedtoday-final-cta__description"><?php echo wp_kses_post($attributes['description']); ?></div>
        <?php endif; ?>
        <div class="nycbedtoday-final-cta__actions">
            <?php if (!empty($attributes['buttonLabel'])) : ?>
                <a class="nycbedtoday-button nycbedtoday-button--primary" href="<?php echo esc_url($attributes['buttonUrl']); ?>">
                    <?php echo esc_html($attributes['buttonLabel']); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php if (!empty($attributes['secondaryText'])) : ?>
            <p class="nycbedtoday-final-cta__secondary"><?php echo esc_html($attributes['secondaryText']); ?></p>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
