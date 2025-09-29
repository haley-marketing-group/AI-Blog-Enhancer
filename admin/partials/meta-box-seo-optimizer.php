<?php
/**
 * SEO Optimizer Meta Box
 *
 * Displays SEO optimization tools and insights in the post editor
 *
 * @link       https://haleymarketing.com
 * @since      1.3.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/admin/partials
 */

// Get current post
global $post;
$post_id = $post->ID;

// Load SEO Optimizer
if (!class_exists('HMG_AI_SEO_Optimizer')) {
    require_once HMG_AI_BLOG_ENHANCER_PLUGIN_DIR . 'includes/services/class-seo-optimizer.php';
}

$seo_optimizer = new HMG_AI_SEO_Optimizer();
$seo_data = $seo_optimizer->get_seo_data($post_id);

// Get plugin options
$options = get_option('hmg_ai_blog_enhancer_options', array());
?>

<div class="hmg-ai-seo-box">
    
    <!-- SEO Score Overview -->
    <div class="hmg-ai-seo-score-section">
        <h4>
            <span class="dashicons dashicons-chart-line"></span>
            <?php _e('SEO Score', 'hmg-ai-blog-enhancer'); ?>
        </h4>
        
        <div class="hmg-ai-seo-score-display">
            <?php if (!empty($seo_data['readability_score'])): ?>
                <?php 
                $score = $seo_data['readability_score'];
                $score_class = $score >= 70 ? 'good' : ($score >= 50 ? 'moderate' : 'poor');
                ?>
                <div class="hmg-ai-score-circle-container">
                    <svg class="score-svg" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="90" stroke="rgba(255,255,255,0.2)" stroke-width="10" fill="none"/>
                        <circle class="score-progress" cx="100" cy="100" r="90" 
                                stroke="<?php echo $score >= 70 ? '#4caf50' : ($score >= 50 ? '#ff9800' : '#f44336'); ?>" 
                                stroke-width="10" 
                                fill="none"
                                stroke-dasharray="<?php echo 565 * ($score / 100); ?> 565"
                                transform="rotate(-90 100 100)"/>
                    </svg>
                    <div class="score-inner">
                        <span class="score-value"><?php echo round($score); ?></span>
                        <span class="score-label">/ 100</span>
                    </div>
                </div>
                <div class="hmg-ai-score-details">
                    <div class="score-status <?php echo $score_class; ?>">
                        <?php 
                        if ($score >= 70) {
                            echo '<span class="dashicons dashicons-awards"></span>';
                            echo '<h3>' . __('Excellent SEO!', 'hmg-ai-blog-enhancer') . '</h3>';
                            echo '<p>' . __('Your content is well-optimized for search engines', 'hmg-ai-blog-enhancer') . '</p>';
                        } elseif ($score >= 50) {
                            echo '<span class="dashicons dashicons-thumbs-up"></span>';
                            echo '<h3>' . __('Good Progress', 'hmg-ai-blog-enhancer') . '</h3>';
                            echo '<p>' . __('Your content has room for improvement', 'hmg-ai-blog-enhancer') . '</p>';
                        } else {
                            echo '<span class="dashicons dashicons-info"></span>';
                            echo '<h3>' . __('Needs Attention', 'hmg-ai-blog-enhancer') . '</h3>';
                            echo '<p>' . __('Optimize your content for better results', 'hmg-ai-blog-enhancer') . '</p>';
                        }
                        ?>
                    </div>
                    <div class="score-meta">
                        <?php if (!empty($seo_data['optimized_date'])): ?>
                            <span class="dashicons dashicons-clock"></span>
                            <?php echo sprintf(
                                __('Optimized %s ago', 'hmg-ai-blog-enhancer'),
                                human_time_diff(strtotime($seo_data['optimized_date']), current_time('timestamp'))
                            ); ?>
                        <?php else: ?>
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('Click Auto-Optimize for improvements', 'hmg-ai-blog-enhancer'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="hmg-ai-seo-not-analyzed">
                    <div class="seo-placeholder-circle">
                        <svg viewBox="0 0 200 200">
                            <circle cx="100" cy="100" r="90" stroke="rgba(255,255,255,0.2)" stroke-width="10" fill="none"/>
                            <circle cx="100" cy="100" r="90" 
                                    stroke="rgba(255,255,255,0.4)" 
                                    stroke-width="10" 
                                    fill="none"
                                    stroke-dasharray="20 10"
                                    class="rotating-dashes"/>
                        </svg>
                        <div class="placeholder-content">
                            <span class="dashicons dashicons-search"></span>
                            <span class="placeholder-text">?</span>
                        </div>
                    </div>
                    <div class="seo-placeholder-info">
                        <h3><?php _e('SEO Analysis Ready', 'hmg-ai-blog-enhancer'); ?></h3>
                        <p><?php _e('Analyze your content to get:', 'hmg-ai-blog-enhancer'); ?></p>
                        <ul class="seo-benefits">
                            <li><span class="dashicons dashicons-yes-alt"></span> <?php _e('Readability Score', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><span class="dashicons dashicons-yes-alt"></span> <?php _e('Keyword Optimization', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><span class="dashicons dashicons-yes-alt"></span> <?php _e('Meta Description', 'hmg-ai-blog-enhancer'); ?></li>
                            <li><span class="dashicons dashicons-yes-alt"></span> <?php _e('Internal Link Suggestions', 'hmg-ai-blog-enhancer'); ?></li>
                        </ul>
                        <div class="seo-cta">
                            <span class="dashicons dashicons-arrow-right-alt"></span>
                            <?php _e('Click "Analyze SEO" below to start', 'hmg-ai-blog-enhancer'); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SEO Actions -->
    <div class="hmg-ai-seo-actions">
        <button type="button" class="button button-primary hmg-ai-analyze-seo">
            <span class="dashicons dashicons-search"></span>
            <?php _e('Analyze SEO', 'hmg-ai-blog-enhancer'); ?>
        </button>
        <button type="button" class="button hmg-ai-optimize-seo" <?php echo empty($seo_data['readability_score']) ? 'disabled' : ''; ?>>
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Auto-Optimize', 'hmg-ai-blog-enhancer'); ?>
        </button>
    </div>

    <!-- Meta Description -->
    <div class="hmg-ai-seo-field">
        <label for="hmg-ai-meta-description">
            <strong><?php _e('Meta Description', 'hmg-ai-blog-enhancer'); ?></strong>
            <span class="hmg-ai-char-count" id="meta-desc-count">
                <?php echo !empty($seo_data['meta_description']) ? strlen($seo_data['meta_description']) : 0; ?>/160
            </span>
        </label>
        <textarea 
            id="hmg-ai-meta-description" 
            name="hmg_ai_meta_description" 
            rows="3"
            maxlength="160"
            placeholder="<?php _e('Enter a compelling meta description for search results...', 'hmg-ai-blog-enhancer'); ?>"
        ><?php echo esc_textarea($seo_data['meta_description'] ?? ''); ?></textarea>
        <button type="button" class="button button-small hmg-ai-generate-meta">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php _e('Generate with AI', 'hmg-ai-blog-enhancer'); ?>
        </button>
    </div>

    <!-- Focus Keywords -->
    <div class="hmg-ai-seo-field">
        <label for="hmg-ai-keywords">
            <strong><?php _e('Focus Keywords', 'hmg-ai-blog-enhancer'); ?></strong>
        </label>
        <div class="hmg-ai-keywords-container">
            <?php if (!empty($seo_data['keywords']) && is_array($seo_data['keywords'])): ?>
                <?php foreach ($seo_data['keywords'] as $keyword): ?>
                    <span class="hmg-ai-keyword-tag">
                        <?php echo esc_html($keyword); ?>
                        <button type="button" class="hmg-ai-remove-keyword" data-keyword="<?php echo esc_attr($keyword); ?>">×</button>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
            <input 
                type="text" 
                id="hmg-ai-add-keyword" 
                placeholder="<?php _e('Add keyword...', 'hmg-ai-blog-enhancer'); ?>"
                class="hmg-ai-keyword-input"
            />
        </div>
        <button type="button" class="button button-small hmg-ai-extract-keywords">
            <span class="dashicons dashicons-tag"></span>
            <?php _e('Extract Keywords', 'hmg-ai-blog-enhancer'); ?>
        </button>
    </div>

    <!-- SEO Title -->
    <div class="hmg-ai-seo-field">
        <label for="hmg-ai-seo-title">
            <strong><?php _e('SEO Title', 'hmg-ai-blog-enhancer'); ?></strong>
            <span class="hmg-ai-char-count" id="title-count">
                <?php echo !empty($seo_data['seo_title']) ? strlen($seo_data['seo_title']) : strlen($post->post_title); ?>/60
            </span>
        </label>
        <input 
            type="text" 
            id="hmg-ai-seo-title" 
            name="hmg_ai_seo_title" 
            value="<?php echo esc_attr($seo_data['seo_title'] ?? $post->post_title); ?>"
            maxlength="60"
            placeholder="<?php _e('SEO-optimized title...', 'hmg-ai-blog-enhancer'); ?>"
        />
    </div>

    <!-- SEO Suggestions -->
    <?php if (!empty($seo_data['suggestions']) && is_array($seo_data['suggestions'])): ?>
        <div class="hmg-ai-seo-suggestions">
            <h4>
                <span class="dashicons dashicons-lightbulb"></span>
                <?php _e('SEO Suggestions', 'hmg-ai-blog-enhancer'); ?>
            </h4>
            <ul>
                <?php foreach ($seo_data['suggestions'] as $suggestion): ?>
                    <li class="suggestion-<?php echo esc_attr($suggestion['priority'] ?? 'low'); ?>">
                        <?php 
                        $icon = 'info';
                        if (isset($suggestion['priority'])) {
                            if ($suggestion['priority'] == 'high') $icon = 'warning';
                            elseif ($suggestion['priority'] == 'medium') $icon = 'info';
                            else $icon = 'yes-alt';
                        }
                        ?>
                        <span class="dashicons dashicons-<?php echo $icon; ?>"></span>
                        <?php echo esc_html($suggestion['message']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Internal Links -->
    <?php if (!empty($seo_data['internal_links']) && is_array($seo_data['internal_links'])): ?>
        <div class="hmg-ai-seo-internal-links">
            <h4>
                <span class="dashicons dashicons-admin-links"></span>
                <?php _e('Suggested Internal Links', 'hmg-ai-blog-enhancer'); ?>
            </h4>
            <ul>
                <?php foreach ($seo_data['internal_links'] as $link): ?>
                    <li>
                        <a href="<?php echo esc_url($link['url']); ?>" target="_blank">
                            <?php echo esc_html($link['title']); ?>
                        </a>
                        <span class="link-keyword">(<?php echo esc_html($link['keyword']); ?>)</span>
                        <button type="button" class="button button-small hmg-ai-insert-link" 
                                data-url="<?php echo esc_attr($link['url']); ?>"
                                data-title="<?php echo esc_attr($link['title']); ?>"
                                data-keyword="<?php echo esc_attr($link['keyword']); ?>">
                            <?php _e('Insert', 'hmg-ai-blog-enhancer'); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Schema Markup Toggle -->
    <div class="hmg-ai-seo-schema">
        <label>
            <input type="checkbox" id="hmg-ai-enable-schema" name="hmg_ai_enable_schema" 
                   <?php checked(!empty($seo_data['schema_markup'])); ?> />
            <strong><?php _e('Enable Schema Markup', 'hmg-ai-blog-enhancer'); ?></strong>
        </label>
        <p class="description">
            <?php _e('Adds structured data to help search engines understand your content better', 'hmg-ai-blog-enhancer'); ?>
        </p>
    </div>

    <!-- Hidden fields for data storage -->
    <input type="hidden" id="hmg-ai-seo-data" value="<?php echo esc_attr(json_encode($seo_data)); ?>" />
    <?php wp_nonce_field('hmg_ai_seo_nonce', 'hmg_ai_seo_nonce'); ?>
</div>

<style>
/* SEO Meta Box Styles */
.hmg-ai-seo-box {
    padding: 15px;
}

.hmg-ai-seo-score-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.hmg-ai-seo-score-section h4 {
    color: white;
    margin: 0 0 15px 0;
}

.hmg-ai-seo-score-display {
    display: flex;
    align-items: center;
    gap: 20px;
}

.hmg-ai-score-circle-container {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.score-svg {
    width: 100%;
    height: 100%;
}

.score-progress {
    animation: drawCircle 1.5s ease-out forwards;
}

@keyframes drawCircle {
    from {
        stroke-dasharray: 0 565;
    }
}

.score-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    background: white;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    animation: scaleIn 0.5s ease-out 0.5s both;
}

@keyframes scaleIn {
    from {
        transform: translate(-50%, -50%) scale(0);
    }
    to {
        transform: translate(-50%, -50%) scale(1);
    }
}

.score-inner .score-value {
    font-size: 28px;
    font-weight: bold;
    line-height: 1;
    color: #333;
    animation: countUp 1s ease-out 0.8s both;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.score-inner .score-label {
    font-size: 12px;
    color: #999;
    opacity: 0;
    animation: fadeIn 0.5s ease-out 1.3s forwards;
}

.hmg-ai-score-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.score-status {
    margin-bottom: 15px;
    animation: slideInRight 0.6s ease-out 0.2s both;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.score-status .dashicons {
    font-size: 36px;
    display: block;
    margin-bottom: 10px;
}

.score-status.good .dashicons {
    color: #4caf50;
}

.score-status.moderate .dashicons {
    color: #ff9800;
}

.score-status.poor .dashicons {
    color: #f44336;
}

.score-status h3 {
    color: white;
    margin: 0 0 5px 0;
    font-size: 20px;
    font-weight: 600;
}

.score-status p {
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-size: 14px;
}

.score-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.8);
    font-size: 13px;
    animation: fadeIn 0.5s ease-out 1s both;
}

.score-meta .dashicons {
    font-size: 16px;
    color: rgba(255,255,255,0.7);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.hmg-ai-seo-not-analyzed {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 20px;
}

.seo-placeholder-circle {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.seo-placeholder-circle svg {
    width: 100%;
    height: 100%;
}

.rotating-dashes {
    animation: rotate 20s linear infinite;
    transform-origin: center;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.placeholder-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.placeholder-content .dashicons {
    font-size: 32px;
    color: rgba(255,255,255,0.8);
    display: block;
    animation: pulse 2s ease-in-out infinite;
}

.placeholder-text {
    font-size: 28px;
    font-weight: bold;
    color: rgba(255,255,255,0.9);
    display: block;
    margin-top: -10px;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}

.seo-placeholder-info {
    flex: 1;
    color: white;
}

.seo-placeholder-info h3 {
    color: white;
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 600;
}

.seo-placeholder-info > p {
    color: rgba(255,255,255,0.9);
    margin: 0 0 15px 0;
}

.seo-benefits {
    list-style: none;
    margin: 0 0 20px 0;
    padding: 0;
}

.seo-benefits li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 8px 0;
    color: rgba(255,255,255,0.95);
    font-size: 14px;
}

.seo-benefits .dashicons {
    color: #4caf50;
    font-size: 18px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.seo-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    padding: 10px 15px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 500;
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    animation: glow 2s ease-in-out infinite;
}

.seo-cta .dashicons {
    font-size: 16px;
    animation: slideRight 1.5s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(255,255,255,0.2);
    }
    50% {
        box-shadow: 0 0 20px rgba(255,255,255,0.4);
    }
}

@keyframes slideRight {
    0%, 100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(4px);
    }
}

/* Analyzing state */
.seo-analyzing {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 20px;
}

.analyzing-circle {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.analyzing-circle svg {
    width: 100%;
    height: 100%;
}

.analyzing-progress {
    animation: rotateAnalyzing 2s linear infinite;
    transform-origin: center;
}

@keyframes rotateAnalyzing {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.analyzing-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.analyzing-inner .dashicons {
    font-size: 32px;
    color: #667eea;
}

.analyzing-text {
    flex: 1;
    color: white;
}

.analyzing-text h3 {
    color: white;
    margin: 0 0 20px 0;
    font-size: 20px;
}

.analyzing-steps {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.analyzing-steps .step {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255,255,255,0.5);
    transition: all 0.3s ease;
}

.analyzing-steps .step.active {
    color: white;
}

.analyzing-steps .step .dashicons {
    font-size: 16px;
}

.analyzing-steps .step.active .dashicons-yes {
    color: #4caf50;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hmg-ai-seo-not-analyzed,
    .seo-analyzing {
        flex-direction: column;
        text-align: center;
    }
    
    .seo-placeholder-info,
    .analyzing-text {
        text-align: center;
    }
    
    .seo-benefits,
    .analyzing-steps {
        display: inline-block;
        text-align: left;
    }
}

.hmg-ai-seo-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.hmg-ai-seo-actions .button .dashicons {
    margin-right: 5px;
    margin-top: 3px;
}

.hmg-ai-seo-field {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.hmg-ai-seo-field label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.hmg-ai-char-count {
    font-size: 12px;
    color: #666;
    font-weight: normal;
}

.hmg-ai-seo-field textarea,
.hmg-ai-seo-field input[type="text"] {
    width: 100%;
    margin-bottom: 10px;
}

.hmg-ai-keywords-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    min-height: 40px;
    align-items: center;
}

.hmg-ai-keyword-tag {
    background: #667eea;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.hmg-ai-keyword-tag button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
    font-size: 18px;
    line-height: 1;
    opacity: 0.7;
}

.hmg-ai-keyword-tag button:hover {
    opacity: 1;
}

.hmg-ai-keyword-input {
    border: none !important;
    outline: none !important;
    flex: 1;
    min-width: 100px;
    background: transparent !important;
    box-shadow: none !important;
}

.hmg-ai-seo-suggestions {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    margin: 20px 0;
}

.hmg-ai-seo-suggestions h4 {
    margin-top: 0;
    color: #856404;
}

.hmg-ai-seo-suggestions ul {
    margin: 10px 0 0;
    padding-left: 20px;
}

.hmg-ai-seo-suggestions li {
    margin: 8px 0;
    list-style: none;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.suggestion-high .dashicons-warning {
    color: #dc3545;
}

.suggestion-medium .dashicons-info {
    color: #ffc107;
}

.suggestion-low .dashicons-yes-alt {
    color: #28a745;
}

.hmg-ai-seo-internal-links {
    background: #e7f3ff;
    border-left: 4px solid #2196F3;
    padding: 15px;
    margin: 20px 0;
}

.hmg-ai-seo-internal-links h4 {
    margin-top: 0;
    color: #0c5ea5;
}

.hmg-ai-seo-internal-links ul {
    margin: 10px 0 0;
    padding: 0;
}

.hmg-ai-seo-internal-links li {
    list-style: none;
    padding: 8px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.hmg-ai-seo-internal-links li:last-child {
    border-bottom: none;
}

.link-keyword {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.hmg-ai-seo-schema {
    background: #f0f0f0;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
}

.hmg-ai-seo-schema label {
    display: flex;
    align-items: center;
    gap: 10px;
}

.hmg-ai-seo-schema .description {
    margin: 10px 0 0 28px;
    color: #666;
    font-size: 13px;
}
</style>
