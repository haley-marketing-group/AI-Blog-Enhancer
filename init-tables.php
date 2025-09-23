<?php
/**
 * Initialize database tables for HMG AI Blog Enhancer
 */

// Load WordPress
if (file_exists('/var/www/html/wp-load.php')) {
    require_once('/var/www/html/wp-load.php');
} elseif (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
} else {
    die('Could not find wp-load.php');
}

global $wpdb;

// Create usage tracking table
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$charset_collate = $wpdb->get_charset_collate();

$usage_table = $wpdb->prefix . 'hmg_ai_usage';
$cache_table = $wpdb->prefix . 'hmg_ai_content_cache';

// Drop and recreate tables for fresh start
$wpdb->query("DROP TABLE IF EXISTS $usage_table");
$wpdb->query("DROP TABLE IF EXISTS $cache_table");

// Create usage table
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

dbDelta($sql_usage);
dbDelta($sql_cache);

// Verify tables were created
$usage_exists = $wpdb->get_var("SHOW TABLES LIKE '$usage_table'");
$cache_exists = $wpdb->get_var("SHOW TABLES LIKE '$cache_table'");

if ($usage_exists && $cache_exists) {
    echo "SUCCESS: Tables created\n";
    echo "- $usage_table\n";
    echo "- $cache_table\n";
} else {
    echo "ERROR: Failed to create tables\n";
    if (!$usage_exists) echo "- Usage table missing\n";
    if (!$cache_exists) echo "- Cache table missing\n";
}
?>
