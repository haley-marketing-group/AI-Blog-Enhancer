<?php
/**
 * Provide a admin area view for the analytics page
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
?>

<div class="hmg-ai-admin-wrap">
    <div class="hmg-ai-header">
        <h1><?php _e('Analytics', 'hmg-ai-blog-enhancer'); ?></h1>
        <p><?php _e('Detailed analytics and usage insights for your AI content enhancement', 'hmg-ai-blog-enhancer'); ?></p>
    </div>

    <div class="hmg-ai-notices"></div>

    <?php if (!$auth_status['authenticated']): ?>
        <!-- Authentication Required -->
        <div class="hmg-ai-card">
            <h3><?php _e('🔐 Authentication Required', 'hmg-ai-blog-enhancer'); ?></h3>
            <p><?php _e('Please configure your API key to access analytics features.', 'hmg-ai-blog-enhancer'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>" class="hmg-ai-button">
                    <?php _e('Configure API Key', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Analytics Content -->
        <div class="hmg-ai-cards">
            <div class="hmg-ai-card">
                <h3><?php _e('📊 Coming Soon', 'hmg-ai-blog-enhancer'); ?></h3>
                <p><?php _e('Advanced analytics features are currently in development and will be available in a future update.', 'hmg-ai-blog-enhancer'); ?></p>
                
                <h4><?php _e('Planned Features:', 'hmg-ai-blog-enhancer'); ?></h4>
                <ul>
                    <li><?php _e('Content performance metrics', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><?php _e('AI feature usage trends', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><?php _e('Cost optimization insights', 'hmg-ai-blog-enhancer'); ?></li>
                    <li><?php _e('ROI tracking for AI-enhanced content', 'hmg-ai-blog-enhancer'); ?></li>
                </ul>
                
                <p style="margin-top: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>" class="hmg-ai-button secondary">
                        <?php _e('Back to Dashboard', 'hmg-ai-blog-enhancer'); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div> 