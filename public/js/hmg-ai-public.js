/**
 * HMG AI Blog Enhancer - Public JavaScript
 * 
 * Frontend interactions for AI-generated content
 * Sprint 4.2: Enhanced with smooth animations, scroll spy, and WCAG 2.1 AA compliance
 */

(function($) {
    'use strict';
    
    // Add jQuery easing for smooth animations
    $.extend($.easing, {
        easeInOutCubic: function(x, t, b, c, d) {
            if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
            return c / 2 * ((t -= 2) * t * t + 2) + b;
        }
    });

    /**
     * Main public object
     */
    const HMGAIPublic = {
        
        // Configuration
        scrollOffset: 100,
        animationDuration: 400,
        tocActiveClass: 'hmg-ai-toc-active',
        
        /**
         * Initialize public functionality
         */
        init: function() {
            this.initFAQAccordion();
            this.initSmoothScrolling();
            this.initAccessibility();
            this.initTOCScrollSpy();
            this.initTOCProgress();
            this.initAudioControls();
            this.initTakeawaysInteractions();
            this.initKeyboardNavigation();
            this.initSearchFunctionality();
            this.initReducedMotion();
        },

        /**
         * Initialize FAQ accordion with enhanced animations
         */
        initFAQAccordion: function() {
            const self = this;
            
            // Enhanced accordion functionality with cubic-bezier easing
            $(document).on('click', '[data-hmg-faq-toggle]', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $content = $('#' + $button.attr('aria-controls'));
                const isExpanded = $button.attr('aria-expanded') === 'true';
                const $item = $button.closest('.hmg-ai-faq-accordion-item');
                
                // Add animation class
                $item.addClass('hmg-ai-transitioning');
                
                // Toggle states with enhanced animation
                $button.toggleClass('hmg-ai-active');
                $button.attr('aria-expanded', !isExpanded);
                
                // Enhanced slide animation with easing
                if (isExpanded) {
                    $content.css('height', $content.height());
                    $content.animate({
                        height: 0,
                        opacity: 0
                    }, {
                        duration: self.animationDuration,
                        easing: 'easeInOutCubic',
                        complete: function() {
                            $content.removeClass('hmg-ai-active').hide().css({
                                height: '',
                                opacity: ''
                            });
                            $item.removeClass('hmg-ai-transitioning');
                        }
                    });
                } else {
                    $content.show().addClass('hmg-ai-active');
                    const targetHeight = $content.prop('scrollHeight');
                    $content.css({ height: 0, opacity: 0 });
                    $content.animate({
                        height: targetHeight,
                        opacity: 1
                    }, {
                        duration: self.animationDuration,
                        easing: 'easeInOutCubic',
                        complete: function() {
                            $content.css('height', 'auto');
                            $item.removeClass('hmg-ai-transitioning');
                            
                            // Announce to screen readers
                            self.announceToScreenReader('Expanded ' + $button.text());
                        }
                    });
                }
                
                // Close other items with staggered animation
                const $accordion = $button.closest('.hmg-ai-faq-accordion');
                if ($accordion.length) {
                    let delay = 0;
                    $accordion.find('[data-hmg-faq-toggle]').not($button).each(function() {
                        const $otherButton = $(this);
                        const $otherContent = $('#' + $otherButton.attr('aria-controls'));
                        
                        if ($otherButton.attr('aria-expanded') === 'true') {
                            setTimeout(function() {
                                $otherButton.removeClass('hmg-ai-active').attr('aria-expanded', 'false');
                                $otherContent.animate({
                                    height: 0,
                                    opacity: 0
                                }, {
                                    duration: self.animationDuration - 100,
                                    easing: 'easeInOutCubic',
                                    complete: function() {
                                        $otherContent.removeClass('hmg-ai-active').hide().css({
                                            height: '',
                                            opacity: ''
                                        });
                                    }
                                });
                            }, delay);
                            delay += 50; // Stagger animations
                        }
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
        },

        /**
         * Initialize TOC Scroll Spy for active section highlighting
         */
        initTOCScrollSpy: function() {
            const self = this;
            const $tocLinks = $('.hmg-ai-toc a[href^="#"], [data-hmg-smooth-scroll]');
            
            if (!$tocLinks.length) return;
            
            // Create array of sections with their positions
            const updateSectionPositions = function() {
                const sections = [];
                $tocLinks.each(function() {
                    const targetId = $(this).attr('href').replace('#', '');
                    const $target = $('#' + targetId);
                    
                    if ($target.length) {
                        sections.push({
                            id: targetId,
                            offset: $target.offset().top,
                            height: $target.outerHeight(),
                            link: $(this)
                        });
                    }
                });
                return sections.sort((a, b) => a.offset - b.offset);
            };
            
            let sections = updateSectionPositions();
            
            // Update active section on scroll
            const updateActiveSection = function() {
                const scrollTop = $(window).scrollTop() + self.scrollOffset;
                let activeSection = null;
                
                // Find the current section
                for (let i = sections.length - 1; i >= 0; i--) {
                    if (scrollTop >= sections[i].offset - 10) {
                        activeSection = sections[i];
                        break;
                    }
                }
                
                // Update active classes
                $tocLinks.parent().removeClass(self.tocActiveClass);
                if (activeSection) {
                    activeSection.link.parent().addClass(self.tocActiveClass);
                    
                    // Update ARIA current
                    $tocLinks.removeAttr('aria-current');
                    activeSection.link.attr('aria-current', 'true');
                    
                    // Update progress indicator if exists
                    const progress = ((scrollTop - activeSection.offset) / activeSection.height) * 100;
                    $('.hmg-ai-toc-section-progress').css('width', Math.min(100, Math.max(0, progress)) + '%');
                }
            };
            
            // Debounced scroll handler
            let scrollTimer;
            $(window).on('scroll', function() {
                if (scrollTimer) clearTimeout(scrollTimer);
                scrollTimer = setTimeout(updateActiveSection, 10);
            });
            
            // Update on resize
            $(window).on('resize', function() {
                sections = updateSectionPositions();
                updateActiveSection();
            });
            
            // Initial update
            updateActiveSection();
        },

        /**
         * Enhanced keyboard navigation
         */
        initKeyboardNavigation: function() {
            const self = this;
            
            // FAQ keyboard navigation with arrow keys
            $(document).on('keydown', '[data-hmg-faq-toggle], .hmg-ai-faq-question', function(e) {
                const $current = $(this);
                const $items = $('[data-hmg-faq-toggle], .hmg-ai-faq-question');
                const currentIndex = $items.index($current);
                
                switch(e.which) {
                    case 38: // Arrow Up
                        e.preventDefault();
                        if (currentIndex > 0) {
                            $items.eq(currentIndex - 1).focus();
                        }
                        break;
                    case 40: // Arrow Down
                        e.preventDefault();
                        if (currentIndex < $items.length - 1) {
                            $items.eq(currentIndex + 1).focus();
                        }
                        break;
                    case 36: // Home
                        e.preventDefault();
                        $items.first().focus();
                        break;
                    case 35: // End
                        e.preventDefault();
                        $items.last().focus();
                        break;
                }
            });
            
            // TOC keyboard navigation
            $(document).on('keydown', '.hmg-ai-toc a', function(e) {
                const $current = $(this);
                const $links = $('.hmg-ai-toc a');
                const currentIndex = $links.index($current);
                
                switch(e.which) {
                    case 38: // Arrow Up
                    case 37: // Arrow Left
                        e.preventDefault();
                        if (currentIndex > 0) {
                            $links.eq(currentIndex - 1).focus();
                        }
                        break;
                    case 40: // Arrow Down
                    case 39: // Arrow Right
                        e.preventDefault();
                        if (currentIndex < $links.length - 1) {
                            $links.eq(currentIndex + 1).focus();
                        }
                        break;
                }
            });
            
            // Escape key to close expanded FAQ items
            $(document).on('keydown', function(e) {
                if (e.which === 27) { // Escape
                    const $activeItems = $('[aria-expanded="true"]');
                    if ($activeItems.length) {
                        $activeItems.each(function() {
                            if ($(this).is('[data-hmg-faq-toggle], .hmg-ai-faq-question')) {
                                $(this).click();
                            }
                        });
                        $activeItems.first().focus();
                    }
                }
            });
        },

        /**
         * Initialize search functionality for generated content
         */
        initSearchFunctionality: function() {
            const self = this;
            
            // Add search box if there's searchable content
            const $searchableContainers = $('.hmg-ai-faq, .hmg-ai-takeaways');
            
            if ($searchableContainers.length) {
                $searchableContainers.each(function() {
                    const $container = $(this);
                    const $header = $container.find('.hmg-ai-faq-header, .hmg-ai-takeaways-header').first();
                    
                    // Add search input
                    const $searchBox = $('<div class="hmg-ai-search-box">' +
                        '<input type="search" class="hmg-ai-search-input" placeholder="Search..." aria-label="Search content">' +
                        '<span class="hmg-ai-search-icon">🔍</span>' +
                        '</div>');
                    
                    $header.append($searchBox);
                    
                    // Search functionality
                    const $searchInput = $searchBox.find('.hmg-ai-search-input');
                    let searchTimer;
                    
                    $searchInput.on('input', function() {
                        clearTimeout(searchTimer);
                        const query = $(this).val().toLowerCase();
                        
                        searchTimer = setTimeout(function() {
                            if (query.length > 1) {
                                // Search FAQs
                                if ($container.hasClass('hmg-ai-faq')) {
                                    $container.find('.hmg-ai-faq-accordion-item').each(function() {
                                        const $item = $(this);
                                        const text = $item.text().toLowerCase();
                                        
                                        if (text.includes(query)) {
                                            $item.show().addClass('hmg-ai-search-match');
                                            // Highlight matching text
                                            self.highlightText($item, query);
                                        } else {
                                            $item.hide().removeClass('hmg-ai-search-match');
                                        }
                                    });
                                    
                                    // Show message if no results
                                    const visibleItems = $container.find('.hmg-ai-faq-accordion-item:visible').length;
                                    if (visibleItems === 0) {
                                        self.showNoResultsMessage($container);
                                    } else {
                                        self.hideNoResultsMessage($container);
                                    }
                                }
                                
                                // Search takeaways
                                if ($container.hasClass('hmg-ai-takeaways')) {
                                    $container.find('.hmg-ai-takeaway-item').each(function() {
                                        const $item = $(this);
                                        const text = $item.text().toLowerCase();
                                        
                                        if (text.includes(query)) {
                                            $item.show().addClass('hmg-ai-search-match');
                                            self.highlightText($item, query);
                                        } else {
                                            $item.hide().removeClass('hmg-ai-search-match');
                                        }
                                    });
                                }
                            } else {
                                // Show all items if search is cleared
                                $container.find('.hmg-ai-faq-accordion-item, .hmg-ai-takeaway-item').show();
                                self.removeHighlights($container);
                                self.hideNoResultsMessage($container);
                            }
                        }, 300);
                    });
                    
                    // Clear search on escape
                    $searchInput.on('keydown', function(e) {
                        if (e.which === 27) {
                            $(this).val('').trigger('input');
                        }
                    });
                });
            }
        },

        /**
         * Initialize reduced motion preferences
         */
        initReducedMotion: function() {
            // Check if user prefers reduced motion
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (prefersReducedMotion) {
                // Disable animations
                $('body').addClass('hmg-ai-reduced-motion');
                
                // Override animation duration
                this.animationDuration = 0;
                
                // Use simpler show/hide instead of animations
                $.fx.off = true;
            }
            
            // Listen for changes in motion preference
            window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', function(e) {
                if (e.matches) {
                    $('body').addClass('hmg-ai-reduced-motion');
                    $.fx.off = true;
                } else {
                    $('body').removeClass('hmg-ai-reduced-motion');
                    $.fx.off = false;
                }
            });
        },

        /**
         * Announce to screen readers
         */
        announceToScreenReader: function(message) {
            // Create or get announcement region
            let $announcer = $('#hmg-ai-announcer');
            
            if (!$announcer.length) {
                $announcer = $('<div id="hmg-ai-announcer" aria-live="polite" aria-atomic="true" style="position: absolute; left: -9999px;"></div>');
                $('body').append($announcer);
            }
            
            // Clear and set new message
            $announcer.text('');
            setTimeout(function() {
                $announcer.text(message);
            }, 100);
        },

        /**
         * Highlight search text
         */
        highlightText: function($element, query) {
            const self = this;
            self.removeHighlights($element);
            
            $element.find(':not(script)').contents().filter(function() {
                return this.nodeType === 3;
            }).each(function() {
                const text = $(this).text();
                const regex = new RegExp('(' + query + ')', 'gi');
                
                if (regex.test(text)) {
                    const highlighted = text.replace(regex, '<mark class="hmg-ai-highlight">$1</mark>');
                    $(this).replaceWith(highlighted);
                }
            });
        },

        /**
         * Remove text highlights
         */
        removeHighlights: function($element) {
            $element.find('mark.hmg-ai-highlight').each(function() {
                $(this).replaceWith($(this).text());
            });
        },

        /**
         * Show no results message
         */
        showNoResultsMessage: function($container) {
            this.hideNoResultsMessage($container);
            const $message = $('<div class="hmg-ai-no-results" tabindex="-1">No results found. Try a different search term.</div>');
            $container.find('.hmg-ai-faq-content, .hmg-ai-takeaways-content').append($message);
            
            // Scroll to and focus the message
            this.scrollToElement($message, function() {
                $message.focus();
            });
            
            // Announce to screen readers
            this.announceToScreenReader('No results found. Try a different search term.');
        },

        /**
         * Hide no results message
         */
        hideNoResultsMessage: function($container) {
            $container.find('.hmg-ai-no-results').remove();
        },
        
        /**
         * Scroll to element with callback
         */
        scrollToElement: function($element, callback) {
            if (!$element || !$element.length) return;
            
            const offset = $element.offset().top - this.scrollOffset;
            
            $('html, body').animate({
                scrollTop: offset
            }, this.animationDuration, 'easeInOutCubic', function() {
                if (typeof callback === 'function') {
                    callback();
                }
            });
        },
        
        /**
         * Show inline message for content items
         */
        showInlineMessage: function(message, type, $container) {
            // Remove existing messages
            $container.find('.hmg-ai-inline-message').remove();
            
            // Create message element
            const iconMap = {
                'success': 'dashicons-yes',
                'error': 'dashicons-warning',
                'info': 'dashicons-info',
                'warning': 'dashicons-warning'
            };
            
            const $message = $(`
                <div class="hmg-ai-inline-message hmg-ai-message-${type}" tabindex="-1">
                    <span class="dashicons ${iconMap[type] || 'dashicons-info'}"></span>
                    <span class="hmg-ai-message-text">${message}</span>
                </div>
            `);
            
            // Insert message
            $container.append($message);
            
            // Animate in and scroll to it
            $message.hide().fadeIn(this.animationDuration / 2);
            this.scrollToElement($message, function() {
                $message.focus();
            });
            
            // Announce to screen readers
            this.announceToScreenReader(message);
            
            // Auto-hide after delay for success messages
            if (type === 'success') {
                setTimeout(() => {
                    $message.fadeOut(this.animationDuration, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
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