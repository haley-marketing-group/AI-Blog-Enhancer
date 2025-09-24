<?php
/**
 * Template for displaying AI-generated table of contents
 *
 * Available variables:
 * - $toc_data: Array of TOC items or array with 'html' key
 * - $style: Display style (numbered, horizontal, minimal, sidebar)
 * - $post_id: The post ID
 *
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (empty($toc_data)) {
    return;
}

// Check if we have HTML content or structured data
$is_html = isset($toc_data['html']);
?>

<div class="hmg-ai-toc hmg-ai-toc-<?php echo esc_attr($style); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <div class="hmg-ai-toc-header">
        <h3 class="hmg-ai-toc-title">
            <?php _e('Table of Contents', 'hmg-ai-blog-enhancer'); ?>
        </h3>
        <?php if ($style === 'sidebar'): ?>
            <button class="hmg-ai-toc-toggle" aria-label="<?php _e('Toggle Table of Contents', 'hmg-ai-blog-enhancer'); ?>">
                <span></span>
            </button>
        <?php endif; ?>
    </div>
    
    <div class="hmg-ai-toc-content">
        <?php if ($is_html): ?>
            <?php echo wp_kses_post($toc_data['html']); ?>
        
        <?php elseif ($style === 'horizontal'): ?>
            <nav class="hmg-ai-toc-horizontal-nav">
                <?php foreach ($toc_data as $item): ?>
                    <a href="<?php echo esc_attr($item['anchor']); ?>" 
                       class="hmg-ai-toc-link hmg-ai-toc-level-<?php echo esc_attr($item['level']); ?>">
                        <?php echo esc_html($item['title']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        
        <?php elseif ($style === 'minimal'): ?>
            <ul class="hmg-ai-toc-minimal-list">
                <?php foreach ($toc_data as $item): ?>
                    <li class="hmg-ai-toc-item hmg-ai-toc-level-<?php echo esc_attr($item['level']); ?>">
                        <a href="<?php echo esc_attr($item['anchor']); ?>" class="hmg-ai-toc-link">
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        
        <?php elseif ($style === 'sidebar'): ?>
            <div class="hmg-ai-toc-progress">
                <div class="hmg-ai-toc-progress-bar"></div>
            </div>
            <nav class="hmg-ai-toc-sidebar-nav">
                <?php 
                $current_level = 0;
                foreach ($toc_data as $item): 
                    $level = $item['level'];
                    
                    // Close previous lists if going to lower level
                    while ($current_level > $level) {
                        echo '</ul>';
                        $current_level--;
                    }
                    
                    // Open new lists if going to higher level
                    while ($current_level < $level) {
                        echo '<ul class="hmg-ai-toc-sublevel">';
                        $current_level++;
                    }
                ?>
                    <li class="hmg-ai-toc-item">
                        <a href="<?php echo esc_attr($item['anchor']); ?>" 
                           class="hmg-ai-toc-link"
                           data-anchor="<?php echo esc_attr($item['anchor']); ?>">
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    </li>
                <?php 
                endforeach; 
                
                // Close remaining lists
                while ($current_level > 0) {
                    echo '</ul>';
                    $current_level--;
                }
                ?>
            </nav>
        
        <?php else: // numbered (default) ?>
            <ol class="hmg-ai-toc-numbered-list">
                <?php 
                $current_level = 1;
                $first = true;
                
                foreach ($toc_data as $item): 
                    $level = $item['level'];
                    
                    if ($first) {
                        $current_level = $level;
                        $first = false;
                    }
                    
                    // Close previous lists if going to lower level
                    while ($current_level > $level) {
                        echo '</ol></li>';
                        $current_level--;
                    }
                    
                    // Open new lists if going to higher level
                    while ($current_level < $level) {
                        echo '<ol class="hmg-ai-toc-sublist">';
                        $current_level++;
                    }
                ?>
                    <li class="hmg-ai-toc-item">
                        <a href="<?php echo esc_attr($item['anchor']); ?>" class="hmg-ai-toc-link">
                            <?php echo esc_html($item['title']); ?>
                        </a>
                <?php 
                endforeach; 
                
                // Close remaining lists
                while ($current_level > 1) {
                    echo '</li></ol>';
                    $current_level--;
                }
                ?>
                </li>
            </ol>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    // Smooth scrolling for TOC links
    const tocLinks = document.querySelectorAll('.hmg-ai-toc-<?php echo esc_js($style); ?> .hmg-ai-toc-link');
    
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const offset = <?php echo ($style === 'sidebar') ? '100' : '80'; ?>;
                const targetPosition = targetElement.offsetTop - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL without jumping
                history.pushState(null, null, '#' + targetId);
            }
        });
    });
    
    <?php if ($style === 'sidebar'): ?>
    // Sidebar scroll spy and progress bar
    const progressBar = document.querySelector('.hmg-ai-toc-sidebar .hmg-ai-toc-progress-bar');
    const sidebarLinks = document.querySelectorAll('.hmg-ai-toc-sidebar .hmg-ai-toc-link');
    
    function updateProgress() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = (scrollTop / docHeight) * 100;
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        // Update active link
        sidebarLinks.forEach(link => {
            const targetId = link.getAttribute('data-anchor').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const rect = targetElement.getBoundingClientRect();
                if (rect.top <= 150 && rect.bottom >= 150) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    
    window.addEventListener('scroll', updateProgress);
    updateProgress();
    
    // Toggle button for mobile
    const toggleBtn = document.querySelector('.hmg-ai-toc-sidebar .hmg-ai-toc-toggle');
    const tocContent = document.querySelector('.hmg-ai-toc-sidebar .hmg-ai-toc-content');
    
    if (toggleBtn && tocContent) {
        toggleBtn.addEventListener('click', function() {
            tocContent.classList.toggle('expanded');
            this.classList.toggle('active');
        });
    }
    <?php endif; ?>
})();
</script>