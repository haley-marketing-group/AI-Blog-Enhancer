<?php
/**
 * AJAX endpoint to refresh usage statistics
 * This can be called to get fresh usage data
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Check if user can edit posts
if (!current_user_can('edit_posts')) {
    wp_die(json_encode(array('error' => 'Unauthorized')));
}

// Include required files
require_once(plugin_dir_path(dirname(__FILE__)) . 'services/class-auth-service.php');

// Get fresh statistics
$auth_service = new HMG_AI_Auth_Service();
$spending_stats = $auth_service->get_spending_stats();

// Format response
$response = array(
    'success' => true,
    'usage' => array(
        'spending' => array(
            'used' => $spending_stats['monthly']['spent'],
            'limit' => $spending_stats['monthly']['limit'],
            'percentage' => $spending_stats['monthly']['percentage']
        ),
        'api_calls' => $spending_stats['monthly']['requests'],
        'tokens' => $spending_stats['monthly']['tokens'],
        'reset_date' => $spending_stats['reset_date'],
        'daily' => array(
            'spent' => $spending_stats['daily']['spent'],
            'requests' => $spending_stats['daily']['requests']
        )
    )
);

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
