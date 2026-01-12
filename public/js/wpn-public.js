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
         * Initialize
         */
        init: function () {
            if (typeof wpnPublic === 'undefined' || !wpnPublic.notices) {
                return;
            }

            // Try to render immediately
            this.renderNotices();

            // Also try after DOM is fully ready (for page builders that load content dynamically)
            $(document).ready(this.renderNotices.bind(this));

            // And after a short delay for lazy-loaded content
            setTimeout(this.renderNotices.bind(this), 500);
            setTimeout(this.renderNotices.bind(this), 1500);

            // Watch for DOM changes (for AJAX-loaded content)
            this.observeDOMChanges();
        },

        /**
         * Render all notices
         */
        renderNotices: function () {
            const self = this;

            wpnPublic.notices.forEach(function (notice) {
                // Skip if already rendered
                if (self.renderedNotices[notice.id]) {
                    return;
                }

                self.renderNotice(notice);
            });
        },

        /**
         * Render a single notice
         *
         * @param {Object} notice Notice data
         */
        renderNotice: function (notice) {
            // Parse comma-separated selectors
            const selectors = notice.selector.split(',').map(function (s) {
                return s.trim();
            });

            // Try each selector until one works
            for (let i = 0; i < selectors.length; i++) {
                const $container = $(selectors[i]).first();

                if ($container.length) {
                    this.insertNotice($container, notice);
                    this.renderedNotices[notice.id] = true;
                    return;
                }
            }
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

            // Use MutationObserver if available
            if (typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(function (mutations) {
                    let shouldCheck = false;

                    mutations.forEach(function (mutation) {
                        if (mutation.addedNodes.length > 0) {
                            shouldCheck = true;
                        }
                    });

                    if (shouldCheck) {
                        // Debounce the check
                        clearTimeout(self.observerTimeout);
                        self.observerTimeout = setTimeout(function () {
                            self.renderNotices();
                        }, 100);
                    }
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        }
    };

    // Initialize when document is ready
    $(function () {
        WPNPublic.init();
    });

})(jQuery);
