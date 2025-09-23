<?php
/**
 * Plugin Name:       HMG AI Blog Enhancer
 * Plugin URI:        https://haleymarketing.com/ai-blog-enhancer
 * Description:       Professional AI-powered blog content enhancement with key takeaways, FAQ generation, TOC, and audio conversion. Crafted with Haley Marketing's commitment to excellence and Apple-like polish.
 * Version:           1.0.0
 * Author:            Haley Marketing
 * Author URI:        https://haleymarketing.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hmg-ai-blog-enhancer
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 * 
 * @package HMG_AI_Blog_Enhancer
 * @version 1.0.0
 * @author  Haley Marketing
 * @link    https://haleymarketing.com
 * @since   1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die('Direct access denied.');
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('HMG_AI_BLOG_ENHANCER_VERSION', '1.0.0');

/**
 * Plugin constants for paths and URLs
 */
define('HMG_AI_BLOG_ENHANCER_PLUGIN_NAME', 'hmg-ai-blog-enhancer');
define('HMG_AI_BLOG_ENHANCER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMG_AI_BLOG_ENHANCER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HMG_AI_BLOG_ENHANCER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hmg-ai-activator.php
 */
function activate_hmg_ai_blog_enhancer() {
    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-activator.php';
    HMG_AI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hmg-ai-deactivator.php
 */
function deactivate_hmg_ai_blog_enhancer() {
    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-deactivator.php';
    HMG_AI_Deactivator::deactivate();
}

/**
 * Load debug helper for meta box troubleshooting
 */
if (is_admin()) {
    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'debug-metabox-visibility.php';
}

/**
 * Register activation and deactivation hooks
 */
register_activation_hook(__FILE__, 'activate_hmg_ai_blog_enhancer');
register_deactivation_hook(__FILE__, 'deactivate_hmg_ai_blog_enhancer');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-hmg-ai-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_hmg_ai_blog_enhancer() {
    $plugin = new HMG_AI_Core();
    $plugin->run();
}

/**
 * Initialize the plugin
 */
run_hmg_ai_blog_enhancer(); 