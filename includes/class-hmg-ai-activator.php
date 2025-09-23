<?php
/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Activator {

    /**
     * Plugin activation handler
     *
     * Performs initial setup tasks when the plugin is activated:
     * - Creates database tables if needed
     * - Sets default options
     * - Checks system requirements
     * - Sets up initial configuration
     *
     * @since    1.0.0
     */
    public static function activate() {
        
        // Check WordPress version requirement
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            wp_die(
                esc_html__('HMG AI Blog Enhancer requires WordPress 5.0 or higher.', 'hmg-ai-blog-enhancer'),
                esc_html__('Plugin Activation Error', 'hmg-ai-blog-enhancer'),
                array('back_link' => true)
            );
        }

        // Check PHP version requirement
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(
                esc_html__('HMG AI Blog Enhancer requires PHP 7.4 or higher.', 'hmg-ai-blog-enhancer'),
                esc_html__('Plugin Activation Error', 'hmg-ai-blog-enhancer'),
                array('back_link' => true)
            );
        }

        // Set default options
        self::set_default_options();

        // Create custom database tables if needed
        self::create_tables();

        // Set activation timestamp
        update_option('hmg_ai_blog_enhancer_activated', time());

        // Set plugin version
        update_option('hmg_ai_blog_enhancer_version', HMG_AI_BLOG_ENHANCER_VERSION);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Set default plugin options
     *
     * @since    1.0.0
     * @access   private
     */
    private static function set_default_options() {
        
        $default_options = array(
            'api_key' => '',
            'user_tier' => 'free',
            'auto_generate_takeaways' => false,
            'auto_generate_faq' => false,
            'auto_generate_toc' => true,
            'enable_audio_conversion' => false,
            'brand_colors' => array(
                'primary' => '#332A86',    // Royal Blue
                'secondary' => '#5E9732',  // Lime Green
                'accent' => '#E36F1E'      // Orange
            ),
            'typography' => array(
                'heading_font' => 'Museo Slab',
                'body_font' => 'Roboto'
            ),
            'usage_tracking' => true,
            'cache_enabled' => true,
            'cache_duration' => 3600, // 1 hour
        );

        add_option('hmg_ai_blog_enhancer_options', $default_options);
    }

    /**
     * Create custom database tables
     *
     * @since    1.0.0
     * @access   private
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Usage tracking table
        $table_name = $wpdb->prefix . 'hmg_ai_usage';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            feature_type varchar(50) NOT NULL,
            provider varchar(50) DEFAULT 'unknown',
            api_calls_used int(11) DEFAULT 0,
            tokens_used int(11) DEFAULT 0,
            estimated_cost decimal(10,4) DEFAULT 0.0000,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY post_id (post_id),
            KEY feature_type (feature_type),
            KEY provider (provider),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Generated content cache table
        $cache_table_name = $wpdb->prefix . 'hmg_ai_content_cache';
        
        $cache_sql = "CREATE TABLE $cache_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_hash varchar(64) NOT NULL,
            feature_type varchar(50) NOT NULL,
            generated_content longtext NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY content_hash (content_hash, feature_type),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
        dbDelta($cache_sql);
    }
} 