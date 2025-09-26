/**
 * HMG AI Blog Enhancer Admin JavaScript
 *
 * Handles all admin-side functionality including meta box interactions,
 * AJAX content generation, and UI updates with professional polish.
 * 
 * @package HMG_AI_Blog_Enhancer
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main admin controller
     */
    const HMGAIAdmin = {
        modalTimeout: null, // Store timeout ID for auto-close
        
        /**
         * Initialize the admin interface
         */
        init: function() {
            this.createModal();
            this.bindEvents();
            this.initializeUsageBars();
            this.setupAutoSave();
        },
        
        /**
         * Create custom modal HTML
         */
        createModal: function() {
            const modalHTML = `
                <div id="hmg-ai-modal" class="hmg-ai-modal">
                    <div class="hmg-ai-modal-overlay"></div>
                    <div class="hmg-ai-modal-content">
                        <div class="hmg-ai-modal-header">
                            <h3 class="hmg-ai-modal-title"></h3>
                            <button class="hmg-ai-modal-close" aria-label="Close">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </div>
                        <div class="hmg-ai-modal-body">
                            <p class="hmg-ai-modal-message"></p>
                        </div>
                        <div class="hmg-ai-modal-footer">
                            <button class="button button-primary hmg-ai-modal-confirm">Confirm</button>
                            <button class="button hmg-ai-modal-cancel">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body if it doesn't exist
            if (!$('#hmg-ai-modal').length) {
                $('body').append(modalHTML);
            }
            
            // Bind modal events
            const $modal = $('#hmg-ai-modal');
            $modal.find('.hmg-ai-modal-cancel, .hmg-ai-modal-close, .hmg-ai-modal-overlay').on('click', () => {
                this.hideModal();
            });
            
            // ESC key to close
            $(document).on('keyup.hmgModal', (e) => {
                if (e.key === 'Escape') {
                    this.hideModal();
                }
            });
        },
        
        /**
         * Show custom modal
         */
        showModal: function(title, message, onConfirm, confirmText = 'Confirm', confirmClass = 'button-primary') {
            const $modal = $('#hmg-ai-modal');
            const $confirmBtn = $modal.find('.hmg-ai-modal-confirm');
            const $cancelBtn = $modal.find('.hmg-ai-modal-cancel');
            
            // Set content
            $modal.find('.hmg-ai-modal-title').text(title);
            $modal.find('.hmg-ai-modal-message').html(message);
            $confirmBtn.text(confirmText);
            $confirmBtn.attr('class', 'button ' + confirmClass);
            
            // Hide cancel for informational modals
            if (onConfirm === null) {
                $cancelBtn.hide();
            } else {
                $cancelBtn.show();
            }
            
            // Clear previous handlers
            $confirmBtn.off('click.modalConfirm');
            
            // Set new confirm handler
            if (onConfirm) {
                $confirmBtn.on('click.modalConfirm', () => {
                    this.hideModal();
                    onConfirm();
                });
            } else {
                $confirmBtn.on('click.modalConfirm', () => {
                    this.hideModal();
                });
            }
            
            // Show modal
            $modal.addClass('show').fadeIn(200);
        },
        
        /**
         * Hide custom modal
         */
        hideModal: function() {
            // Clear any auto-close timeout if it exists
            if (this.modalTimeout) {
                clearTimeout(this.modalTimeout);
                this.modalTimeout = null;
            }
            
            const $modal = $('#hmg-ai-modal');
            $modal.fadeOut(200, () => {
                $modal.removeClass('show hmg-ai-modal-success hmg-ai-modal-error');
            });
            $(document).off('keyup.hmgModal');
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {

            // Content generation buttons - use namespaced events to prevent double binding
            $(document).off('click.hmgai-takeaways').on('click.hmgai-takeaways', '.hmg-ai-generate-takeaways', this.generateTakeaways.bind(this));
            $(document).off('click.hmgai-faq').on('click.hmgai-faq', '.hmg-ai-generate-faq', this.generateFAQ.bind(this));
            $(document).off('click.hmgai-toc').on('click.hmgai-toc', '.hmg-ai-generate-toc', this.generateTOC.bind(this));
            $(document).off('click.hmgai-audio').on('click.hmgai-audio', '.hmg-ai-generate-audio', this.generateAudio.bind(this));

            // Edit content buttons - use namespaced events to prevent double binding
            $(document).off('click.hmgai-edit').on('click.hmgai-edit', '.hmg-ai-edit-content', this.editContent.bind(this));
            $(document).off('click.hmgai-save').on('click.hmgai-save', '.hmg-ai-save-content', this.saveContent.bind(this));
            $(document).off('click.hmgai-cancel').on('click.hmgai-cancel', '.hmg-ai-cancel-edit', this.cancelEdit.bind(this));
            $(document).off('click.hmgai-regen').on('click.hmgai-regen', '.hmg-ai-regenerate', this.regenerateContent.bind(this));
            $(document).off('click.hmgai-shortcode').on('click.hmgai-shortcode', '.hmg-ai-insert-shortcode', this.insertShortcode.bind(this));
            $(document).off('click.hmgai-delete').on('click.hmgai-delete', '.hmg-ai-delete-content', this.deleteContent.bind(this));

            // Test provider buttons
            $(document).on('click', '.hmg-ai-test-providers', this.testProviders.bind(this));
            
            // Settings page interactions
            $('input[name="spending_limit_type"]').on('change', this.toggleCustomLimit);
            
            // Refresh voices button (both meta box and settings page)

            $(document).off('click.hmgai-refresh-voices').on('click.hmgai-refresh-voices', '#hmg-ai-refresh-voices, #hmg-ai-refresh-settings-voices', this.refreshVoices.bind(this));
            
            // Verify buttons exist
            if ($('#hmg-ai-refresh-voices').length > 0) {

            }
            if ($('#hmg-ai-refresh-settings-voices').length > 0) {

            }
            
            // Refresh usage stats every 30 seconds if on editor page
            if ($('.hmg-ai-meta-box').length > 0) {
                setInterval(this.refreshUsageStats.bind(this), 30000);
            }
        },

        /**
         * Refresh Eleven Labs voices from API
         */
        refreshVoices: function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Check if hmg_ai_ajax is defined
            if (typeof hmg_ai_ajax === 'undefined') {

                alert('Configuration error. Please refresh the page.');
                return;
            }
            
            const $button = $(e.currentTarget);

            // Get the correct select element based on which page we're on
            let $select = $('#hmg-ai-audio-voice'); // Meta box select
            if ($select.length === 0) {
                $select = $('#tts_voice'); // Settings page select
            }
            
            if ($select.length === 0) {

                this.showNotice('error', 'Unable to find voice selection dropdown');
                return;
            }
            
            const $icon = $button.find('.dashicons');
            const currentValue = $select.val();
            
            // Add spinning animation
            $icon.addClass('hmg-ai-spinning');
            $button.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hmg_ai_refresh_voices',
                    nonce: hmg_ai_ajax.nonce
                },
                success: (response) => {

                    if (response.success && response.data && response.data.voices) {
                        // Clear current options
                        $select.empty();
                        
                        // Separate voices by category
                        const premadeVoices = [];
                        const clonedVoices = [];
                        
                        Object.entries(response.data.voices).forEach(([voiceId, voiceData]) => {
                            const category = voiceData.category || 'premade';
                            const voice = {
                                id: voiceId,
                                name: voiceData.name,
                                description: voiceData.description || ''
                            };
                            
                            if (category === 'cloned') {
                                clonedVoices.push(voice);
                            } else {
                                premadeVoices.push(voice);
                            }
                        });
                        
                        // Add premade voices
                        if (premadeVoices.length > 0) {
                            const $optgroup = $('<optgroup label="Eleven Labs Voices"></optgroup>');
                            premadeVoices.forEach(voice => {
                                const text = voice.description ? 
                                    `${voice.name} - ${voice.description}` : 
                                    voice.name;
                                $optgroup.append(`<option value="${voice.id}">${text}</option>`);
                            });
                            $select.append($optgroup);
                        }
                        
                        // Add cloned voices if any
                        if (clonedVoices.length > 0) {
                            const $optgroup = $('<optgroup label="Custom/Cloned Voices"></optgroup>');
                            clonedVoices.forEach(voice => {
                                const text = voice.description ? 
                                    `${voice.name} - ${voice.description}` : 
                                    voice.name;
                                $optgroup.append(`<option value="${voice.id}">${text}</option>`);
                            });
                            $select.append($optgroup);
                        }
                        
                        // Restore previous selection if it exists
                        if (currentValue && $select.find(`option[value="${currentValue}"]`).length > 0) {
                            $select.val(currentValue);
                        }
                        
                        this.showNotice('success', response.data.message || 'Voices refreshed successfully!');
                    } else {

                        const errorMsg = (response.data && response.data.message) ? response.data.message : 'Failed to refresh voices';
                        this.showNotice('error', errorMsg);
                    }
                },
                error: () => {

                    this.showNotice('error', 'Failed to refresh voices. Please check your API key and network connection.');
                },
                complete: () => {
                    // Always remove spinning and re-enable button
                    $icon.removeClass('hmg-ai-spinning');
                    $button.prop('disabled', false);
                },
                timeout: 30000 // 30 second timeout
            });
        },
        
        /**
         * Initialize usage bar animations
         */
        initializeUsageBars: function() {
            $('.hmg-ai-usage-fill').each(function() {
                const $bar = $(this);
                const width = $bar.data('width') || 0;
                setTimeout(() => {
                    $bar.css('width', width + '%');
                }, 100);
            });
            
            // Load fresh usage stats on page load
            if ($('.hmg-ai-meta-box').length > 0) {
                this.refreshUsageStats();
            }
        },

        /**
         * Setup auto-save for generated content
         */
        setupAutoSave: function() {
            if (typeof wp !== 'undefined' && wp.autosave) {
                const originalGetPostData = wp.autosave.getPostData;
                wp.autosave.getPostData = function() {
                    const data = originalGetPostData.apply(this, arguments);
                    // Add our meta fields to autosave
                    data.hmg_ai_auto_takeaways = $('#hmg_ai_auto_takeaways').is(':checked') ? '1' : '';
                    data.hmg_ai_auto_faq = $('#hmg_ai_auto_faq').is(':checked') ? '1' : '';
                    data.hmg_ai_auto_toc = $('#hmg_ai_auto_toc').is(':checked') ? '1' : '';
                    return data;
                };
            }
        },

        /**
         * Generate takeaways via AJAX
         */
        generateTakeaways: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');
            
            // Get post content
            let content = '';
            if (typeof wp !== 'undefined' && wp.data) {
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                content = $('#content').val();
            }
            
            if (!content) {
                this.showNotice('Please add some content to your post before generating takeaways.', 'warning');
                return;
            }
            
            this.generateContent('takeaways', content, postId, $button);
        },

        /**
         * Generate FAQ via AJAX
         */
        generateFAQ: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');
            
            let content = '';
            if (typeof wp !== 'undefined' && wp.data) {
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                content = $('#content').val();
            }
            
            if (!content) {
                this.showNotice('Please add some content to your post before generating FAQ.', 'warning');
                return;
            }
            
            this.generateContent('faq', content, postId, $button);
        },

        /**
         * Generate Table of Contents via AJAX
         */
        generateTOC: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');
            
            let content = '';
            if (typeof wp !== 'undefined' && wp.data) {
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                content = $('#content').val();
            }
            
            if (!content) {
                this.showNotice('Please add some content to your post before generating table of contents.', 'warning');
                return;
            }
            
            this.generateContent('toc', content, postId, $button);
        },

        /**
         * Generate audio version
         */
        generateAudio: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');
            
            let content = '';
            if (typeof wp !== 'undefined' && wp.data) {
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                content = $('#content').val();
            }
            
            if (!content) {
                this.showNotice('Please add some content to your post before generating audio.', 'warning');
                return;
            }
            
            // Get voice setting for Eleven Labs
            const voice = $('#hmg-ai-audio-voice').val() || 'EXAVITQu4vr4xnSDxMaL';
            
            // Show loading state
            const originalText = $button.html();
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> Generating Audio...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_audio',
                    nonce: hmg_ai_ajax.nonce,
                    content: content,
                    post_id: postId,
                    voice: voice
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Audio generated successfully!', 'success', 'audio');
                        this.updateAudioContent(response.data, postId);
                        
                        if (response.data.usage) {
                            this.updateUsageStats(response.data.usage);
                        }
                    } else {
                        this.showNotice(response.data.message || 'Failed to generate audio.', 'error');
                    }
                },
                error: (xhr, status, error) => {

                    this.showNotice('An error occurred while generating audio. Please try again.', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).html(originalText);
                }
            });
        },

        /**
         * Update audio content in the UI
         */
        updateAudioContent: function(data, postId) {
            const audioHtml = `
                <div class="hmg-ai-content-item" id="audio-item">
                    <div class="hmg-ai-content-header">
                        <strong>Audio Version</strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green);"></span>
                    </div>
                    <div class="hmg-ai-audio-player-wrapper">
                        <audio controls class="hmg-ai-audio-player" style="width: 100%;">
                            <source src="${data.audio_url}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="hmg-ai-audio-info">
                            <span class="hmg-ai-audio-duration">Duration: ${data.duration ? data.duration.formatted : 'Unknown'}</span>
                            <span class="hmg-ai-separator">•</span>
                            <span class="hmg-ai-audio-voice">Voice: ${data.voice || 'Eleven Labs'}</span>
                        </div>
                    </div>
                    <div class="hmg-ai-content-actions">
                        <button type="button" class="button-link hmg-ai-regenerate" data-type="audio" data-post-id="${postId}" title="Regenerate">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                        <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="audio" title="Insert Shortcode">
                            <span class="dashicons dashicons-shortcode"></span>
                        </button>
                        <a href="${data.audio_url}" download class="button-link" title="Download">
                            <span class="dashicons dashicons-download"></span>
                        </a>
                        <button type="button" class="button-link hmg-ai-delete-content" data-type="audio" data-post-id="${postId}" title="Delete" style="color: #d63638;">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="hmg-ai-content-notice" id="audio-notice"></div>
                </div>
            `;
            
            // Check if audio section already exists
            let $audioSection = $('#audio-item');
            if ($audioSection.length) {
                $audioSection.replaceWith(audioHtml);
            } else {
                // Add to generated content section
                if (!$('.hmg-ai-generated-content').length) {
                    $('.hmg-ai-generation-controls').after('<div class="hmg-ai-generated-content"><h4>Generated Content</h4></div>');
                }
                $('.hmg-ai-generated-content').append(audioHtml);
            }
        },

        /**
         * Generic content generation handler
         */
        generateContent: function(type, content, postId, $button) {
            const originalText = $button.html();
            const loadingText = `<span class="spinner is-active" style="float: none; margin: 0;"></span> Generating...`;
            
            $button.prop('disabled', true).html(loadingText);
            this.hideNotices();
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_' + type,
                    nonce: hmg_ai_ajax.nonce,
                    content: content,
                    post_id: postId
                },
                success: (response) => {

                    if (response.success) {
                        this.showNotice(`${this.capitalizeFirst(type)} generated successfully!`, 'success');
                        this.updateGeneratedContent(type, response.data, postId);
                        // Usage data is in response.data.usage
                        if (response.data && response.data.usage) {

                            this.updateUsageStats(response.data.usage);
                        } else {

                        }
                    } else {
                        const errorMsg = (response.data && response.data.message) ? 
                            response.data.message : 
                            (response.data && typeof response.data === 'string') ? 
                            response.data : 
                            'Generation failed. Please try again.';
                        this.showNotice(errorMsg, 'error');
                    }
                },
                error: (xhr, status, error) => {

                    this.showNotice('An error occurred. Please check your connection and try again.', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).html(originalText);
                }
            });
        },

        /**
         * Update the generated content display
         */
        updateGeneratedContent: function(type, data, postId) {
            const $container = $('.hmg-ai-generated-content');
            
            if ($container.length === 0) {
                // Create container if it doesn't exist
                const html = `
                    <div class="hmg-ai-generated-content">
                        <h4>Generated Content</h4>
                    </div>
                `;
                $('.hmg-ai-generation-controls').after(html);
            }

            // Add or update content item
            let $item = $(`#${type}-item`);
            if ($item.length === 0) {
                const itemHtml = this.createContentItemHTML(type, data, postId);
                $('.hmg-ai-generated-content').append(itemHtml);
            } else {
                // Update existing item
                $item.find('.hmg-ai-content-preview').html(this.formatPreview(data.content));
                $item.find('textarea').val(data.content);
            }
        },

        /**
         * Create HTML for a content item
         */
        createContentItemHTML: function(type, data, postId) {
            const typeLabels = {
                'takeaways': 'Key Takeaways',
                'faq': 'FAQ',
                'toc': 'Table of Contents'
            };

            return `
                <div class="hmg-ai-content-item" id="${type}-item">
                    <div class="hmg-ai-content-header">
                        <strong>${typeLabels[type]}</strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green, #5E9732);"></span>
                    </div>
                    <div class="hmg-ai-content-preview" id="${type}-preview">
                        ${this.formatPreview(data.content)}
                    </div>
                    <div class="hmg-ai-content-actions">
                        <button type="button" class="button-link hmg-ai-edit-content" data-type="${type}" data-post-id="${postId}" title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="button-link hmg-ai-regenerate" data-type="${type}" data-post-id="${postId}" title="Regenerate">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                        <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="${type}" title="Insert Shortcode">
                            <span class="dashicons dashicons-shortcode"></span>
                        </button>
                        <button type="button" class="button-link hmg-ai-delete-content" data-type="${type}" data-post-id="${postId}" title="Delete" style="color: #d63638;">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="hmg-ai-content-notice" id="${type}-notice"></div>
                    <div class="hmg-ai-content-editor" id="${type}-editor" style="display: none;">
                        <textarea rows="6" style="width: 100%;" id="${type}-content">${data.content}</textarea>
                        <div class="hmg-ai-editor-actions">
                            <button type="button" class="button button-primary hmg-ai-save-content" data-type="${type}" data-post-id="${postId}">
                                Save
                            </button>
                            <button type="button" class="button hmg-ai-cancel-edit" data-type="${type}">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Format content for preview
         */
        formatPreview: function(content) {
            if (typeof content === 'object') {
                // Handle array or object data
                if (Array.isArray(content)) {
                    return content.slice(0, 2).join('<br>') + '...';
                } else {
                    return JSON.stringify(content).substring(0, 100) + '...';
                }
            }
            // Handle string content
            const stripped = content.replace(/<[^>]*>/g, '');
            return stripped.substring(0, 100) + '...';
        },

        /**
         * Edit content inline
         */
        editContent: function(e) {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            $(`#${type}-preview`).hide();
            $(`#${type}-editor`).show();
        },

        /**
         * Cancel edit
         */
        cancelEdit: function(e) {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            $(`#${type}-editor`).hide();
            $(`#${type}-preview`).show();
        },

        /**
         * Save edited content
         */
        saveContent: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const type = $button.data('type');
            const postId = $button.data('post-id');
            const $textarea = $(`#${type}-content`);
            const content = $textarea.val();
            
            // Debug logging

            $button.prop('disabled', true).text('Saving...');

            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_save_ai_content',
                    nonce: hmg_ai_ajax.nonce,
                    content_type: type,
                    content: content,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Content saved successfully!', 'success', type);
                        $(`#${type}-preview`).html(this.formatPreview(content));
                        $(`#${type}-editor`).hide();
                        $(`#${type}-preview`).show();
                    } else {
                        // Show actual error message from server
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Failed to save content. Please try again.';
                        this.showNotice(errorMsg, 'error', type);

                    }
                },
                error: (xhr, status, error) => {

                    this.showNotice('An error occurred while saving. Please check console for details.', 'error', type);
                },
                complete: () => {
                    $button.prop('disabled', false).text('Save');
                }
            });
        },

        /**
         * Regenerate content
         */
        regenerateContent: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const type = $button.data('type');
            const postId = $button.data('post-id');
            const $icon = $button.find('.dashicons');
            
            // Format type for display
            const displayType = type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ');
            
            this.showModal(
                `Regenerate ${displayType}?`,
                `<p>Are you sure you want to regenerate this content?</p>
                 <p><strong>Warning:</strong> The current version will be replaced with new AI-generated content.</p>`,
                () => {
                    // Add spinning animation to the icon
                    $icon.addClass('hmg-ai-spinning');
                    $button.prop('disabled', true);
                    
                    // Trigger the generation
                    const $generateBtn = $(`.hmg-ai-generate-${type}`);
                    $generateBtn.click();
                    
                    // Watch for completion
                    const checkInterval = setInterval(() => {
                        // Check if generation is complete (button is re-enabled or loading state is gone)
                        if (!$generateBtn.hasClass('loading') && !$generateBtn.prop('disabled')) {
                            $icon.removeClass('hmg-ai-spinning');
                            $button.prop('disabled', false);
                            clearInterval(checkInterval);
                        }
                    }, 500);
                    
                    // Fallback timeout to remove spinning after 30 seconds
                    setTimeout(() => {
                        $icon.removeClass('hmg-ai-spinning');
                        $button.prop('disabled', false);
                        clearInterval(checkInterval);
                    }, 30000);
                },
                'Regenerate',
                'button-primary'
            );
        },

        /**
         * Insert shortcode into editor
         */
        insertShortcode: function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            const $button = $(e.currentTarget);
            
            // Prevent double clicks
            if ($button.hasClass('processing')) {
                return;
            }
            
            $button.addClass('processing');
            
            const type = $button.data('type');
            const shortcode = `[hmg_ai_${type}]`;
            
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                // Gutenberg editor
                try {
                    // Try block editor first (Gutenberg)
                    if (wp.data.select('core/block-editor')) {
                        const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();
                        
                        if (selectedBlock) {
                            // Insert after selected block
                            const selectedBlockIndex = wp.data.select('core/block-editor').getBlockIndex(selectedBlock.clientId);
                            const shortcodeBlock = wp.blocks.createBlock('core/paragraph', {
                                content: shortcode
                            });
                            wp.data.dispatch('core/block-editor').insertBlock(shortcodeBlock, selectedBlockIndex + 1);
                        } else {
                            // Append to end using block
                            const shortcodeBlock = wp.blocks.createBlock('core/paragraph', {
                                content: shortcode
                            });
                            wp.data.dispatch('core/block-editor').insertBlock(shortcodeBlock);
                        }
                    } else {
                        // Fallback to classic content edit
                        const currentContent = wp.data.select('core/editor').getEditedPostContent();
                        wp.data.dispatch('core/editor').editPost({
                            content: currentContent + '\n\n' + shortcode
                        });
                    }
                    this.showNotice('Shortcode added to editor!', 'success', type);
                } catch (error) {

                    // Fallback method
                    const currentContent = wp.data.select('core/editor').getEditedPostContent();
                    wp.data.dispatch('core/editor').editPost({
                        content: currentContent + '\n\n' + shortcode
                    });
                    this.showNotice('Shortcode added to editor!', 'success', type);
                }
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                // Classic editor with TinyMCE
                tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
                this.showNotice('Shortcode inserted!', 'success', type);
            } else {
                // Fallback: copy to clipboard
                this.copyToClipboard(shortcode);
                this.showNotice('Shortcode copied to clipboard!', 'success', type);
            }
            
            // Remove processing class after a delay
            setTimeout(() => {
                $button.removeClass('processing');
            }, 500);
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
        },

        /**
         * Delete generated content
         */
        deleteContent: function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            const $button = $(e.currentTarget);
            
            // Prevent multiple clicks
            if ($button.hasClass('processing')) {
                return;
            }
            
            const type = $button.data('type');
            const postId = $button.data('post-id');
            
            $button.addClass('processing');
            
            // Format type for display
            const displayType = type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ');
            
            this.showModal(
                `Delete ${displayType}?`,
                `<p>Are you sure you want to delete the generated ${displayType.toLowerCase()}?</p>
                 <p><strong>Warning:</strong> This action cannot be undone.</p>`,
                () => {
                    const originalText = $button.html();
                    const $icon = $button.find('.dashicons');
                    
                    // Add spinning animation
                    $icon.addClass('hmg-ai-spinning');
                    $button.prop('disabled', true);
                    
                    $.ajax({
                        url: hmg_ai_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'hmg_delete_content',
                        nonce: hmg_ai_ajax.nonce,
                        type: type,
                        post_id: postId
                    },
                        success: (response) => {
                            $icon.removeClass('hmg-ai-spinning');
                            $button.prop('disabled', false).html(originalText);
                            
                            if (response.success) {
                                this.showNotice(`${this.capitalizeFirst(type)} deleted successfully!`, 'success', type);
                                // Remove the content item from display
                                $button.closest('.hmg-ai-content-item').fadeOut(400, function() {
                                    $(this).remove();
                                    // Check if there are any content items left
                                    if ($('.hmg-ai-content-item').length === 0) {
                                        $('.hmg-ai-generated-content').remove();
                                    }
                                });
                            } else {
                                const errorMsg = (response.data && response.data.message) ? 
                                    response.data.message : 
                                    'Failed to delete content.';
                                this.showNotice(errorMsg, 'error', type);
                                $button.removeClass('processing');
                            }
                        },
                        error: (xhr, status, error) => {

                            $icon.removeClass('hmg-ai-spinning');
                            $button.prop('disabled', false).html(originalText);
                            this.showNotice('An error occurred. Please try again.', 'error', type);
                            $button.removeClass('processing');
                        }
                    });
                },
                'Delete',
                'button-danger'
            );
            
            // Remove processing flag if modal was cancelled
            $button.removeClass('processing');
        },

        /**
         * Test AI providers
         */
        testProviders: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_test_ai_providers',
                    nonce: hmg_ai_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        let message = 'Provider Test Results:\n\n';
                        for (let provider in response.data) {
                            const result = response.data[provider];
                            message += `${result.name}: ${result.success ? '✅ Connected' : '❌ Failed'}\n`;
                            if (result.message) {
                                message += `  ${result.message}\n`;
                            }
                        }
                        alert(message);
                    } else {
                        alert('Test failed: ' + response.data);
                    }
                },
                error: () => {
                    alert('Failed to test providers. Please check your connection.');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Refresh usage stats from server
         */
        refreshUsageStats: function() {
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_get_usage_stats',
                    nonce: hmg_ai_ajax.nonce
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.updateUsageStats(response.data);
                    }
                }
            });
        },

        /**
         * Update usage statistics display
         */
        updateUsageStats: function(usage) {
            if (!usage) {

                return;
            }

            if (usage.spending) {
                // Format spending with appropriate decimal places
                const formatSpending = (amount) => {
                    if (amount < 0.01) {
                        return '$' + amount.toFixed(4); // Show 4 decimals for very small amounts
                    } else if (amount < 1) {
                        return '$' + amount.toFixed(3); // Show 3 decimals for small amounts
                    } else {
                        return '$' + amount.toFixed(2); // Show 2 decimals for regular amounts
                    }
                };
                
                // Update spending display
                $('.spending-used').text(formatSpending(usage.spending.used));
                $('.spending-limit').text(formatSpending(usage.spending.limit));
                $('.spending-percentage').text('(' + usage.spending.percentage.toFixed(1) + '%)');
                
                // Update spending bar with animation
                $('.hmg-ai-usage-fill.spending').each(function() {
                    $(this).attr('data-width', Math.min(100, usage.spending.percentage));
                    $(this).css('width', Math.min(100, usage.spending.percentage) + '%');
                });
                
                // Also update in the usage section
                $('.hmg-ai-usage-stats .spending-used').text(formatSpending(usage.spending.used));
                $('.hmg-ai-usage-stats .spending-limit').text(formatSpending(usage.spending.limit));
                $('.hmg-ai-usage-stats .spending-percentage').text('(' + usage.spending.percentage.toFixed(1) + '%)');
            }

            if (usage.api_calls !== undefined) {
                // Update API calls display
                $('.api-calls-used').text(usage.api_calls.toLocaleString());
                
                // Calculate percentage (using a reasonable scale)
                const percentage = Math.min(100, (usage.api_calls / Math.max(usage.api_calls + 100, 1000)) * 100);
                
                // Update API calls bar
                $('.hmg-ai-usage-fill.api-calls').each(function() {
                    $(this).attr('data-width', percentage);
                    $(this).css('width', percentage + '%');
                });
                
                // Update in usage section
                $('.hmg-ai-usage-stats .api-calls-used').text(usage.api_calls.toLocaleString());
            }

            if (usage.tokens !== undefined) {
                // Update tokens display
                $('.tokens-used').text(usage.tokens.toLocaleString());
                
                // Calculate percentage
                const percentage = Math.min(100, (usage.tokens / Math.max(usage.tokens * 10, 1000000)) * 100);
                
                // Update tokens bar
                $('.hmg-ai-usage-fill.tokens').each(function() {
                    $(this).attr('data-width', percentage);
                    $(this).css('width', percentage + '%');
                });
                
                // Update in usage section
                $('.hmg-ai-usage-stats .tokens-used').text(usage.tokens.toLocaleString());
            }

            if (usage.reset_date) {
                // Update reset date
                $('.hmg-ai-reset-date').text(usage.reset_date);
            }
        },

        /**
         * Toggle custom spending limit field
         */
        toggleCustomLimit: function() {
            const isCustom = $('input[name="spending_limit_type"]:checked').val() === 'custom';
            $('#custom-limit-section').toggle(isCustom);
        },

        /**
         * Show notice message
         */
        showNotice: function(message, type = 'info', contentType = null) {
            // Ensure message is a string
            if (typeof message === 'object' && message !== null) {
                // Try to extract the actual message from the object
                if (message.message) {
                    message = message.message;
                } else if (message.error) {
                    message = message.error;
                } else if (message.data && message.data.message) {
                    message = message.data.message;
                } else {
                    // Fallback to stringify if we can't find a message property
                    message = JSON.stringify(message);
                }
            }
            
            // Convert to string if still not a string
            message = String(message || 'An error occurred');
            // Use modal for success messages
            if (type === 'success') {
                const icon = '<span class="dashicons dashicons-yes-alt" style="color: #00a32a; font-size: 48px; display: inline-block; line-height: 1;"></span>';
                const modalMessage = `
                    <div style="text-align: center;">
                        ${icon}
                        <p style="margin-top: 25px; font-size: 16px; color: #333;">${message}</p>
                    </div>
                `;
                
                // Add success class to modal
                const $modal = $('#hmg-ai-modal');
                $modal.addClass('hmg-ai-modal-success');
                
                this.showModal(
                    'Success!',
                    modalMessage,
                    null,
                    'Great!',
                    'button-primary'
                );
                
                // Play success sound (optional)
                this.playSuccessSound();
                
                // Auto-close modal after 2.5 seconds for success messages
                this.modalTimeout = setTimeout(() => {
                    this.hideModal();
                    $modal.removeClass('hmg-ai-modal-success');
                }, 2500);
                
                return;
            }
            
            // Use modal for error messages
            if (type === 'error') {
                const icon = '<span class="dashicons dashicons-warning" style="color: #d63638; font-size: 48px; display: inline-block; line-height: 1;"></span>';
                const modalMessage = `
                    <div style="text-align: center;">
                        ${icon}
                        <p style="margin-top: 25px; font-size: 16px; color: #333;">${message}</p>
                    </div>
                `;
                
                // Add error class to modal
                const $modal = $('#hmg-ai-modal');
                $modal.addClass('hmg-ai-modal-error');
                
                this.showModal(
                    'Error',
                    modalMessage,
                    null,
                    'OK',
                    'button'
                );
                
                // Remove error class after 5 seconds (but don't auto-close)
                this.modalTimeout = setTimeout(() => {
                    $modal.removeClass('hmg-ai-modal-error');
                }, 5000);
                
                return;
            }
            
            // For warnings and info, use inline notices (less intrusive)
            const $notices = contentType ? $(`#${contentType}-notice`) : $('.hmg-ai-notices');
            
            if (contentType && $notices.length === 0) {
                // Fallback to modal for missing containers
                this.showNotice(message, type);
                return;
            }
            
            const noticeClass = type === 'warning' ? 'notice-warning' : 'notice-info';
            
            // Different HTML based on whether it's a content-specific or global notice
            const html = contentType ? 
                `<div class="hmg-ai-inline-notice ${noticeClass}">
                    <span class="dashicons dashicons-${type === 'warning' ? 'warning' : 'info'}"></span>
                    <span>${message}</span>
                </div>` :
                `<div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>`;
            
            $notices.html(html);
            
            // Auto-dismiss warnings/info after appropriate time
            const dismissTime = contentType ? 3000 : 5000;
            if (type === 'warning' || contentType) {
                setTimeout(() => {
                    $notices.find(contentType ? '.hmg-ai-inline-notice' : '.notice').fadeOut(() => {
                        $notices.empty();
                    });
                }, dismissTime);
            }
            
            // Handle dismiss button
            $notices.find('.notice-dismiss').on('click', function() {
                $(this).parent().fadeOut(() => {
                    $(this).remove();
                });
            });
        },

        /**
         * Hide all notices
         */
        hideNotices: function() {
            $('.hmg-ai-notices').empty();
        },
        
        /**
         * Play success sound using Web Audio API
         */
        playSuccessSound: function() {
            try {
                // Create audio context
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Create oscillator for beep sound
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                // Set frequency for pleasant beep (two-tone success)
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime + 0.1);
                oscillator.type = 'sine';
                
                // Set volume envelope
                gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.2, audioContext.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                
                // Play the sound
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                // Silently fail if Web Audio API is not supported

            }
        },

        /**
         * Capitalize first letter
         */
        capitalizeFirst: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {

        HMGAIAdmin.init();
    });

})(jQuery); 