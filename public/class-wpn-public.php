<?php
/**
 * Clase pública del plugin (frontend).
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/public
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que gestiona la visualización de avisos en el frontend.
 *
 * @since 1.0.0
 */
class WPN_Public {

    /**
     * Nombre del plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * Versión del plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Repositorio de avisos.
     *
     * @var WPN_Notices_Repository
     */
    private $repository;

    /**
     * Verificador de visibilidad.
     *
     * @var WPN_Visibility_Checker
     */
    private $visibility_checker;

    /**
     * Renderizador de plantillas.
     *
     * @var WPN_Template_Renderer
     */
    private $template_renderer;

    /**
     * Avisos ya procesados (para evitar duplicados).
     *
     * @var array
     */
    private $rendered_notices = array();

    /**
     * Constructor.
     *
     * @param string $plugin_name Nombre del plugin.
     * @param string $version     Versión del plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name        = $plugin_name;
        $this->version            = $version;
        $this->repository         = WPN_Notices_Repository::get_instance();
        $this->visibility_checker = WPN_Visibility_Checker::get_instance();
        $this->template_renderer  = WPN_Template_Renderer::get_instance();
    }

    /**
     * Inicializar hooks públicos.
     */
    public function init() {
        // Cargar estilos y scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Registrar hooks de WooCommerce dinámicamente.
        add_action( 'wp', array( $this, 'register_woocommerce_hooks' ) );

        // Registrar shortcode.
        add_shortcode( 'wpn_notice', array( $this, 'shortcode_notice' ) );
    }

    /**
     * Cargar estilos públicos.
     */
    public function enqueue_styles() {
        // Solo cargar en páginas de producto.
        if ( ! is_product() ) {
            return;
        }

        // Verificar si hay avisos activos.
        $notices = $this->repository->get_active();
        if ( empty( $notices ) ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-public',
            WPN_PLUGIN_URL . 'public/css/wpn-public.css',
            array(),
            $this->version
        );
    }

    /**
     * Cargar scripts públicos (para renderizado JS).
     */
    public function enqueue_scripts() {
        // Solo cargar en páginas de producto.
        if ( ! is_product() ) {
            return;
        }

        global $product;

        // Asegurarse de que $product es un objeto WC_Product válido.
        if ( ! $product || ! is_object( $product ) || ! ( $product instanceof WC_Product ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! $product || ! is_object( $product ) ) {
            return;
        }

        $product_id = $product->get_id();

        // Obtener avisos con renderizado JavaScript.
        $notices = $this->repository->get_active();
        $js_notices = array();

        foreach ( $notices as $notice ) {
            // Solo avisos con renderizado JavaScript.
            if ( 'javascript' !== $notice->get_render_method() ) {
                continue;
            }

            // Verificar visibilidad.
            if ( ! $this->visibility_checker->should_display( $notice, $product_id ) ) {
                continue;
            }

            // Renderizar el HTML del aviso.
            $html = $this->template_renderer->render( $notice );

            $js_notices[] = array(
                'id'        => $notice->get_id(),
                'html'      => $html,
                'selector'  => $notice->get_js_selector(),
                'position'  => $notice->get_js_position(),
            );
        }

        if ( empty( $js_notices ) ) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name . '-public',
            WPN_PLUGIN_URL . 'public/js/wpn-public.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-public',
            'wpnPublic',
            array(
                'notices' => $js_notices,
            )
        );
    }

    /**
     * Shortcode para mostrar un aviso específico.
     *
     * @param array $atts Atributos del shortcode.
     * @return string
     */
    public function shortcode_notice( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts,
            'wpn_notice'
        );

        if ( empty( $atts['id'] ) ) {
            return '';
        }

        $notice = $this->repository->get_by_id( $atts['id'] );

        if ( ! $notice || ! $notice->is_active() || ! $notice->is_scheduled_now() ) {
            return '';
        }

        // Si estamos en página de producto, verificar visibilidad.
        if ( is_product() ) {
            global $product;

            // Asegurarse de que $product es un objeto WC_Product válido.
            if ( ! $product || ! is_object( $product ) || ! ( $product instanceof WC_Product ) ) {
                $product = wc_get_product( get_the_ID() );
            }

            if ( $product && is_object( $product ) && ! $this->visibility_checker->should_display( $notice, $product->get_id() ) ) {
                return '';
            }
        }

        return $this->template_renderer->render( $notice );
    }

    /**
     * Registrar hooks de WooCommerce.
     */
    public function register_woocommerce_hooks() {
        // Solo en páginas de producto.
        if ( ! is_product() ) {
            return;
        }

        $notices = $this->repository->get_active();

        if ( empty( $notices ) ) {
            return;
        }

        // Agrupar avisos por hook y prioridad (solo avisos con renderizado PHP).
        $hooks_to_register = array();

        foreach ( $notices as $notice ) {
            // Solo avisos con renderizado PHP.
            if ( 'php' !== $notice->get_render_method() ) {
                continue;
            }

            $hook     = $notice->get_hook();
            $priority = $notice->get_priority();
            $key      = $hook . '_' . $priority;

            if ( ! isset( $hooks_to_register[ $key ] ) ) {
                $hooks_to_register[ $key ] = array(
                    'hook'     => $hook,
                    'priority' => $priority,
                );
            }
        }

        // Registrar cada combinación de hook/prioridad una sola vez.
        foreach ( $hooks_to_register as $hook_data ) {
            $hook     = $hook_data['hook'];
            $priority = $hook_data['priority'];

            add_action(
                $hook,
                function () use ( $hook, $priority ) {
                    $this->render_notices_for_hook( $hook, $priority );
                },
                $priority
            );
        }
    }

    /**
     * Renderizar avisos para un hook específico.
     *
     * @param string $hook     Nombre del hook.
     * @param int    $priority Prioridad del hook.
     */
    public function render_notices_for_hook( $hook, $priority ) {
        global $product;

        if ( ! $product ) {
            return;
        }

        $product_id = $product->get_id();
        $notices    = $this->repository->get_active();
        $output     = '';

        foreach ( $notices as $notice ) {
            // Solo avisos con renderizado PHP.
            if ( 'php' !== $notice->get_render_method() ) {
                continue;
            }

            // Verificar que el aviso corresponde a este hook y prioridad.
            if ( $notice->get_hook() !== $hook || $notice->get_priority() !== $priority ) {
                continue;
            }

            // Evitar duplicados.
            $notice_key = $notice->get_id() . '_' . $product_id;
            if ( isset( $this->rendered_notices[ $notice_key ] ) ) {
                continue;
            }

            // Verificar visibilidad.
            if ( ! $this->visibility_checker->should_display( $notice, $product_id ) ) {
                continue;
            }

            // Renderizar el aviso.
            $output .= $this->template_renderer->render( $notice );

            // Marcar como renderizado.
            $this->rendered_notices[ $notice_key ] = true;
        }

        if ( ! empty( $output ) ) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}
