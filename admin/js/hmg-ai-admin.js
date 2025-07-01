/**
 * HMG AI Blog Enhancer - Admin JavaScript
 * 
 * Professional admin interface interactions with Haley Marketing polish
 */

(function($) {
    'use strict';

    /**
     * Main admin object
     */
    const HMGAIAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initializeComponents();
            this.loadUsageStats();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            // Content generation buttons
            $(document).on('click', '.hmg-ai-generate-takeaways', this.generateTakeaways);
            $(document).on('click', '.hmg-ai-generate-faq', this.generateFAQ);
            $(document).on('click', '.hmg-ai-generate-toc', this.generateTOC);
            $(document).on('click', '.hmg-ai-generate-audio', this.generateAudio);
            
            // Settings validation
            $(document).on('click', '.hmg-ai-validate-api-key', this.validateApiKey);
            
            // Form submissions
            $(document).on('submit', '.hmg-ai-settings-form', this.saveSettings);
            
            // Usage stats refresh
            $(document).on('click', '.hmg-ai-refresh-stats', this.loadUsageStats);
            
            // Dismissible notices
            $(document).on('click', '.hmg-ai-notice .notice-dismiss', this.dismissNotice);
        },

        /**
         * Initialize UI components
         */
        initializeComponents: function() {
            // Initialize tooltips if available
            if (typeof $.fn.tooltip === 'function') {
                $('.hmg-ai-tooltip').tooltip();
            }
            
            // Initialize usage meters
            this.updateUsageMeters();
            
            // Auto-refresh usage stats every 5 minutes
            setInterval(this.loadUsageStats.bind(this), 300000);
        },

        /**
         * Generate takeaways via AJAX
         */
        generateTakeaways: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id') || $('#post_ID').val();
            const content = HMGAIAdmin.getPostContent();
            
            if (!content) {
                HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error + ' Please add some content first.');
                return;
            }
            
            HMGAIAdmin.setLoadingState($button, true);
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_takeaways',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HMGAIAdmin.insertGeneratedContent('takeaways', response.data.content);
                        HMGAIAdmin.showNotice('success', response.data.message);
                        HMGAIAdmin.updateUsageStats();
                    } else {
                        HMGAIAdmin.showNotice('error', response.data || hmg_ai_ajax.strings.error);
                    }
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error);
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($button, false);
                }
            });
        },

        /**
         * Generate FAQ via AJAX
         */
        generateFAQ: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id') || $('#post_ID').val();
            const content = HMGAIAdmin.getPostContent();
            
            if (!content) {
                HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error + ' Please add some content first.');
                return;
            }
            
            HMGAIAdmin.setLoadingState($button, true);
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_faq',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HMGAIAdmin.insertGeneratedContent('faq', response.data.content);
                        HMGAIAdmin.showNotice('success', response.data.message);
                        HMGAIAdmin.updateUsageStats();
                    } else {
                        HMGAIAdmin.showNotice('error', response.data || hmg_ai_ajax.strings.error);
                    }
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error);
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($button, false);
                }
            });
        },

        /**
         * Generate Table of Contents via AJAX
         */
        generateTOC: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id') || $('#post_ID').val();
            const content = HMGAIAdmin.getPostContent();
            
            if (!content) {
                HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error + ' Please add some content first.');
                return;
            }
            
            HMGAIAdmin.setLoadingState($button, true);
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_toc',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HMGAIAdmin.insertGeneratedContent('toc', response.data.content);
                        HMGAIAdmin.showNotice('success', response.data.message);
                        HMGAIAdmin.updateUsageStats();
                    } else {
                        HMGAIAdmin.showNotice('error', response.data || hmg_ai_ajax.strings.error);
                    }
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error);
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($button, false);
                }
            });
        },

        /**
         * Generate audio via AJAX
         */
        generateAudio: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id') || $('#post_ID').val();
            const content = HMGAIAdmin.getPostContent();
            
            if (!content) {
                HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error + ' Please add some content first.');
                return;
            }
            
            HMGAIAdmin.setLoadingState($button, true);
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_generate_audio',
                    nonce: hmg_ai_ajax.nonce,
                    post_id: postId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HMGAIAdmin.showNotice('success', response.data.message);
                        HMGAIAdmin.updateUsageStats();
                        
                        // Update audio player if available
                        if (response.data.audio_url) {
                            $('.hmg-ai-audio-player').attr('src', response.data.audio_url).show();
                        }
                    } else {
                        HMGAIAdmin.showNotice('error', response.data || hmg_ai_ajax.strings.error);
                    }
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', hmg_ai_ajax.strings.error);
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($button, false);
                }
            });
        },

        /**
         * Validate API key
         */
        validateApiKey: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const apiKey = $('#api_key').val();
            
            if (!apiKey) {
                HMGAIAdmin.showNotice('error', 'Please enter an API key first.');
                return;
            }
            
            HMGAIAdmin.setLoadingState($button, true);
            
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_validate_api_key',
                    nonce: hmg_ai_ajax.nonce,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        let message = response.data.message || 'API key is valid!';
                        if (response.data.tier) {
                            message += ' (Tier: ' + response.data.tier.charAt(0).toUpperCase() + response.data.tier.slice(1) + ')';
                        }
                        if (response.data.method) {
                            message += ' [Method: ' + response.data.method.replace('_', ' ') + ']';
                        }
                        
                        HMGAIAdmin.showNotice('success', message);
                        $('.hmg-ai-api-status').removeClass('invalid').addClass('valid');
                        $('.hmg-ai-user-tier').text(response.data.tier);
                        
                        // Optionally reload the page to show updated auth status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        HMGAIAdmin.showNotice('error', response.data.message || 'API key validation failed.');
                        $('.hmg-ai-api-status').removeClass('valid').addClass('invalid');
                    }
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', 'API key validation failed.');
                    $('.hmg-ai-api-status').removeClass('valid').addClass('invalid');
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($button, false);
                }
            });
        },

        /**
         * Load usage statistics
         */
        loadUsageStats: function() {
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_get_usage_stats',
                    nonce: hmg_ai_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        HMGAIAdmin.updateUsageDisplay(response.data);
                    }
                },
                error: function() {
                    console.log('Failed to load usage statistics');
                }
            });
        },

        /**
         * Update usage display
         */
        updateUsageDisplay: function(data) {
            // Update API calls usage
            const apiCallsPercent = (data.api_calls_used / data.api_calls_limit) * 100;
            $('.hmg-ai-usage-fill.api-calls').css('width', apiCallsPercent + '%');
            $('.hmg-ai-usage-stats .api-calls-used').text(data.api_calls_used);
            $('.hmg-ai-usage-stats .api-calls-limit').text(data.api_calls_limit);
            
            // Update tokens usage
            const tokensPercent = (data.tokens_used / data.tokens_limit) * 100;
            $('.hmg-ai-usage-fill.tokens').css('width', tokensPercent + '%');
            $('.hmg-ai-usage-stats .tokens-used').text(data.tokens_used);
            $('.hmg-ai-usage-stats .tokens-limit').text(data.tokens_limit);
            
            // Update reset date
            $('.hmg-ai-reset-date').text(data.reset_date);
        },

        /**
         * Update usage meters
         */
        updateUsageMeters: function() {
            $('.hmg-ai-usage-fill').each(function() {
                const $fill = $(this);
                const width = $fill.data('width') || 0;
                $fill.css('width', width + '%');
            });
        },

        /**
         * Get post content from editor
         */
        getPostContent: function() {
            // Try to get content from various editors
            let content = '';
            
            // Classic editor
            if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                content = tinyMCE.activeEditor.getContent();
            }
            
            // Block editor (Gutenberg)
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                const editorContent = wp.data.select('core/editor').getEditedPostContent();
                if (editorContent) {
                    content = editorContent;
                }
            }
            
            // Fallback to textarea
            if (!content) {
                content = $('#content').val() || $('#post-content').val() || '';
            }
            
            return content.trim();
        },

        /**
         * Insert generated content into editor
         */
        insertGeneratedContent: function(type, content) {
            // Try to insert into various editors
            
            // Block editor (Gutenberg)
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                const currentContent = wp.data.select('core/editor').getEditedPostContent();
                const newContent = currentContent + '\n\n' + content;
                wp.data.dispatch('core/editor').editPost({ content: newContent });
                return;
            }
            
            // Classic editor
            if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
                return;
            }
            
            // Fallback to textarea
            const $textarea = $('#content, #post-content').first();
            if ($textarea.length) {
                const currentContent = $textarea.val();
                $textarea.val(currentContent + '\n\n' + content);
            }
        },

        /**
         * Set loading state for buttons
         */
        setLoadingState: function($element, loading) {
            if (loading) {
                $element.addClass('hmg-ai-loading').prop('disabled', true);
                $element.data('original-text', $element.text());
                $element.text(hmg_ai_ajax.strings.generating);
            } else {
                $element.removeClass('hmg-ai-loading').prop('disabled', false);
                $element.text($element.data('original-text') || $element.text());
            }
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            const $notice = $('<div class="hmg-ai-notice ' + type + '">' + message + '</div>');
            $('.hmg-ai-notices').prepend($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        },

        /**
         * Dismiss notice
         */
        dismissNotice: function(e) {
            e.preventDefault();
            $(this).closest('.hmg-ai-notice').fadeOut(function() {
                $(this).remove();
            });
        },

        /**
         * Save settings form
         */
        saveSettings: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('[type="submit"]');
            
            HMGAIAdmin.setLoadingState($submitButton, true);
            
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    HMGAIAdmin.showNotice('success', 'Settings saved successfully!');
                },
                error: function() {
                    HMGAIAdmin.showNotice('error', 'Failed to save settings. Please try again.');
                },
                complete: function() {
                    HMGAIAdmin.setLoadingState($submitButton, false);
                }
            });
        },

        /**
         * Update usage stats after generation
         */
        updateUsageStats: function() {
            // Reload usage stats after a short delay
            setTimeout(function() {
                HMGAIAdmin.loadUsageStats();
            }, 1000);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        HMGAIAdmin.init();
    });

})(jQuery); 