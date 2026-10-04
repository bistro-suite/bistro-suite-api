# Bistro Suite — mapa de pantallas y flujos (borrador)

**Estado:** propuesta de producto derivada del cliente legado. No es un diseño visual final ni una especificación de API.
**Propósito:** conservar las reglas y tareas de negocio que sí aparecen en la implementación, mientras se separan de las mejoras sugeridas para una experiencia moderna y más clara para bistros.

## Cómo leer este documento

- **Hecho legado**: comportamiento o contenido que se observa en `cars-admin-client`; no implica que deba mantenerse igual.
- **Propuesta**: dirección recomendada para Bistro Suite. Requiere validación comercial cuando se indica.
- **Pendiente**: detalle que el código no permite confirmar o que depende de decisiones del negocio.

El cliente antiguo es una SPA Mithril 0.2 con rutas `/#/`, `/#/login` y `/#/admin`. La interfaz pública incluye partes de una plantilla de restaurante (Inicio, Restaurante, Menú, Horarios, Testimonios, Contáctenos), pero la aplicación de pedidos se concentra en una lista de productos y modales. Los PNG `cars-public.png` y `cars-admin.png` son capturas de esa implementación, no diseños objetivo.

## Inventario observado en el legado

| Área / pantalla | Evidencia histórica | Acciones y datos visibles |
|---|---|---|
| Catálogo público | `app/components/car/list.js`; captura `cars-public.png` | Rejilla de productos disponibles; nombre, precio COP e imagen; enlace para compartir producto por Facebook; dirección del negocio, cobertura y resumen del carrito. Paginación por desplazamiento. |
| Cobertura | `app/components/car/coverage.js`, `modalcoverage.js` | Modal con texto “Solo Cúcuta” e imagen `app/assets/images/image-coverage.png`. La configuración de dirección y mapa vive en `app/config.js`. |
| Detalle de producto | `app/components/car/modalproduct.js` | Imagen, nombre, precio, descripción; cantidad y observación libre opcional por artículo; agregar al carrito. |
| Carrito / iniciar pedido | `app/components/car/indicator.js`, `modalindicator.js` | Lista de artículos, cantidad, precio y total; quitar artículo; cancelar pedido; seleccionar domicilio o pasar por local. Teléfono requerido, email opcional; barrio y dirección requeridos para domicilio. |
| Acceso y registro del cliente | `app/components/car/login.js`, `modallogin.js`, `models.js` | Para completar pedido se solicita iniciar sesión. Hay login por email/contraseña, registro y conexión con Facebook. El formulario de registro pide nombre, número de identificación y celular; teléfono fijo, barrio y dirección aparecen como datos de perfil. Después del login se continúa el pedido pendiente. |
| Confirmación | `app/components/car/list.js` | Al guardar aparece alerta de éxito o error; se limpia el carrito. No se observa una pantalla de recibo con resumen persistente ni seguimiento para el cliente. |
| Login de administración | `app/components/login/login.js`; ruta `/#/login` | Email y contraseña; validación de rol, sesión y acceso a la consola. |
| Pedidos (administración) | `app/components/admin/orders.js`; captura `cars-admin.png` | Tabla/listado paginado; cliente, fecha, estado, tipo de entrega y acciones; consulta, edición y cambio de estado; formulario lateral para crear pedido manual con cliente, productos, cantidades y entrega. Refresca el listado cada 60 segundos. |
| Clientes (administración) | `app/components/admin/clients.js` | Listado y formulario de detalle/edición/creación; nombre, contacto, email, dirección y barrio; acceso a perfil Facebook legado; borrado con confirmación. |
| Productos (administración) | `app/components/admin/products.js` | Listado y formulario para alta/edición; nombre, descripción, precio, IVA, disponibilidad e imagen; borrado con confirmación. No se observan categorías, modificadores/variantes, inventario ni horario de disponibilidad. |
| Navegación del admin | `app/containers/admin/admin.js` | Una consola con pestañas Pedidos, Clientes y Productos; control de sesión y cerrar sesión. |

### Reglas de negocio respaldadas por el código

- El catálogo público solicita únicamente productos disponibles. La tarjeta enlaza a un detalle de producto.
- El artículo del carrito admite una cantidad y una observación libre; el modelo de pedido contempla varias líneas.
- Los tipos de entrega se llaman “Domicilio” y “En local” (en el checkout se presenta como “Pasar por local”). El domicilio exige barrio y dirección.
- Los estados visibles/modelados son **Pendiente**, **Confirmado**, **Cancelado** y **Entregado** (`app/components/car/models.js`). El admin podía guardar cambios de estado desde el selector.
- La administración permite crear pedidos manuales asociando un cliente y seleccionando productos.
- El producto histórico usa precio, descripción, disponibilidad e IVA. La implementación de checkout no presenta desglose de IVA ni cargo de domicilio.
- El cliente necesitaba autenticarse antes de enviar el pedido. Email/contraseña y Facebook aparecen en el frontend legado.

No tomar como requisitos el uso de Facebook, el login obligatorio, la identificación nacional ni las decisiones técnicas de almacenamiento del cliente. Son decisiones históricas, no condiciones del negocio confirmadas hoy.

## Mapa propuesto para Bistro Suite

La arquitectura visual debería sentirse como un **bistro real, cálido y confiable**, con jerarquía de menú y pedido clara. La marca, las fotos y los colores se deben definir con el producto; evitar reutilizar textos de plantilla (“Cars”), logotipo genérico, imágenes demo repetidas o controles Blueprint/Bootstrap antiguos. Priorizar móvil, lectura rápida de precios y accesibilidad; el área de operación debe ser sobria y legible durante el servicio.

### Flujo público (propuesta)

```text
Tienda del bistro
  ├─ explorar menú → detalle de producto → añadir / ajustar cantidad y nota ─┐
  └─ cobertura, horarios y datos del local                              │
                                                                         ↓
Carrito → tipo de entrega → datos de contacto/dirección → revisar total → enviar
                                                                         ↓
                                                       recibo con número y estado inicial
```

1. **Tienda / menú**: encabezado con identidad del bistro, ubicación/horario y estado abierto/cerrado si se configura; categorías si el negocio las valida; búsqueda simple solo si el catálogo lo requiere. Cada tarjeta muestra foto, nombre y precio; disponibilidad se comunica claramente. Acceso persistente al carrito y al método de contacto.
2. **Detalle de producto**: foto y descripción legibles; cantidad; observación opcional. Variantes o extras solo se añaden cuando el negocio confirme opciones y precios. Mostrar el precio de la selección antes de agregar.
3. **Carrito**: editar cantidades/notas y eliminar líneas; subtotales y total explícitos. No presentar impuestos, envío o descuentos sin reglas configuradas. Mantener el carrito al navegar.
4. **Entrega y contacto**: elegir domicilio o recogida. Para domicilio pedir barrio/dirección y validar cobertura configurable; para recogida informar dirección/horario del local. Solicitar nombre y celular para coordinar; email opcional. **Propuesta:** permitir compra como invitado y ofrecer crear cuenta después, en lugar de bloquear la compra con login. No exigir número de identificación por defecto.
5. **Revisión y confirmación**: resumen final (productos, cantidades, notas, entrega, contacto, cargos y total), acción inequívoca para enviar y estado de procesamiento. Luego mostrar un recibo con identificador, resumen y estado inicial. La notificación por email/WhatsApp y el seguimiento desde una cuenta son decisiones posteriores, no capacidades del legado.

### Flujo de operación (propuesta)

```text
Acceso del equipo → Pedidos (cola operativa) → detalle → confirmar / cancelar / entregar
                                  └→ crear pedido manual
Menú → productos (alta, edición, disponibilidad)
Clientes → búsqueda y ficha (solo si se conserva una libreta de clientes)
Configuración del bistro → identidad, dirección, horarios, cobertura y entrega
```

- **Acceso del equipo**: inicio de sesión para administradores/operadores; permisos y recuperación de acceso definidos por la plataforma. Separar auth de cliente y staff.
- **Pedidos**: pantalla inicial del admin con cola ordenable/filtrable por estado, fecha y modalidad; indicadores concisos y actualización comprensible. Abrir un detalle con líneas, notas, totales, contacto y dirección; ofrecer solo acciones válidas para el estado. Conservar estados históricos del legado como base y definir transiciones permitidas en servidor. Estados de cocina adicionales son opcionales y requieren validación.
- **Pedido manual**: acción visible desde pedidos; seleccionar/crear contacto, agregar productos, escoger modalidad y guardar con confirmación del total. Marcar claramente quién lo registró y evitar que la edición modifique importes ya confirmados.
- **Menú / productos**: buscar y gestionar disponibilidad, foto, nombre, descripción y precio. Diseñar categorías, variantes, IVA e inventario como capacidades configurables o futuras hasta confirmar su necesidad.
- **Clientes**: buscar y consultar contactos e historial si hay razón operativa para conservarlos. Eliminar el enlace público a Facebook como dependencia. Aplicar acceso mínimo a datos personales y definir retención/exportación.
- **Configuración del bistro**: datos de identidad, dirección, horarios, cobertura/zonas, modalidad de recogida y reglas de cargos. En el piloto se puede precargar Cúcuta por el antecedente histórico, pero los valores deben ser editables y confirmados.

## Mejoras de experiencia y seguridad que afectan el flujo

- **Propuestas**: guest checkout; validación de cobertura antes de aceptar domicilio; persistencia de carrito; resumen antes de enviar; recibo consultable; estados con acciones guiadas; interfaz adaptable a móvil y teclado; vacíos/errores/carga explícitos.
- **Integridad del pedido**: guardar en cada línea un snapshot del nombre/precio/impuestos aplicados al momento de confirmar. El legado consulta precio de producto vivo al calcular, por lo que editar el menú podría alterar la representación de pedidos pasados. El nuevo checkout debe calcular importes del lado servidor.
- **Privacidad y permisos**: no usar Facebook como requisito ni exponer perfiles; pedir solo los datos necesarios; proteger rutas y acciones administrativas en servidor; auditoría básica para cambios de estado y pedidos manuales.
- **Operación**: no asumir pagos online, notificaciones automáticas, descuentos, costos de domicilio ni tiempos de preparación. Primero validar el proceso real; dejar estos elementos configurables o fuera del MVP hasta entonces.

## Decisiones por validar antes de cerrar diseño

1. ¿El primer bistro piloto sigue en Cúcuta? ¿Qué barrios/zonas cubre, horarios, costo y límites de domicilio?
2. ¿Se acepta pedido como invitado? ¿Qué datos mínimos necesita realmente el equipo para gestionarlo? ¿Se retiene una libreta de clientes?
3. ¿Hay categorías, tamaños, extras, disponibilidad por horario o stock que deban representarse desde el MVP?
4. ¿Cómo se cobra IVA y domicilio, si aplica? ¿Hay descuentos o mínimo de compra?
5. ¿Cómo se confirma y comunica el pedido hoy? ¿Hay pagos (efectivo/transferencia/otro), propinas, cancelaciones, retiro y tiempos de preparación que debamos reflejar?
6. ¿Se mantienen los cuatro estados actuales o hay estados operativos adicionales y transiciones específicas?
7. ¿Qué personas usarán el admin (dueño, caja, cocina) y qué acciones debe tener cada rol?
8. ¿La multi-tienda debe quedar modelada desde el principio? Los repos antiguos reflejan un negocio/sitio, así que esto no se puede inferir del legado.

## Alcance recomendado del siguiente diseño

Hacer wireframes de baja fidelidad para: menú móvil/desktop, detalle, carrito, entrega y contacto, revisión/recibo, login de staff, cola de pedidos/detalle, pedido manual, productos y configuración mínima del bistro. La pantalla de clientes puede quedar en el mapa, pero su prioridad depende de confirmar si la libreta es necesaria. Diseñar primero el flujo feliz y estados vacíos, carga, error de envío y pedido fuera de cobertura; usar datos de ejemplo realistas sin presentar precios heredados como vigentes.

## Fuentes revisadas y limitaciones

- Cliente: `cars-admin-client/app/initialize.js`, `app/components/car/list.js`, `modalproduct.js`, `modalindicator.js`, `modallogin.js`, `models.js`, `coverage.js`, `modalcoverage.js`, `app/components/admin/orders.js`, `products.js`, `clients.js`, `app/containers/admin/admin.js`.
- Capturas: `cars-admin-client/cars-public.png`, `cars-admin-client/cars-admin.png`.
- Assets relevantes: `app/assets/images/image-coverage.png`, `app/assets/resources/images/logo.png`, `car_background.jpg`, iconos y fuentes antiguas. Hay imágenes/carpetas de plantilla genérica; inspeccionarlas y licenciarlas/renovarlas antes de uso en el producto nuevo.
- Este mapa se basa en el frontend. No confirma todas las validaciones ni reglas del servidor, el tratamiento histórico de impuestos/cargos, políticas de datos, el proceso humano real, ni las operaciones actuales del negocio. Contrastar con el API y con quien opera el bistro antes de congelar requisitos.
