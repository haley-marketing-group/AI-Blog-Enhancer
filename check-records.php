<?php
// Load WordPress
if (file_exists('/var/www/html/wp-load.php')) {
    require_once('/var/www/html/wp-load.php');
} elseif (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
} else {
    die('Could not find wp-load.php');
}

global $wpdb;
$usage_table = $wpdb->prefix . 'hmg_ai_usage';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
if (!$table_exists) {
    echo "ERROR: Table does not exist\n";
    exit;
}

// Count records
$count = $wpdb->get_var("SELECT COUNT(*) FROM $usage_table");
echo "Total records: $count\n\n";

// Get recent records
$records = $wpdb->get_results("SELECT * FROM $usage_table ORDER BY created_at DESC LIMIT 5");
if ($records) {
    echo "Recent records:\n";
    foreach ($records as $r) {
        echo "- ID: {$r->id}, Feature: {$r->feature_type}, Provider: {$r->provider}, Tokens: {$r->tokens_used}, Cost: \${$r->estimated_cost}, Date: {$r->created_at}\n";
    }
} else {
    echo "No records found\n";
}

// Get spending stats
require_once(plugin_dir_path(__FILE__) . 'includes/services/class-auth-service.php');
$auth = new HMG_AI_Auth_Service();
$stats = $auth->get_spending_stats();
echo "\nMonthly stats:\n";
echo "- Spending: \${$stats['monthly']['spent']}\n";
echo "- API Calls: {$stats['monthly']['requests']}\n";
echo "- Tokens: {$stats['monthly']['tokens']}\n";
?>
