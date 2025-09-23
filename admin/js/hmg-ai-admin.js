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
        
        /**
         * Initialize the admin interface
         */
        init: function() {
            this.bindEvents();
            this.initializeUsageBars();
            this.setupAutoSave();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            // Content generation buttons
            $(document).on('click', '.hmg-ai-generate-takeaways', this.generateTakeaways.bind(this));
            $(document).on('click', '.hmg-ai-generate-faq', this.generateFAQ.bind(this));
            $(document).on('click', '.hmg-ai-generate-toc', this.generateTOC.bind(this));
            $(document).on('click', '.hmg-ai-generate-audio', this.generateAudio.bind(this));

            // Edit content buttons
            $(document).on('click', '.hmg-ai-edit-content', this.editContent.bind(this));
            $(document).on('click', '.hmg-ai-save-content', this.saveContent.bind(this));
            $(document).on('click', '.hmg-ai-cancel-edit', this.cancelEdit.bind(this));
            $(document).on('click', '.hmg-ai-regenerate', this.regenerateContent.bind(this));
            $(document).on('click', '.hmg-ai-insert-shortcode', this.insertShortcode.bind(this));

            // Test provider buttons
            $(document).on('click', '.hmg-ai-test-providers', this.testProviders.bind(this));
            
            // Settings page interactions
            $('input[name="spending_limit_type"]').on('change', this.toggleCustomLimit);
            
            // Refresh usage stats every 30 seconds if on editor page
            if ($('.hmg-ai-meta-box').length > 0) {
                setInterval(this.refreshUsageStats.bind(this), 30000);
            }
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

            this.showNotice('Audio generation is coming soon! This feature will be available in the next update.', 'info');
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
                    console.log('Generation response:', response);
                    if (response.success) {
                        this.showNotice(`${this.capitalizeFirst(type)} generated successfully!`, 'success');
                        this.updateGeneratedContent(type, response.data, postId);
                        // Usage data is in response.data.usage
                        if (response.data && response.data.usage) {
                            console.log('Found usage data in response:', response.data.usage);
                            this.updateUsageStats(response.data.usage);
                        } else {
                            console.log('No usage data in response');
                        }
                    } else {
                        this.showNotice(response.data || 'Generation failed. Please try again.', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Generation error:', error);
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
                        <strong>${typeLabels[type]}:</strong>
                        <span class="dashicons dashicons-yes-alt" style="color: var(--hmg-lime-green, #5E9732);"></span>
                        <div class="hmg-ai-content-actions">
                            <button type="button" class="button-link hmg-ai-edit-content" data-type="${type}" data-post-id="${postId}">
                                Edit
                            </button>
                            <button type="button" class="button-link hmg-ai-regenerate" data-type="${type}" data-post-id="${postId}">
                                Regenerate
                            </button>
                            <button type="button" class="button-link hmg-ai-insert-shortcode" data-type="${type}">
                                Insert Shortcode
                            </button>
                        </div>
                    </div>
                    <div class="hmg-ai-content-preview" id="${type}-preview">
                        ${this.formatPreview(data.content)}
                    </div>
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
                    return content.slice(0, 3).join('<br>') + '...';
                } else {
                    return JSON.stringify(content).substring(0, 150) + '...';
                }
            }
            // Handle string content
            const stripped = content.replace(/<[^>]*>/g, '');
            return stripped.substring(0, 150) + '...';
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
            const content = $(`#${type}-content`).val();

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
                        this.showNotice('Content saved successfully!', 'success');
                        $(`#${type}-preview`).html(this.formatPreview(content));
                        $(`#${type}-editor`).hide();
                        $(`#${type}-preview`).show();
                    } else {
                        this.showNotice('Failed to save content. Please try again.', 'error');
                    }
                },
                error: () => {
                    this.showNotice('An error occurred while saving. Please try again.', 'error');
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
            const type = $(e.currentTarget).data('type');
            const postId = $(e.currentTarget).data('post-id');
            
            if (confirm('Are you sure you want to regenerate this content? The current version will be replaced.')) {
                $(`.hmg-ai-generate-${type}`).click();
            }
        },

        /**
         * Insert shortcode into editor
         */
        insertShortcode: function(e) {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            const shortcode = `[hmg_ai_${type}]`;
            
            if (typeof wp !== 'undefined' && wp.data) {
                // Gutenberg editor
                const currentContent = wp.data.select('core/editor').getEditedPostContent();
                wp.data.dispatch('core/editor').editPost({
                    content: currentContent + '\n\n' + shortcode
                });
                this.showNotice('Shortcode added to editor!', 'success');
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                // Classic editor with TinyMCE
                tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
                this.showNotice('Shortcode inserted!', 'success');
            } else {
                // Fallback: copy to clipboard
                this.copyToClipboard(shortcode);
                this.showNotice('Shortcode copied to clipboard!', 'success');
            }
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
                console.log('No usage data provided to updateUsageStats');
                return;
            }
            
            console.log('Updating usage stats:', usage);

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
        showNotice: function(message, type = 'info') {
            const $notices = $('.hmg-ai-notices');
            const noticeClass = type === 'error' ? 'notice-error' : 
                               type === 'success' ? 'notice-success' : 
                               type === 'warning' ? 'notice-warning' : 'notice-info';
            
            const html = `
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `;
            
            $notices.html(html);
            
            // Auto-dismiss after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    $notices.find('.notice').fadeOut(() => {
                        $notices.empty();
                    });
                }, 5000);
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