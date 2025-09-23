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

        // AJAX handlers for content generation
        $this->loader->add_action('wp_ajax_hmg_generate_takeaways', $plugin_admin, 'ajax_generate_takeaways');
        $this->loader->add_action('wp_ajax_hmg_generate_faq', $plugin_admin, 'ajax_generate_faq');
        $this->loader->add_action('wp_ajax_hmg_generate_toc', $plugin_admin, 'ajax_generate_toc');
        $this->loader->add_action('wp_ajax_hmg_generate_audio', $plugin_admin, 'ajax_generate_audio');

        // Settings page AJAX handlers
        $this->loader->add_action('wp_ajax_hmg_validate_api_key', $plugin_admin, 'ajax_validate_api_key');
        $this->loader->add_action('wp_ajax_hmg_get_usage_stats', $plugin_admin, 'ajax_get_usage_stats');
        $this->loader->add_action('wp_ajax_hmg_test_ai_providers', $plugin_admin, 'ajax_test_ai_providers');
        $this->loader->add_action('wp_ajax_hmg_save_ai_content', $plugin_admin, 'ajax_save_ai_content');

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

        // Register shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // Content filters for automatic insertion (DISABLED - using shortcodes instead)
        // $this->loader->add_filter('the_content', $plugin_public, 'maybe_add_ai_content', 20);

        // REST API endpoints for frontend interactions
        $this->loader->add_action('rest_api_init', $plugin_public, 'register_rest_routes');

        // Custom post content hooks
        $this->loader->add_action('wp_head', $plugin_public, 'add_structured_data');
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