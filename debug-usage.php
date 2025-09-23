<?php
/**
 * Debug Usage Statistics
 * This file helps diagnose usage tracking issues
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check admin
if (!current_user_can('manage_options')) {
    die('Admin access required');
}

// Include required files
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');

// Initialize
$auth_service = new HMG_AI_Auth_Service();
$options = get_option('hmg_ai_blog_enhancer_options', array());

// Actions
if (isset($_GET['clear_usage'])) {
    global $wpdb;
    $table = $wpdb->prefix . 'hmg_ai_usage';
    $wpdb->query("TRUNCATE TABLE $table");
    echo '<div style="background: #d4edda; padding: 10px; margin: 10px 0;">✅ Usage table cleared!</div>';
}

if (isset($_GET['add_test'])) {
    $result = $auth_service->record_usage(
        1, // post_id
        'test_' . time(),
        1, // api_calls
        rand(500, 2000), // tokens
        'gemini'
    );
    echo '<div style="background: #d4edda; padding: 10px; margin: 10px 0;">✅ Test record added: ' . ($result ? 'Success' : 'Failed') . '</div>';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Usage Statistics</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        pre { background: #f4f4f4; padding: 10px; overflow: auto; }
        .section { margin: 30px 0; padding: 20px; background: white; border: 1px solid #ddd; }
        h2 { color: #332A86; }
        .button { padding: 10px 20px; background: #332A86; color: white; text-decoration: none; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Debug Usage Statistics</h1>

    <div class="section">
        <h2>📊 Current Statistics</h2>
        <?php 
        $stats = $auth_service->get_spending_stats();
        $limit = $auth_service->get_spending_limit();
        ?>
        <table>
            <tr>
                <th>Metric</th>
                <th>Value</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>Monthly Spending</td>
                <td>$<?php echo number_format($stats['monthly']['spent'], 4); ?></td>
                <td>Limit: $<?php echo number_format($stats['monthly']['limit'], 2); ?> (<?php echo number_format($stats['monthly']['percentage'], 1); ?>%)</td>
            </tr>
            <tr>
                <td>Monthly API Calls</td>
                <td><?php echo $stats['monthly']['requests']; ?></td>
                <td>-</td>
            </tr>
            <tr>
                <td>Monthly Tokens</td>
                <td><?php echo number_format($stats['monthly']['tokens']); ?></td>
                <td>-</td>
            </tr>
            <tr>
                <td>Daily Spending</td>
                <td>$<?php echo number_format($stats['daily']['spent'], 4); ?></td>
                <td>Limit: $<?php echo number_format($stats['daily']['limit'], 2); ?></td>
            </tr>
            <tr>
                <td>Reset Date</td>
                <td><?php echo $stats['reset_date']; ?></td>
                <td>Next month's first day</td>
            </tr>
        </table>

        <h3>Provider Breakdown</h3>
        <?php if (!empty($stats['providers'])): ?>
        <table>
            <tr>
                <th>Provider</th>
                <th>Requests</th>
                <th>Tokens</th>
                <th>Cost</th>
            </tr>
            <?php foreach ($stats['providers'] as $provider): ?>
            <tr>
                <td><?php echo ucfirst($provider['provider'] ?? 'unknown'); ?></td>
                <td><?php echo $provider['requests'] ?? 0; ?></td>
                <td><?php echo number_format($provider['tokens'] ?? 0); ?></td>
                <td>$<?php echo number_format($provider['cost'] ?? 0, 4); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>No provider data yet.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>🗄️ Database Records</h2>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'hmg_ai_usage';
        $records = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 10");
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $month_total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
            date('Y-m')
        ));
        ?>
        
        <p>Total Records: <?php echo $total; ?> | This Month: <?php echo $month_total; ?></p>
        
        <?php if ($records): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Post</th>
                <th>Feature</th>
                <th>Provider</th>
                <th>API Calls</th>
                <th>Tokens</th>
                <th>Cost</th>
                <th>Date</th>
            </tr>
            <?php foreach ($records as $r): ?>
            <tr>
                <td><?php echo $r->id; ?></td>
                <td><?php echo $r->user_id; ?></td>
                <td><?php echo $r->post_id; ?></td>
                <td><?php echo $r->feature_type; ?></td>
                <td><?php echo $r->provider; ?></td>
                <td><?php echo $r->api_calls_used; ?></td>
                <td><?php echo $r->tokens_used; ?></td>
                <td>$<?php echo number_format($r->estimated_cost, 4); ?></td>
                <td><?php echo $r->created_at; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>No usage records found.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>⚙️ Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Spending Limit Type</td>
                <td><?php echo $options['spending_limit_type'] ?? 'moderate'; ?></td>
            </tr>
            <tr>
                <td>Custom Monthly Limit</td>
                <td>$<?php echo number_format($options['custom_monthly_limit'] ?? 15.00, 2); ?></td>
            </tr>
            <tr>
                <td>Warning Threshold</td>
                <td><?php echo (($options['warning_threshold'] ?? 0.80) * 100); ?>%</td>
            </tr>
            <tr>
                <td>Gemini API Key</td>
                <td><?php echo !empty($options['gemini_api_key']) ? '✅ Configured' : '❌ Not set'; ?></td>
            </tr>
            <tr>
                <td>OpenAI API Key</td>
                <td><?php echo !empty($options['openai_api_key']) ? '✅ Configured' : '❌ Not set'; ?></td>
            </tr>
            <tr>
                <td>Claude API Key</td>
                <td><?php echo !empty($options['claude_api_key']) ? '✅ Configured' : '❌ Not set'; ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>🔧 Actions</h2>
        <a href="?" class="button">Refresh</a>
        <a href="?add_test=1" class="button">Add Test Record</a>
        <a href="?clear_usage=1" class="button" onclick="return confirm('Clear all usage data?')">Clear Usage Table</a>
        <a href="fix-tables.php" class="button">Fix Database Tables</a>
        <a href="test-usage-recording.php" class="button">Test Usage Recording</a>
    </div>

    <div class="section">
        <h2>🐛 Debug Data</h2>
        <h3>Raw get_spending_stats() Output:</h3>
        <pre><?php print_r($stats); ?></pre>
        
        <h3>Raw get_spending_limit() Output:</h3>
        <pre><?php print_r($limit); ?></pre>
        
        <h3>SQL Queries:</h3>
        <pre>
Monthly Query:
SELECT SUM(estimated_cost) as total_cost, COUNT(*) as total_requests, SUM(tokens_used) as total_tokens
FROM <?php echo $table; ?> 
WHERE DATE_FORMAT(created_at, '%Y-%m') = '<?php echo date('Y-m'); ?>'

Result: 
<?php 
$result = $wpdb->get_row($wpdb->prepare(
    "SELECT SUM(estimated_cost) as total_cost, COUNT(*) as total_requests, SUM(tokens_used) as total_tokens
    FROM {$table} WHERE DATE_FORMAT(created_at, '%%Y-%%m') = %s",
    date('Y-m')
), ARRAY_A);
print_r($result);
?>
        </pre>
    </div>

    <p>
        <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>">← Back to Dashboard</a>
    </p>
</body>
</html>
