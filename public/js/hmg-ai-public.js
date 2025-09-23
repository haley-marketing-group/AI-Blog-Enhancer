/**
 * HMG AI Blog Enhancer - Public JavaScript
 * 
 * Frontend interactions for AI-generated content
 */

(function($) {
    'use strict';

    /**
     * Main public object
     */
    const HMGAIPublic = {
        
        /**
         * Initialize public functionality
         */
        init: function() {
            this.initFAQAccordion();
            this.initSmoothScrolling();
            this.initAccessibility();
            this.initTOCProgress();
            this.initAudioControls();
            this.initTakeawaysInteractions();
        },

        /**
         * Initialize FAQ accordion functionality
         */
        initFAQAccordion: function() {
            // New accordion button functionality
            $(document).on('click', '[data-hmg-faq-toggle]', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $content = $('#' + $button.attr('aria-controls'));
                const isExpanded = $button.attr('aria-expanded') === 'true';
                
                // Toggle states
                $button.toggleClass('hmg-ai-active');
                $button.attr('aria-expanded', !isExpanded);
                
                if (isExpanded) {
                    $content.slideUp(300, function() {
                        $content.removeClass('hmg-ai-active').hide();
                    });
                } else {
                    $content.addClass('hmg-ai-active').slideDown(300);
                }
                
                // Close other items in accordion (optional)
                const $accordion = $button.closest('.hmg-ai-faq-accordion');
                if ($accordion.length) {
                    $accordion.find('[data-hmg-faq-toggle]').not($button).each(function() {
                        const $otherButton = $(this);
                        const $otherContent = $('#' + $otherButton.attr('aria-controls'));
                        
                        $otherButton.removeClass('hmg-ai-active').attr('aria-expanded', 'false');
                        $otherContent.removeClass('hmg-ai-active').slideUp(300);
                    });
                }
            });

            // Legacy support for old FAQ structure
            $(document).on('click', '.hmg-ai-faq-question', function(e) {
                e.preventDefault();
                
                const $question = $(this);
                const $answer = $question.next('.hmg-ai-faq-answer');
                const $faqItem = $question.closest('.hmg-ai-faq-item');
                
                // Toggle active state
                $question.toggleClass('active');
                $answer.toggleClass('active');
                
                // Slide animation
                if ($answer.hasClass('active')) {
                    $answer.slideDown(300);
                } else {
                    $answer.slideUp(300);
                }
                
                // Close other FAQ items in the same container (optional)
                const $otherItems = $faqItem.siblings('.hmg-ai-faq-item');
                $otherItems.find('.hmg-ai-faq-question').removeClass('active');
                $otherItems.find('.hmg-ai-faq-answer').removeClass('active').slideUp(300);
            });
        },

        /**
         * Initialize smooth scrolling for TOC links
         */
        initSmoothScrolling: function() {
            $(document).on('click', '[data-hmg-smooth-scroll]', function(e) {
                e.preventDefault();
                
                const target = $(this.getAttribute('href'));
                
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 600, 'swing', function() {
                        // Update active state for sidebar TOC
                        $('.hmg-ai-toc-sidebar-item').removeClass('active');
                        $('[data-target="' + target.attr('id') + '"]').addClass('active');
                    });
                    
                    // Update focus for accessibility
                    target.focus();
                }
            });

            // Legacy support
            $(document).on('click', '.hmg-ai-toc a[href^="#"]', function(e) {
                e.preventDefault();
                
                const target = $(this.getAttribute('href'));
                
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 600, 'swing');
                    
                    // Update focus for accessibility
                    target.focus();
                }
            });
        },

        /**
         * Initialize accessibility features
         */
        initAccessibility: function() {
            // Add ARIA attributes to FAQ items
            $('.hmg-ai-faq-question').each(function(index) {
                const $question = $(this);
                const $answer = $question.next('.hmg-ai-faq-answer');
                const questionId = 'faq-question-' + index;
                const answerId = 'faq-answer-' + index;
                
                $question.attr({
                    'id': questionId,
                    'role': 'button',
                    'aria-expanded': 'false',
                    'aria-controls': answerId,
                    'tabindex': '0'
                });
                
                $answer.attr({
                    'id': answerId,
                    'role': 'region',
                    'aria-labelledby': questionId
                });
            });
            
            // Handle keyboard navigation for FAQ
            $(document).on('keydown', '.hmg-ai-faq-question', function(e) {
                if (e.which === 13 || e.which === 32) { // Enter or Space
                    e.preventDefault();
                    $(this).click();
                }
            });
            
            // Update ARIA expanded state
            $(document).on('click', '.hmg-ai-faq-question', function() {
                const isExpanded = $(this).hasClass('active');
                $(this).attr('aria-expanded', isExpanded);
            });
            
            // Add skip links for better navigation
            this.addSkipLinks();
        },

        /**
         * Add skip links for better accessibility
         */
        addSkipLinks: function() {
            if ($('.hmg-ai-takeaways, .hmg-ai-faq, .hmg-ai-toc').length) {
                const skipLinks = $('<div class="hmg-ai-skip-links" style="position: absolute; left: -9999px;"></div>');
                
                if ($('.hmg-ai-takeaways').length) {
                    skipLinks.append('<a href="#hmg-ai-takeaways" class="screen-reader-text">Skip to Key Takeaways</a>');
                }
                
                if ($('.hmg-ai-faq').length) {
                    skipLinks.append('<a href="#hmg-ai-faq" class="screen-reader-text">Skip to FAQ</a>');
                }
                
                if ($('.hmg-ai-toc').length) {
                    skipLinks.append('<a href="#hmg-ai-toc" class="screen-reader-text">Skip to Table of Contents</a>');
                }
                
                $('body').prepend(skipLinks);
                
                // Show skip links on focus
                $('.hmg-ai-skip-links a').on('focus', function() {
                    $(this).css({
                        'position': 'fixed',
                        'top': '10px',
                        'left': '10px',
                        'z-index': '999999',
                        'background': 'var(--hmg-royal-blue, #332A86)',
                        'color': 'white',
                        'padding': '8px 16px',
                        'text-decoration': 'none',
                        'border-radius': '4px'
                    });
                }).on('blur', function() {
                    $(this).css({
                        'position': 'absolute',
                        'left': '-9999px'
                    });
                });
            }
        },

        /**
         * Initialize TOC progress tracking
         */
        initTOCProgress: function() {
            const $progressBar = $('.hmg-ai-toc-progress-bar');
            if (!$progressBar.length) return;

            $(window).on('scroll', function() {
                const scrollTop = $(window).scrollTop();
                const docHeight = $(document).height() - $(window).height();
                const scrollPercent = (scrollTop / docHeight) * 100;
                
                $progressBar.css('width', Math.min(scrollPercent, 100) + '%');
                
                // Update active TOC items based on scroll position
                let activeSection = null;
                $('[data-hmg-smooth-scroll]').each(function() {
                    const target = $($(this).attr('href'));
                    if (target.length && target.offset().top <= scrollTop + 100) {
                        activeSection = target.attr('id');
                    }
                });
                
                if (activeSection) {
                    $('.hmg-ai-toc-sidebar-item').removeClass('active');
                    $('[data-target="' + activeSection + '"]').addClass('active');
                }
            });
        },

        /**
         * Initialize audio controls
         */
        initAudioControls: function() {
            // Custom play/pause for minimal style
            $(document).on('click', '[data-hmg-audio-toggle]', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $audio = $button.siblings('[data-hmg-audio-source]')[0];
                const $playIcon = $button.find('.hmg-ai-play-icon');
                const $pauseIcon = $button.find('.hmg-ai-pause-icon');
                
                if ($audio.paused) {
                    $audio.play();
                    $playIcon.hide();
                    $pauseIcon.show();
                } else {
                    $audio.pause();
                    $playIcon.show();
                    $pauseIcon.hide();
                }
            });

            // Progress bar updates
            $(document).on('timeupdate', '[data-hmg-audio-source]', function() {
                const audio = this;
                const $progressBar = $(this).siblings().find('[data-hmg-audio-progress]');
                
                if (audio.duration) {
                    const progress = (audio.currentTime / audio.duration) * 100;
                    $progressBar.css('width', progress + '%');
                }
            });

            // Speed controls
            $(document).on('click', '[data-hmg-audio-speed]', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $audio = $button.closest('[data-hmg-component="audio"]').find('audio')[0];
                
                if ($audio) {
                    const speeds = [1, 1.25, 1.5, 2, 0.75];
                    const currentSpeed = $audio.playbackRate;
                    const currentIndex = speeds.indexOf(currentSpeed);
                    const nextIndex = (currentIndex + 1) % speeds.length;
                    const newSpeed = speeds[nextIndex];
                    
                    $audio.playbackRate = newSpeed;
                    $button.text(newSpeed + 'x');
                }
            });

            // Error handling
            $(document).on('error', '.hmg-ai-audio-element', function() {
                console.error('Audio playback error');
                $(this).closest('[data-hmg-component="audio"]')
                       .append('<p style="color: red; text-align: center; margin-top: 10px;">Audio playback error. Please try again later.</p>');
            });
        },

        /**
         * Initialize takeaways interactions
         */
        initTakeawaysInteractions: function() {
            // Add hover effects and animations
            $('.hmg-ai-takeaway-item, .hmg-ai-takeaway-card').on('mouseenter', function() {
                $(this).addClass('hmg-ai-hover');
            }).on('mouseleave', function() {
                $(this).removeClass('hmg-ai-hover');
            });

            // Add click-to-highlight functionality
            $(document).on('click', '.hmg-ai-takeaway-item, .hmg-ai-takeaway-card', function() {
                const $item = $(this);
                $item.toggleClass('hmg-ai-highlighted');
                
                // Optional: Copy to clipboard functionality
                if ($item.hasClass('hmg-ai-highlighted')) {
                    const text = $item.find('.hmg-ai-takeaway-content, .hmg-ai-card-content').text().trim();
                    
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(function() {
                            // Show temporary feedback
                            const $feedback = $('<span class="hmg-ai-copy-feedback">Copied!</span>');
                            $item.append($feedback);
                            
                            setTimeout(function() {
                                $feedback.fadeOut(function() {
                                    $feedback.remove();
                                });
                            }, 2000);
                        });
                    }
                }
            });
        },

        /**
         * Initialize audio player enhancements (legacy)
         */
        initAudioPlayer: function() {
            $('.hmg-ai-audio-player, .hmg-ai-audio-element').each(function() {
                const $player = $(this);
                
                // Add custom controls if needed
                $player.on('loadstart', function() {
                    console.log('Audio loading started');
                });
                
                $player.on('canplay', function() {
                    console.log('Audio can start playing');
                });
                
                $player.on('error', function() {
                    console.error('Audio playback error');
                    $player.closest('.hmg-ai-audio, .hmg-ai-audio-player-container')
                           .append('<p style="color: red;">Audio playback error. Please try again later.</p>');
                });
            });
        },

        /**
         * Handle print-friendly content
         */
        initPrintOptimization: function() {
            // Expand all FAQ items when printing
            window.addEventListener('beforeprint', function() {
                $('.hmg-ai-faq-answer').addClass('active').show();
                $('.hmg-ai-faq-question').addClass('active');
            });
            
            window.addEventListener('afterprint', function() {
                // Restore original state after printing
                $('.hmg-ai-faq-answer').removeClass('active').hide();
                $('.hmg-ai-faq-question').removeClass('active');
            });
        },

        /**
         * Initialize responsive behavior
         */
        initResponsiveBehavior: function() {
            // Handle mobile-specific interactions
            if (window.innerWidth <= 768) {
                // Adjust FAQ behavior for mobile
                $('.hmg-ai-faq-question').css('font-size', '16px');
                
                // Ensure touch targets are large enough
                $('.hmg-ai-faq-question').css('min-height', '44px');
            }
            
            // Handle window resize
            $(window).on('resize', function() {
                // Recalculate any dynamic positioning if needed
            });
        },

        /**
         * Initialize analytics tracking (if needed)
         */
        initAnalytics: function() {
            // Track FAQ interactions
            $(document).on('click', '.hmg-ai-faq-question', function() {
                const questionText = $(this).text().trim();
                
                // Send analytics event (example)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'faq_interaction', {
                        'event_category': 'engagement',
                        'event_label': questionText,
                        'value': 1
                    });
                }
            });
            
            // Track TOC link clicks
            $(document).on('click', '.hmg-ai-toc a', function() {
                const linkText = $(this).text().trim();
                
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'toc_navigation', {
                        'event_category': 'navigation',
                        'event_label': linkText,
                        'value': 1
                    });
                }
            });
            
            // Track audio player interactions
            $(document).on('play', '.hmg-ai-audio-player', function() {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'audio_play', {
                        'event_category': 'engagement',
                        'event_label': 'audio_version',
                        'value': 1
                    });
                }
            });
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        HMGAIPublic.init();
        HMGAIPublic.initAudioPlayer();
        HMGAIPublic.initPrintOptimization();
        HMGAIPublic.initResponsiveBehavior();
        HMGAIPublic.initAnalytics();
    });

})(jQuery); 