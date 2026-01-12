/**
 * WP Product Notices - Admin JavaScript
 *
 * @package WP_Product_Notices
 */

(function ($) {
    'use strict';

    const WPNAdmin = {
        /**
         * Initialize admin functionality
         */
        init: function () {
            this.initColorPickers();
            this.initSelect2();
            this.initSortable();
            this.bindEvents();
            this.initPreview();
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function () {
            $('.wpn-color-picker').wpColorPicker({
                change: function () {
                    WPNAdmin.updatePreview();
                },
                clear: function () {
                    WPNAdmin.updatePreview();
                }
            });
        },

        /**
         * Initialize Select2 for product/category selectors (static, no AJAX)
         */
        initSelect2: function () {
            $('.wpn-select2-static').each(function () {
                const $select = $(this);

                $select.select2({
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    placeholder: $select.data('placeholder') || 'Selecciona...',
                    language: {
                        noResults: function () {
                            return 'No se encontraron resultados';
                        },
                        searching: function () {
                            return 'Buscando...';
                        }
                    }
                });
            });
        },

        /**
         * Initialize sortable table
         */
        initSortable: function () {
            $('#wpn-notices-list').sortable({
                handle: '.wpn-drag-handle',
                placeholder: 'ui-sortable-placeholder',
                update: function () {
                    WPNAdmin.saveOrder();
                }
            });
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            // Info box toggle (Flatsome guide)
            $('#wpn-flatsome-toggle').on('click', this.handleInfoBoxToggle.bind(this));

            // Form submission
            $('#wpn-notice-form').on('submit', this.handleFormSubmit.bind(this));

            // Toggle status
            $(document).on('change', '[data-action="toggle-status"]', this.handleToggleStatus.bind(this));

            // Delete notice
            $(document).on('click', '[data-action="delete"]', this.handleDelete.bind(this));

            // Duplicate notice
            $(document).on('click', '[data-action="duplicate"]', this.handleDuplicate.bind(this));

            // Visibility type change
            $('input[name="notice[visibility][type]"]').on('change', this.handleVisibilityChange.bind(this));

            // Render method change
            $('input[name="notice[placement][render_method]"]').on('change', this.handleRenderMethodChange.bind(this));

            // Copy shortcode
            $(document).on('click', '.wpn-copy-shortcode', this.handleCopyShortcode.bind(this));

            // Color presets
            $('.wpn-color-preset').on('click', this.handleColorPreset.bind(this));

            // Preview updates
            $('#wpn-text, input[name="notice[content][icon]"], input[name="notice[styles][template]"]').on('change input', function () {
                WPNAdmin.updatePreview();
            });
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function (e) {
            e.preventDefault();

            const $form = $(e.target);
            const $button = $form.find('.wpn-save-btn');
            const originalText = $button.text();

            $button.prop('disabled', true).text(wpnAdmin.strings.saving);

            const formData = this.serializeForm($form);

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_save_notice',
                    nonce: wpnAdmin.nonce,
                    notice: formData
                },
                success: function (response) {
                    if (response.success) {
                        $button.text(wpnAdmin.strings.saved);
                        setTimeout(function () {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                        }, 500);
                    } else {
                        alert(response.data.message || wpnAdmin.strings.error);
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function () {
                    alert(wpnAdmin.strings.error);
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Serialize form data into nested object
         */
        serializeForm: function ($form) {
            const data = {};
            const formArray = $form.serializeArray();

            formArray.forEach(function (item) {
                const match = item.name.match(/notice\[([^\]]+)\](?:\[([^\]]+)\])?(?:\[\])?/);
                if (match) {
                    const key1 = match[1];
                    const key2 = match[2];

                    if (key2) {
                        if (!data[key1]) {
                            data[key1] = {};
                        }
                        if (item.name.endsWith('[]')) {
                            if (!data[key1][key2]) {
                                data[key1][key2] = [];
                            }
                            data[key1][key2].push(item.value);
                        } else {
                            data[key1][key2] = item.value;
                        }
                    } else {
                        data[key1] = item.value;
                    }
                }
            });

            return data;
        },

        /**
         * Handle toggle status
         */
        handleToggleStatus: function (e) {
            const $toggle = $(e.target);
            const noticeId = $toggle.data('notice-id');
            const status = $toggle.is(':checked') ? 'active' : 'inactive';

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_toggle_status',
                    nonce: wpnAdmin.nonce,
                    notice_id: noticeId,
                    status: status
                }
            });
        },

        /**
         * Handle delete
         */
        handleDelete: function (e) {
            e.preventDefault();

            if (!confirm(wpnAdmin.strings.confirmDelete)) {
                return;
            }

            const $button = $(e.currentTarget);
            const noticeId = $button.data('notice-id');
            const $row = $button.closest('tr');

            $row.addClass('wpn-loading');

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_delete_notice',
                    nonce: wpnAdmin.nonce,
                    notice_id: noticeId
                },
                success: function (response) {
                    if (response.success) {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                            if ($('#wpn-notices-list tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        $row.removeClass('wpn-loading');
                        alert(response.data.message);
                    }
                },
                error: function () {
                    $row.removeClass('wpn-loading');
                    alert(wpnAdmin.strings.error);
                }
            });
        },

        /**
         * Handle duplicate
         */
        handleDuplicate: function (e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const noticeId = $button.data('notice-id');

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_duplicate_notice',
                    nonce: wpnAdmin.nonce,
                    notice_id: noticeId
                },
                success: function (response) {
                    if (response.success && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert(response.data.message || wpnAdmin.strings.error);
                    }
                },
                error: function () {
                    alert(wpnAdmin.strings.error);
                }
            });
        },

        /**
         * Save order after drag and drop
         */
        saveOrder: function () {
            const order = [];
            $('#wpn-notices-list tr').each(function () {
                order.push($(this).data('notice-id'));
            });

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_reorder_notices',
                    nonce: wpnAdmin.nonce,
                    order: order
                }
            });
        },

        /**
         * Handle visibility type change
         */
        handleVisibilityChange: function (e) {
            const value = $(e.target).val();

            $('.wpn-field--categories, .wpn-field--products').hide();

            if (value === 'categories') {
                $('.wpn-field--categories').show();
            } else if (value === 'products') {
                $('.wpn-field--products').show();
            }
        },

        /**
         * Handle render method change
         */
        handleRenderMethodChange: function (e) {
            const value = $(e.target).val();

            if (value === 'php') {
                $('.wpn-field--php-options').show();
                $('.wpn-field--js-options').hide();
            } else {
                $('.wpn-field--php-options').hide();
                $('.wpn-field--js-options').show();
            }
        },

        /**
         * Handle copy shortcode
         */
        handleCopyShortcode: function (e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const target = $button.data('clipboard-target');
            const text = $(target).text();

            navigator.clipboard.writeText(text).then(function () {
                const originalText = $button.text();
                $button.text('¡Copiado!');
                setTimeout(function () {
                    $button.text(originalText);
                }, 2000);
            });
        },

        /**
         * Handle info box toggle
         */
        handleInfoBoxToggle: function (e) {
            const $header = $(e.currentTarget);
            const $box = $header.closest('.wpn-info-box');
            const $content = $box.find('.wpn-info-box__content');

            $box.toggleClass('wpn-info-box--open');
            $content.slideToggle(200);
        },

        /**
         * Handle color preset selection
         */
        handleColorPreset: function (e) {
            e.preventDefault();

            const preset = $(e.currentTarget).data('preset');
            const presets = {
                green: {
                    background: '#e8f5e9',
                    border_color: '#4caf50',
                    text_color: '#1b5e20',
                    icon_color: '#2e7d32'
                },
                blue: {
                    background: '#e3f2fd',
                    border_color: '#2196f3',
                    text_color: '#0d47a1',
                    icon_color: '#1565c0'
                },
                orange: {
                    background: '#fff3e0',
                    border_color: '#ff9800',
                    text_color: '#e65100',
                    icon_color: '#ef6c00'
                },
                red: {
                    background: '#ffebee',
                    border_color: '#f44336',
                    text_color: '#b71c1c',
                    icon_color: '#c62828'
                },
                purple: {
                    background: '#f3e5f5',
                    border_color: '#9c27b0',
                    text_color: '#4a148c',
                    icon_color: '#6a1b9a'
                },
                gray: {
                    background: '#f5f5f5',
                    border_color: '#9e9e9e',
                    text_color: '#424242',
                    icon_color: '#616161'
                }
            };

            if (presets[preset]) {
                const colors = presets[preset];

                $('#wpn-bg-color').wpColorPicker('color', colors.background);
                $('#wpn-border-color').wpColorPicker('color', colors.border_color);
                $('#wpn-text-color').wpColorPicker('color', colors.text_color);
                $('#wpn-icon-color').wpColorPicker('color', colors.icon_color);

                this.updatePreview();
            }
        },

        /**
         * Initialize preview
         */
        initPreview: function () {
            if ($('#wpn-preview').length) {
                this.updatePreview();
            }
        },

        /**
         * Update preview via AJAX
         */
        updatePreview: function () {
            const $preview = $('#wpn-preview');
            if (!$preview.length) return;

            const text = $('#wpn-text').val();
            if (!text) {
                $preview.html('<p class="wpn-preview__placeholder">' + 'Escribe un texto para ver la vista previa.' + '</p>');
                return;
            }

            const data = {
                content: {
                    text: text,
                    icon: $('input[name="notice[content][icon]"]:checked').val()
                },
                styles: {
                    template: $('input[name="notice[styles][template]"]:checked').val(),
                    background: $('#wpn-bg-color').val(),
                    border_color: $('#wpn-border-color').val(),
                    text_color: $('#wpn-text-color').val(),
                    icon_color: $('#wpn-icon-color').val()
                }
            };

            $.ajax({
                url: wpnAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpn_preview_notice',
                    nonce: wpnAdmin.nonce,
                    notice: data
                },
                success: function (response) {
                    if (response.success) {
                        $preview.html(response.data.html);
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        WPNAdmin.init();
    });

})(jQuery);
