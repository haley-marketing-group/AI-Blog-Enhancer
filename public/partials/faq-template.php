<?php
/**
 * Template for displaying AI-generated FAQ
 *
 * Available variables:
 * - $faq_data: Array of FAQ items with 'question' and 'answer' keys
 * - $style: Display style (accordion, list, cards)
 * - $post_id: The post ID
 *
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (empty($faq_data)) {
    return;
}

$unique_id = 'hmg-faq-' . $post_id . '-' . wp_rand(1000, 9999);
?>

<div class="hmg-ai-faq hmg-ai-faq-<?php echo esc_attr($style); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <h3 class="hmg-ai-faq-title">
        <span class="hmg-ai-icon">❓</span>
        <?php _e('Frequently Asked Questions', 'hmg-ai-blog-enhancer'); ?>
    </h3>
    
    <?php if ($style === 'accordion'): ?>
        <div class="hmg-ai-faq-accordion" id="<?php echo esc_attr($unique_id); ?>">
            <?php foreach ($faq_data as $index => $item): ?>
                <div class="hmg-ai-faq-accordion-item">
                    <button class="hmg-ai-faq-accordion-button" 
                            aria-expanded="false"
                            aria-controls="<?php echo esc_attr($unique_id . '-answer-' . $index); ?>">
                        <span class="hmg-ai-faq-question"><?php echo esc_html($item['question']); ?></span>
                        <span class="hmg-ai-faq-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>
                    <div class="hmg-ai-faq-accordion-content" 
                         id="<?php echo esc_attr($unique_id . '-answer-' . $index); ?>"
                         aria-hidden="true">
                        <div class="hmg-ai-faq-answer">
                            <?php echo wp_kses_post($item['answer']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    
    <?php elseif ($style === 'cards'): ?>
        <div class="hmg-ai-faq-cards">
            <?php foreach ($faq_data as $index => $item): ?>
                <div class="hmg-ai-faq-card">
                    <div class="hmg-ai-faq-card-header">
                        <span class="hmg-ai-faq-number">Q<?php echo ($index + 1); ?></span>
                        <h4 class="hmg-ai-faq-question"><?php echo esc_html($item['question']); ?></h4>
                    </div>
                    <div class="hmg-ai-faq-card-body">
                        <p class="hmg-ai-faq-answer"><?php echo wp_kses_post($item['answer']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    
    <?php else: // list style ?>
        <div class="hmg-ai-faq-list">
            <?php foreach ($faq_data as $item): ?>
                <div class="hmg-ai-faq-item">
                    <h4 class="hmg-ai-faq-question">
                        <span class="hmg-ai-faq-q">Q:</span>
                        <?php echo esc_html($item['question']); ?>
                    </h4>
                    <div class="hmg-ai-faq-answer">
                        <span class="hmg-ai-faq-a">A:</span>
                        <?php echo wp_kses_post($item['answer']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="hmg-ai-powered-by">
        <small><?php _e('AI-Powered Content by', 'hmg-ai-blog-enhancer'); ?> 
        <a href="https://haleymarketing.com" target="_blank" rel="noopener">Haley Marketing</a></small>
    </div>
</div>

<?php if ($style === 'accordion'): ?>
<script>
(function() {
    const accordion = document.getElementById('<?php echo esc_js($unique_id); ?>');
    if (accordion) {
        const buttons = accordion.querySelectorAll('.hmg-ai-faq-accordion-button');
        
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const expanded = this.getAttribute('aria-expanded') === 'true';
                const content = this.nextElementSibling;
                
                // Close all other items
                buttons.forEach(btn => {
                    if (btn !== this) {
                        btn.setAttribute('aria-expanded', 'false');
                        btn.nextElementSibling.setAttribute('aria-hidden', 'true');
                    }
                });
                
                // Toggle current item
                this.setAttribute('aria-expanded', !expanded);
                content.setAttribute('aria-hidden', expanded);
            });
        });
    }
})();
</script>
<?php endif; ?>