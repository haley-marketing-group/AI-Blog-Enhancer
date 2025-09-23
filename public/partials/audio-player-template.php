<?php
/**
 * Template for displaying AI-generated audio player
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

// Validate audio URL
if (empty($audio_url) || !filter_var($audio_url, FILTER_VALIDATE_URL)) {
    return;
}

// Get style class
$style_class = 'hmg-ai-audio-' . sanitize_html_class($atts['style']);
$unique_id = 'hmg-ai-audio-' . uniqid();

// Get audio metadata if available
$audio_title = get_post_meta($atts['post_id'], '_hmg_ai_audio_title', true) ?: get_the_title($atts['post_id']);
$audio_duration = get_post_meta($atts['post_id'], '_hmg_ai_audio_duration', true);
$audio_size = get_post_meta($atts['post_id'], '_hmg_ai_audio_size', true);
?>

<div class="hmg-ai-audio <?php echo esc_attr($style_class); ?>" data-hmg-component="audio" id="<?php echo esc_attr($unique_id); ?>">
    <div class="hmg-ai-audio-header">
        <h3 class="hmg-ai-audio-title">
            <span class="hmg-ai-icon">🎧</span>
            Listen to This Article
        </h3>
        <div class="hmg-ai-branding">
            <span class="hmg-ai-powered-by">Powered by</span>
            <span class="hmg-ai-brand">Haley Marketing AI</span>
        </div>
    </div>
    
    <div class="hmg-ai-audio-content">
        <?php if ($atts['style'] === 'compact'): ?>
            <div class="hmg-ai-audio-compact">
                <audio 
                    class="hmg-ai-audio-element" 
                    controls 
                    preload="metadata"
                    aria-label="Audio version of <?php echo esc_attr($audio_title); ?>"
                >
                    <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                    <p>Your browser doesn't support HTML5 audio. <a href="<?php echo esc_url($audio_url); ?>">Download the audio file</a>.</p>
                </audio>
                <div class="hmg-ai-audio-info">
                    <div class="hmg-ai-audio-track-title"><?php echo esc_html($audio_title); ?></div>
                    <?php if ($audio_duration): ?>
                        <div class="hmg-ai-audio-duration"><?php echo esc_html($audio_duration); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($atts['style'] === 'minimal'): ?>
            <div class="hmg-ai-audio-minimal">
                <button class="hmg-ai-audio-play-button" data-hmg-audio-toggle aria-label="Play audio">
                    <svg class="hmg-ai-play-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg class="hmg-ai-pause-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>
                <div class="hmg-ai-audio-info">
                    <div class="hmg-ai-audio-track-title"><?php echo esc_html($audio_title); ?></div>
                    <div class="hmg-ai-audio-progress">
                        <div class="hmg-ai-audio-progress-bar" data-hmg-audio-progress></div>
                    </div>
                </div>
                <audio 
                    class="hmg-ai-audio-element" 
                    preload="metadata"
                    data-hmg-audio-source
                    aria-label="Audio version of <?php echo esc_attr($audio_title); ?>"
                >
                    <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                </audio>
            </div>
        <?php elseif ($atts['style'] === 'card'): ?>
            <div class="hmg-ai-audio-card">
                <div class="hmg-ai-audio-card-header">
                    <div class="hmg-ai-audio-artwork">
                        <div class="hmg-ai-audio-artwork-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="hmg-ai-audio-card-info">
                        <h4 class="hmg-ai-audio-card-title"><?php echo esc_html($audio_title); ?></h4>
                        <div class="hmg-ai-audio-card-meta">
                            <?php if ($audio_duration): ?>
                                <span class="hmg-ai-audio-duration"><?php echo esc_html($audio_duration); ?></span>
                            <?php endif; ?>
                            <?php if ($audio_size): ?>
                                <span class="hmg-ai-separator">•</span>
                                <span class="hmg-ai-audio-size"><?php echo esc_html($audio_size); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="hmg-ai-audio-card-controls">
                    <audio 
                        class="hmg-ai-audio-element" 
                        controls 
                        preload="metadata"
                        aria-label="Audio version of <?php echo esc_attr($audio_title); ?>"
                    >
                        <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                        <p>Your browser doesn't support HTML5 audio. <a href="<?php echo esc_url($audio_url); ?>">Download the audio file</a>.</p>
                    </audio>
                </div>
                <div class="hmg-ai-audio-card-actions">
                    <a 
                        href="<?php echo esc_url($audio_url); ?>" 
                        class="hmg-ai-audio-download" 
                        download
                        aria-label="Download audio file"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                        </svg>
                        Download
                    </a>
                    <button 
                        class="hmg-ai-audio-speed" 
                        data-hmg-audio-speed
                        aria-label="Playback speed"
                    >
                        1x
                    </button>
                </div>
            </div>
        <?php else: // player style (default) ?>
            <div class="hmg-ai-audio-player">
                <div class="hmg-ai-audio-player-header">
                    <div class="hmg-ai-audio-player-info">
                        <h4 class="hmg-ai-audio-player-title"><?php echo esc_html($audio_title); ?></h4>
                        <div class="hmg-ai-audio-player-meta">
                            <span class="hmg-ai-audio-type">Audio Article</span>
                            <?php if ($audio_duration): ?>
                                <span class="hmg-ai-separator">•</span>
                                <span class="hmg-ai-audio-duration"><?php echo esc_html($audio_duration); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hmg-ai-audio-player-actions">
                        <button 
                            class="hmg-ai-audio-speed-toggle" 
                            data-hmg-audio-speed
                            aria-label="Playback speed"
                        >
                            1x
                        </button>
                    </div>
                </div>
                <div class="hmg-ai-audio-player-controls">
                    <audio 
                        class="hmg-ai-audio-element" 
                        controls 
                        preload="metadata"
                        aria-label="Audio version of <?php echo esc_attr($audio_title); ?>"
                    >
                        <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                        <p>Your browser doesn't support HTML5 audio. <a href="<?php echo esc_url($audio_url); ?>">Download the audio file</a>.</p>
                    </audio>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="hmg-ai-audio-footer">
        <div class="hmg-ai-meta">
            <span class="hmg-ai-generated">AI-generated audio</span>
            <span class="hmg-ai-separator">•</span>
            <a href="<?php echo esc_url($audio_url); ?>" class="hmg-ai-download-link" download>
                Download MP3
            </a>
        </div>
    </div>
</div> 