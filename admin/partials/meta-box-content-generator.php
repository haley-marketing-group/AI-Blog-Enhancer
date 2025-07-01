<?php
/**
 * Provide a admin area view for the content generator meta box
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Add nonce field for security
wp_nonce_field('hmg_ai_meta_box', 'hmg_ai_meta_nonce');

// Get current post ID
$post_id = $post->ID ?? 0;
$options = get_option('hmg_ai_blog_enhancer_options', array());
?>

<div class="hmg-ai-meta-box">
    <div class="hmg-ai-notices"></div>
    
    <?php if (empty($options['api_key'])): ?>
        <div class="hmg-ai-notice warning">
            <p>
                <?php _e('API key not configured.', 'hmg-ai-blog-enhancer'); ?>
                <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>">
                    <?php _e('Configure now', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        
        <!-- Usage Meter -->
        <div class="hmg-ai-usage-meter">
            <h4><?php _e('Usage This Month', 'hmg-ai-blog-enhancer'); ?></h4>
            
            <div class="hmg-ai-usage-section">
                <label><?php _e('API Calls', 'hmg-ai-blog-enhancer'); ?></label>
                <div class="hmg-ai-usage-bar">
                    <div class="hmg-ai-usage-fill api-calls" data-width="15"></div>
                </div>
                <div class="hmg-ai-usage-stats">
                    <span class="api-calls-used">150</span> / <span class="api-calls-limit">1000</span>
                </div>
            </div>
            
            <div class="hmg-ai-usage-section">
                <label><?php _e('Tokens', 'hmg-ai-blog-enhancer'); ?></label>
                <div class="hmg-ai-usage-bar">
                    <div class="hmg-ai-usage-fill tokens" data-width="25"></div>
                </div>
                <div class="hmg-ai-usage-stats">
                    <span class="tokens-used">25,000</span> / <span class="tokens-limit">100,000</span>
                </div>
            </div>
            
            <p class="hmg-ai-reset-info">
                <small><?php _e('Resets on:', 'hmg-ai-blog-enhancer'); ?> <span class="hmg-ai-reset-date"><?php echo date('M j, Y', strtotime('+1 month')); ?></span></small>
            </p>
        </div>

        <!-- Content Generation Controls -->
        <div class="hmg-ai-generation-controls">
            <h4><?php _e('Generate AI Content', 'hmg-ai-blog-enhancer'); ?></h4>
            
            <button type="button" class="hmg-ai-button hmg-ai-generate-takeaways" data-post-id="<?php echo $post_id; ?>">
                <span class="dashicons dashicons-list-view"></span>
                <?php _e('Generate Key Takeaways', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button secondary hmg-ai-generate-faq" data-post-id="<?php echo $post_id; ?>">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e('Generate FAQ', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button accent hmg-ai-generate-toc" data-post-id="<?php echo $post_id; ?>">
                <span class="dashicons dashicons-editor-ol"></span>
                <?php _e('Generate Table of Contents', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button hmg-ai-generate-audio" data-post-id="<?php echo $post_id; ?>">
                <span class="dashicons dashicons-controls-volumeon"></span>
                <?php _e('Generate Audio Version', 'hmg-ai-blog-enhancer'); ?>
            </button>
        </div>

        <!-- Quick Settings -->
        <div class="hmg-ai-quick-settings">
            <h4><?php _e('Quick Settings', 'hmg-ai-blog-enhancer'); ?></h4>
            
            <label>
                <input type="checkbox" name="hmg_ai_auto_takeaways" value="1" <?php checked(get_post_meta($post_id, '_hmg_ai_auto_takeaways', true), '1'); ?>>
                <?php _e('Auto-generate takeaways on publish', 'hmg-ai-blog-enhancer'); ?>
            </label>
            
            <label>
                <input type="checkbox" name="hmg_ai_auto_faq" value="1" <?php checked(get_post_meta($post_id, '_hmg_ai_auto_faq', true), '1'); ?>>
                <?php _e('Auto-generate FAQ on publish', 'hmg-ai-blog-enhancer'); ?>
            </label>
            
            <label>
                <input type="checkbox" name="hmg_ai_auto_toc" value="1" <?php checked(get_post_meta($post_id, '_hmg_ai_auto_toc', true), '1'); ?>>
                <?php _e('Auto-generate TOC on publish', 'hmg-ai-blog-enhancer'); ?>
            </label>
        </div>

        <!-- Generated Content Preview -->
        <?php
        $generated_takeaways = get_post_meta($post_id, '_hmg_ai_takeaways', true);
        $generated_faq = get_post_meta($post_id, '_hmg_ai_faq', true);
        $generated_toc = get_post_meta($post_id, '_hmg_ai_toc', true);
        $generated_audio = get_post_meta($post_id, '_hmg_ai_audio_url', true);
        
        if ($generated_takeaways || $generated_faq || $generated_toc || $generated_audio): ?>
            <div class="hmg-ai-generated-content">
                <h4><?php _e('Generated Content', 'hmg-ai-blog-enhancer'); ?></h4>
                
                <?php if ($generated_takeaways): ?>
                    <div class="hmg-ai-content-item">
                        <strong><?php _e('Key Takeaways:', 'hmg-ai-blog-enhancer'); ?></strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        <button type="button" class="button-link hmg-ai-regenerate" data-type="takeaways" data-post-id="<?php echo $post_id; ?>">
                            <?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_faq): ?>
                    <div class="hmg-ai-content-item">
                        <strong><?php _e('FAQ:', 'hmg-ai-blog-enhancer'); ?></strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        <button type="button" class="button-link hmg-ai-regenerate" data-type="faq" data-post-id="<?php echo $post_id; ?>">
                            <?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_toc): ?>
                    <div class="hmg-ai-content-item">
                        <strong><?php _e('Table of Contents:', 'hmg-ai-blog-enhancer'); ?></strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        <button type="button" class="button-link hmg-ai-regenerate" data-type="toc" data-post-id="<?php echo $post_id; ?>">
                            <?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_audio): ?>
                    <div class="hmg-ai-content-item">
                        <strong><?php _e('Audio Version:', 'hmg-ai-blog-enhancer'); ?></strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        <audio controls class="hmg-ai-audio-player" src="<?php echo esc_url($generated_audio); ?>" style="width: 100%; margin-top: 10px;"></audio>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Help Section -->
        <div class="hmg-ai-help-section">
            <h4><?php _e('Tips for Better Results', 'hmg-ai-blog-enhancer'); ?></h4>
            <ul>
                <li><?php _e('Write clear, well-structured content with proper headings', 'hmg-ai-blog-enhancer'); ?></li>
                <li><?php _e('Include specific details and examples in your content', 'hmg-ai-blog-enhancer'); ?></li>
                <li><?php _e('Use descriptive subheadings for better TOC generation', 'hmg-ai-blog-enhancer'); ?></li>
                <li><?php _e('Longer content (500+ words) produces better results', 'hmg-ai-blog-enhancer'); ?></li>
            </ul>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>" class="button-secondary">
                    <?php _e('View Documentation', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </p>
        </div>

    <?php endif; ?>
</div>

<style>
/* Meta Box Specific Styles */
.hmg-ai-meta-box {
    font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.hmg-ai-usage-section {
    margin-bottom: 15px;
}

.hmg-ai-usage-section label {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
    color: var(--hmg-dark-gray, #343A40);
}

.hmg-ai-quick-settings label {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13px;
}

.hmg-ai-quick-settings input[type="checkbox"] {
    margin-right: 8px;
}

.hmg-ai-content-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #E1E5E9;
}

.hmg-ai-content-item:last-child {
    border-bottom: none;
}

.hmg-ai-help-section ul {
    font-size: 13px;
    color: var(--hmg-medium-gray, #6C757D);
}

.hmg-ai-help-section li {
    margin-bottom: 5px;
}

.hmg-ai-reset-info {
    text-align: center;
    margin-top: 10px;
    margin-bottom: 0;
}
</style> 