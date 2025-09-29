<?php
/**
 * Database Setup Class
 *
 * Handles database table creation and updates for the plugin
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

class HMG_AI_Database_Setup {

    /**
     * Create or update database tables
     *
     * @since    1.1.0
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Content cache table
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        $sql = "CREATE TABLE IF NOT EXISTS $cache_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            cache_key varchar(32) NOT NULL,
            content longtext NOT NULL,
            content_type varchar(50) DEFAULT NULL,
            provider varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key),
            KEY expires_at (expires_at),
            KEY content_type (content_type),
            KEY provider (provider)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Usage tracking table (if not exists)
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        $sql2 = "CREATE TABLE IF NOT EXISTS $usage_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            post_id bigint(20) unsigned DEFAULT NULL,
            feature varchar(50) NOT NULL,
            api_calls int(11) DEFAULT 1,
            tokens_used int(11) DEFAULT 0,
            cost decimal(10,6) DEFAULT 0.000000,
            provider varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY post_id (post_id),
            KEY feature (feature),
            KEY provider (provider),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($sql2);
        
        // Add version option to track database version
        update_option('hmg_ai_db_version', '1.1.0');
        
        return true;
    }
    
    /**
     * Drop tables (for uninstall)
     *
     * @since    1.1.0
     */
    public static function drop_tables() {
        global $wpdb;
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        $wpdb->query("DROP TABLE IF EXISTS $cache_table");
        $wpdb->query("DROP TABLE IF EXISTS $usage_table");
        
        delete_option('hmg_ai_db_version');
    }
    
    /**
     * Clear cache table
     *
     * @since    1.1.0
     */
    public static function clear_cache() {
        global $wpdb;
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        $wpdb->query("DELETE FROM $cache_table WHERE expires_at < NOW()");
    }
    
    /**
     * Get cache statistics
     *
     * @since    1.1.0
     */
    public static function get_cache_stats() {
        global $wpdb;
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        $stats = array(
            'total_entries' => 0,
            'expired_entries' => 0,
            'active_entries' => 0,
            'total_size' => 0,
            'by_provider' => array(),
            'by_type' => array()
        );
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$cache_table'");
        if (!$table_exists) {
            return $stats;
        }
        
        // Total entries
        $stats['total_entries'] = $wpdb->get_var("SELECT COUNT(*) FROM $cache_table");
        
        // Active entries
        $stats['active_entries'] = $wpdb->get_var("SELECT COUNT(*) FROM $cache_table WHERE expires_at > NOW()");
        
        // Expired entries
        $stats['expired_entries'] = $stats['total_entries'] - $stats['active_entries'];
        
        // By provider
        $provider_stats = $wpdb->get_results(
            "SELECT provider, COUNT(*) as count FROM $cache_table 
             WHERE expires_at > NOW() 
             GROUP BY provider"
        );
        
        foreach ($provider_stats as $stat) {
            $stats['by_provider'][$stat->provider] = $stat->count;
        }
        
        // By content type
        $type_stats = $wpdb->get_results(
            "SELECT content_type, COUNT(*) as count FROM $cache_table 
             WHERE expires_at > NOW() 
             GROUP BY content_type"
        );
        
        foreach ($type_stats as $stat) {
            $stats['by_type'][$stat->content_type] = $stat->count;
        }
        
        return $stats;
    }
}
