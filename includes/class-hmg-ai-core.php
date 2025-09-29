<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      HMG_AI_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('HMG_AI_BLOG_ENHANCER_VERSION')) {
            $this->version = HMG_AI_BLOG_ENHANCER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'hmg-ai-blog-enhancer';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - HMG_AI_Loader. Orchestrates the hooks of the plugin.
     * - HMG_AI_i18n. Defines internationalization functionality.
     * - HMG_AI_Admin. Defines all hooks for the admin area.
     * - HMG_AI_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-public.php';

        /**
         * Load service classes
         */
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-auth-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-gemini-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-openai-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-claude-service.php';
        require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-ai-service-manager.php';

        $this->loader = new HMG_AI_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the HMG_AI_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new HMG_AI_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new HMG_AI_Admin($this->get_plugin_name(), $this->get_version());

        // Enqueue admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Add admin menu and settings
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'admin_init');

        // Add meta boxes for posts/pages
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $plugin_admin, 'save_post_meta', 10, 2);
        
        // Ensure meta boxes work with block editor
        $this->loader->add_action('enqueue_block_editor_assets', $plugin_admin, 'enqueue_block_editor_assets');
        
        // Properly enqueue styles for block editor iframe
        $this->loader->add_action('enqueue_block_assets', $plugin_admin, 'enqueue_block_assets');

        // AJAX handlers for content generation
        $this->loader->add_action('wp_ajax_hmg_generate_takeaways', $plugin_admin, 'ajax_generate_takeaways');
        $this->loader->add_action('wp_ajax_hmg_generate_faq', $plugin_admin, 'ajax_generate_faq');
        $this->loader->add_action('wp_ajax_hmg_generate_toc', $plugin_admin, 'ajax_generate_toc');
        $this->loader->add_action('wp_ajax_hmg_generate_audio', $plugin_admin, 'ajax_generate_audio');

        // Settings page AJAX handlers
        $this->loader->add_action('wp_ajax_hmg_validate_api_key', $plugin_admin, 'ajax_validate_api_key');
        $this->loader->add_action('wp_ajax_hmg_get_usage_stats', $plugin_admin, 'ajax_get_usage_stats');
        $this->loader->add_action('wp_ajax_hmg_test_ai_providers', $plugin_admin, 'ajax_test_ai_providers');
        $this->loader->add_action('wp_ajax_hmg_test_single_provider', $plugin_admin, 'ajax_test_single_provider');
        $this->loader->add_action('wp_ajax_hmg_save_ai_content', $plugin_admin, 'ajax_save_ai_content');
        $this->loader->add_action('wp_ajax_hmg_delete_content', $plugin_admin, 'ajax_delete_content');
        $this->loader->add_action('wp_ajax_hmg_ai_refresh_voices', $plugin_admin, 'ajax_refresh_voices');
        
        // Context-Aware AI handlers
        $this->loader->add_action('wp_ajax_hmg_analyze_brand_voice', $plugin_admin, 'ajax_analyze_brand_voice');
        $this->loader->add_action('wp_ajax_hmg_clear_brand_profile', $plugin_admin, 'ajax_clear_brand_profile');
        
        // SEO Optimizer handlers
        $this->loader->add_action('wp_ajax_hmg_analyze_seo', $plugin_admin, 'ajax_analyze_seo');
        $this->loader->add_action('wp_ajax_hmg_optimize_seo', $plugin_admin, 'ajax_optimize_seo');
        $this->loader->add_action('wp_ajax_hmg_generate_meta_description', $plugin_admin, 'ajax_generate_meta_description');
        $this->loader->add_action('wp_ajax_hmg_extract_keywords', $plugin_admin, 'ajax_extract_keywords');
        $this->loader->add_action('wp_ajax_hmg_save_seo_data', $plugin_admin, 'ajax_save_seo_data');
        
        // Performance Optimizer handlers
        $this->loader->add_action('wp_ajax_hmg_save_performance_settings', $plugin_admin, 'ajax_save_performance_settings');
        $this->loader->add_action('wp_ajax_hmg_optimize_database', $plugin_admin, 'ajax_optimize_database');
        $this->loader->add_action('wp_ajax_hmg_clear_all_caches', $plugin_admin, 'ajax_clear_all_caches');
        $this->loader->add_action('wp_ajax_hmg_process_shortcode', $plugin_admin, 'ajax_process_shortcode');
        $this->loader->add_action('wp_ajax_nopriv_hmg_process_shortcode', $plugin_admin, 'ajax_process_shortcode');

        // Plugin action links
        $this->loader->add_filter('plugin_action_links_' . HMG_AI_BLOG_ENHANCER_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');

        // Admin notices
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new HMG_AI_Public($this->get_plugin_name(), $this->get_version());

        // Enqueue public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Performance Optimizer hooks
        if (!class_exists('HMG_AI_Performance_Optimizer')) {
            require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-performance-optimizer.php';
        }
        $performance = new HMG_AI_Performance_Optimizer();
        
        // Add lazy loading
        $this->loader->add_filter('the_content', $performance, 'add_lazy_loading', 15);
        
        // Preload critical resources
        $this->loader->add_action('wp_head', $performance, 'preload_critical_resources', 1);
        
        // Add cache headers
        $this->loader->add_filter('wp_headers', $performance, 'add_cache_headers');

        // Register shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // Add heading IDs for TOC navigation
        $this->loader->add_filter('the_content', $plugin_public, 'add_heading_ids', 5);

        // Add structured data for FAQ
        $this->loader->add_action('wp_head', $plugin_public, 'add_faq_structured_data');
        
        // SEO Optimizer hooks
        if (class_exists('HMG_AI_SEO_Optimizer')) {
            $seo_optimizer = new HMG_AI_SEO_Optimizer();
            $this->loader->add_action('wp_head', $seo_optimizer, 'add_meta_tags');
            $this->loader->add_action('wp_head', $seo_optimizer, 'output_schema_markup');
        } else {
            // Load SEO Optimizer if not loaded
            require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
            $seo_optimizer = new HMG_AI_SEO_Optimizer();
            $this->loader->add_action('wp_head', $seo_optimizer, 'add_meta_tags');
            $this->loader->add_action('wp_head', $seo_optimizer, 'output_schema_markup');
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    HMG_AI_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
} 