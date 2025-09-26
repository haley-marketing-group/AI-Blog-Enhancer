<?php
/**
 * Template for displaying AI-generated audio player
 *
 * Available variables:
 * - $audio_data: Array with 'url', 'title', 'duration', 'size'
 * - $style: Display style (player, compact, minimal, card)
 * - $post_id: The post ID
 *
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (empty($audio_data['url'])) {
    return;
}
?>

<div class="hmg-ai-audio hmg-ai-audio-<?php echo esc_attr($style); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php if ($style === 'card'): ?>
        <div class="hmg-ai-audio-card">
            <div class="hmg-ai-audio-card-header">
                <span class="hmg-ai-audio-icon">🎧</span>
                <h4 class="hmg-ai-audio-title"><?php _e('Listen to This Article', 'hmg-ai-blog-enhancer'); ?></h4>
            </div>
            <div class="hmg-ai-audio-card-body">
                <audio controls class="hmg-ai-audio-element">
                    <source src="<?php echo esc_url($audio_data['url']); ?>" type="audio/mpeg">
                    <?php _e('Your browser does not support the audio element.', 'hmg-ai-blog-enhancer'); ?>
                </audio>
                <?php if (!empty($audio_data['duration'])): ?>
                    <div class="hmg-ai-audio-meta">
                        <span class="hmg-ai-audio-duration">
                            <?php echo esc_html($audio_data['duration']); ?>
                        </span>
                        <?php if (!empty($audio_data['size'])): ?>
                            <span class="hmg-ai-audio-size">
                                <?php echo esc_html(size_format($audio_data['size'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hmg-ai-audio-card-footer">
                <a href="<?php echo esc_url($audio_data['url']); ?>" 
                   download="<?php echo esc_attr(sanitize_file_name($audio_data['title']) . '.mp3'); ?>"
                   class="hmg-ai-audio-download">
                    <?php _e('Download Audio', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </div>
        </div>
    
    <?php elseif ($style === 'compact'): ?>
        <div class="hmg-ai-audio-compact">
            <span class="hmg-ai-audio-icon">🎧</span>
            <audio controls class="hmg-ai-audio-element">
                <source src="<?php echo esc_url($audio_data['url']); ?>" type="audio/mpeg">
                <?php _e('Your browser does not support the audio element.', 'hmg-ai-blog-enhancer'); ?>
            </audio>
            <a href="<?php echo esc_url($audio_data['url']); ?>" 
               download class="hmg-ai-audio-download-icon" 
               title="<?php _e('Download', 'hmg-ai-blog-enhancer'); ?>">
                ⬇
            </a>
        </div>
    
    <?php elseif ($style === 'minimal'): ?>
        <div class="hmg-ai-audio-minimal">
            <button class="hmg-ai-audio-play-button" data-audio-url="<?php echo esc_url($audio_data['url']); ?>">
                <span class="hmg-ai-play-icon">▶</span>
                <span class="hmg-ai-pause-icon" style="display:none;">⏸</span>
                <?php _e('Play Audio Version', 'hmg-ai-blog-enhancer'); ?>
            </button>
            <div class="hmg-ai-audio-progress" style="display:none;">
                <div class="hmg-ai-audio-progress-bar"></div>
            </div>
        </div>
    
    <?php else: // player (default) ?>
        <div class="hmg-ai-audio-player">
            <h4 class="hmg-ai-audio-title">
                <span class="hmg-ai-audio-icon">🎧</span>
                <?php _e('Audio Version', 'hmg-ai-blog-enhancer'); ?>
            </h4>
            <audio controls class="hmg-ai-audio-element" preload="metadata">
                <source src="<?php echo esc_url($audio_data['url']); ?>" type="audio/mpeg">
                <?php _e('Your browser does not support the audio element.', 'hmg-ai-blog-enhancer'); ?>
            </audio>
            <div class="hmg-ai-audio-controls">
                <div class="hmg-ai-audio-speed-control">
                    <label class="hmg-ai-audio-speed-label">Speed:</label>
                    <div class="hmg-ai-audio-speed-buttons">
                        <button class="hmg-ai-audio-speed active" data-speed="1">1×</button>
                        <button class="hmg-ai-audio-speed" data-speed="1.25">1.25×</button>
                        <button class="hmg-ai-audio-speed" data-speed="1.5">1.5×</button>
                        <button class="hmg-ai-audio-speed" data-speed="2">2×</button>
                    </div>
                </div>
                <a href="<?php echo esc_url($audio_data['url']); ?>" 
                   download="<?php echo esc_attr(sanitize_file_name($audio_data['title']) . '.mp3'); ?>"
                   class="hmg-ai-audio-download">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Download', 'hmg-ai-blog-enhancer'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($style === 'minimal'): ?>
<script>
(function() {
    const button = document.querySelector('.hmg-ai-audio-minimal .hmg-ai-audio-play-button');
    if (button) {
        let audio = null;
        button.addEventListener('click', function() {
            const url = this.dataset.audioUrl;
            const playIcon = this.querySelector('.hmg-ai-play-icon');
            const pauseIcon = this.querySelector('.hmg-ai-pause-icon');
            
            if (!audio) {
                audio = new Audio(url);
                audio.addEventListener('ended', function() {
                    playIcon.style.display = 'inline';
                    pauseIcon.style.display = 'none';
                });
            }
            
            if (audio.paused) {
                audio.play();
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'inline';
            } else {
                audio.pause();
                playIcon.style.display = 'inline';
                pauseIcon.style.display = 'none';
            }
        });
    }
})();
</script>
<?php elseif ($style === 'player'): ?>
<script>
(function() {
    const players = document.querySelectorAll('.hmg-ai-audio-player');
    players.forEach(player => {
        const audio = player.querySelector('.hmg-ai-audio-element');
        const speedButtons = player.querySelectorAll('.hmg-ai-audio-speed');
        
        // Set initial active state
        speedButtons[0]?.classList.add('active');
        
        speedButtons.forEach(button => {
            button.addEventListener('click', function() {
                const speed = parseFloat(this.dataset.speed);
                audio.playbackRate = speed;
                
                speedButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
})();
</script>
<?php endif; ?>