<?php
/**
 * Test Usage Recording for HMG AI Blog Enhancer
 * This file tests the usage recording and display functionality
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You need admin privileges to run this script.');
}

// Include required files
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');

// Initialize auth service
$auth_service = new HMG_AI_Auth_Service();

// Get current statistics
$spending_stats = $auth_service->get_spending_stats();
$spending_limit = $auth_service->get_spending_limit();
$auth_status = $auth_service->get_auth_status();

?>
<!DOCTYPE html>
<html>
<head>
    <title>HMG AI - Usage Recording Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
        h1 { color: #332A86; }
        h2 { color: #214357; margin-top: 30px; }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .success {
            background: #E8F5E8;
            border-color: #5E9732;
        }
        .info {
            background: #E6F4FA;
            border-color: #48A4DD;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        .usage-bar {
            background: #e0e0e0;
            height: 20px;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        .usage-fill {
            background: #5E9732;
            height: 100%;
            transition: width 0.3s;
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
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Usage Recording Test</h1>
        
        <h2>📊 Current Statistics</h2>
        
        <div class="status info">
            <h3>Monthly Usage (<?php echo date('F Y'); ?>)</h3>
            <table>
                <tr>
                    <td><strong>Spending:</strong></td>
                    <td>$<?php echo number_format($spending_stats['monthly']['spent'], 2); ?> / $<?php echo number_format($spending_stats['monthly']['limit'], 2); ?></td>
                    <td><?php echo number_format($spending_stats['monthly']['percentage'], 1); ?>%</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="usage-bar">
                            <div class="usage-fill" style="width: <?php echo min(100, $spending_stats['monthly']['percentage']); ?>%"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>API Calls:</strong></td>
                    <td colspan="2"><?php echo number_format($spending_stats['monthly']['requests']); ?> calls</td>
                </tr>
                <tr>
                    <td><strong>Tokens:</strong></td>
                    <td colspan="2"><?php echo number_format($spending_stats['monthly']['tokens']); ?> tokens</td>
                </tr>
                <tr>
                    <td><strong>Reset Date:</strong></td>
                    <td colspan="2"><?php echo $spending_stats['reset_date']; ?></td>
                </tr>
            </table>
        </div>

        <h2>💰 Spending Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Limit Type</td>
                <td><?php echo $spending_limit['name']; ?></td>
            </tr>
            <tr>
                <td>Monthly Limit</td>
                <td>$<?php echo number_format($spending_limit['monthly_limit'], 2); ?></td>
            </tr>
            <tr>
                <td>Daily Limit</td>
                <td>$<?php echo number_format($spending_limit['daily_limit'], 2); ?></td>
            </tr>
            <tr>
                <td>Warning Threshold</td>
                <td><?php echo ($spending_limit['warning_threshold'] * 100); ?>%</td>
            </tr>
        </table>

        <h2>🔧 Test Recording</h2>
        
        <?php
        if (isset($_GET['test_record'])) {
            // Test recording usage
            $test_tokens = rand(100, 1000);
            $test_provider = ['gemini', 'openai', 'claude'][rand(0, 2)];
            
            $result = $auth_service->record_usage(
                0, // post_id
                'test',
                1, // api_calls
                $test_tokens,
                $test_provider
            );
            
            if ($result) {
                echo '<div class="status success">';
                echo '<strong>✅ Test Usage Recorded!</strong><br>';
                echo 'Provider: ' . $test_provider . '<br>';
                echo 'Tokens: ' . $test_tokens . '<br>';
                echo 'Estimated Cost: $' . number_format(($test_tokens / 1000) * 0.001, 4);
                echo '</div>';
            } else {
                echo '<div class="status error">';
                echo '<strong>❌ Failed to record usage</strong>';
                echo '</div>';
            }
            
            // Refresh stats
            $spending_stats = $auth_service->get_spending_stats();
        }
        ?>
        
        <p>
            <a href="?test_record=1" class="button">Record Test Usage</a>
            <a href="?" class="button">Refresh Stats</a>
        </p>

        <h2>📈 Provider Breakdown</h2>
        <?php if (!empty($spending_stats['providers'])): ?>
            <table>
                <tr>
                    <th>Provider</th>
                    <th>Requests</th>
                    <th>Tokens</th>
                    <th>Cost</th>
                </tr>
                <?php foreach ($spending_stats['providers'] as $provider): ?>
                <tr>
                    <td><?php echo ucfirst($provider['provider']); ?></td>
                    <td><?php echo number_format($provider['requests']); ?></td>
                    <td><?php echo number_format($provider['tokens']); ?></td>
                    <td>$<?php echo number_format($provider['cost'], 4); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No provider usage data yet.</p>
        <?php endif; ?>

        <h2>🔍 Database Check</h2>
        <?php
        global $wpdb;
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        // Get last 5 records
        $recent_usage = $wpdb->get_results("
            SELECT * FROM {$usage_table} 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        
        if ($recent_usage):
        ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Post</th>
                    <th>Feature</th>
                    <th>Provider</th>
                    <th>Tokens</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($recent_usage as $record): ?>
                <tr>
                    <td><?php echo $record->id; ?></td>
                    <td><?php echo $record->user_id; ?></td>
                    <td><?php echo $record->post_id; ?></td>
                    <td><?php echo $record->feature_type; ?></td>
                    <td><?php echo $record->provider; ?></td>
                    <td><?php echo $record->tokens_used; ?></td>
                    <td>$<?php echo number_format($record->estimated_cost, 4); ?></td>
                    <td><?php echo $record->created_at; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No usage records found in database.</p>
        <?php endif; ?>

        <h2>🎯 Debug Info</h2>
        <details>
            <summary>Click to view raw data</summary>
            <pre><?php 
                echo "Spending Stats:\n";
                print_r($spending_stats);
                echo "\n\nSpending Limit:\n";
                print_r($spending_limit);
                echo "\n\nAuth Status:\n";
                print_r($auth_status);
            ?></pre>
        </details>

        <hr>
        <p>
            <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>">← Back to Dashboard</a> |
            <a href="fix-tables.php">Fix Database Tables</a>
        </p>
    </div>
</body>
</html>
