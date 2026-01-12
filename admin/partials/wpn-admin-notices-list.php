<?php
/**
 * Vista del listado de avisos.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/admin/partials
 *
 * @var array $notices Array de objetos WPN_Notice.
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wpn-admin__header">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-product-notices&action=new' ) ); ?>" class="button button-primary">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e( 'Nuevo Aviso', 'wp-product-notices' ); ?>
    </a>
</div>

<!-- Guía de integración con Flatsome -->
<div class="wpn-info-box wpn-info-box--flatsome">
    <div class="wpn-info-box__header">
        <span class="dashicons dashicons-info-outline"></span>
        <strong><?php esc_html_e( '¿Usas Flatsome u otro Page Builder?', 'wp-product-notices' ); ?></strong>
    </div>
    <div class="wpn-info-box__content">
        <p><?php esc_html_e( 'Si los avisos no aparecen con el método PHP (hooks), sigue estos pasos para integrarlos con Flatsome:', 'wp-product-notices' ); ?></p>
        <ol>
            <li><?php esc_html_e( 'Crea un aviso y copia el shortcode que aparece en la sección "Posición"', 'wp-product-notices' ); ?> <code>[wpn_notice id="..."]</code></li>
            <li><?php esc_html_e( 'Ve a', 'wp-product-notices' ); ?> <strong>Flatsome → UX Blocks</strong> <?php esc_html_e( 'y crea un nuevo UX Block', 'wp-product-notices' ); ?></li>
            <li><?php esc_html_e( 'Pega el shortcode del aviso en el contenido del UX Block', 'wp-product-notices' ); ?></li>
            <li><?php esc_html_e( 'Ve a', 'wp-product-notices' ); ?> <strong>Flatsome → Theme Options → WooCommerce → Product Page</strong></li>
            <li><?php esc_html_e( 'Añade un elemento "Block" en la posición deseada del template de producto', 'wp-product-notices' ); ?></li>
            <li><?php esc_html_e( 'Selecciona el UX Block que creaste', 'wp-product-notices' ); ?></li>
        </ol>
        <p class="wpn-info-box__tip">
            <span class="dashicons dashicons-lightbulb"></span>
            <em><?php esc_html_e( 'Alternativa: También puedes usar el método "JavaScript" en la sección Posición del aviso, que funciona automáticamente con la mayoría de page builders.', 'wp-product-notices' ); ?></em>
        </p>
    </div>
</div>

<?php if ( empty( $notices ) ) : ?>
    <div class="wpn-admin__empty">
        <div class="wpn-admin__empty-icon">
            <span class="dashicons dashicons-megaphone"></span>
        </div>
        <h2><?php esc_html_e( 'No hay avisos todavía', 'wp-product-notices' ); ?></h2>
        <p><?php esc_html_e( 'Crea tu primer aviso para mostrar información importante en las páginas de producto.', 'wp-product-notices' ); ?></p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-product-notices&action=new' ) ); ?>" class="button button-primary button-hero">
            <?php esc_html_e( 'Crear primer aviso', 'wp-product-notices' ); ?>
        </a>
    </div>
<?php else : ?>
    <table class="wp-list-table widefat fixed striped wpn-notices-table">
        <thead>
            <tr>
                <th class="wpn-col-order" style="width: 30px;"></th>
                <th class="wpn-col-status" style="width: 60px;"><?php esc_html_e( 'Estado', 'wp-product-notices' ); ?></th>
                <th class="wpn-col-title"><?php esc_html_e( 'Aviso', 'wp-product-notices' ); ?></th>
                <th class="wpn-col-position"><?php esc_html_e( 'Posición', 'wp-product-notices' ); ?></th>
                <th class="wpn-col-visibility"><?php esc_html_e( 'Visibilidad', 'wp-product-notices' ); ?></th>
                <th class="wpn-col-schedule"><?php esc_html_e( 'Programación', 'wp-product-notices' ); ?></th>
                <th class="wpn-col-actions" style="width: 120px;"><?php esc_html_e( 'Acciones', 'wp-product-notices' ); ?></th>
            </tr>
        </thead>
        <tbody id="wpn-notices-list">
            <?php foreach ( $notices as $notice ) : ?>
                <tr data-notice-id="<?php echo esc_attr( $notice->get_id() ); ?>">
                    <td class="wpn-col-order">
                        <span class="wpn-drag-handle dashicons dashicons-menu"></span>
                    </td>
                    <td class="wpn-col-status">
                        <label class="wpn-toggle">
                            <input type="checkbox"
                                   class="wpn-toggle__input"
                                   data-action="toggle-status"
                                   data-notice-id="<?php echo esc_attr( $notice->get_id() ); ?>"
                                   <?php checked( $notice->is_active() ); ?>>
                            <span class="wpn-toggle__slider"></span>
                        </label>
                    </td>
                    <td class="wpn-col-title">
                        <strong>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-product-notices&action=edit&notice_id=' . $notice->get_id() ) ); ?>">
                                <?php echo esc_html( $notice->get_title() ? $notice->get_title() : __( '(Sin título)', 'wp-product-notices' ) ); ?>
                            </a>
                        </strong>
                        <div class="wpn-notice-preview">
                            <?php echo wp_kses_post( wp_trim_words( $notice->get_text(), 15, '...' ) ); ?>
                        </div>
                    </td>
                    <td class="wpn-col-position">
                        <span class="wpn-badge wpn-badge--info">
                            <?php echo esc_html( wpn_get_hook_label( $notice->get_hook(), $notice->get_priority() ) ); ?>
                        </span>
                    </td>
                    <td class="wpn-col-visibility">
                        <?php
                        $visibility_type = $notice->get_visibility_type();
                        switch ( $visibility_type ) {
                            case 'all':
                                echo '<span class="wpn-badge wpn-badge--success">' . esc_html__( 'Todos', 'wp-product-notices' ) . '</span>';
                                break;
                            case 'categories':
                                $cats = $notice->get_visibility()['categories'];
                                echo '<span class="wpn-badge wpn-badge--warning">' . esc_html( count( $cats ) ) . ' ' . esc_html__( 'categorías', 'wp-product-notices' ) . '</span>';
                                break;
                            case 'products':
                                $prods = $notice->get_visibility()['products'];
                                echo '<span class="wpn-badge wpn-badge--warning">' . esc_html( count( $prods ) ) . ' ' . esc_html__( 'productos', 'wp-product-notices' ) . '</span>';
                                break;
                        }
                        ?>
                    </td>
                    <td class="wpn-col-schedule">
                        <?php
                        $schedule = $notice->get_schedule();
                        if ( ! empty( $schedule['start_date'] ) || ! empty( $schedule['end_date'] ) ) {
                            if ( ! empty( $schedule['start_date'] ) && ! empty( $schedule['end_date'] ) ) {
                                echo '<span class="wpn-schedule">';
                                echo esc_html( date_i18n( 'd/m/Y', strtotime( $schedule['start_date'] ) ) );
                                echo ' - ';
                                echo esc_html( date_i18n( 'd/m/Y', strtotime( $schedule['end_date'] ) ) );
                                echo '</span>';
                            } elseif ( ! empty( $schedule['start_date'] ) ) {
                                echo '<span class="wpn-schedule">';
                                echo esc_html__( 'Desde ', 'wp-product-notices' );
                                echo esc_html( date_i18n( 'd/m/Y', strtotime( $schedule['start_date'] ) ) );
                                echo '</span>';
                            } else {
                                echo '<span class="wpn-schedule">';
                                echo esc_html__( 'Hasta ', 'wp-product-notices' );
                                echo esc_html( date_i18n( 'd/m/Y', strtotime( $schedule['end_date'] ) ) );
                                echo '</span>';
                            }
                        } else {
                            echo '<span class="wpn-schedule wpn-schedule--always">' . esc_html__( 'Siempre', 'wp-product-notices' ) . '</span>';
                        }
                        ?>
                    </td>
                    <td class="wpn-col-actions">
                        <div class="wpn-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-product-notices&action=edit&notice_id=' . $notice->get_id() ) ); ?>"
                               class="wpn-action wpn-action--edit"
                               title="<?php esc_attr_e( 'Editar', 'wp-product-notices' ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <button type="button"
                                    class="wpn-action wpn-action--duplicate"
                                    data-action="duplicate"
                                    data-notice-id="<?php echo esc_attr( $notice->get_id() ); ?>"
                                    title="<?php esc_attr_e( 'Duplicar', 'wp-product-notices' ); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            <button type="button"
                                    class="wpn-action wpn-action--delete"
                                    data-action="delete"
                                    data-notice-id="<?php echo esc_attr( $notice->get_id() ); ?>"
                                    title="<?php esc_attr_e( 'Eliminar', 'wp-product-notices' ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
/**
 * Obtener etiqueta legible del hook.
 *
 * @param string $hook     Nombre del hook.
 * @param int    $priority Prioridad.
 * @return string
 */
function wpn_get_hook_label( $hook, $priority ) {
    $hooks = array(
        'woocommerce_before_single_product'         => __( 'Inicio producto', 'wp-product-notices' ),
        'woocommerce_before_single_product_summary' => __( 'Antes resumen', 'wp-product-notices' ),
        'woocommerce_single_product_summary'        => __( 'En resumen', 'wp-product-notices' ),
        'woocommerce_after_add_to_cart_form'        => __( 'Después carrito', 'wp-product-notices' ),
        'woocommerce_product_meta_end'              => __( 'Después meta', 'wp-product-notices' ),
        'woocommerce_after_single_product_summary'  => __( 'Después resumen', 'wp-product-notices' ),
        'woocommerce_after_single_product'          => __( 'Final producto', 'wp-product-notices' ),
    );

    $label = isset( $hooks[ $hook ] ) ? $hooks[ $hook ] : $hook;

    if ( 'woocommerce_single_product_summary' === $hook ) {
        $label .= ' (' . $priority . ')';
    }

    return $label;
}
?>
