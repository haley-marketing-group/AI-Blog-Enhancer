<?php
/**
 * Verify usage recording is working
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check admin
if (!current_user_can('manage_options')) {
    die('Admin access required');
}

global $wpdb;

// Include required files
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');

// Initialize
$auth_service = new HMG_AI_Auth_Service();
$usage_table = $wpdb->prefix . 'hmg_ai_usage';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Usage Recording</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; padding: 10px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 10px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 10px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .button { padding: 10px 20px; background: #332A86; color: white; text-decoration: none; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Verify Usage Recording</h1>

    <?php
    // 1. Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
    if (!$table_exists) {
        echo '<div class="error">❌ Table does not exist! Run <a href="fix-tables.php">fix-tables.php</a></div>';
        exit;
    } else {
        echo '<div class="success">✅ Table exists: ' . $usage_table . '</div>';
    }

    // 2. Check table structure
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $usage_table");
    $required_columns = ['user_id', 'post_id', 'feature_type', 'provider', 'api_calls_used', 'tokens_used', 'estimated_cost'];
    $missing = [];
    $column_names = array_map(function($col) { return $col->Field; }, $columns);
    
    foreach ($required_columns as $req) {
        if (!in_array($req, $column_names)) {
            $missing[] = $req;
        }
    }
    
    if ($missing) {
        echo '<div class="error">❌ Missing columns: ' . implode(', ', $missing) . '</div>';
        echo '<p>Run <a href="fix-tables.php">fix-tables.php</a> to fix the table structure.</p>';
    } else {
        echo '<div class="success">✅ All required columns present</div>';
    }

    // 3. Test recording
    if (isset($_GET['test'])) {
        echo '<h2>Testing Usage Recording</h2>';
        
        $test_post_id = 1;
        $test_feature = 'test_' . time();
        $test_tokens = rand(100, 1000);
        $test_provider = 'gemini';
        
        echo '<div class="info">Recording test usage...</div>';
        echo '<ul>';
        echo '<li>Post ID: ' . $test_post_id . '</li>';
        echo '<li>Feature: ' . $test_feature . '</li>';
        echo '<li>Tokens: ' . $test_tokens . '</li>';
        echo '<li>Provider: ' . $test_provider . '</li>';
        echo '</ul>';
        
        $result = $auth_service->record_usage(
            $test_post_id,
            $test_feature,
            1,
            $test_tokens,
            $test_provider
        );
        
        if ($result) {
            echo '<div class="success">✅ Usage recorded successfully!</div>';
            
            // Verify it was actually saved
            $saved = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $usage_table WHERE feature_type = %s ORDER BY id DESC LIMIT 1",
                $test_feature
            ));
            
            if ($saved) {
                echo '<div class="success">✅ Verified in database:</div>';
                echo '<table>';
                foreach ($saved as $key => $value) {
                    echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="error">❌ Record not found in database!</div>';
            }
        } else {
            echo '<div class="error">❌ Failed to record usage!</div>';
            echo '<div class="error">Last DB Error: ' . $wpdb->last_error . '</div>';
        }
    }

    // 4. Show current stats
    $stats = $auth_service->get_spending_stats();
    ?>

    <h2>Current Statistics</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Monthly Spending</td>
            <td>$<?php echo number_format($stats['monthly']['spent'], 4); ?></td>
        </tr>
        <tr>
            <td>API Calls</td>
            <td><?php echo $stats['monthly']['requests']; ?></td>
        </tr>
        <tr>
            <td>Tokens</td>
            <td><?php echo number_format($stats['monthly']['tokens']); ?></td>
        </tr>
    </table>

    <h2>Recent Records (Last 5)</h2>
    <?php
    $recent = $wpdb->get_results("SELECT * FROM $usage_table ORDER BY created_at DESC LIMIT 5");
    if ($recent):
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
            <?php foreach ($recent as $r): ?>
            <tr>
                <td><?php echo $r->id; ?></td>
                <td><?php echo $r->user_id; ?></td>
                <td><?php echo $r->post_id; ?></td>
                <td><?php echo $r->feature_type; ?></td>
                <td><?php echo $r->provider; ?></td>
                <td><?php echo $r->tokens_used; ?></td>
                <td>$<?php echo number_format($r->estimated_cost, 4); ?></td>
                <td><?php echo $r->created_at; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>

    <p>
        <a href="?test=1" class="button">Test Recording</a>
        <a href="fix-tables.php" class="button">Fix Tables</a>
        <a href="debug-usage.php" class="button">Debug Page</a>
        <a href="<?php echo admin_url('admin.php?page=hmg-ai-blog-enhancer'); ?>" class="button">Dashboard</a>
    </p>
</body>
</html>
