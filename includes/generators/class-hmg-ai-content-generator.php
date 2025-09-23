<?php
/**
 * The base content generator class.
 *
 * This is the base class for all content generators, providing common functionality
 * for AI-powered content generation with proper error handling and caching.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 */

/**
 * The base content generator class.
 *
 * Provides common functionality for all content generators including:
 * - AI service integration
 * - Content validation and sanitization
 * - Error handling and logging
 * - Caching mechanisms
 * - WordPress integration
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/generators
 * @author     Haley Marketing <info@haleymarketing.com>
 */
abstract class HMG_AI_Content_Generator {

    /**
     * The AI service manager instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      HMG_AI_Service_Manager    $ai_service_manager    The AI service manager.
     */
    protected $ai_service_manager;

    /**
     * The authentication service instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      HMG_AI_Auth_Service    $auth_service    The authentication service.
     */
    protected $auth_service;

    /**
     * The content type this generator handles.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $content_type    The content type (takeaways, faq, toc, audio).
     */
    protected $content_type;

    /**
     * Initialize the content generator.
     *
     * @since    1.0.0
     * @param    string    $content_type    The content type this generator handles.
     */
    public function __construct($content_type) {
        $this->content_type = $content_type;
        $this->ai_service_manager = new HMG_AI_Service_Manager();
        $this->auth_service = new HMG_AI_Auth_Service();
    }

    /**
     * Generate content for a specific post.
     *
     * @since    1.0.0
     * @param    int       $post_id         The post ID.
     * @param    array     $options         Additional options for generation.
     * @return   array                      Generation result with success/error status.
     */
    public function generate_for_post($post_id, $options = array()) {
        // Validate post
        $post = get_post($post_id);
        if (!$post) {
            return array(
                'success' => false,
                'error' => __('Invalid post ID provided.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check if content already exists and force regeneration is not requested
        if (empty($options['force_regenerate']) && $this->content_exists($post_id)) {
            return array(
                'success' => true,
                'content' => $this->get_existing_content($post_id),
                'message' => sprintf(
                    __('%s already exists for this post. Use force regenerate to create new content.', 'hmg-ai-blog-enhancer'),
                    ucfirst($this->content_type)
                ),
                'cached' => true
            );
        }

        // Get post content for analysis
        $source_content = $this->prepare_source_content($post);
        if (empty($source_content)) {
            return array(
                'success' => false,
                'error' => __('No content available for analysis. Please add some content to the post first.', 'hmg-ai-blog-enhancer')
            );
        }

        // Generate content using AI service
        $generation_result = $this->ai_service_manager->generate_content(
            $this->content_type,
            $source_content,
            $post_id,
            $options
        );

        if (!$generation_result['success']) {
            return $generation_result;
        }

        // Process and validate the generated content
        $processed_content = $this->process_generated_content($generation_result['content'], $post, $options);
        
        if (!$processed_content) {
            return array(
                'success' => false,
                'error' => __('Failed to process generated content. Please try again.', 'hmg-ai-blog-enhancer')
            );
        }

        // Save the content
        $save_result = $this->save_content($post_id, $processed_content);
        
        if (!$save_result) {
            return array(
                'success' => false,
                'error' => __('Failed to save generated content. Please check your permissions.', 'hmg-ai-blog-enhancer')
            );
        }

        // Log successful generation
        $this->log_generation_success($post_id, $generation_result);

        return array(
            'success' => true,
            'content' => $processed_content,
            'message' => sprintf(
                __('%s generated successfully!', 'hmg-ai-blog-enhancer'),
                ucfirst($this->content_type)
            ),
            'tokens_used' => $generation_result['tokens_used'] ?? 0
        );
    }

    /**
     * Check if content already exists for the post.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   bool               True if content exists, false otherwise.
     */
    protected function content_exists($post_id) {
        $meta_key = $this->get_meta_key();
        $existing_content = get_post_meta($post_id, $meta_key, true);
        return !empty($existing_content);
    }

    /**
     * Get existing content for the post.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   mixed              The existing content or null.
     */
    protected function get_existing_content($post_id) {
        $meta_key = $this->get_meta_key();
        return get_post_meta($post_id, $meta_key, true);
    }

    /**
     * Prepare source content from the post for AI analysis.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     * @return   string             The prepared content.
     */
    protected function prepare_source_content($post) {
        $content = $post->post_content;
        
        // Remove shortcodes to avoid processing our own content
        $content = strip_shortcodes($content);
        
        // Remove HTML tags and clean up
        $content = wp_strip_all_tags($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // Include post title as context
        $title = $post->post_title;
        if (!empty($title)) {
            $content = "Title: " . $title . "\n\n" . $content;
        }
        
        return $content;
    }

    /**
     * Save generated content to post meta.
     *
     * @since    1.0.0
     * @param    int      $post_id    The post ID.
     * @param    mixed    $content    The content to save.
     * @return   bool                 True on success, false on failure.
     */
    protected function save_content($post_id, $content) {
        $meta_key = $this->get_meta_key();
        
        // Save the main content
        $main_result = update_post_meta($post_id, $meta_key, $content);
        
        // Save generation timestamp
        $timestamp_key = $meta_key . '_generated_at';
        update_post_meta($post_id, $timestamp_key, current_time('mysql'));
        
        // Save generation metadata
        $metadata_key = $meta_key . '_metadata';
        $metadata = array(
            'generated_at' => current_time('mysql'),
            'content_type' => $this->content_type,
            'version' => HMG_AI_BLOG_ENHANCER_VERSION,
            'word_count' => $this->get_content_word_count($content)
        );
        update_post_meta($post_id, $metadata_key, $metadata);
        
        return $main_result !== false;
    }

    /**
     * Log successful content generation.
     *
     * @since    1.0.0
     * @param    int      $post_id            The post ID.
     * @param    array    $generation_result  The generation result.
     */
    protected function log_generation_success($post_id, $generation_result) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'post_id' => $post_id,
            'content_type' => $this->content_type,
            'tokens_used' => $generation_result['tokens_used'] ?? 0,
            'provider' => $generation_result['provider'] ?? 'unknown',
            'success' => true
        );
        
        // Store in options table for analytics
        $log_key = 'hmg_ai_generation_log';
        $existing_log = get_option($log_key, array());
        $existing_log[] = $log_entry;
        
        // Keep only last 100 entries
        if (count($existing_log) > 100) {
            $existing_log = array_slice($existing_log, -100);
        }
        
        update_option($log_key, $existing_log);
    }

    /**
     * Get the word count of content.
     *
     * @since    1.0.0
     * @param    mixed    $content    The content to count.
     * @return   int                  Word count.
     */
    protected function get_content_word_count($content) {
        if (is_string($content)) {
            return str_word_count(strip_tags($content));
        } elseif (is_array($content)) {
            $text = '';
            array_walk_recursive($content, function($item) use (&$text) {
                if (is_string($item)) {
                    $text .= ' ' . $item;
                }
            });
            return str_word_count(strip_tags($text));
        }
        return 0;
    }

    /**
     * Get the meta key for storing this content type.
     *
     * @since    1.0.0
     * @return   string    The meta key.
     */
    protected function get_meta_key() {
        return '_hmg_ai_' . $this->content_type;
    }

    /**
     * Process generated content - must be implemented by child classes.
     *
     * @since    1.0.0
     * @param    mixed     $raw_content    The raw content from AI service.
     * @param    WP_Post   $post          The post object.
     * @param    array     $options       Generation options.
     * @return   mixed                    The processed content.
     */
    abstract protected function process_generated_content($raw_content, $post, $options);

    /**
     * Get content type specific validation rules.
     *
     * @since    1.0.0
     * @return   array    Validation rules.
     */
    abstract protected function get_validation_rules();

    /**
     * Validate generated content structure.
     *
     * @since    1.0.0
     * @param    mixed    $content    The content to validate.
     * @return   bool                 True if valid, false otherwise.
     */
    protected function validate_content($content) {
        $rules = $this->get_validation_rules();
        
        // Basic validation
        if (empty($content)) {
            return false;
        }
        
        // Type-specific validation in child classes
        return $this->validate_content_structure($content, $rules);
    }

    /**
     * Validate content structure against rules.
     *
     * @since    1.0.0
     * @param    mixed    $content    The content to validate.
     * @param    array    $rules      Validation rules.
     * @return   bool                 True if valid, false otherwise.
     */
    protected function validate_content_structure($content, $rules) {
        // Default implementation - override in child classes for specific validation
        return !empty($content);
    }
} 