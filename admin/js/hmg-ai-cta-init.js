/**
 * CTA Settings Color Picker Initialization
 * Ensures color pickers work on all pages
 */
(function($) {
    'use strict';
    
    function initColorPickers() {
        // Check if wpColorPicker is available
        if (!$.fn.wpColorPicker) {
            console.log('WordPress color picker not available, loading fallback...');
            return false;
        }
        
        // Initialize all color picker fields
        $('.wpdk-color-picker-field').each(function() {
            var $field = $(this);
            
            // Skip if already initialized
            if ($field.hasClass('wp-color-picker')) {
                return;
            }
            
            // Get default color from data attribute or use fallback
            var defaultColor = $field.data('default-color') || $field.attr('placeholder') || '#ffffff';
            
            // Initialize with WordPress color picker
            $field.wpColorPicker({
                defaultColor: defaultColor,
                change: function(event, ui) {
                    // Update the field value
                    $(event.target).val(ui.color.toString()).trigger('input');
                },
                clear: function() {
                    // Reset to default or empty
                    var $input = $(this).closest('.wp-picker-container').find('.wp-color-picker');
                    $input.val('').trigger('input');
                },
                hide: true,
                palettes: [
                    '#444444',
                    '#eeeeee', 
                    '#cccccc',
                    '#333333',
                    '#f7f7f7',
                    '#dddddd',
                    '#0073e6',
                    '#ffffff'
                ]
            });
        });
        
        console.log('Color pickers initialized:', $('.wpdk-color-picker-field').length);
        return true;
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        initColorPickers();
    });
    
    // Re-initialize when tabs are clicked
    $(document).on('click', '.nav-tab', function() {
        setTimeout(initColorPickers, 100);
    });
    
    // Re-initialize after AJAX calls
    $(document).ajaxComplete(function() {
        setTimeout(initColorPickers, 100);
    });
    
})(jQuery);
