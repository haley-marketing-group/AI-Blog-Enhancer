<?php
/**
 * Debug script for HMG AI Blog Enhancer meta box
 * 
 * This script helps troubleshoot why the meta box might not be showing
 * in the post edit screen.
 */

// Only run if we're in WordPress admin
if (!defined('ABSPATH')) {
    die('This script must be run within WordPress admin.');
}

/**
 * Debug the meta box registration
 */
function debug_hmg_ai_metabox() {
    global $wp_meta_boxes;
    
    echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px; border-radius: 8px;">';
    echo '<h2>🔍 HMG AI Meta Box Debug Information</h2>';
    
    // Check if plugin is active
    if (!class_exists('HMG_AI_Admin')) {
        echo '<p style="color: red;"><strong>❌ Plugin Issue:</strong> HMG_AI_Admin class not found. Plugin may not be properly loaded.</p>';
        return;
    }
    
    echo '<p style="color: green;"><strong>✅ Plugin Status:</strong> HMG AI Blog Enhancer plugin is loaded.</p>';
    
    // Check current screen
    $current_screen = get_current_screen();
    if ($current_screen) {
        echo '<p><strong>Current Screen:</strong> ' . $current_screen->id . '</p>';
        echo '<p><strong>Post Type:</strong> ' . $current_screen->post_type . '</p>';
    }
    
    // Check if we're on post edit screen
    if (!in_array($current_screen->id, ['post', 'page'])) {
        echo '<p style="color: orange;"><strong>⚠️ Screen Issue:</strong> Meta boxes only show on post/page edit screens. Current screen: ' . $current_screen->id . '</p>';
    }
    
    // Check registered meta boxes
    if (isset($wp_meta_boxes['post']['side']['high']['hmg-ai-content-generator'])) {
        echo '<p style="color: green;"><strong>✅ Meta Box Registration:</strong> HMG AI Content Generator meta box is registered for posts.</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ Meta Box Registration:</strong> HMG AI Content Generator meta box is NOT registered for posts.</p>';
    }
    
    if (isset($wp_meta_boxes['page']['side']['high']['hmg-ai-content-generator'])) {
        echo '<p style="color: green;"><strong>✅ Meta Box Registration:</strong> HMG AI Content Generator meta box is registered for pages.</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ Meta Box Registration:</strong> HMG AI Content Generator meta box is NOT registered for pages.</p>';
    }
    
    // Show all registered meta boxes for debugging
    echo '<h3>📋 All Registered Meta Boxes for Current Post Type:</h3>';
    if (isset($wp_meta_boxes[$current_screen->post_type])) {
        echo '<pre style="background: white; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: scroll;">';
        print_r(array_keys($wp_meta_boxes[$current_screen->post_type]['side']['high'] ?? []));
        echo '</pre>';
    } else {
        echo '<p>No meta boxes registered for this post type.</p>';
    }
    
    // Check user capabilities
    if (current_user_can('edit_posts')) {
        echo '<p style="color: green;"><strong>✅ User Permissions:</strong> Current user can edit posts.</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ User Permissions:</strong> Current user cannot edit posts.</p>';
    }
    
    // Check if meta box file exists
    $meta_box_file = HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'admin/partials/meta-box-content-generator.php';
    if (file_exists($meta_box_file)) {
        echo '<p style="color: green;"><strong>✅ Meta Box File:</strong> Template file exists at ' . $meta_box_file . '</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ Meta Box File:</strong> Template file missing at ' . $meta_box_file . '</p>';
    }
    
    // Check if hooks are registered
    $hooks_registered = has_action('add_meta_boxes');
    if ($hooks_registered) {
        echo '<p style="color: green;"><strong>✅ Hook Registration:</strong> add_meta_boxes action has ' . $hooks_registered . ' callback(s) registered.</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ Hook Registration:</strong> No add_meta_boxes callbacks registered.</p>';
    }
    
    echo '<h3>🛠️ Troubleshooting Steps:</h3>';
    echo '<ol>';
    echo '<li><strong>Check Screen Options:</strong> Click "Screen Options" at the top right of the post edit page and make sure "AI Content Generator" is checked.</li>';
    echo '<li><strong>Try Different Post Types:</strong> Check both Posts and Pages to see if the meta box appears on either.</li>';
    echo '<li><strong>Check User Role:</strong> Make sure you have Editor or Administrator permissions.</li>';
    echo '<li><strong>Plugin Conflicts:</strong> Temporarily deactivate other plugins to see if there\'s a conflict.</li>';
    echo '<li><strong>Theme Issues:</strong> Switch to a default WordPress theme temporarily.</li>';
    echo '<li><strong>Clear Cache:</strong> If using caching plugins, clear all caches.</li>';
    echo '</ol>';
    
    echo '<h3>🔧 Manual Registration Test:</h3>';
    echo '<p>Click this button to manually register the meta box:</p>';
    echo '<button onclick="manualMetaBoxTest()" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Test Meta Box Registration</button>';
    
    echo '<script>
    function manualMetaBoxTest() {
        // Try to manually add the meta box via JavaScript
        if (typeof jQuery !== "undefined") {
            jQuery.post(ajaxurl, {
                action: "debug_add_metabox",
                nonce: "' . wp_create_nonce('debug_metabox') . '"
            }, function(response) {
                alert("Meta box registration test completed. Check console for details.");
                console.log("Meta box test response:", response);
                location.reload();
            });
        } else {
            alert("jQuery not available for testing.");
        }
    }
    </script>';
    
    echo '</div>';
}

// Add AJAX handler for manual test
add_action('wp_ajax_debug_add_metabox', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'debug_metabox')) {
        wp_die('Security check failed');
    }
    
    // Manually call the add_meta_boxes function
    if (class_exists('HMG_AI_Admin')) {
        $admin = new HMG_AI_Admin('hmg-ai-blog-enhancer', '1.0.0');
        $admin->add_meta_boxes();
        wp_send_json_success('Meta box registration function called manually.');
    } else {
        wp_send_json_error('HMG_AI_Admin class not found.');
    }
});

// Hook into admin_notices to show debug info on post edit screens
add_action('admin_notices', function() {
    $screen = get_current_screen();
    
    // Only show on post edit screens and if debug is enabled
    if (($screen->id === 'post' || $screen->id === 'page') && isset($_GET['hmg_debug'])) {
        debug_hmg_ai_metabox();
    }
});

// Add debug link to admin bar
add_action('admin_bar_menu', function($wp_admin_bar) {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'post' || $screen->id === 'page')) {
        $wp_admin_bar->add_node(array(
            'id' => 'hmg-debug-metabox',
            'title' => '🔍 Debug HMG Meta Box',
            'href' => add_query_arg('hmg_debug', '1'),
        ));
    }
}, 100);

?> 