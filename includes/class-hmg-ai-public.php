<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the public-facing stylesheet, JavaScript, and shortcodes.
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

        // Load Haley Marketing brand fonts
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

        // Localize script for AJAX calls if needed
        wp_localize_script($this->plugin_name, 'hmg_ai_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hmg_ai_public_nonce')
        ));
    }

    /**
     * Register all shortcodes
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('hmg_ai_takeaways', array($this, 'render_takeaways_shortcode'));
        add_shortcode('hmg_ai_faq', array($this, 'render_faq_shortcode'));
        add_shortcode('hmg_ai_toc', array($this, 'render_toc_shortcode'));
        add_shortcode('hmg_ai_audio', array($this, 'render_audio_shortcode'));
    }

    /**
     * Render takeaways shortcode
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            Rendered HTML.
     */
    public function render_takeaways_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'default'
        ), $atts, 'hmg_ai_takeaways');

        $post_id = (int) $atts['post_id'];
        $style = sanitize_text_field($atts['style']);

        // Get generated takeaways from post meta
        $takeaways = get_post_meta($post_id, '_hmg_ai_takeaways', true);

        if (empty($takeaways)) {
            return '<!-- No takeaways generated yet -->';
        }

        // Parse takeaways data
        $takeaways_data = $this->parse_takeaways_data($takeaways);
        
        // Load template
        ob_start();
        include(HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/takeaways-template.php');
        return ob_get_clean();
    }

    /**
     * Render FAQ shortcode
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            Rendered HTML.
     */
    public function render_faq_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'accordion'
        ), $atts, 'hmg_ai_faq');

        $post_id = (int) $atts['post_id'];
        $style = sanitize_text_field($atts['style']);

        // Get generated FAQ from post meta
        $faq = get_post_meta($post_id, '_hmg_ai_faq', true);

        if (empty($faq)) {
            return '<!-- No FAQ generated yet -->';
        }

        // Parse FAQ data
        $faq_data = $this->parse_faq_data($faq);
        
        // Load template
        ob_start();
        include(HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/faq-template.php');
        return ob_get_clean();
    }

    /**
     * Render table of contents shortcode
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            Rendered HTML.
     */
    public function render_toc_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'numbered'
        ), $atts, 'hmg_ai_toc');

        $post_id = (int) $atts['post_id'];
        $style = sanitize_text_field($atts['style']);

        // Get generated TOC from post meta
        $toc = get_post_meta($post_id, '_hmg_ai_toc', true);

        if (empty($toc)) {
            // Try to generate TOC from post content headings
            $post = get_post($post_id);
            if ($post) {
                $toc = $this->generate_toc_from_content($post->post_content);
            }
        }

        if (empty($toc)) {
            return '<!-- No table of contents available -->';
        }

        // Parse TOC data
        $toc_data = $this->parse_toc_data($toc);
        
        // Load template
        ob_start();
        include(HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/toc-template.php');
        return ob_get_clean();
    }

    /**
     * Render audio player shortcode
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            Rendered HTML.
     */
    public function render_audio_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'style' => 'player'
        ), $atts, 'hmg_ai_audio');

        $post_id = (int) $atts['post_id'];
        $style = sanitize_text_field($atts['style']);

        // Get generated audio URL from post meta
        $audio_url = get_post_meta($post_id, '_hmg_ai_audio_url', true);

        if (empty($audio_url)) {
            return '<!-- No audio version available yet -->';
        }

        // Get audio metadata
        $audio_data = array(
            'url' => $audio_url,
            'title' => get_the_title($post_id),
            'duration' => get_post_meta($post_id, '_hmg_ai_audio_duration', true),
            'size' => get_post_meta($post_id, '_hmg_ai_audio_size', true)
        );
        
        // Load template
        ob_start();
        include(HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'public/partials/audio-player-template.php');
        return ob_get_clean();
    }

    /**
     * Parse takeaways data from stored meta
     *
     * @since    1.0.0
     * @param    mixed    $takeaways    Raw takeaways data.
     * @return   array                   Parsed takeaways array.
     */
    private function parse_takeaways_data($takeaways) {
        if (is_array($takeaways)) {
            return $takeaways;
        }

        // Try to parse as JSON
        $decoded = json_decode($takeaways, true);
        if ($decoded !== null) {
            return $decoded;
        }

        // Parse as line-separated text
        $lines = array_filter(array_map('trim', explode("\n", $takeaways)));
        $parsed = array();

        foreach ($lines as $line) {
            // Remove bullet points, numbers, etc.
            $clean_line = preg_replace('/^[\d\.\-\*\•]\s*/', '', $line);
            if (!empty($clean_line)) {
                $parsed[] = $clean_line;
            }
        }

        return $parsed;
    }

    /**
     * Parse FAQ data from stored meta
     *
     * @since    1.0.0
     * @param    mixed    $faq    Raw FAQ data.
     * @return   array            Parsed FAQ array.
     */
    private function parse_faq_data($faq) {
        if (is_array($faq)) {
            return $faq;
        }

        // Try to parse as JSON
        $decoded = json_decode($faq, true);
        if ($decoded !== null) {
            return $decoded;
        }

        // Parse as Q&A text format
        $parsed = array();
        $lines = explode("\n", $faq);
        $current_q = '';
        $current_a = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^Q:|Question:/i', $line)) {
                // Save previous Q&A if exists
                if (!empty($current_q) && !empty($current_a)) {
                    $parsed[] = array(
                        'question' => $current_q,
                        'answer' => $current_a
                    );
                }
                $current_q = preg_replace('/^Q:|Question:/i', '', $line);
                $current_q = trim($current_q);
                $current_a = '';
            } elseif (preg_match('/^A:|Answer:/i', $line)) {
                $current_a = preg_replace('/^A:|Answer:/i', '', $line);
                $current_a = trim($current_a);
            } elseif (!empty($current_q) && empty($current_a)) {
                // Continue question
                $current_q .= ' ' . $line;
            } elseif (!empty($current_a)) {
                // Continue answer
                $current_a .= ' ' . $line;
            }
        }

        // Save last Q&A
        if (!empty($current_q) && !empty($current_a)) {
            $parsed[] = array(
                'question' => $current_q,
                'answer' => $current_a
            );
        }

        return $parsed;
    }

    /**
     * Parse TOC data from stored meta
     *
     * @since    1.0.0
     * @param    mixed    $toc    Raw TOC data.
     * @return   array            Parsed TOC array.
     */
    private function parse_toc_data($toc) {
        if (is_array($toc)) {
            return $toc;
        }

        // Try to parse as JSON
        $decoded = json_decode($toc, true);
        if ($decoded !== null) {
            return $decoded;
        }

        // Parse HTML structure
        if (strpos($toc, '<') !== false) {
            return array('html' => $toc);
        }

        // Parse as text list
        $lines = array_filter(array_map('trim', explode("\n", $toc)));
        $parsed = array();

        foreach ($lines as $line) {
            // Determine level by indentation or numbering
            $level = 1;
            if (preg_match('/^\s{2,}/', $line)) {
                $level = 2;
            }
            if (preg_match('/^\s{4,}/', $line)) {
                $level = 3;
            }

            // Clean the line
            $clean_line = preg_replace('/^[\d\.\-\*\s]+/', '', $line);
            
            // Generate anchor from title
            $anchor = '#' . sanitize_title_with_dashes($clean_line);

            $parsed[] = array(
                'title' => $clean_line,
                'anchor' => $anchor,
                'level' => $level
            );
        }

        return $parsed;
    }

    /**
     * Generate TOC from post content
     *
     * @since    1.0.0
     * @param    string    $content    Post content.
     * @return   array                  Generated TOC.
     */
    private function generate_toc_from_content($content) {
        $toc = array();
        
        // Match all headings
        preg_match_all('/<h([2-6])[^>]*>(.*?)<\/h[2-6]>/i', $content, $matches);
        
        if (empty($matches[0])) {
            return array();
        }

        foreach ($matches[0] as $index => $heading) {
            $level = (int) $matches[1][$index];
            $title = strip_tags($matches[2][$index]);
            $anchor = '#' . sanitize_title_with_dashes($title);
            
            $toc[] = array(
                'title' => $title,
                'anchor' => $anchor,
                'level' => $level - 1 // Convert h2=1, h3=2, etc.
            );
        }

        return $toc;
    }

    /**
     * Add anchor IDs to headings in content
     *
     * @since    1.0.0
     * @param    string    $content    The content.
     * @return   string                 Modified content.
     */
    public function add_heading_ids($content) {
        if (!is_single() && !is_page()) {
            return $content;
        }

        // Add IDs to headings for TOC navigation
        $content = preg_replace_callback(
            '/<h([2-6])([^>]*)>(.*?)<\/h[2-6]>/i',
            function($matches) {
                $level = $matches[1];
                $attrs = $matches[2];
                $title = strip_tags($matches[3]);
                $id = sanitize_title_with_dashes($title);
                
                // Check if ID already exists
                if (strpos($attrs, 'id=') !== false) {
                    return $matches[0];
                }
                
                return sprintf(
                    '<h%s id="%s"%s>%s</h%s>',
                    $level,
                    $id,
                    $attrs,
                    $matches[3],
                    $level
                );
            },
            $content
        );

        return $content;
    }

    /**
     * Add structured data for FAQ
     *
     * @since    1.0.0
     */
    public function add_faq_structured_data() {
        if (!is_single() && !is_page()) {
            return;
        }

        global $post;
        $faq_data = get_post_meta($post->ID, '_hmg_ai_faq', true);
        
        if (empty($faq_data)) {
            return;
        }

        $parsed_faq = $this->parse_faq_data($faq_data);
        if (empty($parsed_faq)) {
            return;
        }

        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array()
        );

        foreach ($parsed_faq as $item) {
            $structured_data['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $item['answer']
                )
            );
        }

        echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
    }
}