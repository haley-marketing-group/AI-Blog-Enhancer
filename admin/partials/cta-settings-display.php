<?php
/**
 * CTA Settings admin page display
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
            'box_color' => sanitize_hex_color($_POST['box_color'] ?? '') ?: sanitize_text_field($_POST['box_color'] ?? ''),
            'box_bg' => sanitize_hex_color($_POST['box_bg'] ?? '') ?: sanitize_text_field($_POST['box_bg'] ?? ''),
            'box_border_color' => sanitize_hex_color($_POST['box_border_color'] ?? '') ?: sanitize_text_field($_POST['box_border_color'] ?? ''),
            'box_border_width' => sanitize_text_field($_POST['box_border_width'] ?? ''),
            'box_border_rad' => sanitize_text_field($_POST['box_border_rad'] ?? ''),
            'box_pad' => sanitize_text_field($_POST['box_pad'] ?? '')
        ];
        $cta_manager->save_global_settings($global_settings);
        echo '<div class="notice notice-success"><p>' . __('Global settings saved.', 'hmg-ai-blog-enhancer') . '</p></div>';
    } else {
        // Save template settings
        $template_settings = [
            'active' => isset($_POST[$active_tab . '_active']),
            'title' => sanitize_text_field($_POST[$active_tab . '_title'] ?? ''),
            'content' => wp_kses_post($_POST[$active_tab . '_content'] ?? ''),
            'button' => sanitize_text_field($_POST[$active_tab . '_button'] ?? ''),
            'url' => esc_url_raw($_POST[$active_tab . '_url'] ?? ''),
            'target' => isset($_POST[$active_tab . '_target']),
            'button_class' => sanitize_text_field($_POST[$active_tab . '_button_class'] ?? 'hmg-cta-button hmg-cta-btn-default'),
            'img' => esc_url_raw($_POST[$active_tab . '_img'] ?? ''),
            'img_align' => sanitize_text_field($_POST[$active_tab . '_img_align'] ?? 'alignleft')
        ];
        $cta_manager->save_template_settings($active_tab, $template_settings);
        echo '<div class="notice notice-success"><p>' . sprintf(__('%s settings saved.', 'hmg-ai-blog-enhancer'), $templates[$active_tab]) . '</p></div>';
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <p><?php _e('Configure call-to-action templates that can be used on blog posts.', 'hmg-ai-blog-enhancer'); ?></p>

    <!-- Tabs Navigation -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=hmg-ai-blog-enhancer-cta&tab=general" 
           class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General Settings', 'hmg-ai-blog-enhancer'); ?>
        </a>
        <?php foreach ($templates as $key => $label) : ?>
            <a href="?page=hmg-ai-blog-enhancer-cta&tab=<?php echo esc_attr($key); ?>" 
               class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('hmg_ai_cta_settings', 'hmg_ai_cta_nonce'); ?>
        
        <?php if ($active_tab === 'general') : 
            $global_settings = $cta_manager->get_global_settings();
        ?>
            <h2><?php _e('Default CTA Styling', 'hmg-ai-blog-enhancer'); ?></h2>
            <p><?php _e('These settings will be used as defaults for all CTAs unless overridden.', 'hmg-ai-blog-enhancer'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="box_color"><?php _e('Text Color', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_color" id="box_color" 
                               value="<?php echo esc_attr($global_settings['box_color']); ?>" 
                               class="regular-text color-field" />
                        <p class="description"><?php _e('Default text color for CTA boxes.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="box_bg"><?php _e('Background Color', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_bg" id="box_bg" 
                               value="<?php echo esc_attr($global_settings['box_bg']); ?>" 
                               class="regular-text color-field" />
                        <p class="description"><?php _e('Default background color for CTA boxes.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="box_border_color"><?php _e('Border Color', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_border_color" id="box_border_color" 
                               value="<?php echo esc_attr($global_settings['box_border_color']); ?>" 
                               class="regular-text color-field" />
                        <p class="description"><?php _e('Default border color for CTA boxes.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="box_border_width"><?php _e('Border Width', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_border_width" id="box_border_width" 
                               value="<?php echo esc_attr($global_settings['box_border_width']); ?>" 
                               class="regular-text" placeholder="1px" />
                        <p class="description"><?php _e('CSS border width (e.g., 1px, 2px 0 2px 0).', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="box_border_rad"><?php _e('Border Radius', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_border_rad" id="box_border_rad" 
                               value="<?php echo esc_attr($global_settings['box_border_rad']); ?>" 
                               class="regular-text" placeholder="4px" />
                        <p class="description"><?php _e('CSS border radius for rounded corners.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="box_pad"><?php _e('Padding', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="box_pad" id="box_pad" 
                               value="<?php echo esc_attr($global_settings['box_pad']); ?>" 
                               class="regular-text" placeholder="20px" />
                        <p class="description"><?php _e('CSS padding inside CTA boxes.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
            </table>

        <?php else : 
            // Template settings
            $settings = $cta_manager->get_template_settings($active_tab);
        ?>
            <h2><?php echo esc_html($templates[$active_tab]); ?> <?php _e('Settings', 'hmg-ai-blog-enhancer'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php _e('Enable this CTA', 'hmg-ai-blog-enhancer'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo $active_tab; ?>_active" value="1" 
                                   <?php checked($settings['active']); ?> />
                            <?php _e('Make this CTA available for selection on blog posts', 'hmg-ai-blog-enhancer'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_title"><?php _e('Title', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="<?php echo $active_tab; ?>_title" 
                               id="<?php echo $active_tab; ?>_title" 
                               value="<?php echo esc_attr($settings['title']); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('The headline for this CTA.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_content"><?php _e('Content', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <?php 
                        wp_editor(
                            $settings['content'],
                            $active_tab . '_content',
                            [
                                'textarea_name' => $active_tab . '_content',
                                'media_buttons' => false,
                                'textarea_rows' => 5,
                                'teeny' => true
                            ]
                        );
                        ?>
                        <p class="description"><?php _e('The main content/description for this CTA.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_button"><?php _e('Button Text', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="<?php echo $active_tab; ?>_button" 
                               id="<?php echo $active_tab; ?>_button" 
                               value="<?php echo esc_attr($settings['button']); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_url"><?php _e('Button URL', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="<?php echo $active_tab; ?>_url" 
                               id="<?php echo $active_tab; ?>_url" 
                               value="<?php echo esc_url($settings['url']); ?>" 
                               class="large-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Button Target', 'hmg-ai-blog-enhancer'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo $active_tab; ?>_target" value="1" 
                                   <?php checked($settings['target']); ?> />
                            <?php _e('Open link in new window', 'hmg-ai-blog-enhancer'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_button_class"><?php _e('Button CSS Class', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="<?php echo $active_tab; ?>_button_class" 
                               id="<?php echo $active_tab; ?>_button_class" 
                               value="<?php echo esc_attr($settings['button_class']); ?>" 
                               class="regular-text" 
                               placeholder="hmg-cta-button hmg-cta-btn-default" />
                        <p class="description"><?php _e('CSS classes for button styling.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_img"><?php _e('Image URL', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="<?php echo $active_tab; ?>_img" 
                               id="<?php echo $active_tab; ?>_img" 
                               value="<?php echo esc_url($settings['img']); ?>" 
                               class="large-text" />
                        <button type="button" class="button hmg-cta-upload-image" 
                                data-target="<?php echo $active_tab; ?>_img">
                            <?php _e('Upload Image', 'hmg-ai-blog-enhancer'); ?>
                        </button>
                        <p class="description"><?php _e('Optional image to display in the CTA.', 'hmg-ai-blog-enhancer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $active_tab; ?>_img_align"><?php _e('Image Alignment', 'hmg-ai-blog-enhancer'); ?></label>
                    </th>
                    <td>
                        <select name="<?php echo $active_tab; ?>_img_align" id="<?php echo $active_tab; ?>_img_align">
                            <option value="alignleft" <?php selected($settings['img_align'], 'alignleft'); ?>><?php _e('Left', 'hmg-ai-blog-enhancer'); ?></option>
                            <option value="alignright" <?php selected($settings['img_align'], 'alignright'); ?>><?php _e('Right', 'hmg-ai-blog-enhancer'); ?></option>
                            <option value="aligntop" <?php selected($settings['img_align'], 'aligntop'); ?>><?php _e('Top', 'hmg-ai-blog-enhancer'); ?></option>
                            <option value="alignbottom" <?php selected($settings['img_align'], 'alignbottom'); ?>><?php _e('Bottom', 'hmg-ai-blog-enhancer'); ?></option>
                            <option value="background" <?php selected($settings['img_align'], 'background'); ?>><?php _e('Background', 'hmg-ai-blog-enhancer'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize WordPress color picker for all color fields
    if ($.fn.wpColorPicker) {
        $('.color-field').wpColorPicker();
    }
    
    // Media uploader
    $('.hmg-cta-upload-image').on('click', function(e) {
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
