<?php
/**
 * The FAQ content generator class.
 *
 * Generates frequently asked questions from blog content using AI services.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 */

/**
 * The FAQ content generator class.
 *
 * Handles generation of FAQ content from blog posts with:
 * - AI-powered question extraction
 * - Structured Q&A formatting
 * - SEO-optimized structured data
 * - Integration with shortcode system
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 * @author     Haley Marketing <info@haleymarketing.com>
 */
class HMG_AI_FAQ_Generator extends HMG_AI_Content_Generator {

    /**
     * Initialize the FAQ generator.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('faq');
    }

    /**
     * Process generated FAQ content from AI service.
     *
     * @since    1.0.0
     * @param    mixed     $raw_content    The raw content from AI service.
     * @param    WP_Post   $post          The post object.
     * @param    array     $options       Generation options.
     * @return   array                    The processed FAQ array.
     */
    protected function process_generated_content($raw_content, $post, $options) {
        // Handle different possible formats from AI services
        if (is_string($raw_content)) {
            $processed = $this->parse_string_faq($raw_content);
        } elseif (is_array($raw_content)) {
            $processed = $this->process_array_faq($raw_content);
        } else {
            return false;
        }

        // Validate the processed content
        if (!$this->validate_content($processed)) {
            return false;
        }

        // Apply formatting and limits
        $processed = $this->apply_faq_formatting($processed, $options);

        // Generate structured data for SEO
        $this->save_structured_data($post->ID, $processed);

        return $processed;
    }

    /**
     * Parse FAQ from string format.
     *
     * @since    1.0.0
     * @param    string    $content    The raw string content.
     * @return   array                 Parsed FAQ array.
     */
    private function parse_string_faq($content) {
        $faq_items = array();
        
        // Clean the content
        $content = trim($content);
        
        // Try different parsing strategies
        
        // Strategy 1: JSON format
        $json_data = json_decode($content, true);
        if ($json_data && isset($json_data['faq'])) {
            return $this->process_array_faq($json_data['faq']);
        }
        
        // Strategy 2: Q: A: format
        if (preg_match_all('/(?:^|\n)\s*(?:Q|Question):\s*(.+?)\s*(?:A|Answer):\s*(.+?)(?=\n\s*(?:Q|Question):|$)/is', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $question = trim($match[1]);
                $answer = trim($match[2]);
                if (!empty($question) && !empty($answer)) {
                    $faq_items[] = array(
                        'question' => $this->sanitize_faq_text($question, 'question'),
                        'answer' => $this->sanitize_faq_text($answer, 'answer'),
                        'id' => uniqid('faq_')
                    );
                }
            }
        }
        
        // Strategy 3: Numbered Q&A (1. Question? Answer)
        elseif (preg_match_all('/(?:^|\n)\s*\d+\.\s*(.+?\?)\s*(.+?)(?=\n\s*\d+\.|$)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $question = trim($match[1]);
                $answer = trim($match[2]);
                if (!empty($question) && !empty($answer)) {
                    $faq_items[] = array(
                        'question' => $this->sanitize_faq_text($question, 'question'),
                        'answer' => $this->sanitize_faq_text($answer, 'answer'),
                        'id' => uniqid('faq_')
                    );
                }
            }
        }
        
        // Strategy 4: Simple line parsing (fallback)
        else {
            $lines = explode("\n", $content);
            $current_question = '';
            $current_answer = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line)) {
                    continue;
                }
                
                // If line ends with ?, it's likely a question
                if (preg_match('/\?$/', $line)) {
                    // Save previous Q&A if exists
                    if (!empty($current_question) && !empty($current_answer)) {
                        $faq_items[] = array(
                            'question' => $this->sanitize_faq_text($current_question, 'question'),
                            'answer' => $this->sanitize_faq_text($current_answer, 'answer'),
                            'id' => uniqid('faq_')
                        );
                    }
                    
                    $current_question = $line;
                    $current_answer = '';
                } else {
                    // Accumulate answer
                    $current_answer .= ' ' . $line;
                }
            }
            
            // Add final Q&A
            if (!empty($current_question) && !empty($current_answer)) {
                $faq_items[] = array(
                    'question' => $this->sanitize_faq_text($current_question, 'question'),
                    'answer' => $this->sanitize_faq_text($current_answer, 'answer'),
                    'id' => uniqid('faq_')
                );
            }
        }
        
        return $faq_items;
    }

    /**
     * Process FAQ from array format.
     *
     * @since    1.0.0
     * @param    array    $content    The array content.
     * @return   array                Processed FAQ array.
     */
    private function process_array_faq($content) {
        $faq_items = array();
        
        foreach ($content as $item) {
            if (is_array($item) && isset($item['question']) && isset($item['answer'])) {
                $faq_items[] = array(
                    'question' => $this->sanitize_faq_text($item['question'], 'question'),
                    'answer' => $this->sanitize_faq_text($item['answer'], 'answer'),
                    'id' => $item['id'] ?? uniqid('faq_'),
                    'category' => $item['category'] ?? '',
                    'priority' => $item['priority'] ?? 'normal'
                );
            }
        }
        
        return $faq_items;
    }

    /**
     * Sanitize and clean FAQ text.
     *
     * @since    1.0.0
     * @param    string    $text    The raw FAQ text.
     * @param    string    $type    The type (question or answer).
     * @return   string             The sanitized text.
     */
    private function sanitize_faq_text($text, $type) {
        // Remove HTML tags
        $text = wp_strip_all_tags($text);
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if ($type === 'question') {
            // Remove leading "Q:" or numbers
            $text = preg_replace('/^\s*(?:Q|Question):\s*/i', '', $text);
            $text = preg_replace('/^\s*\d+\.\s*/', '', $text);
            
            // Ensure proper capitalization
            $text = ucfirst($text);
            
            // Ensure it ends with a question mark
            if (!preg_match('/\?$/', $text)) {
                $text .= '?';
            }
        } else {
            // Remove leading "A:" 
            $text = preg_replace('/^\s*(?:A|Answer):\s*/i', '', $text);
            
            // Ensure proper capitalization
            $text = ucfirst($text);
            
            // Ensure it ends with proper punctuation
            if (!preg_match('/[.!?]$/', $text)) {
                $text .= '.';
            }
        }
        
        return $text;
    }

    /**
     * Apply formatting and limits to FAQ.
     *
     * @since    1.0.0
     * @param    array    $faq_items    The FAQ items array.
     * @param    array    $options      Generation options.
     * @return   array                  Formatted FAQ.
     */
    private function apply_faq_formatting($faq_items, $options) {
        // Apply limit (default: 5 FAQ items)
        $limit = $options['limit'] ?? 5;
        if (count($faq_items) > $limit) {
            $faq_items = array_slice($faq_items, 0, $limit);
        }
        
        // Add ordering information
        foreach ($faq_items as $index => &$item) {
            $item['order'] = $index + 1;
            
            // Generate anchor link for each question
            $item['anchor'] = 'faq-' . sanitize_title($item['question']);
        }
        
        return $faq_items;
    }

    /**
     * Save structured data for SEO.
     *
     * @since    1.0.0
     * @param    int      $post_id      The post ID.
     * @param    array    $faq_items    The FAQ items.
     */
    private function save_structured_data($post_id, $faq_items) {
        $structured_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array()
        );
        
        foreach ($faq_items as $item) {
            $structured_data['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $item['answer']
                )
            );
        }
        
        update_post_meta($post_id, '_hmg_ai_faq_structured', $structured_data);
    }

    /**
     * Get validation rules for FAQ content.
     *
     * @since    1.0.0
     * @return   array    Validation rules.
     */
    protected function get_validation_rules() {
        return array(
            'min_items' => 2,
            'max_items' => 10,
            'min_question_length' => 10,
            'max_question_length' => 150,
            'min_answer_length' => 20,
            'max_answer_length' => 500,
            'required_fields' => array('question', 'answer', 'id')
        );
    }

    /**
     * Validate FAQ content structure.
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
        
        // Validate each FAQ item
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
            
            // Check question length
            $question_length = strlen($item['question']);
            if ($question_length < $rules['min_question_length'] || $question_length > $rules['max_question_length']) {
                return false;
            }
            
            // Check answer length
            $answer_length = strlen($item['answer']);
            if ($answer_length < $rules['min_answer_length'] || $answer_length > $rules['max_answer_length']) {
                return false;
            }
            
            // Validate question format (should end with ?)
            if (!preg_match('/\?$/', $item['question'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generate sample FAQ for testing.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   array              Sample FAQ data.
     */
    public function generate_sample_content($post_id) {
        return array(
            array(
                'question' => 'How does AI improve content marketing performance?',
                'answer' => 'AI improves content marketing by automating personalization, optimizing content for search engines, and providing data-driven insights that help create more engaging and effective content.',
                'id' => 'faq_sample_1',
                'order' => 1,
                'anchor' => 'faq-how-does-ai-improve-content-marketing-performance'
            ),
            array(
                'question' => 'What are the main benefits of using AI for content creation?',
                'answer' => 'The main benefits include increased efficiency, better targeting, improved SEO performance, personalized user experiences, and the ability to scale content production while maintaining quality.',
                'id' => 'faq_sample_2',
                'order' => 2,
                'anchor' => 'faq-what-are-the-main-benefits-of-using-ai-for-content-creation'
            ),
            array(
                'question' => 'Is AI-generated content as effective as human-written content?',
                'answer' => 'AI-generated content can be highly effective when properly implemented and reviewed. It excels at data analysis, optimization, and personalization, but human oversight ensures quality and maintains authentic brand voice.',
                'id' => 'faq_sample_3',
                'order' => 3,
                'anchor' => 'faq-is-ai-generated-content-as-effective-as-human-written-content'
            ),
            array(
                'question' => 'How much time can AI save in content marketing workflows?',
                'answer' => 'Studies show that AI can save content marketers 15-20 hours per week by automating research, optimization, distribution, and performance analysis tasks.',
                'id' => 'faq_sample_4',
                'order' => 4,
                'anchor' => 'faq-how-much-time-can-ai-save-in-content-marketing-workflows'
            )
        );
    }
} 