<?php
/**
 * CTA Settings admin page display - Exact copy of original UI
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/admin/partials
 */

// Get the active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Get CTA manager instance
$cta_manager = new HMG_AI_CTA_Manager('hmg-ai-blog-enhancer', HMG_AI_BLOG_ENHANCER_VERSION);
$templates = $cta_manager->get_templates();

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['hmg_ai_cta_nonce'], 'hmg_ai_cta_settings')) {
    if ($active_tab === 'general') {
        // Save global settings
        $global_settings = [
            'box_color' => sanitize_text_field($_POST['box_color'] ?? ''),
            'box_bg' => sanitize_text_field($_POST['box_bg'] ?? ''),
            'box_border_color' => sanitize_text_field($_POST['box_border_color'] ?? ''),
            'box_border_width' => sanitize_text_field($_POST['box_border_width'] ?? ''),
            'box_border_rad' => sanitize_text_field($_POST['box_border_rad'] ?? ''),
            'box_pad' => sanitize_text_field($_POST['box_pad'] ?? ''),
            'custom_css' => wp_strip_all_tags($_POST['custom_css'] ?? '')
        ];
        $cta_manager->save_global_settings($global_settings);
        echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'hmg-ai-blog-enhancer') . '</p></div>';
    } else {
        // Save template settings
        $template_settings = [
            'active' => isset($_POST[$active_tab . '_active']) && $_POST[$active_tab . '_active'] === 'on',
            'title' => sanitize_text_field($_POST[$active_tab . '_title'] ?? ''),
            'content' => wp_kses_post($_POST[$active_tab . '_content'] ?? ''),
            'button' => sanitize_text_field($_POST[$active_tab . '_button'] ?? ''),
            'url' => esc_url_raw($_POST[$active_tab . '_url'] ?? ''),
            'target' => isset($_POST[$active_tab . '_target']) && $_POST[$active_tab . '_target'] === 'on',
            'button_class' => sanitize_text_field($_POST[$active_tab . '_button_class'] ?? ''),
            'img' => esc_url_raw($_POST[$active_tab . '_img'] ?? ''),
            'img_align' => sanitize_text_field($_POST[$active_tab . '_img_align'] ?? 'wpt-alignleft'),
            'override_defaults' => isset($_POST[$active_tab . '_override_defaults']) && $_POST[$active_tab . '_override_defaults'] === 'on',
            'box_color' => sanitize_text_field($_POST[$active_tab . '_box_color'] ?? ''),
            'box_bg' => sanitize_text_field($_POST[$active_tab . '_box_bg'] ?? ''),
            'box_border_color' => sanitize_text_field($_POST[$active_tab . '_box_border_color'] ?? ''),
            'box_border_width' => sanitize_text_field($_POST[$active_tab . '_box_border_width'] ?? ''),
            'box_border_rad' => sanitize_text_field($_POST[$active_tab . '_box_border_rad'] ?? ''),
            'box_pad' => sanitize_text_field($_POST[$active_tab . '_box_pad'] ?? ''),
            'custom_css' => wp_strip_all_tags($_POST[$active_tab . '_custom_css'] ?? '')
        ];
        $cta_manager->save_template_settings($active_tab, $template_settings);
        echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'hmg-ai-blog-enhancer') . '</p></div>';
    }
}

// WPDK-style CSS
?>
<style>
.wpdk-form { margin-top: 20px; }
.wpdk-form-section { margin-bottom: 30px; background: #fff; border: 1px solid #e5e5e5; }
.wpdk-form-section h3 { margin: 0; padding: 12px 15px; background: #f5f5f5; border-bottom: 1px solid #e5e5e5; font-size: 14px; font-weight: 600; }
.wpdk-form-fieldset { padding: 15px; }
.wpdk-form-row { margin-bottom: 20px; display: table; width: 100%; }
.wpdk-form-label { display: table-cell; width: 200px; padding: 8px 10px 0 0; text-align: right; vertical-align: top; font-weight: 600; }
.wpdk-form-field { display: table-cell; }
.wpdk-form-description { display: block; margin-top: 5px; color: #666; font-size: 13px; font-style: italic; }
.wpdk-form input[type="text"]:not(.wp-color-picker), .wpdk-form input[type="url"], .wpdk-form textarea, .wpdk-form select { width: 100%; max-width: 400px; }
.wpdk-form textarea { min-height: 100px; }

/* WordPress Color Picker Integration */
.wp-picker-container { 
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
}
.wp-picker-container .wp-color-result { 
    margin: 0 !important; 
    height: 32px !important;
    min-width: 70px !important;
    border: 1px solid #7e8993 !important;
    border-radius: 4px !important;
}
.wp-picker-container .wp-color-result-text { 
    display: block !important;
    line-height: 30px !important;
}
.wp-picker-container .wp-picker-input-wrap {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
}
.wp-picker-container .wp-picker-input-wrap input[type="text"] { 
    width: 80px !important;
    height: 32px !important;
    padding: 0 8px !important;
    border: 1px solid #8c8f94 !important;
    border-radius: 4px !important;
    margin: 0 !important;
}
.wp-picker-container button.wp-picker-default,
.wp-picker-container button.wp-picker-clear { 
    height: 32px !important;
    padding: 0 10px !important;
    margin: 0 !important;
    border: 1px solid #8c8f94 !important;
    border-radius: 4px !important;
    background: #f0f0f1 !important;
    color: #2c3338 !important;
    cursor: pointer !important;
}
.wp-picker-container button:hover {
    background: #e5e5e5 !important;
}
.wp-picker-holder { 
    position: absolute !important; 
    z-index: 9999 !important; 
    margin-top: 5px !important;
}
.iris-picker { 
    position: absolute !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
}

.wpdk-switch-button { position: relative; display: inline-block; width: 60px; height: 28px; }
.wpdk-switch-button input { opacity: 0; width: 0; height: 0; }
.wpdk-switch-button-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 28px; }
.wpdk-switch-button-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .wpdk-switch-button-slider { background-color: #2196F3; }
input:checked + .wpdk-switch-button-slider:before { transform: translateX(32px); }
.wpdk-file-media { display: flex; align-items: center; gap: 10px; }
.wpdk-file-media input { flex: 1; }
.wpdk-file-media button { flex-shrink: 0; }
.wpdk-color-picker-field { max-width: 100px !important; }
</style>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Tabs Navigation -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=hmg-ai-blog-enhancer-cta&tab=general" 
           class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'hmg-ai-blog-enhancer'); ?>
        </a>
        <?php foreach ($templates as $key => $label) : ?>
            <a href="?page=hmg-ai-blog-enhancer-cta&tab=<?php echo esc_attr($key); ?>" 
               class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <form method="post" action="" class="wpdk-form">
        <?php wp_nonce_field('hmg_ai_cta_settings', 'hmg_ai_cta_nonce'); ?>
        
        <?php if ($active_tab === 'general') : 
            $settings = $cta_manager->get_global_settings();
        ?>
            <!-- Default Branding Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Default Branding', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Text Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_color" class="wpdk-color-picker-field" 
                                   data-default-color="#444444"
                                   value="<?php echo esc_attr($settings['box_color']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Background Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_bg" class="wpdk-color-picker-field" 
                                   data-default-color="#eeeeee"
                                   value="<?php echo esc_attr($settings['box_bg']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_border_color" class="wpdk-color-picker-field" 
                                   data-default-color="#cccccc"
                                   value="<?php echo esc_attr($settings['box_border_color']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Width', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_border_width" placeholder="1px" 
                                   title="Enter the border width. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_border_width']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Radius', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_border_rad" placeholder="3px"
                                   title="Enter the border radius. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_border_rad']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Padding', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="box_pad" placeholder="1em"
                                   title="Enter the padding. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_pad']); ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Styles Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Custom Styles', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Custom CSS', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <textarea name="custom_css" rows="8"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        <?php else : 
            // Template settings
            $settings = $cta_manager->get_template_settings($active_tab);
            $template_name = $templates[$active_tab];
        ?>
            <!-- CTA Active Section -->
            <div class="wpdk-form-section">
                <h3><?php echo esc_html($template_name); ?> CTA</h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Active', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <label class="wpdk-switch-button">
                                <input type="checkbox" name="<?php echo $active_tab; ?>_active" value="on" 
                                       <?php checked($settings['active']); ?> />
                                <span class="wpdk-switch-button-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Content', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Title', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_title" 
                                   value="<?php echo esc_attr($settings['title']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Content', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <textarea name="<?php echo $active_tab; ?>_content"><?php echo esc_textarea($settings['content']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Button Text', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_button" 
                                   value="<?php echo esc_attr($settings['button']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Button URL', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="url" name="<?php echo $active_tab; ?>_url" 
                                   value="<?php echo esc_url($settings['url']); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Open in new window', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <label class="wpdk-switch-button">
                                <input type="checkbox" name="<?php echo $active_tab; ?>_target" value="on" 
                                       <?php checked($settings['target']); ?> />
                                <span class="wpdk-switch-button-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Button Class', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_button_class" 
                                   placeholder="ex: btn btn-primary"
                                   value="<?php echo esc_attr($settings['button_class']); ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Image', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Image', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <div class="wpdk-file-media">
                                <input type="url" name="<?php echo $active_tab; ?>_img" 
                                       id="<?php echo $active_tab; ?>_img"
                                       value="<?php echo esc_url($settings['img']); ?>" />
                                <button type="button" class="button wpdk-media-upload" 
                                        data-target="<?php echo $active_tab; ?>_img">
                                    <?php _e('Upload', 'hmg-ai-blog-enhancer'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Image Alignment', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <select name="<?php echo $active_tab; ?>_img_align">
                                <option value="wpt-alignleft" <?php selected($settings['img_align'], 'wpt-alignleft'); ?>><?php _e('Left', 'hmg-ai-blog-enhancer'); ?></option>
                                <option value="wpt-alignright" <?php selected($settings['img_align'], 'wpt-alignright'); ?>><?php _e('Right', 'hmg-ai-blog-enhancer'); ?></option>
                                <option value="wpt-aligntop" <?php selected($settings['img_align'], 'wpt-aligntop'); ?>><?php _e('Top', 'hmg-ai-blog-enhancer'); ?></option>
                                <option value="wpt-alignbottom" <?php selected($settings['img_align'], 'wpt-alignbottom'); ?>><?php _e('Bottom', 'hmg-ai-blog-enhancer'); ?></option>
                                <option value="wpt-background" <?php selected($settings['img_align'], 'wpt-background'); ?>><?php _e('Background', 'hmg-ai-blog-enhancer'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Branding', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Override Defaults', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <label class="wpdk-switch-button">
                                <input type="checkbox" name="<?php echo $active_tab; ?>_override_defaults" value="on" 
                                       <?php checked($settings['override_defaults']); ?> />
                                <span class="wpdk-switch-button-slider"></span>
                            </label>
                            <span class="wpdk-form-description"><?php _e('Turn this on to override the defaults set in the General tab.', 'hmg-ai-blog-enhancer'); ?></span>
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Text Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_color" 
                                   class="wpdk-color-picker-field"
                                   data-default-color="#444444"
                                   value="<?php echo esc_attr($settings['box_color'] ?? ''); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Background Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_bg" 
                                   class="wpdk-color-picker-field"
                                   data-default-color="#eeeeee"
                                   value="<?php echo esc_attr($settings['box_bg'] ?? ''); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_border_color" 
                                   class="wpdk-color-picker-field"
                                   data-default-color="#cccccc"
                                   value="<?php echo esc_attr($settings['box_border_color'] ?? ''); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Width', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_border_width" 
                                   title="Enter the border width. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_border_width'] ?? ''); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Border Radius', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_border_rad" 
                                   title="Enter the border radius. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_border_rad'] ?? ''); ?>" />
                        </div>
                    </div>
                    
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Padding', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <input type="text" name="<?php echo $active_tab; ?>_box_pad" 
                                   title="Enter the padding. Order: top, right, bottom, left"
                                   value="<?php echo esc_attr($settings['box_pad'] ?? ''); ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Styles Section -->
            <div class="wpdk-form-section">
                <h3><?php _e('Custom Styles', 'hmg-ai-blog-enhancer'); ?></h3>
                <div class="wpdk-form-fieldset">
                    <div class="wpdk-form-row">
                        <label class="wpdk-form-label"><?php _e('Custom CSS', 'hmg-ai-blog-enhancer'); ?></label>
                        <div class="wpdk-form-field">
                            <textarea name="<?php echo $active_tab; ?>_custom_css" rows="8"
                                      title="Add custom CSS to this CTA."><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize color pickers on page load
    function initializeColorPickers() {
        $('.wpdk-color-picker-field').each(function() {
            if (!$(this).hasClass('wp-color-picker')) {
                $(this).wpColorPicker();
            }
        });
    }
    
    // Initialize immediately
    initializeColorPickers();
    
    // Re-initialize when switching tabs (in case of dynamic content)
    $('.nav-tab').on('click', function() {
        setTimeout(initializeColorPickers, 100);
    });
    
    // Media uploader
    $('.wpdk-media-upload').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetField = $('#' + button.data('target'));
        
        var mediaUploader = wp.media({
            title: '<?php _e('Choose Image', 'hmg-ai-blog-enhancer'); ?>',
            button: {
                text: '<?php _e('Use this image', 'hmg-ai-blog-enhancer'); ?>'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
        });

        mediaUploader.open();
    });
});
</script>