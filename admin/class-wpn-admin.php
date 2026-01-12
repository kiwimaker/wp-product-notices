<?php
/**
 * Clase principal del panel de administración.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/admin
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que gestiona el panel de administración.
 *
 * @since 1.0.0
 */
class WPN_Admin {

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
     * Renderizador de plantillas.
     *
     * @var WPN_Template_Renderer
     */
    private $template_renderer;

    /**
     * Constructor.
     *
     * @param string $plugin_name Nombre del plugin.
     * @param string $version     Versión del plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name       = $plugin_name;
        $this->version           = $version;
        $this->repository        = WPN_Notices_Repository::get_instance();
        $this->template_renderer = WPN_Template_Renderer::get_instance();
    }

    /**
     * Inicializar hooks del admin.
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // AJAX handlers.
        add_action( 'wp_ajax_wpn_save_notice', array( $this, 'ajax_save_notice' ) );
        add_action( 'wp_ajax_wpn_delete_notice', array( $this, 'ajax_delete_notice' ) );
        add_action( 'wp_ajax_wpn_toggle_status', array( $this, 'ajax_toggle_status' ) );
        add_action( 'wp_ajax_wpn_duplicate_notice', array( $this, 'ajax_duplicate_notice' ) );
        add_action( 'wp_ajax_wpn_reorder_notices', array( $this, 'ajax_reorder_notices' ) );
        add_action( 'wp_ajax_wpn_search_products', array( $this, 'ajax_search_products' ) );
        add_action( 'wp_ajax_wpn_search_categories', array( $this, 'ajax_search_categories' ) );
        add_action( 'wp_ajax_wpn_preview_notice', array( $this, 'ajax_preview_notice' ) );
    }

    /**
     * Añadir página de menú.
     */
    public function add_menu_page() {
        add_menu_page(
            __( 'Avisos de Producto', 'wp-product-notices' ),
            __( 'Avisos Producto', 'wp-product-notices' ),
            'manage_woocommerce',
            'wp-product-notices',
            array( $this, 'render_admin_page' ),
            'dashicons-megaphone',
            56
        );
    }

    /**
     * Cargar estilos del admin.
     *
     * @param string $hook Hook de la página actual.
     */
    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_wp-product-notices' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );

        // Select2 de WooCommerce.
        wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), '4.0.3' );

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            WPN_PLUGIN_URL . 'admin/css/wpn-admin.css',
            array( 'select2' ),
            $this->version
        );
    }

    /**
     * Cargar scripts del admin.
     *
     * @param string $hook Hook de la página actual.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_wp-product-notices' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        // Select2 de WooCommerce.
        wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.3', true );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            WPN_PLUGIN_URL . 'admin/js/wpn-admin.js',
            array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'select2' ),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-admin',
            'wpnAdmin',
            array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( 'wpn_admin_nonce' ),
                'strings'   => array(
                    'confirmDelete'   => __( '¿Estás seguro de que quieres eliminar este aviso?', 'wp-product-notices' ),
                    'saving'          => __( 'Guardando...', 'wp-product-notices' ),
                    'saved'           => __( 'Guardado', 'wp-product-notices' ),
                    'error'           => __( 'Error al guardar', 'wp-product-notices' ),
                    'searchProducts'  => __( 'Buscar productos...', 'wp-product-notices' ),
                    'searchCategories' => __( 'Buscar categorías...', 'wp-product-notices' ),
                ),
            )
        );
    }

    /**
     * Renderizar página principal del admin.
     */
    public function render_admin_page() {
        $action    = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $notice_id = isset( $_GET['notice_id'] ) ? sanitize_text_field( wp_unslash( $_GET['notice_id'] ) ) : '';

        echo '<div class="wrap wpn-admin">';
        echo '<h1 class="wpn-admin__title">';
        echo '<span class="dashicons dashicons-megaphone"></span> ';
        echo esc_html__( 'Avisos de Producto', 'wp-product-notices' );
        echo '</h1>';

        switch ( $action ) {
            case 'new':
            case 'edit':
                $this->render_notice_form( $notice_id );
                break;
            default:
                $this->render_notices_list();
                break;
        }

        echo '</div>';
    }

    /**
     * Renderizar listado de avisos.
     */
    private function render_notices_list() {
        $notices = $this->repository->get_all();
        include WPN_PLUGIN_DIR . 'admin/partials/wpn-admin-notices-list.php';
    }

    /**
     * Renderizar formulario de aviso.
     *
     * @param string $notice_id ID del aviso a editar.
     */
    private function render_notice_form( $notice_id = '' ) {
        $notice = null;

        if ( ! empty( $notice_id ) ) {
            $notice = $this->repository->get_by_id( $notice_id );
        }

        if ( ! $notice ) {
            $notice = new WPN_Notice();
        }

        $templates = $this->template_renderer->get_available_templates();
        $icons     = $this->template_renderer->get_icons_by_category();
        $hooks     = $this->get_available_hooks();

        include WPN_PLUGIN_DIR . 'admin/partials/wpn-admin-notice-form.php';
    }

    /**
     * Obtener hooks disponibles.
     *
     * @return array
     */
    private function get_available_hooks() {
        return array(
            'woocommerce_before_single_product'         => __( 'Antes del producto (inicio)', 'wp-product-notices' ),
            'woocommerce_before_single_product_summary' => __( 'Antes del resumen (después de imagen)', 'wp-product-notices' ),
            'woocommerce_single_product_summary_5'      => __( 'En el resumen - Antes del título', 'wp-product-notices' ),
            'woocommerce_single_product_summary_10'     => __( 'En el resumen - Después del título', 'wp-product-notices' ),
            'woocommerce_single_product_summary_15'     => __( 'En el resumen - Después del precio', 'wp-product-notices' ),
            'woocommerce_single_product_summary_25'     => __( 'En el resumen - Después del extracto', 'wp-product-notices' ),
            'woocommerce_after_add_to_cart_form'        => __( 'Después de "Añadir al carrito"', 'wp-product-notices' ),
            'woocommerce_product_meta_end'              => __( 'Después de los metadatos', 'wp-product-notices' ),
            'woocommerce_after_single_product_summary'  => __( 'Después del resumen (antes de tabs)', 'wp-product-notices' ),
            'woocommerce_after_single_product'          => __( 'Al final del producto', 'wp-product-notices' ),
        );
    }

    /**
     * AJAX: Guardar aviso.
     */
    public function ajax_save_notice() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'wp-product-notices' ) ) );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $data = isset( $_POST['notice'] ) ? wp_unslash( $_POST['notice'] ) : array();

        if ( empty( $data ) ) {
            wp_send_json_error( array( 'message' => __( 'Datos no válidos.', 'wp-product-notices' ) ) );
        }

        // Parsear hook y prioridad.
        $hook_value = isset( $data['placement']['hook'] ) ? sanitize_text_field( $data['placement']['hook'] ) : '';
        $hook       = $hook_value;
        $priority   = 10;

        // Si el hook contiene prioridad (ej: woocommerce_single_product_summary_15).
        if ( preg_match( '/^(.+)_(\d+)$/', $hook_value, $matches ) ) {
            $hook     = $matches[1];
            $priority = (int) $matches[2];
        }

        // Sanitizar datos.
        $notice_data = array(
            'id'         => isset( $data['id'] ) ? sanitize_text_field( $data['id'] ) : '',
            'title'      => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
            'status'     => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
            'content'    => array(
                'text' => isset( $data['content']['text'] ) ? wp_kses_post( $data['content']['text'] ) : '',
                'icon' => isset( $data['content']['icon'] ) ? sanitize_text_field( $data['content']['icon'] ) : 'info-circle',
            ),
            'styles'     => array(
                'template'     => isset( $data['styles']['template'] ) ? sanitize_text_field( $data['styles']['template'] ) : 'standard',
                'background'   => isset( $data['styles']['background'] ) ? sanitize_hex_color( $data['styles']['background'] ) : '#e8f5e9',
                'border_color' => isset( $data['styles']['border_color'] ) ? sanitize_hex_color( $data['styles']['border_color'] ) : '#4caf50',
                'text_color'   => isset( $data['styles']['text_color'] ) ? sanitize_hex_color( $data['styles']['text_color'] ) : '#1b5e20',
                'icon_color'   => isset( $data['styles']['icon_color'] ) ? sanitize_hex_color( $data['styles']['icon_color'] ) : '#2e7d32',
            ),
            'schedule'   => array(
                'start_date' => isset( $data['schedule']['start_date'] ) ? sanitize_text_field( $data['schedule']['start_date'] ) : '',
                'start_time' => isset( $data['schedule']['start_time'] ) ? sanitize_text_field( $data['schedule']['start_time'] ) : '00:00',
                'end_date'   => isset( $data['schedule']['end_date'] ) ? sanitize_text_field( $data['schedule']['end_date'] ) : '',
                'end_time'   => isset( $data['schedule']['end_time'] ) ? sanitize_text_field( $data['schedule']['end_time'] ) : '23:59',
            ),
            'placement'  => array(
                'hook'          => $hook,
                'priority'      => $priority,
                'render_method' => isset( $data['placement']['render_method'] ) ? sanitize_text_field( $data['placement']['render_method'] ) : 'php',
                'js_selector'   => isset( $data['placement']['js_selector'] ) ? sanitize_text_field( $data['placement']['js_selector'] ) : '.product-info, .product-summary, .summary.entry-summary',
                'js_position'   => isset( $data['placement']['js_position'] ) ? sanitize_text_field( $data['placement']['js_position'] ) : 'prepend',
            ),
            'visibility' => array(
                'type'               => isset( $data['visibility']['type'] ) ? sanitize_text_field( $data['visibility']['type'] ) : 'all',
                'categories'         => isset( $data['visibility']['categories'] ) ? array_map( 'absint', (array) $data['visibility']['categories'] ) : array(),
                'products'           => isset( $data['visibility']['products'] ) ? array_map( 'absint', (array) $data['visibility']['products'] ) : array(),
                'exclude_categories' => isset( $data['visibility']['exclude_categories'] ) ? array_map( 'absint', (array) $data['visibility']['exclude_categories'] ) : array(),
                'exclude_products'   => isset( $data['visibility']['exclude_products'] ) ? array_map( 'absint', (array) $data['visibility']['exclude_products'] ) : array(),
            ),
        );

        $notice = WPN_Notice::from_array( $notice_data );
        $result = $this->repository->save( $notice );

        if ( $result ) {
            wp_send_json_success(
                array(
                    'message'   => __( 'Aviso guardado correctamente.', 'wp-product-notices' ),
                    'notice_id' => $notice->get_id() ? $notice->get_id() : $notice_data['id'],
                    'redirect'  => admin_url( 'admin.php?page=wp-product-notices' ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al guardar el aviso.', 'wp-product-notices' ) ) );
        }
    }

    /**
     * AJAX: Eliminar aviso.
     */
    public function ajax_delete_notice() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'wp-product-notices' ) ) );
        }

        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';

        if ( empty( $notice_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID de aviso no válido.', 'wp-product-notices' ) ) );
        }

        $result = $this->repository->delete( $notice_id );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Aviso eliminado correctamente.', 'wp-product-notices' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al eliminar el aviso.', 'wp-product-notices' ) ) );
        }
    }

    /**
     * AJAX: Cambiar estado.
     */
    public function ajax_toggle_status() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'wp-product-notices' ) ) );
        }

        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';
        $status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

        if ( empty( $notice_id ) || empty( $status ) ) {
            wp_send_json_error( array( 'message' => __( 'Datos no válidos.', 'wp-product-notices' ) ) );
        }

        $result = $this->repository->update_status( $notice_id, $status );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Estado actualizado.', 'wp-product-notices' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al actualizar el estado.', 'wp-product-notices' ) ) );
        }
    }

    /**
     * AJAX: Duplicar aviso.
     */
    public function ajax_duplicate_notice() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'wp-product-notices' ) ) );
        }

        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';

        if ( empty( $notice_id ) ) {
            wp_send_json_error( array( 'message' => __( 'ID de aviso no válido.', 'wp-product-notices' ) ) );
        }

        $new_notice = $this->repository->duplicate( $notice_id );

        if ( $new_notice ) {
            wp_send_json_success(
                array(
                    'message'  => __( 'Aviso duplicado correctamente.', 'wp-product-notices' ),
                    'redirect' => admin_url( 'admin.php?page=wp-product-notices&action=edit&notice_id=' . $new_notice->get_id() ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al duplicar el aviso.', 'wp-product-notices' ) ) );
        }
    }

    /**
     * AJAX: Reordenar avisos.
     */
    public function ajax_reorder_notices() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'wp-product-notices' ) ) );
        }

        $order = isset( $_POST['order'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['order'] ) ) : array();

        if ( empty( $order ) ) {
            wp_send_json_error( array( 'message' => __( 'Datos no válidos.', 'wp-product-notices' ) ) );
        }

        $result = $this->repository->update_order( $order );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Orden actualizado.', 'wp-product-notices' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al actualizar el orden.', 'wp-product-notices' ) ) );
        }
    }

    /**
     * AJAX: Buscar productos.
     */
    public function ajax_search_products() {
        // Verificar nonce desde GET o POST.
        $nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
        if ( empty( $nonce ) ) {
            $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        }

        if ( ! wp_verify_nonce( $nonce, 'wpn_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce inválido' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error();
        }

        $search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
        if ( empty( $search ) ) {
            $search = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 20,
            's'              => $search,
            'post_status'    => 'publish',
        );

        $products = get_posts( $args );
        $results  = array();

        foreach ( $products as $product ) {
            $results[] = array(
                'id'   => $product->ID,
                'text' => $product->post_title,
            );
        }

        wp_send_json( array( 'results' => $results ) );
    }

    /**
     * AJAX: Buscar categorías.
     */
    public function ajax_search_categories() {
        // Verificar nonce desde GET o POST.
        $nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
        if ( empty( $nonce ) ) {
            $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        }

        if ( ! wp_verify_nonce( $nonce, 'wpn_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce inválido' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error();
        }

        $search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
        if ( empty( $search ) ) {
            $search = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
        }

        $args = array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'search'     => $search,
            'number'     => 20,
        );

        $categories = get_terms( $args );
        $results    = array();

        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) {
                $results[] = array(
                    'id'   => $category->term_id,
                    'text' => $category->name,
                );
            }
        }

        wp_send_json( array( 'results' => $results ) );
    }

    /**
     * AJAX: Preview del aviso.
     */
    public function ajax_preview_notice() {
        check_ajax_referer( 'wpn_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error();
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $data = isset( $_POST['notice'] ) ? wp_unslash( $_POST['notice'] ) : array();

        if ( empty( $data ) ) {
            wp_send_json_error();
        }

        $notice_data = array(
            'id'      => 'preview',
            'content' => array(
                'text' => isset( $data['content']['text'] ) ? wp_kses_post( $data['content']['text'] ) : '',
                'icon' => isset( $data['content']['icon'] ) ? sanitize_text_field( $data['content']['icon'] ) : 'info-circle',
            ),
            'styles'  => array(
                'template'     => isset( $data['styles']['template'] ) ? sanitize_text_field( $data['styles']['template'] ) : 'standard',
                'background'   => isset( $data['styles']['background'] ) ? sanitize_hex_color( $data['styles']['background'] ) : '#e8f5e9',
                'border_color' => isset( $data['styles']['border_color'] ) ? sanitize_hex_color( $data['styles']['border_color'] ) : '#4caf50',
                'text_color'   => isset( $data['styles']['text_color'] ) ? sanitize_hex_color( $data['styles']['text_color'] ) : '#1b5e20',
                'icon_color'   => isset( $data['styles']['icon_color'] ) ? sanitize_hex_color( $data['styles']['icon_color'] ) : '#2e7d32',
            ),
        );

        $notice = WPN_Notice::from_array( $notice_data );
        $html   = $this->template_renderer->render( $notice );

        wp_send_json_success( array( 'html' => $html ) );
    }
}
