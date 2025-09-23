<?php
/**
 * Template for displaying AI-generated key takeaways
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

// Parse takeaways data
$takeaways_data = is_string($takeaways) ? json_decode($takeaways, true) : $takeaways;
if (empty($takeaways_data) || !is_array($takeaways_data)) {
    return;
}

// Convert associative array to indexed array if needed
if (array_keys($takeaways_data) !== range(0, count($takeaways_data) - 1)) {
    $takeaways_data = array_values($takeaways_data);
}

// Get style class
$style_class = 'hmg-ai-takeaways-' . sanitize_html_class($atts['style']);
?>

<div class="hmg-ai-takeaways <?php echo esc_attr($style_class); ?>" data-hmg-component="takeaways">
    <div class="hmg-ai-takeaways-header">
        <h3 class="hmg-ai-takeaways-title">
            <span class="hmg-ai-icon">💡</span>
            Key Takeaways
        </h3>
        <div class="hmg-ai-branding">
            <span class="hmg-ai-powered-by">Powered by</span>
            <span class="hmg-ai-brand">Haley Marketing AI</span>
        </div>
    </div>
    
    <div class="hmg-ai-takeaways-content">
        <?php if ($atts['style'] === 'numbered'): ?>
            <ol class="hmg-ai-takeaways-list hmg-ai-numbered">
                <?php foreach ($takeaways_data as $index => $takeaway): ?>
                    <li class="hmg-ai-takeaway-item" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-takeaway-content">
                            <?php echo wp_kses_post($takeaway); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php elseif ($atts['style'] === 'cards'): ?>
            <div class="hmg-ai-takeaways-grid">
                <?php foreach ($takeaways_data as $index => $takeaway): ?>
                    <div class="hmg-ai-takeaway-card" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-card-number"><?php echo esc_html($index + 1); ?></div>
                        <div class="hmg-ai-card-content">
                            <?php echo wp_kses_post($takeaway); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($atts['style'] === 'highlights'): ?>
            <div class="hmg-ai-takeaways-highlights">
                <?php foreach ($takeaways_data as $index => $takeaway): ?>
                    <div class="hmg-ai-highlight-item" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-highlight-marker"></div>
                        <div class="hmg-ai-highlight-content">
                            <?php echo wp_kses_post($takeaway); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: // default style ?>
            <ul class="hmg-ai-takeaways-list hmg-ai-default">
                <?php foreach ($takeaways_data as $index => $takeaway): ?>
                    <li class="hmg-ai-takeaway-item" data-index="<?php echo esc_attr($index + 1); ?>">
                        <div class="hmg-ai-takeaway-bullet">
                            <span class="hmg-ai-bullet-icon">✓</span>
                        </div>
                        <div class="hmg-ai-takeaway-content">
                            <?php echo wp_kses_post($takeaway); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="hmg-ai-takeaways-footer">
        <div class="hmg-ai-meta">
            <span class="hmg-ai-count"><?php echo count($takeaways_data); ?> key insights</span>
            <span class="hmg-ai-separator">•</span>
            <span class="hmg-ai-generated">AI-generated</span>
        </div>
    </div>
</div> 