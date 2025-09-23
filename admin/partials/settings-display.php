<?php
/**
 * Provide a admin area view for the settings page
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

// Get authentication service
$auth_service = new HMG_AI_Auth_Service();
$auth_status = $auth_service->get_auth_status();
$current_api_key = $auth_service->get_stored_api_key();
$options = get_option('hmg_ai_blog_enhancer_options', array());

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['hmg_ai_settings_nonce'], 'hmg_ai_settings')) {
    $new_options = array();
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('HMG AI Settings: Form submitted with POST data: ' . print_r($_POST, true));
    }
    
    // API Key
    $new_options['api_key'] = sanitize_text_field($_POST['api_key'] ?? '');
    
    // AI Provider settings
    $new_options['gemini_api_key'] = sanitize_text_field($_POST['gemini_api_key'] ?? '');
    $new_options['openai_api_key'] = sanitize_text_field($_POST['openai_api_key'] ?? '');
    $new_options['claude_api_key'] = sanitize_text_field($_POST['claude_api_key'] ?? '');
    $new_options['gemini_enabled'] = isset($_POST['gemini_enabled']);
    $new_options['openai_enabled'] = isset($_POST['openai_enabled']);
    $new_options['claude_enabled'] = isset($_POST['claude_enabled']);
    $new_options['gemini_priority'] = (int) ($_POST['gemini_priority'] ?? 1);
    $new_options['openai_priority'] = (int) ($_POST['openai_priority'] ?? 2);
    $new_options['claude_priority'] = (int) ($_POST['claude_priority'] ?? 3);
    
    // Model selections
    $new_options['gemini_model'] = sanitize_text_field($_POST['gemini_model'] ?? 'gemini-1.5-flash');
    $new_options['openai_model'] = sanitize_text_field($_POST['openai_model'] ?? 'gpt-3.5-turbo');
    $new_options['claude_model'] = sanitize_text_field($_POST['claude_model'] ?? 'claude-3-haiku-20240307');
    
    // Spending limit settings
    $new_options['spending_limit_type'] = sanitize_text_field($_POST['spending_limit_type'] ?? 'moderate');
    $new_options['custom_monthly_limit'] = (float) ($_POST['custom_monthly_limit'] ?? 15.00);
    $new_options['warning_threshold'] = ((float) ($_POST['warning_threshold'] ?? 80)) / 100; // Convert percentage to decimal
    
    // Auto-generation settings
    $new_options['auto_generate_takeaways'] = isset($_POST['auto_generate_takeaways']);
    $new_options['auto_generate_faq'] = isset($_POST['auto_generate_faq']);
    $new_options['auto_generate_toc'] = isset($_POST['auto_generate_toc']);
    $new_options['enable_audio_conversion'] = isset($_POST['enable_audio_conversion']);
    
    // Cache settings
    $new_options['cache_enabled'] = isset($_POST['cache_enabled']);
    $new_options['cache_duration'] = (int) ($_POST['cache_duration'] ?? 3600);
    
    // Usage tracking
    $new_options['usage_tracking'] = isset($_POST['usage_tracking']);
    
    // Preserve existing brand colors and typography
    $new_options['brand_colors'] = $options['brand_colors'] ?? array(
        'primary' => '#332A86',
        'secondary' => '#5E9732',
        'accent' => '#E36F1E'
    );
    $new_options['typography'] = $options['typography'] ?? array(
        'heading_font' => 'Museo Slab',
        'body_font' => 'Roboto'
    );
    
    // Update options
    $update_result = update_option('hmg_ai_blog_enhancer_options', $new_options);
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('HMG AI Settings: Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED'));
        error_log('HMG AI Settings: New options: ' . print_r($new_options, true));
        error_log('HMG AI Settings: Stored options: ' . print_r(get_option('hmg_ai_blog_enhancer_options'), true));
    }
    
    // Clear auth cache if API key changed
    if ($new_options['api_key'] !== $current_api_key) {
        $auth_service->clear_auth_cache();
    }
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'hmg-ai-blog-enhancer') . '</p></div>';
    
    // Refresh auth status and options
    $auth_status = $auth_service->get_auth_status();
    $options = $new_options;
}
?>

<div class="hmg-ai-admin-wrap">
    <div class="hmg-ai-header">
        <h1><?php _e('Settings', 'hmg-ai-blog-enhancer'); ?></h1>
        <p><?php _e('Configure your HMG AI Blog Enhancer settings and API authentication', 'hmg-ai-blog-enhancer'); ?></p>
    </div>

    <div class="hmg-ai-notices"></div>

    <form method="post" action="" class="hmg-ai-settings-form">
        <?php wp_nonce_field('hmg_ai_settings', 'hmg_ai_settings_nonce'); ?>

        <!-- HMG API Key Configuration -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🔑 HMG AI Authentication', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label for="api_key"><?php _e('HMG AI API Key', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" 
                           id="api_key" 
                           name="api_key" 
                           value="<?php echo esc_attr($options['api_key'] ?? ''); ?>" 
                           placeholder="<?php _e('Enter your HMG AI API key (optional)...', 'hmg-ai-blog-enhancer'); ?>" 
                           style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        <?php _e('Optional: HMG AI API key for enhanced features. You can also use individual AI provider keys below.', 'hmg-ai-blog-enhancer'); ?>
                        <br>
                        <a href="https://haleymarketing.com/ai-api" target="_blank"><?php _e('Get your HMG AI API key', 'hmg-ai-blog-enhancer'); ?></a>
                    </p>
                </div>

                <?php if (!empty($options['api_key'])): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #E8F5E8; border-left: 4px solid #5E9732; border-radius: 4px;">
                        <h4 style="margin-top: 0; color: #5E9732;"><?php _e('✅ HMG AI Key Configured', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><?php _e('Your HMG AI API key is configured and ready to use.', 'hmg-ai-blog-enhancer'); ?></p>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 15px; padding: 15px; background: #FFF3CD; border-left: 4px solid #E36F1E; border-radius: 4px;">
                        <h4 style="margin-top: 0; color: #E36F1E;"><?php _e('⚠️ No HMG AI Key', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><?php _e('You can use individual AI provider keys below, or get an HMG AI key for enhanced features and unified billing.', 'hmg-ai-blog-enhancer'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Spending Limits Configuration -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('💰 Spending Limits', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <p class="description" style="margin-bottom: 20px;">
                    <?php _e('Set your monthly spending limit to control AI generation costs. We\'ll track your usage and warn you when approaching your limit.', 'hmg-ai-blog-enhancer'); ?>
                </p>

                <?php 
                $spending_limit_options = array(
                    'conservative' => array(
                        'name' => 'Conservative ($5/month)',
                        'description' => 'Perfect for occasional content generation (~20 posts/month)'
                    ),
                    'moderate' => array(
                        'name' => 'Moderate ($15/month)', 
                        'description' => 'Good for regular blog posting (~60 posts/month)'
                    ),
                    'active' => array(
                        'name' => 'Active ($30/month)',
                        'description' => 'Ideal for frequent content creation (~120 posts/month)'
                    ),
                    'professional' => array(
                        'name' => 'Professional ($75/month)',
                        'description' => 'For high-volume content production (~300 posts/month)'
                    ),
                    'custom' => array(
                        'name' => 'Custom Amount',
                        'description' => 'Set your own spending limit'
                    )
                );
                
                $current_limit_type = $options['spending_limit_type'] ?? 'moderate';
                ?>

                <div class="hmg-ai-form-group">
                    <label><?php _e('Monthly Spending Limit', 'hmg-ai-blog-enhancer'); ?></label>
                    <?php foreach ($spending_limit_options as $key => $limit_option): ?>
                        <div style="margin-bottom: 10px;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="radio" 
                                       name="spending_limit_type" 
                                       value="<?php echo esc_attr($key); ?>"
                                       <?php checked($current_limit_type, $key); ?> />
                                <strong><?php echo esc_html($limit_option['name']); ?></strong>
                                <span style="color: #666;">- <?php echo esc_html($limit_option['description']); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="hmg-ai-form-group" id="custom-limit-section" style="<?php echo $current_limit_type !== 'custom' ? 'display: none;' : ''; ?>">
                    <label for="custom_monthly_limit"><?php _e('Custom Monthly Limit ($)', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="number" 
                           id="custom_monthly_limit" 
                           name="custom_monthly_limit" 
                           value="<?php echo esc_attr($options['custom_monthly_limit'] ?? 15.00); ?>" 
                           min="1" 
                           max="1000" 
                           step="0.01"
                           style="width: 150px;" />
                    <p class="description"><?php _e('Enter your desired monthly spending limit in USD.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <div class="hmg-ai-form-group">
                    <label for="warning_threshold"><?php _e('Warning Threshold (%)', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="number" 
                           id="warning_threshold" 
                           name="warning_threshold" 
                           value="<?php echo esc_attr(($options['warning_threshold'] ?? 0.80) * 100); ?>" 
                           min="50" 
                           max="95" 
                           style="width: 100px;" />
                    <p class="description"><?php _e('Get warnings when you reach this percentage of your monthly limit.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <?php if ($auth_status['authenticated']): ?>
                    <?php $spending_stats = $auth_status['spending_stats'] ?? array(); ?>
                    <div style="margin-top: 20px; padding: 15px; background: #F0F9F0; border-left: 4px solid var(--hmg-lime-green); border-radius: 4px;">
                        <h4 style="margin-top: 0; color: var(--hmg-lime-green);"><?php _e('📊 Current Usage', 'hmg-ai-blog-enhancer'); ?></h4>
                        
                        <?php if (!empty($spending_stats['monthly'])): ?>
                            <p><strong><?php _e('This Month:', 'hmg-ai-blog-enhancer'); ?></strong> 
                               $<?php echo number_format($spending_stats['monthly']['spent'], 2); ?> / 
                               $<?php echo number_format($spending_stats['monthly']['limit'], 2); ?> 
                               (<?php echo number_format($spending_stats['monthly']['percentage'], 1); ?>%)
                            </p>
                            
                            <div style="background: #fff; border-radius: 4px; padding: 5px; margin: 10px 0;">
                                <div style="background: var(--hmg-lime-green); height: 8px; border-radius: 4px; width: <?php echo min(100, $spending_stats['monthly']['percentage']); ?>%;"></div>
                            </div>
                            
                            <p><small><?php _e('Resets on:', 'hmg-ai-blog-enhancer'); ?> <?php echo esc_html($spending_stats['reset_date'] ?? 'Unknown'); ?></small></p>
                        <?php else: ?>
                            <p><?php _e('No usage data available yet.', 'hmg-ai-blog-enhancer'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Auto-Generation Settings -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🤖 Auto-Generation Settings', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="auto_generate_takeaways" 
                               value="1" 
                               <?php checked($options['auto_generate_takeaways'] ?? false); ?> />
                        <?php _e('Auto-generate key takeaways when publishing posts', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Automatically create key takeaways for new posts.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="auto_generate_faq" 
                               value="1" 
                               <?php checked($options['auto_generate_faq'] ?? false); ?> />
                        <?php _e('Auto-generate FAQ sections when publishing posts', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Automatically create FAQ sections based on post content.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="auto_generate_toc" 
                               value="1" 
                               <?php checked($options['auto_generate_toc'] ?? true); ?> />
                        <?php _e('Auto-generate table of contents when publishing posts', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Automatically create table of contents from post headings.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="enable_audio_conversion" 
                               value="1" 
                               <?php checked($options['enable_audio_conversion'] ?? false); ?> />
                        <?php _e('Enable audio conversion features', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Allow conversion of posts to audio format (requires Pro tier or higher).', 'hmg-ai-blog-enhancer'); ?></p>
                </div>
            </div>
        </div>

        <!-- Performance Settings -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('⚡ Performance Settings', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="cache_enabled" 
                               value="1" 
                               <?php checked($options['cache_enabled'] ?? true); ?> />
                        <?php _e('Enable content caching', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Cache generated content to improve performance and reduce API usage.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>

                <div class="hmg-ai-form-group">
                    <label for="cache_duration"><?php _e('Cache Duration (seconds)', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="number" 
                           id="cache_duration" 
                           name="cache_duration" 
                           value="<?php echo esc_attr($options['cache_duration'] ?? 3600); ?>" 
                           min="300" 
                           max="86400" 
                           style="width: 150px;" />
                    <p class="description"><?php _e('How long to cache generated content (300 seconds minimum, 86400 maximum).', 'hmg-ai-blog-enhancer'); ?></p>
                </div>
            </div>
        </div>

        <!-- AI Provider Settings -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🤖 AI Provider Settings', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label for="gemini_api_key"><?php _e('Google Gemini API Key', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" 
                           id="gemini_api_key" 
                           name="gemini_api_key" 
                           value="<?php echo esc_attr($options['gemini_api_key'] ?? ''); ?>" 
                           placeholder="<?php _e('Enter your Gemini API key...', 'hmg-ai-blog-enhancer'); ?>" 
                           style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        <?php _e('Get your free API key from', 'hmg-ai-blog-enhancer'); ?> 
                        <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                    </p>
                </div>

                <div class="hmg-ai-form-group">
                    <label for="openai_api_key"><?php _e('OpenAI API Key', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" 
                           id="openai_api_key" 
                           name="openai_api_key" 
                           value="<?php echo esc_attr($options['openai_api_key'] ?? ''); ?>" 
                           placeholder="<?php _e('Enter your OpenAI API key...', 'hmg-ai-blog-enhancer'); ?>" 
                           style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        <?php _e('Get your API key from', 'hmg-ai-blog-enhancer'); ?> 
                        <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                    </p>
                </div>

                <div class="hmg-ai-form-group">
                    <label for="claude_api_key"><?php _e('Anthropic Claude API Key', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" 
                           id="claude_api_key" 
                           name="claude_api_key" 
                           value="<?php echo esc_attr($options['claude_api_key'] ?? ''); ?>" 
                           placeholder="<?php _e('Enter your Claude API key...', 'hmg-ai-blog-enhancer'); ?>" 
                           style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        <?php _e('Get your API key from', 'hmg-ai-blog-enhancer'); ?> 
                        <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <h4><?php _e('Google Gemini', 'hmg-ai-blog-enhancer'); ?></h4>
                        <div class="hmg-ai-form-group">
                            <label>
                                <input type="checkbox" 
                                       name="gemini_enabled" 
                                       value="1" 
                                       <?php checked($options['gemini_enabled'] ?? true); ?> />
                                <?php _e('Enable Gemini provider', 'hmg-ai-blog-enhancer'); ?>
                            </label>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="gemini_model"><?php _e('Model', 'hmg-ai-blog-enhancer'); ?></label>
                            <select id="gemini_model" name="gemini_model" style="width: 100%;">
                                <option value="gemini-2.5-flash" <?php selected($options['gemini_model'] ?? 'gemini-2.5-flash', 'gemini-2.5-flash'); ?>>
                                    Gemini 2.5 Flash (Recommended - $0.30/$2.50)
                                </option>
                                <option value="gemini-2.5-flash-lite" <?php selected($options['gemini_model'] ?? 'gemini-2.5-flash', 'gemini-2.5-flash-lite'); ?>>
                                    Gemini 2.5 Flash-Lite (Most Cost-Effective - $0.10/$0.40)
                                </option>
                                <option value="gemini-2.5-pro" <?php selected($options['gemini_model'] ?? 'gemini-2.5-flash', 'gemini-2.5-pro'); ?>>
                                    Gemini 2.5 Pro (Premium Thinking - $1.25/$10.00)
                                </option>
                                <option value="gemini-2.0-flash" <?php selected($options['gemini_model'] ?? 'gemini-2.5-flash', 'gemini-2.0-flash'); ?>>
                                    Gemini 2.0 Flash (Agent-Optimized - $0.10/$0.40)
                                </option>
                                <option value="gemini-1.5-flash" <?php selected($options['gemini_model'] ?? 'gemini-2.5-flash', 'gemini-1.5-flash'); ?>>
                                    Gemini 1.5 Flash (Legacy - $0.075/$0.30)
                                </option>
                            </select>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="gemini_priority"><?php _e('Priority (1 = highest)', 'hmg-ai-blog-enhancer'); ?></label>
                            <input type="number" 
                                   id="gemini_priority" 
                                   name="gemini_priority" 
                                   value="<?php echo esc_attr($options['gemini_priority'] ?? 1); ?>" 
                                   min="1" 
                                   max="10" 
                                   style="width: 80px;" />
                        </div>
                    </div>
                    <div>
                        <h4><?php _e('OpenAI GPT', 'hmg-ai-blog-enhancer'); ?></h4>
                        <div class="hmg-ai-form-group">
                            <label>
                                <input type="checkbox" 
                                       name="openai_enabled" 
                                       value="1" 
                                       <?php checked($options['openai_enabled'] ?? true); ?> />
                                <?php _e('Enable OpenAI provider', 'hmg-ai-blog-enhancer'); ?>
                            </label>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="openai_model"><?php _e('Model', 'hmg-ai-blog-enhancer'); ?></label>
                            <select id="openai_model" name="openai_model" style="width: 100%;">
                                <option value="o4-mini" <?php selected($options['openai_model'] ?? 'o4-mini', 'o4-mini'); ?>>
                                    o4-mini (Best Value Reasoning - $0.15/$0.60)
                                </option>
                                <option value="gpt-4o-mini" <?php selected($options['openai_model'] ?? 'o4-mini', 'gpt-4o-mini'); ?>>
                                    GPT-4o Mini (Fast & Affordable - $0.15/$0.60)
                                </option>
                                <option value="gpt-4.1" <?php selected($options['openai_model'] ?? 'o4-mini', 'gpt-4.1'); ?>>
                                    GPT-4.1 (Enhanced Coding - $2.00/$8.00)
                                </option>
                                <option value="gpt-4o" <?php selected($options['openai_model'] ?? 'o4-mini', 'gpt-4o'); ?>>
                                    GPT-4o (Multimodal Flagship - $2.50/$10.00)
                                </option>
                                <option value="gpt-4.5" <?php selected($options['openai_model'] ?? 'o4-mini', 'gpt-4.5'); ?>>
                                    GPT-4.5 (Creative & Premium - $75.00/$150.00)
                                </option>
                                <option value="gpt-3.5-turbo" <?php selected($options['openai_model'] ?? 'o4-mini', 'gpt-3.5-turbo'); ?>>
                                    GPT-3.5 Turbo (Legacy - $0.50/$1.50)
                                </option>
                            </select>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="openai_priority"><?php _e('Priority (1 = highest)', 'hmg-ai-blog-enhancer'); ?></label>
                            <input type="number" 
                                   id="openai_priority" 
                                   name="openai_priority" 
                                   value="<?php echo esc_attr($options['openai_priority'] ?? 2); ?>" 
                                   min="1" 
                                   max="10" 
                                   style="width: 80px;" />
                        </div>
                    </div>
                    <div>
                        <h4><?php _e('Anthropic Claude', 'hmg-ai-blog-enhancer'); ?></h4>
                        <div class="hmg-ai-form-group">
                            <label>
                                <input type="checkbox" 
                                       name="claude_enabled" 
                                       value="1" 
                                       <?php checked($options['claude_enabled'] ?? true); ?> />
                                <?php _e('Enable Claude provider', 'hmg-ai-blog-enhancer'); ?>
                            </label>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="claude_model"><?php _e('Model', 'hmg-ai-blog-enhancer'); ?></label>
                            <select id="claude_model" name="claude_model" style="width: 100%;">
                                <option value="claude-3-5-haiku-20241022" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-3-5-haiku-20241022'); ?>>
                                    Claude 3.5 Haiku (Fast & Affordable - $0.80/$4.00)
                                </option>
                                <option value="claude-sonnet-4-20250514" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-sonnet-4-20250514'); ?>>
                                    Claude Sonnet 4 (High Performance - $3.00/$15.00)
                                </option>
                                <option value="claude-3-7-sonnet-20250219" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-3-7-sonnet-20250219'); ?>>
                                    Claude 3.7 Sonnet (Extended Thinking - $3.00/$15.00)
                                </option>
                                <option value="claude-opus-4-20250514" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-opus-4-20250514'); ?>>
                                    Claude Opus 4 (Most Intelligent - $15.00/$75.00)
                                </option>
                                <option value="claude-3-5-sonnet-20241022" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-3-5-sonnet-20241022'); ?>>
                                    Claude 3.5 Sonnet (Legacy - $3.00/$15.00)
                                </option>
                                <option value="claude-3-haiku-20240307" <?php selected($options['claude_model'] ?? 'claude-3-5-haiku-20241022', 'claude-3-haiku-20240307'); ?>>
                                    Claude 3 Haiku (Legacy - $0.25/$1.25)
                                </option>
                            </select>
                        </div>
                        <div class="hmg-ai-form-group">
                            <label for="claude_priority"><?php _e('Priority (1 = highest)', 'hmg-ai-blog-enhancer'); ?></label>
                            <input type="number" 
                                   id="claude_priority" 
                                   name="claude_priority" 
                                   value="<?php echo esc_attr($options['claude_priority'] ?? 3); ?>" 
                                   min="1" 
                                   max="10" 
                                   style="width: 80px;" />
                        </div>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" class="hmg-ai-button hmg-ai-test-providers">
                        <?php _e('Test AI Providers', 'hmg-ai-blog-enhancer'); ?>
                    </button>
                </div>

                <!-- Model Comparison -->
                <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h4 style="margin-top: 0;"><?php _e('📊 Model Comparison', 'hmg-ai-blog-enhancer'); ?></h4>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: #e0e0e0;">
                                    <th style="padding: 8px; text-align: left; border: 1px solid #ccc;"><?php _e('Model (2025)', 'hmg-ai-blog-enhancer'); ?></th>
                                    <th style="padding: 8px; text-align: center; border: 1px solid #ccc;"><?php _e('Cost/1M Tokens', 'hmg-ai-blog-enhancer'); ?></th>
                                    <th style="padding: 8px; text-align: center; border: 1px solid #ccc;"><?php _e('Speed', 'hmg-ai-blog-enhancer'); ?></th>
                                    <th style="padding: 8px; text-align: center; border: 1px solid #ccc;"><?php _e('Quality', 'hmg-ai-blog-enhancer'); ?></th>
                                    <th style="padding: 8px; text-align: left; border: 1px solid #ccc;"><?php _e('Best For', 'hmg-ai-blog-enhancer'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Top Recommended Models -->
                                <tr style="background: #f0f9f0;">
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>🌟 Gemini 2.5 Flash-Lite</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$0.10/$0.40</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Most cost-effective</td>
                                </tr>
                                <tr style="background: #f0f9f0;">
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>🌟 o4-mini</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$0.15/$0.60</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Best reasoning value</td>
                                </tr>
                                <tr style="background: #f0f9f0;">
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>🌟 Gemini 2.5 Flash</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$0.30/$2.50</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Best balance quality/cost</td>
                                </tr>
                                <!-- Gemini Models -->
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>Gemini 2.5 Pro</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$1.25/$10.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Complex reasoning, coding</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>Gemini 2.0 Flash</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$0.10/$0.40</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Agent workflows</td>
                                </tr>
                                <!-- OpenAI Models -->
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>GPT-4.1</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$2.00/$8.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Enhanced coding</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>GPT-4o</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$2.50/$10.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Multimodal flagship</td>
                                </tr>
                                <!-- Claude Models -->
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>Claude 3.5 Haiku</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$0.80/$4.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Fast & reliable</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>Claude Sonnet 4</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$3.00/$15.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">High performance</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ccc;"><strong>Claude Opus 4</strong></td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">$15.00/$75.00</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 8px; border: 1px solid #ccc;">Most intelligent</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <p style="margin-top: 15px; font-size: 11px; color: #666;">
                        <?php _e('💡 2025 Tip: Start with the most cost-effective models (Gemini 2.5 Flash-Lite, o4-mini) for basic tasks. Use Gemini 2.5 Flash for the best balance of quality and cost. Upgrade to premium models (Claude Opus 4, GPT-4.1) only for complex reasoning and coding tasks.', 'hmg-ai-blog-enhancer'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🔒 Privacy Settings', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label>
                        <input type="checkbox" 
                               name="usage_tracking" 
                               value="1" 
                               <?php checked($options['usage_tracking'] ?? true); ?> />
                        <?php _e('Enable usage tracking', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    <p class="description"><?php _e('Track API usage for analytics and billing purposes. Required for usage limits and statistics.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>
            </div>
        </div>

        <!-- Brand Information -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🎨 Brand Information', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <h4><?php _e('Haley Marketing Colors', 'hmg-ai-blog-enhancer'); ?></h4>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: #332A86; border-radius: 4px;"></div>
                                <span><?php _e('Royal Blue (#332A86)', 'hmg-ai-blog-enhancer'); ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: #5E9732; border-radius: 4px;"></div>
                                <span><?php _e('Lime Green (#5E9732)', 'hmg-ai-blog-enhancer'); ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: #E36F1E; border-radius: 4px;"></div>
                                <span><?php _e('Orange (#E36F1E)', 'hmg-ai-blog-enhancer'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4><?php _e('Typography', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><strong><?php _e('Headings:', 'hmg-ai-blog-enhancer'); ?></strong> Museo Slab</p>
                        <p><strong><?php _e('Body Text:', 'hmg-ai-blog-enhancer'); ?></strong> Roboto</p>
                        <p><small><?php _e('These fonts are automatically loaded for AI-generated content.', 'hmg-ai-blog-enhancer'); ?></small></p>
                    </div>
                    <div>
                        <h4><?php _e('Design Principles', 'hmg-ai-blog-enhancer'); ?></h4>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li><?php _e('Apple-like polish', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><?php _e('Professional aesthetics', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><?php _e('Accessibility compliance', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><?php _e('Mobile responsiveness', 'hmg-ai-blog-enhancer'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('ℹ️ System Information', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4><?php _e('Plugin Information', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><strong><?php _e('Version:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo HMG_AI_BLOG_ENHANCER_VERSION; ?></p>
                        <p><strong><?php _e('WordPress Version:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo get_bloginfo('version'); ?></p>
                        <p><strong><?php _e('PHP Version:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo PHP_VERSION; ?></p>
                    </div>
                    <div>
                        <h4><?php _e('Database Status', 'hmg-ai-blog-enhancer'); ?></h4>
                        <?php
                        global $wpdb;
                        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
                        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
                        
                        $usage_count = $wpdb->get_var("SELECT COUNT(*) FROM {$usage_table}");
                        $cache_count = $wpdb->get_var("SELECT COUNT(*) FROM {$cache_table}");
                        ?>
                        <p><strong><?php _e('Usage Records:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo number_format($usage_count); ?></p>
                        <p><strong><?php _e('Cached Items:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo number_format($cache_count); ?></p>
                    </div>
                    <div>
                        <h4><?php _e('Quick Actions', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p>
                            <button type="button" class="button" onclick="location.reload();">
                                <?php _e('Refresh Page', 'hmg-ai-blog-enhancer'); ?>
                            </button>
                        </p>
                        <p>
                            <button type="button" class="button hmg-ai-refresh-stats">
                                <?php _e('Refresh Stats', 'hmg-ai-blog-enhancer'); ?>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <input type="submit" name="submit" class="hmg-ai-button" value="<?php _e('Save Settings', 'hmg-ai-blog-enhancer'); ?>" />
            <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>" class="hmg-ai-button secondary" style="margin-left: 10px;">
                <?php _e('Back to Dashboard', 'hmg-ai-blog-enhancer'); ?>
            </a>
        </div>
    </form>
</div> 