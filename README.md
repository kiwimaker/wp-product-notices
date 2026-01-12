# WP Product Notices

**Contributors:** [nexirmarketing](https://profiles.wordpress.org/nexirmarketing/)  
**Donate link:** https://nexir.es  
**Tags:** woocommerce, notices, product, alerts, shipping, flatsome, elementor  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Requires PHP:** 7.4  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Muestra avisos personalizables en páginas de producto de WooCommerce con programación por fechas y filtros por categoría/producto.

## Description

**WP Product Notices** te permite crear y mostrar avisos informativos en las páginas de producto de tu tienda WooCommerce. Perfecto para comunicar información importante a tus clientes de forma visual y atractiva.

### Casos de uso

* Informar sobre fechas límite de pedido (Navidad, Black Friday, etc.)
* Mostrar tiempos de entrega estimados
* Destacar promociones especiales
* Informar sobre disponibilidad de stock
* Mostrar información de envío gratuito
* Avisos de productos personalizados o bajo demanda
* Información de garantía o devoluciones

### Características principales

* **Múltiples avisos**: Crea tantos avisos como necesites y ordénalos con drag & drop
* **Programación por fechas**: Define fechas de inicio y fin para mostrar avisos automáticamente
* **Personalización completa**: Texto, icono, colores de fondo, borde, texto e icono
* **3 plantillas de diseño**: Standard (borde lateral), Minimal (badge compacto), Highlighted (destacado)
* **Posicionamiento flexible**: Elige dónde mostrar el aviso en la página de producto
* **Visibilidad granular**: Muestra avisos en todos los productos, categorías específicas o productos individuales
* **+25 iconos incluidos**: Librería de iconos SVG para envío, comercio, información y soporte
* **6 presets de colores**: Verde, azul, naranja, rojo, morado y gris
* **Compatible con Page Builders**: Funciona con Flatsome, Elementor, Divi y otros constructores visuales
* **Shortcodes**: Inserta avisos en cualquier parte usando shortcodes
* **Responsive**: Diseño que se adapta a cualquier dispositivo

### Compatible con Page Builders

WP Product Notices incluye dos métodos de renderizado para máxima compatibilidad:

#### Método PHP (Hooks de WooCommerce)
El método tradicional usando hooks de WooCommerce. Funciona perfectamente con temas estándar.

#### Método JavaScript
Inyecta los avisos después de que la página carga. Ideal para:

* Flatsome
* Elementor
* Divi
* Beaver Builder
* Y otros Page Builders que modifican los templates de WooCommerce

#### Shortcodes
También puedes usar shortcodes para insertar avisos manualmente:

```
[wpn_notice id="wpn_xxxxxxxxxxxx"]
```

Perfecto para usar con UX Blocks de Flatsome o widgets de Elementor.

### Hooks de WooCommerce disponibles

* Antes del producto (inicio)
* Antes del resumen (después de imagen)
* En el resumen del producto - Antes del título
* En el resumen del producto - Después del título
* En el resumen del producto - Después del precio
* En el resumen del producto - Después del extracto
* Después de "Añadir al carrito"
* Después de los metadatos (SKU, categorías, etc.)
* Después del resumen (antes de tabs)
* Al final del producto

### Integración con Flatsome

Si usas Flatsome y los avisos no aparecen con el método PHP:

1. Crea un aviso y copia el shortcode
2. Ve a **Flatsome → UX Blocks** y crea un nuevo UX Block
3. Pega el shortcode del aviso
4. Ve a **Flatsome → Theme Options → WooCommerce → Product Page**
5. Añade un elemento "Block" en la posición deseada
6. Selecciona el UX Block que creaste

Alternativamente, usa el método "JavaScript" en la configuración del aviso.

## Installation

### Instalación automática

1. Ve a Plugins → Añadir nuevo en tu panel de WordPress
2. Busca "WP Product Notices"
3. Haz clic en "Instalar ahora" y luego en "Activar"

### Instalación manual

1. Descarga el archivo ZIP del plugin
2. Sube la carpeta `wp-product-notices` al directorio `/wp-content/plugins/`
3. Activa el plugin desde el menú 'Plugins' en WordPress
4. Ve a 'Avisos Producto' en el menú de administración
5. Crea tu primer aviso

### Requisitos

* WordPress 5.8 o superior
* WooCommerce 5.0 o superior
* PHP 7.4 o superior

## Frequently Asked Questions

### ¿Necesito WooCommerce para usar este plugin?

Sí, WP Product Notices requiere WooCommerce para funcionar. El plugin se desactiva automáticamente si WooCommerce no está activo.

### ¿Puedo personalizar los colores de los avisos?

Sí, cada aviso permite personalizar completamente el color de fondo, borde, texto e icono usando selectores de color. También hay 6 presets de colores predefinidos para empezar rápidamente.

### ¿Los avisos se pueden programar?

Sí, puedes definir una fecha y hora de inicio y/o fin para cada aviso. El aviso solo se mostrará durante ese período, perfecto para campañas de temporada o promociones limitadas.

### ¿Puedo mostrar diferentes avisos en diferentes productos?

Sí, cada aviso se puede configurar para mostrar en:

* Todos los productos
* Solo en categorías específicas
* Solo en productos específicos

### ¿Funciona con Flatsome?

Sí. Puedes usar el método de renderizado "JavaScript" que funciona automáticamente, o usar el shortcode con UX Blocks para control total sobre la posición.

### ¿Funciona con Elementor?

Sí. Usa el método de renderizado "JavaScript" o inserta el shortcode en un widget de texto/shortcode de Elementor.

### ¿Puedo usar shortcodes?

Sí, cada aviso tiene un shortcode único que puedes copiar desde la pantalla de edición:

```
[wpn_notice id="wpn_xxxxxxxxxxxx"]
```

### ¿Puedo personalizar las plantillas?

Sí, puedes sobrescribir las plantillas en tu tema. Crea una carpeta `wp-product-notices/templates/` en tu tema y copia los archivos de plantilla que quieras modificar:

* `notice-standard.php`
* `notice-minimal.php`
* `notice-highlighted.php`

### ¿El plugin afecta al rendimiento?

No. El plugin está optimizado para cargar recursos solo en páginas de producto y solo cuando hay avisos activos. Los estilos y scripts son mínimos y están optimizados.

### ¿Es compatible con HPOS?

Sí, WP Product Notices es totalmente compatible con High-Performance Order Storage (HPOS) de WooCommerce.

## Screenshots

1. Listado de avisos en el panel de administración con drag & drop para ordenar
2. Formulario de creación/edición de aviso con vista previa en tiempo real
3. Selector de plantillas: Standard, Minimal y Highlighted
4. Selector de iconos organizados por categorías
5. Selector de colores con presets predefinidos
6. Configuración de visibilidad por categorías y productos
7. Opciones de posicionamiento y método de renderizado
8. Aviso con plantilla Standard en la página de producto
9. Aviso con plantilla Minimal (badge compacto)
10. Aviso con plantilla Highlighted (destacado con panel de icono)

## Changelog

### 1.0.0 - 2026-01-12

**Lanzamiento inicial**

* 3 plantillas de diseño (Standard, Minimal, Highlighted)
* +25 iconos SVG incluidos organizados por categorías
* Programación por fechas con hora de inicio y fin
* Visibilidad por categorías y productos específicos
* 6 presets de colores predefinidos
* Método de renderizado PHP (hooks de WooCommerce)
* Método de renderizado JavaScript (compatible con Page Builders)
* Shortcodes para inserción manual
* Vista previa en tiempo real en el editor
* Drag & drop para ordenar avisos
* Compatible con WooCommerce HPOS
* Compatible con Flatsome, Elementor y otros Page Builders
* Guía de integración con Flatsome incluida
* Totalmente traducible (español incluido)

## Upgrade Notice

### 1.0.0
Versión inicial del plugin. ¡Disfruta creando avisos para tus productos!

## Additional Info

**Desarrollado por** [Nexir Marketing](https://nexir.es)

**Soporte**: Si tienes alguna pregunta o problema, por favor abre un ticket en el [foro de soporte](https://wordpress.org/support/plugin/wp-product-notices/) o contacta con nosotros en [nexir.es](https://nexir.es).

**Contribuir**: El código fuente está disponible en GitHub. Las contribuciones son bienvenidas.