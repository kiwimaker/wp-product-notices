/**
 * WP Product Notices - Public JavaScript
 *
 * Handles JavaScript-based rendering for page builder compatibility.
 *
 * @package WP_Product_Notices
 */

(function ($) {
    'use strict';

    const WPNPublic = {
        /**
         * Track rendered notices to avoid duplicates
         */
        renderedNotices: {},

        /**
         * Retry attempts configuration
         */
        maxRetries: 10,
        retryCount: 0,
        retryDelay: 300,

        /**
         * Initialize
         */
        init: function () {
            if (typeof wpnPublic === 'undefined' || !wpnPublic.notices || !wpnPublic.notices.length) {
                return;
            }

            const self = this;

            // Strategy 1: Try immediately (in case DOM is already ready)
            this.renderNotices();

            // Strategy 2: On DOMContentLoaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    self.renderNotices();
                });
            }

            // Strategy 3: On window load (waits for everything including images)
            window.addEventListener('load', function () {
                self.renderNotices();
                // Extra attempt after load
                setTimeout(function () {
                    self.renderNotices();
                }, 100);
            });

            // Strategy 4: Retry mechanism for lazy-loaded content
            this.startRetryMechanism();

            // Strategy 5: MutationObserver for dynamically added content
            this.observeDOMChanges();

            // Strategy 6: Listen for common page builder events
            this.listenForPageBuilderEvents();
        },

        /**
         * Start retry mechanism
         */
        startRetryMechanism: function () {
            const self = this;

            function attemptRender() {
                self.retryCount++;

                if (self.renderNotices() || self.retryCount >= self.maxRetries) {
                    return; // Success or max retries reached
                }

                // Exponential backoff: 300, 600, 900, 1200...
                const delay = self.retryDelay * self.retryCount;
                setTimeout(attemptRender, delay);
            }

            // Start first retry after initial delay
            setTimeout(attemptRender, this.retryDelay);
        },

        /**
         * Listen for page builder specific events
         */
        listenForPageBuilderEvents: function () {
            const self = this;

            // Flatsome quick view and AJAX events
            $(document).on('flatsome-quickview-loaded', function () {
                self.renderNotices();
            });

            // WooCommerce AJAX events
            $(document.body).on('updated_wc_div', function () {
                self.renderNotices();
            });

            // Generic AJAX complete
            $(document).ajaxComplete(function () {
                setTimeout(function () {
                    self.renderNotices();
                }, 100);
            });

            // Elementor frontend loaded
            $(window).on('elementor/frontend/init', function () {
                setTimeout(function () {
                    self.renderNotices();
                }, 500);
            });
        },

        /**
         * Render all notices
         * @returns {boolean} True if at least one notice was rendered
         */
        renderNotices: function () {
            const self = this;
            let rendered = false;

            wpnPublic.notices.forEach(function (notice) {
                // Skip if already rendered
                if (self.renderedNotices[notice.id]) {
                    return;
                }

                if (self.renderNotice(notice)) {
                    rendered = true;
                }
            });

            return rendered;
        },

        /**
         * Render a single notice
         *
         * @param {Object} notice Notice data
         * @returns {boolean} True if rendered successfully
         */
        renderNotice: function (notice) {
            // Parse comma-separated selectors
            const selectors = notice.selector.split(',').map(function (s) {
                return s.trim();
            });

            // Try each selector until one works
            for (let i = 0; i < selectors.length; i++) {
                const selector = selectors[i];

                // Skip empty selectors
                if (!selector) continue;

                const $container = $(selector).first();

                if ($container.length && $container.is(':visible')) {
                    this.insertNotice($container, notice);
                    this.renderedNotices[notice.id] = true;
                    return true;
                }
            }

            return false;
        },

        /**
         * Insert notice into container
         *
         * @param {jQuery} $container Container element
         * @param {Object} notice Notice data
         */
        insertNotice: function ($container, notice) {
            const $notice = $(notice.html);

            // Add a data attribute to identify JS-rendered notices
            $notice.attr('data-wpn-js-rendered', 'true');

            switch (notice.position) {
                case 'prepend':
                    $container.prepend($notice);
                    break;
                case 'append':
                    $container.append($notice);
                    break;
                case 'before':
                    $container.before($notice);
                    break;
                case 'after':
                    $container.after($notice);
                    break;
                default:
                    $container.prepend($notice);
            }
        },

        /**
         * Observe DOM changes for dynamically loaded content
         */
        observeDOMChanges: function () {
            const self = this;

            if (typeof MutationObserver === 'undefined') {
                return;
            }

            const observer = new MutationObserver(function (mutations) {
                let shouldCheck = false;

                for (let i = 0; i < mutations.length; i++) {
                    const mutation = mutations[i];

                    // Check if meaningful nodes were added
                    if (mutation.addedNodes.length > 0) {
                        for (let j = 0; j < mutation.addedNodes.length; j++) {
                            const node = mutation.addedNodes[j];
                            // Only check element nodes, not text nodes
                            if (node.nodeType === 1) {
                                shouldCheck = true;
                                break;
                            }
                        }
                    }

                    if (shouldCheck) break;
                }

                if (shouldCheck) {
                    // Debounce the check
                    clearTimeout(self.observerTimeout);
                    self.observerTimeout = setTimeout(function () {
                        self.renderNotices();
                    }, 150);
                }
            });

            // Wait for body to be available
            if (document.body) {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                });
            }
        }
    };

    // Initialize as soon as possible
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        WPNPublic.init();
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            WPNPublic.init();
        });
    }

})(jQuery);
