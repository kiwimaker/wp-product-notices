<?php
/**
 * Vista del formulario de aviso.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/admin/partials
 *
 * @var WPN_Notice $notice    Instancia del aviso.
 * @var array      $templates Plantillas disponibles.
 * @var array      $icons     Iconos disponibles.
 * @var array      $hooks     Hooks disponibles.
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_new       = empty( $notice->get_id() );
$notice_data  = $notice->to_array();
$styles       = $notice->get_styles();
$schedule     = $notice->get_schedule();
$placement    = $notice->get_placement();
$visibility   = $notice->get_visibility();

// Construir valor del hook para el select.
$hook_value = $placement['hook'];
if ( 'woocommerce_single_product_summary' === $placement['hook'] ) {
    $hook_value = $placement['hook'] . '_' . $placement['priority'];
}
?>

<div class="wpn-admin__header">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-product-notices' ) ); ?>" class="wpn-back-link">
        <span class="dashicons dashicons-arrow-left-alt"></span>
        <?php esc_html_e( 'Volver al listado', 'wp-product-notices' ); ?>
    </a>
</div>

<form id="wpn-notice-form" class="wpn-form" method="post">
    <input type="hidden" name="notice[id]" value="<?php echo esc_attr( $notice->get_id() ); ?>">

    <div class="wpn-form__grid">
        <!-- Columna principal -->
        <div class="wpn-form__main">
            <!-- Información básica -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Información del aviso', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <div class="wpn-field">
                        <label for="wpn-title"><?php esc_html_e( 'Nombre interno', 'wp-product-notices' ); ?></label>
                        <input type="text"
                               id="wpn-title"
                               name="notice[title]"
                               value="<?php echo esc_attr( $notice->get_title() ); ?>"
                               placeholder="<?php esc_attr_e( 'Ej: Aviso Navidad 2026', 'wp-product-notices' ); ?>"
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'Solo visible en el admin para identificar el aviso.', 'wp-product-notices' ); ?></p>
                    </div>

                    <div class="wpn-field">
                        <label for="wpn-text"><?php esc_html_e( 'Texto del aviso', 'wp-product-notices' ); ?></label>
                        <textarea id="wpn-text"
                                  name="notice[content][text]"
                                  rows="3"
                                  class="large-text"
                                  placeholder="<?php esc_attr_e( 'Ej: Trabajaremos para entregarlo cuanto antes. Para productos personalizados el tiempo máximo de entrega es de 7 días laborables.', 'wp-product-notices' ); ?>"><?php echo esc_textarea( $notice->get_text() ); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Diseño -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Diseño', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <!-- Plantilla -->
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Plantilla', 'wp-product-notices' ); ?></label>
                        <div class="wpn-templates">
                            <?php foreach ( $templates as $template_slug => $template_info ) : ?>
                                <label class="wpn-template-option">
                                    <input type="radio"
                                           name="notice[styles][template]"
                                           value="<?php echo esc_attr( $template_slug ); ?>"
                                           <?php checked( $styles['template'], $template_slug ); ?>>
                                    <span class="wpn-template-option__box">
                                        <span class="wpn-template-option__preview wpn-template-option__preview--<?php echo esc_attr( $template_slug ); ?>">
                                            <span class="wpn-template-preview-icon"></span>
                                            <span class="wpn-template-preview-text"></span>
                                        </span>
                                        <span class="wpn-template-option__label"><?php echo esc_html( $template_info['label'] ); ?></span>
                                        <span class="wpn-template-option__desc"><?php echo esc_html( $template_info['description'] ); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Icono -->
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Icono', 'wp-product-notices' ); ?></label>
                        <div class="wpn-icons-selector">
                            <?php foreach ( $icons as $category_slug => $category ) : ?>
                                <div class="wpn-icons-category">
                                    <h4><?php echo esc_html( $category['label'] ); ?></h4>
                                    <div class="wpn-icons-grid">
                                        <?php foreach ( $category['icons'] as $icon_slug => $icon_info ) : ?>
                                            <label class="wpn-icon-option" title="<?php echo esc_attr( $icon_info['label'] ); ?>">
                                                <input type="radio"
                                                       name="notice[content][icon]"
                                                       value="<?php echo esc_attr( $icon_slug ); ?>"
                                                       <?php checked( $notice->get_icon(), $icon_slug ); ?>>
                                                <span class="wpn-icon-option__box">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <?php echo $icon_info['path']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                    </svg>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Colores -->
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Colores', 'wp-product-notices' ); ?></label>
                        <div class="wpn-colors-grid">
                            <div class="wpn-color-field">
                                <label for="wpn-bg-color"><?php esc_html_e( 'Fondo', 'wp-product-notices' ); ?></label>
                                <input type="text"
                                       id="wpn-bg-color"
                                       name="notice[styles][background]"
                                       value="<?php echo esc_attr( $styles['background'] ); ?>"
                                       class="wpn-color-picker"
                                       data-default-color="#e8f5e9">
                            </div>
                            <div class="wpn-color-field">
                                <label for="wpn-border-color"><?php esc_html_e( 'Borde', 'wp-product-notices' ); ?></label>
                                <input type="text"
                                       id="wpn-border-color"
                                       name="notice[styles][border_color]"
                                       value="<?php echo esc_attr( $styles['border_color'] ); ?>"
                                       class="wpn-color-picker"
                                       data-default-color="#4caf50">
                            </div>
                            <div class="wpn-color-field">
                                <label for="wpn-text-color"><?php esc_html_e( 'Texto', 'wp-product-notices' ); ?></label>
                                <input type="text"
                                       id="wpn-text-color"
                                       name="notice[styles][text_color]"
                                       value="<?php echo esc_attr( $styles['text_color'] ); ?>"
                                       class="wpn-color-picker"
                                       data-default-color="#1b5e20">
                            </div>
                            <div class="wpn-color-field">
                                <label for="wpn-icon-color"><?php esc_html_e( 'Icono', 'wp-product-notices' ); ?></label>
                                <input type="text"
                                       id="wpn-icon-color"
                                       name="notice[styles][icon_color]"
                                       value="<?php echo esc_attr( $styles['icon_color'] ); ?>"
                                       class="wpn-color-picker"
                                       data-default-color="#2e7d32">
                            </div>
                        </div>
                    </div>

                    <!-- Presets de colores -->
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Presets de colores', 'wp-product-notices' ); ?></label>
                        <div class="wpn-color-presets">
                            <button type="button" class="wpn-color-preset" data-preset="green" title="<?php esc_attr_e( 'Verde', 'wp-product-notices' ); ?>">
                                <span style="background: #e8f5e9; border-color: #4caf50;"></span>
                            </button>
                            <button type="button" class="wpn-color-preset" data-preset="blue" title="<?php esc_attr_e( 'Azul', 'wp-product-notices' ); ?>">
                                <span style="background: #e3f2fd; border-color: #2196f3;"></span>
                            </button>
                            <button type="button" class="wpn-color-preset" data-preset="orange" title="<?php esc_attr_e( 'Naranja', 'wp-product-notices' ); ?>">
                                <span style="background: #fff3e0; border-color: #ff9800;"></span>
                            </button>
                            <button type="button" class="wpn-color-preset" data-preset="red" title="<?php esc_attr_e( 'Rojo', 'wp-product-notices' ); ?>">
                                <span style="background: #ffebee; border-color: #f44336;"></span>
                            </button>
                            <button type="button" class="wpn-color-preset" data-preset="purple" title="<?php esc_attr_e( 'Morado', 'wp-product-notices' ); ?>">
                                <span style="background: #f3e5f5; border-color: #9c27b0;"></span>
                            </button>
                            <button type="button" class="wpn-color-preset" data-preset="gray" title="<?php esc_attr_e( 'Gris', 'wp-product-notices' ); ?>">
                                <span style="background: #f5f5f5; border-color: #9e9e9e;"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visibilidad -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Visibilidad', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Mostrar en', 'wp-product-notices' ); ?></label>
                        <div class="wpn-visibility-options">
                            <label class="wpn-radio-option">
                                <input type="radio"
                                       name="notice[visibility][type]"
                                       value="all"
                                       <?php checked( $visibility['type'], 'all' ); ?>>
                                <span><?php esc_html_e( 'Todos los productos', 'wp-product-notices' ); ?></span>
                            </label>
                            <label class="wpn-radio-option">
                                <input type="radio"
                                       name="notice[visibility][type]"
                                       value="categories"
                                       <?php checked( $visibility['type'], 'categories' ); ?>>
                                <span><?php esc_html_e( 'Categorías específicas', 'wp-product-notices' ); ?></span>
                            </label>
                            <label class="wpn-radio-option">
                                <input type="radio"
                                       name="notice[visibility][type]"
                                       value="products"
                                       <?php checked( $visibility['type'], 'products' ); ?>>
                                <span><?php esc_html_e( 'Productos específicos', 'wp-product-notices' ); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Selector de categorías -->
                    <div class="wpn-field wpn-field--categories" style="<?php echo 'categories' !== $visibility['type'] ? 'display:none;' : ''; ?>">
                        <label for="wpn-categories"><?php esc_html_e( 'Seleccionar categorías', 'wp-product-notices' ); ?></label>
                        <select id="wpn-categories"
                                name="notice[visibility][categories][]"
                                multiple
                                class="wpn-select2-static"
                                data-placeholder="<?php esc_attr_e( 'Selecciona categorías...', 'wp-product-notices' ); ?>">
                            <?php
                            $all_categories = get_terms(
                                array(
                                    'taxonomy'   => 'product_cat',
                                    'hide_empty' => false,
                                    'orderby'    => 'name',
                                    'order'      => 'ASC',
                                )
                            );
                            if ( ! is_wp_error( $all_categories ) ) {
                                foreach ( $all_categories as $cat ) {
                                    $selected = in_array( $cat->term_id, (array) $visibility['categories'], false ) ? 'selected' : '';
                                    printf(
                                        '<option value="%d" %s>%s</option>',
                                        esc_attr( $cat->term_id ),
                                        $selected,
                                        esc_html( $cat->name )
                                    );
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Selector de productos -->
                    <div class="wpn-field wpn-field--products" style="<?php echo 'products' !== $visibility['type'] ? 'display:none;' : ''; ?>">
                        <label for="wpn-products"><?php esc_html_e( 'Seleccionar productos', 'wp-product-notices' ); ?></label>
                        <select id="wpn-products"
                                name="notice[visibility][products][]"
                                multiple
                                class="wpn-select2-static"
                                data-placeholder="<?php esc_attr_e( 'Selecciona productos...', 'wp-product-notices' ); ?>">
                            <?php
                            $all_products = get_posts(
                                array(
                                    'post_type'      => 'product',
                                    'posts_per_page' => -1,
                                    'post_status'    => 'publish',
                                    'orderby'        => 'title',
                                    'order'          => 'ASC',
                                )
                            );
                            foreach ( $all_products as $prod ) {
                                $selected = in_array( $prod->ID, (array) $visibility['products'], false ) ? 'selected' : '';
                                printf(
                                    '<option value="%d" %s>%s</option>',
                                    esc_attr( $prod->ID ),
                                    $selected,
                                    esc_html( $prod->post_title )
                                );
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Posición -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Posición', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <!-- Método de renderizado -->
                    <div class="wpn-field">
                        <label><?php esc_html_e( 'Método de renderizado', 'wp-product-notices' ); ?></label>
                        <div class="wpn-render-method-options">
                            <label class="wpn-radio-option">
                                <input type="radio"
                                       name="notice[placement][render_method]"
                                       value="php"
                                       <?php checked( $placement['render_method'] ?? 'php', 'php' ); ?>>
                                <span>
                                    <strong><?php esc_html_e( 'PHP (Hooks de WooCommerce)', 'wp-product-notices' ); ?></strong>
                                    <small><?php esc_html_e( 'Funciona con temas estándar de WooCommerce', 'wp-product-notices' ); ?></small>
                                </span>
                            </label>
                            <label class="wpn-radio-option">
                                <input type="radio"
                                       name="notice[placement][render_method]"
                                       value="javascript"
                                       <?php checked( $placement['render_method'] ?? 'php', 'javascript' ); ?>>
                                <span>
                                    <strong><?php esc_html_e( 'JavaScript (Compatible con Page Builders)', 'wp-product-notices' ); ?></strong>
                                    <small><?php esc_html_e( 'Funciona con Flatsome, Elementor, Divi y otros constructores', 'wp-product-notices' ); ?></small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Opciones PHP -->
                    <div class="wpn-field wpn-field--php-options" style="<?php echo ( $placement['render_method'] ?? 'php' ) !== 'php' ? 'display:none;' : ''; ?>">
                        <label for="wpn-hook"><?php esc_html_e( 'Ubicación (Hook de WooCommerce)', 'wp-product-notices' ); ?></label>
                        <select id="wpn-hook" name="notice[placement][hook]" class="regular-text">
                            <?php foreach ( $hooks as $hook_key => $hook_label ) : ?>
                                <option value="<?php echo esc_attr( $hook_key ); ?>" <?php selected( $hook_value, $hook_key ); ?>>
                                    <?php echo esc_html( $hook_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Opciones JavaScript -->
                    <div class="wpn-field wpn-field--js-options" style="<?php echo ( $placement['render_method'] ?? 'php' ) !== 'javascript' ? 'display:none;' : ''; ?>">
                        <label for="wpn-js-selector"><?php esc_html_e( 'Selector CSS del contenedor', 'wp-product-notices' ); ?></label>
                        <input type="text"
                               id="wpn-js-selector"
                               name="notice[placement][js_selector]"
                               value="<?php echo esc_attr( $placement['js_selector'] ?? '.product-info, .product-summary, .summary.entry-summary' ); ?>"
                               class="large-text"
                               placeholder=".product-info, .product-summary">
                        <p class="description">
                            <?php esc_html_e( 'El aviso se insertará en el primer elemento que coincida. Usa la coma para múltiples selectores.', 'wp-product-notices' ); ?>
                            <br>
                            <strong><?php esc_html_e( 'Selectores comunes:', 'wp-product-notices' ); ?></strong>
                            <br>
                            • <code>.product-info</code> - Flatsome
                            <br>
                            • <code>.summary.entry-summary</code> - WooCommerce estándar
                            <br>
                            • <code>.elementor-widget-woocommerce-product-title</code> - Elementor
                        </p>
                    </div>

                    <div class="wpn-field wpn-field--js-options" style="<?php echo ( $placement['render_method'] ?? 'php' ) !== 'javascript' ? 'display:none;' : ''; ?>">
                        <label for="wpn-js-position"><?php esc_html_e( 'Posición respecto al contenedor', 'wp-product-notices' ); ?></label>
                        <select id="wpn-js-position" name="notice[placement][js_position]" class="regular-text">
                            <option value="prepend" <?php selected( $placement['js_position'] ?? 'prepend', 'prepend' ); ?>><?php esc_html_e( 'Al inicio (prepend)', 'wp-product-notices' ); ?></option>
                            <option value="append" <?php selected( $placement['js_position'] ?? 'prepend', 'append' ); ?>><?php esc_html_e( 'Al final (append)', 'wp-product-notices' ); ?></option>
                            <option value="before" <?php selected( $placement['js_position'] ?? 'prepend', 'before' ); ?>><?php esc_html_e( 'Antes del elemento (before)', 'wp-product-notices' ); ?></option>
                            <option value="after" <?php selected( $placement['js_position'] ?? 'prepend', 'after' ); ?>><?php esc_html_e( 'Después del elemento (after)', 'wp-product-notices' ); ?></option>
                        </select>
                    </div>

                    <!-- Shortcode -->
                    <?php if ( ! $is_new && $notice->get_id() ) : ?>
                    <div class="wpn-field wpn-field--shortcode">
                        <label><?php esc_html_e( 'Shortcode (alternativa manual)', 'wp-product-notices' ); ?></label>
                        <div class="wpn-shortcode-box">
                            <code id="wpn-shortcode">[wpn_notice id="<?php echo esc_attr( $notice->get_id() ); ?>"]</code>
                            <button type="button" class="button button-small wpn-copy-shortcode" data-clipboard-target="#wpn-shortcode">
                                <?php esc_html_e( 'Copiar', 'wp-product-notices' ); ?>
                            </button>
                        </div>
                        <p class="description"><?php esc_html_e( 'Puedes usar este shortcode en cualquier parte del tema o en un page builder.', 'wp-product-notices' ); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="wpn-form__sidebar">
            <!-- Estado y guardar -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Publicar', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <div class="wpn-field">
                        <label for="wpn-status"><?php esc_html_e( 'Estado', 'wp-product-notices' ); ?></label>
                        <select id="wpn-status" name="notice[status]">
                            <option value="active" <?php selected( $notice->get_status(), 'active' ); ?>><?php esc_html_e( 'Activo', 'wp-product-notices' ); ?></option>
                            <option value="inactive" <?php selected( $notice->get_status(), 'inactive' ); ?>><?php esc_html_e( 'Inactivo', 'wp-product-notices' ); ?></option>
                        </select>
                    </div>
                </div>
                <div class="wpn-card__footer">
                    <button type="submit" class="button button-primary button-large wpn-save-btn">
                        <?php echo $is_new ? esc_html__( 'Crear aviso', 'wp-product-notices' ) : esc_html__( 'Guardar cambios', 'wp-product-notices' ); ?>
                    </button>
                </div>
            </div>

            <!-- Programación -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Programación', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <div class="wpn-field">
                        <label for="wpn-start-date"><?php esc_html_e( 'Fecha de inicio', 'wp-product-notices' ); ?></label>
                        <div class="wpn-datetime">
                            <input type="date"
                                   id="wpn-start-date"
                                   name="notice[schedule][start_date]"
                                   value="<?php echo esc_attr( $schedule['start_date'] ); ?>">
                            <input type="time"
                                   id="wpn-start-time"
                                   name="notice[schedule][start_time]"
                                   value="<?php echo esc_attr( $schedule['start_time'] ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Dejar vacío para mostrar desde ahora.', 'wp-product-notices' ); ?></p>
                    </div>

                    <div class="wpn-field">
                        <label for="wpn-end-date"><?php esc_html_e( 'Fecha de fin', 'wp-product-notices' ); ?></label>
                        <div class="wpn-datetime">
                            <input type="date"
                                   id="wpn-end-date"
                                   name="notice[schedule][end_date]"
                                   value="<?php echo esc_attr( $schedule['end_date'] ); ?>">
                            <input type="time"
                                   id="wpn-end-time"
                                   name="notice[schedule][end_time]"
                                   value="<?php echo esc_attr( $schedule['end_time'] ); ?>">
                        </div>
                        <p class="description"><?php esc_html_e( 'Dejar vacío para mostrar indefinidamente.', 'wp-product-notices' ); ?></p>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="wpn-card">
                <div class="wpn-card__header">
                    <h2><?php esc_html_e( 'Vista previa', 'wp-product-notices' ); ?></h2>
                </div>
                <div class="wpn-card__body">
                    <div id="wpn-preview" class="wpn-preview">
                        <p class="wpn-preview__placeholder"><?php esc_html_e( 'Escribe un texto para ver la vista previa.', 'wp-product-notices' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
