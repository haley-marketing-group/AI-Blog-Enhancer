<?php
/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Deactivator {

    /**
     * Plugin deactivation handler
     *
     * Performs cleanup tasks when the plugin is deactivated:
     * - Clears scheduled events
     * - Cleans up temporary data
     * - Preserves user settings and data
     * - Flushes rewrite rules
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        
        // Clear any scheduled cron events
        self::clear_scheduled_events();

        // Clear transients and cache
        self::clear_cache();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log deactivation
        update_option('hmg_ai_blog_enhancer_deactivated', time());

        // Note: We don't delete user data or settings on deactivation
        // This is handled in uninstall.php if the user chooses to delete the plugin
    }

    /**
     * Clear all scheduled cron events
     *
     * @since    1.0.0
     * @access   private
     */
    private static function clear_scheduled_events() {
        
        // Clear cache cleanup event
        $timestamp = wp_next_scheduled('hmg_ai_cache_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'hmg_ai_cache_cleanup');
        }

        // Clear usage tracking sync event
        $timestamp = wp_next_scheduled('hmg_ai_usage_sync');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'hmg_ai_usage_sync');
        }

        // Clear any other scheduled events
        wp_clear_scheduled_hook('hmg_ai_cache_cleanup');
        wp_clear_scheduled_hook('hmg_ai_usage_sync');
    }

    /**
     * Clear temporary cache and transients
     *
     * @since    1.0.0
     * @access   private
     */
    private static function clear_cache() {
        
        // Clear expired cache entries from database
        global $wpdb;
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        // Delete expired cache entries
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$cache_table} WHERE expires_at < %s",
                current_time('mysql')
            )
        );

        // Clear WordPress transients
        delete_transient('hmg_ai_api_status');
        delete_transient('hmg_ai_usage_limits');
        delete_transient('hmg_ai_feature_flags');

        // Clear any site transients
        delete_site_transient('hmg_ai_global_settings');
    }
} 