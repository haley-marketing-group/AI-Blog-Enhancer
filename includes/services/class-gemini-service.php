<?php
/**
 * Google Gemini AI Service
 *
 * Handles integration with Google Gemini AI for content generation
 * including takeaways, FAQ, table of contents, and content analysis.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * Google Gemini AI Service Class
 *
 * Provides AI-powered content generation using Google's Gemini API.
 * Handles authentication, rate limiting, and professional content formatting.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Gemini_Service {

    /**
     * Gemini API base URL
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    The base URL for Gemini API calls.
     */
    private $api_base_url;

    /**
     * API key for Gemini
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The API key for Gemini.
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
     * Available Gemini models
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $available_models    Available Gemini models with their specifications.
     */
    private $available_models;

    /**
     * Initialize the Gemini service
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_base_url = 'https://generativelanguage.googleapis.com/v1beta';
        $this->auth_service = new HMG_AI_Auth_Service();
        
        // Get API key and model from options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->api_key = $options['gemini_api_key'] ?? getenv('GEMINI_API_KEY') ?? '';
        $this->selected_model = $options['gemini_model'] ?? 'gemini-2.5-flash';
        
        $this->init_available_models();
        $this->init_prompts();
    }

    /**
     * Initialize available Gemini models (2025 lineup)
     *
     * @since    1.0.0
     */
    private function init_available_models() {
        $this->available_models = array(
            'gemini-2.5-pro' => array(
                'name' => 'Gemini 2.5 Pro',
                'description' => 'Most capable thinking model with enhanced reasoning and coding',
                'context_length' => 1048576, // 1M tokens
                'cost_per_1k_tokens' => 1.25, // $1.25 per 1M tokens (≤200k), $2.50 (>200k)
                'output_cost_per_1k_tokens' => 10.00, // $10.00 per 1M tokens
                'speed_rating' => 8,
                'quality_rating' => 10,
                'best_for' => array('complex_analysis', 'coding', 'premium_content', 'faq')
            ),
            'gemini-2.5-flash' => array(
                'name' => 'Gemini 2.5 Flash',
                'description' => 'Hybrid reasoning model with thinking budgets - Best balance of speed and quality',
                'context_length' => 1048576, // 1M tokens
                'cost_per_1k_tokens' => 0.30, // $0.30 per 1M tokens
                'output_cost_per_1k_tokens' => 2.50, // $2.50 per 1M tokens
                'speed_rating' => 9,
                'quality_rating' => 9,
                'best_for' => array('takeaways', 'faq', 'balanced_usage')
            ),
            'gemini-2.5-flash-lite' => array(
                'name' => 'Gemini 2.5 Flash-Lite',
                'description' => 'Most cost-effective model for high-throughput content generation',
                'context_length' => 1000000, // 1M tokens
                'cost_per_1k_tokens' => 0.10, // $0.10 per 1M tokens
                'output_cost_per_1k_tokens' => 0.40, // $0.40 per 1M tokens
                'speed_rating' => 10,
                'quality_rating' => 8,
                'best_for' => array('toc', 'quick_content', 'high_volume')
            ),
            'gemini-2.0-flash' => array(
                'name' => 'Gemini 2.0 Flash',
                'description' => 'Multimodal model optimized for agent workflows',
                'context_length' => 1048576, // 1M tokens
                'cost_per_1k_tokens' => 0.10, // $0.10 per 1M tokens
                'output_cost_per_1k_tokens' => 0.40, // $0.40 per 1M tokens
                'speed_rating' => 9,
                'quality_rating' => 8,
                'best_for' => array('multimodal', 'agent_workflows', 'balanced_usage')
            ),
            'gemini-1.5-flash' => array(
                'name' => 'Gemini 1.5 Flash (Legacy)',
                'description' => 'Fast multimodal model - Legacy support for existing workflows',
                'context_length' => 1048576, // 1M tokens
                'cost_per_1k_tokens' => 0.075, // $0.075 per 1M tokens (≤128k), $0.15 (>128k)
                'output_cost_per_1k_tokens' => 0.30, // $0.30 per 1M tokens
                'speed_rating' => 9,
                'quality_rating' => 8,
                'best_for' => array('legacy_support', 'fast_generation')
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
                'system' => 'You are a professional content analyst for Haley Marketing, specializing in creating concise, actionable key takeaways from blog content. Focus on practical insights that readers can immediately apply.',
                'user' => 'Analyze the following blog content and create 3-5 key takeaways. Format as HTML with <ul> and <li> tags. Each takeaway should be concise (1-2 sentences) and actionable. Focus on the most valuable insights for the reader.

Content to analyze:
{content}

Please format your response as:
<div class="hmg-ai-takeaways">
<h3>Key Takeaways</h3>
<ul>
<li>First actionable takeaway</li>
<li>Second actionable takeaway</li>
</ul>
</div>'
            ),
            'faq' => array(
                'system' => 'You are a professional content strategist for Haley Marketing, expert at identifying common questions readers might have about blog content and providing clear, helpful answers.',
                'user' => 'Based on the following blog content, generate 3-5 frequently asked questions that readers might have, along with clear, professional answers. Format as HTML with proper structure.

Content to analyze:
{content}

Please format your response as:
<div class="hmg-ai-faq">
<h3>Frequently Asked Questions</h3>
<div class="faq-item">
<h4>Question 1?</h4>
<p>Clear, professional answer.</p>
</div>
</div>'
            ),
            'toc' => array(
                'system' => 'You are a professional content organizer for Haley Marketing, expert at creating logical, user-friendly table of contents structures from blog content.',
                'user' => 'Analyze the following blog content and create a table of contents based on the headings and content structure. Generate anchor links and organize hierarchically.

Content to analyze:
{content}

Please format your response as:
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
     * Generate content using Gemini AI
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
                'error' => __('Gemini API key not configured. Please add your API key in the settings.', 'hmg-ai-blog-enhancer')
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
        $cache_key = md5($content_type . $cleaned_content);
        $cached_result = $this->get_cached_content($cache_key);
        if ($cached_result) {
            return array(
                'success' => true,
                'content' => $cached_result,
                'cached' => true,
                'message' => __('Content retrieved from cache.', 'hmg-ai-blog-enhancer')
            );
        }

        // Generate content with Gemini
        $result = $this->call_gemini_api($content_type, $cleaned_content);
        
        if ($result['success']) {
            // Cache the result
            $this->cache_content($cache_key, $result['content']);
            
            // Record usage
            $this->auth_service->record_usage(
                $post_id,
                $content_type,
                1, // API calls
                $result['tokens_used'] ?? 0,
                'gemini' // Provider name for cost tracking
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
     * Call Gemini API for content generation
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate.
     * @param    string    $content         The cleaned content to analyze.
     * @return   array                      API call result.
     */
    private function call_gemini_api($content_type, $content) {
        $prompt = $this->prompts[$content_type];
        $user_prompt = str_replace('{content}', $content, $prompt['user']);

        // Build request data with 2025 API structure
        $request_data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt['system'] . "\n\n" . $user_prompt
                        )
                    )
                )
            ),
            'generationConfig' => $this->get_generation_config(),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                )
            )
        );

        $request_args = array(
            'method' => 'POST',
            'timeout' => 120, // Increased timeout for thinking models
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'HMG-AI-Blog-Enhancer/' . HMG_AI_BLOG_ENHANCER_VERSION
            ),
            'body' => wp_json_encode($request_data)
        );

        // Use proper 2025 API endpoint with models/ prefix
        $url = $this->api_base_url . '/models/' . $this->selected_model . ':generateContent?key=' . $this->api_key;
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
                    __('Gemini API error (%d): %s', 'hmg-ai-blog-enhancer'),
                    $response_code,
                    $error_message
                )
            );
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return array(
                'success' => false,
                'error' => __('Invalid response format from Gemini API.', 'hmg-ai-blog-enhancer')
            );
        }

        $generated_content = $data['candidates'][0]['content']['parts'][0]['text'];
        $tokens_used = isset($data['usageMetadata']['totalTokenCount']) 
            ? $data['usageMetadata']['totalTokenCount'] 
            : 0;

        return array(
            'success' => true,
            'content' => $this->format_generated_content($generated_content, $content_type),
            'tokens_used' => $tokens_used
        );
    }

    /**
     * Check if current model supports thinking budget parameter
     *
     * @since    1.0.0
     * @return   bool    Whether the model supports thinking budget.
     */
    private function supports_thinking_budget() {
        // For now, disable thinking budget until API supports it
        // This can be enabled once Google confirms the parameter name
        return false;
    }

    /**
     * Get generation config based on selected model (2025 API)
     *
     * @since    1.0.0
     * @return   array    Generation configuration for the current model.
     */
    private function get_generation_config() {
        $model_info = $this->get_current_model_info();
        
        $config = array(
            'temperature' => 0.7,
            'topP' => 0.95,
            'maxOutputTokens' => 8192, // Increased for 2025 models
        );
        
        // Add thinking budget only for models that explicitly support it
        // Note: Disabled until API parameter is confirmed
        if ($this->supports_thinking_budget()) {
            $config['thinking_budget'] = 'medium'; // Options: low, medium, high
        }
        
        // Model-specific adjustments
        switch ($this->selected_model) {
            case 'gemini-2.5-pro':
                $config['maxOutputTokens'] = 65536; // Pro models support higher output
                // thinking_budget removed until API supports it
                break;
                
            case 'gemini-2.5-flash':
                $config['maxOutputTokens'] = 8192;
                // thinking_budget removed until API supports it
                break;
                
            case 'gemini-2.5-flash-lite':
                $config['maxOutputTokens'] = 4096;
                // thinking_budget removed until API supports it
                break;
                
            case 'gemini-2.0-flash':
                $config['maxOutputTokens'] = 8192;
                // No thinking budget for 2.0 models
                break;
        }
        
        return $config;
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
        
        // Trim and limit length (Gemini has token limits)
        $content = trim($content);
        
        // Limit to approximately 8000 characters (rough token estimate)
        if (strlen($content) > 8000) {
            $content = substr($content, 0, 8000) . '...';
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
        $content .= '<!-- Generated by HMG AI Blog Enhancer on ' . current_time('Y-m-d H:i:s') . ' -->';

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
                'message' => __('Gemini API key not configured.', 'hmg-ai-blog-enhancer')
            );
        }

        $test_result = $this->generate_content(
            'summary',
            'This is a test content to verify the Gemini API connection is working properly.',
            0
        );

        if ($test_result['success']) {
            return array(
                'success' => true,
                'message' => __('Gemini API connection successful!', 'hmg-ai-blog-enhancer')
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
        return $this->available_models[$this->selected_model] ?? $this->available_models['gemini-1.5-flash'];
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