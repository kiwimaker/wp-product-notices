<?php
/**
 * Archivo de desinstalación del plugin.
 *
 * Se ejecuta cuando el usuario elimina el plugin desde WordPress.
 *
 * @package WP_Product_Notices
 */

// Si no se llama desde WordPress, salir.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Obtener configuración.
$settings = get_option( 'wpn_settings', array() );

// Solo eliminar datos si está habilitada la opción.
$delete_data = isset( $settings['delete_on_uninstall'] ) && $settings['delete_on_uninstall'];

if ( $delete_data ) {
    // Eliminar opciones.
    delete_option( 'wpn_notices' );
    delete_option( 'wpn_settings' );
    delete_option( 'wpn_version' );

    // Limpiar transients si los hubiera.
    delete_transient( 'wpn_active_notices' );
}
