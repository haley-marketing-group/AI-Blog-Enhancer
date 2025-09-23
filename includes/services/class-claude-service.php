<?php
/**
 * Claude (Anthropic) AI Service
 *
 * Handles integration with Anthropic's Claude AI for content generation
 * including takeaways, FAQ, table of contents, and content analysis.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * Claude AI Service Class
 *
 * Provides AI-powered content generation using Anthropic's Claude API.
 * Handles authentication, rate limiting, and professional content formatting.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Claude_Service {

    /**
     * Claude API base URL
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    The base URL for Claude API calls.
     */
    private $api_base_url;

    /**
     * API key for Claude
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The API key for Claude.
     */
    private $api_key;

    /**
     * Authentication service instance
     *
     * @since    1.0.0
     * @access   private
     * @var      HMG_AI_Auth_Service    $auth_service    Authentication service instance.
     */
    private $auth_service;

    /**
     * Content generation prompts
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $prompts    Predefined prompts for different content types.
     */
    private $prompts;

    /**
     * Available Claude models
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $available_models    Available Claude models with their specifications.
     */
    private $available_models;

    /**
     * Initialize the Claude service
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_base_url = 'https://api.anthropic.com/v1';
        $this->auth_service = new HMG_AI_Auth_Service();
        
        // Get API key and model from options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->api_key = $options['claude_api_key'] ?? getenv('CLAUDE_API_KEY') ?? '';
        $this->selected_model = $options['claude_model'] ?? 'claude-3-5-haiku-20241022';
        
        $this->init_available_models();
        $this->init_prompts();
    }

    /**
     * Initialize available Claude models (2025 lineup)
     *
     * @since    1.0.0
     */
    private function init_available_models() {
        $this->available_models = array(
            'claude-opus-4-20250514' => array(
                'name' => 'Claude Opus 4',
                'description' => 'Most intelligent model for complex tasks with superior reasoning',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 15.00, // $15.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 75.00, // $75.00 per 1M tokens output
                'speed_rating' => 7,
                'quality_rating' => 10,
                'best_for' => array('complex_analysis', 'premium_content', 'advanced_reasoning')
            ),
            'claude-sonnet-4-20250514' => array(
                'name' => 'Claude Sonnet 4',
                'description' => 'High-performance model with exceptional reasoning and efficiency',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 3.00, // $3.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 15.00, // $15.00 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 9,
                'best_for' => array('balanced_usage', 'general_content', 'high_quality')
            ),
            'claude-3-7-sonnet-20250219' => array(
                'name' => 'Claude 3.7 Sonnet',
                'description' => 'Hybrid reasoning model with extended thinking capabilities',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 3.00, // $3.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 15.00, // $15.00 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 9,
                'best_for' => array('extended_thinking', 'coding', 'complex_tasks')
            ),
            'claude-3-5-haiku-20241022' => array(
                'name' => 'Claude 3.5 Haiku',
                'description' => 'Fastest, most cost-effective model for high-volume tasks',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 0.80, // $0.80 per 1M tokens input
                'output_cost_per_1k_tokens' => 4.00, // $4.00 per 1M tokens output
                'speed_rating' => 10,
                'quality_rating' => 8,
                'best_for' => array('high_volume', 'cost_effective', 'fast_generation')
            ),
            'claude-3-5-sonnet-20241022' => array(
                'name' => 'Claude 3.5 Sonnet (Legacy)',
                'description' => 'Previous generation model - Legacy support only',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 3.00, // $3.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 15.00, // $15.00 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 9,
                'best_for' => array('legacy_support', 'reliable_quality')
            ),
            'claude-3-haiku-20240307' => array(
                'name' => 'Claude 3 Haiku (Legacy)',
                'description' => 'Legacy fast model - Basic content generation only',
                'context_length' => 200000,
                'cost_per_1k_tokens' => 0.25, // $0.25 per 1M tokens input
                'output_cost_per_1k_tokens' => 1.25, // $1.25 per 1M tokens output
                'speed_rating' => 9,
                'quality_rating' => 7,
                'best_for' => array('legacy_support', 'basic_tasks')
            )
        );
    }

    /**
     * Initialize content generation prompts
     *
     * @since    1.0.0
     */
    private function init_prompts() {
        $this->prompts = array(
            'takeaways' => array(
                'system' => 'You are a professional content analyst for Haley Marketing, specializing in creating concise, actionable key takeaways from blog content. Focus on practical insights that readers can immediately apply. Always format your response as valid HTML.',
                'user' => 'Analyze the following blog content and create 3-5 key takeaways. Format as HTML with <ul> and <li> tags. Each takeaway should be concise (1-2 sentences) and actionable. Focus on the most valuable insights for the reader.

Content to analyze:
{content}

Please format your response exactly as:
<div class="hmg-ai-takeaways">
<h3>Key Takeaways</h3>
<ul>
<li>First actionable takeaway</li>
<li>Second actionable takeaway</li>
<li>Third actionable takeaway</li>
</ul>
</div>'
            ),
            'faq' => array(
                'system' => 'You are a professional content strategist for Haley Marketing, expert at identifying common questions readers might have about blog content and providing clear, helpful answers. Always format your response as valid HTML.',
                'user' => 'Based on the following blog content, generate 3-5 frequently asked questions that readers might have, along with clear, professional answers. Format as HTML with proper structure.

Content to analyze:
{content}

Please format your response exactly as:
<div class="hmg-ai-faq">
<h3>Frequently Asked Questions</h3>
<div class="faq-item">
<h4>Question 1?</h4>
<p>Clear, professional answer.</p>
</div>
<div class="faq-item">
<h4>Question 2?</h4>
<p>Clear, professional answer.</p>
</div>
</div>'
            ),
            'toc' => array(
                'system' => 'You are a professional content organizer for Haley Marketing, expert at creating logical, user-friendly table of contents structures from blog content. Always format your response as valid HTML.',
                'user' => 'Analyze the following blog content and create a table of contents based on the headings and content structure. Generate anchor links and organize hierarchically.

Content to analyze:
{content}

Please format your response exactly as:
<div class="hmg-ai-toc">
<h3>Table of Contents</h3>
<ol>
<li><a href="#section-1">Main Section 1</a></li>
<li><a href="#section-2">Main Section 2</a>
  <ol>
    <li><a href="#subsection-2-1">Subsection 2.1</a></li>
  </ol>
</li>
</ol>
</div>'
            ),
            'summary' => array(
                'system' => 'You are a professional content summarizer for Haley Marketing, expert at creating concise, engaging summaries that capture the essence of blog content.',
                'user' => 'Create a professional summary of the following blog content. Keep it to 2-3 sentences that capture the main points and value for readers.

Content to analyze:
{content}

Please provide a clear, professional summary without HTML formatting.'
            )
        );
    }

    /**
     * Generate content using Claude AI
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate (takeaways, faq, toc, summary).
     * @param    string    $content         The source content to analyze.
     * @param    int       $post_id         The post ID for tracking.
     * @return   array                      Generation result with content or error.
     */
    public function generate_content($content_type, $content, $post_id = 0) {
        // Check authentication
        $auth_status = $this->auth_service->get_auth_status();
        if (!$auth_status['authenticated']) {
            return array(
                'success' => false,
                'error' => __('Authentication required. Please configure your API key.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check feature access
        if (!$this->auth_service->has_feature_access($content_type)) {
            return array(
                'success' => false,
                'error' => sprintf(
                    __('The %s feature requires a higher tier. Please upgrade your plan.', 'hmg-ai-blog-enhancer'),
                    ucfirst($content_type)
                )
            );
        }

        // Check usage limits
        $usage_check = $this->auth_service->check_usage_limits();
        if ($usage_check['exceeded']) {
            return array(
                'success' => false,
                'error' => __('Usage limit exceeded. Please upgrade your plan or wait for the next billing cycle.', 'hmg-ai-blog-enhancer')
            );
        }

        // Validate content type
        if (!isset($this->prompts[$content_type])) {
            return array(
                'success' => false,
                'error' => __('Invalid content type requested.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check if we have API key
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'error' => __('Claude API key not configured. Please add your API key in the settings.', 'hmg-ai-blog-enhancer')
            );
        }

        // Clean and prepare content
        $cleaned_content = $this->clean_content($content);
        if (empty($cleaned_content)) {
            return array(
                'success' => false,
                'error' => __('No content provided for analysis.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check content cache first
        $cache_key = md5('claude_' . $content_type . $cleaned_content);
        $cached_result = $this->get_cached_content($cache_key);
        if ($cached_result) {
            return array(
                'success' => true,
                'content' => $cached_result,
                'cached' => true,
                'message' => __('Content retrieved from cache.', 'hmg-ai-blog-enhancer')
            );
        }

        // Generate content with Claude
        $result = $this->call_claude_api($content_type, $cleaned_content);
        
        if ($result['success']) {
            // Cache the result
            $this->cache_content($cache_key, $result['content']);
            
            // Record usage
            $this->auth_service->record_usage(
                $post_id,
                $content_type,
                1, // API calls
                $result['tokens_used'] ?? 0,
                'claude' // Provider name for cost tracking
            );
            
            return array(
                'success' => true,
                'content' => $result['content'],
                'tokens_used' => $result['tokens_used'] ?? 0,
                'message' => sprintf(
                    __('%s generated successfully!', 'hmg-ai-blog-enhancer'),
                    ucfirst($content_type)
                )
            );
        }

        return $result;
    }

    /**
     * Call Claude API for content generation
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate.
     * @param    string    $content         The cleaned content to analyze.
     * @return   array                      API call result.
     */
    private function call_claude_api($content_type, $content) {
        $prompt = $this->prompts[$content_type];
        $user_prompt = str_replace('{content}', $content, $prompt['user']);

        // Build request data with 2025 API structure
        $request_data = array(
            'model' => $this->selected_model,
            'max_tokens' => $this->get_max_tokens_for_model(),
            'temperature' => 0.7,
            'system' => $prompt['system'],
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $user_prompt
                )
            )
        );

        // Add extended thinking for Claude 4 and 3.7 models
        // Note: Disabled until API parameter is confirmed
        if ($this->supports_extended_thinking() && false) { // Temporarily disabled
            $request_data['extended_thinking'] = $this->get_extended_thinking_config();
        }

        $request_args = array(
            'method' => 'POST',
            'timeout' => $this->get_timeout_for_model(), // Dynamic timeout
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01', // Updated API version
                'User-Agent' => 'HMG-AI-Blog-Enhancer/' . HMG_AI_BLOG_ENHANCER_VERSION
            ),
            'body' => wp_json_encode($request_data)
        );

        $url = $this->api_base_url . '/messages';
        $response = wp_remote_post($url, $request_args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => sprintf(
                    __('API connection failed: %s', 'hmg-ai-blog-enhancer'),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) 
                ? $data['error']['message'] 
                : __('API request failed', 'hmg-ai-blog-enhancer');
                
            return array(
                'success' => false,
                'error' => sprintf(
                    __('Claude API error (%d): %s', 'hmg-ai-blog-enhancer'),
                    $response_code,
                    $error_message
                )
            );
        }

        if (!isset($data['content'][0]['text'])) {
            return array(
                'success' => false,
                'error' => __('Invalid response format from Claude API.', 'hmg-ai-blog-enhancer')
            );
        }

        $generated_content = $data['content'][0]['text'];
        $tokens_used = isset($data['usage']['output_tokens']) 
            ? $data['usage']['input_tokens'] + $data['usage']['output_tokens']
            : 0;

        return array(
            'success' => true,
            'content' => $this->format_generated_content($generated_content, $content_type),
            'tokens_used' => $tokens_used
        );
    }

    /**
     * Check if current model supports extended thinking
     *
     * @since    1.0.0
     * @return   bool    Whether the current model supports extended thinking.
     */
    private function supports_extended_thinking() {
        // Claude 4 and 3.7 models support extended thinking
        return strpos($this->selected_model, 'claude-sonnet-4') === 0 ||
               strpos($this->selected_model, 'claude-opus-4') === 0 ||
               strpos($this->selected_model, 'claude-3-7-sonnet') === 0;
    }

    /**
     * Get extended thinking configuration
     *
     * @since    1.0.0
     * @return   array    Extended thinking configuration.
     */
    private function get_extended_thinking_config() {
        // For content generation, we typically want balanced thinking
        return array(
            'enabled' => true,
            'mode' => 'balanced' // Options: fast, balanced, thorough
        );
    }

    /**
     * Get max tokens based on model capabilities
     *
     * @since    1.0.0
     * @return   int    Maximum tokens for the current model.
     */
    private function get_max_tokens_for_model() {
        // Set reasonable defaults based on 2025 model capabilities
        switch ($this->selected_model) {
            case 'claude-opus-4-20250514':
                return 32000; // Claude Opus 4 supports up to 32K output
                
            case 'claude-sonnet-4-20250514':
            case 'claude-3-7-sonnet-20250219':
                return 64000; // Claude Sonnet 4 and 3.7 support up to 64K output
                
            case 'claude-3-5-haiku-20241022':
                return 8192; // Haiku models have moderate output limits
                
            case 'claude-3-5-sonnet-20241022':
                return 8192; // Legacy Sonnet 3.5
                
            case 'claude-3-haiku-20240307':
            default:
                return 4096; // Conservative default for legacy models
        }
    }

    /**
     * Get timeout based on model type and capabilities
     *
     * @since    1.0.0
     * @return   int    Timeout in seconds.
     */
    private function get_timeout_for_model() {
        if ($this->supports_extended_thinking()) {
            // Extended thinking models may take longer
            switch ($this->selected_model) {
                case 'claude-opus-4-20250514':
                    return 180; // 3 minutes for most capable model
                    
                case 'claude-sonnet-4-20250514':
                case 'claude-3-7-sonnet-20250219':
                    return 120; // 2 minutes for high-performance models
                    
                default:
                    return 90; // 1.5 minutes for other thinking models
            }
        }
        
        // Standard models
        return 60; // 1 minute for regular models
    }

    /**
     * Clean and prepare content for API
     *
     * @since    1.0.0
     * @param    string    $content    Raw content to clean.
     * @return   string                Cleaned content.
     */
    private function clean_content($content) {
        // Remove HTML tags but preserve structure
        $content = wp_strip_all_tags($content, false);
        
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Trim and limit length (Claude has generous token limits)
        $content = trim($content);
        
        // Limit to approximately 15000 characters (Claude 3 Haiku has 200K context)
        if (strlen($content) > 15000) {
            $content = substr($content, 0, 15000) . '...';
        }
        
        return $content;
    }

    /**
     * Format generated content with Haley Marketing styling
     *
     * @since    1.0.0
     * @param    string    $content         Generated content from API.
     * @param    string    $content_type    Type of content generated.
     * @return   string                     Formatted content.
     */
    private function format_generated_content($content, $content_type) {
        // Add Haley Marketing CSS classes
        $content = str_replace(
            array('<div class="hmg-ai-', '<h3>', '<h4>'),
            array('<div class="hmg-ai-generated hmg-ai-', '<h3 class="hmg-ai-heading">', '<h4 class="hmg-ai-subheading">'),
            $content
        );

        // Add content type specific wrapper if not present
        if (strpos($content, 'hmg-ai-' . $content_type) === false) {
            $content = '<div class="hmg-ai-generated hmg-ai-' . $content_type . '">' . $content . '</div>';
        }

        // Add generation timestamp
        $content .= '<!-- Generated by HMG AI Blog Enhancer (Claude) on ' . current_time('Y-m-d H:i:s') . ' -->';

        return $content;
    }

    /**
     * Get cached content
     *
     * @since    1.0.0
     * @param    string    $cache_key    Cache key to retrieve.
     * @return   string|false            Cached content or false if not found.
     */
    private function get_cached_content($cache_key) {
        global $wpdb;
        
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        if (!($options['cache_enabled'] ?? true)) {
            return false;
        }

        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT content FROM {$cache_table} 
            WHERE cache_key = %s 
            AND expires_at > NOW() 
            LIMIT 1",
            $cache_key
        ));

        return $result ? $result : false;
    }

    /**
     * Cache generated content
     *
     * @since    1.0.0
     * @param    string    $cache_key    Cache key.
     * @param    string    $content      Content to cache.
     * @return   bool                    Whether caching was successful.
     */
    private function cache_content($cache_key, $content) {
        global $wpdb;
        
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        if (!($options['cache_enabled'] ?? true)) {
            return false;
        }

        $cache_duration = $options['cache_duration'] ?? 3600; // Default 1 hour
        $expires_at = date('Y-m-d H:i:s', time() + $cache_duration);
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        return $wpdb->replace(
            $cache_table,
            array(
                'cache_key' => $cache_key,
                'content' => $content,
                'created_at' => current_time('mysql'),
                'expires_at' => $expires_at
            ),
            array('%s', '%s', '%s', '%s')
        );
    }

    /**
     * Test API connection
     *
     * @since    1.0.0
     * @return   array    Test result with success status and message.
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('Claude API key not configured.', 'hmg-ai-blog-enhancer')
            );
        }

        $test_result = $this->generate_content(
            'summary',
            'This is a test content to verify the Claude API connection is working properly.',
            0
        );

        if ($test_result['success']) {
            return array(
                'success' => true,
                'message' => __('Claude API connection successful!', 'hmg-ai-blog-enhancer')
            );
        } else {
            return array(
                'success' => false,
                'message' => $test_result['error']
            );
        }
    }

    /**
     * Get supported content types
     *
     * @since    1.0.0
     * @return   array    Array of supported content types.
     */
    public function get_supported_content_types() {
        return array_keys($this->prompts);
    }

    /**
     * Get available models for this provider
     *
     * @since    1.0.0
     * @return   array    Available models with their specifications.
     */
    public function get_available_models() {
        return $this->available_models;
    }

    /**
     * Get current selected model information
     *
     * @since    1.0.0
     * @return   array    Current model information.
     */
    public function get_current_model_info() {
        return $this->available_models[$this->selected_model] ?? $this->available_models['claude-3-haiku-20240307'];
    }

    /**
     * Update selected model
     *
     * @since    1.0.0
     * @param    string    $model_id    Model ID to select.
     * @return   bool                   Whether the update was successful.
     */
    public function set_model($model_id) {
        if (isset($this->available_models[$model_id])) {
            $this->selected_model = $model_id;
            return true;
        }
        return false;
    }

    /**
     * Clean up expired cache entries
     *
     * @since    1.0.0
     * @return   int      Number of entries cleaned up.
     */
    public function cleanup_cache() {
        global $wpdb;
        
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        return $wpdb->query(
            "DELETE FROM {$cache_table} WHERE expires_at < NOW()"
        );
    }
} 