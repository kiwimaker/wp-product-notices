<?php
/**
 * Modelo de datos para un aviso individual.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que representa un aviso individual.
 *
 * @since 1.0.0
 */
class WPN_Notice {

    /**
     * ID único del aviso.
     *
     * @var string
     */
    private $id;

    /**
     * Título interno (solo admin).
     *
     * @var string
     */
    private $title;

    /**
     * Estado del aviso (active, inactive).
     *
     * @var string
     */
    private $status;

    /**
     * Fecha de creación.
     *
     * @var string
     */
    private $created_at;

    /**
     * Fecha de última modificación.
     *
     * @var string
     */
    private $updated_at;

    /**
     * Contenido del aviso (text, icon).
     *
     * @var array
     */
    private $content;

    /**
     * Estilos del aviso.
     *
     * @var array
     */
    private $styles;

    /**
     * Programación de fechas.
     *
     * @var array
     */
    private $schedule;

    /**
     * Posición del aviso (hook, priority).
     *
     * @var array
     */
    private $placement;

    /**
     * Configuración de visibilidad.
     *
     * @var array
     */
    private $visibility;

    /**
     * Orden de visualización.
     *
     * @var int
     */
    private $order;

    /**
     * Constructor.
     *
     * @param array $data Datos del aviso.
     */
    public function __construct( array $data = array() ) {
        $defaults = self::get_defaults();
        $data     = wp_parse_args( $data, $defaults );

        $this->id         = $data['id'];
        $this->title      = $data['title'];
        $this->status     = $data['status'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->content    = wp_parse_args( $data['content'], $defaults['content'] );
        $this->styles     = wp_parse_args( $data['styles'], $defaults['styles'] );
        $this->schedule   = wp_parse_args( $data['schedule'], $defaults['schedule'] );
        $this->placement  = wp_parse_args( $data['placement'], $defaults['placement'] );
        $this->visibility = wp_parse_args( $data['visibility'], $defaults['visibility'] );
        $this->order      = (int) $data['order'];
    }

    /**
     * Obtener valores por defecto.
     *
     * @return array
     */
    public static function get_defaults() {
        return array(
            'id'         => '',
            'title'      => '',
            'status'     => 'active',
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
            'content'    => array(
                'text' => '',
                'icon' => 'info-circle',
            ),
            'styles'     => array(
                'template'     => 'standard',
                'background'   => '#e8f5e9',
                'border_color' => '#4caf50',
                'text_color'   => '#1b5e20',
                'icon_color'   => '#2e7d32',
            ),
            'schedule'   => array(
                'start_date' => '',
                'start_time' => '00:00',
                'end_date'   => '',
                'end_time'   => '23:59',
            ),
            'placement'  => array(
                'hook'          => 'woocommerce_before_single_product_summary',
                'priority'      => 15,
                'render_method' => 'php',
                'js_selector'   => '.product-info, .product-summary, .summary.entry-summary',
                'js_position'   => 'prepend',
            ),
            'visibility' => array(
                'type'               => 'all',
                'categories'         => array(),
                'products'           => array(),
                'exclude_categories' => array(),
                'exclude_products'   => array(),
            ),
            'order'      => 0,
        );
    }

    /**
     * Obtener ID.
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Establecer ID.
     *
     * @param string $id ID del aviso.
     */
    public function set_id( $id ) {
        $this->id = $id;
    }

    /**
     * Obtener título.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Obtener texto del aviso.
     *
     * @return string
     */
    public function get_text() {
        return $this->content['text'];
    }

    /**
     * Obtener icono.
     *
     * @return string
     */
    public function get_icon() {
        return $this->content['icon'];
    }

    /**
     * Obtener contenido completo.
     *
     * @return array
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * Obtener plantilla.
     *
     * @return string
     */
    public function get_template() {
        return $this->styles['template'];
    }

    /**
     * Obtener estilos.
     *
     * @return array
     */
    public function get_styles() {
        return $this->styles;
    }

    /**
     * Obtener hook.
     *
     * @return string
     */
    public function get_hook() {
        return $this->placement['hook'];
    }

    /**
     * Obtener prioridad.
     *
     * @return int
     */
    public function get_priority() {
        return (int) $this->placement['priority'];
    }

    /**
     * Obtener placement completo.
     *
     * @return array
     */
    public function get_placement() {
        return $this->placement;
    }

    /**
     * Obtener método de renderizado.
     *
     * @return string
     */
    public function get_render_method() {
        return isset( $this->placement['render_method'] ) ? $this->placement['render_method'] : 'php';
    }

    /**
     * Obtener selector CSS para JavaScript.
     *
     * @return string
     */
    public function get_js_selector() {
        return isset( $this->placement['js_selector'] ) ? $this->placement['js_selector'] : '';
    }

    /**
     * Obtener posición JavaScript (prepend/append).
     *
     * @return string
     */
    public function get_js_position() {
        return isset( $this->placement['js_position'] ) ? $this->placement['js_position'] : 'prepend';
    }

    /**
     * Obtener schedule.
     *
     * @return array
     */
    public function get_schedule() {
        return $this->schedule;
    }

    /**
     * Obtener configuración de visibilidad.
     *
     * @return array
     */
    public function get_visibility() {
        return $this->visibility;
    }

    /**
     * Obtener tipo de visibilidad.
     *
     * @return string
     */
    public function get_visibility_type() {
        return $this->visibility['type'];
    }

    /**
     * Obtener orden.
     *
     * @return int
     */
    public function get_order() {
        return $this->order;
    }

    /**
     * Obtener estado.
     *
     * @return string
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Verificar si el aviso está activo.
     *
     * @return bool
     */
    public function is_active() {
        return 'active' === $this->status;
    }

    /**
     * Verificar si el aviso está programado para mostrarse ahora.
     *
     * @return bool
     */
    public function is_scheduled_now() {
        $now = current_time( 'timestamp' );

        // Si no hay fecha de inicio ni fin, siempre mostrar.
        if ( empty( $this->schedule['start_date'] ) && empty( $this->schedule['end_date'] ) ) {
            return true;
        }

        // Verificar fecha de inicio.
        if ( ! empty( $this->schedule['start_date'] ) ) {
            $start_datetime = $this->schedule['start_date'] . ' ' . $this->schedule['start_time'];
            $start_timestamp = strtotime( $start_datetime );

            if ( $now < $start_timestamp ) {
                return false;
            }
        }

        // Verificar fecha de fin.
        if ( ! empty( $this->schedule['end_date'] ) ) {
            $end_datetime = $this->schedule['end_date'] . ' ' . $this->schedule['end_time'];
            $end_timestamp = strtotime( $end_datetime );

            if ( $now > $end_timestamp ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convertir a array.
     *
     * @return array
     */
    public function to_array() {
        return array(
            'id'         => $this->id,
            'title'      => $this->title,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'content'    => $this->content,
            'styles'     => $this->styles,
            'schedule'   => $this->schedule,
            'placement'  => $this->placement,
            'visibility' => $this->visibility,
            'order'      => $this->order,
        );
    }

    /**
     * Crear instancia desde array.
     *
     * @param array $data Datos del aviso.
     * @return self
     */
    public static function from_array( array $data ) {
        return new self( $data );
    }
}
