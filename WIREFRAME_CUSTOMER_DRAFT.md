# Bistro Suite — wireframes cliente (borrador)

**Estado:** wireframes de baja fidelidad para validar flujo y jerarquía; no son diseño visual ni contrato de API.
**Fecha:** 2026-10-03
**Base:** `PRODUCT_SPEC.md`, `SCREEN_MAP_DRAFT.md` y `MVP_BLUEPRINT.md`.

## Convenciones y límites

- **Hecho legado** identifica comportamiento o datos observados en la aplicación antigua; no significa que debamos conservar su interfaz.
- **Propuesta** identifica comportamiento recomendado para el MVP nuevo.
- **Supuesto por validar** identifica decisiones comerciales u operativas aún no confirmadas.
- Los marcos son esquemáticos. En móvil se prioriza una columna y acciones al alcance del pulgar; en escritorio se aprovecha el ancho sin cambiar el orden del flujo.
- Los textos `[$]`, `[nombre]` y similares son contenido de ejemplo, no datos comerciales aprobados.

### Base recuperada del legado

**Hechos legado:** catálogo de productos disponibles con foto, nombre, descripción y valor COP; detalle con cantidad y observación libre por artículo; carrito con varias líneas; opciones “Domicilio” y “Pasar por local”; para domicilio se pedían barrio y dirección; teléfono era requerido y correo opcional; el comprador tenía que iniciar sesión (cuenta local o Facebook); al enviar se mostraba una alerta, sin recibo persistente ni seguimiento para el cliente. No se ha confirmado pago en línea, tarifa de domicilio ni desglose de IVA.

**Propuestas del producto:** checkout como invitado, datos mínimos de nombre y celular, preservar el carrito entre pasos, revisión explícita antes de enviar, y recibo con referencia y estado inicial. No exigir identificación ni depender de Facebook. La dirección, horario, cobertura y cualquier cargo se muestran solo si el negocio los configura.

**Supuestos por validar:** bistró piloto y cobertura real; reglas de IVA/precio, costo de domicilio, horarios y forma de pago; si se ofrecerá cuenta opcional y una página privada de seguimiento. Por eso los wireframes no prometen pago, notificación ni tracking.

## Recorrido principal

```text
Menú → detalle → carrito → entrega y contacto → revisar pedido → enviar → recibo
  ↑          └──────────── seguir explorando ────────┘
```

El cliente puede volver a pasos anteriores sin perder lo ingresado. Antes del envío se vuelven a validar disponibilidad, precios y cobertura en el servidor; el navegador presenta el resultado recibido.

## Patrón de modales heredado y propuesto

El cliente anterior abría el **detalle de producto** y el **carrito/checkout** dentro de un modal global; la información de cobertura y el acceso también aparecían como modales. Conservamos esa interacción para acciones breves: detalle como modal en escritorio/hoja inferior en móvil, carrito como cajón lateral en escritorio/hoja inferior en móvil, y cobertura como diálogo informativo.

El checkout pasa a pantallas completas para modalidad/contacto y revisión. El modal viejo acumulaba carrito, formulario de entrega y login; no replicamos esa ventana larga ni encadenamos un modal de login dentro del checkout. Si se agrega acceso opcional de cliente, será una pantalla o diálogo independiente que conserva el pedido en curso.

Los diálogos tendrán cierre visible y teclado accesible, devolverán el foco al elemento que los abrió y conservarán el carrito al cerrarse. Solo una capa modal puede estar activa.

## 1. Menú del bistró

**Hecho legado:** productos disponibles, fotos, nombre, precio COP, enlace a detalle y acceso al carrito; se mostraba información del local y cobertura. El catálogo era plano, sin categorías confirmadas.

**Propuesta:** identidad inequívoca del bistró y menú claro, con carrito persistente. Categorías solo si el piloto las necesita. Enlaces de horario, ubicación y cobertura solo cuando haya datos configurados.

### Móvil

```text
┌─────────────────────────────┐
│ [marca]  NOMBRE DEL BISTRÓ  │
│ Cúcuta · [abierto/cerrado]* │
│ [Horario] [Ubicación]       │
├─────────────────────────────┤
│ Menú                        │
│ [Buscar, si hace falta]*    │
│ [Categoría]* [Categoría]*   │
│                             │
│ ┌─────────────────────────┐ │
│ │       foto producto     │ │
│ │ Nombre             [$]  │ │
│ │ Descripción breve       │ │
│ │ [Ver producto]          │ │
│ └─────────────────────────┘ │
│          …                  │
├─────────────────────────────┤
│ [Ver carrito · n · subtotal]│
└─────────────────────────────┘
```

### Escritorio

```text
┌──────────────────────────────────────────────────────────────┐
│ [marca] NOMBRE DEL BISTRÓ         [Horario] [Ubicación] [Cart]│
├──────────────────────────────────────────────────────────────┤
│ Menú                         [Buscar opcional]                │
│ [categorías opcionales]                                       │
│ ┌────────────┐ ┌────────────┐ ┌────────────┐                 │
│ │    foto    │ │    foto    │ │    foto    │                 │
│ │ Nombre [$] │ │ Nombre [$] │ │ Nombre [$] │                 │
│ │ descripción│ │ descripción│ │ descripción│                 │
│ │ [Ver]      │ │ [Ver]      │ │ [Ver]      │                 │
│ └────────────┘ └────────────┘ └────────────┘                 │
└──────────────────────────────────────────────────────────────┘
```

`*` **Supuesto por validar:** estado abierto/cerrado, búsqueda y categorías dependen de configuración/catálogo; no están confirmados por el legado. El horario no debe impedir explorar el menú si no se ha configurado.

**Estado vacío — menú sin productos publicados:** indicar que aún no hay productos disponibles y ofrecer datos del local si existen; no mostrar tarjetas ficticias.
**Error — no carga el menú:** explicar que no se pudo cargar, ofrecer “Reintentar” y mantener cualquier carrito local previo; comunicar claramente si el contenido mostrado podría estar desactualizado.

## 2. Detalle del producto

**Hecho legado:** foto, nombre, precio, descripción, cantidad y nota opcional por artículo.

**Propuesta:** mostrar disponibilidad y total de la selección antes de añadir. Variantes/extras no aparecen hasta que el negocio confirme que los necesita.

### Modal de escritorio / hoja inferior en móvil

```text
┌────────────────────────────────────────┐
│ [Cerrar]                  [Carrito n]  │
│ ┌────────────────────────────────────┐ │
│ │              foto                  │ │
│ └────────────────────────────────────┘ │
│ Nombre del producto             [$]    │
│ Descripción completa                    │
│ [Disponible / No disponible]            │
│ Cantidad          [−]  1  [+]           │
│ Nota para este producto (opcional)      │
│ [___________________________________]   │
│ Total de esta línea:             [$]    │
│ [Añadir al carrito]                     │
└────────────────────────────────────────┘
```

En escritorio puede presentarse imagen y contenido en dos columnas; en móvil, imagen arriba y acción principal visible sin competir con texto auxiliar.

**Vacío/error:** imagen ausente usa un reemplazo neutral; fallo al cargar datos ofrece reintento/volver al menú. Si el producto dejó de estar disponible, deshabilitar “Añadir” y explicar el motivo; no aceptar una selección obsoleta.

## 3. Carrito

**Hecho legado:** líneas con producto, cantidad, precio y total; se podía retirar una línea, cancelar y continuar hacia domicilio o recogida.

**Propuesta:** edición en contexto de cantidad y nota, persistencia durante navegación y totales etiquetados. No inventar tarifa, descuento ni desglose fiscal. El precio final se revalida al enviar.

### Móvil

```text
┌─────────────────────────────┐
│ [← Menú]       Tu pedido    │
│ Producto A          [$]     │
│ Nota: …                     │
│ [−] 1 [+]          [Quitar] │
│ Producto B          [$]     │
│ [Editar nota]               │
│ [−] 2 [+]          [Quitar] │
│                             │
│ Subtotal            [$]     │
│ Domicilio           Se calcula/configura*│
│ Total estimado      [$]*    │
│ [Seguir viendo menú]        │
│ [Elegir entrega]            │
└─────────────────────────────┘
```

### Escritorio

```text
┌──────────────────────────────────────────────────────────┐
│ Tu pedido                               [Seguir menú]    │
│ Producto / nota          Cant.       Precio línea       │
│ Producto A / …           [−]1[+]          [$] [Quitar]  │
│ Producto B / …           [−]2[+]          [$] [Quitar]  │
│                                          Subtotal [$]   │
│                                 [Elegir entrega]         │
└──────────────────────────────────────────────────────────┘
```

`*` **Supuesto por validar:** hasta que exista regla confirmada, el cargo de domicilio no se estima; el subtotal de productos sí se muestra y se aclara qué cargos faltan por definir. No mostrar un “total” que parezca definitivo si depende de cargos desconocidos.

**Carrito vacío:** “Tu carrito está vacío” + acción “Explorar menú”. **Producto ya no disponible o precio cambiado:** identificar la línea, explicar el cambio y pedir al cliente que la quite o confirme el nuevo precio; nunca ajustar silenciosamente el pedido.

## 4. Entrega y contacto

**Hecho legado:** domicilio o pasar por el local; dirección y barrio requeridos para domicilio; teléfono requerido, correo opcional. Se solicitaba autenticación antes de ordenar y el perfil pedía identificación.

**Propuesta:** pantalla única con modalidad y contacto esencial; invitado como camino principal y acceso opcional a cuenta si se aprueba. No pedir identificación por defecto. Recogida no exige dirección del cliente y debe mostrar la dirección del local si está configurada.

### Móvil y escritorio

```text
┌────────────────────────────────────────┐
│ [← Carrito]     Entrega y contacto     │
│ ¿Cómo quieres recibirlo?               │
│ (●) Domicilio     ( ) Pasar por el local│
│                                        │
│ [Domicilio seleccionado]               │
│ Barrio*           [________________]    │
│ Dirección*        [________________]    │
│ [Ver cobertura]                         │
│                                        │
│ [Si recogida: dirección del local +    │
│  horario configurados por el bistró]   │
│                                        │
│ Nombre*           [________________]    │
│ Celular*          [________________]    │
│ Correo            [________________]    │
│ [ ] Guardar datos para próxima compra* │
│ [Continuar a revisión]                 │
└────────────────────────────────────────┘
```

En escritorio, formulario y bloque lateral de modalidad/resumen pueden ir en columnas. En móvil, campos a una columna; el resumen del pedido permanece accesible antes de continuar.

`*` **Supuesto por validar:** nombre y celular como mínimos propuestos; guardar datos requiere decidir si habrá cuentas/consentimiento y política de privacidad. No ofrecer “guardar” si no existe una cuenta y una política definida. El correo es opcional como en el checkout histórico.

### Fuera de cobertura

**Propuesta:** comprobar cobertura después de que se ingrese la dirección y antes de permitir avanzar con domicilio. Si el servicio no cubre la zona:

```text
┌─────────────────────────────────────┐
│ Esta dirección está fuera de la     │
│ cobertura de domicilio del bistró.  │
│ [Editar dirección]                  │
│ [Elegir pasar por el local]         │
└─────────────────────────────────────┘
```

No afirmar “solo Cúcuta” como cobertura vigente: es un **hecho del texto histórico**, no una configuración confirmada hoy. Si la cobertura no está configurada o no se puede verificar, mostrar que debe confirmarse con el local y bloquear la promesa automática de domicilio; permitir recogida si está habilitada.

**Errores de formulario:** marcar el campo y explicar el dato requerido; conservar lo ya escrito. **Error de verificación:** indicar que no se pudo validar la zona y permitir reintentar o cambiar a recogida, sin decir que está dentro/fuera de cobertura.

## 5. Revisión y envío

**Hecho legado:** no se observó pantalla de revisión separada; el pedido se guardaba desde el flujo del carrito.

**Propuesta:** resumen final legible y editable antes de enviar; indicar modalidad, contacto, dirección si aplica, líneas/notas e importes conocidos. Pago sigue fuera de alcance hasta validación.

### Móvil y escritorio

```text
┌────────────────────────────────────────┐
│ [← Editar entrega]   Revisa tu pedido │
│ Producto A · cantidad 1          [$]   │
│ Nota: …                                │
│ Producto B · cantidad 2          [$]   │
│ Entrega: Domicilio / Recogida          │
│ Dirección: … (solo domicilio)          │
│ Contacto: nombre · celular              │
│ Correo: … (si se ingresó)               │
│                                        │
│ Subtotal productos               [$]   │
│ Domicilio / impuestos: según config*   │
│ Total a pagar / coordinar        [$]*  │
│                                        │
│ [Enviar pedido]                        │
│ Sin pago en línea en este flujo**       │
└────────────────────────────────────────┘
```

`*` Mostrar solo importes calculados con reglas configuradas y explicar cargos pendientes; el total debe ser respuesta del servidor, no cálculo confiado al navegador. `**` **Hecho legado:** no hay pago online. **Supuesto por validar:** cómo se acuerda el pago con el negocio. No declarar un método (efectivo/transferencia) sin confirmación.

**Enviando:** desactivar envío repetido, mostrar progreso y conservar el pedido ante timeout hasta consultar resultado. El servidor debe evitar duplicados. **Error recuperable:** explicar si no se pudo confirmar; mantener carrito/datos y ofrecer reintento seguro. **Precio/disponibilidad cambió:** devolver a revisión con diferencias señaladas y consentimiento requerido.

## 6. Recibo / pedido recibido

**Hecho legado:** se mostraba alerta de éxito/error y se vaciaba el carrito; no se confirmó recibo persistente ni consulta de estado del lado cliente. En administración sí existían estados Pendiente, Confirmado, Cancelado y Entregado.

**Propuesta:** pantalla de éxito con referencia del pedido, estado inicial y resumen para que el cliente sepa que el envío fue recibido. Enlace privado de seguimiento, cuenta, notificación y hora estimada quedan fuera hasta validación.

```text
┌────────────────────────────────────────┐
│ ✓ Recibimos tu pedido                  │
│ Pedido #[referencia]                  │
│ Estado inicial: Pendiente*             │
│                                        │
│ [Domicilio / Pasar por el local]       │
│ [Resumen de artículos y total]         │
│                                        │
│ [Datos de contacto del bistró]*        │
│ [Volver al menú]                       │
└────────────────────────────────────────┘
```

`*` El estado **Pendiente** existe en el legado; usarlo como estado inicial es una propuesta que debe concordar con el servidor. Mostrar contacto solo si está configurado. No prometer que se envió correo/WhatsApp ni que habrá seguimiento. No incluir dirección completa o datos sensibles en una URL pública.

**Error tras enviar:** distinguir “no se pudo confirmar” de “pedido rechazado”. Ofrecer consultar/reintentar de manera idempotente para no crear dos pedidos; no vaciar el carrito hasta tener confirmación inequívoca.

## Decisiones que deben resolverse al validar

1. Confirmar bistró piloto, dirección, horarios, cobertura por zona y tarifa.
2. Acordar si se permite invitado, si existe cuenta opcional y cuáles datos se conservan; confirmar si identificación tiene alguna necesidad concreta.
3. Definir interpretación de IVA, si los precios lo incluyen, redondeo y presentación al comprador.
4. Definir qué significa “pedido recibido”, estado inicial, canal de coordinación y manejo de cancelaciones.
5. Confirmar recogida, pago fuera de la app y datos que deben mostrarse en el recibo.
6. Decidir si categorías, búsqueda, disponibilidad por horario y compartir productos entran en el MVP.
