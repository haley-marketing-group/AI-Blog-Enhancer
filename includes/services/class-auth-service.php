<?php
/**
 * Authentication Service
 *
 * Handles API key validation, user tier management, and authentication
 * with Haley Marketing servers for the HMG AI Blog Enhancer plugin.
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 */

/**
 * Authentication Service Class
 *
 * Manages authentication, API key validation, and user tier management.
 * Supports both standalone and base plugin authentication modes.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Auth_Service {

    /**
     * API base URL for authentication
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    The base URL for API calls.
     */
    private $api_base_url;

    /**
     * Plugin options
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    Plugin options array.
     */
    private $options;

    /**
     * User tier levels
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $tier_levels    Available user tiers.
     */
    private $tier_levels;

    /**
     * Initialize the authentication service
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_base_url = 'https://api.haleymarketing.com/ai-enhancer/v1';
        $this->options = get_option('hmg_ai_blog_enhancer_options', array());
        
        $this->tier_levels = array(
            'free' => array(
                'name' => 'Free',
                'api_calls_limit' => 50,
                'tokens_limit' => 10000,
                'features' => array('takeaways', 'toc'),
                'priority' => 'standard'
            ),
            'pro' => array(
                'name' => 'Pro',
                'api_calls_limit' => 1000,
                'tokens_limit' => 100000,
                'features' => array('takeaways', 'faq', 'toc', 'audio'),
                'priority' => 'high'
            ),
            'premium' => array(
                'name' => 'Premium',
                'api_calls_limit' => 5000,
                'tokens_limit' => 500000,
                'features' => array('takeaways', 'faq', 'toc', 'audio', 'advanced_analytics'),
                'priority' => 'highest'
            )
        );
    }

    /**
     * Validate API key with authentication server
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to validate.
     * @return   array                 Validation result with user info.
     */
    public function validate_api_key($api_key) {
        if (empty($api_key)) {
            return array(
                'valid' => false,
                'error' => __('API key is required.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check if we're in development mode
        if (defined('WP_DEBUG') && WP_DEBUG && $this->is_development_key($api_key)) {
            return $this->get_development_validation_response($api_key);
        }

        // Check for base plugin authentication first
        $base_plugin_auth = $this->check_base_plugin_authentication();
        if ($base_plugin_auth['authenticated']) {
            return $this->validate_with_base_plugin($api_key, $base_plugin_auth);
        }

        // Standalone authentication
        return $this->validate_standalone_api_key($api_key);
    }

    /**
     * Check if base plugin provides authentication
     *
     * @since    1.0.0
     * @return   array    Base plugin authentication status.
     */
    private function check_base_plugin_authentication() {
        // Check for HMG Base Plugin
        if (function_exists('hmg_get_auth_status')) {
            $auth_status = hmg_get_auth_status();
            if ($auth_status && isset($auth_status['authenticated']) && $auth_status['authenticated']) {
                return array(
                    'authenticated' => true,
                    'method' => 'base_plugin',
                    'user_data' => $auth_status
                );
            }
        }

        // Check for other HMG plugins that might provide auth
        $hmg_plugins = array(
            'hmg-seo-toolkit/hmg-seo-toolkit.php',
            'hmg-analytics-pro/hmg-analytics-pro.php',
            'hmg-content-manager/hmg-content-manager.php'
        );

        foreach ($hmg_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $auth_function = str_replace(array('-', '/'), '_', dirname($plugin)) . '_get_auth';
                if (function_exists($auth_function)) {
                    $auth_data = call_user_func($auth_function);
                    if ($auth_data && $auth_data['authenticated']) {
                        return array(
                            'authenticated' => true,
                            'method' => 'hmg_plugin',
                            'plugin' => $plugin,
                            'user_data' => $auth_data
                        );
                    }
                }
            }
        }

        return array('authenticated' => false);
    }

    /**
     * Validate API key using base plugin authentication
     *
     * @since    1.0.0
     * @param    string    $api_key           The API key to validate.
     * @param    array     $base_plugin_auth  Base plugin authentication data.
     * @return   array                        Validation result.
     */
    private function validate_with_base_plugin($api_key, $base_plugin_auth) {
        // Use base plugin's authentication and extend it for our service
        $user_data = $base_plugin_auth['user_data'];
        
        return array(
            'valid' => true,
            'method' => 'base_plugin',
            'user_id' => $user_data['user_id'] ?? 'unknown',
            'email' => $user_data['email'] ?? '',
            'tier' => $user_data['ai_tier'] ?? 'pro', // Base plugin users get pro by default
            'features' => $this->tier_levels[$user_data['ai_tier'] ?? 'pro']['features'],
            'limits' => $this->tier_levels[$user_data['ai_tier'] ?? 'pro'],
            'expires' => $user_data['expires'] ?? null,
            'message' => __('Authenticated via HMG Base Plugin', 'hmg-ai-blog-enhancer')
        );
    }

    /**
     * Validate standalone API key
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to validate.
     * @return   array                 Validation result.
     */
    private function validate_standalone_api_key($api_key) {
        $request_args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'HMG-AI-Blog-Enhancer/' . HMG_AI_BLOG_ENHANCER_VERSION,
                'X-Site-URL' => home_url()
            ),
            'body' => wp_json_encode(array(
                'api_key' => sanitize_text_field($api_key),
                'domain' => parse_url(home_url(), PHP_URL_HOST),
                'plugin_version' => HMG_AI_BLOG_ENHANCER_VERSION,
                'wp_version' => get_bloginfo('version')
            ))
        );

        $response = wp_remote_post($this->api_base_url . '/auth/validate', $request_args);

        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'error' => sprintf(
                    __('Connection error: %s', 'hmg-ai-blog-enhancer'),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            return array(
                'valid' => false,
                'error' => $data['message'] ?? __('API key validation failed.', 'hmg-ai-blog-enhancer')
            );
        }

        if (!$data || !isset($data['valid']) || !$data['valid']) {
            return array(
                'valid' => false,
                'error' => $data['message'] ?? __('Invalid API key.', 'hmg-ai-blog-enhancer')
            );
        }

        // Cache the validation result
        $cache_key = 'hmg_ai_auth_' . md5($api_key);
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

        return array(
            'valid' => true,
            'method' => 'standalone',
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'tier' => $data['tier'],
            'features' => $this->tier_levels[$data['tier']]['features'],
            'limits' => $this->tier_levels[$data['tier']],
            'expires' => $data['expires'] ?? null,
            'message' => __('API key validated successfully.', 'hmg-ai-blog-enhancer')
        );
    }

    /**
     * Check if API key is a development key
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to check.
     * @return   bool                  Whether it's a development key.
     */
    private function is_development_key($api_key) {
        $dev_keys = array(
            'dev_free_' . wp_hash('development'),
            'dev_pro_' . wp_hash('development'),
            'dev_premium_' . wp_hash('development'),
            'hmg_dev_test_key',
            'local_development_key'
        );

        return in_array($api_key, $dev_keys) || strpos($api_key, 'dev_') === 0;
    }

    /**
     * Get development validation response
     *
     * @since    1.0.0
     * @param    string    $api_key    The development API key.
     * @return   array                 Development validation response.
     */
    private function get_development_validation_response($api_key) {
        // Determine tier from development key
        $tier = 'pro'; // Default
        if (strpos($api_key, 'free') !== false) {
            $tier = 'free';
        } elseif (strpos($api_key, 'premium') !== false) {
            $tier = 'premium';
        }

        return array(
            'valid' => true,
            'method' => 'development',
            'user_id' => 'dev_user_' . get_current_user_id(),
            'email' => get_option('admin_email'),
            'tier' => $tier,
            'features' => $this->tier_levels[$tier]['features'],
            'limits' => $this->tier_levels[$tier],
            'expires' => null,
            'message' => __('Development mode - API key accepted.', 'hmg-ai-blog-enhancer')
        );
    }

    /**
     * Get current user authentication status
     *
     * @since    1.0.0
     * @return   array    Current authentication status.
     */
    public function get_auth_status() {
        $api_key = $this->get_stored_api_key();
        
        if (empty($api_key)) {
            return array(
                'authenticated' => false,
                'message' => __('No API key configured.', 'hmg-ai-blog-enhancer')
            );
        }

        // Check cache first
        $cache_key = 'hmg_ai_auth_status_' . md5($api_key);
        $cached_status = get_transient($cache_key);
        
        if ($cached_status !== false) {
            return $cached_status;
        }

        // Validate API key
        $validation = $this->validate_api_key($api_key);
        
        if (!$validation['valid']) {
            $status = array(
                'authenticated' => false,
                'message' => $validation['error']
            );
        } else {
            $status = array(
                'authenticated' => true,
                'method' => $validation['method'],
                'user_id' => $validation['user_id'],
                'email' => $validation['email'],
                'tier' => $validation['tier'],
                'features' => $validation['features'],
                'limits' => $validation['limits'],
                'expires' => $validation['expires'],
                'message' => $validation['message']
            );
        }

        // Cache the status for 15 minutes
        set_transient($cache_key, $status, 15 * MINUTE_IN_SECONDS);
        
        return $status;
    }

    /**
     * Check if user has access to a specific feature
     *
     * @since    1.0.0
     * @param    string    $feature    The feature to check.
     * @return   bool                  Whether user has access.
     */
    public function has_feature_access($feature) {
        $auth_status = $this->get_auth_status();
        
        if (!$auth_status['authenticated']) {
            return false;
        }

        return in_array($feature, $auth_status['features']);
    }

    /**
     * Get user tier information
     *
     * @since    1.0.0
     * @return   array    User tier information.
     */
    public function get_user_tier() {
        $auth_status = $this->get_auth_status();
        
        if (!$auth_status['authenticated']) {
            return $this->tier_levels['free'];
        }

        return $this->tier_levels[$auth_status['tier']];
    }

    /**
     * Get stored API key
     *
     * @since    1.0.0
     * @return   string    The stored API key.
     */
    public function get_stored_api_key() {
        return $this->options['api_key'] ?? '';
    }

    /**
     * Store API key
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to store.
     * @return   bool                  Whether the key was stored successfully.
     */
    public function store_api_key($api_key) {
        $this->options['api_key'] = sanitize_text_field($api_key);
        $result = update_option('hmg_ai_blog_enhancer_options', $this->options);
        
        // Clear auth cache when API key changes
        $this->clear_auth_cache();
        
        return $result;
    }

    /**
     * Clear authentication cache
     *
     * @since    1.0.0
     */
    public function clear_auth_cache() {
        $api_key = $this->get_stored_api_key();
        
        if (!empty($api_key)) {
            delete_transient('hmg_ai_auth_' . md5($api_key));
            delete_transient('hmg_ai_auth_status_' . md5($api_key));
        }
        
        delete_transient('hmg_ai_api_status');
    }

    /**
     * Get usage statistics for current user
     *
     * @since    1.0.0
     * @return   array    Usage statistics.
     */
    public function get_usage_stats() {
        global $wpdb;
        
        $auth_status = $this->get_auth_status();
        if (!$auth_status['authenticated']) {
            return array(
                'api_calls_used' => 0,
                'tokens_used' => 0,
                'reset_date' => date('Y-m-d', strtotime('first day of next month'))
            );
        }

        $user_limits = $auth_status['limits'];
        $current_month = date('Y-m');
        
        // Get usage from database
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        $usage_query = $wpdb->prepare(
            "SELECT 
                SUM(api_calls_used) as api_calls_used,
                SUM(tokens_used) as tokens_used
            FROM {$usage_table} 
            WHERE user_id = %s 
            AND DATE_FORMAT(created_at, '%%Y-%%m') = %s",
            $auth_status['user_id'],
            $current_month
        );
        
        $usage_data = $wpdb->get_row($usage_query, ARRAY_A);
        
        return array(
            'api_calls_used' => (int) ($usage_data['api_calls_used'] ?? 0),
            'api_calls_limit' => $user_limits['api_calls_limit'],
            'tokens_used' => (int) ($usage_data['tokens_used'] ?? 0),
            'tokens_limit' => $user_limits['tokens_limit'],
            'reset_date' => date('Y-m-d', strtotime('first day of next month')),
            'tier' => $auth_status['tier'],
            'tier_name' => $user_limits['name']
        );
    }

    /**
     * Record API usage
     *
     * @since    1.0.0
     * @param    int       $post_id        The post ID.
     * @param    string    $feature_type   The feature used.
     * @param    int       $api_calls      Number of API calls used.
     * @param    int       $tokens         Number of tokens used.
     * @return   bool                      Whether usage was recorded.
     */
    public function record_usage($post_id, $feature_type, $api_calls = 1, $tokens = 0) {
        global $wpdb;
        
        $auth_status = $this->get_auth_status();
        if (!$auth_status['authenticated']) {
            return false;
        }

        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        return $wpdb->insert(
            $usage_table,
            array(
                'user_id' => $auth_status['user_id'],
                'post_id' => (int) $post_id,
                'feature_type' => sanitize_text_field($feature_type),
                'api_calls_used' => (int) $api_calls,
                'tokens_used' => (int) $tokens,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%d', '%s', '%d', '%d', '%s')
        );
    }

    /**
     * Check if user has exceeded usage limits
     *
     * @since    1.0.0
     * @return   array    Usage limit status.
     */
    public function check_usage_limits() {
        $usage_stats = $this->get_usage_stats();
        
        $limits_exceeded = array();
        
        if ($usage_stats['api_calls_used'] >= $usage_stats['api_calls_limit']) {
            $limits_exceeded[] = 'api_calls';
        }
        
        if ($usage_stats['tokens_used'] >= $usage_stats['tokens_limit']) {
            $limits_exceeded[] = 'tokens';
        }
        
        return array(
            'exceeded' => !empty($limits_exceeded),
            'limits_exceeded' => $limits_exceeded,
            'usage_stats' => $usage_stats
        );
    }
} 