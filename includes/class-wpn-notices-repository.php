<?php
/**
 * Repositorio CRUD para los avisos.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que gestiona el almacenamiento y recuperación de avisos.
 *
 * @since 1.0.0
 */
class WPN_Notices_Repository {

    /**
     * Clave de la opción en wp_options.
     *
     * @var string
     */
    const OPTION_KEY = 'wpn_notices';

    /**
     * Instancia singleton.
     *
     * @var WPN_Notices_Repository
     */
    private static $instance = null;

    /**
     * Caché de avisos.
     *
     * @var array|null
     */
    private $cache = null;

    /**
     * Obtener instancia singleton.
     *
     * @return WPN_Notices_Repository
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener todos los avisos.
     *
     * @return WPN_Notice[]
     */
    public function get_all() {
        $notices_data = $this->get_raw_data();
        $notices      = array();

        foreach ( $notices_data as $data ) {
            $notices[] = WPN_Notice::from_array( $data );
        }

        // Ordenar por 'order'.
        usort(
            $notices,
            function ( $a, $b ) {
                return $a->get_order() - $b->get_order();
            }
        );

        return $notices;
    }

    /**
     * Obtener aviso por ID.
     *
     * @param string $id ID del aviso.
     * @return WPN_Notice|null
     */
    public function get_by_id( $id ) {
        $notices_data = $this->get_raw_data();

        foreach ( $notices_data as $data ) {
            if ( $data['id'] === $id ) {
                return WPN_Notice::from_array( $data );
            }
        }

        return null;
    }

    /**
     * Obtener todos los avisos activos.
     *
     * @return WPN_Notice[]
     */
    public function get_active() {
        $all_notices    = $this->get_all();
        $active_notices = array();

        foreach ( $all_notices as $notice ) {
            if ( $notice->is_active() ) {
                $active_notices[] = $notice;
            }
        }

        return $active_notices;
    }

    /**
     * Obtener avisos por hook.
     *
     * @param string $hook Nombre del hook.
     * @return WPN_Notice[]
     */
    public function get_by_hook( $hook ) {
        $all_notices  = $this->get_active();
        $hook_notices = array();

        foreach ( $all_notices as $notice ) {
            if ( $notice->get_hook() === $hook ) {
                $hook_notices[] = $notice;
            }
        }

        return $hook_notices;
    }

    /**
     * Guardar un aviso.
     *
     * @param WPN_Notice $notice Instancia del aviso.
     * @return bool
     */
    public function save( WPN_Notice $notice ) {
        $notices_data = $this->get_raw_data();
        $notice_array = $notice->to_array();
        $found        = false;

        // Si es nuevo, generar ID.
        if ( empty( $notice_array['id'] ) ) {
            $notice_array['id']         = $this->generate_id();
            $notice_array['created_at'] = current_time( 'mysql' );
        }

        // Actualizar fecha de modificación.
        $notice_array['updated_at'] = current_time( 'mysql' );

        // Buscar si ya existe.
        foreach ( $notices_data as $key => $data ) {
            if ( $data['id'] === $notice_array['id'] ) {
                $notices_data[ $key ] = $notice_array;
                $found                = true;
                break;
            }
        }

        // Si no existe, añadir.
        if ( ! $found ) {
            $notices_data[] = $notice_array;
        }

        // Limpiar caché.
        $this->cache = null;

        return update_option( self::OPTION_KEY, $notices_data );
    }

    /**
     * Eliminar un aviso.
     *
     * @param string $id ID del aviso.
     * @return bool
     */
    public function delete( $id ) {
        $notices_data = $this->get_raw_data();
        $new_data     = array();

        foreach ( $notices_data as $data ) {
            if ( $data['id'] !== $id ) {
                $new_data[] = $data;
            }
        }

        // Limpiar caché.
        $this->cache = null;

        return update_option( self::OPTION_KEY, $new_data );
    }

    /**
     * Actualizar el orden de los avisos.
     *
     * @param array $ids Array de IDs en el nuevo orden.
     * @return bool
     */
    public function update_order( array $ids ) {
        $notices_data = $this->get_raw_data();

        // Crear mapa de ID a índice nuevo.
        $order_map = array_flip( $ids );

        // Actualizar orden.
        foreach ( $notices_data as $key => $data ) {
            if ( isset( $order_map[ $data['id'] ] ) ) {
                $notices_data[ $key ]['order'] = $order_map[ $data['id'] ];
            }
        }

        // Limpiar caché.
        $this->cache = null;

        return update_option( self::OPTION_KEY, $notices_data );
    }

    /**
     * Cambiar estado de un aviso.
     *
     * @param string $id     ID del aviso.
     * @param string $status Nuevo estado.
     * @return bool
     */
    public function update_status( $id, $status ) {
        $notice = $this->get_by_id( $id );

        if ( ! $notice ) {
            return false;
        }

        $notice_data           = $notice->to_array();
        $notice_data['status'] = $status;

        $updated_notice = WPN_Notice::from_array( $notice_data );

        return $this->save( $updated_notice );
    }

    /**
     * Duplicar un aviso.
     *
     * @param string $id ID del aviso a duplicar.
     * @return WPN_Notice|null
     */
    public function duplicate( $id ) {
        $notice = $this->get_by_id( $id );

        if ( ! $notice ) {
            return null;
        }

        $notice_data         = $notice->to_array();
        $notice_data['id']   = '';
        $notice_data['title'] = $notice_data['title'] . ' (copia)';

        $new_notice = WPN_Notice::from_array( $notice_data );
        $this->save( $new_notice );

        return $new_notice;
    }

    /**
     * Obtener datos crudos de wp_options.
     *
     * @return array
     */
    private function get_raw_data() {
        if ( null !== $this->cache ) {
            return $this->cache;
        }

        $this->cache = get_option( self::OPTION_KEY, array() );

        if ( ! is_array( $this->cache ) ) {
            $this->cache = array();
        }

        return $this->cache;
    }

    /**
     * Generar ID único.
     *
     * @return string
     */
    private function generate_id() {
        return 'wpn_' . substr( md5( uniqid( wp_rand(), true ) ), 0, 12 );
    }

    /**
     * Obtener cantidad de avisos.
     *
     * @return int
     */
    public function count() {
        return count( $this->get_raw_data() );
    }

    /**
     * Obtener cantidad de avisos activos.
     *
     * @return int
     */
    public function count_active() {
        return count( $this->get_active() );
    }
}
