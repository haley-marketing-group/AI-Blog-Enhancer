<?php
/**
 * The Table of Contents (TOC) generator class.
 *
 * Generates table of contents from blog content using AI services.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 */

/**
 * The TOC generator class.
 *
 * Handles generation of table of contents from blog posts with:
 * - AI-powered content structure analysis
 * - Hierarchical heading extraction
 * - Anchor link generation
 * - Integration with shortcode system
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 * @author     Haley Marketing <info@haleymarketing.com>
 */
class HMG_AI_TOC_Generator extends HMG_AI_Content_Generator {

    /**
     * Initialize the TOC generator.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('toc');
    }

    /**
     * Process generated TOC content from AI service.
     *
     * @since    1.0.0
     * @param    mixed     $raw_content    The raw content from AI service.
     * @param    WP_Post   $post          The post object.
     * @param    array     $options       Generation options.
     * @return   array                    The processed TOC array.
     */
    protected function process_generated_content($raw_content, $post, $options) {
        // First try to extract TOC from actual post content
        $post_toc = $this->extract_toc_from_post($post);
        
        // If we have a good post TOC, use it; otherwise process AI-generated content
        if (!empty($post_toc) && count($post_toc) >= 3) {
            $processed = $post_toc;
        } else {
            // Handle different possible formats from AI services
            if (is_string($raw_content)) {
                $processed = $this->parse_string_toc($raw_content);
            } elseif (is_array($raw_content)) {
                $processed = $this->process_array_toc($raw_content);
            } else {
                return false;
            }
        }

        // Validate the processed content
        if (!$this->validate_content($processed)) {
            return false;
        }

        // Apply formatting and hierarchy
        $processed = $this->apply_toc_formatting($processed, $options);

        return $processed;
    }

    /**
     * Extract TOC from existing post content.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     * @return   array               Extracted TOC array.
     */
    private function extract_toc_from_post($post) {
        $content = $post->post_content;
        $toc_items = array();
        
        // Extract headings from HTML content
        if (preg_match_all('/<h([1-6])[^>]*id=["\']([^"\']*)["\'][^>]*>(.+?)<\/h[1-6]>/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = intval($match[1]);
                $id = $match[2];
                $title = wp_strip_all_tags($match[3]);
                
                if (!empty($title) && !empty($id)) {
                    $toc_items[] = array(
                        'title' => trim($title),
                        'anchor' => $id,
                        'level' => $level,
                        'id' => uniqid('toc_')
                    );
                }
            }
        }
        
        // If no IDs found, extract headings and generate IDs
        if (empty($toc_items)) {
            if (preg_match_all('/<h([1-6])[^>]*>(.+?)<\/h[1-6]>/i', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $level = intval($match[1]);
                    $title = wp_strip_all_tags($match[2]);
                    
                    if (!empty($title)) {
                        $toc_items[] = array(
                            'title' => trim($title),
                            'anchor' => sanitize_title($title),
                            'level' => $level,
                            'id' => uniqid('toc_')
                        );
                    }
                }
            }
        }
        
        return $toc_items;
    }

    /**
     * Parse TOC from string format.
     *
     * @since    1.0.0
     * @param    string    $content    The raw string content.
     * @return   array                 Parsed TOC array.
     */
    private function parse_string_toc($content) {
        $toc_items = array();
        
        // Clean the content
        $content = trim($content);
        
        // Try different parsing strategies
        
        // Strategy 1: JSON format
        $json_data = json_decode($content, true);
        if ($json_data && isset($json_data['toc'])) {
            return $this->process_array_toc($json_data['toc']);
        }
        
        // Strategy 2: Numbered outline (1. 1.1. 1.2.)
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Skip headers
            if ($this->is_toc_header($line)) {
                continue;
            }
            
            // Parse numbered items (1. 1.1. 2.1.1.)
            if (preg_match('/^(\d+(?:\.\d+)*)\.\s*(.+)$/', $line, $matches)) {
                $number = $matches[1];
                $title = trim($matches[2]);
                $level = substr_count($number, '.') + 1;
                
                $toc_items[] = array(
                    'title' => $this->sanitize_toc_title($title),
                    'anchor' => sanitize_title($title),
                    'level' => min($level, 6), // Cap at h6
                    'number' => $number,
                    'id' => uniqid('toc_')
                );
            }
            // Parse indented items
            elseif (preg_match('/^(\s*)([-\*]|\d+\.)\s*(.+)$/', $line, $matches)) {
                $indent = strlen($matches[1]);
                $title = trim($matches[3]);
                $level = min(floor($indent / 2) + 1, 6);
                
                $toc_items[] = array(
                    'title' => $this->sanitize_toc_title($title),
                    'anchor' => sanitize_title($title),
                    'level' => $level,
                    'id' => uniqid('toc_')
                );
            }
            // Parse simple lines as top-level items
            else {
                $toc_items[] = array(
                    'title' => $this->sanitize_toc_title($line),
                    'anchor' => sanitize_title($line),
                    'level' => 2, // Default to h2
                    'id' => uniqid('toc_')
                );
            }
        }
        
        return $toc_items;
    }

    /**
     * Process TOC from array format.
     *
     * @since    1.0.0
     * @param    array    $content    The array content.
     * @return   array                Processed TOC array.
     */
    private function process_array_toc($content) {
        $toc_items = array();
        
        foreach ($content as $item) {
            if (is_string($item)) {
                $toc_items[] = array(
                    'title' => $this->sanitize_toc_title($item),
                    'anchor' => sanitize_title($item),
                    'level' => 2,
                    'id' => uniqid('toc_')
                );
            } elseif (is_array($item) && isset($item['title'])) {
                $toc_items[] = array(
                    'title' => $this->sanitize_toc_title($item['title']),
                    'anchor' => $item['anchor'] ?? sanitize_title($item['title']),
                    'level' => $item['level'] ?? 2,
                    'id' => $item['id'] ?? uniqid('toc_'),
                    'number' => $item['number'] ?? ''
                );
            }
        }
        
        return $toc_items;
    }

    /**
     * Check if a line appears to be a TOC header.
     *
     * @since    1.0.0
     * @param    string    $line    The line to check.
     * @return   bool               True if appears to be a header.
     */
    private function is_toc_header($line) {
        $header_patterns = array(
            '/^table\s+of\s+contents?:?$/i',
            '/^contents?:?$/i',
            '/^outline:?$/i',
            '/^index:?$/i'
        );
        
        foreach ($header_patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Sanitize and clean TOC title.
     *
     * @since    1.0.0
     * @param    string    $title    The raw title.
     * @return   string              The sanitized title.
     */
    private function sanitize_toc_title($title) {
        // Remove HTML tags
        $title = wp_strip_all_tags($title);
        
        // Remove leading numbers/bullets
        $title = preg_replace('/^\s*[\d\.\-\*\•]+\s*/', '', $title);
        
        // Clean up whitespace
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);
        
        // Ensure proper capitalization
        $title = ucfirst($title);
        
        return $title;
    }

    /**
     * Apply formatting and hierarchy to TOC.
     *
     * @since    1.0.0
     * @param    array    $toc_items    The TOC items array.
     * @param    array    $options      Generation options.
     * @return   array                  Formatted TOC.
     */
    private function apply_toc_formatting($toc_items, $options) {
        // Apply limit (default: 10 TOC items)
        $limit = $options['limit'] ?? 10;
        if (count($toc_items) > $limit) {
            $toc_items = array_slice($toc_items, 0, $limit);
        }
        
        // Normalize hierarchy - ensure we start at a reasonable level
        $min_level = min(array_column($toc_items, 'level'));
        if ($min_level > 2) {
            $adjustment = $min_level - 2;
            foreach ($toc_items as &$item) {
                $item['level'] = max(1, $item['level'] - $adjustment);
            }
        }
        
        // Add ordering and parent-child relationships
        $previous_levels = array();
        
        foreach ($toc_items as $index => &$item) {
            $item['order'] = $index + 1;
            $level = $item['level'];
            
            // Find parent
            $parent_id = null;
            for ($i = $index - 1; $i >= 0; $i--) {
                if ($toc_items[$i]['level'] < $level) {
                    $parent_id = $toc_items[$i]['id'];
                    break;
                }
            }
            $item['parent_id'] = $parent_id;
            
            // Count children (for styling purposes)
            $children_count = 0;
            for ($i = $index + 1; $i < count($toc_items); $i++) {
                if ($toc_items[$i]['level'] <= $level) {
                    break;
                }
                if ($toc_items[$i]['level'] == $level + 1) {
                    $children_count++;
                }
            }
            $item['children_count'] = $children_count;
            
            // Generate numbering if not present
            if (empty($item['number'])) {
                $item['number'] = $this->generate_toc_number($index, $toc_items);
            }
        }
        
        return $toc_items;
    }

    /**
     * Generate automatic numbering for TOC items.
     *
     * @since    1.0.0
     * @param    int      $current_index    Current item index.
     * @param    array    $toc_items        All TOC items.
     * @return   string                     Generated number.
     */
    private function generate_toc_number($current_index, $toc_items) {
        $current_level = $toc_items[$current_index]['level'];
        $numbers = array();
        
        // Initialize counters for each level
        $level_counters = array();
        
        // Count items at each level up to current item
        for ($i = 0; $i <= $current_index; $i++) {
            $level = $toc_items[$i]['level'];
            
            // Reset deeper level counters when we encounter a higher level
            for ($j = $level + 1; $j <= 6; $j++) {
                $level_counters[$j] = 0;
            }
            
            // Increment counter for this level
            if (!isset($level_counters[$level])) {
                $level_counters[$level] = 0;
            }
            $level_counters[$level]++;
        }
        
        // Build number string
        for ($level = 1; $level <= $current_level; $level++) {
            if (isset($level_counters[$level]) && $level_counters[$level] > 0) {
                $numbers[] = $level_counters[$level];
            }
        }
        
        return implode('.', $numbers);
    }

    /**
     * Get validation rules for TOC content.
     *
     * @since    1.0.0
     * @return   array    Validation rules.
     */
    protected function get_validation_rules() {
        return array(
            'min_items' => 2,
            'max_items' => 15,
            'min_title_length' => 5,
            'max_title_length' => 100,
            'min_level' => 1,
            'max_level' => 6,
            'required_fields' => array('title', 'anchor', 'level', 'id')
        );
    }

    /**
     * Validate TOC content structure.
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
        
        // Validate each TOC item
        foreach ($content as $item) {
            if (!is_array($item)) {
                return false;
            }
            
            // Check required fields
            foreach ($rules['required_fields'] as $field) {
                if (!isset($item[$field]) || empty($item[$field])) {
                    return false;
                }
            }
            
            // Check title length
            $title_length = strlen($item['title']);
            if ($title_length < $rules['min_title_length'] || $title_length > $rules['max_title_length']) {
                return false;
            }
            
            // Check level range
            $level = $item['level'];
            if ($level < $rules['min_level'] || $level > $rules['max_level']) {
                return false;
            }
            
            // Validate anchor format
            if (!preg_match('/^[a-z0-9\-]+$/', $item['anchor'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generate sample TOC for testing.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   array              Sample TOC data.
     */
    public function generate_sample_content($post_id) {
        return array(
            array(
                'title' => 'Introduction',
                'anchor' => 'introduction',
                'level' => 2,
                'id' => 'toc_sample_1',
                'order' => 1,
                'number' => '1',
                'parent_id' => null,
                'children_count' => 0
            ),
            array(
                'title' => 'What is AI Content Marketing',
                'anchor' => 'what-is-ai-content-marketing',
                'level' => 2,
                'id' => 'toc_sample_2',
                'order' => 2,
                'number' => '2',
                'parent_id' => null,
                'children_count' => 2
            ),
            array(
                'title' => 'Current Market Trends',
                'anchor' => 'current-market-trends',
                'level' => 2,
                'id' => 'toc_sample_3',
                'order' => 3,
                'number' => '3',
                'parent_id' => null,
                'children_count' => 2
            ),
            array(
                'title' => 'Increased Engagement',
                'anchor' => 'increased-engagement',
                'level' => 3,
                'id' => 'toc_sample_4',
                'order' => 4,
                'number' => '3.1',
                'parent_id' => 'toc_sample_3',
                'children_count' => 0
            ),
            array(
                'title' => 'Cost Efficiency',
                'anchor' => 'cost-efficiency',
                'level' => 3,
                'id' => 'toc_sample_5',
                'order' => 5,
                'number' => '3.2',
                'parent_id' => 'toc_sample_3',
                'children_count' => 0
            ),
            array(
                'title' => 'Implementation Strategy',
                'anchor' => 'implementation-strategy',
                'level' => 2,
                'id' => 'toc_sample_6',
                'order' => 6,
                'number' => '4',
                'parent_id' => null,
                'children_count' => 0
            ),
            array(
                'title' => 'Conclusion',
                'anchor' => 'conclusion',
                'level' => 2,
                'id' => 'toc_sample_7',
                'order' => 7,
                'number' => '5',
                'parent_id' => null,
                'children_count' => 0
            )
        );
    }
} 