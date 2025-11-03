<?php
/**
 * Block registration and configuration.
 *
 * @package NYC Bed Today Blocks
 */

defined('ABSPATH') || exit;

/**
 * Registers custom block category.
 *
 * @param array $categories Existing categories.
 * @param mixed $post       Current post context.
 *
 * @return array
 */
function nycbedtoday_blocks_register_category($categories, $post)
{
    $category = [
        'slug'  => 'nycbedtoday',
        'title' => __('NYC Bed Today', 'nycbedtoday-blocks'),
    ];

    foreach ($categories as $existing) {
        if (isset($existing['slug']) && $existing['slug'] === $category['slug']) {
            return $categories;
        }
    }

    $categories[] = $category;

    return $categories;
}

/**
 * Returns configuration for all custom blocks.
 *
 * @return array
 */
function nycbedtoday_blocks_get_definitions()
{
    return [
        'hero-offer'         => [
            'title'       => __('Hero Offer', 'nycbedtoday-blocks'),
            'description' => __('Prominent hero section with flexible messaging and calls to action.', 'nycbedtoday-blocks'),
            'icon'        => 'cover-image',
            'class_name'  => 'nycbedtoday-hero-offer',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'eyebrow'             => [
                    'label'       => __('Eyebrow', 'nycbedtoday-blocks'),
                    'default'     => __('Same-day delivery across NYC', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Same-day delivery across NYC', 'nycbedtoday-blocks'),
                ],
                'headline'            => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Sleep better tonight with NYC Bed Today', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h1',
                    'placeholder' => __('Sleep better tonight with NYC Bed Today', 'nycbedtoday-blocks'),
                ],
                'description'         => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Premium mattresses delivered the same day across all five boroughs. Try yours risk-free.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Premium mattresses delivered the same day across all five boroughs. Try yours risk-free.', 'nycbedtoday-blocks'),
                ],
                'primaryButtonLabel'  => [
                    'label'       => __('Primary button label', 'nycbedtoday-blocks'),
                    'default'     => __('Shop mattresses', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Shop mattresses', 'nycbedtoday-blocks'),
                ],
                'primaryButtonUrl'    => [
                    'label'       => __('Primary button URL', 'nycbedtoday-blocks'),
                    'default'     => '#',
                    'control'     => 'url',
                    'placeholder' => __('https://', 'nycbedtoday-blocks'),
                ],
                'secondaryButtonLabel' => [
                    'label'       => __('Secondary button label', 'nycbedtoday-blocks'),
                    'default'     => __('Talk to a sleep expert', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Talk to a sleep expert', 'nycbedtoday-blocks'),
                ],
                'secondaryButtonUrl'  => [
                    'label'       => __('Secondary button URL', 'nycbedtoday-blocks'),
                    'default'     => '#',
                    'control'     => 'url',
                    'placeholder' => __('https://', 'nycbedtoday-blocks'),
                ],
                'backgroundImageUrl'  => [
                    'label'       => __('Background image URL', 'nycbedtoday-blocks'),
                    'default'     => '',
                    'control'     => 'url',
                    'placeholder' => __('https://', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'social-proof-strip' => [
            'title'       => __('Social Proof Strip', 'nycbedtoday-blocks'),
            'description' => __('Compact strip highlighting social proof metrics.', 'nycbedtoday-blocks'),
            'icon'        => 'awards',
            'class_name'  => 'nycbedtoday-social-proof',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'        => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Trusted by 10,000+ New Yorkers', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Trusted by 10,000+ New Yorkers', 'nycbedtoday-blocks'),
                ],
                'supportingText'  => [
                    'label'       => __('Supporting text', 'nycbedtoday-blocks'),
                    'default'     => __('Five-star rated delivery experience', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Five-star rated delivery experience', 'nycbedtoday-blocks'),
                ],
                'statOneLabel'    => [
                    'label'       => __('First stat label', 'nycbedtoday-blocks'),
                    'default'     => __('Average rating', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Average rating', 'nycbedtoday-blocks'),
                ],
                'statOneValue'    => [
                    'label'       => __('First stat value', 'nycbedtoday-blocks'),
                    'default'     => __('4.9 / 5', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('4.9 / 5', 'nycbedtoday-blocks'),
                ],
                'statTwoLabel'    => [
                    'label'       => __('Second stat label', 'nycbedtoday-blocks'),
                    'default'     => __('Same-day deliveries', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Same-day deliveries', 'nycbedtoday-blocks'),
                ],
                'statTwoValue'    => [
                    'label'       => __('Second stat value', 'nycbedtoday-blocks'),
                    'default'     => __('+12,500', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('+12,500', 'nycbedtoday-blocks'),
                ],
                'statThreeLabel'  => [
                    'label'       => __('Third stat label', 'nycbedtoday-blocks'),
                    'default'     => __('On-time rate', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('On-time rate', 'nycbedtoday-blocks'),
                ],
                'statThreeValue'  => [
                    'label'       => __('Third stat value', 'nycbedtoday-blocks'),
                    'default'     => __('99.2%', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('99.2%', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'product-picker'     => [
            'title'       => __('Product Picker', 'nycbedtoday-blocks'),
            'description' => __('Showcase curated product options side by side.', 'nycbedtoday-blocks'),
            'icon'        => 'cart',
            'class_name'  => 'nycbedtoday-product-picker',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'               => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Choose your perfect mattress', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h2',
                    'placeholder' => __('Choose your perfect mattress', 'nycbedtoday-blocks'),
                ],
                'description'            => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Select one of our curated comfort profiles and schedule delivery in under five minutes.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Select one of our curated comfort profiles and schedule delivery in under five minutes.', 'nycbedtoday-blocks'),
                ],
                'productOneTitle'        => [
                    'label'       => __('First product title', 'nycbedtoday-blocks'),
                    'default'     => __('The Classic', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('The Classic', 'nycbedtoday-blocks'),
                ],
                'productOneDescription'  => [
                    'label'       => __('First product description', 'nycbedtoday-blocks'),
                    'default'     => __('Balanced support with breathable comfort layers.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Balanced support with breathable comfort layers.', 'nycbedtoday-blocks'),
                ],
                'productOnePrice'        => [
                    'label'       => __('First product price', 'nycbedtoday-blocks'),
                    'default'     => __('$699', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('$699', 'nycbedtoday-blocks'),
                ],
                'productTwoTitle'        => [
                    'label'       => __('Second product title', 'nycbedtoday-blocks'),
                    'default'     => __('The Plush', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('The Plush', 'nycbedtoday-blocks'),
                ],
                'productTwoDescription'  => [
                    'label'       => __('Second product description', 'nycbedtoday-blocks'),
                    'default'     => __('A cloud-like feel with pressure-relief for side sleepers.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('A cloud-like feel with pressure-relief for side sleepers.', 'nycbedtoday-blocks'),
                ],
                'productTwoPrice'        => [
                    'label'       => __('Second product price', 'nycbedtoday-blocks'),
                    'default'     => __('$849', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('$849', 'nycbedtoday-blocks'),
                ],
                'productThreeTitle'      => [
                    'label'       => __('Third product title', 'nycbedtoday-blocks'),
                    'default'     => __('The Elevated', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('The Elevated', 'nycbedtoday-blocks'),
                ],
                'productThreeDescription' => [
                    'label'       => __('Third product description', 'nycbedtoday-blocks'),
                    'default'     => __('Adjustable base with zero-gravity presets for weightless comfort.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Adjustable base with zero-gravity presets for weightless comfort.', 'nycbedtoday-blocks'),
                ],
                'productThreePrice'      => [
                    'label'       => __('Third product price', 'nycbedtoday-blocks'),
                    'default'     => __('$1,199', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('$1,199', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'zip-checker'       => [
            'title'       => __('ZIP Checker', 'nycbedtoday-blocks'),
            'description' => __('Form that prompts visitors to confirm delivery availability.', 'nycbedtoday-blocks'),
            'icon'        => 'location-alt',
            'class_name'  => 'nycbedtoday-zip-checker',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'       => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Check delivery in your neighborhood', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('Check delivery in your neighborhood', 'nycbedtoday-blocks'),
                ],
                'description'    => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Enter your ZIP code to confirm same-day availability.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Enter your ZIP code to confirm same-day availability.', 'nycbedtoday-blocks'),
                ],
                'placeholder'    => [
                    'label'       => __('Input placeholder', 'nycbedtoday-blocks'),
                    'default'     => __('e.g. 10001', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('e.g. 10001', 'nycbedtoday-blocks'),
                ],
                'buttonLabel'    => [
                    'label'       => __('Button label', 'nycbedtoday-blocks'),
                    'default'     => __('Check availability', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Check availability', 'nycbedtoday-blocks'),
                ],
                'successMessage' => [
                    'label'       => __('Success message', 'nycbedtoday-blocks'),
                    'default'     => __('Great news! Same-day delivery is available for your ZIP.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Great news! Same-day delivery is available for your ZIP.', 'nycbedtoday-blocks'),
                ],
                'errorMessage'   => [
                    'label'       => __('Error message', 'nycbedtoday-blocks'),
                    'default'     => __('We’re expanding fast—chat with us to find an alternate delivery time.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('We’re expanding fast—chat with us to find an alternate delivery time.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'time-slots'        => [
            'title'       => __('Time Slots', 'nycbedtoday-blocks'),
            'description' => __('Highlights the next available delivery windows.', 'nycbedtoday-blocks'),
            'icon'        => 'clock',
            'class_name'  => 'nycbedtoday-time-slots',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'       => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Available delivery windows', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('Available delivery windows', 'nycbedtoday-blocks'),
                ],
                'description'    => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Pick the slot that fits your schedule—our team does the heavy lifting.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Pick the slot that fits your schedule—our team does the heavy lifting.', 'nycbedtoday-blocks'),
                ],
                'slotOneLabel'   => [
                    'label'       => __('First slot label', 'nycbedtoday-blocks'),
                    'default'     => __('Morning', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Morning', 'nycbedtoday-blocks'),
                ],
                'slotOneWindow'  => [
                    'label'       => __('First slot window', 'nycbedtoday-blocks'),
                    'default'     => __('8:00 AM – 11:00 AM', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('8:00 AM – 11:00 AM', 'nycbedtoday-blocks'),
                ],
                'slotTwoLabel'   => [
                    'label'       => __('Second slot label', 'nycbedtoday-blocks'),
                    'default'     => __('Afternoon', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Afternoon', 'nycbedtoday-blocks'),
                ],
                'slotTwoWindow'  => [
                    'label'       => __('Second slot window', 'nycbedtoday-blocks'),
                    'default'     => __('12:00 PM – 3:00 PM', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('12:00 PM – 3:00 PM', 'nycbedtoday-blocks'),
                ],
                'slotThreeLabel' => [
                    'label'       => __('Third slot label', 'nycbedtoday-blocks'),
                    'default'     => __('Evening', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Evening', 'nycbedtoday-blocks'),
                ],
                'slotThreeWindow' => [
                    'label'       => __('Third slot window', 'nycbedtoday-blocks'),
                    'default'     => __('4:00 PM – 7:00 PM', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('4:00 PM – 7:00 PM', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'value-stack'       => [
            'title'       => __('Value Stack', 'nycbedtoday-blocks'),
            'description' => __('Summarises the key benefits of choosing NYC Bed Today.', 'nycbedtoday-blocks'),
            'icon'        => 'yes-alt',
            'class_name'  => 'nycbedtoday-value-stack',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'            => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Why NYC Bed Today?', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('Why NYC Bed Today?', 'nycbedtoday-blocks'),
                ],
                'description'         => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Everything you need for a full night of rest, delivered and set up today.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Everything you need for a full night of rest, delivered and set up today.', 'nycbedtoday-blocks'),
                ],
                'pointOneTitle'       => [
                    'label'       => __('First point title', 'nycbedtoday-blocks'),
                    'default'     => __('Full-service delivery', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Full-service delivery', 'nycbedtoday-blocks'),
                ],
                'pointOneDescription' => [
                    'label'       => __('First point description', 'nycbedtoday-blocks'),
                    'default'     => __('Two-person team handles staircases, setup, and packaging removal.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Two-person team handles staircases, setup, and packaging removal.', 'nycbedtoday-blocks'),
                ],
                'pointTwoTitle'       => [
                    'label'       => __('Second point title', 'nycbedtoday-blocks'),
                    'default'     => __('Premium mattresses', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Premium mattresses', 'nycbedtoday-blocks'),
                ],
                'pointTwoDescription' => [
                    'label'       => __('Second point description', 'nycbedtoday-blocks'),
                    'default'     => __('Five curated comfort profiles tested for popular NYC apartments.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Five curated comfort profiles tested for popular NYC apartments.', 'nycbedtoday-blocks'),
                ],
                'pointThreeTitle'     => [
                    'label'       => __('Third point title', 'nycbedtoday-blocks'),
                    'default'     => __('Risk-free comfort', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Risk-free comfort', 'nycbedtoday-blocks'),
                ],
                'pointThreeDescription' => [
                    'label'       => __('Third point description', 'nycbedtoday-blocks'),
                    'default'     => __('120-night trial with free exchanges during your first 30 nights.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('120-night trial with free exchanges during your first 30 nights.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'how-it-works'      => [
            'title'       => __('How It Works', 'nycbedtoday-blocks'),
            'description' => __('Step-by-step overview of the NYC Bed Today experience.', 'nycbedtoday-blocks'),
            'icon'        => 'editor-ol',
            'class_name'  => 'nycbedtoday-how-it-works',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'          => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('How it works', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('How it works', 'nycbedtoday-blocks'),
                ],
                'stepOneTitle'      => [
                    'label'       => __('First step title', 'nycbedtoday-blocks'),
                    'default'     => __('Choose your setup', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Choose your setup', 'nycbedtoday-blocks'),
                ],
                'stepOneDescription' => [
                    'label'       => __('First step description', 'nycbedtoday-blocks'),
                    'default'     => __('Pick your mattress style, base, and any add-ons in a guided quiz.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Pick your mattress style, base, and any add-ons in a guided quiz.', 'nycbedtoday-blocks'),
                ],
                'stepTwoTitle'      => [
                    'label'       => __('Second step title', 'nycbedtoday-blocks'),
                    'default'     => __('Book your delivery', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Book your delivery', 'nycbedtoday-blocks'),
                ],
                'stepTwoDescription' => [
                    'label'       => __('Second step description', 'nycbedtoday-blocks'),
                    'default'     => __('Choose a time slot that works—we text you when we are on the way.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Choose a time slot that works—we text you when we are on the way.', 'nycbedtoday-blocks'),
                ],
                'stepThreeTitle'    => [
                    'label'       => __('Third step title', 'nycbedtoday-blocks'),
                    'default'     => __('Relax and enjoy', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Relax and enjoy', 'nycbedtoday-blocks'),
                ],
                'stepThreeDescription' => [
                    'label'       => __('Third step description', 'nycbedtoday-blocks'),
                    'default'     => __('We assemble everything, remove packaging, and take away your old mattress on request.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('We assemble everything, remove packaging, and take away your old mattress on request.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'urgency-counter'   => [
            'title'       => __('Urgency Counter', 'nycbedtoday-blocks'),
            'description' => __('Highlights how long is left to secure the next delivery slot.', 'nycbedtoday-blocks'),
            'icon'        => 'warning',
            'class_name'  => 'nycbedtoday-urgency-counter',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'        => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Last delivery windows closing soon', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h4',
                    'placeholder' => __('Last delivery windows closing soon', 'nycbedtoday-blocks'),
                ],
                'subheadline'     => [
                    'label'       => __('Subheadline', 'nycbedtoday-blocks'),
                    'default'     => __('Reserve before midnight to guarantee tonight’s setup.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Reserve before midnight to guarantee tonight’s setup.', 'nycbedtoday-blocks'),
                ],
                'targetTime'      => [
                    'label'       => __('Target cutoff (ISO 8601 date/time)', 'nycbedtoday-blocks'),
                    'default'     => gmdate('Y-m-d\TH:i:s', strtotime('+1 day')),
                    'control'     => 'datetime',
                    'placeholder' => gmdate('Y-m-d\TH:i:s'),
                ],
                'fallbackMessage' => [
                    'label'       => __('Fallback message', 'nycbedtoday-blocks'),
                    'default'     => __('Our next route is loading now—reserve to see available times.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Our next route is loading now—reserve to see available times.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'local-neighborhoods' => [
            'title'       => __('Local Neighborhoods', 'nycbedtoday-blocks'),
            'description' => __('Lists neighborhoods and boroughs covered by NYC Bed Today.', 'nycbedtoday-blocks'),
            'icon'        => 'admin-site',
            'class_name'  => 'nycbedtoday-local-neighborhoods',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'         => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Serving all five boroughs', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('Serving all five boroughs', 'nycbedtoday-blocks'),
                ],
                'description'      => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('From Harlem to Hoboken, we arrive ready to set up your dream bedroom.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('From Harlem to Hoboken, we arrive ready to set up your dream bedroom.', 'nycbedtoday-blocks'),
                ],
                'neighborhoodList' => [
                    'label'       => __('Neighborhoods (one per line)', 'nycbedtoday-blocks'),
                    'default'     => "Upper East Side\nBrooklyn Heights\nWilliamsburg\nAstoria\nLong Island City\nDowntown Jersey City",
                    'control'     => 'textarea',
                    'placeholder' => "Upper East Side\nBrooklyn Heights\nWilliamsburg",
                ],
            ],
        ],
        'reviews-carousel'  => [
            'title'       => __('Reviews Carousel', 'nycbedtoday-blocks'),
            'description' => __('Highlights customer testimonials in a simple rotating style.', 'nycbedtoday-blocks'),
            'icon'        => 'format-quote',
            'class_name'  => 'nycbedtoday-reviews-carousel',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'             => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('What our neighbors say', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('What our neighbors say', 'nycbedtoday-blocks'),
                ],
                'reviewOneQuote'       => [
                    'label'       => __('First review quote', 'nycbedtoday-blocks'),
                    'default'     => __('"Delivery was on time, setup was flawless, and I was sleeping in my new bed by dinner."', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('"Delivery was on time, setup was flawless, and I was sleeping in my new bed by dinner."', 'nycbedtoday-blocks'),
                ],
                'reviewOneName'        => [
                    'label'       => __('First review name', 'nycbedtoday-blocks'),
                    'default'     => __('Maya R.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Maya R.', 'nycbedtoday-blocks'),
                ],
                'reviewOneNeighborhood' => [
                    'label'       => __('First review neighborhood', 'nycbedtoday-blocks'),
                    'default'     => __('Park Slope', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Park Slope', 'nycbedtoday-blocks'),
                ],
                'reviewTwoQuote'       => [
                    'label'       => __('Second review quote', 'nycbedtoday-blocks'),
                    'default'     => __('"Felt like a luxury concierge service—worth every penny."', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('"Felt like a luxury concierge service—worth every penny."', 'nycbedtoday-blocks'),
                ],
                'reviewTwoName'        => [
                    'label'       => __('Second review name', 'nycbedtoday-blocks'),
                    'default'     => __('Andre L.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Andre L.', 'nycbedtoday-blocks'),
                ],
                'reviewTwoNeighborhood' => [
                    'label'       => __('Second review neighborhood', 'nycbedtoday-blocks'),
                    'default'     => __('Upper West Side', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Upper West Side', 'nycbedtoday-blocks'),
                ],
                'reviewThreeQuote'     => [
                    'label'       => __('Third review quote', 'nycbedtoday-blocks'),
                    'default'     => __('"Booked at lunch, sleeping like royalty by nightfall."', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('"Booked at lunch, sleeping like royalty by nightfall."', 'nycbedtoday-blocks'),
                ],
                'reviewThreeName'      => [
                    'label'       => __('Third review name', 'nycbedtoday-blocks'),
                    'default'     => __('Jessie T.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Jessie T.', 'nycbedtoday-blocks'),
                ],
                'reviewThreeNeighborhood' => [
                    'label'       => __('Third review neighborhood', 'nycbedtoday-blocks'),
                    'default'     => __('Astoria', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Astoria', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'faq'               => [
            'title'       => __('FAQ', 'nycbedtoday-blocks'),
            'description' => __('Simple frequently asked questions section.', 'nycbedtoday-blocks'),
            'icon'        => 'editor-help',
            'class_name'  => 'nycbedtoday-faq',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'       => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Frequently asked questions', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h3',
                    'placeholder' => __('Frequently asked questions', 'nycbedtoday-blocks'),
                ],
                'faqOneQuestion' => [
                    'label'       => __('First question', 'nycbedtoday-blocks'),
                    'default'     => __('How fast can you deliver?', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('How fast can you deliver?', 'nycbedtoday-blocks'),
                ],
                'faqOneAnswer'   => [
                    'label'       => __('First answer', 'nycbedtoday-blocks'),
                    'default'     => __('Schedule before 3PM and we arrive the very same evening. Later requests roll to the next morning routes.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Schedule before 3PM and we arrive the very same evening.', 'nycbedtoday-blocks'),
                ],
                'faqTwoQuestion' => [
                    'label'       => __('Second question', 'nycbedtoday-blocks'),
                    'default'     => __('Do you remove old mattresses?', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Do you remove old mattresses?', 'nycbedtoday-blocks'),
                ],
                'faqTwoAnswer'   => [
                    'label'       => __('Second answer', 'nycbedtoday-blocks'),
                    'default'     => __('Yes—we handle disposal or donation for a small additional fee. Just let us know during booking.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Yes—we handle disposal or donation for a small additional fee.', 'nycbedtoday-blocks'),
                ],
                'faqThreeQuestion' => [
                    'label'       => __('Third question', 'nycbedtoday-blocks'),
                    'default'     => __('What is the trial policy?', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('What is the trial policy?', 'nycbedtoday-blocks'),
                ],
                'faqThreeAnswer'   => [
                    'label'       => __('Third answer', 'nycbedtoday-blocks'),
                    'default'     => __('Every mattress comes with 120 nights to get comfortable. Exchanges are free within the first 30 nights.', 'nycbedtoday-blocks'),
                    'control'     => 'textarea',
                    'placeholder' => __('Every mattress comes with 120 nights to get comfortable.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
        'final-cta'         => [
            'title'       => __('Final Call to Action', 'nycbedtoday-blocks'),
            'description' => __('Closing section encouraging visitors to book their delivery.', 'nycbedtoday-blocks'),
            'icon'        => 'megaphone',
            'class_name'  => 'nycbedtoday-final-cta',
            'supports'    => [
                'anchor' => true,
                'align'  => ['wide', 'full'],
                'html'   => false,
            ],
            'fields'      => [
                'headline'      => [
                    'label'       => __('Headline', 'nycbedtoday-blocks'),
                    'default'     => __('Ready for the best sleep in the city?', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'h2',
                    'placeholder' => __('Ready for the best sleep in the city?', 'nycbedtoday-blocks'),
                ],
                'description'   => [
                    'label'       => __('Description', 'nycbedtoday-blocks'),
                    'default'     => __('Pick your mattress, choose your time, and we handle the rest—white glove service included.', 'nycbedtoday-blocks'),
                    'control'     => 'richtext',
                    'tag'         => 'p',
                    'placeholder' => __('Pick your mattress, choose your time, and we handle the rest—white glove service included.', 'nycbedtoday-blocks'),
                ],
                'buttonLabel'   => [
                    'label'       => __('Button label', 'nycbedtoday-blocks'),
                    'default'     => __('Reserve my delivery', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Reserve my delivery', 'nycbedtoday-blocks'),
                ],
                'buttonUrl'     => [
                    'label'       => __('Button URL', 'nycbedtoday-blocks'),
                    'default'     => '#',
                    'control'     => 'url',
                    'placeholder' => __('https://', 'nycbedtoday-blocks'),
                ],
                'secondaryText' => [
                    'label'       => __('Secondary text', 'nycbedtoday-blocks'),
                    'default'     => __('Same-day delivery available across all five boroughs.', 'nycbedtoday-blocks'),
                    'control'     => 'text',
                    'placeholder' => __('Same-day delivery available across all five boroughs.', 'nycbedtoday-blocks'),
                ],
            ],
        ],
    ];
}

/**
 * Registers scripts, styles, and block types.
 *
 * @return void
 */
function nycbedtoday_blocks_register()
{
    $script_path = NYCBEDTODAY_BLOCKS_PATH . '/assets/js/editor.js';
    $style_path  = NYCBEDTODAY_BLOCKS_PATH . '/assets/css/blocks.css';

    $script_dependencies = ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'];

    wp_register_script(
        'nycbedtoday-blocks-editor',
        NYCBEDTODAY_BLOCKS_URL . '/assets/js/editor.js',
        $script_dependencies,
        file_exists($script_path) ? filemtime($script_path) : NYCBEDTODAY_BLOCKS_VERSION,
        true
    );

    wp_register_style(
        'nycbedtoday-blocks-style',
        NYCBEDTODAY_BLOCKS_URL . '/assets/css/blocks.css',
        [],
        file_exists($style_path) ? filemtime($style_path) : NYCBEDTODAY_BLOCKS_VERSION
    );

    wp_register_style(
        'nycbedtoday-blocks-editor-style',
        NYCBEDTODAY_BLOCKS_URL . '/assets/css/blocks.css',
        ['wp-edit-blocks'],
        file_exists($style_path) ? filemtime($style_path) : NYCBEDTODAY_BLOCKS_VERSION
    );

    $definitions = nycbedtoday_blocks_get_definitions();

    $definitions_for_js = [];
    foreach ($definitions as $slug => $definition) {
        $fields_for_js = [];
        foreach ($definition['fields'] as $field_name => $field_config) {
            $fields_for_js[] = [
                'name'        => $field_name,
                'label'       => $field_config['label'],
                'control'     => $field_config['control'],
                'placeholder' => isset($field_config['placeholder']) ? $field_config['placeholder'] : '',
                'default'     => isset($field_config['default']) ? $field_config['default'] : '',
                'tag'         => isset($field_config['tag']) ? $field_config['tag'] : '',
            ];
        }

        $definitions_for_js[] = [
            'name'        => 'nycbedtoday/' . $slug,
            'slug'        => $slug,
            'title'       => $definition['title'],
            'description' => $definition['description'],
            'icon'        => isset($definition['icon']) ? $definition['icon'] : 'layout',
            'className'   => $definition['class_name'],
            'fields'      => $fields_for_js,
            'supports'    => isset($definition['supports']) ? $definition['supports'] : new stdClass(),
        ];
    }

    wp_localize_script(
        'nycbedtoday-blocks-editor',
        'nycbedtodayBlockLibrary',
        [
            'category' => [
                'slug'  => 'nycbedtoday',
                'title' => __('NYC Bed Today', 'nycbedtoday-blocks'),
            ],
            'blocks'   => $definitions_for_js,
        ]
    );

    foreach ($definitions as $slug => $definition) {
        $attributes = [];
        foreach ($definition['fields'] as $field_name => $field_config) {
            $attributes[$field_name] = [
                'type'    => 'string',
                'default' => isset($field_config['default']) ? $field_config['default'] : '',
            ];
        }

        $settings = [
            'api_version'     => 2,
            'title'           => $definition['title'],
            'description'     => $definition['description'],
            'category'        => 'nycbedtoday',
            'icon'            => isset($definition['icon']) ? $definition['icon'] : 'layout',
            'attributes'      => $attributes,
            'style'           => 'nycbedtoday-blocks-style',
            'editor_style'    => 'nycbedtoday-blocks-editor-style',
            'editor_script'   => 'nycbedtoday-blocks-editor',
            'supports'        => isset($definition['supports']) ? $definition['supports'] : [],
            'render_callback' => function ($attributes, $content, $block) use ($slug) {
                return nycbedtoday_blocks_render($slug, $attributes, $content, $block);
            },
        ];

        register_block_type('nycbedtoday/' . $slug, $settings);
    }
}
