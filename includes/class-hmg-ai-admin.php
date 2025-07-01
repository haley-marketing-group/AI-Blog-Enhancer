<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The authentication service instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      HMG_AI_Auth_Service    $auth_service    Authentication service instance.
     */
    private $auth_service;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->auth_service = new HMG_AI_Auth_Service();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'admin/css/hmg-ai-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Load Haley Marketing brand fonts
        wp_enqueue_style(
            $this->plugin_name . '-fonts',
            'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
            array(),
            $this->version
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'admin/js/hmg-ai-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name,
            'hmg_ai_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hmg_ai_nonce'),
                'plugin_url' => HMG_AI_BLOG_ENHANCER_PLUGIN_URL,
                'strings' => array(
                    'generating' => __('Generating content...', 'hmg-ai-blog-enhancer'),
                    'error' => __('An error occurred. Please try again.', 'hmg-ai-blog-enhancer'),
                    'success' => __('Content generated successfully!', 'hmg-ai-blog-enhancer'),
                )
            )
        );
    }

    /**
     * Add admin menu pages
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('HMG AI Blog Enhancer', 'hmg-ai-blog-enhancer'),
            __('AI Blog Enhancer', 'hmg-ai-blog-enhancer'),
            'manage_options',
            'hmg-ai-blog-enhancer',
            array($this, 'display_admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg viewBox="0 0 24 24" fill="#332A86"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'),
            30
        );

        // Settings submenu
        add_submenu_page(
            'hmg-ai-blog-enhancer',
            __('Settings', 'hmg-ai-blog-enhancer'),
            __('Settings', 'hmg-ai-blog-enhancer'),
            'manage_options',
            'hmg-ai-settings',
            array($this, 'display_settings_page')
        );

        // Analytics submenu
        add_submenu_page(
            'hmg-ai-blog-enhancer',
            __('Analytics', 'hmg-ai-blog-enhancer'),
            __('Analytics', 'hmg-ai-blog-enhancer'),
            'manage_options',
            'hmg-ai-analytics',
            array($this, 'display_analytics_page')
        );
    }

    /**
     * Initialize admin settings
     *
     * @since    1.0.0
     */
    public function admin_init() {
        register_setting('hmg_ai_settings', 'hmg_ai_blog_enhancer_options');
    }

    /**
     * Display the main admin page
     *
     * @since    1.0.0
     */
    public function display_admin_page() {
        include_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/admin-display.php';
    }

    /**
     * Display the settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/settings-display.php';
    }

    /**
     * Display the analytics page
     *
     * @since    1.0.0
     */
    public function display_analytics_page() {
        include_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/analytics-display.php';
    }

    /**
     * Add meta boxes to post edit screens
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'hmg-ai-content-generator',
            __('AI Content Generator', 'hmg-ai-blog-enhancer'),
            array($this, 'display_content_generator_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
    }

    /**
     * Display the content generator meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function display_content_generator_meta_box($post) {
        include_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/meta-box-content-generator.php';
    }

    /**
     * Save post meta data
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @param    WP_Post    $post    The post object.
     */
    public function save_post_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['hmg_ai_meta_nonce']) || !wp_verify_nonce($_POST['hmg_ai_meta_nonce'], 'hmg_ai_meta_box')) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta data
        if (isset($_POST['hmg_ai_generated_content'])) {
            update_post_meta($post_id, '_hmg_ai_generated_content', sanitize_textarea_field($_POST['hmg_ai_generated_content']));
        }
    }

    /**
     * Add plugin action links
     *
     * @since    1.0.0
     * @param    array    $links    The existing action links.
     * @return   array             The modified action links.
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=hmg-ai-settings') . '">' . __('Settings', 'hmg-ai-blog-enhancer') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Display admin notices
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        
        // Show notice if API key is not set
        if (empty($options['api_key'])) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                __('HMG AI Blog Enhancer requires an API key to function. <a href="%s">Configure it now</a>.', 'hmg-ai-blog-enhancer'),
                admin_url('admin.php?page=hmg-ai-settings')
            ) . '</p>';
            echo '</div>';
        }
    }

    /**
     * AJAX handler for generating takeaways
     *
     * @since    1.0.0
     */
    public function ajax_generate_takeaways() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Placeholder response - will be implemented with actual AI service
        wp_send_json_success(array(
            'content' => '<div class="hmg-ai-takeaways"><h3>Key Takeaways</h3><ul><li>Sample takeaway 1</li><li>Sample takeaway 2</li></ul></div>',
            'message' => __('Takeaways generated successfully!', 'hmg-ai-blog-enhancer')
        ));
    }

    /**
     * AJAX handler for generating FAQ
     *
     * @since    1.0.0
     */
    public function ajax_generate_faq() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Placeholder response - will be implemented with actual AI service
        wp_send_json_success(array(
            'content' => '<div class="hmg-ai-faq"><h3>Frequently Asked Questions</h3><div class="faq-item"><h4>Sample Question?</h4><p>Sample answer.</p></div></div>',
            'message' => __('FAQ generated successfully!', 'hmg-ai-blog-enhancer')
        ));
    }

    /**
     * AJAX handler for generating table of contents
     *
     * @since    1.0.0
     */
    public function ajax_generate_toc() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Placeholder response - will be implemented with actual AI service
        wp_send_json_success(array(
            'content' => '<div class="hmg-ai-toc"><h3>Table of Contents</h3><ol><li><a href="#section1">Section 1</a></li><li><a href="#section2">Section 2</a></li></ol></div>',
            'message' => __('Table of contents generated successfully!', 'hmg-ai-blog-enhancer')
        ));
    }

    /**
     * AJAX handler for generating audio
     *
     * @since    1.0.0
     */
    public function ajax_generate_audio() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Placeholder response - will be implemented with actual TTS service
        wp_send_json_success(array(
            'audio_url' => '#',
            'message' => __('Audio generated successfully!', 'hmg-ai-blog-enhancer')
        ));
    }

    /**
     * AJAX handler for validating API key
     *
     * @since    1.0.0
     */
    public function ajax_validate_api_key() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => __('Please enter an API key.', 'hmg-ai-blog-enhancer')
            ));
        }

        // Validate API key using auth service
        $validation_result = $this->auth_service->validate_api_key($api_key);

        if ($validation_result['valid']) {
            // Store the API key if valid
            $this->auth_service->store_api_key($api_key);
            
            wp_send_json_success(array(
                'valid' => true,
                'tier' => $validation_result['tier'],
                'method' => $validation_result['method'],
                'message' => $validation_result['message']
            ));
        } else {
            wp_send_json_error(array(
                'message' => $validation_result['error']
            ));
        }
    }

    /**
     * AJAX handler for getting usage statistics
     *
     * @since    1.0.0
     */
    public function ajax_get_usage_stats() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Get real usage statistics from auth service
        $usage_stats = $this->auth_service->get_usage_stats();
        
        wp_send_json_success($usage_stats);
    }
} 