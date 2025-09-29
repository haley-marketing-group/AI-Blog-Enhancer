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

// Get authentication status
$auth_service = new HMG_AI_Auth_Service();
$auth_status = $auth_service->get_auth_status();

// Check which API keys are configured
// AI provider keys are stored in the options array
$gemini_key = $options['gemini_api_key'] ?? '';
$openai_key = $options['openai_api_key'] ?? '';
$claude_key = $options['claude_api_key'] ?? '';
// Eleven Labs key is stored separately
$elevenlabs_key = get_option('hmg_ai_elevenlabs_api_key', '');

// Check if any AI provider is configured for content generation
$has_ai_provider = !empty($gemini_key) || !empty($openai_key) || !empty($claude_key);
$has_audio_provider = !empty($elevenlabs_key);
?>

<div class="hmg-ai-meta-box">
    <div class="hmg-ai-notices"></div>
    
    <?php if (!$auth_status['authenticated']): ?>
        <div class="hmg-ai-notice warning">
            <p>
                <strong><?php _e('API Configuration Required', 'hmg-ai-blog-enhancer'); ?></strong><br>
                <?php echo esc_html($auth_status['message']); ?>
            </p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>" class="button button-primary">
                    <?php _e('Configure API Keys', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        
        <!-- Authentication Status -->
        <div class="hmg-ai-auth-status">
            <div class="hmg-ai-auth-indicator">
                <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green, #5E9732);"></span>
                <strong><?php _e('Connected', 'hmg-ai-blog-enhancer'); ?></strong>
            </div>
            <div class="hmg-ai-auth-providers">
                <?php if (!empty($auth_status['providers'])): ?>
                    <small><?php echo esc_html(implode(', ', $auth_status['providers'])); ?></small>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Usage Meter -->
        <?php $spending_stats = $auth_status['spending_stats'] ?? array(); ?>
        <div class="hmg-ai-usage-meter">
            <h4><?php _e('Usage This Month', 'hmg-ai-blog-enhancer'); ?></h4>
            
            <?php if (!empty($spending_stats['monthly'])): ?>
                <div class="hmg-ai-usage-section">
                    <label><?php _e('Spending', 'hmg-ai-blog-enhancer'); ?></label>
                    <div class="hmg-ai-usage-bar">
                        <div class="hmg-ai-usage-fill spending" data-width="<?php echo min(100, $spending_stats['monthly']['percentage']); ?>"></div>
                    </div>
                    <div class="hmg-ai-usage-stats">
                        <span class="spending-used">$<?php 
                            $spent = $spending_stats['monthly']['spent'];
                            echo $spent < 0.01 ? number_format($spent, 4) : ($spent < 1 ? number_format($spent, 3) : number_format($spent, 2));
                        ?></span> / 
                        <span class="spending-limit">$<?php 
                            $limit = $spending_stats['monthly']['limit'];
                            echo $limit < 0.01 ? number_format($limit, 4) : ($limit < 1 ? number_format($limit, 3) : number_format($limit, 2));
                        ?></span>
                        <span class="spending-percentage">(<?php echo number_format($spending_stats['monthly']['percentage'], 1); ?>%)</span>
                    </div>
                </div>
                
                <div class="hmg-ai-usage-section">
                    <label><?php _e('API Calls', 'hmg-ai-blog-enhancer'); ?></label>
                    <div class="hmg-ai-usage-bar">
                        <div class="hmg-ai-usage-fill api-calls" data-width="<?php echo min(100, ($spending_stats['monthly']['requests'] / max(1, $spending_stats['monthly']['requests'] + 100)) * 100); ?>"></div>
                    </div>
                    <div class="hmg-ai-usage-stats">
                        <span class="api-calls-used"><?php echo number_format($spending_stats['monthly']['requests']); ?></span> calls
                    </div>
                </div>
                
                <?php if (!empty($spending_stats['monthly']['tokens'])): ?>
                    <div class="hmg-ai-usage-section">
                        <label><?php _e('Tokens Used', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="hmg-ai-usage-bar">
                            <div class="hmg-ai-usage-fill tokens" data-width="<?php echo min(100, ($spending_stats['monthly']['tokens'] / max(1000000, $spending_stats['monthly']['tokens'])) * 100); ?>"></div>
                        </div>
                        <div class="hmg-ai-usage-stats">
                            <span class="tokens-used"><?php echo number_format($spending_stats['monthly']['tokens']); ?></span> tokens
                        </div>
                    </div>
                <?php endif; ?>
                
                <p class="hmg-ai-reset-info">
                    <small><?php _e('Resets on:', 'hmg-ai-blog-enhancer'); ?> <span class="hmg-ai-reset-date"><?php echo esc_html($spending_stats['reset_date'] ?? 'Unknown'); ?></span></small>
                </p>
            <?php else: ?>
                <div class="hmg-ai-usage-section">
                    <p class="hmg-ai-no-usage"><?php _e('No usage data available yet. Generate some content to see statistics!', 'hmg-ai-blog-enhancer'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content Generation Controls -->
        <div class="hmg-ai-generation-controls">
            <h4><?php _e('Generate AI Content', 'hmg-ai-blog-enhancer'); ?></h4>
            
            <button type="button" class="hmg-ai-button hmg-ai-generate-takeaways" data-post-id="<?php echo $post_id; ?>" 
                    <?php echo !$has_ai_provider ? 'disabled' : ''; ?>
                    title="<?php echo !$has_ai_provider ? esc_attr__('Please configure an AI API key (Gemini, OpenAI, or Claude) in Settings', 'hmg-ai-blog-enhancer') : ''; ?>">
                <span class="dashicons dashicons-list-view"></span>
                <?php _e('Generate Key Takeaways', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button secondary hmg-ai-generate-faq" data-post-id="<?php echo $post_id; ?>"
                    <?php echo !$has_ai_provider ? 'disabled' : ''; ?>
                    title="<?php echo !$has_ai_provider ? esc_attr__('Please configure an AI API key (Gemini, OpenAI, or Claude) in Settings', 'hmg-ai-blog-enhancer') : ''; ?>">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e('Generate FAQ', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button accent hmg-ai-generate-toc" data-post-id="<?php echo $post_id; ?>"
                    <?php echo !$has_ai_provider ? 'disabled' : ''; ?>
                    title="<?php echo !$has_ai_provider ? esc_attr__('Please configure an AI API key (Gemini, OpenAI, or Claude) in Settings', 'hmg-ai-blog-enhancer') : ''; ?>">
                <span class="dashicons dashicons-editor-ol"></span>
                <?php _e('Generate Table of Contents', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <button type="button" class="hmg-ai-button hmg-ai-generate-audio" data-post-id="<?php echo $post_id; ?>"
                    <?php echo !$has_audio_provider ? 'disabled' : ''; ?>
                    title="<?php echo !$has_audio_provider ? esc_attr__('Please configure Eleven Labs API key in Settings', 'hmg-ai-blog-enhancer') : ''; ?>">
                <span class="dashicons dashicons-controls-volumeon"></span>
                <?php _e('Generate Audio Version', 'hmg-ai-blog-enhancer'); ?>
            </button>
            
            <?php if (!$has_ai_provider || !$has_audio_provider): ?>
            <div class="hmg-ai-notice info" style="margin-top: 15px; padding: 10px; font-size: 12px;">
                <?php if (!$has_ai_provider): ?>
                    <p style="margin: 5px 0;">
                        <span class="dashicons dashicons-info" style="color: #0073aa;"></span>
                        <?php _e('Configure an AI provider (Gemini, OpenAI, or Claude) to generate content.', 'hmg-ai-blog-enhancer'); ?>
                        <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>"><?php _e('Go to Settings →', 'hmg-ai-blog-enhancer'); ?></a>
                    </p>
                <?php endif; ?>
                <?php if (!$has_audio_provider): ?>
                    <p style="margin: 5px 0;">
                        <span class="dashicons dashicons-controls-volumeon" style="color: #0073aa;"></span>
                        <?php _e('Configure Eleven Labs API key to generate audio.', 'hmg-ai-blog-enhancer'); ?>
                        <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>"><?php _e('Go to Settings →', 'hmg-ai-blog-enhancer'); ?></a>
                    </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Audio Settings -->
            <div class="hmg-ai-audio-settings">
                <div class="hmg-ai-form-group">
                    <label for="hmg-ai-audio-voice" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">
                        <?php _e('Voice:', 'hmg-ai-blog-enhancer'); ?>
                        <button type="button" id="hmg-ai-refresh-voices" class="button-link" style="float: right; font-size: 12px; font-weight: normal; display: inline-flex; align-items: center; gap: 4px;" title="<?php _e('Refresh voice list from Eleven Labs', 'hmg-ai-blog-enhancer'); ?>">
                            <span class="dashicons dashicons-update" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span>
                            <span style="line-height: 1;"><?php _e('Refresh', 'hmg-ai-blog-enhancer'); ?></span>
                        </button>
                    </label>
                    <select id="hmg-ai-audio-voice" class="hmg-ai-select hmg-ai-compact" style="width: 100%;">
                        <?php
                        // Get TTS service to fetch available voices
                        if (class_exists('HMG_AI_TTS_Service')) {
                            $tts_service = new HMG_AI_TTS_Service();
                            $voices = $tts_service->get_available_voices();
                            $saved_voice = get_option('hmg_ai_tts_voice', 'EXAVITQu4vr4xnSDxMaL');
                            
                            if (!empty($voices)) {
                                // Group voices by category if available
                                $premade_voices = array();
                                $cloned_voices = array();
                                
                                foreach ($voices as $voice_id => $voice_data) {
                                    $category = isset($voice_data['category']) ? $voice_data['category'] : 'premade';
                                    if ($category === 'cloned') {
                                        $cloned_voices[$voice_id] = $voice_data;
                                    } else {
                                        $premade_voices[$voice_id] = $voice_data;
                                    }
                                }
                                
                                // Display premade voices first
                                if (!empty($premade_voices)) {
                                    echo '<optgroup label="' . esc_attr__('Eleven Labs Voices', 'hmg-ai-blog-enhancer') . '">';
                                    foreach ($premade_voices as $voice_id => $voice_data) {
                                        $selected = ($voice_id == $saved_voice) ? ' selected' : '';
                                        $name = esc_html($voice_data['name']);
                                        $description = isset($voice_data['description']) ? ' - ' . esc_html($voice_data['description']) : '';
                                        echo '<option value="' . esc_attr($voice_id) . '"' . $selected . '>' . $name . $description . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                
                                // Display cloned voices if any
                                if (!empty($cloned_voices)) {
                                    echo '<optgroup label="' . esc_attr__('Custom/Cloned Voices', 'hmg-ai-blog-enhancer') . '">';
                                    foreach ($cloned_voices as $voice_id => $voice_data) {
                                        $selected = ($voice_id == $saved_voice) ? ' selected' : '';
                                        $name = esc_html($voice_data['name']);
                                        $description = isset($voice_data['description']) ? ' - ' . esc_html($voice_data['description']) : '';
                                        echo '<option value="' . esc_attr($voice_id) . '"' . $selected . '>' . $name . $description . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            } else {
                                // Fallback to basic voices if API fetch fails
                                ?>
                                <option value="EXAVITQu4vr4xnSDxMaL">Sarah - Professional Female</option>
                                <option value="21m00Tcm4TlvDq8ikWAM">Rachel - News Narrator</option>
                                <option value="JBFqnCBsd6RMkjVDRZzb">George - Professional Male</option>
                                <option value="pNInz6obpgDQGcFmaJgB">Adam - Conversational Male</option>
                                <?php
                            }
                        } else {
                            // If TTS service not available, show basic voices
                            ?>
                            <option value="EXAVITQu4vr4xnSDxMaL">Sarah - Professional Female</option>
                            <option value="21m00Tcm4TlvDq8ikWAM">Rachel - News Narrator</option>
                            <option value="JBFqnCBsd6RMkjVDRZzb">George - Professional Male</option>
                            <option value="pNInz6obpgDQGcFmaJgB">Adam - Conversational Male</option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                
                <span class="hmg-ai-audio-info">
                    <span class="dashicons dashicons-info-outline"></span>
                    <?php _e('Powered by Eleven Labs - Natural AI voices', 'hmg-ai-blog-enhancer'); ?>
                </span>
                
                <div style="margin-top: 8px; padding: 8px; background: rgba(0, 115, 170, 0.05); border-left: 3px solid #0073aa; border-radius: 3px;">
                    <small style="color: #555;">
                        <strong><?php _e('Audio Preview:', 'hmg-ai-blog-enhancer'); ?></strong>
                        <?php _e('Audio will read the post as it appears in preview, including any inserted shortcodes (Key Takeaways, FAQ, TOC)', 'hmg-ai-blog-enhancer'); ?>
                    </small>
                </div>
            </div>
        </div>
        <br />
        <!-- AI Provider Selection -->
        <?php if ($has_ai_provider): ?>
        <div class="hmg-ai-provider-selection">
            <h4><?php _e('AI Provider', 'hmg-ai-blog-enhancer'); ?></h4>
            <select id="hmg-ai-provider-select" class="hmg-ai-select" style="width: 100%; margin-bottom: 10px;">
                <option value="auto"><?php _e('Auto (Use priority settings)', 'hmg-ai-blog-enhancer'); ?></option>
                <?php if (!empty($gemini_key)): ?>
                    <option value="gemini"><?php _e('Google Gemini', 'hmg-ai-blog-enhancer'); ?></option>
                <?php endif; ?>
                <?php if (!empty($openai_key)): ?>
                    <option value="openai"><?php _e('OpenAI GPT', 'hmg-ai-blog-enhancer'); ?></option>
                <?php endif; ?>
                <?php if (!empty($claude_key)): ?>
                    <option value="claude"><?php _e('Anthropic Claude', 'hmg-ai-blog-enhancer'); ?></option>
                <?php endif; ?>
            </select>
            <p class="description" style="margin-top: 5px; font-size: 11px;">
                <?php _e('Select which AI provider to use for content generation', 'hmg-ai-blog-enhancer'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Insert Summarize Buttons -->
        <div class="hmg-ai-shortcodes-section">
            <h4>
                <span class="dashicons dashicons-admin-links"></span>
                <?php _e('Summarize Buttons', 'hmg-ai-blog-enhancer'); ?>
            </h4>
            <div class="hmg-ai-summarize-options">
                <button type="button" class="button button-primary hmg-ai-insert-shortcode" data-shortcode="[hmg_ai_summarize]" title="Insert [hmg_ai_summarize] shortcode">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php _e('Insert Summarize Buttons', 'hmg-ai-blog-enhancer'); ?>
                </button>
            </div>
        </div>
        
        <!-- Quick Settings -->
        <div class="hmg-ai-quick-settings">
            <h4><?php _e('Quick Settings', 'hmg-ai-blog-enhancer'); ?></h4>
            <br />
            
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
        <br />
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
                        <div class="hmg-ai-content-header">
                            <strong><?php _e('Key Takeaways', 'hmg-ai-blog-enhancer'); ?></strong>
                            <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        </div>
                        <div class="hmg-ai-content-preview" id="takeaways-preview">
                            <?php 
                                // Format preview properly
                                $preview_text = '';
                                if (is_array($generated_takeaways)) {
                                    $preview_text = implode(' • ', array_slice($generated_takeaways, 0, 2));
                                } elseif (is_string($generated_takeaways)) {
                                    $decoded = json_decode($generated_takeaways, true);
                                    if ($decoded !== null && is_array($decoded)) {
                                        $preview_text = implode(' • ', array_slice($decoded, 0, 2));
                                    } else {
                                        $preview_text = strip_tags($generated_takeaways);
                                    }
                                } else {
                                    $preview_text = $generated_takeaways;
                                }
                                echo wp_kses_post(substr($preview_text, 0, 100) . '...'); 
                            ?>
                        </div>
                        <div class="hmg-ai-content-actions">
                            <button type="button" class="button-link hmg-ai-edit-content" data-type="takeaways" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Edit', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-regenerate" data-type="takeaways" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="takeaways" title="<?php _e('Insert Shortcode', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-shortcode"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-delete-content" data-type="takeaways" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Delete', 'hmg-ai-blog-enhancer'); ?>" style="color: #d63638;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                        <div class="hmg-ai-content-notice" id="takeaways-notice"></div>
                        <div class="hmg-ai-content-editor" id="takeaways-editor" style="display: none;">
                            <textarea rows="6" style="width: 100%;" id="takeaways-content"><?php 
                                // Properly format takeaways for editing
                                if (is_array($generated_takeaways)) {
                                    echo esc_textarea(implode("\n", $generated_takeaways));
                                } elseif (is_string($generated_takeaways)) {
                                    // Try to decode JSON
                                    $decoded = json_decode($generated_takeaways, true);
                                    if ($decoded !== null && is_array($decoded)) {
                                        echo esc_textarea(implode("\n", $decoded));
                                    } else {
                                        // Plain text - just output as is
                                        echo esc_textarea($generated_takeaways);
                                    }
                                } else {
                                    echo esc_textarea($generated_takeaways);
                                }
                            ?></textarea>
                            <div class="hmg-ai-editor-actions">
                                <button type="button" class="button button-primary hmg-ai-save-content" data-type="takeaways" data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('Save', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                                <button type="button" class="button hmg-ai-cancel-edit" data-type="takeaways">
                                    <?php _e('Cancel', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_faq): ?>
                    <div class="hmg-ai-content-item">
                        <div class="hmg-ai-content-header">
                            <strong><?php _e('FAQ', 'hmg-ai-blog-enhancer'); ?></strong>
                            <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        </div>
                        <div class="hmg-ai-content-preview" id="faq-preview">
                            <?php echo wp_kses_post(substr(strip_tags($generated_faq), 0, 100) . '...'); ?>
                        </div>
                        <div class="hmg-ai-content-actions">
                            <button type="button" class="button-link hmg-ai-edit-content" data-type="faq" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Edit', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-regenerate" data-type="faq" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="faq" title="<?php _e('Insert Shortcode', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-shortcode"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-delete-content" data-type="faq" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Delete', 'hmg-ai-blog-enhancer'); ?>" style="color: #d63638;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                        <div class="hmg-ai-content-notice" id="faq-notice"></div>
                        <div class="hmg-ai-content-editor" id="faq-editor" style="display: none;">
                            <textarea rows="8" style="width: 100%;" id="faq-content"><?php echo esc_textarea($generated_faq); ?></textarea>
                            <div class="hmg-ai-editor-actions">
                                <button type="button" class="button button-primary hmg-ai-save-content" data-type="faq" data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('Save', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                                <button type="button" class="button hmg-ai-cancel-edit" data-type="faq">
                                    <?php _e('Cancel', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_toc): ?>
                    <div class="hmg-ai-content-item">
                        <div class="hmg-ai-content-header">
                            <strong><?php _e('Table of Contents', 'hmg-ai-blog-enhancer'); ?></strong>
                            <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        </div>
                        <div class="hmg-ai-content-preview" id="toc-preview">
                            <?php echo wp_kses_post(substr(strip_tags($generated_toc), 0, 100) . '...'); ?>
                        </div>
                        <div class="hmg-ai-content-actions">
                            <button type="button" class="button-link hmg-ai-edit-content" data-type="toc" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Edit', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-regenerate" data-type="toc" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Regenerate', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="toc" title="<?php _e('Insert Shortcode', 'hmg-ai-blog-enhancer'); ?>">
                                <span class="dashicons dashicons-shortcode"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-delete-content" data-type="toc" data-post-id="<?php echo $post_id; ?>" title="<?php _e('Delete', 'hmg-ai-blog-enhancer'); ?>" style="color: #d63638;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                        <div class="hmg-ai-content-notice" id="toc-notice"></div>
                        <div class="hmg-ai-content-editor" id="toc-editor" style="display: none;">
                            <textarea rows="6" style="width: 100%;" id="toc-content"><?php echo esc_textarea($generated_toc); ?></textarea>
                            <div class="hmg-ai-editor-actions">
                                <button type="button" class="button button-primary hmg-ai-save-content" data-type="toc" data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('Save', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                                <button type="button" class="button hmg-ai-cancel-edit" data-type="toc">
                                    <?php _e('Cancel', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($generated_audio): ?>
                    <div class="hmg-ai-content-item" id="audio-item">
                        <div class="hmg-ai-content-header">
                            <strong><?php _e('Audio Version', 'hmg-ai-blog-enhancer'); ?></strong>
                            <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                        </div>
                        <div class="hmg-ai-audio-player-wrapper">
                            <audio controls class="hmg-ai-audio-player" style="width: 100%;">
                                <source src="<?php echo esc_url($generated_audio); ?>" type="audio/mpeg">
                                <?php _e('Your browser does not support the audio element.', 'hmg-ai-blog-enhancer'); ?>
                            </audio>
                            <div class="hmg-ai-audio-info">
                                <span class="hmg-ai-audio-voice">🎙️ Eleven Labs Audio</span>
                            </div>
                        </div>
                        <div class="hmg-ai-content-actions">
                            <button type="button" class="button-link hmg-ai-regenerate" data-type="audio" data-post-id="<?php echo $post_id; ?>" title="Regenerate">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="audio" title="Insert Shortcode">
                                <span class="dashicons dashicons-shortcode"></span>
                            </button>
                            <a href="<?php echo esc_url($generated_audio); ?>" download class="button-link" title="Download">
                                <span class="dashicons dashicons-download"></span>
                            </a>
                            <button type="button" class="button-link hmg-ai-delete-content" data-type="audio" data-post-id="<?php echo $post_id; ?>" title="Delete">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                        <div class="hmg-ai-content-notice" id="audio-notice"></div>
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

.hmg-ai-auth-status {
    background: #E8F5E8;
    border-left: 4px solid var(--hmg-lime-green, #5E9732);
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.hmg-ai-auth-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.hmg-ai-auth-providers {
    color: var(--hmg-medium-gray, #6C757D);
    font-size: 12px;
}

.hmg-ai-no-usage {
    color: var(--hmg-medium-gray, #6C757D);
    font-style: italic;
    text-align: center;
    padding: 20px;
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
    padding: 12px 0;
    border-bottom: 1px solid #E1E5E9;
}

.hmg-ai-content-item:last-child {
    border-bottom: none;
}

.hmg-ai-content-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.hmg-ai-content-actions {
    display: flex;
    gap: 8px;
}

.hmg-ai-content-preview {
    background: #f9f9f9;
    padding: 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}

.hmg-ai-content-editor {
    margin-top: 8px;
}

.hmg-ai-editor-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
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