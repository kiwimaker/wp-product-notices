<?php
/**
 * Se ejecuta durante la desactivación del plugin.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que se ejecuta durante la desactivación del plugin.
 *
 * @since 1.0.0
 */
class WPN_Deactivator {

    /**
     * Código a ejecutar durante la desactivación.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Limpiar caché de rewrite rules.
        flush_rewrite_rules();
    }
}
