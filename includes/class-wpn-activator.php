<?php
/**
 * Se ejecuta durante la activación del plugin.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que se ejecuta durante la activación del plugin.
 *
 * @since 1.0.0
 */
class WPN_Activator {

    /**
     * Código a ejecutar durante la activación.
     *
     * Crea las opciones por defecto del plugin si no existen.
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Crear opciones por defecto si no existen.
        if ( false === get_option( 'wpn_notices' ) ) {
            add_option( 'wpn_notices', array() );
        }

        if ( false === get_option( 'wpn_settings' ) ) {
            $default_settings = array(
                'version'            => WPN_VERSION,
                'default_template'   => 'standard',
                'load_styles'        => true,
                'custom_css'         => '',
                'delete_on_uninstall' => false,
            );
            add_option( 'wpn_settings', $default_settings );
        }

        // Guardar la versión actual.
        update_option( 'wpn_version', WPN_VERSION );

        // Limpiar caché de rewrite rules.
        flush_rewrite_rules();
    }
}
