<?php
/**
 * Verificador de visibilidad de avisos.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que determina si un aviso debe mostrarse.
 *
 * @since 1.0.0
 */
class WPN_Visibility_Checker {

    /**
     * Instancia singleton.
     *
     * @var WPN_Visibility_Checker
     */
    private static $instance = null;

    /**
     * Obtener instancia singleton.
     *
     * @return WPN_Visibility_Checker
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Verificar si un aviso debe mostrarse para un producto.
     *
     * @param WPN_Notice $notice     Instancia del aviso.
     * @param int        $product_id ID del producto.
     * @return bool
     */
    public function should_display( WPN_Notice $notice, $product_id ) {
        // Verificar si está activo.
        if ( ! $notice->is_active() ) {
            return false;
        }

        // Verificar programación.
        if ( ! $this->check_schedule( $notice ) ) {
            return false;
        }

        // Verificar visibilidad por producto/categoría.
        if ( ! $this->check_visibility( $notice, $product_id ) ) {
            return false;
        }

        // Verificar exclusiones.
        if ( $this->is_excluded( $notice, $product_id ) ) {
            return false;
        }

        /**
         * Filtro para personalizar la visibilidad de un aviso.
         *
         * @param bool       $should_display Si el aviso debe mostrarse.
         * @param WPN_Notice $notice         Instancia del aviso.
         * @param int        $product_id     ID del producto.
         */
        return apply_filters( 'wpn_should_display_notice', true, $notice, $product_id );
    }

    /**
     * Verificar la programación del aviso.
     *
     * @param WPN_Notice $notice Instancia del aviso.
     * @return bool
     */
    private function check_schedule( WPN_Notice $notice ) {
        return $notice->is_scheduled_now();
    }

    /**
     * Verificar visibilidad por tipo.
     *
     * @param WPN_Notice $notice     Instancia del aviso.
     * @param int        $product_id ID del producto.
     * @return bool
     */
    private function check_visibility( WPN_Notice $notice, $product_id ) {
        $visibility = $notice->get_visibility();
        $type       = $visibility['type'];

        switch ( $type ) {
            case 'all':
                return true;

            case 'categories':
                return $this->check_category_visibility( $notice, $product_id );

            case 'products':
                return $this->check_product_visibility( $notice, $product_id );

            default:
                return true;
        }
    }

    /**
     * Verificar visibilidad por categoría.
     *
     * @param WPN_Notice $notice     Instancia del aviso.
     * @param int        $product_id ID del producto.
     * @return bool
     */
    private function check_category_visibility( WPN_Notice $notice, $product_id ) {
        $visibility = $notice->get_visibility();
        $categories = $visibility['categories'];

        if ( empty( $categories ) ) {
            return true;
        }

        // Obtener categorías del producto.
        $product_categories = $this->get_product_category_ids( $product_id );

        // Verificar si el producto tiene alguna de las categorías.
        $intersection = array_intersect( $product_categories, $categories );

        return ! empty( $intersection );
    }

    /**
     * Verificar visibilidad por producto.
     *
     * @param WPN_Notice $notice     Instancia del aviso.
     * @param int        $product_id ID del producto.
     * @return bool
     */
    private function check_product_visibility( WPN_Notice $notice, $product_id ) {
        $visibility = $notice->get_visibility();
        $products   = $visibility['products'];

        if ( empty( $products ) ) {
            return true;
        }

        return in_array( $product_id, array_map( 'intval', $products ), true );
    }

    /**
     * Verificar si el producto está excluido.
     *
     * @param WPN_Notice $notice     Instancia del aviso.
     * @param int        $product_id ID del producto.
     * @return bool
     */
    private function is_excluded( WPN_Notice $notice, $product_id ) {
        $visibility = $notice->get_visibility();

        // Verificar exclusión por producto.
        $exclude_products = isset( $visibility['exclude_products'] ) ? $visibility['exclude_products'] : array();
        if ( ! empty( $exclude_products ) && in_array( $product_id, array_map( 'intval', $exclude_products ), true ) ) {
            return true;
        }

        // Verificar exclusión por categoría.
        $exclude_categories = isset( $visibility['exclude_categories'] ) ? $visibility['exclude_categories'] : array();
        if ( ! empty( $exclude_categories ) ) {
            $product_categories = $this->get_product_category_ids( $product_id );
            $intersection       = array_intersect( $product_categories, $exclude_categories );

            if ( ! empty( $intersection ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener IDs de categorías de un producto.
     *
     * @param int $product_id ID del producto.
     * @return array
     */
    private function get_product_category_ids( $product_id ) {
        $terms = get_the_terms( $product_id, 'product_cat' );

        if ( ! $terms || is_wp_error( $terms ) ) {
            return array();
        }

        $category_ids = array();
        foreach ( $terms as $term ) {
            $category_ids[] = $term->term_id;

            // Incluir categorías padre.
            $ancestors = get_ancestors( $term->term_id, 'product_cat' );
            $category_ids = array_merge( $category_ids, $ancestors );
        }

        return array_unique( $category_ids );
    }
}
