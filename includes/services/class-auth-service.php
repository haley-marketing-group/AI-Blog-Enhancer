<?php
/**
 * Authentication Service
 *
 * Handles API key validation, spending limit management, and cost tracking
 * for the HMG AI Blog Enhancer plugin with user-defined budgets.
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
 * Manages authentication, API key validation, and spending limit management.
 * Users set their own monthly budgets and we help them track costs.
 *
 * @since      1.0.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes/services
 * @author     Haley Marketing <support@haleymarketing.com>
 */
class HMG_AI_Auth_Service {

    /**
     * Plugin options
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    Plugin options array.
     */
    private $options;

    /**
     * Default spending limits (in USD)
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $default_limits    Default spending limits.
     */
    private $default_limits;

    /**
     * Provider cost rates (per 1K tokens)
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $provider_costs    Cost per 1K tokens by provider.
     */
    private $provider_costs;

    /**
     * Initialize the authentication service
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->options = get_option('hmg_ai_blog_enhancer_options', array());
        
        // Default spending limit options
        $this->default_limits = array(
            'conservative' => array(
                'name' => 'Conservative ($5/month)',
                'monthly_limit' => 5.00,
                'daily_limit' => 0.25,
                'warning_threshold' => 0.80, // 80%
                'description' => 'Perfect for occasional content generation'
            ),
            'moderate' => array(
                'name' => 'Moderate ($15/month)',
                'monthly_limit' => 15.00,
                'daily_limit' => 0.75,
                'warning_threshold' => 0.80,
                'description' => 'Good for regular blog posting'
            ),
            'active' => array(
                'name' => 'Active ($30/month)',
                'monthly_limit' => 30.00,
                'daily_limit' => 1.50,
                'warning_threshold' => 0.80,
                'description' => 'Ideal for frequent content creation'
            ),
            'professional' => array(
                'name' => 'Professional ($75/month)',
                'monthly_limit' => 75.00,
                'daily_limit' => 3.75,
                'warning_threshold' => 0.80,
                'description' => 'For high-volume content production'
            ),
            'custom' => array(
                'name' => 'Custom Amount',
                'monthly_limit' => 0.00, // User sets this
                'daily_limit' => 0.00,   // Calculated as monthly/30
                'warning_threshold' => 0.80,
                'description' => 'Set your own spending limit'
            )
        );

        // Default provider costs (will be updated based on selected models)
        $this->provider_costs = array(
            'gemini' => 0.00075,  // Default: Gemini 1.5 Flash
            'openai' => 0.0015,   // Default: GPT-3.5 Turbo
            'claude' => 0.00025   // Default: Claude 3 Haiku
        );
        
        // Update costs based on selected models
        $this->update_provider_costs();
    }

    /**
     * Update provider costs based on selected models
     *
     * @since    1.0.0
     */
    private function update_provider_costs() {
        // Model cost mappings (cost per 1000 tokens)
        $model_costs = array(
            // Gemini models - Updated 2025 pricing
            'gemini-2.5-flash' => 0.00075,  // $0.75 per 1M tokens
            'gemini-2.5-pro' => 0.00125,    // $1.25 per 1M tokens
            'gemini-2.5-flash-lite' => 0.0001, // $0.10 per 1M tokens
            'gemini-2.0-flash' => 0.0001,   // $0.10 per 1M tokens
            'gemini-1.5-flash' => 0.000075, // $0.075 per 1M tokens
            'gemini-1.5-pro' => 0.0035,
            'gemini-1.0-pro' => 0.0005,
            
            // OpenAI models  
            'gpt-3.5-turbo' => 0.0015,
            'gpt-4o-mini' => 0.00015,
            'gpt-4-turbo' => 0.01,
            'gpt-4' => 0.03,
            
            // Claude models
            'claude-3-haiku-20240307' => 0.00025,
            'claude-3-sonnet-20240229' => 0.003,
            'claude-3-5-sonnet-20241022' => 0.003,
            'claude-3-opus-20240229' => 0.015
        );
        
        // Update costs based on selected models
        $selected_gemini_model = $this->options['gemini_model'] ?? 'gemini-2.5-flash';
        $selected_openai_model = $this->options['openai_model'] ?? 'gpt-3.5-turbo';
        $selected_claude_model = $this->options['claude_model'] ?? 'claude-3-haiku-20240307';
        
        $this->provider_costs['gemini'] = $model_costs[$selected_gemini_model] ?? 0.00075;
        $this->provider_costs['openai'] = $model_costs[$selected_openai_model] ?? 0.0015;
        $this->provider_costs['claude'] = $model_costs[$selected_claude_model] ?? 0.00025;
    }

    /**
     * Ensure database tables exist
     *
     * @since    1.0.0
     */
    private function ensure_tables_exist() {
        // Only check/create tables on admin pages, not during AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Also skip during cron or CLI
        if (defined('DOING_CRON') || defined('WP_CLI')) {
            return;
        }
        
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
        
        if (!$table_exists) {
            // Create tables if they don't exist
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE IF NOT EXISTS $usage_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                post_id bigint(20) NOT NULL,
                feature_type varchar(50) NOT NULL,
                provider varchar(50) DEFAULT 'unknown',
                api_calls_used int(11) DEFAULT 0,
                tokens_used int(11) DEFAULT 0,
                estimated_cost decimal(10,4) DEFAULT 0.0000,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY post_id (post_id),
                KEY feature_type (feature_type),
                KEY provider (provider),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            dbDelta($sql);
            
            // Also create cache table
            $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
            $cache_exists = $wpdb->get_var("SHOW TABLES LIKE '$cache_table'");
            
            if (!$cache_exists) {
                $cache_sql = "CREATE TABLE IF NOT EXISTS $cache_table (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    content_hash varchar(64) NOT NULL,
                    feature_type varchar(50) NOT NULL,
                    generated_content longtext NOT NULL,
                    expires_at datetime NOT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY content_hash (content_hash, feature_type),
                    KEY expires_at (expires_at)
                ) $charset_collate;";
                
                dbDelta($cache_sql);
            }
        }
    }

    /**
     * Validate API key (simplified - just check if keys exist)
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to validate (legacy parameter).
     * @return   array                 Validation result.
     */
    public function validate_api_key($api_key = '') {
        // Check for HMG AI API key first
        $has_hmg_key = !empty($this->options['api_key']);
        
        // Check if any provider API keys are configured
        $has_gemini = !empty($this->options['gemini_api_key']);
        $has_openai = !empty($this->options['openai_api_key']);
        $has_claude = !empty($this->options['claude_api_key']);
        
        if (!$has_hmg_key && !$has_gemini && !$has_openai && !$has_claude) {
            return array(
                'valid' => false,
                'error' => __('Please configure either an HMG AI API key or at least one AI provider API key in the Settings page.', 'hmg-ai-blog-enhancer')
            );
        }

        $configured_providers = array();
        $auth_method = 'provider_keys';
        
        if ($has_hmg_key) {
            $auth_method = 'hmg_api_key';
            $configured_providers[] = 'HMG AI (Unified)';
        }
        
        if ($has_gemini) $configured_providers[] = 'Google Gemini';
        if ($has_openai) $configured_providers[] = 'OpenAI';
        if ($has_claude) $configured_providers[] = 'Anthropic Claude';

        return array(
            'valid' => true,
            'method' => $auth_method,
            'providers' => $configured_providers,
            'spending_limit' => $this->get_spending_limit(),
            'message' => sprintf(
                __('Authentication configured with: %s', 'hmg-ai-blog-enhancer'),
                implode(', ', $configured_providers)
            )
        );
    }

    /**
     * Get current user authentication status
     *
     * @since    1.0.0
     * @return   array    Current authentication status.
     */
    public function get_auth_status() {
        $validation = $this->validate_api_key();
        
        if (!$validation['valid']) {
            return array(
                'authenticated' => false,
                'message' => $validation['error']
            );
        }

        $spending_limit = $this->get_spending_limit();
        $spending_stats = $this->get_spending_stats();

        return array(
            'authenticated' => true,
            'method' => 'spending_limits',
            'providers' => $validation['providers'],
            'spending_limit' => $spending_limit,
            'spending_stats' => $spending_stats,
            'message' => $validation['message']
        );
    }

    /**
     * Get user's spending limit configuration
     *
     * @since    1.0.0
     * @return   array    Spending limit configuration.
     */
    public function get_spending_limit() {
        $limit_type = $this->options['spending_limit_type'] ?? 'moderate';
        $custom_monthly = (float) ($this->options['custom_monthly_limit'] ?? 15.00);
        
        if ($limit_type === 'custom') {
            return array(
                'type' => 'custom',
                'name' => 'Custom ($' . number_format($custom_monthly, 2) . '/month)',
                'monthly_limit' => $custom_monthly,
                'daily_limit' => round($custom_monthly / 30, 2),
                'warning_threshold' => (float) ($this->options['warning_threshold'] ?? 0.80),
                'description' => 'Custom spending limit'
            );
        }

        return $this->default_limits[$limit_type] ?? $this->default_limits['moderate'];
    }

    /**
     * Get current spending statistics
     *
     * @since    1.0.0
     * @return   array    Spending statistics.
     */
    public function get_spending_stats() {
        global $wpdb;
        
        $current_month = date('Y-m');
        $current_date = date('Y-m-d');
        
        // Get usage from database
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        // Monthly spending
        $monthly_query = $wpdb->prepare(
            "SELECT 
                SUM(estimated_cost) as total_cost,
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens
            FROM {$usage_table} 
            WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
            $current_month
        );
        
        $monthly_data = $wpdb->get_row($monthly_query, ARRAY_A);
        
        error_log('HMG AI Monthly Data: ' . json_encode($monthly_data));
        
        // Daily spending
        $daily_query = $wpdb->prepare(
            "SELECT 
                SUM(estimated_cost) as total_cost,
                COUNT(*) as total_requests
            FROM {$usage_table} 
            WHERE DATE(created_at) = %s",
            $current_date
        );
        
        $daily_data = $wpdb->get_row($daily_query, ARRAY_A);
        
        // Provider breakdown
        $provider_query = $wpdb->prepare(
            "SELECT 
                provider,
                SUM(estimated_cost) as cost,
                COUNT(*) as requests,
                SUM(tokens_used) as tokens
            FROM {$usage_table} 
            WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s
            GROUP BY provider",
            $current_month
        );
        
        $provider_breakdown = $wpdb->get_results($provider_query, ARRAY_A);
        
        $spending_limit = $this->get_spending_limit();
        
        return array(
            'monthly' => array(
                'spent' => (float) ($monthly_data['total_cost'] ?? 0),
                'limit' => $spending_limit['monthly_limit'],
                'percentage' => $spending_limit['monthly_limit'] > 0 
                    ? min(100, (($monthly_data['total_cost'] ?? 0) / $spending_limit['monthly_limit']) * 100)
                    : 0,
                'requests' => (int) ($monthly_data['total_requests'] ?? 0),
                'tokens' => (int) ($monthly_data['total_tokens'] ?? 0)
            ),
            'daily' => array(
                'spent' => (float) ($daily_data['total_cost'] ?? 0),
                'limit' => $spending_limit['daily_limit'],
                'percentage' => $spending_limit['daily_limit'] > 0 
                    ? min(100, (($daily_data['total_cost'] ?? 0) / $spending_limit['daily_limit']) * 100)
                    : 0,
                'requests' => (int) ($daily_data['total_requests'] ?? 0)
            ),
            'providers' => $provider_breakdown ?: array(),
            'reset_date' => date('Y-m-d', strtotime('first day of next month')),
            'warning_threshold' => $spending_limit['warning_threshold'] * 100
        );
    }

    /**
     * Check if user has access to a specific feature (always true now)
     *
     * @since    1.0.0
     * @param    string    $feature    The feature to check.
     * @return   bool                  Whether user has access.
     */
    public function has_feature_access($feature) {
        $auth_status = $this->get_auth_status();
        return $auth_status['authenticated'];
    }

    /**
     * Check spending limits before API call
     *
     * @since    1.0.0
     * @return   array    Usage limit check result.
     */
    public function check_usage_limits() {
        $spending_stats = $this->get_spending_stats();
        $spending_limit = $this->get_spending_limit();
        
        // Check if monthly limit exceeded
        if ($spending_stats['monthly']['spent'] >= $spending_limit['monthly_limit']) {
            return array(
                'exceeded' => true,
                'type' => 'monthly',
                'message' => sprintf(
                    __('Monthly spending limit of $%.2f has been reached. Limit resets on %s.', 'hmg-ai-blog-enhancer'),
                    $spending_limit['monthly_limit'],
                    $spending_stats['reset_date']
                )
            );
        }
        
        // Check if daily limit exceeded
        if ($spending_stats['daily']['spent'] >= $spending_limit['daily_limit']) {
            return array(
                'exceeded' => true,
                'type' => 'daily',
                'message' => sprintf(
                    __('Daily spending limit of $%.2f has been reached. Try again tomorrow.', 'hmg-ai-blog-enhancer'),
                    $spending_limit['daily_limit']
                )
            );
        }
        
        // Check if approaching warning threshold
        $monthly_percentage = $spending_stats['monthly']['percentage'] / 100;
        if ($monthly_percentage >= $spending_limit['warning_threshold']) {
            return array(
                'exceeded' => false,
                'warning' => true,
                'message' => sprintf(
                    __('You have used %.1f%% of your monthly spending limit ($%.2f of $%.2f).', 'hmg-ai-blog-enhancer'),
                    $spending_stats['monthly']['percentage'],
                    $spending_stats['monthly']['spent'],
                    $spending_limit['monthly_limit']
                )
            );
        }
        
        return array(
            'exceeded' => false,
            'warning' => false,
            'remaining_monthly' => $spending_limit['monthly_limit'] - $spending_stats['monthly']['spent'],
            'remaining_daily' => $spending_limit['daily_limit'] - $spending_stats['daily']['spent']
        );
    }

    /**
     * Record API usage with cost estimation
     *
     * @since    1.0.0
     * @param    int       $post_id        The post ID.
     * @param    string    $feature_type   The feature used.
     * @param    int       $api_calls      Number of API calls used.
     * @param    int       $tokens         Number of tokens used.
     * @param    string    $provider       AI provider used.
     * @return   bool                      Whether usage was recorded.
     */
    public function record_usage($post_id, $feature_type, $api_calls = 1, $tokens = 0, $provider = 'unknown') {
        global $wpdb;
        
        // Debug logging
        error_log('HMG AI: Recording usage - Feature: ' . $feature_type . ', Tokens: ' . $tokens . ', Provider: ' . $provider);
        
        // Get current user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            $user_id = 1; // Default to admin user if not logged in
        }
        
        // Calculate estimated cost
        $cost_per_1k_tokens = $this->provider_costs[$provider] ?? 0.001; // Default fallback
        $estimated_cost = ($tokens / 1000) * $cost_per_1k_tokens;
        
        error_log('HMG AI Cost Calculation: Provider=' . $provider . ', Tokens=' . $tokens . ', Cost/1K=' . $cost_per_1k_tokens . ', Total Cost=' . $estimated_cost);
        
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
        if (!$table_exists) {
            error_log('HMG AI Error: Usage table does not exist: ' . $usage_table);
            return false;
        }
        
        $result = $wpdb->insert(
            $usage_table,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'feature_type' => $feature_type,
                'provider' => $provider,
                'api_calls_used' => $api_calls,
                'tokens_used' => $tokens,
                'estimated_cost' => $estimated_cost,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d', '%f', '%s')
        );
        
        if ($result === false) {
            error_log('HMG AI Usage Recording Error: ' . $wpdb->last_error);
        } else {
            error_log('HMG AI: Usage recorded successfully - ID: ' . $wpdb->insert_id);
        }
        
        return $result !== false;
    }

    /**
     * Get usage statistics (legacy method for compatibility)
     *
     * @since    1.0.0
     * @return   array    Usage statistics.
     */
    public function get_usage_stats() {
        $spending_stats = $this->get_spending_stats();
        $spending_limit = $this->get_spending_limit();
        
        // Convert to legacy format for compatibility
        return array(
            'api_calls_used' => $spending_stats['monthly']['requests'],
            'api_calls_limit' => 999999, // Unlimited calls, limited by spending
            'tokens_used' => $spending_stats['monthly']['tokens'],
            'tokens_limit' => 999999, // Unlimited tokens, limited by spending
            'spending_used' => $spending_stats['monthly']['spent'],
            'spending_limit' => $spending_limit['monthly_limit'],
            'reset_date' => $spending_stats['reset_date'],
            'limit_type' => $spending_limit['name']
        );
    }

    /**
     * Get stored API key (legacy method)
     *
     * @since    1.0.0
     * @return   string    The stored API key.
     */
    public function get_stored_api_key() {
        // Return any available API key for legacy compatibility
        return $this->options['gemini_api_key'] ?? 
               $this->options['openai_api_key'] ?? 
               $this->options['claude_api_key'] ?? 
               '';
    }

    /**
     * Store API key (legacy method)
     *
     * @since    1.0.0
     * @param    string    $api_key    The API key to store.
     * @return   bool                  Whether the key was stored successfully.
     */
    public function store_api_key($api_key) {
        // For legacy compatibility, store as gemini key
        $this->options['gemini_api_key'] = sanitize_text_field($api_key);
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
        // Clear any cached authentication data
        delete_transient('hmg_ai_auth_status');
        delete_transient('hmg_ai_spending_stats');
    }

    /**
     * Get available spending limit presets
     *
     * @since    1.0.0
     * @return   array    Available spending limit options.
     */
    public function get_spending_limit_options() {
        return $this->default_limits;
    }

    /**
     * Update spending limit configuration
     *
     * @since    1.0.0
     * @param    string    $limit_type      The limit type (conservative, moderate, etc.).
     * @param    float     $custom_amount   Custom monthly amount (if limit_type is 'custom').
     * @param    float     $warning_threshold Warning threshold (0.0-1.0).
     * @return   bool                       Whether the update was successful.
     */
    public function update_spending_limit($limit_type, $custom_amount = 0.00, $warning_threshold = 0.80) {
        $this->options['spending_limit_type'] = $limit_type;
        $this->options['custom_monthly_limit'] = (float) $custom_amount;
        $this->options['warning_threshold'] = (float) $warning_threshold;
        
        return update_option('hmg_ai_blog_enhancer_options', $this->options);
    }

    /**
     * Get cost estimate for content generation
     *
     * @since    1.0.0
     * @param    string    $content       Content to analyze.
     * @param    string    $provider      AI provider.
     * @return   array                    Cost estimate.
     */
    public function estimate_cost($content, $provider = 'gemini') {
        // Rough token estimation (1 token ≈ 4 characters for English)
        $estimated_input_tokens = strlen($content) / 4;
        $estimated_output_tokens = 500; // Average output size
        $total_tokens = $estimated_input_tokens + $estimated_output_tokens;
        
        $cost_per_1k_tokens = $this->provider_costs[$provider] ?? 0.001;
        $estimated_cost = ($total_tokens / 1000) * $cost_per_1k_tokens;
        
        return array(
            'estimated_tokens' => (int) $total_tokens,
            'estimated_cost' => round($estimated_cost, 4),
            'provider' => $provider,
            'cost_per_1k_tokens' => $cost_per_1k_tokens
        );
    }

    /**
     * Get spending insights and recommendations
     *
     * @since    1.0.0
     * @return   array    Spending insights.
     */
    public function get_spending_insights() {
        $spending_stats = $this->get_spending_stats();
        $insights = array();
        
        // Cost efficiency analysis
        if (!empty($spending_stats['providers'])) {
            $cheapest_provider = null;
            $lowest_cost_per_request = PHP_FLOAT_MAX;
            
            foreach ($spending_stats['providers'] as $provider_data) {
                if ($provider_data['requests'] > 0) {
                    $cost_per_request = $provider_data['cost'] / $provider_data['requests'];
                    if ($cost_per_request < $lowest_cost_per_request) {
                        $lowest_cost_per_request = $cost_per_request;
                        $cheapest_provider = $provider_data['provider'];
                    }
                }
            }
            
            if ($cheapest_provider) {
                $insights[] = array(
                    'type' => 'cost_efficiency',
                    'message' => sprintf(
                        __('%s is your most cost-effective provider at $%.4f per request.', 'hmg-ai-blog-enhancer'),
                        ucfirst($cheapest_provider),
                        $lowest_cost_per_request
                    )
                );
            }
        }
        
        // Spending trend
        $monthly_percentage = $spending_stats['monthly']['percentage'];
        if ($monthly_percentage > 50) {
            $insights[] = array(
                'type' => 'spending_trend',
                'message' => sprintf(
                    __('You\'ve used %.1f%% of your monthly budget. Consider monitoring usage closely.', 'hmg-ai-blog-enhancer'),
                    $monthly_percentage
                )
            );
        }
        
        return $insights;
    }
} 