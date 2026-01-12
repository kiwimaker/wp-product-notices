<?php
/**
 * Plantilla: Highlighted
 *
 * Diseño llamativo con panel de icono a color.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/public/partials/templates
 *
 * Variables disponibles:
 * @var string $notice_id     - ID único del aviso
 * @var string $text          - Texto del aviso
 * @var string $icon_svg      - SVG del icono
 * @var array  $styles        - Array con colores personalizados
 * @var string $inline_styles - Estilos inline generados
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wpn-notice wpn-notice--highlighted"
     id="<?php echo esc_attr( $notice_id ); ?>"
     style="<?php echo esc_attr( $inline_styles ); ?>">

    <div class="wpn-notice__icon-wrapper">
        <?php if ( $icon_svg ) : ?>
            <div class="wpn-notice__icon">
                <?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wpn-notice__content">
        <p class="wpn-notice__text">
            <?php echo wp_kses_post( $text ); ?>
        </p>
    </div>

</div>
