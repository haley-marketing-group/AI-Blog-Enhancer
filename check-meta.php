<?php
/**
 * Simple check for AI meta data
 */

// Load WordPress
require_once('../../../wp-load.php');

global $wpdb;

echo "<h2>Checking AI Blog Enhancer Meta Data</h2>";

// Check for posts with AI metadata
$query = "
    SELECT p.ID, p.post_title, pm.meta_key, pm.meta_value
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE pm.meta_key LIKE '_hmg_ai_%'
    ORDER BY p.ID DESC
    LIMIT 20
";

$results = $wpdb->get_results($query);

if (empty($results)) {
    echo "<p style='color: red;'><strong>No AI-generated content found in the database!</strong></p>";
    echo "<p>You need to:</p>";
    echo "<ol>";
    echo "<li>Go to a post in the WordPress editor</li>";
    echo "<li>Find the 'AI Content Generator' meta box (usually in the sidebar)</li>";
    echo "<li>Click 'Generate Key Takeaways' button</li>";
    echo "<li>Wait for generation to complete</li>";
    echo "<li>Then insert the [hmg_ai_takeaways] shortcode in your post</li>";
    echo "</ol>";
} else {
    echo "<h3>Found AI Meta Data:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Post ID</th><th>Title</th><th>Meta Key</th><th>Meta Value (first 200 chars)</th></tr>";
    
    foreach ($results as $row) {
        $value_preview = substr($row->meta_value, 0, 200);
        if (strlen($row->meta_value) > 200) {
            $value_preview .= '...';
        }
        echo "<tr>";
        echo "<td>{$row->ID}</td>";
        echo "<td>" . esc_html($row->post_title) . "</td>";
        echo "<td>{$row->meta_key}</td>";
        echo "<td><pre>" . esc_html($value_preview) . "</pre></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show example shortcodes
    echo "<h3>Example Shortcodes to Use:</h3>";
    echo "<ul>";
    
    $unique_posts = array();
    foreach ($results as $row) {
        if (!isset($unique_posts[$row->ID])) {
            $unique_posts[$row->ID] = $row->post_title;
        }
    }
    
    foreach ($unique_posts as $id => $title) {
        echo "<li>For post '{$title}' (ID: {$id}):<br>";
        echo "<code>[hmg_ai_takeaways post_id='{$id}']</code><br>";
        echo "<code>[hmg_ai_faq post_id='{$id}']</code><br>";
        echo "<code>[hmg_ai_toc post_id='{$id}']</code>";
        echo "</li>";
    }
    echo "</ul>";
}

// Check if API keys are configured
$options = get_option('hmg_ai_blog_enhancer_options', array());
$has_gemini = !empty($options['gemini_api_key']);
$has_openai = !empty($options['openai_api_key']);

echo "<h3>API Configuration:</h3>";
echo "<ul>";
echo "<li>Gemini API Key: " . ($has_gemini ? '✅ Configured' : '❌ Not configured') . "</li>";
echo "<li>OpenAI API Key: " . ($has_openai ? '✅ Configured' : '❌ Not configured') . "</li>";
echo "</ul>";

if (!$has_gemini && !$has_openai) {
    echo "<p style='color: red;'><strong>No API keys configured!</strong> You need to configure at least one API key in the plugin settings to generate content.</p>";
}
?>
