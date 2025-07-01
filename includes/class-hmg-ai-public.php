<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Public {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'public/css/hmg-ai-public.css',
            array(),
            $this->version,
            'all'
        );

        // Load Haley Marketing brand fonts for public content
        wp_enqueue_style(
            $this->plugin_name . '-fonts',
            'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
            array(),
            $this->version
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'public/js/hmg-ai-public.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize script for public interactions
        wp_localize_script(
            $this->plugin_name,
            'hmg_ai_public',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hmg_ai_public_nonce'),
                'plugin_url' => HMG_AI_BLOG_ENHANCER_PLUGIN_URL,
            )
        );
    }

    /**
     * Register shortcodes for the plugin
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('hmg_ai_takeaways', array($this, 'shortcode_takeaways'));
        add_shortcode('hmg_ai_faq', array($this, 'shortcode_faq'));
        add_shortcode('hmg_ai_toc', array($this, 'shortcode_toc'));
        add_shortcode('hmg_ai_audio', array($this, 'shortcode_audio'));
    }

    /**
     * Maybe add AI content to post content automatically
     *
     * @since    1.0.0
     * @param    string    $content    The post content.
     * @return   string               The modified content.
     */
    public function maybe_add_ai_content($content) {
        if (!is_single() && !is_page()) {
            return $content;
        }

        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $post_id = get_the_ID();

        // Check if auto-generation is enabled
        if (!empty($options['auto_generate_takeaways'])) {
            $takeaways = get_post_meta($post_id, '_hmg_ai_takeaways', true);
            if (!empty($takeaways)) {
                $content = $this->inject_takeaways($content, $takeaways);
            }
        }

        if (!empty($options['auto_generate_faq'])) {
            $faq = get_post_meta($post_id, '_hmg_ai_faq', true);
            if (!empty($faq)) {
                $content = $this->inject_faq($content, $faq);
            }
        }

        if (!empty($options['auto_generate_toc'])) {
            $toc = get_post_meta($post_id, '_hmg_ai_toc', true);
            if (!empty($toc)) {
                $content = $this->inject_toc($content, $toc);
            }
        }

        return $content;
    }

    /**
     * Register REST API routes
     *
     * @since    1.0.0
     */
    public function register_rest_routes() {
        register_rest_route('hmg-ai/v1', '/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_generate_content'),
            'permission_callback' => array($this, 'rest_permission_check'),
        ));
    }

    /**
     * Add structured data to the page head
     *
     * @since    1.0.0
     */
    public function add_structured_data() {
        if (is_single() || is_page()) {
            $post_id = get_the_ID();
            $faq_data = get_post_meta($post_id, '_hmg_ai_faq_structured', true);
            
            if (!empty($faq_data)) {
                echo '<script type="application/ld+json">' . wp_json_encode($faq_data) . '</script>';
            }
        }
    }

    /**
     * Takeaways shortcode handler
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string           The shortcode output.
     */
    public function shortcode_takeaways($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'default',
        ), $atts, 'hmg_ai_takeaways');

        $takeaways = get_post_meta($atts['post_id'], '_hmg_ai_takeaways', true);
        
        if (empty($takeaways)) {
            return '';
        }

        ob_start();
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/takeaways-template.php';
        return ob_get_clean();
    }

    /**
     * FAQ shortcode handler
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string           The shortcode output.
     */
    public function shortcode_faq($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'accordion',
        ), $atts, 'hmg_ai_faq');

        $faq = get_post_meta($atts['post_id'], '_hmg_ai_faq', true);
        
        if (empty($faq)) {
            return '';
        }

        ob_start();
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/faq-template.php';
        return ob_get_clean();
    }

    /**
     * Table of Contents shortcode handler
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string           The shortcode output.
     */
    public function shortcode_toc($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'numbered',
        ), $atts, 'hmg_ai_toc');

        $toc = get_post_meta($atts['post_id'], '_hmg_ai_toc', true);
        
        if (empty($toc)) {
            return '';
        }

        ob_start();
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/toc-template.php';
        return ob_get_clean();
    }

    /**
     * Audio player shortcode handler
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string           The shortcode output.
     */
    public function shortcode_audio($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'player',
        ), $atts, 'hmg_ai_audio');

        $audio_url = get_post_meta($atts['post_id'], '_hmg_ai_audio_url', true);
        
        if (empty($audio_url)) {
            return '';
        }

        ob_start();
        include HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/audio-player-template.php';
        return ob_get_clean();
    }

    /**
     * REST API content generation endpoint
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request.
     * @return   WP_REST_Response              The REST response.
     */
    public function rest_generate_content($request) {
        $type = $request->get_param('type');
        $post_id = $request->get_param('post_id');
        $content = $request->get_param('content');

        // Placeholder implementation
        return new WP_REST_Response(array(
            'success' => true,
            'data' => array(
                'type' => $type,
                'content' => 'Generated ' . $type . ' content for post ' . $post_id,
            )
        ), 200);
    }

    /**
     * REST API permission check
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request.
     * @return   bool                         Whether the user has permission.
     */
    public function rest_permission_check($request) {
        return current_user_can('edit_posts');
    }

    /**
     * Inject takeaways into content
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $content      The post content.
     * @param    string    $takeaways    The takeaways content.
     * @return   string                  The modified content.
     */
    private function inject_takeaways($content, $takeaways) {
        // Insert after first paragraph
        $paragraphs = explode('</p>', $content);
        if (count($paragraphs) > 1) {
            $paragraphs[0] .= '</p>' . $takeaways;
            return implode('</p>', $paragraphs);
        }
        
        return $takeaways . $content;
    }

    /**
     * Inject FAQ into content
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $content    The post content.
     * @param    string    $faq       The FAQ content.
     * @return   string               The modified content.
     */
    private function inject_faq($content, $faq) {
        // Append to end of content
        return $content . $faq;
    }

    /**
     * Inject table of contents into content
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $content    The post content.
     * @param    string    $toc       The TOC content.
     * @return   string               The modified content.
     */
    private function inject_toc($content, $toc) {
        // Insert at the beginning
        return $toc . $content;
    }
} 