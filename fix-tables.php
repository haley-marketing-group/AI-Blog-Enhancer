<?php
/**
 * Fix database tables for HMG AI Blog Enhancer
 * Run this file to recreate the database tables with the correct schema
 */

// Load WordPress
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
} elseif (file_exists('/var/www/html/wp-load.php')) {
    require_once('/var/www/html/wp-load.php');
} else {
    die('Could not find wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You need admin privileges to run this script.');
}

global $wpdb;

// Drop existing tables if they exist
$usage_table = $wpdb->prefix . 'hmg_ai_usage';
$cache_table = $wpdb->prefix . 'hmg_ai_content_cache';

echo "<h2>HMG AI Blog Enhancer - Database Fix</h2>";

// Check current table structure
$usage_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
$cache_exists = $wpdb->get_var("SHOW TABLES LIKE '$cache_table'");

echo "<p>Current status:</p>";
echo "<ul>";
echo "<li>Usage table ($usage_table): " . ($usage_exists ? "EXISTS" : "DOES NOT EXIST") . "</li>";
echo "<li>Cache table ($cache_table): " . ($cache_exists ? "EXISTS" : "DOES NOT EXIST") . "</li>";
echo "</ul>";

// Drop old tables
if ($usage_exists) {
    $wpdb->query("DROP TABLE IF EXISTS $usage_table");
    echo "<p>Dropped old usage table.</p>";
}
if ($cache_exists) {
    $wpdb->query("DROP TABLE IF EXISTS $cache_table");
    echo "<p>Dropped old cache table.</p>";
}

// Create new tables with correct schema
$charset_collate = $wpdb->get_charset_collate();

// Create usage tracking table
$sql_usage = "CREATE TABLE $usage_table (
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

// Create cache table
$sql_cache = "CREATE TABLE $cache_table (
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

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Execute table creation
dbDelta($sql_usage);
dbDelta($sql_cache);

// Verify tables were created
$usage_created = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
$cache_created = $wpdb->get_var("SHOW TABLES LIKE '$cache_table'");

echo "<h3>Results:</h3>";
echo "<ul>";
echo "<li>Usage table: " . ($usage_created ? "<strong style='color:green;'>✅ CREATED SUCCESSFULLY</strong>" : "<strong style='color:red;'>❌ FAILED</strong>") . "</li>";
echo "<li>Cache table: " . ($cache_created ? "<strong style='color:green;'>✅ CREATED SUCCESSFULLY</strong>" : "<strong style='color:red;'>❌ FAILED</strong>") . "</li>";
echo "</ul>";

// Show table structure
if ($usage_created) {
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $usage_table");
    echo "<h4>Usage Table Structure:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
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
}

// Add test data
if ($usage_created && isset($_GET['test'])) {
    $test_result = $wpdb->insert(
        $usage_table,
        array(
            'user_id' => get_current_user_id(),
            'post_id' => 0,
            'feature_type' => 'test',
            'provider' => 'gemini',
            'api_calls_used' => 1,
            'tokens_used' => 100,
            'estimated_cost' => 0.001,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%s', '%s', '%d', '%d', '%f', '%s')
    );
    
    if ($test_result) {
        echo "<p style='color:green;'><strong>✅ Test record inserted successfully!</strong></p>";
    } else {
        echo "<p style='color:red;'><strong>❌ Failed to insert test record: " . $wpdb->last_error . "</strong></p>";
    }
}

echo "<hr>";
echo "<p><a href='?test=1'>Run Test Insert</a> | ";
echo "<a href='" . admin_url('admin.php?page=hmg-ai-blog-enhancer') . "'>Back to Plugin Dashboard</a></p>";
?>
