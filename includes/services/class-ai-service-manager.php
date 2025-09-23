<?php
/**
 * AI Service Manager
 *
 * Coordinates between different AI service providers (Gemini, OpenAI)
 * and manages content generation routing, fallbacks, and load balancing.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * AI Service Manager Class
 *
 * Manages multiple AI service providers and handles intelligent routing,
 * fallbacks, and load balancing for content generation requests.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Service_Manager {

    /**
     * Available AI service providers
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $providers    Available AI service providers.
     */
    private $providers;

    /**
     * Authentication service instance
     *
     * @since    1.0.0
     * @access   private
     * @var      HMG_AI_Auth_Service    $auth_service    Authentication service instance.
     */
    private $auth_service;

    /**
     * Plugin options
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    Plugin options.
     */
    private $options;

    /**
     * Initialize the AI service manager
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->auth_service = new HMG_AI_Auth_Service();
        $this->options = get_option('hmg_ai_blog_enhancer_options', array());
        $this->init_providers();
    }

    /**
     * Initialize AI service providers
     *
     * @since    1.0.0
     */
    private function init_providers() {
        $this->providers = array(
            'gemini' => array(
                'name' => 'Google Gemini',
                'class' => 'HMG_AI_Gemini_Service',
                'instance' => null,
                'priority' => $this->options['gemini_priority'] ?? 1,
                'enabled' => $this->options['gemini_enabled'] ?? true,
                'features' => array('takeaways', 'faq', 'toc', 'summary'),
                'cost_per_token' => 0.00001, // Approximate cost
                'speed_rating' => 8, // 1-10 scale
                'quality_rating' => 9
            ),
            'openai' => array(
                'name' => 'OpenAI GPT',
                'class' => 'HMG_AI_OpenAI_Service',
                'instance' => null,
                'priority' => $this->options['openai_priority'] ?? 2,
                'enabled' => $this->options['openai_enabled'] ?? true,
                'features' => array('takeaways', 'faq', 'toc', 'summary'),
                'cost_per_token' => 0.00002, // Approximate cost
                'speed_rating' => 7,
                'quality_rating' => 9
            ),
            'claude' => array(
                'name' => 'Anthropic Claude',
                'class' => 'HMG_AI_Claude_Service',
                'instance' => null,
                'priority' => $this->options['claude_priority'] ?? 3,
                'enabled' => $this->options['claude_enabled'] ?? true,
                'features' => array('takeaways', 'faq', 'toc', 'summary'),
                'cost_per_token' => 0.000008, // Claude 3 Haiku pricing
                'speed_rating' => 9, // Claude is very fast
                'quality_rating' => 10 // Excellent quality
            )
        );

        // Sort providers by priority (lower number = higher priority)
        uasort($this->providers, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Generate content using the best available AI service
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate.
     * @param    string    $content         Source content to analyze.
     * @param    int       $post_id         Post ID for tracking.
     * @param    array     $options         Additional options.
     * @return   array                      Generation result.
     */
    public function generate_content($content_type, $content, $post_id = 0, $options = array()) {
        // Check authentication first
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

        // Get preferred provider or determine best one
        $preferred_provider = $options['provider'] ?? $this->get_best_provider($content_type, $options);
        
        if (!$preferred_provider) {
            return array(
                'success' => false,
                'error' => __('No AI service providers are available or configured.', 'hmg-ai-blog-enhancer')
            );
        }

        // Try the preferred provider first
        $result = $this->try_provider($preferred_provider, $content_type, $content, $post_id, $options);
        
        if ($result['success']) {
            return $result;
        }

        // If preferred provider failed, try fallback providers
        $fallback_providers = $this->get_fallback_providers($preferred_provider, $content_type);
        
        foreach ($fallback_providers as $provider_key) {
            $fallback_result = $this->try_provider($provider_key, $content_type, $content, $post_id, $options);
            
            if ($fallback_result['success']) {
                // Add note about fallback
                $fallback_result['provider_used'] = $provider_key;
                $fallback_result['fallback_used'] = true;
                $fallback_result['original_error'] = $result['error'];
                
                return $fallback_result;
            }
        }

        // All providers failed
        return array(
            'success' => false,
            'error' => sprintf(
                __('Content generation failed. Primary error: %s', 'hmg-ai-blog-enhancer'),
                $result['error']
            ),
            'provider_errors' => $this->get_all_provider_errors($content_type, $content, $post_id)
        );
    }

    /**
     * Try a specific AI provider for content generation
     *
     * @since    1.0.0
     * @param    string    $provider_key    Provider key.
     * @param    string    $content_type    Type of content to generate.
     * @param    string    $content         Source content.
     * @param    int       $post_id         Post ID.
     * @param    array     $options         Additional options.
     * @return   array                      Generation result.
     */
    private function try_provider($provider_key, $content_type, $content, $post_id, $options) {
        if (!isset($this->providers[$provider_key]) || !$this->providers[$provider_key]['enabled']) {
            return array(
                'success' => false,
                'error' => sprintf(__('Provider %s is not available.', 'hmg-ai-blog-enhancer'), $provider_key)
            );
        }

        $provider = $this->get_provider_instance($provider_key);
        
        if (!$provider) {
            return array(
                'success' => false,
                'error' => sprintf(__('Failed to initialize provider %s.', 'hmg-ai-blog-enhancer'), $provider_key)
            );
        }

        // Check if provider supports the content type
        if (!in_array($content_type, $this->providers[$provider_key]['features'])) {
            return array(
                'success' => false,
                'error' => sprintf(
                    __('Provider %s does not support %s generation.', 'hmg-ai-blog-enhancer'),
                    $this->providers[$provider_key]['name'],
                    $content_type
                )
            );
        }

        try {
            $start_time = microtime(true);
            $result = $provider->generate_content($content_type, $content, $post_id);
            $generation_time = microtime(true) - $start_time;

            if ($result['success']) {
                $result['provider_used'] = $provider_key;
                $result['provider_name'] = $this->providers[$provider_key]['name'];
                $result['generation_time'] = round($generation_time, 2);
                
                // Log successful generation
                $this->log_generation_success($provider_key, $content_type, $generation_time, $result['tokens_used'] ?? 0);
            } else {
                // Log generation failure
                $this->log_generation_failure($provider_key, $content_type, $result['error']);
            }

            return $result;

        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => sprintf(
                    __('Provider %s encountered an error: %s', 'hmg-ai-blog-enhancer'),
                    $this->providers[$provider_key]['name'],
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get the best AI provider for a content type
     *
     * @since    1.0.0
     * @param    string    $content_type    Type of content to generate.
     * @param    array     $options         Additional options.
     * @return   string|null                Best provider key or null.
     */
    private function get_best_provider($content_type, $options = array()) {
        $available_providers = array();

        foreach ($this->providers as $key => $provider) {
            if (!$provider['enabled'] || !in_array($content_type, $provider['features'])) {
                continue;
            }

            // Check if provider is configured
            $provider_instance = $this->get_provider_instance($key);
            if (!$provider_instance) {
                continue;
            }

            $available_providers[$key] = $provider;
        }

        if (empty($available_providers)) {
            return null;
        }

        // If user specified cost optimization
        if (isset($options['optimize_for']) && $options['optimize_for'] === 'cost') {
            $best_provider = min($available_providers);
            return array_search($best_provider, $available_providers);
        }

        // If user specified speed optimization
        if (isset($options['optimize_for']) && $options['optimize_for'] === 'speed') {
            $best_speed = 0;
            $best_provider = null;
            
            foreach ($available_providers as $key => $provider) {
                if ($provider['speed_rating'] > $best_speed) {
                    $best_speed = $provider['speed_rating'];
                    $best_provider = $key;
                }
            }
            
            return $best_provider;
        }

        // Default: return highest priority (lowest priority number)
        return array_key_first($available_providers);
    }

    /**
     * Get fallback providers for a failed provider
     *
     * @since    1.0.0
     * @param    string    $failed_provider    The provider that failed.
     * @param    string    $content_type       Type of content to generate.
     * @return   array                         Array of fallback provider keys.
     */
    private function get_fallback_providers($failed_provider, $content_type) {
        $fallbacks = array();

        foreach ($this->providers as $key => $provider) {
            if ($key === $failed_provider || !$provider['enabled']) {
                continue;
            }

            if (in_array($content_type, $provider['features'])) {
                $fallbacks[] = $key;
            }
        }

        return $fallbacks;
    }

    /**
     * Get provider instance
     *
     * @since    1.0.0
     * @param    string    $provider_key    Provider key.
     * @return   object|null                Provider instance or null.
     */
    private function get_provider_instance($provider_key) {
        if (!isset($this->providers[$provider_key])) {
            return null;
        }

        // Return cached instance if available
        if ($this->providers[$provider_key]['instance']) {
            return $this->providers[$provider_key]['instance'];
        }

        // Create new instance
        $class_name = $this->providers[$provider_key]['class'];
        
        if (!class_exists($class_name)) {
            return null;
        }

        try {
            $instance = new $class_name();
            $this->providers[$provider_key]['instance'] = $instance;
            return $instance;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Test all configured AI providers
     *
     * @since    1.0.0
     * @return   array    Test results for all providers.
     */
    public function test_all_providers() {
        $results = array();

        foreach ($this->providers as $key => $provider) {
            if (!$provider['enabled']) {
                $results[$key] = array(
                    'success' => false,
                    'message' => __('Provider is disabled.', 'hmg-ai-blog-enhancer'),
                    'name' => $provider['name']
                );
                continue;
            }

            $provider_instance = $this->get_provider_instance($key);
            
            if (!$provider_instance) {
                $results[$key] = array(
                    'success' => false,
                    'message' => __('Failed to initialize provider.', 'hmg-ai-blog-enhancer'),
                    'name' => $provider['name']
                );
                continue;
            }

            if (method_exists($provider_instance, 'test_connection')) {
                try {
                    $test_result = $provider_instance->test_connection();
                    $results[$key] = array_merge($test_result, array('name' => $provider['name']));
                } catch (Exception $e) {
                    $results[$key] = array(
                        'success' => false,
                        'message' => sprintf(__('Test failed: %s', 'hmg-ai-blog-enhancer'), $e->getMessage()),
                        'name' => $provider['name']
                    );
                }
            } else {
                $results[$key] = array(
                    'success' => false,
                    'message' => __('Provider does not support connection testing.', 'hmg-ai-blog-enhancer'),
                    'name' => $provider['name']
                );
            }
        }

        return $results;
    }

    /**
     * Get available providers and their status
     *
     * @since    1.0.0
     * @return   array    Array of providers with their status.
     */
    public function get_providers_status() {
        $status = array();

        foreach ($this->providers as $key => $provider) {
            $provider_instance = $this->get_provider_instance($key);
            
            $status[$key] = array(
                'name' => $provider['name'],
                'enabled' => $provider['enabled'],
                'configured' => $provider_instance !== null,
                'priority' => $provider['priority'],
                'features' => $provider['features'],
                'speed_rating' => $provider['speed_rating'],
                'quality_rating' => $provider['quality_rating']
            );
        }

        return $status;
    }

    /**
     * Log successful content generation
     *
     * @since    1.0.0
     * @param    string    $provider         Provider key.
     * @param    string    $content_type     Content type generated.
     * @param    float     $generation_time  Time taken to generate.
     * @param    int       $tokens_used      Tokens consumed.
     */
    private function log_generation_success($provider, $content_type, $generation_time, $tokens_used) {
        // Update provider performance metrics
        $metrics_key = 'hmg_ai_provider_metrics_' . $provider;
        $metrics = get_option($metrics_key, array(
            'total_requests' => 0,
            'successful_requests' => 0,
            'total_time' => 0,
            'total_tokens' => 0,
            'last_success' => null
        ));

        $metrics['total_requests']++;
        $metrics['successful_requests']++;
        $metrics['total_time'] += $generation_time;
        $metrics['total_tokens'] += $tokens_used;
        $metrics['last_success'] = current_time('mysql');

        update_option($metrics_key, $metrics);
    }

    /**
     * Log failed content generation
     *
     * @since    1.0.0
     * @param    string    $provider        Provider key.
     * @param    string    $content_type    Content type attempted.
     * @param    string    $error           Error message.
     */
    private function log_generation_failure($provider, $content_type, $error) {
        // Update provider performance metrics
        $metrics_key = 'hmg_ai_provider_metrics_' . $provider;
        $metrics = get_option($metrics_key, array(
            'total_requests' => 0,
            'successful_requests' => 0,
            'total_time' => 0,
            'total_tokens' => 0,
            'last_failure' => null,
            'last_error' => null
        ));

        $metrics['total_requests']++;
        $metrics['last_failure'] = current_time('mysql');
        $metrics['last_error'] = $error;

        update_option($metrics_key, $metrics);
    }

    /**
     * Get provider performance metrics
     *
     * @since    1.0.0
     * @return   array    Performance metrics for all providers.
     */
    public function get_provider_metrics() {
        $metrics = array();

        foreach ($this->providers as $key => $provider) {
            $provider_metrics = get_option('hmg_ai_provider_metrics_' . $key, array());
            
            if (!empty($provider_metrics)) {
                $success_rate = $provider_metrics['total_requests'] > 0 
                    ? ($provider_metrics['successful_requests'] / $provider_metrics['total_requests']) * 100 
                    : 0;
                    
                $avg_time = $provider_metrics['successful_requests'] > 0 
                    ? $provider_metrics['total_time'] / $provider_metrics['successful_requests'] 
                    : 0;

                $metrics[$key] = array(
                    'name' => $provider['name'],
                    'success_rate' => round($success_rate, 1),
                    'total_requests' => $provider_metrics['total_requests'],
                    'successful_requests' => $provider_metrics['successful_requests'],
                    'average_time' => round($avg_time, 2),
                    'total_tokens' => $provider_metrics['total_tokens'],
                    'last_success' => $provider_metrics['last_success'] ?? null,
                    'last_failure' => $provider_metrics['last_failure'] ?? null,
                    'last_error' => $provider_metrics['last_error'] ?? null
                );
            } else {
                $metrics[$key] = array(
                    'name' => $provider['name'],
                    'success_rate' => 0,
                    'total_requests' => 0,
                    'successful_requests' => 0,
                    'average_time' => 0,
                    'total_tokens' => 0,
                    'last_success' => null,
                    'last_failure' => null,
                    'last_error' => null
                );
            }
        }

        return $metrics;
    }

    /**
     * Get all provider errors for debugging
     *
     * @since    1.0.0
     * @param    string    $content_type    Content type.
     * @param    string    $content         Source content.
     * @param    int       $post_id         Post ID.
     * @return   array                      All provider errors.
     */
    private function get_all_provider_errors($content_type, $content, $post_id) {
        $errors = array();

        foreach ($this->providers as $key => $provider) {
            if (!$provider['enabled']) {
                continue;
            }

            $result = $this->try_provider($key, $content_type, $content, $post_id, array());
            
            if (!$result['success']) {
                $errors[$key] = array(
                    'name' => $provider['name'],
                    'error' => $result['error']
                );
            }
        }

        return $errors;
    }

    /**
     * Clean up old provider metrics
     *
     * @since    1.0.0
     * @param    int    $days_old    Days to keep metrics.
     */
    public function cleanup_old_metrics($days_old = 30) {
        // This is a placeholder for future implementation
        // Could clean up old detailed logs while keeping summary metrics
    }
} 