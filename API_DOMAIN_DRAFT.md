# Bistro Suite — borrador de dominio y flujo vertical del API

**Estado:** borrador de análisis estático del API legado; validar las incertidumbres con el negocio antes de cerrar el esquema.

## Resumen

El legado modela un catálogo público de productos disponibles y órdenes que pertenecen a un usuario. Cada orden contiene líneas con producto, cantidad y observación. La modalidad se almacena como entero (`1` domicilio, `2` en local) y el estado como entero (`1` Pendiente, `2` Confirmado, `3` Cancelado, `4` Entregado). La escritura de la orden y sus líneas usa una transacción de base de datos.

El modelo no conserva los datos operativos completos del envío, el precio histórico ni el negocio propietario. Para un SaaS multi-bistró esos vacíos son importantes: no basta con añadir `bistro_id` a una tabla; cada lectura, escritura, relación, adjunto y permiso debe quedar dentro del tenant correspondiente.

## Dominio propuesto

- **Bistro:** tenant con identidad pública, datos de contacto, ubicación, cobertura/configuración de domicilio, recogida habilitada y zona horaria. En el piloto podría existir uno; todos sus recursos quedan asociados a él desde el inicio.
- **Membership / usuario de personal:** cuenta autenticada con pertenencia a un bistró y rol. El legado tiene usuarios y roles globales (`Admin`/`Client`), pero no pertenencia a negocio. Propuesta inicial: administrador del bistró y personal; la operación de plataforma queda separada.
- **Customer:** datos necesarios para contacto y pedidos. Puede ser usuario autenticado o identidad de invitado según decisión del producto. No imponer número de identificación sin necesidad validada.
- **Product:** producto de un bistró, nombre, descripción, importe, tratamiento de IVA, disponibilidad y referencia a imagen almacenada. Categorías, opciones/adicionales y variantes son ampliaciones, no existen en el modelo histórico.
- **Order:** bistró, cliente (posiblemente opcional si se acepta checkout invitado), modalidad, estado, fecha, datos de contacto/dirección usados en esta compra, importes calculados y origen (web o entrada manual). Guardar una copia de datos de entrega y contacto evita que editar el perfil cambie el pedido histórico.
- **OrderItem:** orden, producto de referencia opcional si se elimina/archiva, nombre y precio/IVA históricos, cantidad y observación. La referencia histórica al producto por sí sola no basta para reconstruir el pedido.
- **Delivery configuration:** cobertura y tarifa del bistró. El legado menciona cobertura de Cúcuta, pero no guarda zona ni tarifa en las tablas inspeccionadas.
- **Order status history (recomendado):** actor, transición, instante y nota opcional para auditoría. No aparece en el legado; puede ser una pequeña ampliación o diferirse si se acuerda expresamente.

Usar importes decimales exactos o enteros en unidad monetaria menor; no replicar el `FLOAT` histórico para `value`. La moneda piloto es COP. Definir si el precio ingresado incluye IVA, la precisión y la regla de redondeo antes de implementar totales.

## Flujo vertical recomendado

### 1. Menú público

1. Resolver el bistró por slug/dominio público y consultar únicamente sus productos publicados/disponibles.
2. Devolver campos públicos explícitos: nombre, descripción, importe y moneda, imagen servida desde almacenamiento, disponibilidad y categorías si se incorporan.
3. Permitir consultar detalle y compartir URL pública. El legado construye vistas OG para Facebook y sirve imagen desde base64 en base de datos; conservar el resultado social si aún se necesita, pero guardar imágenes en storage con validación y límites.

**Contrato ejecutable de demo:** `GET /api/v1/public/bistros/{slug}/menu` responde `{ data, meta }`. Cada elemento usa un ID numérico, `name`, `description`, `price_cop` entero, `available`, `image_url` nullable, `category`, `illustration` y `tag`. El slug resuelve un bistró; la consulta de productos usa su relación, solo devuelve disponibles y devuelve 404 para un slug desconocido o inactivo. PostgreSQL guarda este catálogo. El seed local crea 16 productos ficticios visibles y uno no disponible en cinco categorías. Seis productos representan preparaciones nortesantandereanas con imágenes de menú generadas para la demo. Las categorías son una ampliación del modelo legado, que no incluía ese campo, y no se importaron registros de negocio antiguos. El cliente carga el contrato por el proxy de Vite.

### 2. Carrito y checkout

1. Mantener carrito de cliente en la interfaz como borrador; el cliente envía identificadores de producto, cantidad, observación y modalidad, nunca un total confiable.
2. Solicitar los datos mínimos de contacto. Para domicilio, dirección/barrio y comprobación de cobertura; para pasar por el local, no exigir domicilio.
3. El checkout como invitado, acceso requerido, teléfono obligatorio, correo opcional y reutilización de direcciones quedan por validar. Históricamente se requería login antes de comprar, se ofrecían cuenta local y Facebook, teléfono era requerido y correo se describía como opcional; el perfil de usuario también guarda dirección/barrio.
4. Explicar antes de confirmar la modalidad, destino o recogida, artículos, notas, importes y total. El pago en línea no aparece en el legado y queda fuera mientras el negocio no lo solicite.

### 3. Crear pedido

En una operación atómica y bajo el `bistro_id` resuelto del servidor:

1. Validar cantidades positivas dentro de límites, producto activo y perteneciente al bistró, modalidad permitida y dirección cubierta cuando aplique.
2. Leer precios e IVA actuales desde la base de datos; no aceptar del navegador `bistro_id`, estado, rol, precio ni total como autoridad.
3. Calcular subtotal, impuestos, tarifa de domicilio y total con reglas monetarias configuradas y guardarlas en la orden y sus líneas como snapshot.
4. Guardar datos de contacto/entrega correspondientes a esa compra, modalidad y estado inicial Pendiente.
5. Crear cada línea con producto, nombre/precio/IVA snapshot, cantidad y observación. Responder con un identificador y confirmación; el legado no implementa consulta pública de estado ni envía notificación al crear.
6. Añadir idempotencia o mecanismo equivalente para que reintentar una petición no duplique órdenes; el código histórico no muestra control de duplicados.

### 4. Administración de pedidos

1. Autenticar y autorizar en servidor a un miembro del bistró; limitar listado, detalle, edición y artículos al tenant de esa identidad.
2. Mostrar pedidos con cliente/contacto, modalidad, domicilio cuando corresponda, detalle de artículos y notas, totales, estado y fecha.
3. Permitir entrada manual por otro canal, identificando quién lo creó y asociándolo al bistró. La consola histórica permite crear/editar orden y líneas.
4. Mantener los cuatro estados históricos, pero implementar una máquina de transiciones documentada y validada. Pendiente → Confirmado/Cancelado; Confirmado → Entregado/Cancelado es una propuesta razonable para validar. No asumir que reabrir o cancelar después de entregar sea permitido.
5. Guardar actor y momento de cada cambio. Usar paginación y actualización periódica sencilla al inicio; el legado refrescaba el listado cada 60 segundos. Tiempo real no es requisito probado.

## Reglas con evidencia en el legado

- El catálogo público se filtra por disponibilidad (`available = 1`) y se ordena por ID descendente; hay detalle y rutas para compartir producto en Facebook.
- El producto registra nombre, descripción, `value`, `iva`, `available` e imagen/mime. No tiene categorías o variantes en las migraciones vistas.
- La línea guarda referencia de producto, entero `amount` (por defecto 1) y `observations`; el carrito tiene observación libre por artículo.
- La orden registra modalidad y `users_id`; estado por defecto 1. En la migración: 1 domicilio, 2 en local; estados 1 Pendiente, 2 Confirmado, 3 Cancelado, 4 Entregado.
- Alta/actualización de la orden y reemplazo de líneas ocurren dentro de transacción; actualizar elimina e inserta de nuevo todas las líneas.
- El panel lista pedidos descendentes, los pagina, filtra por estado/modalidad y consulta líneas. Se permite entrada manual y edición desde el panel.
- El flujo del cliente usa usuario, teléfono móvil y perfil con dirección/barrio; el código contempla asociación mediante Facebook.

## Discrepancias y riesgos del modelo heredado

- **Sin tenant:** `products`, `orders`, `items_orders` y `users` no tienen clave de bistró. Las consultas consultan tablas enteras. Aislamiento multi-bistró requiere ownership explícito y scoping obligatorio en servicio/repositorio y autorización.
- **Pedido incompleto:** `orders` solo guarda `delivery_type`, `status`, timestamps y `users_id`. No guarda dirección/barrio del pedido, teléfono de contacto, tarifa, subtotal, IVA, total, notas generales ni origen manual. Es posible que algunos datos estén en el perfil del usuario, pero esto no representa el estado real de la entrega al momento de compra.
- **Precio mutable:** `items_orders` no tiene precio/IVA/nombre snapshot. La relación con producto deja reconstrucción histórica sujeta al precio actual; la especificación ya propone corregirlo.
- **Disponibilidad no garantizada:** la creación usa `products_id` y `amount` aportados en `items_orders`; no se aprecia revalidación de tenant, disponibilidad, precios o cantidades antes de insertar.
- **Cuenta vs contacto:** el modelo hace `users_id` obligatorio y creación pública ligada a usuario/Facebook. Esto contradice la hipótesis de carrito anónimo; escoger una política explícita en producto.
- **Cobertura y costo:** el relato operativo histórico dice solo Cúcuta y muestra mapa, pero las migraciones y orden no guardan cobertura ni tarifa.
- **IVA ambiguo:** `iva` es `string(45)` con valor predeterminado `'0'`; no hay unidad/semántica ni cálculo verificable en el API inspeccionado.
- **Moneda/precisión:** `value` es `FLOAT`; no hay moneda ni regla de redondeo almacenadas.
- **Borrado y métodos:** rutas `GET /temporal/delete/...` eliminan datos. En el nuevo API usar `DELETE` con autenticación/autorización, auditoría y tenant scope; `GET` debe ser de solo lectura.
- **Roles insuficientes:** el middleware admin comprueba rol global `roles_id == 1`, no pertenencia a negocio ni rol contextual. Todas las mutaciones administrativas deben hacer autorización de objeto y tenant.
- **Público sensible:** endpoints públicos incluyen alta de cliente, consulta por Facebook ID, creación de órdenes, logout/refresh/check y ruta de prueba de email. Rediseñar límites, validación y exposición de datos; no publicar identificadores secuenciales ni información privada.
- **Media:** imagen de producto se convierte a base64 y se guarda en tabla. Evitar blobs base64 en DB y validar MIME/tamaño en storage.
- **Borrado de producto y órdenes antiguas:** FKs bloquean borrar líneas con producto relacionado; el producto usa soft delete, pero la retención y representación de órdenes históricas debe ser intencional.
- **Semántica de edición:** la actualización reemplaza todas las líneas; no se identifica historial de edición ni restricciones por estado. Definir qué puede editarse y en qué estados.

## Preguntas para cerrar antes de migraciones

1. ¿Se mantiene un piloto en Cúcuta y qué dato determina cobertura (barrios, polígono/mapa o decisión manual)? ¿Hay tarifa fija o por zona?
2. ¿Checkout exige cuenta? ¿Invitado puede pedir? ¿Qué canales de autenticación siguen vigentes?
3. ¿Qué contacto es obligatorio y se necesita guardar una dirección snapshot en cada pedido?
4. ¿El precio de producto incluye IVA? ¿Cómo se interpreta hoy el campo `iva` y cómo se redondea?
5. ¿Se requiere anular pedidos después de confirmar/entregar? ¿Quién puede mover cada estado?
6. ¿Habrá pedidos manuales con estado inicial distinto o datos incompletos?
7. ¿Categorías, variantes/adicionales o disponibilidad por horario son necesarias para el primer menú?
8. ¿El negocio continúa coordinando el pago fuera de la plataforma y sin registrar método/estado de pago?
9. ¿Se necesita consulta de estado para clientes o solo confirmación privada de recepción?

## Referencias revisadas

- `routes/api.php`
- `app/Http/Controllers/ProductController.php`, `OrderController.php`, `ItemsOrdersController.php`, `ClientController.php`, `SessionController.php`
- `app/Car/Product.php`, `app/Car/Order.php`, `app/Users/Client.php`
- `app/Models/Product.php`, `Order.php`, `ItemsOrder.php`, `User.php`, `Role.php`
- `database/migrations/2017_02_23_002400_create_{users,products,orders,items_orders}_table.php` y migraciones de llaves foráneas
- `PRODUCT_SPEC.md`
