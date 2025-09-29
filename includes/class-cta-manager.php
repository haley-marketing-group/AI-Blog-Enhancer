<?php
/**
 * CTA Manager Class
 *
 * Handles all CTA functionality including templates, custom CTAs, and display
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

class HMG_AI_CTA_Manager {

    /**
     * The ID of this plugin.
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Pre-built CTA templates
     *
     * @since    1.1.0
     * @access   private
     * @var      array    $templates    Available CTA templates
     */
    private $templates = [
        'search_jobs' => 'Search Jobs',
        'job_alerts' => 'Sign Up for Job Alerts',
        'submit_resume' => 'Send Us Your Resume',
        'talent_showcase' => 'Check Out Our Talent Showcase',
        'top_talent' => 'View Our Top Talent',
        'talent_alerts' => 'Sign Up for Talent Alerts',
        'contact_us' => 'Contact Us',
        'request_employee' => 'Request an Employee',
        'follow_us' => 'Follow Us',
        'join_team' => 'Join Our Team'
    ];

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.1.0
     * @param    string    $plugin_name    The name of the plugin.
     * @param    string    $version       The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Get all CTA templates
     *
     * @since    1.1.0
     * @return   array    Available CTA templates
     */
    public function get_templates() {
        return $this->templates;
    }

    /**
     * Get CTA settings for a specific template
     *
     * @since    1.1.0
     * @param    string    $template    Template ID
     * @return   array     CTA settings
     */
    public function get_template_settings($template) {
        $option_name = 'hmg_ai_cta_' . $template;
        $defaults = $this->get_default_settings($template);
        $saved = get_option($option_name, []);
        
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Save CTA template settings
     *
     * @since    1.1.0
     * @param    string    $template    Template ID
     * @param    array     $settings    Settings to save
     * @return   bool      Success status
     */
    public function save_template_settings($template, $settings) {
        $option_name = 'hmg_ai_cta_' . $template;
        return update_option($option_name, $settings);
    }

    /**
     * Get default settings for a template
     *
     * @since    1.1.0
     * @param    string    $template    Template ID
     * @return   array     Default settings
     */
    private function get_default_settings($template) {
        $defaults = [
            'active' => false,
            'title' => $this->templates[$template] ?? '',
            'content' => '',
            'button' => $this->get_default_button_text($template),
            'url' => '',
            'target' => false,
            'button_class' => 'hmg-cta-button hmg-cta-btn-default',
            'img' => '',
            'img_align' => 'alignleft',
            'override_defaults' => false,
            'box_color' => '#333333',
            'box_bg' => '#f7f7f7',
            'box_border_color' => '#dddddd',
            'box_border_width' => '1px',
            'box_border_rad' => '4px',
            'box_pad' => '20px',
            'custom_css' => ''
        ];

        return $defaults;
    }

    /**
     * Get default button text for templates
     *
     * @since    1.1.0
     * @param    string    $template    Template ID
     * @return   string    Default button text
     */
    private function get_default_button_text($template) {
        $button_texts = [
            'search_jobs' => 'Search Jobs',
            'job_alerts' => 'Sign Up Now',
            'submit_resume' => 'Submit Resume',
            'talent_showcase' => 'View Showcase',
            'top_talent' => 'View Talent',
            'talent_alerts' => 'Get Alerts',
            'contact_us' => 'Contact Us',
            'request_employee' => 'Request Now',
            'follow_us' => 'Follow Us',
            'join_team' => 'Join Our Team'
        ];

        return $button_texts[$template] ?? 'Learn More';
    }

    /**
     * Get global CTA settings
     *
     * @since    1.1.0
     * @return   array    Global settings
     */
    public function get_global_settings() {
        $defaults = [
            'box_color' => '#333333',
            'box_bg' => '#f7f7f7',
            'box_border_color' => '#dddddd',
            'box_border_width' => '1px',
            'box_border_rad' => '4px',
            'box_pad' => '20px'
        ];

        $saved = get_option('hmg_ai_cta_global_settings', []);
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Save global CTA settings
     *
     * @since    1.1.0
     * @param    array    $settings    Settings to save
     * @return   bool     Success status
     */
    public function save_global_settings($settings) {
        return update_option('hmg_ai_cta_global_settings', $settings);
    }

    /**
     * Render CTA for a post
     *
     * @since    1.1.0
     * @param    int      $post_id    Post ID
     * @return   string   HTML output
     */
    public function render_cta($post_id) {
        $cta_type = get_post_meta($post_id, '_hmg_ai_cta_type', true);
        
        if (empty($cta_type) || $cta_type === 'none') {
            return '';
        }

        $settings = [];
        
        if ($cta_type === 'custom') {
            // Get custom CTA settings from post meta
            $settings = [
                'active' => true,
                'title' => get_post_meta($post_id, '_hmg_ai_cta_title', true),
                'content' => get_post_meta($post_id, '_hmg_ai_cta_content', true),
                'button' => get_post_meta($post_id, '_hmg_ai_cta_button_text', true),
                'url' => get_post_meta($post_id, '_hmg_ai_cta_button_url', true),
                'target' => get_post_meta($post_id, '_hmg_ai_cta_button_target', true),
                'button_class' => get_post_meta($post_id, '_hmg_ai_cta_button_class', true) ?: 'hmg-cta-button hmg-cta-btn-default',
                'img' => get_post_meta($post_id, '_hmg_ai_cta_img', true),
                'img_align' => get_post_meta($post_id, '_hmg_ai_cta_img_align', true) ?: 'alignleft',
                'override_defaults' => get_post_meta($post_id, '_hmg_ai_cta_override_defaults', true),
                'box_color' => get_post_meta($post_id, '_hmg_ai_cta_box_color', true),
                'box_bg' => get_post_meta($post_id, '_hmg_ai_cta_box_bg', true),
                'box_border_color' => get_post_meta($post_id, '_hmg_ai_cta_box_border_color', true),
                'box_border_width' => get_post_meta($post_id, '_hmg_ai_cta_box_border_width', true),
                'box_border_rad' => get_post_meta($post_id, '_hmg_ai_cta_box_border_rad', true),
                'box_pad' => get_post_meta($post_id, '_hmg_ai_cta_box_pad', true)
            ];
        } else {
            // Get template settings
            $settings = $this->get_template_settings($cta_type);
        }

        if (empty($settings['active']) && $cta_type !== 'custom') {
            return '';
        }

        // Get custom CSS for this post
        $custom_css = get_post_meta($post_id, '_hmg_ai_cta_custom_css', true);

        return $this->generate_cta_html($settings, $post_id, $custom_css);
    }

    /**
     * Generate CTA HTML
     *
     * @since    1.1.0
     * @param    array    $settings      CTA settings
     * @param    int      $post_id       Post ID
     * @param    string   $custom_css    Custom CSS
     * @return   string   HTML output
     */
    private function generate_cta_html($settings, $post_id, $custom_css = '') {
        $global_settings = $this->get_global_settings();
        
        // Use global settings unless overridden
        if (empty($settings['override_defaults'])) {
            $settings = wp_parse_args([
                'title' => $settings['title'],
                'content' => $settings['content'],
                'button' => $settings['button'],
                'url' => $settings['url'],
                'target' => $settings['target'],
                'button_class' => $settings['button_class'],
                'img' => $settings['img'],
                'img_align' => $settings['img_align']
            ], $global_settings);
        }

        $html = '';
        
        // Add custom CSS if provided
        if (!empty($custom_css)) {
            $html .= '<style>#hmg-cta-box-' . $post_id . ' { ' . esc_html($custom_css) . ' }</style>';
        }

        // Add inline styles
        $inline_styles = '';
        if (!empty($settings['box_color'])) {
            $inline_styles .= 'color: ' . esc_attr($settings['box_color']) . ';';
        }
        if (!empty($settings['box_bg'])) {
            $inline_styles .= 'background-color: ' . esc_attr($settings['box_bg']) . ';';
        }
        if (!empty($settings['box_border_color'])) {
            $inline_styles .= 'border-color: ' . esc_attr($settings['box_border_color']) . ';';
        }
        if (!empty($settings['box_border_width'])) {
            $inline_styles .= 'border-width: ' . esc_attr($settings['box_border_width']) . ';';
        }
        if (!empty($settings['box_border_rad'])) {
            $inline_styles .= 'border-radius: ' . esc_attr($settings['box_border_rad']) . ';';
        }
        if (!empty($settings['box_pad'])) {
            $inline_styles .= 'padding: ' . esc_attr($settings['box_pad']) . ';';
        }

        // Build CTA HTML
        $background_class = '';
        $background_style = '';
        if (!empty($settings['img']) && $settings['img_align'] === 'background') {
            $background_class = ' hmg-background-image';
            $background_style = ' style="background-image: url(' . esc_url($settings['img']) . ');"';
        }

        $html .= '<div class="hmg-cta-box' . $background_class . ' clearfix" id="hmg-cta-box-' . $post_id . '"';
        if (!empty($inline_styles) && empty($background_style)) {
            $html .= ' style="' . $inline_styles . '"';
        } elseif (!empty($background_style)) {
            $html .= $background_style;
        }
        $html .= '>';
        
        $html .= '<div class="hmg-cta-box-flex-wrapper hmg-' . esc_attr($settings['img_align']) . '">';
        
        // Add image if not background
        if (!empty($settings['img']) && $settings['img_align'] !== 'background') {
            $html .= '<img src="' . esc_url($settings['img']) . '" alt="" class="hmg-cta-box-image" />';
        }
        
        $html .= '<div class="hmg-cta-box-content-wrapper">';
        
        // Add title
        if (!empty($settings['title'])) {
            $html .= '<div class="hmg-cta-box-header">';
            $html .= '<h3 class="hmg-cta-box-title">' . esc_html($settings['title']) . '</h3>';
            $html .= '</div>';
        }
        
        // Add content
        if (!empty($settings['content'])) {
            $html .= '<div class="hmg-cta-box-content clearfix">';
            $html .= wpautop(wp_kses_post($settings['content']));
            $html .= '</div>';
        }
        
        // Add button
        if (!empty($settings['button']) && !empty($settings['url'])) {
            $target = !empty($settings['target']) ? ' target="_blank" rel="noopener noreferrer"' : '';
            $html .= '<div class="hmg-cta-box-footer">';
            $html .= '<a class="' . esc_attr($settings['button_class']) . '" href="' . esc_url($settings['url']) . '"' . $target . '>';
            $html .= esc_html($settings['button']);
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // .hmg-cta-box-content-wrapper
        $html .= '</div>'; // .hmg-cta-box-flex-wrapper
        $html .= '</div>'; // .hmg-cta-box
        
        return $html;
    }

    /**
     * Filter content to add CTA
     *
     * @since    1.1.0
     * @param    string    $content    Post content
     * @return   string    Modified content
     */
    public function filter_content($content) {
        if (!is_single() || !is_main_query()) {
            return $content;
        }

        $post_id = get_the_ID();
        $cta_html = $this->render_cta($post_id);
        
        if (!empty($cta_html)) {
            $content .= $cta_html;
        }

        return $content;
    }
}
