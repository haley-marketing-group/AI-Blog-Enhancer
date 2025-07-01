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
        },

        /**
         * Initialize FAQ accordion functionality
         */
        initFAQAccordion: function() {
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
         * Initialize audio player enhancements
         */
        initAudioPlayer: function() {
            $('.hmg-ai-audio-player').each(function() {
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
                    $player.closest('.hmg-ai-audio-player-container')
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