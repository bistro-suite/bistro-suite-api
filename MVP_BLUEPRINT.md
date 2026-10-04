# Bistro Suite — blueprint del MVP

**Estado:** base para iniciar diseño y arquitectura; los puntos marcados para validar no son requisitos cerrados.
**Fecha:** 2026-10-03

Este documento integra el conocimiento recuperado de los dos repos antiguos con la dirección técnica acordada: conservar PHP/Laravel y JavaScript/Mithril, actualizados a versiones vigentes, y desplegar en Railway. Se conserva el conocimiento del negocio; la implementación vieja sirve como referencia, no como base segura de producción.

## Objetivo del primer flujo

Un cliente abre el menú público de un bistró, agrega productos con cantidades y notas, elige domicilio o recogida, aporta los datos necesarios y envía el pedido. El equipo lo ve en una consola y actualiza su estado. La misma consola permite crear pedidos recibidos por otros canales.

El legado confirma: catálogo con disponibilidad, descripción, imagen y precio; notas por línea; modalidades domicilio/en local; estados Pendiente, Confirmado, Cancelado y Entregado; panel de pedidos/productos/clientes; alta manual de pedidos. El legado no confirma pago en línea, notificaciones operativas ni seguimiento del pedido por parte del cliente.

## Mapa de pantallas

**Cliente:**

1. Menú del bistró con identidad, disponibilidad, ubicación/horario cuando estén configurados, productos disponibles y carrito persistente.
2. Detalle de producto con foto, descripción, precio, cantidad y observación opcional.
3. Carrito para cambiar cantidades/notas, quitar productos y revisar importes.
4. Entrega y contacto: domicilio o recogida; dirección/barrio y validación de cobertura solo para domicilio; nombre y celular para coordinar.
5. Revisión final y confirmación/recibo del pedido.

Se propone checkout como invitado para reducir fricción; el legado exigía sesión y ofrecía cuenta local o Facebook. La identificación nacional no se hace obligatoria sin necesidad validada.

**Equipo del bistró:** acceso; cola y detalle de pedidos; creación de pedido manual; gestión de productos/disponibilidad; configuración mínima del bistró (identidad, horario, cobertura y modalidad de recogida). La libreta de clientes queda detrás de los pedidos en prioridad hasta confirmar su necesidad. Mantener los cuatro estados existentes y definir transiciones permitidas; no agregar estados de cocina antes de validarlos.

## Contrato y datos mínimos

- **Bistro/tenant:** slug público, identidad, configuración de entrega/recogida y zona horaria. El modelo heredado era de un solo negocio; multi-bistró es una decisión nueva. Todo dato y permiso administrativo debe quedar aislado por bistró.
- **Personal:** usuario con membresía y rol dentro del bistró. No reutilizar los roles globales antiguos como autorización suficiente.
- **Cliente/contacto:** guardar solo los datos necesarios para atender y entregar. Acordar acceso de invitado/cuenta y política de retención.
- **Producto:** nombre, descripción, precio COP exacto, regla explícita de IVA, disponibilidad e imagen en almacenamiento persistente.
- **Pedido:** bistró, contacto, modalidad, dirección snapshot cuando aplique, estado, fecha, importes calculados por servidor y origen web/manual.
- **Línea:** producto relacionado cuando exista, nombre/precio/IVA snapshot, cantidad y observación. Los pedidos deben conservar el valor aplicado aunque luego cambie el catálogo.

No aceptar como autoridad del navegador `bistro_id`, estado, precio ni total. Crear pedido y líneas en transacción, revalidar pertenencia/disponibilidad y cantidades, calcular los importes, aplicar idempotencia y guardar snapshots de contacto/entrega. Definir direcciones/zonas y tarifa antes de activar validación automatizada de cobertura.

## Stack y Railway

- API en Laravel moderno sobre PHP actualmente soportado; cliente en Mithril 2 y JavaScript moderno, con compilación Vite/esbuild en lugar de Brunch/Babel 6.
- Servicios iniciales `api`, `web` y PostgreSQL. API con health check y variables seguras; frontend estático con fallback de rutas SPA y puerto `$PORT`.
- Decidir cookies/sesión y CSRF junto con los dominios definitivos; no persistir credenciales en `localStorage`.
- Usar storage de objetos para imágenes. Incorporar workers/cron cuando exista un proceso asíncrono real.
- Usar como referencia de documentación/configuración Railway `/home/stivenson/projects/gestion-monitorias/docs/railway.md` y sus `railway.toml`, adaptando root directories, health checks, variables y fallback SPA. No copiar IDs, dominios, builder ni comandos de backend particulares sin validarlos para Laravel.

## Secuencia de implementación

1. Validar las decisiones comerciales pendientes y aprobar los wireframes del flujo público y consola.
2. Crear bases limpias de Laravel y Mithril; definir PostgreSQL, configuración local, Railway y CI reproducible.
3. Implementar identidad/tenant, membresías, catálogo público y administración de productos.
4. Implementar carrito/checkout y creación segura del pedido con snapshots e idempotencia.
5. Implementar cola/detalle, estados válidos y pedido manual; verificar aislamiento entre bistrós.
6. Publicar un entorno de prueba en Railway y verificar health check, rutas SPA, persistencia, carga de imágenes y variables sin secretos en Git.
7. Integrar el producto en `contenido-ia.app` al final del mega plan, como página/entrada de producto con enlace a la aplicación desplegada; coordinarlo con las instrucciones y skills del repositorio de Contenido IA.

## Decisiones que bloquean el esquema final

1. ¿Quién es el bistró piloto, sigue en Cúcuta y qué cobertura/horarios/tarifas aplica?
2. ¿Se permite checkout como invitado? ¿Qué datos son obligatorios y cuáles se guardan para futuras compras?
3. ¿El precio incluye IVA, cómo se interpreta el campo antiguo `iva` y cuál es la regla de redondeo?
4. ¿Pago y confirmación siguen coordinándose fuera de la app? ¿Qué canal notifica al equipo/cliente?
5. ¿Cuáles transiciones de estado se permiten y qué personas operan cada función?
6. ¿Se requieren categorías, variantes/extras, horario por producto o inventario en el piloto?
7. ¿Multi-bistró es requisito de lanzamiento o preparación para una segunda etapa? La propuesta actual lo incorpora desde el modelo inicial.

## Documentos de respaldo

- [Especificación inicial de producto](PRODUCT_SPEC.md)
- [Mapa de pantallas y flujos](SCREEN_MAP_DRAFT.md)
- [Dominio y contrato preliminar del API](API_DOMAIN_DRAFT.md)
- [Evaluación de stack, Railway y reutilización](STACK_REUSE_ASSESSMENT.md)
