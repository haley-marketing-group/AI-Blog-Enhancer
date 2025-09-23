<?php
/**
 * The takeaways content generator class.
 *
 * Generates key takeaways from blog content using AI services.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 */

/**
 * The takeaways content generator class.
 *
 * Handles generation of key takeaways from blog content with:
 * - AI-powered analysis of content
 * - Structured takeaway extraction
 * - Validation and formatting
 * - Integration with shortcode system
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 * @author     Haley Marketing <info@haleymarketing.com>
 */
class HMG_AI_Takeaways_Generator extends HMG_AI_Content_Generator {

    /**
     * Initialize the takeaways generator.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('takeaways');
    }

    /**
     * Process generated takeaways content from AI service.
     *
     * @since    1.0.0
     * @param    mixed     $raw_content    The raw content from AI service.
     * @param    WP_Post   $post          The post object.
     * @param    array     $options       Generation options.
     * @return   array                    The processed takeaways array.
     */
    protected function process_generated_content($raw_content, $post, $options) {
        // Handle different possible formats from AI services
        if (is_string($raw_content)) {
            $processed = $this->parse_string_takeaways($raw_content);
        } elseif (is_array($raw_content)) {
            $processed = $this->process_array_takeaways($raw_content);
        } else {
            return false;
        }

        // Validate the processed content
        if (!$this->validate_content($processed)) {
            return false;
        }

        // Apply formatting and limits
        $processed = $this->apply_takeaways_formatting($processed, $options);

        return $processed;
    }

    /**
     * Parse takeaways from string format.
     *
     * @since    1.0.0
     * @param    string    $content    The raw string content.
     * @return   array                 Parsed takeaways array.
     */
    private function parse_string_takeaways($content) {
        $takeaways = array();
        
        // Clean the content
        $content = trim($content);
        
        // Try different parsing strategies
        
        // Strategy 1: JSON format
        $json_data = json_decode($content, true);
        if ($json_data && isset($json_data['takeaways'])) {
            return $this->process_array_takeaways($json_data['takeaways']);
        }
        
        // Strategy 2: Numbered list (1. 2. 3.)
        if (preg_match_all('/(?:^|\n)\s*\d+\.\s*(.+?)(?=\n\s*\d+\.|$)/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $takeaway = trim($match);
                if (!empty($takeaway)) {
                    $takeaways[] = array(
                        'text' => $this->sanitize_takeaway_text($takeaway),
                        'id' => uniqid('takeaway_')
                    );
                }
            }
        }
        
        // Strategy 3: Bullet points (- or *)
        elseif (preg_match_all('/(?:^|\n)\s*[-\*]\s*(.+?)(?=\n\s*[-\*]|$)/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $takeaway = trim($match);
                if (!empty($takeaway)) {
                    $takeaways[] = array(
                        'text' => $this->sanitize_takeaway_text($takeaway),
                        'id' => uniqid('takeaway_')
                    );
                }
            }
        }
        
        // Strategy 4: Line-by-line (fallback)
        else {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip empty lines, headers, or very short content
                if (empty($line) || strlen($line) < 10 || $this->is_header_line($line)) {
                    continue;
                }
                
                $takeaways[] = array(
                    'text' => $this->sanitize_takeaway_text($line),
                    'id' => uniqid('takeaway_')
                );
            }
        }
        
        return $takeaways;
    }

    /**
     * Process takeaways from array format.
     *
     * @since    1.0.0
     * @param    array    $content    The array content.
     * @return   array                Processed takeaways array.
     */
    private function process_array_takeaways($content) {
        $takeaways = array();
        
        foreach ($content as $item) {
            if (is_string($item)) {
                $takeaways[] = array(
                    'text' => $this->sanitize_takeaway_text($item),
                    'id' => uniqid('takeaway_')
                );
            } elseif (is_array($item) && isset($item['text'])) {
                $takeaways[] = array(
                    'text' => $this->sanitize_takeaway_text($item['text']),
                    'id' => $item['id'] ?? uniqid('takeaway_'),
                    'highlight' => $item['highlight'] ?? false,
                    'category' => $item['category'] ?? ''
                );
            }
        }
        
        return $takeaways;
    }

    /**
     * Sanitize and clean takeaway text.
     *
     * @since    1.0.0
     * @param    string    $text    The raw takeaway text.
     * @return   string             The sanitized text.
     */
    private function sanitize_takeaway_text($text) {
        // Remove HTML tags
        $text = wp_strip_all_tags($text);
        
        // Remove leading numbers, bullets, etc.
        $text = preg_replace('/^\s*[\d\.\-\*\•]+\s*/', '', $text);
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Ensure proper capitalization
        $text = ucfirst($text);
        
        // Ensure it ends with proper punctuation
        if (!preg_match('/[.!?]$/', $text)) {
            $text .= '.';
        }
        
        return $text;
    }

    /**
     * Check if a line appears to be a header.
     *
     * @since    1.0.0
     * @param    string    $line    The line to check.
     * @return   bool               True if appears to be a header.
     */
    private function is_header_line($line) {
        // Common header patterns
        $header_patterns = array(
            '/^(key\s+)?takeaways?:?$/i',
            '/^summary:?$/i',
            '/^main\s+points?:?$/i',
            '/^important\s+points?:?$/i'
        );
        
        foreach ($header_patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Apply formatting and limits to takeaways.
     *
     * @since    1.0.0
     * @param    array    $takeaways    The takeaways array.
     * @param    array    $options      Generation options.
     * @return   array                  Formatted takeaways.
     */
    private function apply_takeaways_formatting($takeaways, $options) {
        // Apply limit (default: 5 takeaways)
        $limit = $options['limit'] ?? 5;
        if (count($takeaways) > $limit) {
            $takeaways = array_slice($takeaways, 0, $limit);
        }
        
        // Ensure minimum number of takeaways (at least 3)
        if (count($takeaways) < 3) {
            // Could implement logic to expand short takeaways or regenerate
            // For now, we'll accept what we have
        }
        
        // Add ordering information
        foreach ($takeaways as $index => &$takeaway) {
            $takeaway['order'] = $index + 1;
        }
        
        return $takeaways;
    }

    /**
     * Get validation rules for takeaways content.
     *
     * @since    1.0.0
     * @return   array    Validation rules.
     */
    protected function get_validation_rules() {
        return array(
            'min_items' => 2,
            'max_items' => 10,
            'min_text_length' => 20,
            'max_text_length' => 200,
            'required_fields' => array('text', 'id')
        );
    }

    /**
     * Validate takeaways content structure.
     *
     * @since    1.0.0
     * @param    mixed    $content    The content to validate.
     * @param    array    $rules      Validation rules.
     * @return   bool                 True if valid, false otherwise.
     */
    protected function validate_content_structure($content, $rules) {
        if (!is_array($content)) {
            return false;
        }
        
        // Check minimum and maximum items
        $count = count($content);
        if ($count < $rules['min_items'] || $count > $rules['max_items']) {
            return false;
        }
        
        // Validate each takeaway
        foreach ($content as $takeaway) {
            if (!is_array($takeaway)) {
                return false;
            }
            
            // Check required fields
            foreach ($rules['required_fields'] as $field) {
                if (!isset($takeaway[$field]) || empty($takeaway[$field])) {
                    return false;
                }
            }
            
            // Check text length
            $text_length = strlen($takeaway['text']);
            if ($text_length < $rules['min_text_length'] || $text_length > $rules['max_text_length']) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generate sample takeaways for testing.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   array              Sample takeaways data.
     */
    public function generate_sample_content($post_id) {
        return array(
            array(
                'text' => 'AI-powered content marketing increases engagement by up to 73% compared to traditional methods.',
                'id' => 'takeaway_sample_1',
                'order' => 1
            ),
            array(
                'text' => 'Personalized content recommendations can boost conversion rates by 19% on average.',
                'id' => 'takeaway_sample_2',
                'order' => 2
            ),
            array(
                'text' => 'Automated content optimization saves content creators 15-20 hours per week.',
                'id' => 'takeaway_sample_3',
                'order' => 3
            ),
            array(
                'text' => 'Machine learning algorithms can predict trending topics 48 hours before they peak.',
                'id' => 'takeaway_sample_4',
                'order' => 4
            ),
            array(
                'text' => 'AI-driven content distribution increases reach by 45% across social media platforms.',
                'id' => 'takeaway_sample_5',
                'order' => 5
            )
        );
    }
} 