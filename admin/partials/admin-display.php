<?php
/**
 * Provide a admin area view for the main dashboard
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
$usage_stats = $auth_service->get_usage_stats();

// Ensure required array keys exist with defaults
$auth_status = wp_parse_args($auth_status, array(
    'authenticated' => false,
    'tier' => 'free',
    'method' => 'standalone',
    'user_id' => get_current_user_id()
));

$usage_stats = wp_parse_args($usage_stats, array(
    'api_calls_used' => 0,
    'api_calls_limit' => 1000,
    'tokens_used' => 0,
    'tokens_limit' => 1000000,
    'reset_date' => date('Y-m-01')
));
?>

<div class="hmg-ai-admin-wrap">
    <div class="hmg-ai-header">
        <h1><?php _e('HMG AI Blog Enhancer', 'hmg-ai-blog-enhancer'); ?></h1>
        <p><?php _e('Professional AI-powered content enhancement with Haley Marketing excellence', 'hmg-ai-blog-enhancer'); ?></p>
    </div>

    <div class="hmg-ai-notices"></div>

    <?php if (!$auth_status['authenticated']): ?>
        <!-- Authentication Required -->
        <div class="hmg-ai-cards">
            <div class="hmg-ai-card">
                <h3><?php _e('🔐 Authentication Required', 'hmg-ai-blog-enhancer'); ?></h3>
                <p><?php _e('To use the AI enhancement features, you need to configure your API key.', 'hmg-ai-blog-enhancer'); ?></p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>" class="hmg-ai-button">
                        <?php _e('Configure API Key', 'hmg-ai-blog-enhancer'); ?>
                    </a>
                </p>
            </div>

            <div class="hmg-ai-card">
                <h3><?php _e('🚀 Get Started', 'hmg-ai-blog-enhancer'); ?></h3>
                <h4><?php _e('Development Mode', 'hmg-ai-blog-enhancer'); ?></h4>
                <p><?php _e('For testing purposes, you can use a development API key:', 'hmg-ai-blog-enhancer'); ?></p>
                <code style="background: #f0f0f0; padding: 8px; border-radius: 4px; display: block; margin: 10px 0;">dev_pro_test_key</code>
                <p><small><?php _e('This will give you Pro tier access for development.', 'hmg-ai-blog-enhancer'); ?></small></p>
            </div>

            <div class="hmg-ai-card">
                <h3><?php _e('📚 Features Overview', 'hmg-ai-blog-enhancer'); ?></h3>
                <ul>
                    <li><strong><?php _e('Key Takeaways:', 'hmg-ai-blog-enhancer'); ?></strong> <?php _e('Auto-generate bullet points', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><strong><?php _e('FAQ Generation:', 'hmg-ai-blog-enhancer'); ?></strong> <?php _e('Create relevant Q&A sections', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><strong><?php _e('Table of Contents:', 'hmg-ai-blog-enhancer'); ?></strong> <?php _e('Smart navigation structure', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><strong><?php _e('Audio Conversion:', 'hmg-ai-blog-enhancer'); ?></strong> <?php _e('Text-to-speech for accessibility', 'hmg-ai-blog-enhancer'); ?></li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <!-- Authenticated Dashboard -->
        <div class="hmg-ai-cards">
            <!-- Welcome Card -->
            <div class="hmg-ai-card">
                <h3><?php _e('👋 Welcome Back!', 'hmg-ai-blog-enhancer'); ?></h3>
                <p>
                    <strong><?php _e('Status:', 'hmg-ai-blog-enhancer'); ?></strong> 
                    <span style="color: var(--hmg-lime-green);"><?php _e('Authenticated', 'hmg-ai-blog-enhancer'); ?></span>
                </p>
                <p>
                    <strong><?php _e('Tier:', 'hmg-ai-blog-enhancer'); ?></strong> 
                    <?php echo esc_html(ucfirst($auth_status['tier'] ?? 'free')); ?>
                    <?php if (isset($auth_status['method']) && $auth_status['method'] === 'development'): ?>
                        <span style="color: var(--hmg-orange); font-size: 12px;">(Development)</span>
                    <?php endif; ?>
                </p>
                <p>
                    <strong><?php _e('Method:', 'hmg-ai-blog-enhancer'); ?></strong> 
                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $auth_status['method'] ?? 'standalone'))); ?>
                </p>
            </div>

            <!-- Usage Statistics -->
            <div class="hmg-ai-card">
                <h3><?php _e('📊 Usage This Month', 'hmg-ai-blog-enhancer'); ?></h3>
                
                <!-- API Calls Usage -->
                <div class="hmg-ai-usage-section">
                    <label><?php _e('API Calls', 'hmg-ai-blog-enhancer'); ?></label>
                    <div class="hmg-ai-usage-bar">
                        <?php 
                        $api_calls_used = $usage_stats['api_calls_used'] ?? 0;
                        $api_calls_limit = $usage_stats['api_calls_limit'] ?? 1000;
                        $api_percentage = $api_calls_limit > 0 ? ($api_calls_used / $api_calls_limit) * 100 : 0;
                        ?>
                        <div class="hmg-ai-usage-fill api-calls" style="width: <?php echo min($api_percentage, 100); ?>%"></div>
                    </div>
                    <div class="hmg-ai-usage-stats">
                        <span><?php echo number_format($api_calls_used); ?> / <?php echo number_format($api_calls_limit); ?></span>
                    </div>
                </div>

                <!-- Tokens Usage -->
                <div class="hmg-ai-usage-section">
                    <label><?php _e('Tokens', 'hmg-ai-blog-enhancer'); ?></label>
                    <div class="hmg-ai-usage-bar">
                        <?php 
                        $tokens_used = $usage_stats['tokens_used'] ?? 0;
                        $tokens_limit = $usage_stats['tokens_limit'] ?? 1000000;
                        $token_percentage = $tokens_limit > 0 ? ($tokens_used / $tokens_limit) * 100 : 0;
                        ?>
                        <div class="hmg-ai-usage-fill tokens" style="width: <?php echo min($token_percentage, 100); ?>%"></div>
                    </div>
                    <div class="hmg-ai-usage-stats">
                        <span><?php echo number_format($tokens_used); ?> / <?php echo number_format($tokens_limit); ?></span>
                    </div>
                </div>

                <p style="text-align: center; margin-top: 15px;">
                    <small><?php _e('Resets on:', 'hmg-ai-blog-enhancer'); ?> <?php echo date('M j, Y', strtotime($usage_stats['reset_date'] ?? date('Y-m-01'))); ?></small>
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="hmg-ai-card">
                <h3><?php _e('⚡ Quick Actions', 'hmg-ai-blog-enhancer'); ?></h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo admin_url('post-new.php'); ?>" class="hmg-ai-button">
                        <?php _e('Create New Post', 'hmg-ai-blog-enhancer'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php'); ?>" class="hmg-ai-button secondary">
                        <?php _e('Enhance Existing Posts', 'hmg-ai-blog-enhancer'); ?>
                    </a>
                    <button type="button" class="hmg-ai-button hmg-ai-test-providers" style="background: #667eea; color: white;">
                        <span class="dashicons dashicons-admin-plugins" style="margin-right: 5px;"></span>
                        <?php _e('Test All Providers', 'hmg-ai-blog-enhancer'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>" class="hmg-ai-button accent">
                        <?php _e('Plugin Settings', 'hmg-ai-blog-enhancer'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="hmg-ai-card" style="margin-top: 20px;">
            <h3><?php _e('📈 Recent Activity', 'hmg-ai-blog-enhancer'); ?></h3>
            <?php
            global $wpdb;
            $usage_table = $wpdb->prefix . 'hmg_ai_usage';
            
            $user_id = $auth_status['user_id'] ?? get_current_user_id();
            $recent_activity = $wpdb->get_results($wpdb->prepare(
                "SELECT u.*, p.post_title 
                FROM {$usage_table} u 
                LEFT JOIN {$wpdb->posts} p ON u.post_id = p.ID 
                WHERE u.user_id = %s 
                ORDER BY u.created_at DESC 
                LIMIT 10",
                $user_id
            ));
            
            if ($recent_activity): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Post', 'hmg-ai-blog-enhancer'); ?></th>
                            <th><?php _e('Feature', 'hmg-ai-blog-enhancer'); ?></th>
                            <th><?php _e('API Calls', 'hmg-ai-blog-enhancer'); ?></th>
                            <th><?php _e('Tokens', 'hmg-ai-blog-enhancer'); ?></th>
                            <th><?php _e('Date', 'hmg-ai-blog-enhancer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $activity): ?>
                            <tr>
                                <td>
                                    <?php if ($activity->post_title): ?>
                                        <a href="<?php echo get_edit_post_link($activity->post_id); ?>">
                                            <?php echo esc_html($activity->post_title); ?>
                                        </a>
                                    <?php else: ?>
                                        <em><?php _e('Post not found', 'hmg-ai-blog-enhancer'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $activity->feature_type))); ?></td>
                                <td><?php echo number_format($activity->api_calls_used); ?></td>
                                <td><?php echo number_format($activity->tokens_used); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($activity->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No recent activity found. Start by creating or editing a post to use AI features!', 'hmg-ai-blog-enhancer'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Available Features -->
        <div class="hmg-ai-cards" style="margin-top: 20px;">
            <div class="hmg-ai-card">
                <h3><?php _e('✨ Available Features', 'hmg-ai-blog-enhancer'); ?></h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <?php
                    $features = array(
                        'takeaways' => array('name' => 'Key Takeaways', 'icon' => '💡'),
                        'faq' => array('name' => 'FAQ Generation', 'icon' => '❓'),
                        'toc' => array('name' => 'Table of Contents', 'icon' => '📋'),
                        'audio' => array('name' => 'Audio Conversion', 'icon' => '🎧'),
                        'advanced_analytics' => array('name' => 'Advanced Analytics', 'icon' => '📊')
                    );
                    
                    foreach ($features as $feature_key => $feature):
                        $has_access = $auth_service->has_feature_access($feature_key);
                    ?>
                        <div style="padding: 15px; border: 1px solid #E1E5E9; border-radius: 8px; text-align: center; <?php echo $has_access ? 'background: #F0F9F0;' : 'background: #F8F9FA; opacity: 0.6;'; ?>">
                            <div style="font-size: 24px; margin-bottom: 8px;"><?php echo $feature['icon']; ?></div>
                            <div style="font-weight: 500; margin-bottom: 4px;"><?php echo $feature['name']; ?></div>
                            <div style="font-size: 12px; color: <?php echo $has_access ? 'var(--hmg-lime-green)' : 'var(--hmg-medium-gray)'; ?>;">
                                <?php echo $has_access ? __('Available', 'hmg-ai-blog-enhancer') : __('Upgrade Required', 'hmg-ai-blog-enhancer'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="hmg-ai-card" style="margin-top: 20px;">
        <h3><?php _e('💡 Getting Started', 'hmg-ai-blog-enhancer'); ?></h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <h4><?php _e('1. Create Content', 'hmg-ai-blog-enhancer'); ?></h4>
                <p><?php _e('Write your blog post or page content with clear headings and structure.', 'hmg-ai-blog-enhancer'); ?></p>
            </div>
            <div>
                <h4><?php _e('2. Use AI Features', 'hmg-ai-blog-enhancer'); ?></h4>
                <p><?php _e('Access the AI Content Generator in the post editor sidebar to enhance your content.', 'hmg-ai-blog-enhancer'); ?></p>
            </div>
            <div>
                <h4><?php _e('3. Customize & Publish', 'hmg-ai-blog-enhancer'); ?></h4>
                <p><?php _e('Review and customize the AI-generated content before publishing your enhanced post.', 'hmg-ai-blog-enhancer'); ?></p>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: var(--hmg-light-gray); border-radius: 8px;">
            <h4 style="margin-top: 0;"><?php _e('🎨 Haley Marketing Brand Standards', 'hmg-ai-blog-enhancer'); ?></h4>
            <p><?php _e('This plugin is designed with Haley Marketing\'s professional brand standards, featuring our signature colors and Apple-like attention to detail.', 'hmg-ai-blog-enhancer'); ?></p>
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; background: var(--hmg-royal-blue); border-radius: 50%;"></div>
                    <span><?php _e('Royal Blue', 'hmg-ai-blog-enhancer'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; background: var(--hmg-lime-green); border-radius: 50%;"></div>
                    <span><?php _e('Lime Green', 'hmg-ai-blog-enhancer'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; background: var(--hmg-orange); border-radius: 50%;"></div>
                    <span><?php _e('Orange', 'hmg-ai-blog-enhancer'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div> 