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
     * The AI service manager instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      HMG_AI_Service_Manager    $ai_service_manager    AI service manager instance.
     */
    private $ai_service_manager;

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
        $this->ai_service_manager = new HMG_AI_Service_Manager();
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
     * Enqueue block editor assets to ensure meta boxes work properly
     *
     * @since    1.0.0
     */
    public function enqueue_block_editor_assets() {
        // Only enqueue on post edit screens
        $current_screen = get_current_screen();
        if (!$current_screen || !in_array($current_screen->post_type, ['post', 'page'])) {
            return;
        }

        // Enqueue our admin script for block editor compatibility
        wp_enqueue_script(
            $this->plugin_name . '-block-editor',
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'admin/js/hmg-ai-admin.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'jquery'),
            $this->version,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name . '-block-editor',
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

        // Enqueue admin styles for block editor
        wp_enqueue_style(
            $this->plugin_name . '-block-editor',
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'admin/css/hmg-ai-admin.css',
            array(),
            $this->version
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
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HMG AI: add_meta_boxes called');
        }
        
        // Check if we're in the block editor
        $current_screen = get_current_screen();
        $is_block_editor = $current_screen && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor();
        
        // For block editor, we need to register the meta box differently
        if ($is_block_editor) {
            // Register meta box for block editor
            add_meta_box(
                'hmg-ai-content-generator',
                __('AI Content Generator', 'hmg-ai-blog-enhancer'),
                array($this, 'display_content_generator_meta_box'),
                array('post', 'page'),
                'side',
                'high',
                array(
                    '__block_editor_compatible_meta_box' => true,
                    '__back_compat_meta_box' => false,
                )
            );
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HMG AI: Meta box added for block editor');
            }
        } else {
            // Classic editor
            add_meta_box(
                'hmg-ai-content-generator',
                __('AI Content Generator', 'hmg-ai-blog-enhancer'),
                array($this, 'display_content_generator_meta_box'),
                array('post', 'page'),
                'side',
                'high'
            );
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HMG AI: Meta box added for classic editor');
            }
        }
        
        // Force meta box to be visible by default
        if ($current_screen && in_array($current_screen->post_type, ['post', 'page'])) {
            // Get user meta for hidden meta boxes
            $user_id = get_current_user_id();
            $hidden_meta_boxes = get_user_meta($user_id, 'metaboxhidden_' . $current_screen->id, true);
            
            if (is_array($hidden_meta_boxes)) {
                // Remove our meta box from hidden list if it's there
                $key = array_search('hmg-ai-content-generator', $hidden_meta_boxes);
                if ($key !== false) {
                    unset($hidden_meta_boxes[$key]);
                    update_user_meta($user_id, 'metaboxhidden_' . $current_screen->id, $hidden_meta_boxes);
                    
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('HMG AI: Removed meta box from hidden list');
                    }
                }
            }
        }
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
        $auth_status = $this->auth_service->get_auth_status();
        
        // Show notice if no authentication is configured
        if (!$auth_status['authenticated']) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                __('HMG AI Blog Enhancer requires API key configuration to function. <a href="%s">Configure it now</a>.', 'hmg-ai-blog-enhancer'),
                admin_url('admin.php?page=hmg-ai-settings')
            ) . '</p>';
            echo '<p><small>' . esc_html($auth_status['message']) . '</small></p>';
            echo '</div>';
        }
        
        // Show meta box troubleshooting notice on post edit screens
        $current_screen = get_current_screen();
        if ($current_screen && in_array($current_screen->id, ['post', 'page']) && isset($_GET['hmg_metabox_help'])) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<h3>' . __('HMG AI Meta Box Troubleshooting', 'hmg-ai-blog-enhancer') . '</h3>';
            echo '<p><strong>' . __('Can\'t see the AI Content Generator meta box?', 'hmg-ai-blog-enhancer') . '</strong></p>';
            echo '<ol>';
            echo '<li><strong>' . __('Check Screen Options:', 'hmg-ai-blog-enhancer') . '</strong> ' . __('Click "Screen Options" at the top right of this page and make sure "AI Content Generator" is checked.', 'hmg-ai-blog-enhancer') . '</li>';
            echo '<li><strong>' . __('Look in the Sidebar:', 'hmg-ai-blog-enhancer') . '</strong> ' . __('The meta box appears in the right sidebar of the post editor.', 'hmg-ai-blog-enhancer') . '</li>';
            echo '<li><strong>' . __('Try Refreshing:', 'hmg-ai-blog-enhancer') . '</strong> ' . __('Refresh the page or try editing a different post.', 'hmg-ai-blog-enhancer') . '</li>';
            echo '<li><strong>' . __('Check Permissions:', 'hmg-ai-blog-enhancer') . '</strong> ' . __('Make sure you have Editor or Administrator role.', 'hmg-ai-blog-enhancer') . '</li>';
            echo '</ol>';
            echo '<p>';
            echo '<a href="' . remove_query_arg('hmg_metabox_help') . '" class="button button-secondary">' . __('Hide This Help', 'hmg-ai-blog-enhancer') . '</a> ';
            echo '<a href="' . add_query_arg('hmg_debug', '1') . '" class="button button-primary">' . __('Run Debug Check', 'hmg-ai-blog-enhancer') . '</a>';
            echo '</p>';
            echo '</div>';
        }
        
        // Add debug link to admin bar if not already present
        if ($current_screen && in_array($current_screen->id, ['post', 'page']) && !isset($_GET['hmg_metabox_help']) && !isset($_GET['hmg_debug'])) {
            // Only show this notice once per session
            if (!get_transient('hmg_ai_metabox_notice_shown_' . get_current_user_id())) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p>';
                echo '<strong>' . __('HMG AI Blog Enhancer:', 'hmg-ai-blog-enhancer') . '</strong> ';
                echo __('Look for the "AI Content Generator" box in the right sidebar. ', 'hmg-ai-blog-enhancer');
                echo '<a href="' . add_query_arg('hmg_metabox_help', '1') . '">' . __('Need help finding it?', 'hmg-ai-blog-enhancer') . '</a>';
                echo '</p>';
                echo '</div>';
                
                // Set transient to show notice only once per hour per user
                set_transient('hmg_ai_metabox_notice_shown_' . get_current_user_id(), true, HOUR_IN_SECONDS);
            }
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

        // Get content and post ID
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $post_id = (int) ($_POST['post_id'] ?? 0);

        if (empty($content)) {
            wp_send_json_error(array(
                'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
            ));
        }

        // Generate takeaways using AI service manager
        $result = $this->ai_service_manager->generate_content('takeaways', $content, $post_id);

        if ($result['success']) {
            wp_send_json_success(array(
                'content' => $result['content'],
                'message' => $result['message'],
                'provider_used' => $result['provider_name'] ?? 'AI Service',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'generation_time' => $result['generation_time'] ?? 0,
                'cached' => $result['cached'] ?? false
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['error']
            ));
        }
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

        // Get content and post ID
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $post_id = (int) ($_POST['post_id'] ?? 0);

        if (empty($content)) {
            wp_send_json_error(array(
                'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
            ));
        }

        // Generate FAQ using AI service manager
        $result = $this->ai_service_manager->generate_content('faq', $content, $post_id);

        if ($result['success']) {
            wp_send_json_success(array(
                'content' => $result['content'],
                'message' => $result['message'],
                'provider_used' => $result['provider_name'] ?? 'AI Service',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'generation_time' => $result['generation_time'] ?? 0,
                'cached' => $result['cached'] ?? false
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['error']
            ));
        }
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

        // Get content and post ID
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $post_id = (int) ($_POST['post_id'] ?? 0);

        if (empty($content)) {
            wp_send_json_error(array(
                'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
            ));
        }

        // Generate TOC using AI service manager
        $result = $this->ai_service_manager->generate_content('toc', $content, $post_id);

        if ($result['success']) {
            wp_send_json_success(array(
                'content' => $result['content'],
                'message' => $result['message'],
                'provider_used' => $result['provider_name'] ?? 'AI Service',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'generation_time' => $result['generation_time'] ?? 0,
                'cached' => $result['cached'] ?? false
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['error']
            ));
        }
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

    /**
     * AJAX handler for testing AI providers
     *
     * @since    1.0.0
     */
    public function ajax_test_ai_providers() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
            ));
        }

        try {
            // Test all AI providers
            $test_results = $this->ai_service_manager->test_all_providers();
            
            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HMG AI Provider Test Results: ' . print_r($test_results, true));
            }
            
            wp_send_json_success(array(
                'providers' => $test_results,
                'message' => __('Provider tests completed.', 'hmg-ai-blog-enhancer')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('Error testing providers: %s', 'hmg-ai-blog-enhancer'),
                    $e->getMessage()
                )
            ));
        }
    }

    /**
     * AJAX handler for saving AI content
     *
     * @since    1.0.0
     */
    public function ajax_save_ai_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hmg_ai_nonce')) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        $post_id = (int) ($_POST['post_id'] ?? 0);
        $content_type = sanitize_text_field($_POST['content_type'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');

        if (!$post_id || !$content_type || !$content) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters.', 'hmg-ai-blog-enhancer')
            ));
        }

        // Save content based on type
        $meta_key = '';
        switch ($content_type) {
            case 'takeaways':
                $meta_key = '_hmg_ai_takeaways';
                break;
            case 'faq':
                $meta_key = '_hmg_ai_faq';
                break;
            case 'toc':
                $meta_key = '_hmg_ai_toc';
                break;
            default:
                wp_send_json_error(array(
                    'message' => __('Invalid content type.', 'hmg-ai-blog-enhancer')
                ));
        }

        $result = update_post_meta($post_id, $meta_key, $content);

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => sprintf(__('%s content saved successfully!', 'hmg-ai-blog-enhancer'), ucfirst($content_type))
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to save content.', 'hmg-ai-blog-enhancer')
            ));
        }
    }
} 