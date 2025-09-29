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
     * Style analyzer instance
     *
     * @since    1.0.0
     * @access   private
     * @var      HMG_AI_Style_Analyzer    $style_analyzer    The style analyzer instance.
     */
    private $style_analyzer;
    
    /**
     * Stores if adaptive styles have been output for a post
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $styles_output    Track which posts have had styles output.
     */
    private $styles_output = array();

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
        // Load CSS for audio player and dashicons
        if (is_singular()) {
            $post_id = get_the_ID();
            if ($post_id) {
                $post_content = get_post_field('post_content', $post_id);
                
                // Check if audio shortcode is present OR if audio meta exists
                $has_audio = has_shortcode($post_content, 'hmg_ai_audio') || 
                            get_post_meta($post_id, '_hmg_ai_audio_url', true);
                
                if ($has_audio) {
                    // Load the public CSS for audio player styles
                    wp_enqueue_style(
                        $this->plugin_name . '-public',
                        HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'public/css/hmg-ai-public.css',
                        array('dashicons'),
                        $this->version,
                        'all'
                    );
                }
            }
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Always enqueue on single posts/pages
        if (is_singular() || has_shortcode(get_post_field('post_content', get_the_ID()), 'hmg_ai_takeaways') 
            || has_shortcode(get_post_field('post_content', get_the_ID()), 'hmg_ai_faq')
            || has_shortcode(get_post_field('post_content', get_the_ID()), 'hmg_ai_toc')
            || has_shortcode(get_post_field('post_content', get_the_ID()), 'hmg_ai_audio')) {
            
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
            
            // Ensure jQuery easing is available
            wp_enqueue_script('jquery-effects-core');
            
            // Enqueue lazy loading script if enabled
            $options = get_option('hmg_ai_blog_enhancer_options', array());
            if ($options['enable_lazy_load'] ?? true) {
                wp_enqueue_script(
                    $this->plugin_name . '-lazy-load',
                    HMG_AI_BLOG_ENHANCER_PLUGIN_URL . 'public/js/hmg-ai-lazy-load.js',
                    array('jquery'),
                    $this->version,
                    true
                );
                
                // Add AJAX data for lazy loading
                wp_localize_script($this->plugin_name . '-lazy-load', 'hmg_ai_ajax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hmg-ai-ajax-nonce')
                ));
            }
        }
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
        add_shortcode('hmg_ai_summarize', array($this, 'render_summarize_shortcode'));
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
            return '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; margin: 1rem 0;">
                <strong>No takeaways generated yet.</strong> 
                <br>Please generate takeaways from the post editor.
            </div>';
        }

        // Parse takeaways data
        $takeaways_data = $this->parse_takeaways_data($takeaways);
        
        // Debug: Check if parsing worked
        if (empty($takeaways_data)) {
            return '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 1rem; margin: 1rem 0;">
                <strong>Debug Info:</strong><br>
                Raw takeaways found but parsing failed.<br>
                Type: ' . gettype($takeaways) . '<br>
                Length: ' . strlen($takeaways) . '<br>
                First 200 chars: ' . esc_html(substr($takeaways, 0, 200)) . '
            </div>';
        }
        
        // Get inline styles
        $styles = $this->get_inline_styles('takeaways', $post_id);
        
        // Build inline HTML
        $output = '<div class="hmg-ai-takeaways" style="' . esc_attr($styles['container']) . '">';
        $output .= '<h3 style="' . esc_attr($styles['title']) . '">' . __('Key Takeaways', 'hmg-ai-blog-enhancer') . '</h3>';
        
        $output .= '<ul style="' . esc_attr($styles['list']) . '">';
        
        $count = count($takeaways_data);
        foreach ($takeaways_data as $index => $takeaway) {
            // Handle different data types
            if (is_array($takeaway)) {
                // If it's an array, try to get the text content
                $takeaway_text = isset($takeaway['text']) ? $takeaway['text'] : 
                               (isset($takeaway['content']) ? $takeaway['content'] : 
                               (isset($takeaway[0]) ? $takeaway[0] : ''));
            } else {
                $takeaway_text = (string) $takeaway;
            }
            
            // Clean the takeaway text
            $takeaway_text = trim($takeaway_text);
            if (empty($takeaway_text)) {
                continue;
            }
            
            $item_style = ($index === $count - 1) ? $styles['item_last'] : $styles['item'];
            
            $output .= '<li style="' . esc_attr($item_style) . '">';
            $output .= '<span style="' . esc_attr($styles['icon']) . '">•</span>';
            $output .= '<span style="' . esc_attr($styles['text']) . '">' . esc_html($takeaway_text) . '</span>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
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
            'style' => 'list'
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
        
        // Check if we have valid FAQ data
        if (empty($faq_data)) {
            return '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; margin: 1rem 0;">
                <strong>No FAQ items found.</strong> 
                <br>The FAQ content exists but could not be parsed.
            </div>';
        }
        
        // Get inline styles
        $styles = $this->get_inline_styles('faq', $post_id);
        
        // Build inline HTML
        $output = '<div class="hmg-ai-faq" style="' . esc_attr($styles['container']) . '">';
        $output .= '<h3 style="' . esc_attr($styles['title']) . '">' . __('Frequently Asked Questions', 'hmg-ai-blog-enhancer') . '</h3>';
        
        $count = count($faq_data);
        foreach ($faq_data as $index => $item) {
            // Ensure we have both question and answer
            if (!isset($item['question']) || !isset($item['answer'])) {
                continue;
            }
            
            $item_style = ($index === $count - 1) ? 'margin-bottom: 1.5rem; padding-bottom: 0; border-bottom: none;' : 'margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.1);';
            
            $output .= '<div class="hmg-ai-faq-item" style="' . esc_attr($item_style) . '">';
            $output .= '<h4 style="' . esc_attr($styles['question']) . '"><strong style="color: #0073aa;">Q:</strong> ' . esc_html($item['question']) . '</h4>';
            $output .= '<div style="' . esc_attr($styles['answer']) . '"><strong style="color: #0073aa;">A:</strong> ' . wp_kses_post($item['answer']) . '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
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
        
        // Get inline styles
        $styles = $this->get_inline_styles('toc', $post_id);
        
        // Build inline HTML
        $output = '<div class="hmg-ai-toc" style="' . esc_attr($styles['container']) . '">';
        $output .= '<h3 style="' . esc_attr($styles['title']) . '">' . __('Table of Contents', 'hmg-ai-blog-enhancer') . '</h3>';
        
        if (isset($toc_data['items']) && is_array($toc_data['items']) && !empty($toc_data['items'])) {
            $output .= '<ol style="' . esc_attr($styles['list']) . '">';
            
            $count = count($toc_data['items']);
            foreach ($toc_data['items'] as $index => $item) {
                // Make sure we have the required fields
                if (!isset($item['title']) || empty($item['title'])) {
                    continue;
                }
                
                // Get the link or generate it
                $link = isset($item['link']) ? $item['link'] : '#' . sanitize_title_with_dashes($item['title']);
                
                $item_style = 'margin-bottom: 0.5rem;';
                
                $output .= '<li style="' . esc_attr($item_style) . '">';
                $output .= '<a href="' . esc_url($link) . '" style="' . esc_attr($styles['link']) . '">';
                $output .= esc_html($item['title']);
                $output .= '</a>';
                $output .= '</li>';
            }
            
            $output .= '</ol>';
        } elseif (isset($toc_data['html'])) {
            // If we have HTML content, display it
            $output .= wp_kses_post($toc_data['html']);
        } else {
            // No valid TOC items found
            $output .= '<p style="color: #666; font-style: italic;">No table of contents available.</p>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Get inline styles for AI content
     * 
     * @since    1.0.0
     * @param    string $content_type Type of content (takeaways, faq, toc)
     * @param    int    $post_id The post ID
     * @return   array  Inline style attributes for different elements
     */
    private function get_inline_styles($content_type, $post_id = 0) {
        $style_mode = get_option('hmg_ai_style_override', 'best-practices');
        
        // Best practices mode - clean, minimal inline styles
        if ($style_mode === 'best-practices') {
            return $this->get_minimal_inline_styles($content_type);
        }
        
        // Dynamic adaptive styling
        if (get_option('hmg_ai_dynamic_styling', true) && $post_id > 0) {
            return $this->get_adaptive_inline_styles($content_type, $post_id);
        }
        
        // Default theme styles
        return $this->get_default_inline_styles($content_type);
    }
    
    /**
     * Get minimal inline styles for best practices mode
     */
    private function get_minimal_inline_styles($content_type) {
        // Simple, clean styles with good readability
        $text_color = '#333333'; // Dark gray for text
        $title_color = '#222222'; // Darker for headers
        $border_color = 'rgba(0,0,0,0.1)';
        $bg_color = 'transparent'; // Transparent background for seamless integration
        
        $styles = array(
            'container' => sprintf('margin: 1.5rem 0; padding: 1rem; background: %s; border: none;', $bg_color),
            'title' => sprintf('font-size: 1.25rem !important; font-weight: 600 !important; margin: 0 0 1rem 0 !important; color: %s !important;', $title_color),
            'list' => 'list-style: none !important; margin: 0 !important; padding: 0 !important;',
            'item' => 'display: flex !important; align-items: flex-start !important; padding: 0.5rem 0 !important; border-bottom: 1px solid ' . $border_color . ' !important;',
            'item_last' => 'display: flex !important; align-items: flex-start !important; padding: 0.5rem 0 !important; border-bottom: none !important;',
            'icon' => 'margin-right: 0.75rem !important; color: ' . $text_color . ' !important; font-weight: normal !important; font-size: 1rem !important;',
            'text' => 'flex: 1 !important; color: ' . $text_color . ' !important; line-height: 1.6 !important; opacity: 1 !important; visibility: visible !important; display: block !important; font-size: inherit !important;',
            'question' => 'font-weight: 600 !important; margin-bottom: 0.5rem !important; color: ' . $text_color . ' !important; font-size: inherit !important;',
            'answer' => 'color: ' . $text_color . ' !important; line-height: 1.6 !important; opacity: 1 !important; font-size: inherit !important; margin-top: 0.5rem !important;',
            'link' => 'color: #0073aa !important; text-decoration: underline !important;'
        );
        
        return $styles;
    }
    
    /**
     * Get adaptive inline styles based on post analysis
     */
    private function get_adaptive_inline_styles($content_type, $post_id) {
        // Initialize style analyzer if needed
        if (!$this->style_analyzer) {
            require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-style-analyzer.php';
            $this->style_analyzer = new HMG_AI_Style_Analyzer();
        }
        
        // Analyze post and theme
        $analysis = $this->style_analyzer->analyze_post_style($post_id);
        
        // Generate adaptive styles based on analysis
        $primary_color = $analysis['colors']['primary'] ?? 'inherit';
        $text_color = $analysis['colors']['text'] ?? 'inherit';
        $border_color = $analysis['colors']['borders'] ?? 'rgba(0,0,0,0.1)';
        $font_family = $analysis['typography']['font_family'] ?? 'inherit';
        $font_size = $analysis['typography']['base_size'] ?? '1rem';
        $spacing = $analysis['spacing']['base'] ?? '1rem';
        
        $styles = array(
            'container' => sprintf(
                'margin: %s 0; padding: %s; background: transparent; border: 1px solid %s; border-radius: %s; font-family: %s;',
                $spacing, $spacing, $border_color, 
                $analysis['theme']['has_rounded_corners'] ? '8px' : '0',
                $font_family
            ),
            'title' => sprintf(
                'font-size: calc(%s * 1.25); font-weight: 600; margin: 0 0 %s 0; color: %s;',
                $font_size, $spacing, $primary_color
            ),
            'list' => 'list-style: none; margin: 0; padding: 0;',
            'item' => sprintf(
                'padding: calc(%s * 0.5) 0; border-bottom: 1px solid %s;',
                $spacing, $border_color
            ),
            'item_last' => sprintf(
                'padding: calc(%s * 0.5) 0; border-bottom: none;',
                $spacing
            ),
            'text' => sprintf('color: %s; line-height: 1.6;', $text_color),
            'question' => sprintf('font-weight: 600; color: %s; margin-bottom: 0.5rem;', $primary_color),
            'answer' => sprintf('color: %s; line-height: 1.6; margin-top: 0.5rem;', $text_color),
            'link' => sprintf('color: %s; text-decoration: underline;', $primary_color)
        );
        
        return $styles;
    }
    
    /**
     * Get default inline styles
     */
    private function get_default_inline_styles($content_type) {
        return $this->get_minimal_inline_styles($content_type);
    }
    
    /**
     * Get modern theme specific styles
     */
    private function get_modern_theme_styles() {
        return '
.hmg-ai-takeaways, .hmg-ai-faq, .hmg-ai-toc {
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}
.hmg-ai-takeaways:hover, .hmg-ai-faq:hover, .hmg-ai-toc:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px var(--hmg-ai-shadow);
}
';
    }
    
    /**
     * Get minimal theme specific styles
     */
    private function get_minimal_theme_styles() {
        return '
.hmg-ai-takeaways, .hmg-ai-faq, .hmg-ai-toc {
    border-left: 4px solid var(--hmg-ai-primary);
    background: transparent;
    box-shadow: none;
}
.hmg-ai-takeaways h3, .hmg-ai-faq h3, .hmg-ai-toc h3 {
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
';
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
            'size' => get_post_meta($post_id, '_hmg_ai_audio_size', true),
            'voice' => get_post_meta($post_id, '_hmg_ai_audio_voice', true)
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
        // If already an array, process it to ensure strings
        if (is_array($takeaways)) {
            $result = array();
            foreach ($takeaways as $item) {
                if (is_string($item) && !empty(trim($item))) {
                    $result[] = trim($item);
                } elseif (is_array($item)) {
                    // Handle nested arrays (could be from JSON)
                    $text = isset($item['text']) ? $item['text'] : 
                           (isset($item['content']) ? $item['content'] : 
                           (isset($item[0]) ? $item[0] : ''));
                    if (!empty($text)) {
                        $result[] = trim($text);
                    }
                }
            }
            return $result;
        }

        // Try to parse as JSON
        $decoded = json_decode($takeaways, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            // Recursively process the decoded array
            return $this->parse_takeaways_data($decoded);
        }

        // Parse as line-separated text
        $parsed = array();
        
        // Split by newlines and clean each line
        $lines = explode("\n", $takeaways);
        
        foreach ($lines as $line) {
            // Trim whitespace
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Remove common bullet points and numbering
            $clean_line = preg_replace('/^[\d\.\-\*\•\□\◦\▪\▫\→\⭐\✓\✔\➤]+\s*/', '', $line);
            $clean_line = trim($clean_line);
            
            // Only add non-empty lines
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
            // Check if it's already properly structured
            if (!empty($faq) && isset($faq[0]['question'])) {
                return $faq;
            }
        }

        // Try to parse as JSON
        $decoded = json_decode($faq, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        $parsed = array();
        
        // First try to parse Q: and A: format
        if (strpos($faq, 'Q:') !== false || strpos($faq, 'A:') !== false) {
            preg_match_all('/Q:\s*(.*?)(?=A:|Q:|$)/s', $faq, $questions);
            preg_match_all('/A:\s*(.*?)(?=Q:|A:|$)/s', $faq, $answers);
            
            if (!empty($questions[1])) {
                for ($i = 0; $i < count($questions[1]); $i++) {
                    $q = trim($questions[1][$i]);
                    $a = isset($answers[1][$i]) ? trim($answers[1][$i]) : '';
                    
                    if (!empty($q) && !empty($a)) {
                        $parsed[] = array(
                            'question' => $q,
                            'answer' => $a
                        );
                    }
                }
            }
        }
        
        // If no Q:/A: format found, try alternating paragraphs format
        if (empty($parsed)) {
            // Remove any header like "Frequently Asked Questions"
            $faq_clean = preg_replace('/^(Frequently Asked Questions|FAQ|FAQs)[\s\n]*/i', '', $faq);
            
            // Split into paragraphs (separated by double newlines or significant whitespace)
            $paragraphs = preg_split('/\n\s*\n/', $faq_clean);
            
            // Clean up paragraphs
            $cleaned_paragraphs = array();
            foreach ($paragraphs as $para) {
                $para = trim($para);
                // Remove excessive whitespace
                $para = preg_replace('/\s+/', ' ', $para);
                if (!empty($para)) {
                    $cleaned_paragraphs[] = $para;
                }
            }
            
            // Pair up questions and answers (alternating format)
            for ($i = 0; $i < count($cleaned_paragraphs); $i += 2) {
                if (isset($cleaned_paragraphs[$i]) && isset($cleaned_paragraphs[$i + 1])) {
                    $question = trim($cleaned_paragraphs[$i]);
                    $answer = trim($cleaned_paragraphs[$i + 1]);
                    
                    // Basic validation - questions often end with ? or are shorter
                    // If the first one doesn't end with ?, but the second does, swap them
                    if (!preg_match('/\?/', $question) && preg_match('/\?/', $answer)) {
                        $temp = $question;
                        $question = $answer;
                        $answer = $temp;
                    }
                    
                    if (!empty($question) && !empty($answer)) {
                        $parsed[] = array(
                            'question' => $question,
                            'answer' => $answer
                        );
                    }
                }
            }
        }
        
        // If still empty, try line-by-line alternating
        if (empty($parsed)) {
            $lines = explode("\n", $faq);
            $cleaned_lines = array();
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && !preg_match('/^(Frequently Asked Questions|FAQ|FAQs)$/i', $line)) {
                    $cleaned_lines[] = $line;
                }
            }
            
            // Try pairing consecutive non-empty lines
            for ($i = 0; $i < count($cleaned_lines); $i += 2) {
                if (isset($cleaned_lines[$i]) && isset($cleaned_lines[$i + 1])) {
                    $parsed[] = array(
                        'question' => $cleaned_lines[$i],
                        'answer' => $cleaned_lines[$i + 1]
                    );
                }
            }
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
        // If already an array with items key, return it
        if (is_array($toc)) {
            if (isset($toc['items'])) {
                return $toc;
            }
            // Wrap in items key
            return array('items' => $toc);
        }

        // Try to parse as JSON
        $decoded = json_decode($toc, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            if (isset($decoded['items'])) {
                return $decoded;
            }
            // If it's an array but no items key, wrap it
            if (is_array($decoded)) {
                return array('items' => $decoded);
            }
        }

        // Parse HTML structure
        if (strpos($toc, '<') !== false) {
            return array('html' => $toc);
        }

        // Parse as text list
        $lines = array_filter(array_map('trim', explode("\n", $toc)));
        $parsed = array();

        foreach ($lines as $line) {
            // Skip empty lines
            if (empty($line)) continue;
            
            // Determine level by indentation or numbering
            $level = 1;
            if (preg_match('/^\s{2,}/', $line)) {
                $level = 2;
            }
            if (preg_match('/^\s{4,}/', $line)) {
                $level = 3;
            }

            // Clean the line
            $clean_line = preg_replace('/^[\d\.\-\*\s•]+/', '', $line);
            $clean_line = trim($clean_line);
            
            if (empty($clean_line)) continue;
            
            // Generate anchor from title
            $anchor = '#' . sanitize_title_with_dashes($clean_line);

            $parsed[] = array(
                'title' => $clean_line,
                'link' => $anchor,  // Changed from 'anchor' to 'link' to match the rendering expectation
                'level' => $level
            );
        }

        // Return with items wrapper
        return array('items' => $parsed);
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
     * Render summarize shortcode
     *
     * @since    1.5.0
     * @param    array    $atts    Shortcode attributes
     * @return   string           The shortcode output
     */
    public function render_summarize_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'services' => 'chatgpt,perplexity,claude',
            'label' => __('Summarize this blog post with:', 'hmg-ai-blog-enhancer'),
            'style' => 'buttons', // buttons or links
            'align' => 'left' // left, center, right
        ), $atts, 'hmg_ai_summarize');
        
        // Get the current post URL
        global $post;
        if (!$post) {
            return '';
        }
        
        $post_url = get_permalink($post->ID);
        $post_title = get_the_title($post->ID);
        
        // Parse services
        $services = array_map('trim', explode(',', $atts['services']));
        
        // Service configurations
        $service_configs = array(
            'chatgpt' => array(
                'name' => 'ChatGPT',
                'url' => 'https://chat.openai.com/?q=' . urlencode('Summarize this article: ' . $post_url),
                'icon' => '🤖',
                'color' => '#74aa9c'
            ),
            'perplexity' => array(
                'name' => 'Perplexity',
                'url' => 'https://www.perplexity.ai/?q=' . urlencode('Summarize this article: ' . $post_url),
                'icon' => '🔍',
                'color' => '#20808d'
            ),
            'claude' => array(
                'name' => 'Claude',
                'url' => 'https://claude.ai/new?q=' . urlencode('Please summarize this article: ' . $post_url),
                'icon' => '🎭',
                'color' => '#d97757'
            ),
            'gemini' => array(
                'name' => 'Gemini',
                'url' => 'https://gemini.google.com/app?prompt=' . urlencode('Summarize this article: ' . $post_url),
                'icon' => '✨',
                'color' => '#4285f4'
            )
        );
        
        // Build output
        $output = '<div class="hmg-ai-summarize-container" data-align="' . esc_attr($atts['align']) . '">';
        
        if (!empty($atts['label'])) {
            $output .= '<p class="hmg-ai-summarize-label">' . esc_html($atts['label']) . '</p>';
        }
        
        $output .= '<div class="hmg-ai-summarize-buttons">';
        
        foreach ($services as $service) {
            $service = strtolower($service);
            if (isset($service_configs[$service])) {
                $config = $service_configs[$service];
                
                if ($atts['style'] === 'buttons') {
                    $output .= sprintf(
                        '<a href="%s" target="_blank" rel="noopener" class="hmg-ai-summarize-btn hmg-ai-summarize-%s">
                            <span class="hmg-ai-service-name">%s</span>
                        </a>',
                        esc_url($config['url']),
                        esc_attr($service),
                        esc_html($config['name'])
                    );
                } else {
                    $output .= sprintf(
                        '<a href="%s" target="_blank" rel="noopener" class="hmg-ai-summarize-link hmg-ai-summarize-%s">
                            %s
                        </a>',
                        esc_url($config['url']),
                        esc_attr($service),
                        esc_html($config['name'])
                    );
                }
            }
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
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