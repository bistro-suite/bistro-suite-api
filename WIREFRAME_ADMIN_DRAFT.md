# Bistro Suite — wireframes de consola (borrador)

**Estado:** wireframe funcional de baja fidelidad; validar con el equipo del bistró antes de congelar requisitos.
**Alcance:** acceso, pedidos, pedido manual, productos y ajustes mínimos del bistró. Incluye vistas desktop y mobile.

## Convenciones

- **[Legado]** aparece en la aplicación histórica revisada; se conserva como conocimiento del negocio, no necesariamente como diseño final.
- **[Propuesta]** decisión de experiencia o producto recomendada para la nueva app.
- **[Pendiente]** requiere validación del negocio.
- Los dibujos son esquemáticos: no fijan colores, marca, textos legales ni reglas comerciales todavía.
- Todas las vistas privadas se limitan al bistró asociado a la sesión. No se muestran datos de otros negocios.

## Interacciones heredadas que se conservan

**[Legado]** La consola abría un modal pequeño para agregar producto a un pedido y usaba diálogos de confirmación/resultado para operaciones. **[Propuesta]** mantener esos modales breves: agregar línea a pedido, confirmar cancelación o avisar un resultado. El detalle de pedido puede ser panel lateral en escritorio y pantalla completa en móvil. La creación/edición de producto usa diálogo solo si el formulario cabe cómodamente en escritorio; en móvil se muestra como pantalla completa. Evitar formularios largos, checkout o flujos anidados dentro de modales.

Los modales deben poder cerrarse por teclado, contener el foco mientras estén abiertos, devolverlo al control de origen y no descartar cambios sin aviso. Mantener una sola capa modal.

## Roles y alcance

| Acción | Administrador del bistró | Personal |
|---|---:|---:|
| Ver y atender pedidos | Sí | Sí |
| Crear pedido manual | Sí | Sí, propuesta; validar quién recibe pedidos por otros canales |
| Crear/editar productos y disponibilidad | Sí | No, propuesta |
| Cambiar configuración del bistró | Sí | No |
| Gestionar cuentas del equipo | Sí, fuera del alcance de estas pantallas | No |

**[Legado]** La app antigua distinguía administrador y cliente; el panel permitía administrar pedidos y productos. **[Propuesta]** separamos administrador y personal con permisos mínimos. La app debe explicar por qué una acción no está disponible, sin ocultar errores de autorización del servidor.

## Estructura persistente de consola

### Desktop

```text
┌──────────────────┬──────────────────────────────────────────────────────────────┐
│ Bistro Suite     │  Bistró: [Nombre ▼]                  Ayuda  Usuario [▼]       │
│                  ├──────────────────────────────────────────────────────────────┤
│ ▣ Pedidos        │                                                              │
│   Productos      │                  Área de trabajo                            │
│   Ajustes        │                                                              │
│                  │                                                              │
│ [Cerrar sesión]  │                                                              │
└──────────────────┴──────────────────────────────────────────────────────────────┘
```

### Mobile

```text
┌──────────────────────────────┐
│ Bistro Suite     [Bistró ▼]  │
├──────────────────────────────┤
│                              │
│        Área de trabajo       │
│                              │
├──────────────────────────────┤
│ Pedidos  Productos  Ajustes  │
└──────────────────────────────┘
```

**[Propuesta]** En móvil usar navegación inferior para las tres áreas principales; en desktop, barra lateral. El selector de bistró solo aparece si la cuenta tiene acceso a más de uno. No ofrecer cambio de tenant como sustituto de autorización en servidor.

## 1. Acceso del equipo

**[Legado]** Inicio administrativo por correo y contraseña, con comprobación del rol antes de entrar al panel. **[Propuesta]** pantalla enfocada al equipo, separada del acceso de clientes; recuperación de acceso se incluye solo si el mecanismo de identidad del piloto la soporta.

### Desktop / mobile (misma composición centrada y adaptable)

```text
┌─────────────────────────────────┐
│        [Marca Bistro Suite]      │
│  Acceso al panel de [Nombre]     │
│                                 │
│  Correo                         │
│  [_________________________]    │
│  Contraseña                     │
│  [_________________________]    │
│  [ ] Mostrar contraseña         │
│                                 │
│  [       Iniciar sesión      ]  │
│  ¿Olvidaste tu contraseña?      │
│                                 │
│  Solo personal autorizado       │
└─────────────────────────────────┘
```

- Botón deshabilitado mientras faltan campos; al enviar muestra progreso y evita envíos repetidos.
- **[Propuesta]** Tras autenticar, abrir Pedidos. Si la persona tiene acceso a varios bistrós, elegir uno antes de mostrar datos.
- Errores de credenciales con mensaje neutro (“No pudimos iniciar sesión. Revisa tus datos e inténtalo de nuevo.”); no revelar si el correo existe.
- Sin conexión: conservar el correo, no la contraseña; mostrar “No se pudo conectar. Intenta de nuevo”.
- Sesión vencida: redirigir al acceso y, tras entrar, volver a la pantalla solicitada si sigue autorizada.

## 2. Cola de pedidos

**[Legado]** Listado paginado de pedidos con cliente, fecha, estado, modalidad y acciones; se actualizaba cada 60 segundos. **[Propuesta]** cola operativa con filtros claros y actualización discreta, indicando cuándo se refrescó. Conservar los estados históricos: Pendiente, Confirmado, Cancelado y Entregado; no introducir estados de cocina sin validación.

### Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ Pedidos                                      [+ Crear pedido manual]          │
│ [Pendientes 8] [Confirmados 3] [Todos]                                        │
│ Buscar nombre/teléfono  [____________]  Fecha [____]  Modalidad [Todas ▼]    │
│ Actualizado hace 20 s  [Actualizar]                                           │
├────────────┬─────────────────┬─────────────┬────────────┬────────────┬────────┤
│ Pedido     │ Cliente         │ Recibido    │ Modalidad  │ Total      │ Estado │
├────────────┼─────────────────┼─────────────┼────────────┼────────────┼────────┤
│ #1042      │ Ana R.           │ Hoy 12:42   │ Domicilio  │ $ 42.000   │ Pend.  │
│ #1041      │ Luis M.          │ Hoy 12:31   │ Recogida   │ $ 18.500   │ Conf.  │
│ #1040      │ Camila P.        │ Ayer 19:08  │ Domicilio  │ $ 36.000   │ Entreg.│
├────────────┴─────────────────┴─────────────┴────────────┴────────────┴────────┤
│ Mostrando 1–20 de 38                          [← Anterior] [Siguiente →]     │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Mobile

```text
┌──────────────────────────────┐
│ Pedidos       [+ Pedido]     │
│ [Pendientes 8] [Confirmados] │
│ [Todos ▼] [Filtrar ⚲]        │
│ Buscar [________________]    │
│ Actualizado hace 20 s        │
├──────────────────────────────┤
│ #1042 · Pendiente            │
│ Ana R. · Hoy, 12:42          │
│ Domicilio · $42.000          │
│ [Ver pedido →]               │
├──────────────────────────────┤
│ #1041 · Confirmado           │
│ Luis M. · Hoy, 12:31         │
│ Recogida · $18.500           │
│ [Ver pedido →]               │
└──────────────────────────────┘
```

Filtros secundarios en mobile abren una hoja inferior con fecha, modalidad y estado. La modalidad debe llamarse consistentemente “Domicilio” o “Recogida (pasar por el local)”; **[Legado]** usaba ambos textos para la recogida.

### Estados de la cola

- **Carga inicial:** esqueleto de filas/tarjetas; no mostrar “sin pedidos” mientras la consulta sigue activa.
- **Vacío sin pedidos:** “Aún no hay pedidos” + explicación breve. Si aplica, botón de crear pedido manual. **[Propuesta]**
- **Vacío por filtro:** “No hay pedidos con estos filtros” + acción “Limpiar filtros”.
- **Error:** conservar filtros, mostrar aviso y botón “Reintentar”; si la actualización automática falla, indicar que los datos podrían estar desactualizados.
- **Actualización:** mantener visible la lista existente y mostrar indicador pequeño; evitar saltos de posición o perder el pedido abierto.
- **Sin permiso / sesión vencida:** comunicar acceso vencido o insuficiente y ofrecer volver a iniciar sesión; el servidor sigue siendo autoridad.

## 3. Detalle de pedido y cambio de estado

**[Legado]** Se podían consultar/editar pedidos y seleccionar estado; cada línea contenía producto, cantidad y observación. **[Propuesta]** detalle de solo lectura para importes/contacto confirmados, con acciones de estado validadas y auditables. El pedido manual puede editarse durante su creación; editar un pedido ya confirmado requiere una regla explícita aún pendiente.

### Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ ← Pedidos    Pedido #1042   [Pendiente ▼]               Recibido hoy 12:42   │
├───────────────────────────────────────────────┬──────────────────────────────┤
│ CLIENTE                                       │ ENTREGA                      │
│ Ana Rodríguez                                 │ Domicilio                    │
│ [Llamar: 300 555 0101] [Copiar teléfono]      │ Barrio La Riviera            │
│ ana@example.com (si fue provisto)             │ Calle 10 # 2-30              │
│                                               │ Indicaciones: portón azul    │
│ ARTÍCULOS                                     │                              │
│ 2 × Arepa de la casa                 $24.000  │ RESUMEN                      │
│   “Sin cebolla”                              │ Subtotal             $40.000 │
│ 1 × Limonada                      $ 8.000  │ Domicilio           $ 2.000 │
│                                               │ Total               $42.000 │
│ Nota general: llamar al llegar                │                              │
│                                               │ [Confirmar pedido]           │
│ Origen: Web · recibido 03 oct, 12:42         │ [Cancelar pedido]            │
└───────────────────────────────────────────────┴──────────────────────────────┘
```

Los importes son ilustrativos; tarifa, impuestos y desglose solo aparecen cuando el negocio defina sus reglas. El contacto se muestra con acceso directo para llamar/copy en móvil. No mostrar email si no existe.

### Mobile

```text
┌──────────────────────────────┐
│ ← Pedidos     Pedido #1042   │
│ Estado: Pendiente            │
├──────────────────────────────┤
│ Ana Rodríguez                │
│ [Llamar] [Copiar teléfono]   │
│ Domicilio · La Riviera       │
│ Calle 10 # 2-30              │
│ [Abrir dirección en mapa]*   │
├──────────────────────────────┤
│ 2 × Arepa de la casa $24.000 │
│ Nota: “Sin cebolla”          │
│ 1 × Limonada         $ 8.000 │
│ Total              $42.000  │
├──────────────────────────────┤
│ [Confirmar pedido]           │
│ [Más acciones ▼]             │
└──────────────────────────────┘
```

`*` Abrir mapa es propuesta y solo procede si la dirección puede convertirse de forma segura en enlace; no implica geocodificación almacenada.

### Cambio de estado

- **[Legado]** Estados existentes: Pendiente, Confirmado, Cancelado, Entregado; el selector administrativo permitía elegirlos.
- **[Propuesta]** Mostrar acciones contextuales, no un selector libre: Pendiente → Confirmar o Cancelar; Confirmado → Entregar o Cancelar. Cancelado y Entregado son terminales en el MVP salvo que el negocio valide una corrección excepcional. Esta transición es propuesta y debe validarse.
- Al cancelar, pedir confirmación y motivo opcional; **[Pendiente]** definir si el motivo se conserva y quién lo ve.
- Confirmar/entregar cambia el estado con acción explícita. Mostrar progreso en el botón; al éxito actualizar detalle y cola sin perder contexto.
- Error al guardar: conservar estado mostrado como vigente, indicar que no se aplicó el cambio y ofrecer reintentar. Si hubo conflicto con otro operador, recargar el estado del servidor antes de volver a habilitar acciones.
- Registrar quién realizó el cambio y cuándo es **[Propuesta]** para operación y auditoría básica.

## 4. Pedido manual

**[Legado]** El panel permitía crear pedido asociando cliente, productos, cantidades y modalidad. **[Propuesta]** formulario guiado para pedidos recibidos por teléfono, mostrador u otro canal; marcar el origen como manual y quién lo registró. La selección/creación de contacto y la posibilidad de domicilio siguen la regla de pedido común.

### Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ ← Pedidos              Nuevo pedido manual                                   │
├───────────────────────────────────────────────┬──────────────────────────────┤
│ 1. CONTACTO                                    │ 3. ENTREGA                   │
│ Buscar por nombre/teléfono [_____________]    │ (•) Recogida  ( ) Domicilio  │
│ [Seleccionar Ana R.]  [+ Contacto rápido]      │ Dirección/barrio (si aplica)│
│ Nombre [____________________]                 │ [_________________________]  │
│ Celular [___________________]                 │                              │
│                                               │ 4. TOTAL                     │
│ 2. PRODUCTOS                                  │ Subtotal            $____    │
│ Buscar producto [________________________]    │ Cargos confirmados $____    │
│ Arepa de la casa      [−] 2 [+]   $____       │ Total               $____    │
│ Nota por artículo [______________________]    │                              │
│ [+ Agregar producto]                          │ [Guardar como Pendiente]     │
│                                               │                              │
└───────────────────────────────────────────────┴──────────────────────────────┘
```

### Mobile (pasos apilados)

```text
┌──────────────────────────────┐
│ ← Pedidos   Nuevo manual     │
│ 1 Contacto  2 Productos      │
├──────────────────────────────┤
│ Buscar contacto              │
│ [________________________]   │
│ [Elegir contacto]            │
│ o [+ Ingresar contacto]      │
│                              │
│ Productos                   │
│ [Buscar producto__________]  │
│ Arepa de la casa             │
│ [−] 2 [+]          $____     │
│ Nota [___________________]   │
│ [+ Agregar producto]         │
├──────────────────────────────┤
│ Recogida ○  Domicilio ○      │
│ Total estimado        $____  │
│ [Guardar como Pendiente]     │
└──────────────────────────────┘
```

- No fijar tarifa ni cálculo de IVA hasta validar reglas; el total presentado debe ser calculado por el servidor al guardar. Si no hay regla configurada para un cargo, no inventarlo.
- Producto no disponible: no permitir agregar a un pedido nuevo; si la operación real requiere excepción, definir permiso y registro explícitos.
- Validar contacto y campos de dirección según modalidad. Número de identificación y email no son obligatorios por defecto (**[Propuesta]**, contrario a algunos campos históricos).
- **Carga:** resultados de búsqueda y guardado muestran progreso localizado.
- **Vacío:** catálogo vacío explica que primero hay que crear/publicar productos; selector sin coincidencias permite probar otra búsqueda.
- **Error:** mantener formulario localmente durante el intento; avisar si un producto/cantidad cambió y pedir revisar el total antes de reenviar.
- **Abandono:** advertir antes de descartar un formulario con datos; no guardar automáticamente información sensible en almacenamiento del navegador (**[Propuesta]**).

## 5. Productos

**[Legado]** Lista y formulario de alta/edición con nombre, descripción, precio, IVA, disponibilidad e imagen; no había categorías, variantes ni inventario explícito. **[Propuesta]** priorizar disponibilidad y precio en la lista, y validación/preview de imagen. La semántica del campo IVA debe definirse antes de mostrarla como precio final.

### Lista — desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ Productos                                            [+ Crear producto]      │
│ Buscar [____________________]     Disponibilidad [Todas ▼]                  │
├──────────────────┬──────────────────────┬──────────────┬────────────┬────────┤
│ Producto         │ Descripción          │ Precio COP  │ Disponible │ Acción │
├──────────────────┼──────────────────────┼──────────────┼────────────┼────────┤
│ [img] Arepa casa │ Arepa de maíz...     │ $12.000     │ Sí [●]     │ Editar │
│ [img] Limonada   │ Limón natural...     │ $ 8.000     │ No [○]     │ Editar │
└──────────────────┴──────────────────────┴──────────────┴────────────┴────────┘
```

### Lista — mobile

```text
┌──────────────────────────────┐
│ Productos        [+ Crear]   │
│ Buscar [________________]    │
│ Disponibilidad [Todas ▼]     │
├──────────────────────────────┤
│ [img] Arepa de la casa       │
│ $12.000 · Disponible         │
│ [Editar]       [Ocultar]     │
├──────────────────────────────┤
│ [img] Limonada                │
│ $8.000 · No disponible       │
│ [Editar]       [Publicar]    │
└──────────────────────────────┘
```

### Alta/edición

```text
┌─────────────────────────────────────────────────────┐
│ ← Productos       Crear producto                    │
│ Nombre *       [________________________________]   │
│ Descripción    [________________________________]   │
│                [________________________________]   │
│ Precio (COP) * [________________]                    │
│ IVA            [Pendiente de definir]                │
│ Foto           [Elegir imagen]  formatos/tamaño      │
│                ┌──────────────┐                      │
│                │ vista previa │  [Quitar]            │
│                └──────────────┘                      │
│ [●] Disponible en el menú                            │
│                                                     │
│ [Cancelar]                       [Guardar producto] │
└─────────────────────────────────────────────────────┘
```

- **[Propuesta]** Precio en COP entero para esta primera versión, sujeto a confirmar si se permiten fracciones y cómo se redondean impuestos.
- No guardar la imagen como base64 dentro de la base de datos; cargar a almacenamiento de archivos con límites y tipos permitidos (requisito técnico de seguridad del blueprint).
- Al cambiar precio, informar que nuevos pedidos usarán el precio actualizado; los pedidos anteriores deben conservar su snapshot (**[Propuesta]**).
- Cambiar disponibilidad debe ser reversible y actualizarse en el menú público. Eliminar producto no debe borrar historial de pedidos; preferir archivar/desactivar (**[Propuesta]**).
- **Carga:** esqueleto de lista; al subir imagen, progreso y opción de cancelar.
- **Vacío:** “Aún no hay productos” con CTA “Crear producto”. Filtrado vacío ofrece limpiar búsqueda/filtro.
- **Error:** lista mantiene datos existentes y permite reintentar; errores de campo junto al campo; fallo al cargar imagen conserva el resto del formulario.
- **Acceso:** personal ve lista en modo lectura o no ve Productos; decisión visual recomendada: permitir lectura solo si es útil, pero ocultar acciones de edición y validar en backend.

## 6. Ajustes mínimos del bistró

**[Legado]** Sitio único con dirección/contacto y mapa/texto de cobertura (“Solo Cúcuta”); no se encontró una pantalla administrativa moderna para estas opciones. **[Propuesta]** agrupar configuración mínima en una pantalla y hacerla editable por administrador. No dar por vigente Cúcuta, los horarios ni ninguna tarifa sin validación.

### Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ Ajustes del bistró                                      [Guardar cambios]    │
├──────────────────────────────────────────────────────────────────────────────┤
│ INFORMACIÓN PÚBLICA                                                          │
│ Nombre del bistró * [____________________]   Slug: /b/[nombre] (solo lectura)│
│ Teléfono            [____________________]   Correo [____________________]  │
│ Dirección del local [____________________________________________________]  │
│ Zona horaria        America/Bogota (MVP)                                    │
├──────────────────────────────────────────────────────────────────────────────┤
│ HORARIOS                                                                      │
│ [ ] Mostrar horarios  Lun–Dom [configuración por día, opcional]              │
│ [ ] Mostrar abierto/cerrado en menú (requiere horario configurado)          │
├──────────────────────────────────────────────────────────────────────────────┤
│ ENTREGA Y RECOGIDA                                                           │
│ [●] Permitir recogida en el local                                            │
│ [●] Permitir domicilio                                                       │
│ Cobertura [texto/zonas pendientes de definir]     Tarifa [pendiente]          │
│ [Vista previa de cobertura pública]                                         │
│ Nota: no se aceptan domicilios fuera de cobertura una vez configurada.       │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Mobile

```text
┌──────────────────────────────┐
│ Ajustes                      │
├──────────────────────────────┤
│ Información del bistró    ›  │
│ Horarios                  ›  │
│ Domicilio y recogida      ›  │
├──────────────────────────────┤
│ Cambios sin guardar          │
│ [Guardar cambios]            │
└──────────────────────────────┘
```

Al entrar a una sección mobile se muestran campos apilados con botón fijo “Guardar”. Si se cambia de sección con ediciones sin guardar, avisar antes de descartarlas.

- **[Propuesta]** No permitir activar domicilio hasta configurar cobertura suficiente para que el cliente sepa si su dirección puede atenderse. **[Pendiente]** Acordar si la cobertura será texto informativo, barrios configurables o mapa; el legado solo confirma un texto y una imagen de cobertura.
- No configurar tarifa, mínimo, horas ni impuestos con valores de ejemplo como si fueran reales. Mantener ajustes pendientes deshabilitados o señalados hasta su validación.
- Carga de configuración: controles deshabilitados y esqueleto; no presentar valores predeterminados como guardados.
- Error al cargar: panel de error y reintento; no permitir sobrescribir datos desconocidos.
- Error al guardar: conservar los cambios escritos y destacar campos inválidos/conflictos; indicar qué se guardó y qué no solo si el servidor confirma ese resultado.
- Vacío o incompleto: aviso “Completa estos datos para publicar/activar domicilio” con enlaces a los campos pendientes. No inventar un domicilio público.
- Solo administrador puede guardar; el personal recibe pantalla de solo lectura o mensaje de permiso insuficiente.

## Reglas de interacción compartidas

1. Mostrar COP y formato colombiano de miles; respetar `America/Bogota` para fechas (**[Legado]** operación en Colombia; zona explícita en el blueprint).
2. No confiar en importes calculados en la interfaz: para el pedido manual, el servidor valida productos, disponibilidad y total (**[Propuesta de integridad]**).
3. Las listas deben diferenciar cero resultados, carga y error; el refresco de pedidos no debe reemplazar el contenido por una pantalla vacía.
4. En móvil, botones de acción primarios al alcance del pulgar; no depender de hover, tablas anchas ni modales laterales.
5. Proteger datos de contacto y dirección según el rol y la tarea. No incluir identificación nacional por defecto (**[Propuesta]**, pendiente de necesidad operativa).
6. Acciones destructivas como archivar/cancelar requieren contexto y confirmación; operaciones de lectura no cambian estado.

## Decisiones necesarias para cerrar estos wireframes

- Confirmar roles reales del piloto y si el personal crea pedidos manuales.
- Confirmar transiciones de estado, si se permite reabrir/corregir un pedido y si la cancelación requiere motivo.
- Definir cobertura, horarios, domicilio/recogida y tarifa; el legado solo confirma “Solo Cúcuta” y modalidad de recogida/domicilio.
- Definir la semántica del IVA y el formato/rango del precio antes de cerrar formulario y resumen.
- Confirmar si se conserva la libreta de clientes; el flujo de pedido manual podría necesitar solo nombre y teléfono.
- Validar si hace falta que personal pueda consultar, pero no editar, productos o ajustes.

## Base documental

Derivado de `PRODUCT_SPEC.md`, `SCREEN_MAP_DRAFT.md` y `MVP_BLUEPRINT.md`. El contenido histórico y las propuestas se etiquetan por separado; este documento no constituye aprobación comercial ni define el API.
