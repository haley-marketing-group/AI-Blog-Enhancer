<?php
/**
 * Template for displaying AI-generated table of contents
 *
 * @link       https://haleymarketing.com
 * @since      1.0.0
 *
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/public/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Parse TOC data
$toc_data = is_string($toc) ? json_decode($toc, true) : $toc;
if (empty($toc_data) || !is_array($toc_data)) {
    return;
}

// Get style class
$style_class = 'hmg-ai-toc-' . sanitize_html_class($atts['style']);
$unique_id = 'hmg-ai-toc-' . uniqid();
?>

<div class="hmg-ai-toc <?php echo esc_attr($style_class); ?>" data-hmg-component="toc" id="<?php echo esc_attr($unique_id); ?>">
    <div class="hmg-ai-toc-header">
        <h3 class="hmg-ai-toc-title">
            <span class="hmg-ai-icon">📋</span>
            Table of Contents
        </h3>
        <div class="hmg-ai-branding">
            <span class="hmg-ai-powered-by">Powered by</span>
            <span class="hmg-ai-brand">Haley Marketing AI</span>
        </div>
    </div>
    
    <div class="hmg-ai-toc-content">
        <?php if ($atts['style'] === 'horizontal'): ?>
            <div class="hmg-ai-toc-horizontal">
                <div class="hmg-ai-toc-scroll-container">
                    <?php foreach ($toc_data as $index => $item): ?>
                        <a 
                            href="<?php echo esc_attr($item['anchor']); ?>" 
                            class="hmg-ai-toc-horizontal-item"
                            data-hmg-smooth-scroll
                            data-index="<?php echo esc_attr($index + 1); ?>"
                        >
                            <span class="hmg-ai-toc-number"><?php echo esc_html($index + 1); ?></span>
                            <span class="hmg-ai-toc-text"><?php echo wp_kses_post($item['title']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($atts['style'] === 'minimal'): ?>
            <div class="hmg-ai-toc-minimal">
                <?php foreach ($toc_data as $index => $item): ?>
                    <a 
                        href="<?php echo esc_attr($item['anchor']); ?>" 
                        class="hmg-ai-toc-minimal-item"
                        data-hmg-smooth-scroll
                        data-level="<?php echo esc_attr($item['level'] ?? 1); ?>"
                    >
                        <?php echo wp_kses_post($item['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php elseif ($atts['style'] === 'sidebar'): ?>
            <div class="hmg-ai-toc-sidebar">
                <div class="hmg-ai-toc-sticky">
                    <div class="hmg-ai-toc-progress">
                        <div class="hmg-ai-toc-progress-bar" id="<?php echo esc_attr($unique_id); ?>-progress"></div>
                    </div>
                    <nav class="hmg-ai-toc-nav" role="navigation" aria-label="Table of Contents">
                        <?php foreach ($toc_data as $index => $item): ?>
                            <a 
                                href="<?php echo esc_attr($item['anchor']); ?>" 
                                class="hmg-ai-toc-sidebar-item"
                                data-hmg-smooth-scroll
                                data-level="<?php echo esc_attr($item['level'] ?? 1); ?>"
                                data-target="<?php echo esc_attr(ltrim($item['anchor'], '#')); ?>"
                            >
                                <span class="hmg-ai-toc-dot"></span>
                                <span class="hmg-ai-toc-text"><?php echo wp_kses_post($item['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>
        <?php else: // numbered style (default) ?>
            <nav class="hmg-ai-toc-nav" role="navigation" aria-label="Table of Contents">
                <ol class="hmg-ai-toc-list">
                    <?php foreach ($toc_data as $index => $item): ?>
                        <li class="hmg-ai-toc-item" data-level="<?php echo esc_attr($item['level'] ?? 1); ?>">
                            <a 
                                href="<?php echo esc_attr($item['anchor']); ?>" 
                                class="hmg-ai-toc-link"
                                data-hmg-smooth-scroll
                                data-target="<?php echo esc_attr(ltrim($item['anchor'], '#')); ?>"
                            >
                                <span class="hmg-ai-toc-number"><?php echo esc_html($index + 1); ?>.</span>
                                <span class="hmg-ai-toc-text"><?php echo wp_kses_post($item['title']); ?></span>
                                <?php if (!empty($item['subsections'])): ?>
                                    <span class="hmg-ai-toc-subsection-count">
                                        (<?php echo count($item['subsections']); ?> sections)
                                    </span>
                                <?php endif; ?>
                            </a>
                            <?php if (!empty($item['subsections'])): ?>
                                <ol class="hmg-ai-toc-subsections">
                                    <?php foreach ($item['subsections'] as $sub_index => $subsection): ?>
                                        <li class="hmg-ai-toc-subsection">
                                            <a 
                                                href="<?php echo esc_attr($subsection['anchor']); ?>" 
                                                class="hmg-ai-toc-sublink"
                                                data-hmg-smooth-scroll
                                            >
                                                <?php echo wp_kses_post($subsection['title']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
    </div>
    
    <div class="hmg-ai-toc-footer">
        <div class="hmg-ai-meta">
            <span class="hmg-ai-count"><?php echo count($toc_data); ?> sections</span>
            <span class="hmg-ai-separator">•</span>
            <span class="hmg-ai-generated">AI-generated</span>
        </div>
    </div>
</div> 