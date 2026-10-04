# Bistro Suite — especificación inicial del producto

**Estado:** borrador basado en la implementación histórica; falta validarlo con el negocio
**Fecha:** 2026-10-03
**Propósito:** conservar el conocimiento operativo existente y acordar qué modernizar antes de implementar.

## Lo que ya sabemos del negocio

La implementación histórica era una vitrina de productos y pedidos para un bistró de Cúcuta. El cliente veía productos disponibles con foto, descripción y precio en COP; podía compartir productos en Facebook. La cobertura de domicilios se describía como solo Cúcuta y se mostraba en un mapa.

Un pedido contenía productos, cantidades y observaciones por artículo. El cliente elegía domicilio o pasar por el local. Para domicilio debía proporcionar barrio y dirección; teléfono era requerido y correo opcional al confirmar. El total se calculaba con el precio de los productos y sus cantidades. No hay flujo de pago en línea en la implementación revisada.

El cliente podía iniciar sesión con Facebook o registrarse/iniciar sesión con correo y contraseña. El registro histórico pedía nombre, número de identificación, celular, barrio, dirección y correo; teléfono fijo era opcional. El panel permitía consultar y administrar clientes, productos y pedidos. El administrador podía crear pedidos manualmente, editar sus artículos y cambiar su estado. El listado de pedidos se actualizaba cada minuto. Hay una plantilla y una ruta de prueba de correo, pero la creación del pedido no dispara por sí misma una notificación; el correo no se considera un flujo operativo confirmado.

Los estados existentes eran **Pendiente, Confirmado, Cancelado y Entregado**. Las modalidades eran **Domicilio** y **Pasar por local**. El catálogo tenía nombre, descripción, valor, IVA, disponibilidad e imagen; no tenía categorías explícitas ni variantes configurables.

| Flujo histórico | Tratamiento en la nueva app |
|---|---|
| Menú de productos disponibles con foto, detalle y precio COP | Conservar; cambiar presentación y diseño para que sea claramente de bistró. |
| Compartir un producto en Facebook con página de vista previa | Conservar si Facebook sigue siendo un canal útil; permitir ampliar canales después. |
| Inicio de sesión con Facebook o registro/inicio con correo y contraseña | Conservar el perfil del cliente; decidir si el checkout también admite invitados. Facebook deja de ser requisito para comprar. |
| Nombre, identificación, celular, dirección/barrio, email y teléfono fijo opcional | Conservar solo los datos operativamente necesarios; validar si identificación y teléfono fijo siguen haciendo falta. |
| Carrito con cantidad y observación libre por artículo | Conservar; capturar precio e IVA históricos en cada línea del pedido. |
| Domicilio dentro de cobertura de Cúcuta o pasar por el local | Conservar ambas modalidades; convertir cobertura y tarifa en configuración del bistró. |
| Pedidos Pendiente, Confirmado, Cancelado o Entregado | Conservar los cuatro nombres en el MVP; definir transiciones permitidas en el servidor. |
| Panel de productos, clientes y pedidos; creación manual de pedidos | Conservar las tareas principales; renovar la experiencia de escritorio y móvil. |
| Sin pago electrónico ni consulta pública del estado del pedido | Mantener pagos fuera de la app para el piloto; el seguimiento privado sería una capacidad nueva por validar. |

## Producto propuesto

Bistro Suite moderniza esos flujos para que un bistró publique su menú, reciba pedidos y los atienda desde una consola web. La propuesta comercial es admitir varios bistrós con información y acceso separados, y comenzar el piloto con uno. La implementación histórica era de un solo negocio: la separación multi-bistró es una evolución nueva, no una regla heredada.

El alta de negocios y del primer administrador será manual durante el piloto. Cada bistró tendrá una página pública propia y una consola privada para el personal.

## Usuarios y permisos

- **Administrador del bistró:** configura datos del negocio y menú, administra su personal, clientes y pedidos.
- **Personal:** consulta y atiende pedidos de su bistró; no modifica la configuración ni administra cuentas.
- **Cliente:** consulta la oferta, arma su pedido y aporta los datos necesarios para entrega. Consultar el estado desde la app sería una capacidad nueva por validar.
- **Operación de Bistro Suite:** provisiona los negocios mientras el piloto tenga altas manuales.

Todas las consultas administrativas quedan limitadas al negocio del usuario. La implementación histórica solo distinguía administrador y cliente; separar administrador y personal es una propuesta nueva y se puede dejar para después del piloto si no hace falta.

## Dirección visual

La implementación mezclaba nombres y recursos de una plantilla de autos con funciones de menú y pedidos de restaurante. Se conserva el contenido y la operación aprendidos, no esa apariencia. La nueva interfaz debe poner comida, precio y modalidad de pedido al frente; mostrar una lista de pedidos legible para el personal; y funcionar bien en móvil. Las fotos y el tono de marca del bistró se definirán con el negocio antes de diseñar las pantallas.

## Flujos del MVP

### Publicar el menú

1. Operación crea el bistró y su administrador.
2. El administrador configura nombre, datos de contacto, ubicación, cobertura de domicilios y modalidad de recogida.
3. Crea y mantiene productos con nombre, descripción, valor en COP, IVA, imagen y disponibilidad.
4. El cliente ve los productos disponibles y puede abrir el detalle, añadir una cantidad y dejar una observación por artículo.
5. La página del producto permite compartirlo mediante una vista previa social. Facebook fue el canal existente; el canal se puede ampliar después.

Las categorías son una mejora propuesta para ordenar menús grandes; la implementación antigua tenía un catálogo plano. Se incluirán si el negocio confirma que las necesita desde el piloto.

### Registrar al cliente y hacer un pedido

1. El cliente se identifica o inicia sesión. El flujo histórico ofrecía Facebook y cuenta con correo/contraseña; la nueva app debe conservar un acceso sencillo y no depender obligatoriamente de Facebook.
2. Añade productos al carrito, indica cantidades y observaciones, y revisa el subtotal.
3. Elige **Domicilio** o **Pasar por el local**.
4. Para domicilio, proporciona barrio y dirección. Se verifica que la dirección esté dentro de la cobertura que configure el bistró. Para recoger, esos campos no son obligatorios.
5. Confirma un teléfono de contacto; correo queda opcional salvo que el negocio demuestre que lo necesita.
6. El servidor valida disponibilidad y precios y crea el pedido con sus artículos, observaciones y total calculado.
7. El cliente ve una confirmación de que el pedido fue recibido. Un enlace privado para seguirlo sería una capacidad nueva: la versión antigua confirmaba el envío, pero no ofrecía consulta del pedido al cliente.

La cuenta conserva los datos de contacto usados en pedidos anteriores para evitar repetirlos. El número de identificación que pedía el formulario histórico no se hará obligatorio sin una razón operativa o legal clara. El checkout como invitado queda como alternativa por validar, porque el flujo antiguo requería iniciar sesión antes de seleccionar productos.

### Atender pedidos

1. El panel presenta pedidos recientes y permite consultar los anteriores.
2. Personal o administrador ve cliente, teléfono, modalidad, dirección cuando aplica, fecha, artículos, cantidades y observaciones.
3. Puede crear manualmente un pedido recibido por otro canal, editarlo y cambiar su estado.
4. El flujo conserva los estados **Pendiente, Confirmado, Cancelado y Entregado**. La versión antigua permitía al administrador elegir cualquiera desde el panel; la nueva propuesta limita transiciones inválidas.
5. El listado se actualiza oportunamente. La versión antigua consultaba de nuevo cada 60 segundos; el piloto puede comenzar con una frecuencia menor y sencilla, sin requerir tiempo real.

Los cuatro estados históricos se conservan para el MVP. Estados de cocina como Preparando/Listo solo se agregan si el personal del bistró confirma que forman parte de su operación. No se asume seguimiento público del pedido hasta que se defina cómo se protege y comparte ese acceso.

## Alcance propuesto del MVP

- Un piloto inicial, con modelo de datos preparado para separar varios bistrós.
- Página de menú pública por bistró y consola autenticada para administrador y personal.
- CRUD de productos, imágenes, valor, IVA y disponibilidad.
- Carrito con cantidades y observaciones por producto.
- Pedidos para domicilio o recogida en local, con cobertura configurable por negocio.
- Perfiles de cliente con nombre, celular, correo opcional y datos de dirección reutilizables.
- Pedidos manuales desde la consola para ventas recibidas fuera de la página.
- Estados históricos de pedido y listado paginado de pedidos recientes.
- Enlaces para compartir productos; Facebook primero si sigue siendo un canal importante.
- Español colombiano, precios COP y zona horaria `America/Bogota`.
- Sin pagos en línea durante el piloto; no existe esa función en la versión histórica.

## Decisiones nuevas que requieren validación

- **Acceso del cliente:** cuentas por correo, acceso social opcional y/o checkout como invitado. La versión histórica combinaba Facebook y registro local, y exigía sesión para comprar.
- **Seguimiento al cliente:** la versión antigua mostraba una confirmación de envío y el estado solo estaba en el panel; decidir si el MVP añade una página privada de consulta.
- **Datos del cliente:** confirmar si se necesita el número de identificación; en la versión antigua era requerido en el formulario, pero la tabla lo permitía nulo.
- **Menú:** confirmar si categorías, adicionales, tamaños o variantes hacen falta. El modelo antiguo solo tenía productos y observaciones libres.
- **Cobertura y costo:** rescatar el mapa/zona histórica de Cúcuta y confirmar tarifa de domicilio. La versión antigua mostraba cobertura, pero el pedido no guardaba tarifa ni zonas.
- **Pago:** el flujo anterior no registraba pagos. Confirmar si el piloto seguirá coordinando el pago por fuera de la app.
- **Operación de pedidos:** validar si Pendiente/Confirmado/Cancelado/Entregado bastan o si cocina necesita Preparando/Listo.
- **Multi-bistró:** validar que la oferta sea SaaS para negocios independientes; el sistema antiguo servía a una sola marca.

## Datos de pedido y reglas comerciales

Cada artículo del pedido conserva producto, cantidad y observación. La versión antigua vinculaba el artículo al producto y recalculaba el total con el precio actual; la nueva guardará también el nombre, precio e IVA aplicados al confirmar, para que editar el menú después no cambie pedidos históricos.

La versión histórica no expresaba de forma clara si `valor` incluía IVA, cómo se aplicaba el impuesto, si había tarifa de domicilio ni cómo se comunicaba la confirmación. Esas reglas deben definirse con el negocio antes de cerrar el modelo de datos.

## Requisitos de seguridad

- Autenticación y permisos en el servidor para cada acción administrativa.
- Aislamiento de todos los datos por bistró, comprobado en cada ruta y consulta.
- Contraseñas con hash resistente; secretos solo en configuración segura.
- HTTPS y sesiones protegidas; el token no se guarda en `localStorage`.
- El servidor valida cliente, productos, disponibilidad, cantidades, IVA, cobertura y total; nunca confía en el cálculo del navegador.
- Protección contra pedidos duplicados y abuso de rutas públicas.
- Las modificaciones usan métodos HTTP adecuados y autorización; nunca se borra ni actualiza con GET.
- Imágenes con límites de tamaño y validación de formato.
- Solo clientes autorizados ven sus pedidos; enlaces de seguimiento privados, impredecibles y revocables.
- Registros sin contraseñas, tokens ni datos personales innecesarios; copias de seguridad y retención definidas.

## Criterios de aceptación

1. Un administrador puede crear y publicar un producto; el cliente ve productos disponibles, su descripción, imagen y precio.
2. El cliente añade varias unidades, escribe observaciones y elige domicilio o recogida.
3. Para domicilio se valida la cobertura; para recogida no se exige dirección.
4. El pedido guarda sus artículos, cantidades, observaciones, modalidad, contacto y una copia de precio/IVA usada para calcular el total.
5. El personal puede consultar detalles, crear pedidos manuales y moverlos por las transiciones permitidas.
6. Los datos del cliente se reutilizan en una compra posterior según el mecanismo de acceso que se acuerde.
7. Un negocio no puede consultar ni modificar datos de otro. Si se incorpora consulta de estado para clientes, solo el dueño del pedido puede acceder.
8. La interfaz funciona en móvil y muestra claramente modalidad, datos de entrega, total y confirmación de recepción.

## Primera rebanada implementada

El demo acepta pedidos como invitado, para domicilio o recogida, con nombre y teléfono, correo opcional y observaciones por artículo. En **Entrega y recogida**, el administrador habilita las modalidades, configura una tarifa plana y hasta 100 barrios, y define la dirección de recogida. El API valida que el producto siga disponible, exige un barrio configurado para domicilio, compara el nombre sin distinguir mayúsculas y guarda la tarifa o dirección aplicada como snapshot. El total suma los productos y, si corresponde, el domicilio. Una clave UUID persistida con el carrito impide que reintentos o envíos simultáneos creen pedidos duplicados. El panel muestra los 50 pedidos más recientes, ofrece búsqueda por referencia y datos de cliente, filtros con conteos por estado y un detalle con contacto, destino, artículos, notas y desglose del total. Permite Pendiente → Confirmado/Cancelado y Confirmado → Entregado/Cancelado; se actualiza cada minuto.

Los valores sembrados —Centro, Caobos y La Riviera; $5.000 COP; “Dirección de muestra, Cúcuta”— son ficticios y solo sirven para recorrer el demo. Antes de representar un negocio real, el administrador debe reemplazarlos. La tarifa es única para todos los barrios; no hay cálculo de impuestos, cobro electrónico ni notificaciones. El checkout como invitado, impuestos (el IVA histórico es ambiguo), y retención de datos de contacto siguen pendientes de validar con el negocio. La dirección se recibe como texto; la lista configura cobertura por barrio, sin geocodificación ni validación de ubicación exacta.

## Referencias históricas

- Backend: rutas públicas y administrativas en `routes/api.php`; catálogo en `app/Car/Product.php`; pedidos en `app/Car/Order.php`; perfil de cliente en `app/Users/Client.php`; tablas en `database/migrations/`.
- Web: compra pública en `app/components/car/list.js`, `modalproduct.js`, `modalindicator.js` y `models.js`; cobertura en `modalcoverage.js`; administración en `app/components/admin/products.js`, `clients.js` y `orders.js`.
