=== WP Product Notices ===
Contributors: nexirmarketing
Tags: woocommerce, notices, product, alerts, shipping
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Muestra avisos personalizables en páginas de producto de WooCommerce con programación por fechas y filtros por categoría/producto.

== Description ==

WP Product Notices te permite crear y mostrar avisos informativos en las páginas de producto de tu tienda WooCommerce. Perfecto para:

* Informar sobre fechas límite de pedido (Navidad, Black Friday, etc.)
* Mostrar tiempos de entrega estimados
* Destacar promociones especiales
* Informar sobre disponibilidad de stock
* Mostrar información de envío

= Características principales =

* **Múltiples avisos**: Crea tantos avisos como necesites
* **Programación por fechas**: Define fechas de inicio y fin para mostrar avisos automáticamente
* **Personalización completa**: Texto, icono, colores de fondo, borde, texto e icono
* **3 plantillas de diseño**: Standard (borde lateral), Minimal (badge), Highlighted (destacado)
* **Posicionamiento flexible**: Elige dónde mostrar el aviso en la página de producto
* **Visibilidad granular**: Muestra avisos en todos los productos, categorías específicas o productos individuales
* **+25 iconos incluidos**: Librería de iconos para envío, comercio, información y más
* **Compatible con temas**: Diseño responsive que se adapta a cualquier tema

= Hooks de WooCommerce disponibles =

* Antes del producto (inicio)
* Antes del resumen (después de imagen)
* En el resumen del producto (varias posiciones)
* Después de "Añadir al carrito"
* Después de los metadatos
* Después del resumen (antes de tabs)
* Al final del producto

== Installation ==

1. Sube la carpeta `wp-product-notices` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' en WordPress
3. Ve a 'Avisos Producto' en el menú de administración
4. Crea tu primer aviso

== Frequently Asked Questions ==

= ¿Necesito WooCommerce para usar este plugin? =

Sí, WP Product Notices requiere WooCommerce para funcionar.

= ¿Puedo personalizar los colores de los avisos? =

Sí, cada aviso permite personalizar el color de fondo, borde, texto e icono. También hay presets de colores predefinidos.

= ¿Los avisos se pueden programar? =

Sí, puedes definir una fecha de inicio y/o fin para cada aviso. El aviso solo se mostrará durante ese período.

= ¿Puedo mostrar diferentes avisos en diferentes productos? =

Sí, puedes configurar cada aviso para que se muestre en todos los productos, solo en categorías específicas, o solo en productos específicos.

= ¿Puedo personalizar las plantillas? =

Sí, puedes crear plantillas personalizadas en tu tema. Crea una carpeta `wp-product-notices` en tu tema y añade archivos como `notice-standard.php`, `notice-minimal.php` o `notice-highlighted.php`.

== Screenshots ==

1. Listado de avisos en el panel de administración
2. Formulario de creación/edición de aviso
3. Selector de iconos
4. Selector de colores con presets
5. Aviso con plantilla Standard en la página de producto
6. Aviso con plantilla Minimal
7. Aviso con plantilla Highlighted

== Changelog ==

= 1.0.0 =
* Versión inicial
* 3 plantillas de diseño (Standard, Minimal, Highlighted)
* +25 iconos incluidos
* Programación por fechas
* Visibilidad por categorías y productos
* 6 presets de colores
* Compatible con WooCommerce HPOS

== Upgrade Notice ==

= 1.0.0 =
Versión inicial del plugin.
