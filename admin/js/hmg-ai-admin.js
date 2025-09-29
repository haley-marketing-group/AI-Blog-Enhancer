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
            
            // Context-Aware AI handlers
            $(document).on('click', '.hmg-ai-analyze-brand', this.analyzeBrandVoice.bind(this));
            $(document).on('click', '.hmg-ai-clear-profile', this.clearBrandProfile.bind(this));
            $('#use_brand_context').on('change', this.toggleBrandProfileSection);
            
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
            
            // Initialize SEO features if SEO box exists
            if ($('.hmg-ai-seo-box').length > 0) {
                this.initSEO();
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
            
            // Get selected provider
            const selectedProvider = $('#hmg-ai-provider-select').val() || 'auto';
            
            $button.prop('disabled', true).html(loadingText);
            this.hideNotices();
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_' + type,
                    nonce: hmg_ai_ajax.nonce,
                    content: content,
                    post_id: postId,
                    provider: selectedProvider
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
            
            // Check if button has a complete shortcode or a type
            let shortcode = $button.data('shortcode');
            if (!shortcode) {
                // Fallback to type for generated content buttons
                const type = $button.data('type');
                shortcode = type ? `[hmg_ai_${type}]` : '[hmg_ai_summarize]';
            }
            
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
                    this.showInlineMessage($button, 'Shortcode inserted!', 'success');
                } catch (error) {

                    // Fallback method
                    const currentContent = wp.data.select('core/editor').getEditedPostContent();
                    wp.data.dispatch('core/editor').editPost({
                        content: currentContent + '\n\n' + shortcode
                    });
                    this.showInlineMessage($button, 'Added to end of post!', 'success');
                }
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                // Classic editor with TinyMCE
                tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
                this.showInlineMessage($button, 'Shortcode inserted!', 'success');
            } else {
                // Fallback: copy to clipboard
                this.copyToClipboard(shortcode);
                this.showInlineMessage($button, 'Copied to clipboard!', 'info');
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
            const $temp = $('<textarea>');
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
         * Analyze brand voice from existing content
         */
        analyzeBrandVoice: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const $statusDiv = $('#hmg-ai-analysis-status');
            const postCount = $('#analysis_post_count').val() || 10;
            
            // Disable button and show loading
            $button.prop('disabled', true);
            $button.html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Analyzing...');
            
            // Show status
            $statusDiv.html(`
                <div class="hmg-ai-info-box" style="background: #f0f8ff; border-left: 4px solid #2196F3;">
                    <div style="display: flex; align-items: center;">
                        <span class="dashicons dashicons-update hmg-ai-spinning" style="margin-right: 10px; color: #2196F3;"></span>
                        <div>
                            <strong>Analyzing your content...</strong><br>
                            <small>Scanning ${postCount} recent posts to understand your brand voice</small>
                        </div>
                    </div>
                </div>
            `).show();
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_analyze_brand_voice',
                    nonce: hmg_ai_ajax.nonce,
                    post_count: postCount
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $statusDiv.html(`
                            <div class="hmg-ai-info-box" style="background: #d4edda; border-left: 4px solid #28a745;">
                                <div style="display: flex; align-items: center;">
                                    <span class="dashicons dashicons-yes-alt" style="margin-right: 10px; color: #28a745; font-size: 24px;"></span>
                                    <div>
                                        <strong>Analysis Complete!</strong><br>
                                        <small>${response.data.message}</small>
                                    </div>
                                </div>
                            </div>
                        `);
                        
                        // Reload page after 2 seconds to show updated profile
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                        
                    } else {
                        $statusDiv.html(`
                            <div class="hmg-ai-info-box" style="background: #f8d7da; border-left: 4px solid #dc3545;">
                                <div style="display: flex; align-items: center;">
                                    <span class="dashicons dashicons-warning" style="margin-right: 10px; color: #dc3545;"></span>
                                    <div>
                                        <strong>Analysis Failed</strong><br>
                                        <small>${response.data.message || 'An error occurred'}</small>
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $statusDiv.html(`
                        <div class="hmg-ai-info-box" style="background: #f8d7da; border-left: 4px solid #dc3545;">
                            <div style="display: flex; align-items: center;">
                                <span class="dashicons dashicons-warning" style="margin-right: 10px; color: #dc3545;"></span>
                                <div>
                                    <strong>Connection Error</strong><br>
                                    <small>Failed to analyze brand voice. Please try again.</small>
                                </div>
                            </div>
                        </div>
                    `);
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false);
                    $button.html('<span class="dashicons dashicons-search" style="margin-right: 5px; margin-top: 2px;"></span> Analyze Brand Voice');
                }
            });
        },
        
        /**
         * Clear brand profile
         */
        clearBrandProfile: function(e) {
            e.preventDefault();
            
            // Confirm with user
            this.showModal(
                'Confirm Clear Profile',
                'Are you sure you want to clear your brand profile? This will remove all learned patterns about your writing style.',
                'warning',
                function() {
                    const $button = $('.hmg-ai-clear-profile');
                    const $statusDiv = $('#hmg-ai-analysis-status');
                    
                    // Disable button
                    $button.prop('disabled', true);
                    
                    $.ajax({
                        url: hmg_ai_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'hmg_clear_brand_profile',
                            nonce: hmg_ai_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                $statusDiv.html(`
                                    <div class="hmg-ai-info-box" style="background: #d4edda; border-left: 4px solid #28a745;">
                                        <div style="display: flex; align-items: center;">
                                            <span class="dashicons dashicons-yes-alt" style="margin-right: 10px; color: #28a745;"></span>
                                            <div>
                                                <strong>Profile Cleared</strong><br>
                                                <small>Brand profile has been removed successfully</small>
                                            </div>
                                        </div>
                                    </div>
                                `).show();
                                
                                // Reload page after 1.5 seconds
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                HMGAIAdmin.showModal(
                                    'Error',
                                    response.data.message || 'Failed to clear profile',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            HMGAIAdmin.showModal(
                                'Error',
                                'Failed to clear brand profile. Please try again.',
                                'error'
                            );
                        }
                    });
                }.bind(this)
            );
        },
        
        /**
         * Toggle brand profile section visibility
         */
        toggleBrandProfileSection: function() {
            const isChecked = $('#use_brand_context').is(':checked');
            const $section = $('#brand-profile-section');
            
            if (isChecked) {
                $section.slideDown();
            } else {
                $section.slideUp();
            }
        },
        
        /**
         * Initialize SEO features
         */
        initSEO: function() {
            // SEO button handlers
            $(document).on('click', '.hmg-ai-analyze-seo', this.analyzeSEO.bind(this));
            $(document).on('click', '.hmg-ai-optimize-seo', this.optimizeSEO.bind(this));
            $(document).on('click', '.hmg-ai-generate-meta', this.generateMetaDescription.bind(this));
            $(document).on('click', '.hmg-ai-extract-keywords', this.extractKeywords.bind(this));
            $(document).on('click', '.hmg-ai-insert-link', this.insertInternalLink.bind(this));
            $(document).on('click', '.hmg-ai-remove-keyword', this.removeKeyword.bind(this));
            
            // Character counters
            $('#hmg-ai-meta-description').on('input', function() {
                $('#meta-desc-count').text($(this).val().length + '/160');
            });
            
            $('#hmg-ai-seo-title').on('input', function() {
                $('#title-count').text($(this).val().length + '/60');
            });
            
            // Keyword input
            $('#hmg-ai-add-keyword').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    HMGAIAdmin.addKeyword($(this).val());
                    $(this).val('');
                }
            });
            
            // Auto-save SEO data on blur
            $('#hmg-ai-meta-description, #hmg-ai-seo-title').on('blur', this.saveSEOData.bind(this));
            $('#hmg-ai-enable-schema').on('change', this.saveSEOData.bind(this));
        },
        
        /**
         * Analyze SEO
         */
        analyzeSEO: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const postId = $('#post_ID').val();
            const content = this.getPostContent();
            const title = $('#title').val() || $('#post-title-0').val();
            
            // Show analyzing state in the score display
            if ($('.hmg-ai-seo-not-analyzed').length) {
                $('.hmg-ai-seo-not-analyzed').html(`
                    <div class="seo-analyzing">
                        <div class="analyzing-circle">
                            <svg viewBox="0 0 200 200">
                                <circle cx="100" cy="100" r="90" stroke="rgba(255,255,255,0.2)" stroke-width="10" fill="none"/>
                                <circle cx="100" cy="100" r="90" 
                                        stroke="url(#gradient)" 
                                        stroke-width="10" 
                                        fill="none"
                                        stroke-dasharray="20 10"
                                        class="analyzing-progress"/>
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="analyzing-inner">
                                <span class="dashicons dashicons-update hmg-ai-spinning"></span>
                            </div>
                        </div>
                        <div class="analyzing-text">
                            <h3>Analyzing Your Content...</h3>
                            <div class="analyzing-steps">
                                <div class="step active">
                                    <span class="dashicons dashicons-yes"></span> Reading content
                                </div>
                                <div class="step">
                                    <span class="dashicons dashicons-clock"></span> Checking readability
                                </div>
                                <div class="step">
                                    <span class="dashicons dashicons-clock"></span> Extracting keywords
                                </div>
                                <div class="step">
                                    <span class="dashicons dashicons-clock"></span> Generating suggestions
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                // Animate through steps
                let currentStep = 0;
                const steps = $('.analyzing-steps .step');
                const stepInterval = setInterval(function() {
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        $(steps[currentStep]).removeClass('step').addClass('step active');
                        $(steps[currentStep]).find('.dashicons-clock').removeClass('dashicons-clock').addClass('dashicons-yes');
                    }
                }, 500);
                
                // Store interval to clear later
                $button.data('stepInterval', stepInterval);
            }
            
            // Disable button and show loading
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Analyzing...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_analyze_seo',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content,
                    title: title
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI with SEO data
                        this.updateSEODisplay(response.data);
                        this.showModal('Success', 'SEO analysis complete!', 'success');
                    } else {
                        this.showModal('Error', response.data.message || 'Analysis failed', 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showModal('Error', 'Failed to analyze SEO. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    // Clear the step animation interval
                    const stepInterval = $button.data('stepInterval');
                    if (stepInterval) {
                        clearInterval(stepInterval);
                    }
                    
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> Analyze SEO');
                }.bind(this)
            });
        },
        
        /**
         * Auto-optimize SEO
         */
        optimizeSEO: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const postId = $('#post_ID').val();
            const content = this.getPostContent();
            const title = $('#title').val() || $('#post-title-0').val();
            const keywords = this.getKeywords();
            
            // Disable button and show loading
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Optimizing...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_optimize_seo',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content,
                    title: title,
                    keywords: keywords
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI with optimized data
                        this.updateSEODisplay(response.data);
                        
                        // Update content if optimized
                        if (response.data.optimized_content) {
                            // For block editor
                            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                                wp.data.dispatch('core/editor').editPost({
                                    content: response.data.optimized_content
                                });
                            }
                        }
                        
                        this.showModal('Success', 'Content optimized for SEO!', 'success');
                    } else {
                        this.showModal('Error', response.data.message || 'Optimization failed', 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showModal('Error', 'Failed to optimize SEO. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> Auto-Optimize');
                }
            });
        },
        
        /**
         * Generate meta description
         */
        generateMetaDescription: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const postId = $('#post_ID').val();
            const content = this.getPostContent();
            const title = $('#title').val() || $('#post-title-0').val();
            
            // Disable button
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Generating...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_meta_description',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content,
                    title: title
                },
                success: function(response) {
                    if (response.success) {
                        $('#hmg-ai-meta-description').val(response.data.meta_description);
                        $('#meta-desc-count').text(response.data.meta_description.length + '/160');
                        this.saveSEOData();
                    } else {
                        this.showModal('Error', response.data.message || 'Failed to generate meta description', 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showModal('Error', 'Failed to generate meta description', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Generate with AI');
                }
            });
        },
        
        /**
         * Extract keywords
         */
        extractKeywords: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const postId = $('#post_ID').val();
            const content = this.getPostContent();
            const title = $('#title').val() || $('#post-title-0').val();
            
            // Disable button
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update hmg-ai-spinning"></span> Extracting...');
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_extract_keywords',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content,
                    title: title
                },
                success: function(response) {
                    if (response.success && response.data.keywords) {
                        // Clear existing keywords
                        $('.hmg-ai-keyword-tag').remove();
                        
                        // Add new keywords
                        response.data.keywords.forEach(function(keyword) {
                            this.addKeyword(keyword);
                        }.bind(this));
                        
                        this.saveSEOData();
                    } else {
                        this.showModal('Error', 'Failed to extract keywords', 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showModal('Error', 'Failed to extract keywords', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-tag"></span> Extract Keywords');
                }
            });
        },
        
        /**
         * Add keyword
         */
        addKeyword: function(keyword) {
            if (!keyword || keyword.trim() === '') return;
            
            // Check if already exists
            const exists = $('.hmg-ai-keyword-tag').filter(function() {
                return $(this).data('keyword') === keyword;
            }).length > 0;
            
            if (!exists) {
                const $tag = $('<span class="hmg-ai-keyword-tag">')
                    .attr('data-keyword', keyword)
                    .html(keyword + '<button type="button" class="hmg-ai-remove-keyword" data-keyword="' + keyword + '">×</button>');
                
                $('#hmg-ai-add-keyword').before($tag);
                this.saveSEOData();
            }
        },
        
        /**
         * Remove keyword
         */
        removeKeyword: function(e) {
            e.preventDefault();
            $(e.currentTarget).parent().remove();
            this.saveSEOData();
        },
        
        /**
         * Get keywords
         */
        getKeywords: function() {
            const keywords = [];
            $('.hmg-ai-keyword-tag').each(function() {
                keywords.push($(this).data('keyword'));
            });
            return keywords;
        },
        
        /**
         * Insert internal link
         */
        insertInternalLink: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const url = $button.data('url');
            const title = $button.data('title');
            const keyword = $button.data('keyword');
            
            // Create link HTML
            const linkHtml = '<a href="' + url + '" title="' + title + '">' + keyword + '</a>';
            
            // For block editor
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                // Get current content
                const content = wp.data.select('core/editor').getEditedPostContent();
                
                // Find first occurrence of keyword that's not already linked
                const regex = new RegExp('(?![^<]*>)' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?![^<]*</a>)', 'i');
                const newContent = content.replace(regex, linkHtml);
                
                // Update content
                wp.data.dispatch('core/editor').editPost({
                    content: newContent
                });
                
                $button.text('Inserted').prop('disabled', true);
            } else {
                // For classic editor
                this.showModal('Info', 'Link copied: ' + linkHtml, 'info');
            }
        },
        
        /**
         * Update SEO display
         */
        updateSEODisplay: function(data) {
            // Update score
            if (data.readability_score) {
                const score = Math.round(data.readability_score);
                const scoreClass = score >= 70 ? 'good' : (score >= 50 ? 'moderate' : 'poor');
                
                // Update score display
                const scoreHtml = `
                    <div class="hmg-ai-score-circle ${scoreClass}">
                        <span class="score-value">${score}</span>
                        <span class="score-label">/ 100</span>
                    </div>
                `;
                $('.hmg-ai-seo-score-display').html(scoreHtml);
                
                // Enable optimize button
                $('.hmg-ai-optimize-seo').prop('disabled', false);
            }
            
            // Update meta description
            if (data.meta_description) {
                $('#hmg-ai-meta-description').val(data.meta_description);
                $('#meta-desc-count').text(data.meta_description.length + '/160');
            }
            
            // Update SEO title
            if (data.seo_title) {
                $('#hmg-ai-seo-title').val(data.seo_title);
                $('#title-count').text(data.seo_title.length + '/60');
            }
            
            // Update keywords
            if (data.keywords && data.keywords.length > 0) {
                $('.hmg-ai-keyword-tag').remove();
                data.keywords.forEach(function(keyword) {
                    this.addKeyword(keyword);
                }.bind(this));
            }
            
            // Update suggestions
            if (data.suggestions && data.suggestions.length > 0) {
                let suggestionsHtml = '<h4><span class="dashicons dashicons-lightbulb"></span> SEO Suggestions</h4><ul>';
                
                data.suggestions.forEach(function(suggestion) {
                    const icon = suggestion.priority === 'high' ? 'warning' : (suggestion.priority === 'medium' ? 'info' : 'yes-alt');
                    suggestionsHtml += '<li class="suggestion-' + suggestion.priority + '"><span class="dashicons dashicons-' + icon + '"></span> ' + suggestion.message + '</li>';
                });
                
                suggestionsHtml += '</ul>';
                
                // Add or update suggestions section
                if ($('.hmg-ai-seo-suggestions').length) {
                    $('.hmg-ai-seo-suggestions').html(suggestionsHtml);
                } else {
                    $('<div class="hmg-ai-seo-suggestions">').html(suggestionsHtml).insertAfter('.hmg-ai-seo-field:last');
                }
            }
            
            // Update internal links
            if (data.internal_links && data.internal_links.length > 0) {
                let linksHtml = '<h4><span class="dashicons dashicons-admin-links"></span> Suggested Internal Links</h4><ul>';
                
                data.internal_links.forEach(function(link) {
                    linksHtml += `
                        <li>
                            <a href="${link.url}" target="_blank">${link.title}</a>
                            <span class="link-keyword">(${link.keyword})</span>
                            <button type="button" class="button button-small hmg-ai-insert-link" 
                                    data-url="${link.url}"
                                    data-title="${link.title}"
                                    data-keyword="${link.keyword}">
                                Insert
                            </button>
                        </li>
                    `;
                });
                
                linksHtml += '</ul>';
                
                // Add or update links section
                if ($('.hmg-ai-seo-internal-links').length) {
                    $('.hmg-ai-seo-internal-links').html(linksHtml);
                } else {
                    $('<div class="hmg-ai-seo-internal-links">').html(linksHtml).insertAfter('.hmg-ai-seo-suggestions');
                }
            }
        },
        
        /**
         * Save SEO data
         */
        saveSEOData: function() {
            const postId = $('#post_ID').val();
            
            if (!postId) return;
            
            const data = {
                action: 'hmg_save_seo_data',
                nonce: hmg_ai_ajax.nonce,
                post_id: postId,
                meta_description: $('#hmg-ai-meta-description').val(),
                seo_title: $('#hmg-ai-seo-title').val(),
                keywords: this.getKeywords(),
                enable_schema: $('#hmg-ai-enable-schema').is(':checked') ? 1 : 0
            };
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    // Silently save
                },
                error: function() {
                    // Silently fail
                }
            });
        },
        
        /**
         * Get post content
         */
        getPostContent: function() {
            // Try block editor first
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                try {
                    return wp.data.select('core/editor').getEditedPostContent();
                } catch (e) {
                    // Fall through to classic editor
                }
            }
            
            // Try classic editor
            if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                return tinyMCE.activeEditor.getContent();
            }
            
            // Fall back to textarea
            return $('#content').val() || '';
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