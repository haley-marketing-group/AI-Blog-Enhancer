<?php
/**
 * Performance Dashboard
 *
 * Displays performance metrics and optimization tools
 *
 * @link       https://haleymarketing.com
 * @since      1.4.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/admin/partials
 */

// Load Performance Optimizer
if (!class_exists('HMG_AI_Performance_Optimizer')) {
    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/class-performance-optimizer.php';
}

$performance = new HMG_AI_Performance_Optimizer();
$metrics = $performance->get_metrics();
$report = $performance->get_performance_report();

// Get cache stats
global $wpdb;
$cache_table = $wpdb->prefix . 'hmg_ai_content_cache';
$cache_stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as total_entries,
        SUM(CASE WHEN expires_at > NOW() THEN 1 ELSE 0 END) as active_entries,
        SUM(LENGTH(content)) / 1024 / 1024 as total_size_mb
    FROM $cache_table
");

// Get usage stats
$usage_table = $wpdb->prefix . 'hmg_ai_usage';
$usage_stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as total_requests,
        SUM(tokens_used) as total_tokens,
        COUNT(DISTINCT user_id) as unique_users
    FROM $usage_table
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");

$options = get_option('hmg_ai_blog_enhancer_options', array());
?>

<div class="wrap hmg-ai-performance-dashboard">
    <h1>
        <span class="dashicons dashicons-performance" style="font-size: 36px; margin-right: 10px;"></span>
        <?php _e('Performance Dashboard', 'hmg-ai-blog-enhancer'); ?>
    </h1>

    <!-- Performance Score -->
    <div class="hmg-ai-performance-score">
        <div class="score-container <?php echo esc_attr($report['status']); ?>">
            <div class="score-circle">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="90" stroke="#e0e0e0" stroke-width="10" fill="none"/>
                    <circle cx="100" cy="100" r="90" 
                            stroke="<?php echo $report['status'] === 'excellent' ? '#4caf50' : ($report['status'] === 'good' ? '#ff9800' : '#f44336'); ?>" 
                            stroke-width="10" 
                            fill="none"
                            stroke-dasharray="<?php echo 565 * ($report['load_time'] < 500 ? 1 : ($report['load_time'] < 1000 ? 0.7 : 0.4)); ?> 565"
                            transform="rotate(-90 100 100)"/>
                </svg>
                <div class="score-text">
                    <span class="score-value"><?php echo round($report['load_time']); ?></span>
                    <span class="score-unit">ms</span>
                </div>
            </div>
            <div class="score-details">
                <h2><?php 
                    if ($report['status'] === 'excellent') {
                        _e('Excellent Performance!', 'hmg-ai-blog-enhancer');
                    } elseif ($report['status'] === 'good') {
                        _e('Good Performance', 'hmg-ai-blog-enhancer');
                    } else {
                        _e('Performance Can Be Improved', 'hmg-ai-blog-enhancer');
                    }
                ?></h2>
                <p><?php echo sprintf(__('Current page load time: %sms', 'hmg-ai-blog-enhancer'), round($report['load_time'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="hmg-ai-metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="metric-content">
                <h3><?php _e('Load Time', 'hmg-ai-blog-enhancer'); ?></h3>
                <p class="metric-value"><?php echo round($metrics['load_time']); ?>ms</p>
                <p class="metric-target">Target: < 500ms</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <span class="dashicons dashicons-database"></span>
            </div>
            <div class="metric-content">
                <h3><?php _e('Database Queries', 'hmg-ai-blog-enhancer'); ?></h3>
                <p class="metric-value"><?php echo $metrics['queries']; ?></p>
                <p class="metric-target">Optimal: < 50</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <span class="dashicons dashicons-admin-generic"></span>
            </div>
            <div class="metric-content">
                <h3><?php _e('Memory Usage', 'hmg-ai-blog-enhancer'); ?></h3>
                <p class="metric-value"><?php echo round($metrics['memory_used'], 2); ?>MB</p>
                <p class="metric-target">Peak: <?php echo round($metrics['peak_memory'], 2); ?>MB</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <span class="dashicons dashicons-archive"></span>
            </div>
            <div class="metric-content">
                <h3><?php _e('Cache Hit Rate', 'hmg-ai-blog-enhancer'); ?></h3>
                <p class="metric-value">
                    <?php 
                    $hit_rate = $cache_stats->total_entries > 0 
                        ? round(($cache_stats->active_entries / $cache_stats->total_entries) * 100) 
                        : 0;
                    echo $hit_rate . '%';
                    ?>
                </p>
                <p class="metric-target"><?php echo $cache_stats->active_entries; ?> active entries</p>
            </div>
        </div>
    </div>

    <!-- Optimization Recommendations -->
    <?php if (!empty($report['recommendations'])): ?>
    <div class="hmg-ai-recommendations">
        <h2>
            <span class="dashicons dashicons-lightbulb"></span>
            <?php _e('Performance Recommendations', 'hmg-ai-blog-enhancer'); ?>
        </h2>
        <ul>
            <?php foreach ($report['recommendations'] as $recommendation): ?>
                <li><?php echo esc_html($recommendation); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Performance Settings -->
    <div class="hmg-ai-performance-settings">
        <h2>
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Performance Optimization Settings', 'hmg-ai-blog-enhancer'); ?>
        </h2>
        
        <form method="post" action="" id="hmg-ai-performance-form">
            <?php wp_nonce_field('hmg_ai_performance_settings', 'hmg_ai_performance_nonce'); ?>
            
            <div class="settings-grid">
                <!-- Asset Optimization -->
                <div class="setting-group">
                    <h3><?php _e('Asset Optimization', 'hmg-ai-blog-enhancer'); ?></h3>
                    
                    <label>
                        <input type="checkbox" name="minify_css" value="1" 
                               <?php checked($options['minify_css'] ?? true); ?> />
                        <?php _e('Minify CSS', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <input type="checkbox" name="minify_js" value="1" 
                               <?php checked($options['minify_js'] ?? true); ?> />
                        <?php _e('Minify JavaScript', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <input type="checkbox" name="concatenate_assets" value="1" 
                               <?php checked($options['concatenate_assets'] ?? true); ?> />
                        <?php _e('Concatenate Assets', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <input type="checkbox" name="enable_lazy_load" value="1" 
                               <?php checked($options['enable_lazy_load'] ?? true); ?> />
                        <?php _e('Enable Lazy Loading', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <input type="checkbox" name="enable_async_js" value="1" 
                               <?php checked($options['enable_async_js'] ?? true); ?> />
                        <?php _e('Load JavaScript Asynchronously', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                </div>

                <!-- Cache Settings -->
                <div class="setting-group">
                    <h3><?php _e('Cache Settings', 'hmg-ai-blog-enhancer'); ?></h3>
                    
                    <label>
                        <input type="checkbox" name="enable_object_cache" value="1" 
                               <?php checked($options['enable_object_cache'] ?? true); ?> />
                        <?php _e('Enable Object Cache', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <input type="checkbox" name="enable_fragment_cache" value="1" 
                               <?php checked($options['enable_fragment_cache'] ?? true); ?> />
                        <?php _e('Enable Fragment Cache', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <?php _e('Browser Cache TTL', 'hmg-ai-blog-enhancer'); ?>
                        <select name="browser_cache_ttl">
                            <option value="3600" <?php selected($options['browser_cache_ttl'] ?? 86400, 3600); ?>>1 Hour</option>
                            <option value="86400" <?php selected($options['browser_cache_ttl'] ?? 86400, 86400); ?>>1 Day</option>
                            <option value="604800" <?php selected($options['browser_cache_ttl'] ?? 86400, 604800); ?>>1 Week</option>
                            <option value="2592000" <?php selected($options['browser_cache_ttl'] ?? 86400, 2592000); ?>>30 Days</option>
                        </select>
                    </label>
                    
                    <label>
                        <?php _e('API Cache TTL', 'hmg-ai-blog-enhancer'); ?>
                        <select name="api_cache_ttl">
                            <option value="300" <?php selected($options['api_cache_ttl'] ?? 3600, 300); ?>>5 Minutes</option>
                            <option value="900" <?php selected($options['api_cache_ttl'] ?? 3600, 900); ?>>15 Minutes</option>
                            <option value="3600" <?php selected($options['api_cache_ttl'] ?? 3600, 3600); ?>>1 Hour</option>
                            <option value="86400" <?php selected($options['api_cache_ttl'] ?? 3600, 86400); ?>>1 Day</option>
                        </select>
                    </label>
                </div>

                <!-- CDN Settings -->
                <div class="setting-group">
                    <h3><?php _e('CDN Settings', 'hmg-ai-blog-enhancer'); ?></h3>
                    
                    <label>
                        <input type="checkbox" name="cdn_enabled" value="1" 
                               <?php checked($options['cdn_enabled'] ?? false); ?> />
                        <?php _e('Enable CDN', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <?php _e('CDN URL', 'hmg-ai-blog-enhancer'); ?>
                        <input type="url" name="cdn_url" 
                               value="<?php echo esc_attr($options['cdn_url'] ?? ''); ?>" 
                               placeholder="https://cdn.example.com" />
                    </label>
                </div>

                <!-- Critical CSS -->
                <div class="setting-group">
                    <h3><?php _e('Advanced', 'hmg-ai-blog-enhancer'); ?></h3>
                    
                    <label>
                        <input type="checkbox" name="enable_critical_css" value="1" 
                               <?php checked($options['enable_critical_css'] ?? true); ?> />
                        <?php _e('Enable Critical CSS', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                    
                    <label>
                        <?php _e('Max Concurrent Lazy Loads', 'hmg-ai-blog-enhancer'); ?>
                        <input type="number" name="max_concurrent_loads" 
                               value="<?php echo esc_attr($options['max_concurrent_loads'] ?? 2); ?>" 
                               min="1" max="5" />
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">
                    <span class="dashicons dashicons-saved"></span>
                    <?php _e('Save Performance Settings', 'hmg-ai-blog-enhancer'); ?>
                </button>
                
                <button type="button" class="button hmg-ai-optimize-db">
                    <span class="dashicons dashicons-database"></span>
                    <?php _e('Optimize Database', 'hmg-ai-blog-enhancer'); ?>
                </button>
                
                <button type="button" class="button hmg-ai-clear-cache">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Clear All Caches', 'hmg-ai-blog-enhancer'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Cache Statistics -->
    <div class="hmg-ai-cache-stats">
        <h2>
            <span class="dashicons dashicons-chart-bar"></span>
            <?php _e('Cache Statistics', 'hmg-ai-blog-enhancer'); ?>
        </h2>
        
        <div class="stats-grid">
            <div class="stat-item">
                <strong><?php _e('Total Cache Entries:', 'hmg-ai-blog-enhancer'); ?></strong>
                <span><?php echo number_format($cache_stats->total_entries ?? 0); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php _e('Active Entries:', 'hmg-ai-blog-enhancer'); ?></strong>
                <span><?php echo number_format($cache_stats->active_entries ?? 0); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php _e('Cache Size:', 'hmg-ai-blog-enhancer'); ?></strong>
                <span><?php echo round($cache_stats->total_size_mb ?? 0, 2); ?>MB</span>
            </div>
            <div class="stat-item">
                <strong><?php _e('API Requests (30 days):', 'hmg-ai-blog-enhancer'); ?></strong>
                <span><?php echo number_format($usage_stats->total_requests ?? 0); ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.hmg-ai-performance-dashboard {
    max-width: 1200px;
}

.hmg-ai-performance-score {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 30px;
    margin: 20px 0;
    color: white;
}

.score-container {
    display: flex;
    align-items: center;
    gap: 30px;
}

.score-circle {
    position: relative;
    width: 200px;
    height: 200px;
}

.score-circle svg {
    width: 100%;
    height: 100%;
}

.score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.score-value {
    font-size: 48px;
    font-weight: bold;
    display: block;
}

.score-unit {
    font-size: 18px;
    opacity: 0.9;
}

.score-details h2 {
    color: white;
    margin: 0 0 10px 0;
}

.score-details p {
    margin: 0;
    opacity: 0.9;
}

.hmg-ai-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.metric-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.metric-icon {
    background: #f0f0f0;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.metric-icon .dashicons {
    font-size: 24px;
    color: #667eea;
}

.metric-content h3 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #666;
}

.metric-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.metric-target {
    font-size: 12px;
    color: #999;
    margin: 5px 0 0 0;
}

.hmg-ai-recommendations {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 20px;
    margin: 30px 0;
    border-radius: 5px;
}

.hmg-ai-recommendations h2 {
    margin-top: 0;
    color: #856404;
}

.hmg-ai-recommendations ul {
    margin: 15px 0 0 20px;
}

.hmg-ai-recommendations li {
    margin: 8px 0;
}

.hmg-ai-performance-settings {
    background: white;
    padding: 30px;
    border-radius: 8px;
    margin: 30px 0;
    border: 1px solid #e0e0e0;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 30px 0;
}

.setting-group h3 {
    margin: 0 0 15px 0;
    color: #333;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}

.setting-group label {
    display: block;
    margin: 12px 0;
    cursor: pointer;
}

.setting-group input[type="checkbox"] {
    margin-right: 8px;
}

.setting-group input[type="url"],
.setting-group input[type="number"],
.setting-group select {
    display: block;
    width: 100%;
    margin-top: 5px;
    padding: 8px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.form-actions .dashicons {
    margin-right: 5px;
}

.hmg-ai-cache-stats {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin: 30px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stat-item strong {
    color: #666;
    font-size: 13px;
}

.stat-item span {
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.score-container.excellent .score-circle circle:nth-child(2) {
    stroke: #4caf50;
}

.score-container.good .score-circle circle:nth-child(2) {
    stroke: #ff9800;
}

.score-container.needs_improvement .score-circle circle:nth-child(2) {
    stroke: #f44336;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle performance settings form
    $('#hmg-ai-performance-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Saving...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=hmg_save_performance_settings',
            success: function(response) {
                if (response.success) {
                    $button.html('<span class="dashicons dashicons-yes"></span> Saved!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Use modal for error
                    HMGAIAdmin.showNotice(
                        'Error: ' + (response.data.message || 'Failed to save settings'),
                        'error'
                    );
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Performance Settings');
                }
            },
            error: function() {
                // Use modal for error
                HMGAIAdmin.showNotice(
                    'Failed to save settings. Please check your connection and try again.',
                    'error'
                );
                $button.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Performance Settings');
            }
        });
    });
    
    // Handle database optimization
    $('.hmg-ai-optimize-db').on('click', function() {
        const $button = $(this);
        
        if (!confirm('This will optimize database tables. Continue?')) {
            return;
        }
        
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Optimizing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hmg_optimize_database',
                nonce: '<?php echo wp_create_nonce('hmg_ai_performance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $button.html('<span class="dashicons dashicons-yes"></span> Optimized!');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Use modal for error
                    HMGAIAdmin.showNotice(
                        'Error: ' + (response.data.message || 'Optimization failed'),
                        'error'
                    );
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-database"></span> Optimize Database');
                }
            },
            error: function() {
                // Use modal for error
                HMGAIAdmin.showNotice(
                    'Optimization failed. Please check your connection and try again.',
                    'error'
                );
                $button.prop('disabled', false).html('<span class="dashicons dashicons-database"></span> Optimize Database');
            }
        });
    });
    
    // Handle cache clearing
    $('.hmg-ai-clear-cache').on('click', function() {
        const $button = $(this);
        
        if (!confirm('This will clear all plugin caches. Continue?')) {
            return;
        }
        
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Clearing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hmg_clear_all_caches',
                nonce: '<?php echo wp_create_nonce('hmg_ai_performance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $button.html('<span class="dashicons dashicons-yes"></span> Cleared!');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Use modal for error
                    HMGAIAdmin.showNotice(
                        'Error: ' + (response.data.message || 'Failed to clear cache'),
                        'error'
                    );
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Clear All Caches');
                }
            },
            error: function() {
                // Use modal for error
                HMGAIAdmin.showNotice(
                    'Failed to clear cache. Please check your connection and try again.',
                    'error'
                );
                $button.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Clear All Caches');
            }
        });
    });
});
</script>
