<?php
/**
 * Debug script to check takeaways content
 */

// Load WordPress
require_once('../../../wp-load.php');

// Get the most recent post with takeaways
$posts = get_posts(array(
    'numberposts' => 10,
    'post_status' => 'any',
    'meta_key' => '_hmg_ai_takeaways',
    'meta_compare' => 'EXISTS'
));

echo "=== Checking Takeaways Content ===\n\n";

if (empty($posts)) {
    echo "No posts found with takeaways.\n";
} else {
    foreach ($posts as $post) {
        echo "Post ID: " . $post->ID . "\n";
        echo "Post Title: " . $post->post_title . "\n";
        
        // Get raw takeaways content
        $takeaways = get_post_meta($post->ID, '_hmg_ai_takeaways', true);
        
        echo "Raw takeaways content:\n";
        echo "Type: " . gettype($takeaways) . "\n";
        echo "Length: " . strlen($takeaways) . "\n";
        echo "Content:\n";
        var_dump($takeaways);
        echo "\n";
        
        // Show first 500 chars
        if (is_string($takeaways)) {
            echo "First 500 chars: " . substr($takeaways, 0, 500) . "\n";
        }
        
        // Try parsing
        echo "\nParsing as lines:\n";
        if (is_string($takeaways)) {
            $lines = explode("\n", $takeaways);
            foreach ($lines as $i => $line) {
                echo "Line $i: [" . $line . "]\n";
            }
        }
        
        echo "\n" . str_repeat('-', 50) . "\n\n";
    }
}

// Check if any posts exist at all
$all_posts = get_posts(array('numberposts' => 5, 'post_status' => 'any'));
echo "\nTotal posts found: " . count($all_posts) . "\n";
foreach ($all_posts as $post) {
    echo "- " . $post->ID . ": " . $post->post_title . "\n";
    
    // Check all AI meta keys
    $ai_meta_keys = array(
        '_hmg_ai_takeaways',
        '_hmg_ai_faq', 
        '_hmg_ai_toc',
        '_hmg_ai_audio_url'
    );
    
    foreach ($ai_meta_keys as $key) {
        $value = get_post_meta($post->ID, $key, true);
        if (!empty($value)) {
            echo "  Has $key: " . (is_string($value) ? substr($value, 0, 50) . '...' : 'Yes') . "\n";
        }
    }
}
