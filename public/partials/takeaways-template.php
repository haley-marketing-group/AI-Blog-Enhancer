<?php
/**
 * Template for displaying AI-generated takeaways
 *
 * Available variables:
 * - $takeaways_data: Array of takeaway items
 * - $style: Display style (default, numbered, cards, highlights)
 * - $post_id: The post ID
 *
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (empty($takeaways_data)) {
    return;
}
?>

<div class="hmg-ai-takeaways hmg-ai-takeaways-<?php echo esc_attr($style); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <h3 class="hmg-ai-takeaways-title">
        <?php _e('Key Takeaways', 'hmg-ai-blog-enhancer'); ?>
    </h3>
    
    <?php if ($style === 'cards'): ?>
        <div class="hmg-ai-takeaways-cards">
            <?php foreach ($takeaways_data as $index => $takeaway): ?>
                <div class="hmg-ai-takeaway-card">
                    <div class="hmg-ai-takeaway-number"><?php echo ($index + 1); ?></div>
                    <div class="hmg-ai-takeaway-content"><?php echo esc_html($takeaway); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    
    <?php elseif ($style === 'numbered'): ?>
        <ol class="hmg-ai-takeaways-numbered">
            <?php foreach ($takeaways_data as $takeaway): ?>
                <li class="hmg-ai-takeaway-item">
                    <span class="hmg-ai-takeaway-text"><?php echo esc_html($takeaway); ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    
    <?php elseif ($style === 'highlights'): ?>
        <div class="hmg-ai-takeaways-highlights">
            <?php foreach ($takeaways_data as $index => $takeaway): ?>
                <div class="hmg-ai-takeaway-highlight">
                    <div class="hmg-ai-takeaway-marker"></div>
                    <div class="hmg-ai-takeaway-content">
                        <strong><?php echo sprintf(__('Point %d:', 'hmg-ai-blog-enhancer'), $index + 1); ?></strong>
                        <?php echo esc_html($takeaway); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    
    <?php else: // default style ?>
        <ul class="hmg-ai-takeaways-list">
            <?php foreach ($takeaways_data as $takeaway): ?>
                <li class="hmg-ai-takeaway-item">
                    <span class="hmg-ai-takeaway-icon">•</span>
                    <span class="hmg-ai-takeaway-text"><?php echo esc_html($takeaway); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>