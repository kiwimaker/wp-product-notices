<?php
/**
 * Plugin Name:       WP Product Notices
 * Plugin URI:        https://nexir.es
 * Description:       Muestra avisos personalizables en páginas de producto de WooCommerce con programación por fechas y filtros por categoría/producto.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Nexir Marketing
 * Author URI:        https://nexir.es
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-product-notices
 * Domain Path:       /languages
 * WC requires at least: 5.0
 * WC tested up to:   8.0
 *
 * @package WP_Product_Notices
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Versión actual del plugin.
 */
define( 'WPN_VERSION', '1.0.0' );

/**
 * Ruta del archivo principal del plugin.
 */
define( 'WPN_PLUGIN_FILE', __FILE__ );

/**
 * Ruta del directorio del plugin.
 */
define( 'WPN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL del directorio del plugin.
 */
define( 'WPN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Nombre del plugin.
 */
define( 'WPN_PLUGIN_NAME', 'wp-product-notices' );

/**
 * El código que se ejecuta durante la activación del plugin.
 */
function wpn_activate() {
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-activator.php';
    WPN_Activator::activate();
}

/**
 * El código que se ejecuta durante la desactivación del plugin.
 */
function wpn_deactivate() {
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-deactivator.php';
    WPN_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wpn_activate' );
register_deactivation_hook( __FILE__, 'wpn_deactivate' );

/**
 * Verificar que WooCommerce está activo.
 *
 * @return bool
 */
function wpn_is_woocommerce_active() {
    return class_exists( 'WooCommerce' );
}

/**
 * Mostrar aviso si WooCommerce no está activo.
 */
function wpn_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                /* translators: %s: WooCommerce */
                esc_html__( '%s requiere que WooCommerce esté instalado y activo.', 'wp-product-notices' ),
                '<strong>WP Product Notices</strong>'
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Inicializar el plugin.
 */
function wpn_init() {
    // Verificar que WooCommerce está activo.
    if ( ! wpn_is_woocommerce_active() ) {
        add_action( 'admin_notices', 'wpn_woocommerce_missing_notice' );
        return;
    }

    // Cargar el archivo principal del plugin.
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-loader.php';
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-notice.php';
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-notices-repository.php';
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-visibility-checker.php';
    require_once WPN_PLUGIN_DIR . 'includes/class-wpn-template-renderer.php';

    // Cargar la clase admin.
    if ( is_admin() ) {
        require_once WPN_PLUGIN_DIR . 'admin/class-wpn-admin.php';
        $admin = new WPN_Admin( WPN_PLUGIN_NAME, WPN_VERSION );
        $admin->init();
    }

    // Cargar la clase pública.
    require_once WPN_PLUGIN_DIR . 'public/class-wpn-public.php';
    $public = new WPN_Public( WPN_PLUGIN_NAME, WPN_VERSION );
    $public->init();
}

add_action( 'plugins_loaded', 'wpn_init' );

/**
 * Añadir enlace de configuración en el listado de plugins.
 *
 * @param array $links Enlaces existentes.
 * @return array
 */
function wpn_plugin_action_links( $links ) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url( 'admin.php?page=wp-product-notices' ),
        __( 'Configuración', 'wp-product-notices' )
    );
    array_unshift( $links, $settings_link );
    return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpn_plugin_action_links' );

/**
 * Declarar compatibilidad con HPOS de WooCommerce.
 */
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);
