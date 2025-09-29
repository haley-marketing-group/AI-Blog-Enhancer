<?php
/**
 * CTA Metabox Class
 *
 * Handles the CTA metabox in the post editor
 *
 * @link       https://haleymarketing.com
 * @since      1.1.0
 * @package    HMG_AI_Blog_Enhancer
 * @subpackage HMG_AI_Blog_Enhancer/includes
 */

class HMG_AI_CTA_Metabox {

    /**
     * CTA Manager instance
     *
     * @since    1.1.0
     * @access   private
     * @var      HMG_AI_CTA_Manager    $cta_manager    CTA Manager instance.
     */
    private $cta_manager;

    /**
     * Initialize the class
     *
     * @since    1.1.0
     * @param    HMG_AI_CTA_Manager    $cta_manager    CTA Manager instance
     */
    public function __construct($cta_manager) {
        $this->cta_manager = $cta_manager;
    }

    /**
     * Register metabox
     *
     * @since    1.1.0
     */
    public function add_metabox() {
        add_meta_box(
            'hmg_ai_cta_manager',
            __('CTA Manager', 'hmg-ai-blog-enhancer'),
            [$this, 'render_metabox'],
            'post',
            'normal',
            'default'
        );
    }

    /**
     * Render metabox content
     *
     * @since    1.1.0
     * @param    WP_Post    $post    Current post object
     */
    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('hmg_ai_cta_metabox', 'hmg_ai_cta_metabox_nonce');

        // Get current values
        $current_cta_type = get_post_meta($post->ID, '_hmg_ai_cta_type', true) ?: 'none';
        $templates = $this->cta_manager->get_templates();
        
        // Get custom CTA values
        $custom_values = [
            'title' => get_post_meta($post->ID, '_hmg_ai_cta_title', true),
            'content' => get_post_meta($post->ID, '_hmg_ai_cta_content', true),
            'button_text' => get_post_meta($post->ID, '_hmg_ai_cta_button_text', true),
            'button_url' => get_post_meta($post->ID, '_hmg_ai_cta_button_url', true),
            'button_target' => get_post_meta($post->ID, '_hmg_ai_cta_button_target', true),
            'button_class' => get_post_meta($post->ID, '_hmg_ai_cta_button_class', true),
            'img' => get_post_meta($post->ID, '_hmg_ai_cta_img', true),
            'img_align' => get_post_meta($post->ID, '_hmg_ai_cta_img_align', true) ?: 'alignleft',
            'override_defaults' => get_post_meta($post->ID, '_hmg_ai_cta_override_defaults', true),
            'box_color' => get_post_meta($post->ID, '_hmg_ai_cta_box_color', true),
            'box_bg' => get_post_meta($post->ID, '_hmg_ai_cta_box_bg', true),
            'box_border_color' => get_post_meta($post->ID, '_hmg_ai_cta_box_border_color', true),
            'box_border_width' => get_post_meta($post->ID, '_hmg_ai_cta_box_border_width', true),
            'box_border_rad' => get_post_meta($post->ID, '_hmg_ai_cta_box_border_rad', true),
            'box_pad' => get_post_meta($post->ID, '_hmg_ai_cta_box_pad', true),
            'custom_css' => get_post_meta($post->ID, '_hmg_ai_cta_custom_css', true),
        ];
        ?>
        <style>
            .hmg-cta-metabox-section { margin: 20px 0; }
            .hmg-cta-metabox-section h4 { margin: 15px 0 10px; font-size: 14px; font-weight: 600; }
            .hmg-cta-field { margin-bottom: 15px; }
            .hmg-cta-field label { display: block; margin-bottom: 5px; font-weight: 500; }
            .hmg-cta-field input[type="text"],
            .hmg-cta-field input[type="url"],
            .hmg-cta-field textarea,
            .hmg-cta-field select { width: 100%; }
            .hmg-cta-field textarea { min-height: 100px; }
            .hmg-cta-field-help { color: #666; font-size: 12px; margin-top: 3px; }
            #hmg-custom-cta-settings { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
            .hmg-cta-checkbox { margin: 10px 0; }
        </style>

        <div class="hmg-cta-metabox">
            <p><?php _e('Select a call-to-action to display at the end of this blog post.', 'hmg-ai-blog-enhancer'); ?></p>
            
            <div class="hmg-cta-field">
                <label for="hmg_ai_cta_type"><?php _e('CTA Type', 'hmg-ai-blog-enhancer'); ?></label>
                <select name="hmg_ai_cta_type" id="hmg_ai_cta_type">
                    <option value="none" <?php selected($current_cta_type, 'none'); ?>><?php _e('None', 'hmg-ai-blog-enhancer'); ?></option>
                    <?php foreach ($templates as $key => $label) : 
                        $settings = $this->cta_manager->get_template_settings($key);
                        if ($settings['active']) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_cta_type, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endif;
                    endforeach; ?>
                    <option value="custom" <?php selected($current_cta_type, 'custom'); ?>><?php _e('Custom', 'hmg-ai-blog-enhancer'); ?></option>
                </select>
                <p class="hmg-cta-field-help">
                    <?php 
                    printf(
                        __('Configure default CTAs in the <a href="%s" target="_blank">plugin settings</a>.', 'hmg-ai-blog-enhancer'),
                        admin_url('admin.php?page=hmg-ai-blog-enhancer-cta')
                    ); 
                    ?>
                </p>
            </div>

            <div id="hmg-custom-cta-settings">
                <h4><?php _e('Content', 'hmg-ai-blog-enhancer'); ?></h4>
                
                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_title"><?php _e('Title', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" name="hmg_ai_cta_title" id="hmg_ai_cta_title" value="<?php echo esc_attr($custom_values['title']); ?>" />
                </div>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_content"><?php _e('Content', 'hmg-ai-blog-enhancer'); ?></label>
                    <textarea name="hmg_ai_cta_content" id="hmg_ai_cta_content"><?php echo esc_textarea($custom_values['content']); ?></textarea>
                </div>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_button_text"><?php _e('Button Text', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" name="hmg_ai_cta_button_text" id="hmg_ai_cta_button_text" value="<?php echo esc_attr($custom_values['button_text']); ?>" />
                </div>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_button_url"><?php _e('Button URL', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="url" name="hmg_ai_cta_button_url" id="hmg_ai_cta_button_url" value="<?php echo esc_url($custom_values['button_url']); ?>" />
                </div>

                <div class="hmg-cta-checkbox">
                    <label>
                        <input type="checkbox" name="hmg_ai_cta_button_target" value="1" <?php checked($custom_values['button_target'], '1'); ?> />
                        <?php _e('Open in new window', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                </div>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_button_class"><?php _e('Button CSS Class', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="text" name="hmg_ai_cta_button_class" id="hmg_ai_cta_button_class" 
                           value="<?php echo esc_attr($custom_values['button_class']); ?>" 
                           placeholder="hmg-cta-button hmg-cta-btn-default" />
                </div>

                <h4><?php _e('Image', 'hmg-ai-blog-enhancer'); ?></h4>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_img"><?php _e('Image URL', 'hmg-ai-blog-enhancer'); ?></label>
                    <input type="url" name="hmg_ai_cta_img" id="hmg_ai_cta_img" value="<?php echo esc_url($custom_values['img']); ?>" />
                    <button type="button" class="button" id="hmg-cta-upload-image"><?php _e('Upload Image', 'hmg-ai-blog-enhancer'); ?></button>
                </div>

                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_img_align"><?php _e('Image Alignment', 'hmg-ai-blog-enhancer'); ?></label>
                    <select name="hmg_ai_cta_img_align" id="hmg_ai_cta_img_align">
                        <option value="alignleft" <?php selected($custom_values['img_align'], 'alignleft'); ?>><?php _e('Left', 'hmg-ai-blog-enhancer'); ?></option>
                        <option value="alignright" <?php selected($custom_values['img_align'], 'alignright'); ?>><?php _e('Right', 'hmg-ai-blog-enhancer'); ?></option>
                        <option value="aligntop" <?php selected($custom_values['img_align'], 'aligntop'); ?>><?php _e('Top', 'hmg-ai-blog-enhancer'); ?></option>
                        <option value="alignbottom" <?php selected($custom_values['img_align'], 'alignbottom'); ?>><?php _e('Bottom', 'hmg-ai-blog-enhancer'); ?></option>
                        <option value="background" <?php selected($custom_values['img_align'], 'background'); ?>><?php _e('Background', 'hmg-ai-blog-enhancer'); ?></option>
                    </select>
                </div>

                <h4><?php _e('Styling (Optional)', 'hmg-ai-blog-enhancer'); ?></h4>

                <div class="hmg-cta-checkbox">
                    <label>
                        <input type="checkbox" name="hmg_ai_cta_override_defaults" value="1" <?php checked($custom_values['override_defaults'], '1'); ?> />
                        <?php _e('Override default styling', 'hmg-ai-blog-enhancer'); ?>
                    </label>
                </div>

                <div id="hmg-custom-styling" style="display: none;">
                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_color"><?php _e('Text Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_color" id="hmg_ai_cta_box_color" class="hmg-color-field" value="<?php echo esc_attr($custom_values['box_color']); ?>" placeholder="#333333" />
                    </div>

                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_bg"><?php _e('Background Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_bg" id="hmg_ai_cta_box_bg" class="hmg-color-field" value="<?php echo esc_attr($custom_values['box_bg']); ?>" placeholder="#f7f7f7" />
                    </div>

                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_border_color"><?php _e('Border Color', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_border_color" id="hmg_ai_cta_box_border_color" class="hmg-color-field" value="<?php echo esc_attr($custom_values['box_border_color']); ?>" placeholder="#dddddd" />
                    </div>

                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_border_width"><?php _e('Border Width', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_border_width" id="hmg_ai_cta_box_border_width" value="<?php echo esc_attr($custom_values['box_border_width']); ?>" placeholder="1px" />
                    </div>

                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_border_rad"><?php _e('Border Radius', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_border_rad" id="hmg_ai_cta_box_border_rad" value="<?php echo esc_attr($custom_values['box_border_rad']); ?>" placeholder="4px" />
                    </div>

                    <div class="hmg-cta-field">
                        <label for="hmg_ai_cta_box_pad"><?php _e('Padding', 'hmg-ai-blog-enhancer'); ?></label>
                        <input type="text" name="hmg_ai_cta_box_pad" id="hmg_ai_cta_box_pad" value="<?php echo esc_attr($custom_values['box_pad']); ?>" placeholder="20px" />
                    </div>
                </div>
            </div>

            <div class="hmg-cta-metabox-section">
                <h4><?php _e('Custom CSS', 'hmg-ai-blog-enhancer'); ?></h4>
                <div class="hmg-cta-field">
                    <label for="hmg_ai_cta_custom_css"><?php _e('Custom CSS for this CTA', 'hmg-ai-blog-enhancer'); ?></label>
                    <textarea name="hmg_ai_cta_custom_css" id="hmg_ai_cta_custom_css" rows="4"><?php echo esc_textarea($custom_values['custom_css']); ?></textarea>
                    <p class="hmg-cta-field-help"><?php _e('Add custom CSS rules for this CTA only.', 'hmg-ai-blog-enhancer'); ?></p>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Initialize color pickers for color fields
            if ($.fn.wpColorPicker) {
                $('.hmg-color-field').wpColorPicker();
            }

            // Toggle custom CTA settings
            $('#hmg_ai_cta_type').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#hmg-custom-cta-settings').slideDown();
                    // Reinitialize color pickers when showing
                    if ($.fn.wpColorPicker) {
                        $('.hmg-color-field').wpColorPicker();
                    }
                } else {
                    $('#hmg-custom-cta-settings').slideUp();
                }
            }).trigger('change');

            // Toggle custom styling fields
            $('input[name="hmg_ai_cta_override_defaults"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#hmg-custom-styling').slideDown();
                    // Reinitialize color pickers when showing
                    if ($.fn.wpColorPicker) {
                        $('.hmg-color-field').wpColorPicker();
                    }
                } else {
                    $('#hmg-custom-styling').slideUp();
                }
            }).trigger('change');

            // Media uploader
            $('#hmg-cta-upload-image').on('click', function(e) {
                e.preventDefault();
                
                var mediaUploader = wp.media({
                    title: '<?php _e('Choose Image', 'hmg-ai-blog-enhancer'); ?>',
                    button: {
                        text: '<?php _e('Use this image', 'hmg-ai-blog-enhancer'); ?>'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#hmg_ai_cta_img').val(attachment.url);
                });

                mediaUploader.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Save metabox data
     *
     * @since    1.1.0
     * @param    int    $post_id    Post ID
     */
    public function save_metabox($post_id) {
        // Check nonce
        if (!isset($_POST['hmg_ai_cta_metabox_nonce']) || 
            !wp_verify_nonce($_POST['hmg_ai_cta_metabox_nonce'], 'hmg_ai_cta_metabox')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save CTA type
        if (isset($_POST['hmg_ai_cta_type'])) {
            update_post_meta($post_id, '_hmg_ai_cta_type', sanitize_text_field($_POST['hmg_ai_cta_type']));
        }

        // Save custom CTA fields
        $fields = [
            '_hmg_ai_cta_title' => 'sanitize_text_field',
            '_hmg_ai_cta_content' => 'wp_kses_post',
            '_hmg_ai_cta_button_text' => 'sanitize_text_field',
            '_hmg_ai_cta_button_url' => 'esc_url_raw',
            '_hmg_ai_cta_button_class' => 'sanitize_text_field',
            '_hmg_ai_cta_img' => 'esc_url_raw',
            '_hmg_ai_cta_img_align' => 'sanitize_text_field',
            '_hmg_ai_cta_box_color' => function($value) { 
                return sanitize_hex_color($value) ?: sanitize_text_field($value); 
            },
            '_hmg_ai_cta_box_bg' => function($value) { 
                return sanitize_hex_color($value) ?: sanitize_text_field($value); 
            },
            '_hmg_ai_cta_box_border_color' => function($value) { 
                return sanitize_hex_color($value) ?: sanitize_text_field($value); 
            },
            '_hmg_ai_cta_box_border_width' => 'sanitize_text_field',
            '_hmg_ai_cta_box_border_rad' => 'sanitize_text_field',
            '_hmg_ai_cta_box_pad' => 'sanitize_text_field',
            '_hmg_ai_cta_custom_css' => 'wp_strip_all_tags'
        ];

        foreach ($fields as $meta_key => $sanitize_callback) {
            $field_name = str_replace('_hmg_ai_cta_', 'hmg_ai_cta_', $meta_key);
            if (isset($_POST[$field_name])) {
                if (is_callable($sanitize_callback)) {
                    $value = call_user_func($sanitize_callback, $_POST[$field_name]);
                } else {
                    $value = $_POST[$field_name];
                }
                update_post_meta($post_id, $meta_key, $value);
            }
        }

        // Handle checkboxes
        $checkbox_fields = ['_hmg_ai_cta_button_target', '_hmg_ai_cta_override_defaults'];
        foreach ($checkbox_fields as $meta_key) {
            $field_name = str_replace('_hmg_ai_cta_', 'hmg_ai_cta_', $meta_key);
            $value = isset($_POST[$field_name]) ? '1' : '';
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}
