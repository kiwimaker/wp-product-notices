<?php
/**
 * Renderizador de plantillas de avisos.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que renderiza las plantillas de avisos.
 *
 * @since 1.0.0
 */
class WPN_Template_Renderer {

    /**
     * Instancia singleton.
     *
     * @var WPN_Template_Renderer
     */
    private static $instance = null;

    /**
     * Ruta a las plantillas.
     *
     * @var string
     */
    private $templates_path;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->templates_path = WPN_PLUGIN_DIR . 'public/partials/templates/';
    }

    /**
     * Obtener instancia singleton.
     *
     * @return WPN_Template_Renderer
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Renderizar un aviso.
     *
     * @param WPN_Notice $notice Instancia del aviso.
     * @return string HTML del aviso.
     */
    public function render( WPN_Notice $notice ) {
        $template      = $notice->get_template();
        $template_file = $this->get_template_path( $template );

        if ( ! file_exists( $template_file ) ) {
            $template_file = $this->get_template_path( 'standard' );
        }

        // Preparar variables para la plantilla.
        $notice_id     = $notice->get_id();
        $text          = $notice->get_text();
        $icon_svg      = $this->get_icon_svg( $notice->get_icon() );
        $styles        = $notice->get_styles();
        $inline_styles = $this->generate_inline_styles( $notice );

        // Iniciar buffer de salida.
        ob_start();
        include $template_file;
        $output = ob_get_clean();

        /**
         * Filtro para modificar el HTML del aviso.
         *
         * @param string     $output  HTML del aviso.
         * @param WPN_Notice $notice  Instancia del aviso.
         */
        return apply_filters( 'wpn_notice_html', $output, $notice );
    }

    /**
     * Obtener plantillas disponibles.
     *
     * @return array
     */
    public function get_available_templates() {
        return array(
            'standard'    => array(
                'label'       => __( 'Estándar', 'wp-product-notices' ),
                'description' => __( 'Borde lateral con icono y texto', 'wp-product-notices' ),
            ),
            'minimal'     => array(
                'label'       => __( 'Minimal', 'wp-product-notices' ),
                'description' => __( 'Estilo compacto tipo badge', 'wp-product-notices' ),
            ),
            'highlighted' => array(
                'label'       => __( 'Destacado', 'wp-product-notices' ),
                'description' => __( 'Panel de icono con color sólido', 'wp-product-notices' ),
            ),
        );
    }

    /**
     * Obtener ruta de una plantilla.
     *
     * @param string $template Nombre de la plantilla.
     * @return string
     */
    private function get_template_path( $template ) {
        // Primero buscar en el tema.
        $theme_template = locate_template( 'wp-product-notices/notice-' . $template . '.php' );

        if ( $theme_template ) {
            return $theme_template;
        }

        return $this->templates_path . 'notice-' . $template . '.php';
    }

    /**
     * Obtener SVG de un icono.
     *
     * @param string $icon_slug Slug del icono.
     * @return string
     */
    public function get_icon_svg( $icon_slug ) {
        $icons = $this->get_icons();

        if ( ! isset( $icons[ $icon_slug ] ) ) {
            $icon_slug = 'info-circle';
        }

        return sprintf(
            '<svg class="wpn-notice__icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
            $icons[ $icon_slug ]['path']
        );
    }

    /**
     * Generar estilos inline para un aviso.
     *
     * @param WPN_Notice $notice Instancia del aviso.
     * @return string
     */
    private function generate_inline_styles( WPN_Notice $notice ) {
        $styles = $notice->get_styles();

        $css_vars = array(
            '--wpn-bg-color'     => $styles['background'],
            '--wpn-border-color' => $styles['border_color'],
            '--wpn-text-color'   => $styles['text_color'],
            '--wpn-icon-color'   => $styles['icon_color'],
        );

        $inline = array();
        foreach ( $css_vars as $var => $value ) {
            $inline[] = esc_attr( $var ) . ':' . esc_attr( $value );
        }

        return implode( ';', $inline );
    }

    /**
     * Obtener lista de iconos disponibles.
     *
     * @return array
     */
    public function get_icons() {
        return array(
            // Envío.
            'truck'         => array(
                'label'    => __( 'Camión', 'wp-product-notices' ),
                'category' => 'shipping',
                'path'     => '<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>',
            ),
            'truck-fast'    => array(
                'label'    => __( 'Envío rápido', 'wp-product-notices' ),
                'category' => 'shipping',
                'path'     => '<path d="M10 17h4V5H2v12h3m15 0h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1"></path><circle cx="7.5" cy="17.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle>',
            ),
            'package'       => array(
                'label'    => __( 'Paquete', 'wp-product-notices' ),
                'category' => 'shipping',
                'path'     => '<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line>',
            ),
            'box'           => array(
                'label'    => __( 'Caja', 'wp-product-notices' ),
                'category' => 'shipping',
                'path'     => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line>',
            ),
            'globe'         => array(
                'label'    => __( 'Internacional', 'wp-product-notices' ),
                'category' => 'shipping',
                'path'     => '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
            ),

            // Comercio.
            'tag'           => array(
                'label'    => __( 'Etiqueta', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line>',
            ),
            'percent'       => array(
                'label'    => __( 'Descuento', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle>',
            ),
            'gift'          => array(
                'label'    => __( 'Regalo', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>',
            ),
            'cart'          => array(
                'label'    => __( 'Carrito', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>',
            ),
            'credit-card'   => array(
                'label'    => __( 'Tarjeta', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line>',
            ),
            'wallet'        => array(
                'label'    => __( 'Billetera', 'wp-product-notices' ),
                'category' => 'commerce',
                'path'     => '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4z"></path>',
            ),

            // Info.
            'info-circle'   => array(
                'label'    => __( 'Información', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
            ),
            'exclamation'   => array(
                'label'    => __( 'Advertencia', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
            ),
            'check-circle'  => array(
                'label'    => __( 'Verificado', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
            ),
            'shield-check'  => array(
                'label'    => __( 'Garantía', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline>',
            ),
            'clock'         => array(
                'label'    => __( 'Tiempo', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
            ),
            'calendar'      => array(
                'label'    => __( 'Calendario', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
            ),
            'bell'          => array(
                'label'    => __( 'Notificación', 'wp-product-notices' ),
                'category' => 'info',
                'path'     => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>',
            ),

            // Soporte.
            'phone'         => array(
                'label'    => __( 'Teléfono', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>',
            ),
            'chat'          => array(
                'label'    => __( 'Chat', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>',
            ),
            'question-mark' => array(
                'label'    => __( 'Ayuda', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>',
            ),
            'heart'         => array(
                'label'    => __( 'Favorito', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>',
            ),
            'star'          => array(
                'label'    => __( 'Destacado', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
            ),
            'thumbs-up'     => array(
                'label'    => __( 'Recomendado', 'wp-product-notices' ),
                'category' => 'support',
                'path'     => '<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>',
            ),

            // Logística.
            'map-pin'       => array(
                'label'    => __( 'Ubicación', 'wp-product-notices' ),
                'category' => 'logistics',
                'path'     => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
            ),
            'home'          => array(
                'label'    => __( 'Domicilio', 'wp-product-notices' ),
                'category' => 'logistics',
                'path'     => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',
            ),
            'building'      => array(
                'label'    => __( 'Punto recogida', 'wp-product-notices' ),
                'category' => 'logistics',
                'path'     => '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="6" x2="9.01" y2="6"></line><line x1="15" y1="6" x2="15.01" y2="6"></line><line x1="9" y1="10" x2="9.01" y2="10"></line><line x1="15" y1="10" x2="15.01" y2="10"></line><line x1="9" y1="14" x2="9.01" y2="14"></line><line x1="15" y1="14" x2="15.01" y2="14"></line><line x1="9" y1="18" x2="15" y2="18"></line>',
            ),
            'refresh'       => array(
                'label'    => __( 'Devoluciones', 'wp-product-notices' ),
                'category' => 'logistics',
                'path'     => '<polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>',
            ),
        );
    }

    /**
     * Obtener iconos agrupados por categoría.
     *
     * @return array
     */
    public function get_icons_by_category() {
        $icons     = $this->get_icons();
        $grouped   = array();
        $categories = array(
            'shipping'  => __( 'Envío', 'wp-product-notices' ),
            'commerce'  => __( 'Comercio', 'wp-product-notices' ),
            'info'      => __( 'Información', 'wp-product-notices' ),
            'support'   => __( 'Soporte', 'wp-product-notices' ),
            'logistics' => __( 'Logística', 'wp-product-notices' ),
        );

        foreach ( $categories as $cat_slug => $cat_label ) {
            $grouped[ $cat_slug ] = array(
                'label' => $cat_label,
                'icons' => array(),
            );
        }

        foreach ( $icons as $slug => $icon ) {
            $cat = $icon['category'];
            if ( isset( $grouped[ $cat ] ) ) {
                $grouped[ $cat ]['icons'][ $slug ] = $icon;
            }
        }

        return $grouped;
    }
}
