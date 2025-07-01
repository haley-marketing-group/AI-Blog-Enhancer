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
    
    // API Key
    $new_options['api_key'] = sanitize_text_field($_POST['api_key'] ?? '');
    
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
    update_option('hmg_ai_blog_enhancer_options', $new_options);
    
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

        <!-- API Configuration -->
        <div class="hmg-ai-settings-section">
            <h3><?php _e('🔐 API Configuration', 'hmg-ai-blog-enhancer'); ?></h3>
            <div class="inside">
                <div class="hmg-ai-form-group">
                    <label for="api_key"><?php _e('API Key', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" 
                           id="api_key" 
                           name="api_key" 
                           value="<?php echo esc_attr($options['api_key'] ?? ''); ?>" 
                           placeholder="<?php _e('Enter your API key...', 'hmg-ai-blog-enhancer'); ?>" 
                           style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        <?php _e('Enter your HMG AI Blog Enhancer API key. For development, you can use:', 'hmg-ai-blog-enhancer'); ?>
                        <code>dev_pro_test_key</code>
                    </p>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" class="hmg-ai-button hmg-ai-validate-api-key">
                        <?php _e('Validate API Key', 'hmg-ai-blog-enhancer'); ?>
                    </button>
                </div>

                <?php if ($auth_status['authenticated']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #F0F9F0; border-left: 4px solid var(--hmg-lime-green); border-radius: 4px;">
                        <h4 style="margin-top: 0; color: var(--hmg-lime-green);"><?php _e('✅ Authentication Status', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><strong><?php _e('Status:', 'hmg-ai-blog-enhancer'); ?></strong> <?php _e('Authenticated', 'hmg-ai-blog-enhancer'); ?></p>
                        <p><strong><?php _e('Tier:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo esc_html(ucfirst($auth_status['tier'])); ?></p>
                        <p><strong><?php _e('Method:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $auth_status['method']))); ?></p>
                        <?php if (!empty($auth_status['email'])): ?>
                            <p><strong><?php _e('Email:', 'hmg-ai-blog-enhancer'); ?></strong> <?php echo esc_html($auth_status['email']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 15px; padding: 15px; background: #FFF8F0; border-left: 4px solid var(--hmg-orange); border-radius: 4px;">
                        <h4 style="margin-top: 0; color: var(--hmg-orange);"><?php _e('⚠️ Authentication Required', 'hmg-ai-blog-enhancer'); ?></h4>
                        <p><?php echo esc_html($auth_status['message']); ?></p>
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