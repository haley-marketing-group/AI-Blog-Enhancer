/**
 * CTA Color Picker Enhancement
 * 
 * Ensures all color fields have proper color picker functionality
 * @since 1.1.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Initialize color pickers for CTA settings
     */
    function initializeColorPickers() {
        // Check if WordPress color picker is available
        if ($.fn.wpColorPicker) {
            // Initialize all color fields with the WordPress color picker
            $('.color-field, .hmg-color-field').each(function() {
                var $this = $(this);
                
                // Skip if already initialized
                if ($this.hasClass('wp-color-picker')) {
                    return;
                }
                
                // Initialize with options
                $this.wpColorPicker({
                    // Default color when cleared
                    defaultColor: $this.attr('placeholder') || false,
                    
                    // Callback when color changes
                    change: function(event, ui) {
                        // Trigger change event for any custom handlers
                        $(event.target).trigger('colorchange', [ui.color.toString()]);
                    },
                    
                    // Callback when color is cleared
                    clear: function() {
                        // Reset to placeholder if available
                        var placeholder = $(this).attr('placeholder');
                        if (placeholder) {
                            $(this).val(placeholder);
                        }
                    },
                    
                    // Show alpha channel for transparency
                    palettes: ['#333333', '#f7f7f7', '#dddddd', '#0073e6', '#28a745', '#ffffff', '#000000', '#ff0000']
                });
            });
            
            console.log('✅ Color pickers initialized');
        } else {
            // Fallback: Use HTML5 color input type
            console.log('⚠️ WordPress color picker not available, using HTML5 color inputs');
            
            $('.color-field, .hmg-color-field').each(function() {
                var $this = $(this);
                var currentValue = $this.val();
                
                // Create a color input wrapper
                var $wrapper = $('<div class="hmg-color-input-wrapper" style="display: flex; align-items: center; gap: 10px;"></div>');
                $this.wrap($wrapper);
                
                // Create HTML5 color input
                var $colorInput = $('<input type="color" class="hmg-html5-color-picker" style="width: 50px; height: 35px; padding: 0; border: 1px solid #ddd;">');
                
                // Set initial value if it's a valid hex color
                if (currentValue && /^#[0-9A-F]{6}$/i.test(currentValue)) {
                    $colorInput.val(currentValue);
                } else if (currentValue) {
                    // Try to convert named colors to hex
                    var tempElem = $('<div>').css('color', currentValue);
                    var rgb = tempElem.css('color');
                    if (rgb) {
                        var hex = rgbToHex(rgb);
                        if (hex) {
                            $colorInput.val(hex);
                        }
                    }
                }
                
                // Insert color picker after text input
                $this.after($colorInput);
                
                // Sync color picker with text input
                $colorInput.on('input change', function() {
                    $this.val($(this).val()).trigger('change');
                });
                
                // Sync text input with color picker
                $this.on('input change', function() {
                    var val = $(this).val();
                    if (/^#[0-9A-F]{6}$/i.test(val)) {
                        $colorInput.val(val);
                    }
                });
            });
        }
    }
    
    /**
     * Convert RGB color to hex
     */
    function rgbToHex(rgb) {
        if (!rgb) return null;
        
        var match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!match) return null;
        
        function hex(x) {
            return ("0" + parseInt(x).toString(16)).slice(-2);
        }
        
        return "#" + hex(match[1]) + hex(match[2]) + hex(match[3]);
    }
    
    /**
     * Re-initialize color pickers when dynamic content is loaded
     */
    function reinitializeOnDynamicContent() {
        // When custom CTA settings are shown
        $('#hmg_ai_cta_type').on('change', function() {
            if ($(this).val() === 'custom') {
                setTimeout(initializeColorPickers, 100);
            }
        });
        
        // When override defaults is checked
        $('input[name="hmg_ai_cta_override_defaults"]').on('change', function() {
            if ($(this).is(':checked')) {
                setTimeout(initializeColorPickers, 100);
            }
        });
        
        // When switching tabs in settings
        $('.nav-tab').on('click', function() {
            setTimeout(initializeColorPickers, 100);
        });
    }
    
    // Initial initialization
    initializeColorPickers();
    
    // Setup dynamic content handlers
    reinitializeOnDynamicContent();
    
    // Also reinitialize on AJAX complete (for dynamic content)
    $(document).ajaxComplete(function() {
        setTimeout(initializeColorPickers, 100);
    });
});
