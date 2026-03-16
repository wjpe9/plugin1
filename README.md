=== CuPe Prices System ===
Contributors: CuscoPeru
Tags: prices, currency, acf, shortcode, oxygen, polylang, tourism, multilingual
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema de precios base en USD para WordPress con soporte para lectura desde ACF/meta, precio manual por shortcode, conversión de moneda, geolocalización básica, almacenamiento local de tasas y compatibilidad inicial con Oxygen y Polylang.

== Description ==

CuPe Prices System es un plugin diseñado para centralizar y renderizar precios dentro de un sitio WordPress multilenguaje y orientado a turismo, donde el precio base siempre parte de USD y luego puede convertirse a otras monedas para su visualización.

El plugin fue planteado para resolver escenarios en los que actualmente los precios suelen mostrarse directamente desde ACF o desde meta fields dentro de Oxygen, por ejemplo usando estructuras como:

[oxygen ... data='custom_acf_content' settings_path='precio' ... ]
o
[oxygen ... data='meta' key='precio' ]

En lugar de depender del render directo de Oxygen o de escribir la moneda manualmente en la vista, este plugin encapsula la lógica de precios en una sola capa reutilizable mediante shortcode.

Principales objetivos del plugin:

- Tomar un precio base desde un campo ACF/meta del post.
- Permitir también ingresar un precio manual directamente en el shortcode.
- Asumir siempre que el valor de origen está en USD.
- Convertir el precio a otra moneda de visualización.
- Permitir forzar moneda por shortcode.
- Permitir conservar preferencia de moneda vía query string + cookie.
- Resolver automáticamente una moneda sugerida según país del visitante.
- Consultar tasas desde una API gratuita y almacenarlas localmente.
- Refrescar las tasas automáticamente cada 48 horas.
- Mantener fallback seguro a tasas manuales si la API falla.
- Renderizar una salida HTML estructurada y estilizable.
- Preparar la base para compatibilidad con Polylang y futuras reglas por idioma o país.
- Reemplazar usos rígidos de precios en Oxygen por una capa centralizada y escalable.

== How it works ==

El plugin trabaja en varias capas:

1. Resolución del precio base
El sistema decide primero de dónde sale el valor del precio:
- desde el atributo manual "price" del shortcode, o
- desde un campo ACF/meta del post, por defecto "precio"

2. Resolución del post fuente
Si el sitio usa Polylang, el plugin deja preparada una capa para intentar resolver el post fuente por idioma. Si no puede hacerlo, usa el post actual como fallback seguro.

3. Resolución de moneda
La moneda de salida se determina en este orden:
- moneda forzada con el atributo "currency"
- moneda enviada por query string ?cupe_currency=XXX
- moneda guardada en cookie
- moneda resuelta por geolocalización de país
- USD como fallback

4. Geolocalización
El plugin intenta detectar el país del visitante a través de headers disponibles del servidor o CDN, por ejemplo:
- HTTP_CF_IPCOUNTRY
- CF_IPCOUNTRY
- HTTP_X_COUNTRY_CODE
- GEOIP_COUNTRY_CODE

Si el país se detecta correctamente, se busca una moneda asociada en el mapa país → moneda.

5. Conversión
El valor base en USD se convierte a la moneda final usando tasas almacenadas localmente. Estas tasas se obtienen desde una API gratuita y se guardan en WordPress para no depender de una llamada remota en cada visita.

6. Refresco de tasas
Las tasas se actualizan cada 48 horas mediante WP-Cron y también pueden refrescarse manualmente desde el panel admin. Si la API falla, el sistema usa la última tasa válida guardada o, en su defecto, tasas manuales de respaldo.

7. Formato
El valor convertido se redondea y se formatea según la moneda:
- símbolo
- separador decimal
- separador de miles
- código de moneda opcional

8. Render visual
El sistema genera un HTML estructurado con clases CSS reutilizables para que el precio pueda estilizarse fácilmente desde el theme, Oxygen o CSS personalizado.

== Confirmed data source ==

En el sistema actual del sitio, el campo que almacena el precio fue identificado como:

precio

Ese valor aparece disponible como meta key real del post, por lo que el plugin lo consulta directamente usando esta lógica:

- primero intenta get_field('precio', $post_id)
- luego usa get_post_meta($post_id, 'precio', true) como fallback

Esto permite trabajar sin depender del render interno de Oxygen.

== Current features ==

- Shortcode principal: [cupe_price]
- Precio manual en USD
- Precio leído desde ACF/meta
- Campo ACF/meta configurable por atributo
- Forzado manual de moneda
- Uso de cookie para preferencia de moneda
- Cambio de moneda por query string
- Geolocalización básica por país
- Mapa país → moneda
- API gratuita para tasas de cambio
- Almacenamiento local de tasas en wp_options
- Refresco automático de tasas cada 48 horas
- Refresco manual desde panel admin
- Fallback a tasas manuales
- Formato visual configurable por atributos
- Compatibilidad inicial con Polylang
- HTML con clases CSS listas para customizar
- Panel admin básico de diagnóstico
- Integración amigable con Oxygen

== Installation ==

1. Subir la carpeta del plugin a:
wp-content/plugins/CuPe-Prices-System/

2. Activar el plugin desde el panel de WordPress.

3. Usar el shortcode [cupe_price] dentro de:
- Oxygen
- contenido de WordPress
- plantillas
- widgets compatibles con shortcodes
- reusables o bloques compatibles

== Folder structure ==

CuPe-Prices-System/
├── CuPe-Prices-System.php
├── README.md
├── assets/
│   └── css/
│       └── cups-prices-public.css
├── src/
│   ├── Autoloader.php
│   ├── Plugin.php
│   ├── Admin/
│   │   └── SettingsPage.php
│   ├── Currency/
│   │   ├── ApiRateProvider.php
│   │   ├── CurrencyResolver.php
│   │   ├── ExchangeRateCron.php
│   │   ├── ExchangeRateService.php
│   │   └── ManualRateProvider.php
│   ├── Geo/
│   │   └── GeoCountryResolver.php
│   ├── Integrations/
│   │   ├── ACF/
│   │   │   └── ACFPriceSource.php
│   │   └── Polylang/
│   │       └── PolylangResolver.php
│   ├── Pricing/
│   │   ├── PriceSourceResolver.php
│   │   ├── PriceResolver.php
│   │   ├── PriceFormatter.php
│   │   └── PriceRenderer.php
│   ├── Shortcodes/
│   │   └── PriceShortcode.php
│   ├── Storage/
│   │   └── ExchangeRateStorage.php
│   └── Support/
│       ├── Defaults.php
│       └── CountryMap.php
├── templates/
│   └── price.php
└── assets/
    └── css/
        └── cups-prices-public.css

== Main shortcode ==

[cupe_price]

Este es el shortcode principal del plugin.

== Supported attributes ==

El shortcode actualmente soporta estos atributos:

- price
- field
- post_id
- source_lang
- currency
- label
- suffix
- context
- show_symbol
- show_code
- round
- decimals
- class

== Attribute reference ==

= price =
Precio manual base en USD.
Si este atributo existe, tiene prioridad sobre ACF/meta.

Ejemplo:
[cupe_price price="1105"]

= field =
Nombre del campo ACF/meta a consultar.
Por defecto: precio

Ejemplo:
[cupe_price field="precio"]

= post_id =
Permite leer el precio de otro post específico.

Ejemplo:
[cupe_price post_id="123" field="precio"]

= source_lang =
Preparado para intentar resolver el post fuente por idioma en Polylang.
Si no aplica o falla, se usa el post actual.

Ejemplo:
[cupe_price field="precio" source_lang="es"]

= currency =
Fuerza manualmente la moneda de salida.

Valores válidos actuales:
- USD
- EUR
- PEN
- BRL
- CLP

Ejemplo:
[cupe_price price="1105" currency="EUR"]

= label =
Texto antes del precio.

Ejemplo:
[cupe_price price="1105" label="Desde"]

= suffix =
Texto después del precio.

Ejemplo:
[cupe_price price="1105" suffix="por persona"]

= context =
Agrega una clase de contexto visual para facilitar estilos.
No cambia la lógica del precio, solo la salida visual.

Ejemplos:
- default
- hero
- card
- inline
- sidebar
- promo

Ejemplo:
[cupe_price price="1105" context="hero"]

= show_symbol =
Muestra u oculta el símbolo de moneda.

Valores:
- true
- false

Ejemplo:
[cupe_price price="1105" show_symbol="false"]

= show_code =
Muestra u oculta el código ISO de moneda.

Valores:
- true
- false

Ejemplo:
[cupe_price price="1105" show_code="true"]

= round =
Modo de redondeo del monto convertido.

Valores soportados:
- ceil
- floor
- round
- none

Ejemplo:
[cupe_price price="1105.20" round="ceil"]

= decimals =
Número de decimales a mostrar.

Ejemplo:
[cupe_price price="1105.50" round="none" decimals="2"]

= class =
Clases CSS extra que se agregarán al wrapper del precio.

Ejemplo:
[cupe_price price="1105" class="precio-destacado"]

== Default behavior ==

Si usas simplemente:

[cupe_price]

el plugin hará esto:
- tomará el post actual
- intentará leer el campo "precio"
- usará USD como base
- resolverá moneda según atributos, cookie, geolocalización o fallback
- buscará una tasa válida guardada localmente
- renderizará el HTML del precio

== All current shortcode examples ==

= 1. Prueba mínima =
[cupe_price]

= 2. Precio desde ACF/meta del post actual =
[cupe_price field="precio"]

= 3. Precio manual en USD =
[cupe_price price="1105"]

= 4. Precio manual con decimal =
[cupe_price price="1105.50"]

= 5. Precio manual con etiqueta =
[cupe_price price="1105" label="Desde"]

= 6. Precio manual con etiqueta y sufijo =
[cupe_price price="1105" label="Desde" suffix="por persona"]

= 7. Precio desde ACF con etiqueta y sufijo =
[cupe_price field="precio" label="Desde" suffix="por persona"]

= 8. Forzar USD =
[cupe_price price="1105" currency="USD"]

= 9. Forzar EUR =
[cupe_price price="1105" currency="EUR"]

= 10. Forzar PEN =
[cupe_price price="1105" currency="PEN"]

= 11. Forzar BRL =
[cupe_price price="1105" currency="BRL"]

= 12. Forzar CLP =
[cupe_price price="1105" currency="CLP"]

= 13. ACF + moneda forzada =
[cupe_price field="precio" currency="EUR"]

= 14. Mostrar código de moneda =
[cupe_price price="1105" currency="EUR" show_code="true"]

= 15. Mostrar código desde ACF =
[cupe_price field="precio" currency="USD" show_code="true"]

= 16. Ocultar símbolo =
[cupe_price price="1105" currency="EUR" show_symbol="false"]

= 17. Ocultar símbolo y mostrar código =
[cupe_price price="1105" currency="EUR" show_symbol="false" show_code="true"]

= 18. Contexto default =
[cupe_price price="1105" context="default"]

= 19. Contexto hero =
[cupe_price price="1105" label="Desde" suffix="por persona" context="hero"]

= 20. Contexto card =
[cupe_price field="precio" label="Desde" suffix="por persona" context="card"]

= 21. Contexto inline =
[cupe_price price="1105" context="inline"]

= 22. Contexto sidebar =
[cupe_price price="1105" context="sidebar"]

= 23. Contexto promo =
[cupe_price price="1105" context="promo"]

= 24. Clase CSS extra =
[cupe_price price="1105" class="mi-precio-especial"]

= 25. Varias clases =
[cupe_price price="1105" class="mi-precio-especial precio-destacado"]

= 26. Contexto + clase extra =
[cupe_price price="1105" context="hero" class="price-hero-main"]

= 27. Redondeo ceil =
[cupe_price price="1105.20" round="ceil"]

= 28. Redondeo floor =
[cupe_price price="1105.80" round="floor"]

= 29. Redondeo round =
[cupe_price price="1105.50" round="round"]

= 30. Sin redondeo previo =
[cupe_price price="1105.50" round="none" decimals="2"]

= 31. Sin decimales =
[cupe_price price="1105.50" decimals="0"]

= 32. Con 2 decimales =
[cupe_price price="1105.50" round="none" decimals="2"]

= 33. Leer precio desde otro post =
[cupe_price post_id="123" field="precio"]

= 34. Otro post + moneda forzada =
[cupe_price post_id="123" field="precio" currency="EUR"]

= 35. Uso de source_lang =
[cupe_price field="precio" source_lang="es"]

= 36. source_lang + post_id =
[cupe_price post_id="123" field="precio" source_lang="es"]

= 37. Caso completo desde ACF =
[cupe_price field="precio" label="Desde" suffix="por persona" context="card" class="precio-tour-main"]

= 38. Caso completo manual =
[cupe_price price="1105" label="Desde" suffix="por persona" context="hero" currency="EUR" show_code="true"]

== Query string currency switch ==

El plugin también permite fijar moneda mediante query string:

?cupe_currency=EUR

Ejemplo:
https://tusitio.com/tour/machu-picchu/?cupe_currency=EUR

Cuando esto sucede:
- el plugin sanitiza la moneda
- guarda la preferencia en cookie
- los siguientes shortcodes sin currency forzada tenderán a usar esa moneda

Monedas válidas actuales:
- USD
- EUR
- PEN
- BRL
- CLP

== Geolocation ==

El plugin ya incluye una capa básica de geolocalización por país para resolver una moneda automática cuando no se ha forzado una moneda manualmente.

Headers actualmente considerados:
- HTTP_CF_IPCOUNTRY
- CF_IPCOUNTRY
- HTTP_X_COUNTRY_CODE
- GEOIP_COUNTRY_CODE

Ejemplos de mapeo actual:
- PE => PEN
- ES => EUR
- BR => BRL
- CL => CLP
- US => USD

Si no se detecta un país válido o no existe en el mapa, el sistema usa USD como fallback final.

== Exchange rate storage ==

Las tasas ya no dependen solo de valores manuales en código como fuente principal.

El sistema actual:
- consulta una API gratuita
- guarda las tasas en wp_options
- las usa localmente en frontend
- evita consultar la API en cada visita

Option principal usada:
cupe_prices_exchange_rates

El payload guardado incluye:
- base
- provider
- status
- fetched_at_gmt
- expires_at_gmt
- rates
- last_error

== Automatic refresh ==

Las tasas se refrescan automáticamente cada 48 horas mediante WP-Cron.

Hook del cron:
cupe_prices_refresh_exchange_rates

Además del cron, el plugin mantiene refresh “lazy” si detecta que la tasa ya venció y todavía no se actualizó.

== Admin panel ==

El plugin ya incluye una página básica de administración en:

Settings → CuPe Prices System

Actualmente permite:

- ver moneda base
- ver provider
- ver estado de las tasas
- ver última actualización
- ver próxima actualización
- ver último error
- ver tasas almacenadas
- ver país detectado
- ver moneda sugerida por geolocalización
- ver moneda final resuelta
- ver cookie activa
- ver header de país detectado
- refrescar tasas manualmente
- limpiar la cookie de moneda

== Example replacement for Oxygen ==

Si hoy en Oxygen se usa algo como:

[oxygen ... settings_path='precio' ... ] USD

puede reemplazarse por:

[cupe_price field="precio" label="Desde" suffix="por persona" context="card"]

o por un valor manual:

[cupe_price price="1105" label="Desde" suffix="por persona" context="card"]

Esto permite que Oxygen deje de depender del precio crudo y use el sistema centralizado del plugin.

== Output HTML structure ==

La salida del plugin se renderiza con una estructura HTML similar a esta:

<div class="cupe-price cupe-price--currency-usd cupe-price--context-card cupe-price--source-acf">
    <span class="cupe-price__label">Desde</span>
    <span class="cupe-price__main">
        <span class="cupe-price__symbol">$</span>
        <span class="cupe-price__amount">1,105</span>
        <span class="cupe-price__code">USD</span>
    </span>
    <span class="cupe-price__suffix">por persona</span>
</div>

== CSS classes generated ==

Clases principales:
- cupe-price
- cupe-price--currency-usd
- cupe-price--currency-eur
- cupe-price--currency-pen
- cupe-price--currency-brl
- cupe-price--currency-clp
- cupe-price--context-default
- cupe-price--context-hero
- cupe-price--context-card
- cupe-price--context-inline
- cupe-price--source-manual
- cupe-price--source-acf

Subelementos:
- cupe-price__label
- cupe-price__main
- cupe-price__symbol
- cupe-price__amount
- cupe-price__code
- cupe-price__suffix

== Current exchange rates ==

El sistema actualmente usa esta estrategia:

1. intenta usar tasas válidas guardadas en wp_options
2. si vencieron, intenta refrescarlas desde API gratuita
3. si la API falla, usa la última tasa válida guardada
4. si no existe ninguna, usa tasas manuales de respaldo

Tasas manuales actuales de respaldo:
- USD => 1.00
- EUR => 0.92
- PEN => 3.75
- BRL => 4.95
- CLP => 970.00

== Limitations in this current version ==

- La resolución de Polylang sigue siendo inicial y con fallback seguro.
- No existe todavía selector visual de moneda por shortcode independiente.
- No existe aún sincronización avanzada por canonical ID personalizado.
- El mapa país → moneda todavía no es editable desde admin.
- La configuración global del plugin todavía es básica.
- La geolocalización depende de headers disponibles en servidor/CDN.

== Recommended next steps ==

Siguientes mejoras sugeridas:
- selector visual de moneda en frontend
- panel admin más avanzado de configuración
- mapeo editable país → moneda
- mejor resolución multilenguaje/canonical
- soporte para formatos regionales avanzados
- integración con reglas de mercado o país
- overrides de precio por contexto comercial
- configuración de defaults desde admin

== Changelog ==

= 0.1.0 =
- Base inicial del plugin
- Shortcode [cupe_price]
- Soporte para precio manual y ACF/meta
- Campo por defecto "precio"
- Formato y conversión básica de moneda
- Render HTML estructurado
- Integración inicial con Oxygen
- Compatibilidad inicial con Polylang
- Geolocalización básica por país
- API gratuita para tasas de cambio
- Almacenamiento local en wp_options
- Refresco automático cada 48 horas con WP-Cron
- Fallback a tasas manuales
- Panel admin básico para diagnóstico y refresco manual