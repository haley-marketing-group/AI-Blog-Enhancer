<?php
/**
 * OpenAI Service
 *
 * Handles integration with OpenAI API for content generation
 * including takeaways, FAQ, table of contents, and content analysis.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * OpenAI Service Class
 *
 * Provides AI-powered content generation using OpenAI's GPT API.
 * Handles authentication, rate limiting, and professional content formatting.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_OpenAI_Service {

    /**
     * OpenAI API base URL
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    The base URL for OpenAI API calls.
     */
    private $api_base_url;

    /**
     * API key for OpenAI
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The API key for OpenAI.
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
     * Available OpenAI models
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $available_models    Available OpenAI models with their specifications.
     */
    private $available_models;

    /**
     * Initialize the OpenAI service
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_base_url = 'https://api.openai.com/v1';
        $this->auth_service = new HMG_AI_Auth_Service();
        
        // Get API key and model from options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->api_key = $options['openai_api_key'] ?? getenv('OPENAI_API_KEY') ?? '';
        $this->selected_model = $options['openai_model'] ?? 'o4-mini';
        
        $this->init_available_models();
        $this->init_prompts();
    }

    /**
     * Initialize available OpenAI models (2025 lineup)
     *
     * @since    1.0.0
     */
    private function init_available_models() {
        $this->available_models = array(
            'gpt-4.1' => array(
                'name' => 'GPT-4.1',
                'description' => 'Enhanced coding model with million-token context and superior instruction following',
                'context_length' => 1048576, // 1M tokens
                'cost_per_1k_tokens' => 2.00, // $2.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 8.00, // $8.00 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 10,
                'best_for' => array('coding', 'complex_analysis', 'long_content')
            ),
            'gpt-4.5' => array(
                'name' => 'GPT-4.5',
                'description' => 'Largest model for creative tasks and natural conversation',
                'context_length' => 128000,
                'cost_per_1k_tokens' => 75.00, // $75.00 per 1M tokens input
                'output_cost_per_1k_tokens' => 150.00, // $150.00 per 1M tokens output
                'speed_rating' => 6,
                'quality_rating' => 10,
                'best_for' => array('creative_content', 'premium_analysis', 'empathy_tasks')
            ),
            'gpt-4o' => array(
                'name' => 'GPT-4o',
                'description' => 'Multimodal flagship model with excellent reasoning capabilities',
                'context_length' => 128000,
                'cost_per_1k_tokens' => 2.50, // $2.50 per 1M tokens input
                'output_cost_per_1k_tokens' => 10.00, // $10.00 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 9,
                'best_for' => array('multimodal', 'balanced_performance', 'reasoning')
            ),
            'o4-mini' => array(
                'name' => 'o4-mini',
                'description' => 'Small reasoning model with excellent performance per dollar',
                'context_length' => 128000,
                'cost_per_1k_tokens' => 0.15, // $0.15 per 1M tokens input
                'output_cost_per_1k_tokens' => 0.60, // $0.60 per 1M tokens output
                'speed_rating' => 9,
                'quality_rating' => 8,
                'best_for' => array('reasoning', 'cost_effective', 'high_volume')
            ),
            'gpt-4o-mini' => array(
                'name' => 'GPT-4o Mini',
                'description' => 'Lightweight model for fast, cost-effective content generation',
                'context_length' => 128000,
                'cost_per_1k_tokens' => 0.15, // $0.15 per 1M tokens input
                'output_cost_per_1k_tokens' => 0.60, // $0.60 per 1M tokens output
                'speed_rating' => 10,
                'quality_rating' => 7,
                'best_for' => array('simple_tasks', 'high_volume', 'cost_sensitive')
            ),
            'gpt-3.5-turbo' => array(
                'name' => 'GPT-3.5 Turbo (Legacy)',
                'description' => 'Legacy model for basic content generation - Legacy support only',
                'context_length' => 16385,
                'cost_per_1k_tokens' => 0.50, // $0.50 per 1M tokens input
                'output_cost_per_1k_tokens' => 1.50, // $1.50 per 1M tokens output
                'speed_rating' => 8,
                'quality_rating' => 6,
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
     * Generate content using OpenAI
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
                'error' => __('OpenAI API key not configured. Please add your API key in the settings.', 'hmg-ai-blog-enhancer')
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
        $cache_key = md5('openai_' . $content_type . $cleaned_content);
        $cached_result = $this->get_cached_content($cache_key);
        if ($cached_result) {
            return array(
                'success' => true,
                'content' => $cached_result,
                'cached' => true,
                'message' => __('Content retrieved from cache.', 'hmg-ai-blog-enhancer')
            );
        }

        // Generate content with OpenAI
        $result = $this->call_openai_api($content_type, $cleaned_content);
        
        if ($result['success']) {
            // Cache the result
            $this->cache_content($cache_key, $result['content']);
            
            // Record usage
            $this->auth_service->record_usage(
                $post_id,
                $content_type,
                1, // API calls
                $result['tokens_used'] ?? 0,
                'openai' // Provider name for cost tracking
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
     * Call OpenAI API for content generation
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate.
     * @param    string    $content         The cleaned content to analyze.
     * @return   array                      API call result.
     */
    private function call_openai_api($content_type, $content) {
        $prompt = $this->prompts[$content_type];
        $user_prompt = str_replace('{content}', $content, $prompt['user']);

        // Build request data with 2025 API structure
        $request_data = array(
            'model' => $this->selected_model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $prompt['system']
                ),
                array(
                    'role' => 'user',
                    'content' => $user_prompt
                )
            )
        );

        // Add model-appropriate parameters
        $this->add_model_parameters($request_data);

        // Add appropriate token parameter based on model
        if ($this->uses_max_completion_tokens()) {
            $request_data['max_completion_tokens'] = $this->get_max_tokens_for_model();
        } else {
            $request_data['max_tokens'] = $this->get_max_tokens_for_model();
        }

        // Add reasoning effort for o-series models
        if ($this->is_reasoning_model()) {
            $request_data['reasoning_effort'] = $this->get_reasoning_effort();
        }

        $request_args = array(
            'method' => 'POST',
            'timeout' => $this->get_timeout_for_model(), // Dynamic timeout based on model
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
                'User-Agent' => 'HMG-AI-Blog-Enhancer/' . HMG_AI_BLOG_ENHANCER_VERSION
            ),
            'body' => wp_json_encode($request_data)
        );

        $url = $this->api_base_url . '/chat/completions';
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
                    __('OpenAI API error (%d): %s', 'hmg-ai-blog-enhancer'),
                    $response_code,
                    $error_message
                )
            );
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => false,
                'error' => __('Invalid response format from OpenAI API.', 'hmg-ai-blog-enhancer')
            );
        }

        $generated_content = $data['choices'][0]['message']['content'];
        $tokens_used = isset($data['usage']['total_tokens']) 
            ? $data['usage']['total_tokens'] 
            : 0;

        return array(
            'success' => true,
            'content' => $this->format_generated_content($generated_content, $content_type),
            'tokens_used' => $tokens_used
        );
    }

    /**
     * Check if current model is a reasoning model (o-series)
     *
     * @since    1.0.0
     * @return   bool    Whether the current model is a reasoning model.
     */
    private function is_reasoning_model() {
        return strpos($this->selected_model, 'o') === 0; // o4-mini, o3, etc.
    }

    /**
     * Check if current model uses max_completion_tokens instead of max_tokens
     *
     * @since    1.0.0
     * @return   bool    Whether to use max_completion_tokens parameter.
     */
    private function uses_max_completion_tokens() {
        // Newer models (GPT-4.1+, o-series) use max_completion_tokens
        $newer_models = array('gpt-4.1', 'gpt-4o', 'gpt-4.5', 'o4-mini', 'o3', 'o3-mini');
        
        foreach ($newer_models as $model) {
            if (strpos($this->selected_model, $model) === 0) {
                return true;
            }
        }
        
        return false; // Legacy models use max_tokens
    }

    /**
     * Add model-appropriate parameters to request data
     *
     * @since    1.0.0
     * @param    array    $request_data    Request data to modify.
     */
    private function add_model_parameters(&$request_data) {
        // For o-series models, use minimal parameters (they have strict requirements)
        if ($this->is_reasoning_model()) {
            // o-series models are very restrictive with parameters
            // Only add what's absolutely necessary
            return; // Use default temperature (1.0) and no other parameters
        }
        
        // For other models, add standard parameters
        $request_data['temperature'] = 0.7;
        $request_data['top_p'] = 0.9;
        $request_data['frequency_penalty'] = 0.0;
        $request_data['presence_penalty'] = 0.0;
    }

    /**
     * Get reasoning effort for o-series models
     *
     * @since    1.0.0
     * @return   string    Reasoning effort level.
     */
    private function get_reasoning_effort() {
        // For content generation, we typically want medium effort
        // for balance between quality and speed
        switch ($this->selected_model) {
            case 'o4-mini':
            case 'o4-mini-high':
                return 'medium'; // Good balance for content generation
                
            case 'o3':
            case 'o3-mini':
                return 'high'; // Use full reasoning for complex models
                
            default:
                return 'medium';
        }
    }

    /**
     * Get max tokens based on model capabilities
     *
     * @since    1.0.0
     * @return   int    Maximum tokens for the current model.
     */
    private function get_max_tokens_for_model() {
        $model_info = $this->get_current_model_info();
        
        // Set reasonable defaults based on 2025 model capabilities
        switch ($this->selected_model) {
            case 'o4-mini':
            case 'o4-mini-high':
                return 4096; // o4-mini supports up to 100K output, but 4K is good for content
                
            case 'gpt-4.1':
            case 'gpt-4o':
                return 4096; // GPT-4 models support higher output
                
            case 'gpt-4.5':
                return 8192; // Premium model, higher output
                
            case 'gpt-4o-mini':
                return 2048; // Mini model, moderate output
                
            case 'gpt-3.5-turbo':
            default:
                return 1000; // Conservative default
        }
    }

    /**
     * Get timeout based on model type
     *
     * @since    1.0.0
     * @return   int    Timeout in seconds.
     */
    private function get_timeout_for_model() {
        if ($this->is_reasoning_model()) {
            // Reasoning models take longer, especially with high effort
            $effort = $this->get_reasoning_effort();
            switch ($effort) {
                case 'high':
                    return 180; // 3 minutes for high effort reasoning
                case 'medium':
                    return 120; // 2 minutes for medium effort
                default:
                    return 90;  // 1.5 minutes for low effort
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
        
        // Trim and limit length (OpenAI has token limits)
        $content = trim($content);
        
        // Limit to approximately 6000 characters (rough token estimate for GPT-3.5)
        if (strlen($content) > 6000) {
            $content = substr($content, 0, 6000) . '...';
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
        $content .= '<!-- Generated by HMG AI Blog Enhancer (OpenAI) on ' . current_time('Y-m-d H:i:s') . ' -->';

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
                'message' => __('OpenAI API key not configured.', 'hmg-ai-blog-enhancer')
            );
        }

        $test_result = $this->generate_content(
            'summary',
            'This is a test content to verify the OpenAI API connection is working properly.',
            0
        );

        if ($test_result['success']) {
            return array(
                'success' => true,
                'message' => __('OpenAI API connection successful!', 'hmg-ai-blog-enhancer')
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
        return $this->available_models[$this->selected_model] ?? $this->available_models['gpt-3.5-turbo'];
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