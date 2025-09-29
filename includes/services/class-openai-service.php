<?php
/**
 * OpenAI Service
 *
 * Handles integration with OpenAI (GPT-4/GPT-3.5) for content generation
 * including takeaways, FAQ, table of contents, and content analysis.
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * OpenAI Service Class
 *
 * Provides AI-powered content generation using OpenAI's GPT models.
 * Handles authentication, rate limiting, and professional content formatting.
 *
 * @since      1.1.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_OpenAI_Service {

    /**
     * OpenAI API base URL
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $api_base_url    The base URL for OpenAI API calls.
     */
    private $api_base_url;

    /**
     * API key for OpenAI
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $api_key    The API key for OpenAI.
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
     * Available OpenAI models
     *
     * @since    1.1.0
     * @access   private
     * @var      array    $available_models    Available OpenAI models with their specifications.
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
     * Initialize the OpenAI service
     *
     * @since    1.1.0
     */
    public function __construct() {
        $this->api_base_url = 'https://api.openai.com/v1';
        
        // Get API key and model from options
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->api_key = $options['openai_api_key'] ?? getenv('OPENAI_API_KEY') ?? '';
        $this->selected_model = $options['openai_model'] ?? 'gpt-4o-mini';
        
        $this->init_available_models();
        $this->init_prompts();
        
        // Initialize authentication service if available
        if (class_exists('HMG_AI_Auth_Service')) {
            $this->auth_service = new HMG_AI_Auth_Service();
        }
    }

    /**
     * Initialize available OpenAI models
     *
     * @since    1.1.0
     */
    private function init_available_models() {
        $this->available_models = array(
            'gpt-5-ultra' => array(
                'name' => 'GPT-5 Ultra',
                'description' => 'Most advanced AI model with unprecedented capabilities',
                'context_window' => 256000,
                'max_output' => 32768,
                'cost_per_1k_input' => 0.015,
                'cost_per_1k_output' => 0.045,
                'recommended_for' => array('advanced reasoning', 'complex analysis', 'creative excellence')
            ),
            'gpt-5' => array(
                'name' => 'GPT-5',
                'description' => 'Next-generation model with superior understanding',
                'context_window' => 256000,
                'max_output' => 16384,
                'cost_per_1k_input' => 0.008,
                'cost_per_1k_output' => 0.024,
                'recommended_for' => array('complex content', 'nuanced writing', 'professional tasks')
            ),
            'gpt-5-mini' => array(
                'name' => 'GPT-5 Mini',
                'description' => 'Efficient GPT-5 variant for everyday tasks',
                'context_window' => 128000,
                'max_output' => 8192,
                'cost_per_1k_input' => 0.002,
                'cost_per_1k_output' => 0.006,
                'recommended_for' => array('general content', 'quick generation', 'cost-effective')
            ),
            'gpt-4o' => array(
                'name' => 'GPT-4o',
                'description' => 'Multimodal model with 128K context',
                'context_window' => 128000,
                'max_output' => 4096,
                'cost_per_1k_input' => 0.005,
                'cost_per_1k_output' => 0.015,
                'recommended_for' => array('complex content', 'technical writing', 'premium features')
            ),
            'gpt-4o-mini' => array(
                'name' => 'GPT-4o Mini',
                'description' => 'Affordable, smaller model with great performance',
                'context_window' => 128000,
                'max_output' => 16384,
                'cost_per_1k_input' => 0.00015,
                'cost_per_1k_output' => 0.0006,
                'recommended_for' => array('general content', 'summaries', 'FAQs')
            ),
            'gpt-3.5-turbo' => array(
                'name' => 'GPT-3.5 Turbo',
                'description' => 'Fast and cost-effective for simple tasks',
                'context_window' => 16385,
                'max_output' => 4096,
                'cost_per_1k_input' => 0.0005,
                'cost_per_1k_output' => 0.0015,
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
     * Generate content using OpenAI
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
                'error' => __('OpenAI API key is not configured. Please add your API key in the settings.', 'hmg-ai-blog-enhancer')
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
            $cache_key = 'hmg_ai_openai_' . $content_type . '_' . $post_id . '_' . md5($content);
            $cached_content = get_transient($cache_key);
            
            if ($cached_content !== false) {
                return array(
                    'success' => true,
                    'content' => $cached_content,
                    'cached' => true,
                    'provider' => 'openai',
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
        $max_tokens_field = 'max_tokens';
        // Use max_completion_tokens for newer models
        if (in_array($this->selected_model, ['gpt-5-ultra', 'gpt-5', 'gpt-5-mini', 'gpt-4o', 'gpt-4o-mini', 'o1-mini', 'o1-preview'])) {
            $max_tokens_field = 'max_completion_tokens';
        }
        
        $request_body = array(
            'model' => $this->selected_model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a professional content creator specializing in creating structured, engaging content for blogs. Follow the format instructions exactly.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => 0.7,
            $max_tokens_field => $this->available_models[$this->selected_model]['max_output'] ?? 4096,
            'top_p' => 0.9,
            'frequency_penalty' => 0.3,
            'presence_penalty' => 0.3
        );

        // Make API call
        $response = wp_remote_post(
            $this->api_base_url . '/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
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
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => false,
                'error' => __('Unexpected API response format.', 'hmg-ai-blog-enhancer')
            );
        }

        $generated_content = trim($data['choices'][0]['message']['content']);

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
                    'provider' => 'openai',
                    'model' => $this->selected_model,
                    'type' => $content_type,
                    'tokens' => $data['usage']['total_tokens'] ?? 0
                )
            );
        }

        return array(
            'success' => true,
            'content' => $formatted_content,
            'provider' => 'openai',
            'model' => $this->selected_model,
            'usage' => array(
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $data['usage']['total_tokens'] ?? 0
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
     * Test the OpenAI connection
     *
     * @since    1.1.0
     * @return   array    Result array with success status and message.
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key is not configured.', 'hmg-ai-blog-enhancer')
            );
        }

        // Test with a simple completion
        $max_tokens_field = 'max_tokens';
        // Use max_completion_tokens for newer models
        if (in_array($this->selected_model, ['gpt-5-ultra', 'gpt-5', 'gpt-5-mini', 'gpt-4o', 'gpt-4o-mini', 'o1-mini', 'o1-preview'])) {
            $max_tokens_field = 'max_completion_tokens';
        }
        
        $response = wp_remote_post(
            $this->api_base_url . '/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'model' => $this->selected_model,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => 'Say "Connection successful"'
                        )
                    ),
                    $max_tokens_field => 10
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
            return array(
                'success' => true,
                'message' => sprintf(__('Successfully connected to OpenAI using %s model.', 'hmg-ai-blog-enhancer'), $this->available_models[$this->selected_model]['name'])
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
            $options['openai_model'] = $model_id;
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
                '_transient_timeout_hmg_ai_openai_%',
                time()
            )
        );
    }
}