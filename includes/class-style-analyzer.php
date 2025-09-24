<?php
/**
 * Style Analyzer Class
 * 
 * Analyzes post and theme styles to dynamically adapt AI content styling
 *
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

class HMG_AI_Style_Analyzer {
    
    /**
     * Analyzed styles cache
     */
    private $analyzed_styles = array();
    
    /**
     * Current theme info
     */
    private $theme_info;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->theme_info = wp_get_theme();
    }
    
    /**
     * Analyze post and return dynamic styles
     *
     * @param int $post_id The post ID to analyze
     * @return array Style configuration
     */
    public function analyze_post_style($post_id) {
        // Check cache first
        if (isset($this->analyzed_styles[$post_id])) {
            return $this->analyzed_styles[$post_id];
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return $this->get_default_styles();
        }
        
        // Analyze various aspects
        $theme_style = $this->analyze_theme();
        $content_style = $this->analyze_content($post->post_content);
        $computed_styles = $this->get_computed_styles($post_id);
        $color_scheme = $this->detect_color_scheme($post_id);
        
        // Merge and generate final style configuration
        $styles = array(
            'theme' => $theme_style,
            'colors' => $color_scheme,
            'typography' => $this->detect_typography($post_id),
            'spacing' => $this->detect_spacing_patterns($post->post_content),
            'components' => $this->detect_component_styles($post->post_content),
            'css_variables' => $this->generate_css_variables($color_scheme, $theme_style),
            'custom_css' => $this->generate_adaptive_css($theme_style, $color_scheme, $content_style)
        );
        
        // Cache the result
        $this->analyzed_styles[$post_id] = $styles;
        
        return $styles;
    }
    
    /**
     * Analyze theme characteristics
     */
    private function analyze_theme() {
        $theme_name = strtolower($this->theme_info->get('Name'));
        $theme_template = strtolower($this->theme_info->get('Template'));
        
        // Detect popular themes and their characteristics
        $theme_profiles = array(
            'twentytwentyfour' => array(
                'style' => 'modern',
                'borders' => 'rounded',
                'shadows' => 'subtle',
                'spacing' => 'generous',
                'font_style' => 'system'
            ),
            'twentytwentythree' => array(
                'style' => 'minimal',
                'borders' => 'sharp',
                'shadows' => 'none',
                'spacing' => 'moderate',
                'font_style' => 'serif'
            ),
            'astra' => array(
                'style' => 'clean',
                'borders' => 'slightly-rounded',
                'shadows' => 'light',
                'spacing' => 'balanced',
                'font_style' => 'sans-serif'
            ),
            'generatepress' => array(
                'style' => 'professional',
                'borders' => 'sharp',
                'shadows' => 'minimal',
                'spacing' => 'compact',
                'font_style' => 'sans-serif'
            ),
            'oceanwp' => array(
                'style' => 'modern',
                'borders' => 'rounded',
                'shadows' => 'medium',
                'spacing' => 'generous',
                'font_style' => 'sans-serif'
            ),
            'neve' => array(
                'style' => 'contemporary',
                'borders' => 'slightly-rounded',
                'shadows' => 'soft',
                'spacing' => 'balanced',
                'font_style' => 'system'
            ),
            'blocksy' => array(
                'style' => 'dynamic',
                'borders' => 'rounded',
                'shadows' => 'layered',
                'spacing' => 'flexible',
                'font_style' => 'variable'
            )
        );
        
        // Check for known themes
        foreach ($theme_profiles as $theme_key => $profile) {
            if (strpos($theme_name, $theme_key) !== false || strpos($theme_template, $theme_key) !== false) {
                return $profile;
            }
        }
        
        // Default profile for unknown themes
        return array(
            'style' => 'neutral',
            'borders' => 'slightly-rounded',
            'shadows' => 'subtle',
            'spacing' => 'moderate',
            'font_style' => 'inherit'
        );
    }
    
    /**
     * Analyze content structure and styling patterns
     */
    private function analyze_content($content) {
        $analysis = array(
            'has_headings' => preg_match('/<h[1-6]/i', $content),
            'has_lists' => preg_match('/<[ou]l/i', $content),
            'has_blockquotes' => preg_match('/<blockquote/i', $content),
            'has_tables' => preg_match('/<table/i', $content),
            'has_code' => preg_match('/<code|<pre/i', $content),
            'has_images' => preg_match('/<img/i', $content),
            'content_length' => strlen($content),
            'paragraph_count' => substr_count($content, '</p>'),
            'style_tone' => $this->detect_content_tone($content)
        );
        
        return $analysis;
    }
    
    /**
     * Detect content tone (formal, casual, technical, etc.)
     */
    private function detect_content_tone($content) {
        $text = wp_strip_all_tags($content);
        
        // Simple tone detection based on word patterns
        $formal_words = preg_match_all('/\b(therefore|however|furthermore|consequently|nevertheless|whereas)\b/i', $text);
        $casual_words = preg_match_all('/\b(gonna|wanna|yeah|hey|cool|awesome|stuff)\b/i', $text);
        $technical_words = preg_match_all('/\b(API|database|function|algorithm|implementation|framework|protocol)\b/i', $text);
        
        if ($technical_words > 5) return 'technical';
        if ($formal_words > $casual_words * 2) return 'formal';
        if ($casual_words > $formal_words * 2) return 'casual';
        
        return 'neutral';
    }
    
    /**
     * Get computed styles from the frontend
     */
    private function get_computed_styles($post_id) {
        // This would ideally fetch actual computed styles via JavaScript
        // For now, we'll use WordPress theme mods and customizer settings
        
        $styles = array(
            'primary_color' => get_theme_mod('primary_color', '#332a86'),
            'secondary_color' => get_theme_mod('secondary_color', '#5e9732'),
            'text_color' => get_theme_mod('text_color', '#333333'),
            'background_color' => get_theme_mod('background_color', '#ffffff'),
            'link_color' => get_theme_mod('link_color', '#0073aa'),
            'heading_font' => get_theme_mod('heading_font_family', 'inherit'),
            'body_font' => get_theme_mod('body_font_family', 'inherit'),
            'font_size_base' => get_theme_mod('font_size_base', '16px'),
            'line_height' => get_theme_mod('line_height_base', '1.6')
        );
        
        return $styles;
    }
    
    /**
     * Detect color scheme from post and theme
     */
    private function detect_color_scheme($post_id) {
        $computed = $this->get_computed_styles($post_id);
        
        // Extract colors from post content if any inline styles exist
        $post = get_post($post_id);
        $content_colors = $this->extract_colors_from_content($post->post_content);
        
        // Determine if light or dark mode
        $bg_color = $computed['background_color'] ?? '#ffffff';
        $is_dark_mode = $this->is_dark_color($bg_color);
        
        // Generate complementary colors
        $primary = $computed['primary_color'] ?? '#332a86';
        $secondary = $computed['secondary_color'] ?? '#5e9732';
        
        return array(
            'mode' => $is_dark_mode ? 'dark' : 'light',
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $this->adjust_color_brightness($primary, 20),
            'text' => $computed['text_color'] ?? ($is_dark_mode ? '#f0f0f0' : '#333333'),
            'background' => $bg_color,
            'surface' => $this->adjust_color_brightness($bg_color, $is_dark_mode ? 10 : -5),
            'border' => $this->adjust_color_brightness($bg_color, $is_dark_mode ? 20 : -15),
            'shadow' => $is_dark_mode ? 'rgba(0,0,0,0.5)' : 'rgba(0,0,0,0.1)',
            'content_colors' => $content_colors
        );
    }
    
    /**
     * Extract colors from content inline styles
     */
    private function extract_colors_from_content($content) {
        $colors = array();
        
        // Extract colors from style attributes
        preg_match_all('/style=["\'][^"\']*color:\s*([^;"\']*)/', $content, $matches);
        if (!empty($matches[1])) {
            $colors = array_merge($colors, $matches[1]);
        }
        
        // Extract background colors
        preg_match_all('/style=["\'][^"\']*background(?:-color)?:\s*([^;"\']*)/', $content, $matches);
        if (!empty($matches[1])) {
            $colors = array_merge($colors, $matches[1]);
        }
        
        return array_unique($colors);
    }
    
    /**
     * Detect typography patterns
     */
    private function detect_typography($post_id) {
        $computed = $this->get_computed_styles($post_id);
        
        // Get actual font stack from theme
        $heading_font = $computed['heading_font'] ?? 'inherit';
        $body_font = $computed['body_font'] ?? 'inherit';
        
        // Detect font categories
        $font_categories = array(
            'serif' => array('Georgia', 'Times', 'Garamond', 'Merriweather', 'Lora'),
            'sans-serif' => array('Arial', 'Helvetica', 'Roboto', 'Open Sans', 'Lato', 'Inter'),
            'monospace' => array('Courier', 'Monaco', 'Consolas', 'Fira Code'),
            'display' => array('Playfair', 'Bebas', 'Oswald', 'Montserrat')
        );
        
        $heading_category = 'sans-serif';
        $body_category = 'sans-serif';
        
        foreach ($font_categories as $category => $fonts) {
            foreach ($fonts as $font) {
                if (stripos($heading_font, $font) !== false) {
                    $heading_category = $category;
                }
                if (stripos($body_font, $font) !== false) {
                    $body_category = $category;
                }
            }
        }
        
        return array(
            'heading_font' => $heading_font,
            'heading_category' => $heading_category,
            'body_font' => $body_font,
            'body_category' => $body_category,
            'base_size' => $computed['font_size_base'] ?? '16px',
            'line_height' => $computed['line_height'] ?? '1.6',
            'heading_weight' => $heading_category === 'display' ? '700' : '600',
            'body_weight' => '400'
        );
    }
    
    /**
     * Detect spacing patterns in content
     */
    private function detect_spacing_patterns($content) {
        // Analyze margin/padding patterns in content
        $has_wide_spacing = preg_match('/margin:\s*[3-9]\d+px|padding:\s*[3-9]\d+px/', $content);
        $has_tight_spacing = preg_match('/margin:\s*[0-1]?\d+px|padding:\s*[0-1]?\d+px/', $content);
        
        if ($has_wide_spacing) {
            return array(
                'style' => 'spacious',
                'block_margin' => '2rem',
                'element_padding' => '1.5rem',
                'line_spacing' => '1.8'
            );
        } elseif ($has_tight_spacing) {
            return array(
                'style' => 'compact',
                'block_margin' => '1rem',
                'element_padding' => '0.75rem',
                'line_spacing' => '1.5'
            );
        }
        
        return array(
            'style' => 'balanced',
            'block_margin' => '1.5rem',
            'element_padding' => '1rem',
            'line_spacing' => '1.6'
        );
    }
    
    /**
     * Detect component styles (cards, buttons, etc.)
     */
    private function detect_component_styles($content) {
        $styles = array();
        
        // Check for card-like structures
        if (preg_match('/<div[^>]*class="[^"]*card[^"]*"/', $content)) {
            $styles['has_cards'] = true;
            $styles['card_style'] = 'elevated';
        }
        
        // Check for button styles
        if (preg_match('/<(a|button)[^>]*class="[^"]*btn[^"]*"/', $content)) {
            $styles['has_buttons'] = true;
            preg_match('/class="[^"]*(rounded|pill|square)[^"]*"/', $content, $matches);
            $styles['button_style'] = $matches[1] ?? 'rounded';
        }
        
        // Check for bordered elements
        $styles['uses_borders'] = preg_match('/border:\s*\d+px/', $content);
        $styles['uses_shadows'] = preg_match('/box-shadow:/', $content);
        $styles['uses_gradients'] = preg_match('/gradient/', $content);
        
        return $styles;
    }
    
    /**
     * Generate CSS variables based on analysis
     */
    private function generate_css_variables($colors, $theme) {
        $border_radius = array(
            'sharp' => '0',
            'slightly-rounded' => '4px',
            'rounded' => '8px',
            'very-rounded' => '16px'
        );
        
        $shadow_styles = array(
            'none' => 'none',
            'minimal' => '0 1px 2px ' . $colors['shadow'],
            'subtle' => '0 2px 4px ' . $colors['shadow'],
            'light' => '0 2px 8px ' . $colors['shadow'],
            'medium' => '0 4px 12px ' . $colors['shadow'],
            'soft' => '0 4px 16px ' . $colors['shadow'],
            'layered' => '0 2px 4px ' . $colors['shadow'] . ', 0 4px 8px ' . $colors['shadow']
        );
        
        return array(
            '--hmg-ai-primary' => $colors['primary'],
            '--hmg-ai-secondary' => $colors['secondary'],
            '--hmg-ai-accent' => $colors['accent'],
            '--hmg-ai-text' => $colors['text'],
            '--hmg-ai-background' => $colors['background'],
            '--hmg-ai-surface' => $colors['surface'],
            '--hmg-ai-border' => $colors['border'],
            '--hmg-ai-radius' => $border_radius[$theme['borders']] ?? '4px',
            '--hmg-ai-shadow' => $shadow_styles[$theme['shadows']] ?? 'none',
            '--hmg-ai-spacing' => $theme['spacing'] === 'generous' ? '1.5rem' : ($theme['spacing'] === 'compact' ? '0.75rem' : '1rem')
        );
    }
    
    /**
     * Generate adaptive CSS based on analysis
     */
    private function generate_adaptive_css($theme, $colors, $content) {
        $css = '';
        
        // Base adaptive styles
        $css .= '.hmg-ai-takeaways, .hmg-ai-faq, .hmg-ai-toc {';
        $css .= 'color: var(--hmg-ai-text);';
        $css .= 'background: var(--hmg-ai-surface);';
        $css .= 'border-radius: var(--hmg-ai-radius);';
        $css .= 'padding: var(--hmg-ai-spacing);';
        $css .= 'margin: var(--hmg-ai-spacing) 0;';
        $css .= 'box-shadow: var(--hmg-ai-shadow);';
        
        // Adapt border style
        if ($theme['borders'] === 'sharp') {
            $css .= 'border: 1px solid var(--hmg-ai-border);';
        } elseif ($theme['style'] === 'modern') {
            $css .= 'border: none;';
            $css .= 'background: linear-gradient(135deg, var(--hmg-ai-surface) 0%, var(--hmg-ai-background) 100%);';
        } else {
            $css .= 'border: 1px solid var(--hmg-ai-border);';
        }
        $css .= '}';
        
        // Heading styles
        $css .= '.hmg-ai-takeaways h3, .hmg-ai-faq h3, .hmg-ai-toc h3 {';
        $css .= 'color: var(--hmg-ai-primary);';
        $css .= 'border-bottom: 2px solid var(--hmg-ai-accent);';
        $css .= 'padding-bottom: 0.5rem;';
        $css .= 'margin-bottom: var(--hmg-ai-spacing);';
        $css .= '}';
        
        // Content tone adjustments
        if ($content['style_tone'] === 'formal') {
            $css .= '.hmg-ai-content { font-family: Georgia, serif; line-height: 1.8; }';
        } elseif ($content['style_tone'] === 'technical') {
            $css .= '.hmg-ai-content { font-family: "SF Mono", Monaco, monospace; }';
        }
        
        // Dark mode adjustments
        if ($colors['mode'] === 'dark') {
            $css .= '.hmg-ai-takeaways, .hmg-ai-faq, .hmg-ai-toc {';
            $css .= 'background: rgba(255,255,255,0.05);';
            $css .= 'border-color: rgba(255,255,255,0.1);';
            $css .= '}';
        }
        
        return $css;
    }
    
    /**
     * Check if color is dark
     */
    private function is_dark_color($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        return $luminance < 0.5;
    }
    
    /**
     * Adjust color brightness
     */
    private function adjust_color_brightness($hex, $percent) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Get default styles as fallback
     */
    private function get_default_styles() {
        return array(
            'theme' => array(
                'style' => 'neutral',
                'borders' => 'slightly-rounded',
                'shadows' => 'subtle',
                'spacing' => 'moderate',
                'font_style' => 'inherit'
            ),
            'colors' => array(
                'mode' => 'light',
                'primary' => '#332a86',
                'secondary' => '#5e9732',
                'accent' => '#5e9732',
                'text' => '#333333',
                'background' => '#ffffff',
                'surface' => '#f9f9f9',
                'border' => '#e0e0e0',
                'shadow' => 'rgba(0,0,0,0.1)'
            ),
            'typography' => array(
                'heading_font' => 'inherit',
                'body_font' => 'inherit',
                'base_size' => '16px',
                'line_height' => '1.6'
            ),
            'spacing' => array(
                'style' => 'balanced',
                'block_margin' => '1.5rem',
                'element_padding' => '1rem',
                'line_spacing' => '1.6'
            ),
            'css_variables' => array(),
            'custom_css' => ''
        );
    }
    
    /**
     * Apply analyzed styles to content
     */
    public function apply_styles($content, $post_id) {
        $styles = $this->analyze_post_style($post_id);
        
        // Generate inline style block
        $style_block = '<style id="hmg-ai-adaptive-styles-' . $post_id . '">';
        $style_block .= ':root {';
        foreach ($styles['css_variables'] as $var => $value) {
            $style_block .= $var . ': ' . $value . ';';
        }
        $style_block .= '}';
        $style_block .= $styles['custom_css'];
        $style_block .= '</style>';
        
        // Prepend styles to content (will be added once per page)
        if (strpos($content, 'hmg-ai-adaptive-styles-' . $post_id) === false) {
            $content = $style_block . $content;
        }
        
        return $content;
    }
}
