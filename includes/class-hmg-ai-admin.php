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
        // Skip if we're in the block editor (styles are loaded via enqueue_block_assets)
        $current_screen = get_current_screen();
        if ($current_screen && $current_screen->is_block_editor()) {
            return;
        }
        
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
                'nonce' => wp_create_nonce('hmg-ai-ajax-nonce'),
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
     * Enqueue block editor JavaScript assets
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
                'nonce' => wp_create_nonce('hmg-ai-ajax-nonce'),
                'plugin_url' => HMG_AI_BLOG_ENHANCER_PLUGIN_URL,
                'strings' => array(
                    'generating' => __('Generating content...', 'hmg-ai-blog-enhancer'),
                    'error' => __('An error occurred. Please try again.', 'hmg-ai-blog-enhancer'),
                    'success' => __('Content generated successfully!', 'hmg-ai-blog-enhancer'),
                )
            )
        );
        
        // Note: Styles are enqueued via enqueue_block_assets for proper iframe support
    }
    
    /**
     * Enqueue block assets for both editor and frontend
     * This is the proper way to add styles to the block editor iframe
     *
     * @since    1.0.0
     */
    public function enqueue_block_assets() {
        // Only enqueue in admin area for editor
        if (!is_admin()) {
            return;
        }
        
        $current_screen = get_current_screen();
        if (!$current_screen || !in_array($current_screen->post_type, ['post', 'page'])) {
            return;
        }
        
        // Only enqueue if we're in the block editor
        if (!$current_screen->is_block_editor()) {
            return;
        }
        
        // Enqueue styles for block editor iframe
        wp_enqueue_style(
            $this->plugin_name . '-block-styles',
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'admin/css/hmg-ai-admin.css',
            array(),
            $this->version
        );
        
        // Load Haley Marketing brand fonts for block editor
        wp_enqueue_style(
            $this->plugin_name . '-block-fonts',
            'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
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
        
        // Performance submenu
        add_submenu_page(
            'hmg-ai-blog-enhancer',
            __('Performance', 'hmg-ai-blog-enhancer'),
            __('Performance', 'hmg-ai-blog-enhancer'),
            'manage_options',
            'hmg-ai-performance',
            array($this, 'display_performance_page')
        );
    }
    
    /**
     * Display the performance page
     *
     * @since    1.4.0
     */
    public function display_performance_page() {
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/performance-dashboard.php';
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
                    }
                }
            }
        }
        
        // Add SEO Optimizer meta box
        add_meta_box(
            'hmg-ai-seo-optimizer',
            __('SEO Optimizer', 'hmg-ai-blog-enhancer'),
            array($this, 'display_seo_optimizer_meta_box'),
            array('post'),
            'normal',
            'high',
            array(
                '__block_editor_compatible_meta_box' => true,
                '__back_compat_meta_box' => false
            )
        );
    }
    
    /**
     * Display SEO Optimizer meta box
     *
     * @since    1.3.0
     * @param    WP_Post    $post    Current post object.
     */
    public function display_seo_optimizer_meta_box($post) {
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/meta-box-seo-optimizer.php';
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
        try {
            // Verify nonce
            if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
                wp_send_json_error(array(
                    'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Check user capabilities
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array(
                    'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Get content and post ID
            $content = sanitize_textarea_field($_POST['content'] ?? '');
            $post_id = (int) ($_POST['post_id'] ?? 0);
            $provider = sanitize_text_field($_POST['provider'] ?? 'auto');

            if (empty($content)) {
                wp_send_json_error(array(
                    'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Prepare options for AI service manager
            $options = array();
            if ($provider !== 'auto') {
                $options['provider'] = $provider;
            }

            // Generate takeaways using AI service manager
            $result = $this->ai_service_manager->generate_content('takeaways', $content, $post_id, $options);

        if ($result['success']) {
            // Save generated content as post meta
            if ($post_id > 0) {
                update_post_meta($post_id, '_hmg_ai_takeaways', $result['content']);
                update_post_meta($post_id, '_hmg_ai_takeaways_generated', current_time('mysql'));
            }

            // Get updated usage stats
            $updated_stats = $this->auth_service->get_spending_stats();

            wp_send_json_success(array(
                'content' => $result['content'],
                'message' => $result['message'] ?? __('Takeaways generated successfully!', 'hmg-ai-blog-enhancer'),
                'provider_used' => $result['provider_name'] ?? 'AI Service',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'generation_time' => $result['generation_time'] ?? 0,
                'cached' => $result['cached'] ?? false,
                'usage' => array(
                    'spending' => array(
                        'used' => $updated_stats['monthly']['spent'],
                        'limit' => $updated_stats['monthly']['limit'],
                        'percentage' => $updated_stats['monthly']['percentage']
                    ),
                    'api_calls' => $updated_stats['monthly']['requests'],
                    'tokens' => $updated_stats['monthly']['tokens'],
                    'reset_date' => $updated_stats['reset_date']
                )
            ));
            } else {
                wp_send_json_error(array(
                    'message' => $result['error']
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Error: ' . $e->getMessage()
            ));
        } catch (Error $e) {
            wp_send_json_error(array(
                'message' => 'Fatal Error: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX handler for generating FAQ
     *
     * @since    1.0.0
     */
    public function ajax_generate_faq() {
        try {
            // Verify nonce
            if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
                wp_send_json_error(array(
                    'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Check user capabilities
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array(
                    'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Get content and post ID
            $content = sanitize_textarea_field($_POST['content'] ?? '');
            $post_id = (int) ($_POST['post_id'] ?? 0);
            $provider = sanitize_text_field($_POST['provider'] ?? 'auto');

            if (empty($content)) {
                wp_send_json_error(array(
                    'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Prepare options for AI service manager
            $options = array();
            if ($provider !== 'auto') {
                $options['provider'] = $provider;
            }

            // Generate FAQ using AI service manager
            $result = $this->ai_service_manager->generate_content('faq', $content, $post_id, $options);

        if ($result['success']) {
            // Save generated content as post meta
            if ($post_id > 0) {
                update_post_meta($post_id, '_hmg_ai_faq', $result['content']);
                update_post_meta($post_id, '_hmg_ai_faq_generated', current_time('mysql'));
            }

            // Get updated usage stats
            $updated_stats = $this->auth_service->get_spending_stats();

            wp_send_json_success(array(
                'content' => $result['content'],
                'message' => $result['message'] ?? __('FAQ generated successfully!', 'hmg-ai-blog-enhancer'),
                'provider_used' => $result['provider_name'] ?? 'AI Service',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'generation_time' => $result['generation_time'] ?? 0,
                'cached' => $result['cached'] ?? false,
                'usage' => array(
                    'spending' => array(
                        'used' => $updated_stats['monthly']['spent'],
                        'limit' => $updated_stats['monthly']['limit'],
                        'percentage' => $updated_stats['monthly']['percentage']
                    ),
                    'api_calls' => $updated_stats['monthly']['requests'],
                    'tokens' => $updated_stats['monthly']['tokens'],
                    'reset_date' => $updated_stats['reset_date']
                )
            ));
            } else {
                wp_send_json_error(array(
                    'message' => $result['error']
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Error: ' . $e->getMessage()
            ));
        } catch (Error $e) {
            wp_send_json_error(array(
                'message' => 'Fatal Error: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX handler for generating table of contents
     *
     * @since    1.0.0
     */
    public function ajax_generate_toc() {
        try {
            // Verify nonce
            if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
                wp_send_json_error(array(
                    'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Check user capabilities
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array(
                    'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Get content and post ID
            $content = sanitize_textarea_field($_POST['content'] ?? '');
            $post_id = (int) ($_POST['post_id'] ?? 0);
            $provider = sanitize_text_field($_POST['provider'] ?? 'auto');

            if (empty($content)) {
                wp_send_json_error(array(
                    'message' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Prepare options for AI service manager
            $options = array();
            if ($provider !== 'auto') {
                $options['provider'] = $provider;
            }

            // Generate TOC using AI service manager
            $result = $this->ai_service_manager->generate_content('toc', $content, $post_id, $options);

            if ($result['success']) {
                // Save generated content as post meta
                if ($post_id > 0) {
                    update_post_meta($post_id, '_hmg_ai_toc', $result['content']);
                    update_post_meta($post_id, '_hmg_ai_toc_generated', current_time('mysql'));
                }

                // Get updated usage stats
                $updated_stats = $this->auth_service->get_spending_stats();

                wp_send_json_success(array(
                    'content' => $result['content'],
                    'message' => $result['message'] ?? __('Table of Contents generated successfully!', 'hmg-ai-blog-enhancer'),
                    'provider_used' => $result['provider_name'] ?? 'AI Service',
                    'tokens_used' => $result['tokens_used'] ?? 0,
                    'generation_time' => $result['generation_time'] ?? 0,
                    'cached' => $result['cached'] ?? false,
                    'usage' => array(
                        'spending' => array(
                            'used' => $updated_stats['monthly']['spent'],
                            'limit' => $updated_stats['monthly']['limit'],
                            'percentage' => $updated_stats['monthly']['percentage']
                        ),
                        'api_calls' => $updated_stats['monthly']['requests'],
                        'tokens' => $updated_stats['monthly']['tokens'],
                        'reset_date' => $updated_stats['reset_date']
                    )
                ));
            } else {
                wp_send_json_error(array(
                    'message' => $result['error']
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Error: ' . $e->getMessage()
            ));
        } catch (Error $e) {
            wp_send_json_error(array(
                'message' => 'Fatal Error: ' . $e->getMessage()
            ));
        }
    }

    /**
     * AJAX handler for generating audio
     * 
     * @since    1.0.0
     */
    public function ajax_generate_audio() {
        try {
            // Verify nonce
            if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
                wp_send_json_error(array(
                    'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Check user capabilities
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array(
                    'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
                ));
                return;
            }

            // Get post content
            $content = $_POST['content'] ?? '';  // Don't sanitize yet - we need to check for shortcodes
            $post_id = (int) ($_POST['post_id'] ?? 0);
            $voice = sanitize_text_field($_POST['voice'] ?? 'EXAVITQu4vr4xnSDxMaL');
            
            if (empty($content)) {
                wp_send_json_error(array(
                    'message' => __('No content provided for audio generation.', 'hmg-ai-blog-enhancer')
                ));
                return;
            }
            
            // Check if content has our shortcodes and process them
            $has_takeaways_shortcode = has_shortcode($content, 'hmg_ai_takeaways');
            $has_faq_shortcode = has_shortcode($content, 'hmg_ai_faq');
            $has_toc_shortcode = has_shortcode($content, 'hmg_ai_toc');
            $has_audio_shortcode = has_shortcode($content, 'hmg_ai_audio');
            
            // Process shortcodes to get the rendered content
            if ($has_takeaways_shortcode || $has_faq_shortcode || $has_toc_shortcode || $has_audio_shortcode) {
                // Ensure shortcodes are registered (they might not be in AJAX context)
                if (!shortcode_exists('hmg_ai_takeaways')) {
                    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-public.php';
                    $plugin_public = new HMG_AI_Public('hmg-ai-blog-enhancer', HMG_AI_BLOG_ENHANCER_VERSION);
                    $plugin_public->register_shortcodes();
                }
                
                // Temporarily save the post ID for shortcode context
                global $post;
                $original_post = $post;
                $post = get_post($post_id);
                
                // Apply shortcode processing
                $content = do_shortcode($content);
                
                // Restore original post
                $post = $original_post;
            }
            
            // Now strip HTML tags for audio
            $audio_content = wp_strip_all_tags($content);
            
            // Add title of the post at the beginning
            $post = get_post($post_id);
            if ($post) {
                $audio_content = $post->post_title . "\n\n" . $audio_content;
            }
            
            // Clean up the content for better audio
            // Remove excessive newlines but preserve paragraph breaks
            $audio_content = preg_replace('/\n{3,}/', "\n\n", $audio_content);
            // Remove multiple spaces (but preserve newlines)
            $audio_content = preg_replace('/[^\S\n]+/', ' ', $audio_content);
            // Remove spaces at the beginning and end of lines
            $audio_content = preg_replace('/^ +| +$/m', '', $audio_content);
            $audio_content = trim($audio_content);
            
            // Use the processed content for audio generation
            $content = $audio_content;
            
            // Load TTS service
            require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-tts-service.php';
            
            $tts_service = new HMG_AI_TTS_Service();
            
            // Generate audio with Eleven Labs
            $result = $tts_service->generate_audio($content, array(
                'post_id' => $post_id,
                'voice' => $voice
            ));
            
            if (isset($result['error']) && $result['error']) {
                wp_send_json_error(array(
                    'message' => $result['message'] ?? __('Failed to generate audio', 'hmg-ai-blog-enhancer')
                ));
                return;
            }
            
            // Save audio URL as post meta
            if ($post_id > 0 && isset($result['audio_url'])) {
                update_post_meta($post_id, '_hmg_ai_audio_url', $result['audio_url']);
                update_post_meta($post_id, '_hmg_ai_audio_generated', current_time('mysql'));
                update_post_meta($post_id, '_hmg_ai_audio_duration', $result['duration'] ?? array());
                update_post_meta($post_id, '_hmg_ai_audio_voice', $result['voice'] ?? '');
            }
            
            // Get usage stats for response
            $auth_service = new HMG_AI_Auth_Service();
            $usage_stats = $auth_service->get_usage_stats();
            $spending_stats = $auth_service->get_spending_stats();
            
            wp_send_json_success(array(
                'audio_url' => $result['audio_url'],
                'duration' => $result['duration'] ?? array(),
                'voice' => $result['voice'] ?? '',
                'provider' => $result['provider'] ?? '',
                'message' => __('Audio generated successfully!', 'hmg-ai-blog-enhancer'),
                'usage' => array(
                    'api_calls' => array(
                        'used' => $usage_stats['api_calls_used'],
                        'limit' => $usage_stats['api_calls_limit'],
                        'percentage' => round(($usage_stats['api_calls_used'] / max($usage_stats['api_calls_limit'], 1)) * 100, 1)
                    ),
                    'tokens' => array(
                        'used' => $usage_stats['tokens_used'],
                        'limit' => $usage_stats['tokens_limit'],
                        'percentage' => round(($usage_stats['tokens_used'] / max($usage_stats['tokens_limit'], 1)) * 100, 1)
                    ),
                    'spending' => array(
                        'used' => $spending_stats['monthly']['spent'],
                        'limit' => $spending_stats['monthly']['limit'],
                        'percentage' => $spending_stats['monthly']['percentage']
                    )
                )
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('An error occurred while generating audio. Please try again.', 'hmg-ai-blog-enhancer')
            ));
        }
    }

    /**
     * AJAX handler for validating API key
     *
     * @since    1.0.0
     */
    public function ajax_validate_api_key() {
        // Verify nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
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
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_die(__('Security check failed', 'hmg-ai-blog-enhancer'));
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'hmg-ai-blog-enhancer'));
        }

        // Get spending statistics from auth service
        $spending_stats = $this->auth_service->get_spending_stats();
        
        // Format for JavaScript consumption
        $formatted_stats = array(
            'spending' => array(
                'used' => $spending_stats['monthly']['spent'],
                'limit' => $spending_stats['monthly']['limit'],
                'percentage' => $spending_stats['monthly']['percentage']
            ),
            'api_calls' => $spending_stats['monthly']['requests'],
            'tokens' => $spending_stats['monthly']['tokens'],
            'reset_date' => $spending_stats['reset_date']
        );
        
        wp_send_json_success($formatted_stats);
    }

    /**
     * AJAX handler for testing AI providers
     *
     * @since    1.0.0
     */
    public function ajax_test_ai_providers() {
        // Verify nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
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
     * AJAX handler for testing a single AI provider
     *
     * @since    1.0.0
     */
    public function ajax_test_single_provider() {
        // Verify nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
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

        $provider = sanitize_text_field($_POST['provider'] ?? '');
        
        if (empty($provider)) {
            wp_send_json_error(array(
                'message' => __('No provider specified', 'hmg-ai-blog-enhancer')
            ));
        }

        try {
            // Get the provider instance
            $provider_instance = null;
            
            switch ($provider) {
                case 'gemini':
                    $provider_instance = new HMG_AI_Gemini_Service();
                    break;
                case 'openai':
                    $provider_instance = new HMG_AI_OpenAI_Service();
                    break;
                case 'claude':
                    $provider_instance = new HMG_AI_Claude_Service();
                    break;
                default:
                    wp_send_json_error(array(
                        'message' => __('Invalid provider', 'hmg-ai-blog-enhancer')
                    ));
                    return;
            }

            // Test the connection
            if (method_exists($provider_instance, 'test_connection')) {
                $result = $provider_instance->test_connection();
                
                if ($result['success']) {
                    // Also try a simple generation to fully test
                    $test_content = "This is a test of the HMG AI Blog Enhancer plugin.";
                    $test_response = null;
                    
                    if (method_exists($provider_instance, 'generate_takeaways')) {
                        try {
                            $test_response = $provider_instance->generate_takeaways($test_content, 1);
                        } catch (Exception $e) {
                            // Generation failed but connection worked
                            $test_response = array('note' => 'Connection successful but generation test failed: ' . $e->getMessage());
                        }
                    }
                    
                    wp_send_json_success(array(
                        'success' => true,
                        'message' => $result['message'] ?? __('Provider connected successfully', 'hmg-ai-blog-enhancer'),
                        'response' => $test_response
                    ));
                } else {
                    wp_send_json_error(array(
                        'message' => $result['message'] ?? __('Provider connection failed', 'hmg-ai-blog-enhancer')
                    ));
                }
            } else {
                wp_send_json_error(array(
                    'message' => __('Provider does not support connection testing', 'hmg-ai-blog-enhancer')
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(__('Error testing provider: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())
            ));
        }
    }

    /**
     * AJAX handler for deleting AI content
     *
     * @since    1.0.0
     */
    public function ajax_delete_content() {
        // Verify nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
            return;
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
            ));
            return;
        }

        // Get the content type and post ID
        $type = sanitize_text_field($_POST['type'] ?? '');
        $post_id = (int) ($_POST['post_id'] ?? 0);

        if (empty($type) || $post_id <= 0) {
            wp_send_json_error(array(
                'message' => __('Invalid request parameters.', 'hmg-ai-blog-enhancer')
            ));
            return;
        }

        // Delete the content based on type
        $deleted = false;
        switch ($type) {
            case 'takeaways':
                delete_post_meta($post_id, '_hmg_ai_takeaways');
                delete_post_meta($post_id, '_hmg_ai_takeaways_generated');
                $deleted = true;
                break;
            case 'faq':
                delete_post_meta($post_id, '_hmg_ai_faq');
                delete_post_meta($post_id, '_hmg_ai_faq_generated');
                $deleted = true;
                break;
            case 'toc':
                delete_post_meta($post_id, '_hmg_ai_toc');
                delete_post_meta($post_id, '_hmg_ai_toc_generated');
                $deleted = true;
                break;
            case 'audio':
                delete_post_meta($post_id, '_hmg_ai_audio_url');
                delete_post_meta($post_id, '_hmg_ai_audio_generated');
                delete_post_meta($post_id, '_hmg_ai_audio_duration');
                delete_post_meta($post_id, '_hmg_ai_audio_voice');
                delete_post_meta($post_id, '_hmg_ai_audio_size');
                $deleted = true;
                break;
            default:
                wp_send_json_error(array(
                    'message' => __('Invalid content type.', 'hmg-ai-blog-enhancer')
                ));
                return;
        }

        if ($deleted) {
            wp_send_json_success(array(
                'message' => sprintf(__('%s deleted successfully!', 'hmg-ai-blog-enhancer'), ucfirst($type))
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to delete content.', 'hmg-ai-blog-enhancer')
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
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
            return;
        }

        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => __('Insufficient permissions', 'hmg-ai-blog-enhancer')
            ));
            return;
        }

        $post_id = (int) ($_POST['post_id'] ?? 0);
        $content_type = sanitize_text_field($_POST['content_type'] ?? '');
        $content = $_POST['content'] ?? ''; // Don't use wp_kses_post here as it might strip valid content

        // Allow empty content (user might be clearing it)
        if (!$post_id || !$content_type) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters (post_id or content_type).', 'hmg-ai-blog-enhancer'),
                'debug' => array(
                    'post_id' => $post_id,
                    'content_type' => $content_type
                )
            ));
            return;
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
                    'message' => __('Invalid content type.', 'hmg-ai-blog-enhancer'),
                    'debug' => array(
                        'content_type' => $content_type
                    )
                ));
                return;
        }

        // Sanitize content appropriately based on type
        // For takeaways, we may need to preserve newlines and basic formatting
        if ($content_type === 'takeaways') {
            // Preserve newlines and basic formatting for takeaways
            $content = sanitize_textarea_field($content);
        } else {
            // For FAQ and TOC, use wp_kses_post
            $content = wp_kses_post($content);
        }

        // Update post meta (returns false if the value is the same, which is ok)
        $old_value = get_post_meta($post_id, $meta_key, true);
        $result = update_post_meta($post_id, $meta_key, $content);

        // If result is false, check if the value was already the same (which is fine)
        if ($result === false && $old_value !== $content) {
            wp_send_json_error(array(
                'message' => __('Failed to save content.', 'hmg-ai-blog-enhancer'),
                'debug' => array(
                    'old_value_type' => gettype($old_value),
                    'new_value_type' => gettype($content),
                    'meta_key' => $meta_key
                )
            ));
        } else {
            wp_send_json_success(array(
                'message' => sprintf(__('%s content saved successfully!', 'hmg-ai-blog-enhancer'), ucfirst($content_type)),
                'content' => $content
            ));
        }
    }
    
    /**
     * AJAX handler for refreshing Eleven Labs voices
     *
     * @since    1.0.0
     */
    public function ajax_refresh_voices() {
        
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to refresh voices', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        // Check if API key is configured
        $api_key = get_option('hmg_ai_elevenlabs_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => __('Eleven Labs API key not configured. Please add your API key in settings.', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        try {
            // Get TTS service and clear cache
            if (!class_exists('HMG_AI_TTS_Service')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-tts-service.php';
            }
            
            $tts_service = new HMG_AI_TTS_Service();
            
            // Clear the voice cache to force a fresh fetch
            $tts_service->clear_voice_cache();
            
            // Fetch fresh voices from API
            $voices = $tts_service->get_available_voices();
            
            
            if (!empty($voices)) {
                wp_send_json_success(array(
                    'message' => sprintf(__('Successfully fetched %d voices from Eleven Labs', 'hmg-ai-blog-enhancer'), count($voices)),
                    'voices' => $voices,
                    'count' => count($voices)
                ));
            } else {
                wp_send_json_error(array(
                    'message' => __('Failed to fetch voices from Eleven Labs. Please check your API key and network connection.', 'hmg-ai-blog-enhancer')
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(__('Error: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())
            ));
        }
    }
    
    /**
     * AJAX handler for analyzing brand voice
     *
     * @since    1.2.0
     */
    public function ajax_analyze_brand_voice() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to analyze brand voice', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        // Get post count parameter
        $post_count = isset($_POST['post_count']) ? intval($_POST['post_count']) : 10;
        $post_count = max(1, min(100, $post_count)); // Limit between 1 and 100
        
        try {
            // Load Context Analyzer
            if (!class_exists('HMG_AI_Context_Analyzer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-context-analyzer.php';
            }
            
            $analyzer = new HMG_AI_Context_Analyzer();
            $result = $analyzer->analyze_website_content($post_count);
            
            if ($result['success']) {
                wp_send_json_success(array(
                    'message' => sprintf(
                        __('Successfully analyzed %d posts and created brand profile', 'hmg-ai-blog-enhancer'),
                        $result['posts_analyzed']
                    ),
                    'brand_profile' => $result['brand_profile']
                ));
            } else {
                wp_send_json_error(array(
                    'message' => $result['message'] ?? __('Failed to analyze content', 'hmg-ai-blog-enhancer')
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(__('Analysis failed: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())
            ));
        }
    }
    
    /**
     * AJAX handler for clearing brand profile
     *
     * @since    1.2.0
     */
    public function ajax_clear_brand_profile() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to clear brand profile', 'hmg-ai-blog-enhancer')
            ));
            return;
        }
        
        try {
            // Load Context Analyzer
            if (!class_exists('HMG_AI_Context_Analyzer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-context-analyzer.php';
            }
            
            $analyzer = new HMG_AI_Context_Analyzer();
            $analyzer->clear_brand_profile();
            
            wp_send_json_success(array(
                'message' => __('Brand profile cleared successfully', 'hmg-ai-blog-enhancer')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(__('Failed to clear profile: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())
            ));
        }
    }
    
    /**
     * AJAX handler for analyzing SEO
     *
     * @since    1.3.0
     */
    public function ajax_analyze_seo() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        try {
            // Load SEO Optimizer
            if (!class_exists('HMG_AI_SEO_Optimizer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
            }
            
            $seo_optimizer = new HMG_AI_SEO_Optimizer();
            $result = $seo_optimizer->optimize_content($content, $title, $post_id);
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error(array('message' => $result['error'] ?? __('SEO analysis failed', 'hmg-ai-blog-enhancer')));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => sprintf(__('Error: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())));
        }
    }
    
    /**
     * AJAX handler for auto-optimizing SEO
     *
     * @since    1.3.0
     */
    public function ajax_optimize_seo() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? array());
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        try {
            // Load SEO Optimizer
            if (!class_exists('HMG_AI_SEO_Optimizer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
            }
            
            $seo_optimizer = new HMG_AI_SEO_Optimizer();
            $result = $seo_optimizer->optimize_content($content, $title, $post_id, array('keywords' => $keywords));
            
            if ($result['success']) {
                // Also update post content if optimized
                if ($result['optimized_content'] !== $content) {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $result['optimized_content']
                    ));
                }
                
                wp_send_json_success($result);
            } else {
                wp_send_json_error(array('message' => $result['error'] ?? __('Optimization failed', 'hmg-ai-blog-enhancer')));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => sprintf(__('Error: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())));
        }
    }
    
    /**
     * AJAX handler for generating meta description
     *
     * @since    1.3.0
     */
    public function ajax_generate_meta_description() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        
        try {
            // Use AI to generate meta description
            if (class_exists('HMG_AI_Service_Manager')) {
                $ai_service = new HMG_AI_Service_Manager();
                
                $prompt = sprintf(
                    "Title: %s\n\nContent: %s\n\nGenerate a compelling meta description (max 155 characters) that summarizes this content and encourages clicks from search results.",
                    $title,
                    substr(wp_strip_all_tags($content), 0, 1000)
                );
                
                $result = $ai_service->generate_content('summary', $prompt, $post_id);
                
                if ($result['success']) {
                    $meta_desc = $result['content'];
                    if (strlen($meta_desc) > 155) {
                        $meta_desc = substr($meta_desc, 0, 152) . '...';
                    }
                    
                    // Save to post meta
                    update_post_meta($post_id, '_hmg_ai_meta_description', $meta_desc);
                    
                    wp_send_json_success(array(
                        'meta_description' => $meta_desc
                    ));
                } else {
                    wp_send_json_error(array('message' => $result['error'] ?? __('Failed to generate meta description', 'hmg-ai-blog-enhancer')));
                }
            } else {
                wp_send_json_error(array('message' => __('AI service not available', 'hmg-ai-blog-enhancer')));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => sprintf(__('Error: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())));
        }
    }
    
    /**
     * AJAX handler for extracting keywords
     *
     * @since    1.3.0
     */
    public function ajax_extract_keywords() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        
        try {
            // Load SEO Optimizer
            if (!class_exists('HMG_AI_SEO_Optimizer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
            }
            
            $seo_optimizer = new HMG_AI_SEO_Optimizer();
            
            // Use reflection to access private method (or we could make it public)
            $reflection = new ReflectionClass($seo_optimizer);
            $method = $reflection->getMethod('extract_keywords');
            $method->setAccessible(true);
            
            $keywords = $method->invoke($seo_optimizer, $title . ' ' . wp_strip_all_tags($content));
            
            // Save to post meta
            if ($post_id) {
                update_post_meta($post_id, '_hmg_ai_keywords', $keywords);
            }
            
            wp_send_json_success(array(
                'keywords' => $keywords
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => sprintf(__('Error: %s', 'hmg-ai-blog-enhancer'), $e->getMessage())));
        }
    }
    
    /**
     * AJAX handler for saving SEO data
     *
     * @since    1.3.0
     */
    public function ajax_save_seo_data() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Save all SEO fields
        if (isset($_POST['meta_description'])) {
            update_post_meta($post_id, '_hmg_ai_meta_description', sanitize_textarea_field($_POST['meta_description']));
        }
        
        if (isset($_POST['seo_title'])) {
            update_post_meta($post_id, '_hmg_ai_seo_title', sanitize_text_field($_POST['seo_title']));
        }
        
        if (isset($_POST['keywords'])) {
            $keywords = array_map('sanitize_text_field', (array)$_POST['keywords']);
            update_post_meta($post_id, '_hmg_ai_keywords', $keywords);
        }
        
        if (isset($_POST['enable_schema'])) {
            $enable_schema = (bool)$_POST['enable_schema'];
            if ($enable_schema) {
                // Generate and save schema markup
                if (!class_exists('HMG_AI_SEO_Optimizer')) {
                    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
                }
                
                $seo_optimizer = new HMG_AI_SEO_Optimizer();
                $reflection = new ReflectionClass($seo_optimizer);
                $method = $reflection->getMethod('generate_schema_markup');
                $method->setAccessible(true);
                
                $content = get_post_field('post_content', $post_id);
                $title = get_the_title($post_id);
                $schema = $method->invoke($seo_optimizer, $content, $title, $post_id);
                
                update_post_meta($post_id, '_hmg_ai_schema_markup', $schema);
            } else {
                delete_post_meta($post_id, '_hmg_ai_schema_markup');
            }
        }
        
        wp_send_json_success(array(
            'message' => __('SEO data saved successfully', 'hmg-ai-blog-enhancer')
        ));
    }
    
    /**
     * AJAX handler for saving performance settings
     *
     * @since    1.4.0
     */
    public function ajax_save_performance_settings() {
        // Check nonce
        if (!check_ajax_referer('hmg_ai_performance_settings', 'hmg_ai_performance_nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Get current options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        
        // Update performance settings
        $options['minify_css'] = isset($_POST['minify_css']);
        $options['minify_js'] = isset($_POST['minify_js']);
        $options['concatenate_assets'] = isset($_POST['concatenate_assets']);
        $options['enable_lazy_load'] = isset($_POST['enable_lazy_load']);
        $options['enable_async_js'] = isset($_POST['enable_async_js']);
        $options['enable_object_cache'] = isset($_POST['enable_object_cache']);
        $options['enable_fragment_cache'] = isset($_POST['enable_fragment_cache']);
        $options['enable_critical_css'] = isset($_POST['enable_critical_css']);
        $options['browser_cache_ttl'] = intval($_POST['browser_cache_ttl'] ?? 86400);
        $options['api_cache_ttl'] = intval($_POST['api_cache_ttl'] ?? 3600);
        $options['cdn_enabled'] = isset($_POST['cdn_enabled']);
        $options['cdn_url'] = sanitize_url($_POST['cdn_url'] ?? '');
        $options['max_concurrent_loads'] = intval($_POST['max_concurrent_loads'] ?? 2);
        
        // Save options
        update_option('hmg_ai_blog_enhancer_options', $options);
        
        wp_send_json_success(array(
            'message' => __('Performance settings saved successfully', 'hmg-ai-blog-enhancer')
        ));
    }
    
    /**
     * AJAX handler for optimizing database
     *
     * @since    1.4.0
     */
    public function ajax_optimize_database() {
        // Check nonce
        if (!check_ajax_referer('hmg_ai_performance', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        try {
            // Load Performance Optimizer
            if (!class_exists('HMG_AI_Performance_Optimizer')) {
                require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-performance-optimizer.php';
            }
            
            $performance = new HMG_AI_Performance_Optimizer();
            $performance->optimize_database('all');
            
            wp_send_json_success(array(
                'message' => __('Database optimized successfully', 'hmg-ai-blog-enhancer')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for clearing all caches
     *
     * @since    1.4.0
     */
    public function ajax_clear_all_caches() {
        // Check nonce
        if (!check_ajax_referer('hmg_ai_performance', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Check permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        try {
            global $wpdb;
            
            // Clear content cache
            $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
            $wpdb->query("TRUNCATE TABLE $cache_table");
            
            // Clear transient cache
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_hmg_ai_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_hmg_ai_%'");
            
            // Clear fragment cache
            delete_transient('hmg_ai_fragment_cache');
            
            // Clear minified assets cache
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/hmg-ai-cache';
            if (file_exists($cache_dir)) {
                $files = glob($cache_dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            
            wp_send_json_success(array(
                'message' => __('All caches cleared successfully', 'hmg-ai-blog-enhancer')
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for processing shortcodes (for lazy loading)
     *
     * @since    1.4.0
     */
    public function ajax_process_shortcode() {
        // Check nonce
        if (!check_ajax_referer('hmg-ai-ajax-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');
        
        if (empty($shortcode)) {
            wp_send_json_error(array('message' => __('No shortcode provided', 'hmg-ai-blog-enhancer')));
            return;
        }
        
        // Process the shortcode
        $html = do_shortcode($shortcode);
        
        wp_send_json_success(array(
            'html' => $html
        ));
    }
} 