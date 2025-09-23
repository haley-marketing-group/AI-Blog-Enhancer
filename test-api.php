<?php
/**
 * Test API Configuration
 * 
 * This file tests if your AI API keys are configured correctly.
 * Access this file directly to test your API connection.
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in as admin
if (!current_user_can('manage_options')) {
    wp_die('You need to be an administrator to access this page.');
}

// Include required files
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-ai-service-manager.php');
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-gemini-service.php');
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-openai-service.php');
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-claude-service.php');

// Get plugin options
$options = get_option('hmg_ai_blog_enhancer_options', array());

// Initialize service manager
$ai_manager = new HMG_AI_Service_Manager();

?>
<!DOCTYPE html>
<html>
<head>
    <title>HMG AI Blog Enhancer - API Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #332A86;
            margin-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .success {
            background: #E8F5E8;
            border-color: #5E9732;
            color: #2E5A16;
        }
        .error {
            background: #FFF0EE;
            border-color: #8A1F03;
            color: #8A1F03;
        }
        .warning {
            background: #FFF8E6;
            border-color: #E36F1E;
            color: #B35600;
        }
        .info {
            background: #E6F4FA;
            border-color: #48A4DD;
            color: #1A5A7A;
        }
        .provider {
            margin: 20px 0;
            padding: 20px;
            background: #F9F9F9;
            border-radius: 4px;
        }
        .provider h3 {
            margin-top: 0;
            color: #214357;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #332A86;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background: #4A3BA0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 HMG AI Blog Enhancer - API Test</h1>
        <p>Testing your AI provider configuration...</p>

        <h2>📋 Configuration Status</h2>
        
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Gemini API Key</td>
                <td><?php echo !empty($options['gemini_api_key']) ? '✅ Configured (' . substr($options['gemini_api_key'], 0, 10) . '...)' : '❌ Not configured'; ?></td>
            </tr>
            <tr>
                <td>OpenAI API Key</td>
                <td><?php echo !empty($options['openai_api_key']) ? '✅ Configured (' . substr($options['openai_api_key'], 0, 10) . '...)' : '❌ Not configured'; ?></td>
            </tr>
            <tr>
                <td>Claude API Key</td>
                <td><?php echo !empty($options['claude_api_key']) ? '✅ Configured (' . substr($options['claude_api_key'], 0, 10) . '...)' : '❌ Not configured'; ?></td>
            </tr>
            <tr>
                <td>Spending Limit</td>
                <td>$<?php echo number_format($options['custom_monthly_limit'] ?? 15.00, 2); ?>/month (<?php echo $options['spending_limit_type'] ?? 'moderate'; ?>)</td>
            </tr>
        </table>

        <h2>🔧 Provider Test Results</h2>
        
        <?php
        // Test all configured providers
        $test_results = $ai_manager->test_all_providers();
        
        if (empty($test_results)) {
            echo '<div class="status warning">No AI providers are configured. Please add at least one API key in the settings.</div>';
        } else {
            foreach ($test_results as $provider_key => $result) {
                echo '<div class="provider">';
                echo '<h3>' . $result['name'] . '</h3>';
                
                if ($result['success']) {
                    echo '<div class="status success">';
                    echo '<strong>✅ Connection Successful!</strong><br>';
                    echo 'Response Time: ' . number_format($result['response_time'] ?? 0, 2) . 's<br>';
                    if (isset($result['model'])) {
                        echo 'Model: ' . $result['model'] . '<br>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="status error">';
                    echo '<strong>❌ Connection Failed</strong><br>';
                    echo 'Error: ' . ($result['message'] ?? 'Unknown error') . '<br>';
                    echo '</div>';
                }
                
                if (isset($result['details'])) {
                    echo '<details>';
                    echo '<summary>View Details</summary>';
                    echo '<pre>' . print_r($result['details'], true) . '</pre>';
                    echo '</details>';
                }
                
                echo '</div>';
            }
        }
        ?>

        <h2>🧪 Sample Content Generation Test</h2>
        
        <?php
        // Only test if at least one provider is configured
        $has_provider = !empty($options['gemini_api_key']) || !empty($options['openai_api_key']) || !empty($options['claude_api_key']);
        
        if ($has_provider && isset($_GET['test_generation'])) {
            $test_content = "WordPress is a powerful content management system that powers over 43% of all websites on the internet. It's known for its flexibility, extensive plugin ecosystem, and user-friendly interface.";
            
            echo '<div class="status info">Testing content generation with sample text...</div>';
            
            // Try to generate takeaways
            $takeaways = $ai_manager->generate_content('takeaways', $test_content);
            
            if ($takeaways['success']) {
                echo '<div class="status success">';
                echo '<strong>✅ Content Generation Successful!</strong><br>';
                echo '<strong>Generated Takeaways:</strong><br>';
                echo '<ul>';
                foreach ($takeaways['data'] as $takeaway) {
                    echo '<li>' . esc_html($takeaway) . '</li>';
                }
                echo '</ul>';
                echo '<small>Provider used: ' . $takeaways['provider'] . '</small>';
                echo '</div>';
            } else {
                echo '<div class="status error">';
                echo '<strong>❌ Content Generation Failed</strong><br>';
                echo 'Error: ' . $takeaways['error'] . '<br>';
                echo '</div>';
            }
        } elseif ($has_provider) {
            echo '<div class="status info">';
            echo 'Ready to test content generation. This will use a small amount of your API credits.<br>';
            echo '<a href="?test_generation=1" class="button">Run Generation Test</a>';
            echo '</div>';
        } else {
            echo '<div class="status warning">';
            echo 'Configure at least one AI provider to test content generation.';
            echo '</div>';
        }
        ?>

        <h2>📊 Usage Statistics</h2>
        <?php
        $auth_service = new HMG_AI_Auth_Service();
        $usage_stats = $auth_service->get_usage_stats();
        ?>
        <table>
            <tr>
                <td>API Calls Used</td>
                <td><?php echo number_format($usage_stats['api_calls_used'] ?? 0); ?> / <?php echo number_format($usage_stats['api_calls_limit'] ?? 1000); ?></td>
            </tr>
            <tr>
                <td>Tokens Used</td>
                <td><?php echo number_format($usage_stats['tokens_used'] ?? 0); ?> / <?php echo number_format($usage_stats['tokens_limit'] ?? 1000000); ?></td>
            </tr>
            <tr>
                <td>Reset Date</td>
                <td><?php echo date('F j, Y', strtotime($usage_stats['reset_date'] ?? date('Y-m-01'))); ?></td>
            </tr>
        </table>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>" class="button">← Back to Dashboard</a>
            <a href="<?php echo admin_url('admin.php?page=hmg-ai-settings'); ?>" class="button">Go to Settings</a>
        </div>
    </div>
</body>
</html>
