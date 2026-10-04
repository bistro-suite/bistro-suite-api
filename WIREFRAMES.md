# Bistro Suite — wireframes de baja fidelidad

**Estado:** propuesta para revisar el flujo y la jerarquía antes de implementar.
**Fecha:** 2026-10-03. Los nombres/precios dentro de los dibujos son ejemplos y no representan datos del negocio.

## Dirección acordada para el MVP

El recorrido principal es **menú → producto → carrito → entrega/contacto → revisión → pedido recibido**. El equipo opera los pedidos desde una consola separada. El diseño prioriza móvil para comprar y una cola legible para operar; las vistas desktop amplían el espacio sin cambiar las tareas.

El legado confirma catálogo con disponibilidad, imagen, descripción y valor COP; notas por artículo; domicilio o pasar por el local; y los estados Pendiente, Confirmado, Cancelado y Entregado. Checkout como invitado, recibo con número, consola adaptable y separación de roles son propuestas nuevas.

## Qué conservamos de la interacción original

Sí conviene conservar el uso de modales como una interacción característica, porque el original los usaba para el detalle del producto, el carrito, la cobertura, el login y la edición rápida de artículos. Los wireframes iniciales no lo habían explicitado. La propuesta ajustada es:

- **Detalle de producto:** modal centrado en escritorio y hoja inferior amplia en móvil; cerrar o volver mantiene menú y carrito.
- **Carrito:** cajón lateral en escritorio y hoja inferior en móvil para revisar/agregar/quitar artículos.
- **Cobertura y ayuda breve:** diálogo informativo. La confirmación de cobertura ocurre dentro del checkout.
- **Checkout:** pantallas completas y consecutivas para entrega/contacto y revisión. El legado metía carrito, datos de entrega y login en un modal largo; separar pasos mejora el uso móvil, conserva el carrito y evita ventanas anidadas.
- **Consola:** detalle de pedido en panel lateral en escritorio y pantalla completa en móvil. Agregar un artículo puede abrir un modal pequeño. Crear/editar producto puede usar diálogo en escritorio si cabe sin scroll excesivo y pantalla completa en móvil.
- **Confirmaciones:** diálogos solo para decisiones que el usuario pueda lamentar, como descartar cambios o cancelar un pedido; el cambio normal de estado usa una acción contextual visible.

Todos los modales deben tener cierre visible, cierre con Escape, foco contenido mientras estén abiertos, devolver el foco al control de origen y no perder datos al cerrarse accidentalmente. Usar una sola capa modal a la vez.

## Cliente

### Menú y detalle de producto

```text
MÓVIL                                      ESCRITORIO
┌────────────────────────────┐             ┌────────────────────────────────────────┐
│ [Marca] Nombre del bistró  │             │ [Marca] Nombre       Horario Ubicación │
│ Menú               [Carrito]│             ├────────────────────────────────────────┤
│                            │             │ Menú                                   │
│ ┌────────────────────────┐ │             │ [Categorías si hacen falta]             │
│ │    Foto del producto   │ │             │ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│ │ Arepa de la casa       │ │             │ │ Foto     │ │ Foto     │ │ Foto     │ │
│ │ Descripción · $ precio │ │             │ │ Nombre   │ │ Nombre   │ │ Nombre   │ │
│ │ [Ver producto]         │ │             │ │ Precio   │ │ Precio   │ │ Precio   │ │
│ └────────────────────────┘ │             │ │ [Ver]    │ │ [Ver]    │ │ [Ver]    │ │
│ [Ver carrito · cantidad]   │             │ └──────────┘ └──────────┘ └──────────┘ │
└────────────────────────────┘             └────────────────────────────────────────┘

DETALLE (modal de escritorio / hoja inferior en móvil)
┌────────────────────────────────────┐
│ [Cerrar]                [Carrito n]│
│ [                Foto             ]│
│ Nombre · precio COP                │
│ Descripción                        │
│ Cantidad [−] 1 [+]                 │
│ Nota opcional [_________________]  │
│ Total conocido de la línea: $____  │
│ [Añadir al carrito]                │
└────────────────────────────────────┘
```

La marca, horario, ubicación y categorías aparecen cuando estén configurados. No afirmar que la cobertura actual sea Cúcuta hasta validarla; el texto y mapa antiguos son referencias históricas.

### Carrito, entrega y revisión

```text
┌────────────────────────────────────┐
│ Carrito (cajón / hoja inferior)    │
│ Arepa de la casa              $___ │
│ Nota: sin cebolla                   │
│ Cantidad [−] 1 [+]       [Quitar]  │
│                                    │
│ [Seguir viendo menú]               │
│ Subtotal de productos        $___  │
│ Domicilio o impuestos: según config│
│ [Continuar]                       │
└────────────────────────────────────┘
                 ↓
┌────────────────────────────────────┐
│ Entrega y contacto                  │
│ (•) Domicilio  ( ) Pasar por local │
│ Barrio* [_______________________]   │
│ Dirección* [____________________]   │
│ Nombre* [_______________________]   │
│ Celular* [______________________]   │
│ Correo opcional [________________]  │
│ [Continuar a revisión]              │
└────────────────────────────────────┘
                 ↓
┌────────────────────────────────────┐
│ Revisa tu pedido                    │
│ Artículos, cantidades y notas       │
│ Modalidad y dirección si aplica    │
│ Contacto                           │
│ Importes confirmados por el servidor│
│ [Volver a editar] [Enviar pedido]  │
└────────────────────────────────────┘
                 ↓
┌────────────────────────────────────┐
│ Recibimos tu pedido                 │
│ Número: #________                   │
│ Estado inicial: Pendiente           │
│ Resumen · modalidad · contacto      │
│ [Volver al menú]                    │
└────────────────────────────────────┘
```

El cliente puede volver a pasos anteriores sin perder el carrito. Para domicilio se piden barrio y dirección; para recogida se muestra la dirección del local si fue configurada. Nombre y celular se proponen como datos mínimos; teléfono requerido y correo opcional aparecen en el checkout histórico. No se exige identificación ni login para comprar en la propuesta.

No se promete pago en línea, correo/WhatsApp automático ni seguimiento público. No mostrar un cargo, impuesto o total definitivo hasta que sus reglas estén configuradas; el servidor valida disponibilidad, precios y cobertura al enviar.

### Estados alternos del cliente

- **Menú vacío:** explicar que aún no hay productos disponibles; no mostrar tarjetas inventadas.
- **Error al cargar:** ofrecer reintento y preservar el carrito local si existe.
- **Producto no disponible o precio cambiado:** marcar la línea y pedir confirmación del cambio antes de enviar.
- **Carrito vacío:** acción clara para volver al menú.
- **Fuera de cobertura:** explicar el resultado, permitir editar la dirección o cambiar a recogida.
- **Pedido en envío:** desactivar doble envío mientras la solicitud está pendiente; si falla la conexión, mostrar resultado incierto y permitir verificar/reintentar sin duplicar el pedido.

## Consola del bistró

### Estructura y cola de pedidos

```text
DESKTOP                                   MÓVIL
┌──────────────────┬────────────────────┐ ┌─────────────────────────────┐
│ Bistro Suite     │ Bistró · Usuario   │ │ Nombre del bistró      [⋮] │
│ Pedidos          ├────────────────────┤ ├─────────────────────────────┤
│ Productos        │ Pedidos [ + Manual]│ │ Pedidos         [+ Manual] │
│ Ajustes          │ [Pendientes] [Todos]│ │ [Pend.] [Confirm.] [Todos]│
│                  │ Buscar · Fecha     │ │ Pedido #1042 · Pendiente   │
│ Cerrar sesión    │────────────────────│ │ Cliente · hora             │
│                  │ #1042 Ana · dom.   │ │ Domicilio · total conocido│
│                  │ #1041 Luis · recoger│ │ [Abrir pedido]             │
│                  │ #1040 Camila · dom.│ │ …                           │
└──────────────────┴────────────────────┘ ├─────────────────────────────┤
                                           │ Pedidos Productos Ajustes  │
                                           └─────────────────────────────┘
```

Al abrir un pedido: cliente y celular; modalidad; dirección snapshot cuando aplique; artículos, notas, importes y fecha; acciones contextuales. La propuesta de transición inicial es Pendiente → Confirmado/Cancelado y Confirmado → Entregado/Cancelado. Cancelado y Entregado terminales es una propuesta para validar, no una regla heredada.

### Pedido manual y mantenimiento del menú

```text
PEDIDO MANUAL                           PRODUCTOS
┌──────────────────────────────┐        ┌─────────────────────────────────┐
│ Crear pedido                 │        │ Productos          [+ Crear]   │
│ Contacto [Buscar/crear]      │        │ Buscar [________] Disponibilidad│
│ Celular [________________]   │        │ Producto · Precio · Disponible │
│ Modalidad ○ Recogida ○ Dom.  │        │ Arepa casa · $___ · Sí [Editar]│
│ Dirección si es domicilio    │        │ Limonada · $___ · No [Editar]  │
│ Producto [Seleccionar]       │        └─────────────────────────────────┘
│ Cantidad [−] 1 [+]           │
│ Nota [___________________]   │        AJUSTES DEL BISTRÓ
│ Total calculado por servidor │        ┌─────────────────────────────────┐
│ [Guardar como Pendiente]     │        │ Identidad · contacto · dirección│
└──────────────────────────────┘        │ Horarios* · cobertura*           │
                                         │ Domicilio* · recogida            │
                                         │ [Guardar cambios]                │
                                         └─────────────────────────────────┘
```

La operación histórica permite pedidos manuales, gestión de productos y clientes. Se propone registrar quién creó el pedido, archivar productos sin borrar el historial y limitar cambios de catálogo/configuración al administrador. Horarios, cobertura, cargos, IVA, categorías y variantes quedan sin valores inventados.

## Lenguaje compartido y reglas

| Concepto | Etiqueta al cliente | Etiqueta en consola | Valor de dominio heredado |
|---|---|---|---|
| Entrega a domicilio | Domicilio | Domicilio | `1` |
| Recoger en el local | Pasar por el local | Recogida (pasar por el local) | `2` |
| Pedido recibido | Pendiente | Pendiente | `1` |
| Aceptado por el bistró | Confirmado | Confirmado | `2` |
| Anulado | Cancelado | Cancelado | `3` |
| Finalizado | Entregado | Entregado | `4` |

Los números anteriores documentan el legado; la API nueva debe usar enums/constantes con nombres y nunca depender de valores duplicados en componentes. Precios y totales se calculan en el servidor y cada pedido guarda snapshots para conservar el historial.

## Supuestos reversibles usados en estos wireframes

- Un bistró piloto, modelo preparado para separar varios bistrós desde el comienzo.
- Español colombiano, COP y zona horaria `America/Bogota`.
- Checkout invitado con nombre y celular; cuenta opcional por definir.
- Pago fuera de la app durante el piloto; sin seguimiento público.
- Cuatro estados históricos, con transiciones guiadas por definir.
- Domicilio y recogida configurables; Cúcuta es antecedente, no cobertura vigente confirmada.
- Categorías, extras, horarios, IVA/tarifas y libreta de clientes solo se muestran cuando el negocio los valide.

## Pendientes antes de congelar diseño

1. Confirmar bistró piloto, dirección, horarios, cobertura y costo de domicilio.
2. Definir checkout invitado/cuenta y qué datos realmente requiere el personal.
3. Aclarar IVA, si el precio incluye impuesto y cómo redondear.
4. Confirmar pagos, canal de aviso y transiciones de estado.
5. Confirmar si hacen falta categorías, extras/variantes, horarios por producto o libreta de clientes.

## Detalle de las vistas

- [Wireframes detallados del cliente](WIREFRAME_CUSTOMER_DRAFT.md)
- [Wireframes detallados de la consola](WIREFRAME_ADMIN_DRAFT.md)
- [Blueprint del MVP](MVP_BLUEPRINT.md)
