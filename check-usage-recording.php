<?php
/**
 * Quick check for usage recording
 */

// Load WordPress
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
} elseif (file_exists('/var/www/html/wp-load.php')) {
    require_once('/var/www/html/wp-load.php');
} else {
    die('Could not find wp-load.php');
}

// Check admin
if (!current_user_can('manage_options')) {
    die('Admin access required');
}

global $wpdb;
$usage_table = $wpdb->prefix . 'hmg_ai_usage';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");

echo "<h2>Usage Recording Check</h2>";

if (!$table_exists) {
    echo "<p style='color:red'>❌ Table does not exist: $usage_table</p>";
    echo "<p>Creating table now...</p>";
    
    // Create the table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $usage_table (
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
    
    // Check again
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
    if ($table_exists) {
        echo "<p style='color:green'>✅ Table created successfully!</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create table</p>";
    }
} else {
    echo "<p style='color:green'>✅ Table exists: $usage_table</p>";
}

// Show table structure
if ($table_exists) {
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $usage_table");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column->Field}</td>";
        echo "<td>{$column->Type}</td>";
        echo "<td>{$column->Null}</td>";
        echo "<td>{$column->Key}</td>";
        echo "<td>{$column->Default}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show recent records
    $recent = $wpdb->get_results("SELECT * FROM $usage_table ORDER BY created_at DESC LIMIT 10");
    echo "<h3>Recent Usage Records:</h3>";
    if ($recent) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User</th><th>Post</th><th>Feature</th><th>Provider</th><th>Tokens</th><th>Cost</th><th>Date</th></tr>";
        foreach ($recent as $r) {
            echo "<tr>";
            echo "<td>{$r->id}</td>";
            echo "<td>{$r->user_id}</td>";
            echo "<td>{$r->post_id}</td>";
            echo "<td>{$r->feature_type}</td>";
            echo "<td>{$r->provider}</td>";
            echo "<td>{$r->tokens_used}</td>";
            echo "<td>\${$r->estimated_cost}</td>";
            echo "<td>{$r->created_at}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No records found in the usage table.</p>";
    }
    
    // Test recording
    if (isset($_GET['test'])) {
        echo "<h3>Testing Usage Recording:</h3>";
        
        require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');
        $auth_service = new HMG_AI_Auth_Service();
        
        $result = $auth_service->record_usage(
            1, // post_id
            'test_' . time(),
            1, // api_calls
            500, // tokens
            'gemini'
        );
        
        if ($result) {
            echo "<p style='color:green'>✅ Test record inserted successfully!</p>";
            echo "<p>Last insert ID: " . $wpdb->insert_id . "</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to insert test record</p>";
            echo "<p>Last error: " . $wpdb->last_error . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='?test=1'>Test Recording</a> | ";
echo "<a href='fix-tables.php'>Fix Tables</a> | ";
echo "<a href='" . admin_url('admin.php?page=hmg-ai-blog-enhancer') . "'>Dashboard</a></p>";
?>
