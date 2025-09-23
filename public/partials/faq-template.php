<?php
/**
 * Template for displaying AI-generated FAQ section
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/public/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Parse FAQ data
$faq_data = is_string($faq) ? json_decode($faq, true) : $faq;
if (empty($faq_data) || !is_array($faq_data)) {
    return;
}

// Get style class
$style_class = 'hmg-ai-faq-' . sanitize_html_class($atts['style']);
$unique_id = 'hmg-ai-faq-' . uniqid();
?>

<div class="hmg-ai-faq <?php echo esc_attr($style_class); ?>" data-hmg-component="faq" id="<?php echo esc_attr($unique_id); ?>">
    <div class="hmg-ai-faq-header">
        <h3 class="hmg-ai-faq-title">
            <span class="hmg-ai-icon">❓</span>
            Frequently Asked Questions
        </h3>
        <div class="hmg-ai-branding">
            <span class="hmg-ai-powered-by">Powered by</span>
            <span class="hmg-ai-brand">Haley Marketing AI</span>
        </div>
    </div>
    
    <div class="hmg-ai-faq-content">
        <?php if ($atts['style'] === 'list'): ?>
            <div class="hmg-ai-faq-list">
                <?php foreach ($faq_data as $index => $item): ?>
                    <div class="hmg-ai-faq-item" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-faq-question">
                            <h4><?php echo wp_kses_post($item['question']); ?></h4>
                        </div>
                        <div class="hmg-ai-faq-answer">
                            <?php echo wp_kses_post($item['answer']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($atts['style'] === 'cards'): ?>
            <div class="hmg-ai-faq-cards">
                <?php foreach ($faq_data as $index => $item): ?>
                    <div class="hmg-ai-faq-card" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-faq-card-header">
                            <div class="hmg-ai-faq-card-icon">Q</div>
                            <h4 class="hmg-ai-faq-card-question"><?php echo wp_kses_post($item['question']); ?></h4>
                        </div>
                        <div class="hmg-ai-faq-card-answer">
                            <?php echo wp_kses_post($item['answer']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: // accordion style (default) ?>
            <div class="hmg-ai-faq-accordion" role="tablist" aria-multiselectable="true">
                <?php foreach ($faq_data as $index => $item): ?>
                    <?php 
                    $item_id = $unique_id . '-item-' . $index;
                    $is_first = $index === 0;
                    ?>
                    <div class="hmg-ai-faq-accordion-item" data-index="<?php echo esc_attr($index + 1); ?>">
                        <button 
                            class="hmg-ai-faq-accordion-button <?php echo $is_first ? 'hmg-ai-active' : ''; ?>"
                            type="button"
                            role="tab"
                            aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo esc_attr($item_id); ?>"
                            id="<?php echo esc_attr($item_id); ?>-button"
                            data-hmg-faq-toggle
                        >
                            <span class="hmg-ai-faq-question-text">
                                <?php echo wp_kses_post($item['question']); ?>
                            </span>
                            <span class="hmg-ai-faq-accordion-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                            </span>
                        </button>
                        <div 
                            class="hmg-ai-faq-accordion-content <?php echo $is_first ? 'hmg-ai-active' : ''; ?>"
                            role="tabpanel"
                            aria-labelledby="<?php echo esc_attr($item_id); ?>-button"
                            id="<?php echo esc_attr($item_id); ?>"
                            <?php echo $is_first ? '' : 'style="display: none;"'; ?>
                        >
                            <div class="hmg-ai-faq-accordion-body">
                                <?php echo wp_kses_post($item['answer']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="hmg-ai-faq-footer">
        <div class="hmg-ai-meta">
            <span class="hmg-ai-count"><?php echo count($faq_data); ?> questions answered</span>
            <span class="hmg-ai-separator">•</span>
            <span class="hmg-ai-generated">AI-generated</span>
        </div>
    </div>
</div>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        <?php foreach ($faq_data as $index => $item): ?>
        {
            "@type": "Question",
            "name": "<?php echo esc_js($item['question']); ?>",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "<?php echo esc_js(wp_strip_all_tags($item['answer'])); ?>"
            }
        }<?php echo $index < count($faq_data) - 1 ? ',' : ''; ?>
        <?php endforeach; ?>
    ]
}
</script> 