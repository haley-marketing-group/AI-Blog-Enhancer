<?php
/**
 * Performance Optimizer Class
 *
 * Handles all performance optimizations including lazy loading, caching,
 * asset minification, and load time improvements.
 *
 * @link       https://haleymarketing.com
 * @since      1.4.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

class HMG_AI_Performance_Optimizer {

    /**
     * Performance metrics
     *
     * @since    1.4.0
     * @access   private
     * @var      array    $metrics    Performance measurement data.
     */
    private $metrics;

    /**
     * Cache configuration
     *
     * @since    1.4.0
     * @access   private
     * @var      array    $cache_config    Cache settings.
     */
    private $cache_config;

    /**
     * Asset optimization settings
     *
     * @since    1.4.0
     * @access   private
     * @var      array    $asset_config    Asset optimization settings.
     */
    private $asset_config;

    /**
     * Initialize the Performance Optimizer
     *
     * @since    1.4.0
     */
    public function __construct() {
        $this->metrics = array();
        
        $options = get_option('hmg_ai_blog_enhancer_options', array());
        
        $this->cache_config = array(
            'browser_cache_ttl' => $options['browser_cache_ttl'] ?? 86400, // 1 day
            'api_cache_ttl' => $options['api_cache_ttl'] ?? 3600, // 1 hour
            'static_cache_ttl' => $options['static_cache_ttl'] ?? 604800, // 1 week
            'enable_object_cache' => $options['enable_object_cache'] ?? true,
            'enable_fragment_cache' => $options['enable_fragment_cache'] ?? true
        );
        
        $this->asset_config = array(
            'minify_css' => $options['minify_css'] ?? true,
            'minify_js' => $options['minify_js'] ?? true,
            'concatenate_assets' => $options['concatenate_assets'] ?? true,
            'enable_lazy_load' => $options['enable_lazy_load'] ?? true,
            'enable_async_js' => $options['enable_async_js'] ?? true,
            'enable_critical_css' => $options['enable_critical_css'] ?? true,
            'cdn_enabled' => $options['cdn_enabled'] ?? false,
            'cdn_url' => $options['cdn_url'] ?? ''
        );
        
        // Start performance tracking
        $this->start_tracking();
    }

    /**
     * Start performance tracking
     *
     * @since    1.4.0
     */
    private function start_tracking() {
        $this->metrics['start_time'] = microtime(true);
        $this->metrics['start_memory'] = memory_get_usage();
        $this->metrics['queries_start'] = get_num_queries();
    }

    /**
     * Get performance metrics
     *
     * @since    1.4.0
     * @return   array    Performance metrics
     */
    public function get_metrics() {
        return array(
            'load_time' => (microtime(true) - $this->metrics['start_time']) * 1000, // in ms
            'memory_used' => (memory_get_usage() - $this->metrics['start_memory']) / 1024 / 1024, // in MB
            'queries' => get_num_queries() - $this->metrics['queries_start'],
            'peak_memory' => memory_get_peak_usage() / 1024 / 1024 // in MB
        );
    }

    /**
     * Optimize asset loading
     *
     * @since    1.4.0
     * @param    string    $type        Asset type (css/js)
     * @param    array     $assets      Assets to optimize
     * @return   array                  Optimized assets
     */
    public function optimize_assets($type, $assets) {
        $optimized = array();
        
        foreach ($assets as $handle => $asset) {
            // Skip external assets
            if ($this->is_external_url($asset['src'])) {
                $optimized[$handle] = $asset;
                continue;
            }
            
            // Minify if enabled
            if (($type === 'css' && $this->asset_config['minify_css']) || 
                ($type === 'js' && $this->asset_config['minify_js'])) {
                $asset['src'] = $this->minify_asset($asset['src'], $type);
            }
            
            // Add version for cache busting
            if (empty($asset['ver'])) {
                $asset['ver'] = $this->get_file_version($asset['src']);
            }
            
            // Add async/defer for JS
            if ($type === 'js' && $this->asset_config['enable_async_js']) {
                $asset['async'] = true;
            }
            
            // Convert to CDN URL if enabled
            if ($this->asset_config['cdn_enabled'] && !empty($this->asset_config['cdn_url'])) {
                $asset['src'] = $this->convert_to_cdn_url($asset['src']);
            }
            
            $optimized[$handle] = $asset;
        }
        
        // Concatenate if enabled
        if ($this->asset_config['concatenate_assets']) {
            $optimized = $this->concatenate_assets($type, $optimized);
        }
        
        return $optimized;
    }

    /**
     * Implement lazy loading for components
     *
     * @since    1.4.0
     * @param    string    $content    Content to process
     * @return   string                Processed content with lazy loading
     */
    public function add_lazy_loading($content) {
        if (!$this->asset_config['enable_lazy_load']) {
            return $content;
        }
        
        // Add loading="lazy" to images
        $content = preg_replace(
            '/<img((?!loading=)[^>])*>/i',
            '<img loading="lazy"$1>',
            $content
        );
        
        // Add loading="lazy" to iframes
        $content = preg_replace(
            '/<iframe((?!loading=)[^>])*>/i',
            '<iframe loading="lazy"$1>',
            $content
        );
        
        // Wrap heavy shortcodes in lazy load containers
        $heavy_shortcodes = array('hmg_ai_audio', 'hmg_ai_faq', 'hmg_ai_toc');
        
        foreach ($heavy_shortcodes as $shortcode) {
            $content = preg_replace_callback(
                '/\[' . $shortcode . '[^\]]*\]/i',
                function($matches) use ($shortcode) {
                    return $this->wrap_in_lazy_container($matches[0], $shortcode);
                },
                $content
            );
        }
        
        return $content;
    }

    /**
     * Wrap content in lazy load container
     *
     * @since    1.4.0
     * @param    string    $content        Content to wrap
     * @param    string    $identifier     Identifier for the content
     * @return   string                    Wrapped content
     */
    private function wrap_in_lazy_container($content, $identifier) {
        $unique_id = 'hmg-lazy-' . uniqid();
        
        return sprintf(
            '<div class="hmg-ai-lazy-load" id="%s" data-shortcode="%s" data-content="%s">
                <div class="hmg-ai-lazy-placeholder">
                    <span class="dashicons dashicons-update hmg-ai-spinning"></span>
                    <span>Loading %s...</span>
                </div>
            </div>',
            esc_attr($unique_id),
            esc_attr($identifier),
            esc_attr(base64_encode($content)),
            esc_html(str_replace('_', ' ', $identifier))
        );
    }

    /**
     * Generate critical CSS
     *
     * @since    1.4.0
     * @param    string    $page_type    Type of page
     * @return   string                  Critical CSS
     */
    public function generate_critical_css($page_type = 'post') {
        if (!$this->asset_config['enable_critical_css']) {
            return '';
        }
        
        // Critical CSS for above-the-fold content
        $critical_css = '
        /* HMG AI Critical CSS */
        .hmg-ai-meta-box {
            position: relative;
            background: #fff;
            padding: 15px;
        }
        .hmg-ai-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .hmg-ai-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: hmg-spin 1s ease-in-out infinite;
        }
        @keyframes hmg-spin {
            to { transform: rotate(360deg); }
        }
        .hmg-ai-lazy-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .hmg-ai-spinning {
            animation: hmg-spin 1s linear infinite;
        }
        ';
        
        return $this->minify_css($critical_css);
    }

    /**
     * Optimize database queries
     *
     * @since    1.4.0
     * @param    string    $query_type    Type of query to optimize
     * @return   void
     */
    public function optimize_database($query_type = 'all') {
        global $wpdb;
        
        // Add indexes if not exist
        $this->ensure_indexes();
        
        // Clean up old cache entries
        if ($query_type === 'all' || $query_type === 'cache') {
            $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
            $wpdb->query("DELETE FROM $cache_table WHERE expires_at < NOW()");
            
            // Optimize cache table
            $wpdb->query("OPTIMIZE TABLE $cache_table");
        }
        
        // Clean up old usage data
        if ($query_type === 'all' || $query_type === 'usage') {
            $usage_table = $wpdb->prefix . 'hmg_ai_usage';
            $days_to_keep = 90; // Keep 90 days of usage data
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $usage_table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days_to_keep
            ));
            
            // Optimize usage table
            $wpdb->query("OPTIMIZE TABLE $usage_table");
        }
    }

    /**
     * Ensure database indexes exist
     *
     * @since    1.4.0
     */
    private function ensure_indexes() {
        global $wpdb;
        
        // Check and add indexes for cache table
        $cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
        
        // Check if indexes exist
        $indexes = $wpdb->get_results("SHOW INDEX FROM $cache_table");
        $existing_indexes = array_column($indexes, 'Key_name');
        
        // Add composite index for faster lookups
        if (!in_array('idx_cache_lookup', $existing_indexes)) {
            $wpdb->query("ALTER TABLE $cache_table ADD INDEX idx_cache_lookup (cache_key, expires_at)");
        }
        
        // Check and add indexes for usage table
        $usage_table = $wpdb->prefix . 'hmg_ai_usage';
        
        $usage_indexes = $wpdb->get_results("SHOW INDEX FROM $usage_table");
        $existing_usage_indexes = array_column($usage_indexes, 'Key_name');
        
        // Add composite index for usage queries
        if (!in_array('idx_usage_lookup', $existing_usage_indexes)) {
            $wpdb->query("ALTER TABLE $usage_table ADD INDEX idx_usage_lookup (user_id, created_at)");
        }
    }

    /**
     * Implement fragment caching
     *
     * @since    1.4.0
     * @param    string    $fragment_id    Fragment identifier
     * @param    callable  $callback       Callback to generate content
     * @param    int       $ttl            Time to live in seconds
     * @return   string                    Cached or generated content
     */
    public function fragment_cache($fragment_id, $callback, $ttl = 3600) {
        if (!$this->cache_config['enable_fragment_cache']) {
            return call_user_func($callback);
        }
        
        $cache_key = 'hmg_ai_fragment_' . md5($fragment_id);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $content = call_user_func($callback);
        set_transient($cache_key, $content, $ttl);
        
        return $content;
    }

    /**
     * Preload critical resources
     *
     * @since    1.4.0
     * @return   void
     */
    public function preload_critical_resources() {
        // Preload critical fonts
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" as="style">' . "\n";
        
        // Preload critical scripts
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        
        if (is_singular('post')) {
            // Preload admin JS for editor
            echo '<link rel="preload" href="' . $plugin_url . 'admin/js/hmg-ai-admin.js" as="script">' . "\n";
            
            // Preconnect to API endpoints
            echo '<link rel="preconnect" href="https://generativelanguage.googleapis.com">' . "\n";
            echo '<link rel="preconnect" href="https://api.openai.com">' . "\n";
            echo '<link rel="preconnect" href="https://api.anthropic.com">' . "\n";
            echo '<link rel="dns-prefetch" href="https://api.elevenlabs.io">' . "\n";
        }
    }

    /**
     * Implement browser caching headers
     *
     * @since    1.4.0
     * @param    array    $headers    Current headers
     * @return   array                Modified headers
     */
    public function add_cache_headers($headers) {
        // For static assets
        if (preg_match('/\.(js|css|jpg|jpeg|png|gif|svg|woff|woff2)$/i', $_SERVER['REQUEST_URI'])) {
            $headers['Cache-Control'] = 'public, max-age=' . $this->cache_config['static_cache_ttl'];
            $headers['Expires'] = gmdate('D, d M Y H:i:s', time() + $this->cache_config['static_cache_ttl']) . ' GMT';
        }
        
        // For API responses
        if (strpos($_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php') !== false) {
            $action = $_REQUEST['action'] ?? '';
            
            // Cache read-only API responses
            $cacheable_actions = array('hmg_get_usage_stats', 'hmg_ai_refresh_voices');
            
            if (in_array($action, $cacheable_actions)) {
                $headers['Cache-Control'] = 'private, max-age=' . $this->cache_config['api_cache_ttl'];
            }
        }
        
        return $headers;
    }

    /**
     * Minify CSS
     *
     * @since    1.4.0
     * @param    string    $css    CSS to minify
     * @return   string            Minified CSS
     */
    private function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove unnecessary spaces around punctuation
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        // Remove trailing semicolon before closing brace
        $css = str_replace(';}', '}', $css);
        
        return trim($css);
    }

    /**
     * Minify JavaScript
     *
     * @since    1.4.0
     * @param    string    $js    JavaScript to minify
     * @return   string           Minified JavaScript
     */
    private function minify_js($js) {
        // This is a basic minification. For production, use a proper minifier
        
        // Remove single-line comments
        $js = preg_replace('/\/\/[^\n]*/', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove spaces around operators
        $js = preg_replace('/\s*([\{\}\[\]\(\);,:])\s*/', '$1', $js);
        
        return trim($js);
    }

    /**
     * Minify asset file
     *
     * @since    1.4.0
     * @param    string    $file_url    File URL to minify
     * @param    string    $type        Asset type
     * @return   string                 Minified file URL
     */
    private function minify_asset($file_url, $type) {
        // Convert URL to file path
        $file_path = str_replace(
            plugin_dir_url(dirname(__FILE__)),
            plugin_dir_path(dirname(__FILE__)),
            $file_url
        );
        
        if (!file_exists($file_path)) {
            return $file_url;
        }
        
        // Check if minified version exists
        $minified_path = str_replace('.' . $type, '.min.' . $type, $file_path);
        $minified_url = str_replace('.' . $type, '.min.' . $type, $file_url);
        
        // If minified doesn't exist or is older than source
        if (!file_exists($minified_path) || filemtime($minified_path) < filemtime($file_path)) {
            $content = file_get_contents($file_path);
            
            if ($type === 'css') {
                $minified_content = $this->minify_css($content);
            } else {
                $minified_content = $this->minify_js($content);
            }
            
            // Save minified version
            file_put_contents($minified_path, $minified_content);
        }
        
        return $minified_url;
    }

    /**
     * Check if URL is external
     *
     * @since    1.4.0
     * @param    string    $url    URL to check
     * @return   bool              Whether URL is external
     */
    private function is_external_url($url) {
        $site_host = parse_url(get_site_url(), PHP_URL_HOST);
        $url_host = parse_url($url, PHP_URL_HOST);
        
        return $url_host && $url_host !== $site_host;
    }

    /**
     * Get file version for cache busting
     *
     * @since    1.4.0
     * @param    string    $file_url    File URL
     * @return   string                 Version string
     */
    private function get_file_version($file_url) {
        $file_path = str_replace(
            plugin_dir_url(dirname(__FILE__)),
            plugin_dir_path(dirname(__FILE__)),
            $file_url
        );
        
        if (file_exists($file_path)) {
            return filemtime($file_path);
        }
        
        return HMG_AI_BLOG_ENHANCER_VERSION;
    }

    /**
     * Convert URL to CDN URL
     *
     * @since    1.4.0
     * @param    string    $url    Original URL
     * @return   string            CDN URL
     */
    private function convert_to_cdn_url($url) {
        if (empty($this->asset_config['cdn_url'])) {
            return $url;
        }
        
        $site_url = get_site_url();
        $cdn_url = rtrim($this->asset_config['cdn_url'], '/');
        
        return str_replace($site_url, $cdn_url, $url);
    }

    /**
     * Concatenate assets
     *
     * @since    1.4.0
     * @param    string    $type      Asset type
     * @param    array     $assets    Assets to concatenate
     * @return   array                Concatenated assets
     */
    private function concatenate_assets($type, $assets) {
        // Don't concatenate in development mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return $assets;
        }
        
        $concatenated_content = '';
        $handles_to_remove = array();
        
        foreach ($assets as $handle => $asset) {
            // Skip external assets
            if ($this->is_external_url($asset['src'])) {
                continue;
            }
            
            // Get file content
            $file_path = str_replace(
                plugin_dir_url(dirname(__FILE__)),
                plugin_dir_path(dirname(__FILE__)),
                $asset['src']
            );
            
            if (file_exists($file_path)) {
                $concatenated_content .= file_get_contents($file_path) . "\n";
                $handles_to_remove[] = $handle;
            }
        }
        
        if (!empty($concatenated_content)) {
            // Save concatenated file
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/hmg-ai-cache';
            
            if (!file_exists($cache_dir)) {
                wp_mkdir_p($cache_dir);
            }
            
            $hash = md5($concatenated_content);
            $filename = 'hmg-ai-bundle-' . $hash . '.' . $type;
            $file_path = $cache_dir . '/' . $filename;
            $file_url = $upload_dir['baseurl'] . '/hmg-ai-cache/' . $filename;
            
            if (!file_exists($file_path)) {
                file_put_contents($file_path, $concatenated_content);
            }
            
            // Remove individual assets and add bundle
            foreach ($handles_to_remove as $handle) {
                unset($assets[$handle]);
            }
            
            $assets['hmg-ai-bundle'] = array(
                'src' => $file_url,
                'ver' => $hash
            );
        }
        
        return $assets;
    }

    /**
     * Get performance report
     *
     * @since    1.4.0
     * @return   array    Performance report
     */
    public function get_performance_report() {
        $metrics = $this->get_metrics();
        
        $report = array(
            'status' => $metrics['load_time'] < 500 ? 'excellent' : ($metrics['load_time'] < 1000 ? 'good' : 'needs_improvement'),
            'load_time' => $metrics['load_time'],
            'memory_used' => $metrics['memory_used'],
            'queries' => $metrics['queries'],
            'recommendations' => array()
        );
        
        // Add recommendations
        if ($metrics['load_time'] > 500) {
            $report['recommendations'][] = 'Consider enabling object caching';
        }
        
        if ($metrics['queries'] > 50) {
            $report['recommendations'][] = 'High number of database queries detected';
        }
        
        if ($metrics['memory_used'] > 50) {
            $report['recommendations'][] = 'High memory usage detected';
        }
        
        if (!$this->asset_config['minify_js']) {
            $report['recommendations'][] = 'Enable JavaScript minification';
        }
        
        if (!$this->asset_config['minify_css']) {
            $report['recommendations'][] = 'Enable CSS minification';
        }
        
        if (!$this->asset_config['enable_lazy_load']) {
            $report['recommendations'][] = 'Enable lazy loading for better performance';
        }
        
        return $report;
    }
}
