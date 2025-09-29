<?php
/**
 * Anthropic Claude Service
 *
 * Handles integration with Anthropic Claude for content generation
 * including takeaways, FAQ, table of contents, and content analysis.
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * Claude Service Class
 *
 * Provides AI-powered content generation using Anthropic's Claude models.
 * Handles authentication, rate limiting, and professional content formatting.
 *
 * @since      1.1.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Claude_Service {

    /**
     * Claude API base URL
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $api_base_url    The base URL for Claude API calls.
     */
    private $api_base_url;

    /**
     * API key for Claude
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $api_key    The API key for Claude.
     */
    private $api_key;

    /**
     * Authentication service instance
     *
     * @since    1.1.0
     * @access   private
     * @var      HMG_AI_Auth_Service    $auth_service    Authentication service instance.
     */
    private $auth_service;

    /**
     * Content generation prompts
     *
     * @since    1.1.0
     * @access   private
     * @var      array    $prompts    Predefined prompts for different content types.
     */
    private $prompts;

    /**
     * Available Claude models
     *
     * @since    1.1.0
     * @access   private
     * @var      array    $available_models    Available Claude models with their specifications.
     */
    private $available_models;

    /**
     * Selected model
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $selected_model    The currently selected model.
     */
    private $selected_model;

    /**
     * Initialize the Claude service
     *
     * @since    1.1.0
     */
    public function __construct() {
        $this->api_base_url = 'https://api.anthropic.com/v1';
        
        // Get API key and model from options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->api_key = $options['claude_api_key'] ?? getenv('ANTHROPIC_API_KEY') ?? '';
        $this->selected_model = $options['claude_model'] ?? 'claude-3-5-sonnet-20241022';
        
        $this->init_available_models();
        $this->init_prompts();
        
        // Initialize authentication service if available
        if (class_exists('HMG_AI_Auth_Service')) {
            $this->auth_service = new HMG_AI_Auth_Service();
        }
    }

    /**
     * Initialize available Claude models
     *
     * @since    1.1.0
     */
    private function init_available_models() {
        $this->available_models = array(
            'claude-sonnet-4-20250514' => array(
                'name' => 'Claude 4 Sonnet',
                'description' => 'Latest high-performance model with excellent reasoning',
                'context_window' => 200000,
                'max_output' => 8192,
                'cost_per_1k_input' => 0.003,
                'cost_per_1k_output' => 0.015,
                'recommended_for' => array('complex content', 'creative writing', 'analysis')
            ),
            'claude-opus-4-20250514' => array(
                'name' => 'Claude 4 Opus',
                'description' => 'Most intelligent model with advanced capabilities',
                'context_window' => 200000,
                'max_output' => 8192,
                'cost_per_1k_input' => 0.015,
                'cost_per_1k_output' => 0.075,
                'recommended_for' => array('complex reasoning', 'nuanced content', 'premium features')
            ),
            'claude-3-5-sonnet-20241022' => array(
                'name' => 'Claude 3.5 Sonnet',
                'description' => 'Excellent balance of intelligence and speed',
                'context_window' => 200000,
                'max_output' => 8192,
                'cost_per_1k_input' => 0.003,
                'cost_per_1k_output' => 0.015,
                'recommended_for' => array('general content', 'creative tasks', 'analysis')
            ),
            'claude-3-5-haiku-20241022' => array(
                'name' => 'Claude 3.5 Haiku',
                'description' => 'Fast and affordable for routine tasks',
                'context_window' => 200000,
                'max_output' => 8192,
                'cost_per_1k_input' => 0.001,
                'cost_per_1k_output' => 0.005,
                'recommended_for' => array('simple tasks', 'quick responses', 'high volume')
            ),
            'claude-3-haiku-20240307' => array(
                'name' => 'Claude 3 Haiku',
                'description' => 'Budget-friendly option for simple tasks',
                'context_window' => 200000,
                'max_output' => 4096,
                'cost_per_1k_input' => 0.00025,
                'cost_per_1k_output' => 0.00125,
                'recommended_for' => array('simple tasks', 'quick responses', 'budget-conscious')
            )
        );
    }

    /**
     * Initialize content generation prompts
     *
     * @since    1.1.0
     */
    private function init_prompts() {
        $this->prompts = array(
            'takeaways' => 'Generate 3-5 key takeaways from the following content. 
Format each takeaway as a bullet point starting with "• ".
Keep each takeaway concise (1-2 sentences).
Focus on actionable insights and important points.
Do NOT include any headers, titles, or HTML tags.
Just return the bullet points, nothing else.

Content:
{content}',
            
            'faq' => 'Generate 4-6 frequently asked questions and answers based on the following content.
Format EXACTLY as follows:
Q: [Question text]
A: [Answer text]

Requirements:
- Each question must start with "Q: "
- Each answer must start with "A: "
- Keep answers concise but informative (2-3 sentences)
- Questions should address common concerns or clarifications
- Do NOT include any headers, titles, or HTML tags
- Do NOT number the questions
- Just return the Q&A pairs, nothing else

Content:
{content}',
            
            'toc' => 'Generate a table of contents for the following content.
Format as a numbered list (1., 2., 3., etc.).
Include main topics and subtopics where appropriate.
Keep items concise and descriptive.
Do NOT include any headers, titles, or HTML tags.
Just return the numbered list, nothing else.

Content:
{content}',
            
            'audio' => 'Convert the following content into natural, conversational text suitable for text-to-speech.
Make it flow naturally when spoken aloud.
Remove any formatting, links, or visual references.
Keep the tone professional but engaging.

Content:
{content}',
            
            'summary' => 'Create a concise summary of the following content in 2-3 paragraphs.
Focus on the main points and key information.
Keep it informative and engaging.

Content:
{content}'
        );
    }

    /**
     * Generate content using Claude
     *
     * @since    1.1.0
     * @param    string    $content_type    The type of content to generate.
     * @param    string    $content         The source content.
     * @param    int       $post_id         Optional. The post ID for caching.
     * @return   array                      Result array with success status and generated content or error message.
     */
    public function generate_content($content_type, $content, $post_id = 0, $options = array()) {
        // Check if API key is configured
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'error' => __('Claude API key is not configured. Please add your API key in the settings.', 'hmg-ai-blog-enhancer')
            );
        }

        // Validate content type
        if (!isset($this->prompts[$content_type])) {
            return array(
                'success' => false,
                'error' => sprintf(__('Invalid content type: %s', 'hmg-ai-blog-enhancer'), $content_type)
            );
        }

        // Check content length
        if (empty($content)) {
            return array(
                'success' => false,
                'error' => __('No content provided for generation.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check cache if post_id is provided
        if ($post_id) {
            $cache_key = 'hmg_ai_claude_' . $content_type . '_' . $post_id . '_' . md5($content);
            $cached_content = get_transient($cache_key);
            
            if ($cached_content !== false) {
                return array(
                    'success' => true,
                    'content' => $cached_content,
                    'cached' => true,
                    'provider' => 'claude',
                    'model' => $this->selected_model
                );
            }
        }

        // Prepare the prompt
        $base_prompt = $this->prompts[$content_type];
        
        // Add brand context if provided
        if (!empty($options['brand_context'])) {
            $base_prompt = "Important Context: " . $options['brand_context'] . "\n\n" . $base_prompt;
        }
        
        $prompt = str_replace('{content}', $content, $base_prompt);

        // Prepare API request
        $request_body = array(
            'model' => $this->selected_model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $this->available_models[$this->selected_model]['max_output'] ?? 4096,
            'temperature' => 0.7
        );

        // Make API call
        $response = wp_remote_post(
            $this->api_base_url . '/messages',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($request_body),
                'timeout' => 30,
                'sslverify' => true
            )
        );

        // Handle errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => sprintf(__('API request failed: %s', 'hmg-ai-blog-enhancer'), $response->get_error_message())
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : sprintf(__('API returned error code: %d', 'hmg-ai-blog-enhancer'), $response_code);
            
            return array(
                'success' => false,
                'error' => $error_message
            );
        }

        // Parse response
        $data = json_decode($response_body, true);
        
        if (!isset($data['content'][0]['text'])) {
            return array(
                'success' => false,
                'error' => __('Unexpected API response format.', 'hmg-ai-blog-enhancer')
            );
        }

        $generated_content = trim($data['content'][0]['text']);

        // Format content based on type
        $formatted_content = $this->format_content_for_type($generated_content, $content_type);

        // Cache the result if post_id is provided
        if ($post_id && !empty($cache_key)) {
            set_transient($cache_key, $formatted_content, DAY_IN_SECONDS);
        }

        // Track usage if auth service is available and has track_usage method
        if ($this->auth_service && method_exists($this->auth_service, 'track_usage')) {
            $this->auth_service->track_usage(
                'content_generation',
                array(
                    'provider' => 'claude',
                    'model' => $this->selected_model,
                    'type' => $content_type,
                    'tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0)
                )
            );
        }

        return array(
            'success' => true,
            'content' => $formatted_content,
            'provider' => 'claude',
            'model' => $this->selected_model,
            'usage' => array(
                'input_tokens' => $data['usage']['input_tokens'] ?? 0,
                'output_tokens' => $data['usage']['output_tokens'] ?? 0,
                'total_tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0)
            )
        );
    }

    /**
     * Format content based on type for consistency
     *
     * @since    1.1.0
     * @param    string    $content         The generated content.
     * @param    string    $content_type    The type of content.
     * @return   string                     Formatted content.
     */
    private function format_content_for_type($content, $content_type) {
        // Remove any markdown formatting that might have been added
        $content = str_replace('```', '', $content);
        $content = trim($content);

        switch ($content_type) {
            case 'takeaways':
                // Ensure bullet points are consistent
                $lines = explode("\n", $content);
                $formatted_lines = array();
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Ensure line starts with bullet point
                        if (!preg_match('/^[•\-\*]/', $line)) {
                            $line = '• ' . $line;
                        } else {
                            // Standardize to bullet point
                            $line = preg_replace('/^[\-\*]/', '•', $line);
                        }
                        $formatted_lines[] = $line;
                    }
                }
                return implode("\n", $formatted_lines);

            case 'faq':
                // Ensure Q: and A: format
                $lines = explode("\n", $content);
                $formatted_lines = array();
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Ensure proper Q: and A: prefixes
                        if (preg_match('/^(Question:|Q\.|Question \d+:|FAQ \d+:)/i', $line)) {
                            $line = preg_replace('/^(Question:|Q\.|Question \d+:|FAQ \d+:)/i', 'Q:', $line);
                        }
                        if (preg_match('/^(Answer:|A\.|Answer:)/i', $line)) {
                            $line = preg_replace('/^(Answer:|A\.|Answer:)/i', 'A:', $line);
                        }
                        $formatted_lines[] = $line;
                    }
                }
                return implode("\n", $formatted_lines);

            case 'toc':
                // Ensure numbered list format
                $lines = explode("\n", $content);
                $formatted_lines = array();
                $number = 1;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Remove existing numbering and re-number
                        $line = preg_replace('/^(\d+[\.\)]\s*|\-\s*|\*\s*|•\s*)/', '', $line);
                        $formatted_lines[] = $number . '. ' . $line;
                        $number++;
                    }
                }
                return implode("\n", $formatted_lines);

            default:
                return $content;
        }
    }

    /**
     * Test the Claude connection
     *
     * @since    1.1.0
     * @return   array    Result array with success status and message.
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('Claude API key is not configured.', 'hmg-ai-blog-enhancer')
            );
        }

        // Test with a simple completion
        $response = wp_remote_post(
            $this->api_base_url . '/messages',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'model' => $this->selected_model,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => 'Say "Connection successful" in exactly those words.'
                        )
                    ),
                    'max_tokens' => 10
                )),
                'timeout' => 10,
                'sslverify' => true
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'hmg-ai-blog-enhancer'), $response->get_error_message())
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            $model_info = $this->available_models[$this->selected_model] ?? array('name' => 'Unknown');
            return array(
                'success' => true,
                'message' => sprintf(__('Successfully connected to Claude using %s model.', 'hmg-ai-blog-enhancer'), $model_info['name'])
            );
        }

        $response_body = wp_remote_retrieve_body($response);
        $error_data = json_decode($response_body, true);
        $error_message = isset($error_data['error']['message']) 
            ? $error_data['error']['message'] 
            : sprintf(__('API returned error code: %d', 'hmg-ai-blog-enhancer'), $response_code);

        return array(
            'success' => false,
            'message' => $error_message
        );
    }

    /**
     * Get supported content types
     *
     * @since    1.1.0
     * @return   array    Array of supported content types.
     */
    public function get_supported_content_types() {
        return array_keys($this->prompts);
    }

    /**
     * Get available models
     *
     * @since    1.1.0
     * @return   array    Array of available models.
     */
    public function get_available_models() {
        return $this->available_models;
    }

    /**
     * Get current model info
     *
     * @since    1.1.0
     * @return   array    Current model information.
     */
    public function get_current_model_info() {
        return array(
            'id' => $this->selected_model,
            'info' => $this->available_models[$this->selected_model] ?? null
        );
    }

    /**
     * Set the model to use
     *
     * @since    1.1.0
     * @param    string    $model_id    The model ID to use.
     * @return   bool                   Success status.
     */
    public function set_model($model_id) {
        if (isset($this->available_models[$model_id])) {
            $this->selected_model = $model_id;
            
            // Save to options
            $options = get_option('hmg_ai_blog_enhancer_options', array());
            $options['claude_model'] = $model_id;
            update_option('hmg_ai_blog_enhancer_options', $options);
            
            return true;
        }
        return false;
    }

    /**
     * Clean up old cache entries
     *
     * @since    1.1.0
     */
    public function cleanup_cache() {
        global $wpdb;
        
        // Clean up transients older than 7 days
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                AND option_value < %s",
                '_transient_timeout_hmg_ai_claude_%',
                time()
            )
        );
    }
}