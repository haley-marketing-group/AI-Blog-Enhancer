<?php
/**
 * Debug script for HMG AI Blog Enhancer meta box visibility
 * 
 * Add ?hmg_debug_metabox=1 to any post edit URL to see debug information
 */

// Only run if debug parameter is present
if (!isset($_GET['hmg_debug_metabox'])) {
    return;
}

// Only run in admin area
if (!is_admin()) {
    return;
}

// Add debug information to admin footer
add_action('admin_footer', function() {
    $current_screen = get_current_screen();
    if (!$current_screen || !in_array($current_screen->post_type, ['post', 'page'])) {
        return;
    }
    
    echo '<div style="position: fixed; bottom: 20px; right: 20px; background: #fff; border: 2px solid #0073aa; padding: 15px; max-width: 400px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 9999;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #0073aa;">HMG AI Meta Box Debug</h3>';
    
    // Check if our plugin is active
    if (class_exists('HMG_AI_Admin')) {
        echo '<p style="color: green; margin: 5px 0;"><strong>✓ Plugin Active:</strong> HMG AI Blog Enhancer is loaded</p>';
    } else {
        echo '<p style="color: red; margin: 5px 0;"><strong>✗ Plugin Inactive:</strong> HMG AI Blog Enhancer is not loaded</p>';
    }
    
    // Check current screen info
    echo '<p style="margin: 5px 0;"><strong>Screen ID:</strong> ' . esc_html($current_screen->id) . '</p>';
    echo '<p style="margin: 5px 0;"><strong>Post Type:</strong> ' . esc_html($current_screen->post_type) . '</p>';
    
    // Check if block editor
    if (method_exists($current_screen, 'is_block_editor')) {
        $is_block_editor = $current_screen->is_block_editor();
        echo '<p style="margin: 5px 0;"><strong>Block Editor:</strong> ' . ($is_block_editor ? 'Yes' : 'No') . '</p>';
    } else {
        echo '<p style="margin: 5px 0;"><strong>Block Editor:</strong> Cannot detect</p>';
    }
    
    // Check meta box registration
    global $wp_meta_boxes;
    $post_type = $current_screen->post_type;
    $meta_box_found = false;
    
    if (isset($wp_meta_boxes[$post_type])) {
        foreach ($wp_meta_boxes[$post_type] as $context => $priority_boxes) {
            foreach ($priority_boxes as $priority => $boxes) {
                if (isset($boxes['hmg-ai-content-generator'])) {
                    $meta_box_found = true;
                    echo '<p style="color: green; margin: 5px 0;"><strong>✓ Meta Box Registered:</strong> Found in ' . $context . ' context</p>';
                    break 2;
                }
            }
        }
    }
    
    if (!$meta_box_found) {
        echo '<p style="color: red; margin: 5px 0;"><strong>✗ Meta Box Not Found:</strong> Not registered for this post type</p>';
    }
    
    // Check user meta for hidden boxes
    $user_id = get_current_user_id();
    $hidden_meta_boxes = get_user_meta($user_id, 'metaboxhidden_' . $current_screen->id, true);
    
    if (is_array($hidden_meta_boxes) && in_array('hmg-ai-content-generator', $hidden_meta_boxes)) {
        echo '<p style="color: orange; margin: 5px 0;"><strong>⚠ Meta Box Hidden:</strong> User has hidden this meta box</p>';
        echo '<p style="margin: 5px 0; font-size: 12px;">Check "Screen Options" at the top of the page and make sure "AI Content Generator" is checked.</p>';
    } else {
        echo '<p style="color: green; margin: 5px 0;"><strong>✓ Meta Box Visible:</strong> Not in user\'s hidden list</p>';
    }
    
    // Check hook registration
    $hooks_registered = has_action('add_meta_boxes');
    echo '<p style="margin: 5px 0;"><strong>add_meta_boxes Hook:</strong> ' . ($hooks_registered ? 'Registered' : 'Not registered') . '</p>';
    
    // Instructions
    echo '<div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">';
    echo '<h4 style="margin: 0 0 5px 0; font-size: 12px;">Troubleshooting Steps:</h4>';
    echo '<ol style="font-size: 11px; margin: 0; padding-left: 20px;">';
    echo '<li>Check "Screen Options" at the top right</li>';
    echo '<li>Look for the meta box in the right sidebar</li>';
    echo '<li>Try refreshing the page</li>';
    echo '<li>Try switching to Classic Editor if available</li>';
    echo '</ol>';
    echo '</div>';
    
    echo '<p style="margin: 10px 0 0 0; text-align: center;">';
    echo '<a href="' . remove_query_arg('hmg_debug_metabox') . '" style="text-decoration: none; color: #0073aa; font-size: 11px;">Hide Debug Info</a>';
    echo '</p>';
    
    echo '</div>';
});

// Also add some JavaScript to highlight the meta box if it exists
add_action('admin_footer', function() {
    if (!isset($_GET['hmg_debug_metabox'])) {
        return;
    }
    
    echo '<script>
    jQuery(document).ready(function($) {
        // Try to find and highlight the meta box
        var metaBox = $("#hmg-ai-content-generator");
        if (metaBox.length) {
            metaBox.css({
                "border": "3px solid #00a32a",
                "box-shadow": "0 0 10px rgba(0,163,42,0.3)"
            });
            
            // Scroll to the meta box
            $("html, body").animate({
                scrollTop: metaBox.offset().top - 100
            }, 1000);
            
            console.log("HMG AI Debug: Meta box found and highlighted!");
        } else {
            console.log("HMG AI Debug: Meta box not found in DOM");
        }
    });
    </script>';
}); 