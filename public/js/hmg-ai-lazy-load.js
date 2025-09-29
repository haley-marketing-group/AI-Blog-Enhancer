/**
 * HMG AI Blog Enhancer - Lazy Loading
 * 
 * Handles lazy loading of heavy components for better performance
 *
 * @since 1.4.0
 */

(function($) {
    'use strict';

    const HMGAILazyLoad = {
        
        /**
         * Intersection Observer instance
         */
        observer: null,
        
        /**
         * Loading queue
         */
        loadingQueue: [],
        
        /**
         * Max concurrent loads
         */
        maxConcurrent: 2,
        
        /**
         * Currently loading count
         */
        currentlyLoading: 0,
        
        /**
         * Initialize lazy loading
         */
        init: function() {
            // Check if IntersectionObserver is supported
            if (!('IntersectionObserver' in window)) {
                // Fallback: load all immediately
                this.loadAllComponents();
                return;
            }
            
            // Create intersection observer
            this.observer = new IntersectionObserver(
                this.handleIntersection.bind(this),
                {
                    rootMargin: '100px', // Load 100px before visible
                    threshold: 0.01
                }
            );
            
            // Observe all lazy load containers
            this.observeComponents();
            
            // Handle print media
            this.handlePrintMedia();
        },
        
        /**
         * Observe lazy load components
         */
        observeComponents: function() {
            const components = document.querySelectorAll('.hmg-ai-lazy-load');
            
            components.forEach(component => {
                this.observer.observe(component);
            });
        },
        
        /**
         * Handle intersection changes
         */
        handleIntersection: function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Stop observing this element
                    this.observer.unobserve(entry.target);
                    
                    // Add to queue
                    this.loadingQueue.push(entry.target);
                    
                    // Process queue
                    this.processQueue();
                }
            });
        },
        
        /**
         * Process loading queue
         */
        processQueue: function() {
            while (this.loadingQueue.length > 0 && this.currentlyLoading < this.maxConcurrent) {
                const component = this.loadingQueue.shift();
                this.loadComponent(component);
            }
        },
        
        /**
         * Load a component
         */
        loadComponent: function(container) {
            const $container = $(container);
            const shortcode = $container.data('shortcode');
            const content = $container.data('content');
            
            if (!content) return;
            
            this.currentlyLoading++;
            
            // Show loading state
            $container.addClass('hmg-ai-loading');
            
            // Decode and process shortcode
            const decodedContent = atob(content);
            
            // Make AJAX request to process shortcode
            $.ajax({
                url: hmg_ai_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hmg_process_shortcode',
                    shortcode: decodedContent,
                    nonce: hmg_ai_ajax.nonce
                },
                success: (response) => {
                    if (response.success && response.data.html) {
                        // Fade out placeholder
                        $container.find('.hmg-ai-lazy-placeholder').fadeOut(200, function() {
                            // Replace with actual content
                            $container.html(response.data.html);
                            
                            // Add loaded class
                            $container.removeClass('hmg-ai-loading').addClass('hmg-ai-loaded');
                            
                            // Trigger custom event
                            $(document).trigger('hmg-ai-component-loaded', [shortcode, container]);
                            
                            // Initialize any JavaScript for the component
                            this.initializeComponent(shortcode, container);
                        }.bind(this));
                    } else {
                        // Fall back to showing the shortcode
                        this.fallbackLoad($container, decodedContent);
                    }
                },
                error: () => {
                    // Fall back to showing the shortcode
                    this.fallbackLoad($container, decodedContent);
                },
                complete: () => {
                    this.currentlyLoading--;
                    this.processQueue();
                }
            });
        },
        
        /**
         * Fallback load method
         */
        fallbackLoad: function($container, content) {
            // For fallback, just process the shortcode client-side if possible
            $container.html('<div class="hmg-ai-fallback">' + content + '</div>');
            $container.removeClass('hmg-ai-loading').addClass('hmg-ai-loaded');
        },
        
        /**
         * Initialize component after loading
         */
        initializeComponent: function(shortcode, container) {
            const $container = $(container);
            
            switch(shortcode) {
                case 'hmg_ai_audio':
                    this.initAudioPlayer($container);
                    break;
                    
                case 'hmg_ai_faq':
                    this.initFAQAccordion($container);
                    break;
                    
                case 'hmg_ai_toc':
                    this.initTOCLinks($container);
                    break;
            }
        },
        
        /**
         * Initialize audio player
         */
        initAudioPlayer: function($container) {
            const $audio = $container.find('audio');
            
            if ($audio.length) {
                // Initialize speed controls
                $container.find('.hmg-ai-speed-btn').on('click', function() {
                    const speed = parseFloat($(this).data('speed'));
                    $audio[0].playbackRate = speed;
                    
                    // Update active state
                    $container.find('.hmg-ai-speed-btn').removeClass('active');
                    $(this).addClass('active');
                });
            }
        },
        
        /**
         * Initialize FAQ accordion
         */
        initFAQAccordion: function($container) {
            $container.find('.hmg-ai-faq-question').on('click', function() {
                const $answer = $(this).next('.hmg-ai-faq-answer');
                
                // Toggle answer
                $answer.slideToggle(300);
                
                // Toggle active class
                $(this).toggleClass('active');
            });
        },
        
        /**
         * Initialize TOC links
         */
        initTOCLinks: function($container) {
            $container.find('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                
                const target = $(this).attr('href');
                const $target = $(target);
                
                if ($target.length) {
                    // Smooth scroll to target
                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 500);
                }
            });
        },
        
        /**
         * Load all components immediately
         */
        loadAllComponents: function() {
            $('.hmg-ai-lazy-load').each((index, element) => {
                setTimeout(() => {
                    this.loadComponent(element);
                }, index * 100); // Stagger loading
            });
        },
        
        /**
         * Handle print media
         */
        handlePrintMedia: function() {
            // Load all components before printing
            window.addEventListener('beforeprint', () => {
                this.loadAllComponents();
            });
        },
        
        /**
         * Preload component
         */
        preloadComponent: function(shortcode) {
            // Find components not yet loaded
            const $components = $(`.hmg-ai-lazy-load[data-shortcode="${shortcode}"]:not(.hmg-ai-loaded)`);
            
            $components.each((index, element) => {
                if (this.observer) {
                    this.observer.unobserve(element);
                }
                this.loadingQueue.push(element);
            });
            
            this.processQueue();
        },
        
        /**
         * Get loading statistics
         */
        getStats: function() {
            return {
                total: $('.hmg-ai-lazy-load').length,
                loaded: $('.hmg-ai-lazy-load.hmg-ai-loaded').length,
                loading: $('.hmg-ai-lazy-load.hmg-ai-loading').length,
                pending: $('.hmg-ai-lazy-load:not(.hmg-ai-loaded):not(.hmg-ai-loading)').length,
                queueLength: this.loadingQueue.length,
                currentlyLoading: this.currentlyLoading
            };
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        HMGAILazyLoad.init();
        
        // Expose for external use
        window.HMGAILazyLoad = HMGAILazyLoad;
    });

})(jQuery);
