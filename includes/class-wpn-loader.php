<?php
/**
 * Registra todos los hooks del plugin.
 *
 * @package WP_Product_Notices
 * @subpackage WP_Product_Notices/includes
 */

// Si se accede directamente, salir.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase que gestiona los hooks del plugin.
 *
 * @since 1.0.0
 */
class WPN_Loader {

    /**
     * Array de acciones registradas.
     *
     * @var array
     */
    protected $actions = array();

    /**
     * Array de filtros registrados.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Añadir una nueva acción al array de acciones.
     *
     * @param string $hook          Nombre del hook de WordPress.
     * @param object $component     Instancia del objeto donde se define el callback.
     * @param string $callback      Nombre del método del callback.
     * @param int    $priority      Prioridad del hook.
     * @param int    $accepted_args Número de argumentos aceptados.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Añadir un nuevo filtro al array de filtros.
     *
     * @param string $hook          Nombre del hook de WordPress.
     * @param object $component     Instancia del objeto donde se define el callback.
     * @param string $callback      Nombre del método del callback.
     * @param int    $priority      Prioridad del hook.
     * @param int    $accepted_args Número de argumentos aceptados.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Utilidad para registrar hooks en los arrays.
     *
     * @param array  $hooks         Array de hooks existentes.
     * @param string $hook          Nombre del hook.
     * @param object $component     Instancia del componente.
     * @param string $callback      Método callback.
     * @param int    $priority      Prioridad.
     * @param int    $accepted_args Argumentos aceptados.
     * @return array
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );

        return $hooks;
    }

    /**
     * Registrar todos los hooks con WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ( $this->actions as $hook ) {
            add_action(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
