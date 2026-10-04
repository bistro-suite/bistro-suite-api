# Evaluación del stack y reutilización

**Estado:** decisión de dirección técnica para el producto nuevo; no implica migrar ni desplegar todavía.
**Fecha:** 2026-10-03

## Decisión

Mantener las tecnologías reconocibles del producto anterior: **PHP + Laravel para el backend y JavaScript + Mithril para la interfaz**. Railway soporta PHP/Laravel y Node, y documenta el despliegue de Laravel con PHP-FPM y Caddy. El cliente antiguo compila a JavaScript, CSS y HTML; Railway puede ejecutar un servicio web Node para publicar ese resultado.

La recomendación no es actualizar los repositorios antiguos paquete por paquete y llevarlos directamente a producción. Conviene crear una base moderna y portar de forma selectiva el conocimiento de dominio y los flujos, usando el código viejo como referencia verificable.

## Versiones de partida

Las versiones deben fijarse al iniciar la implementación y revisarse antes del despliegue. A la fecha de esta evaluación:

- **Backend:** Laravel 13 sobre PHP 8.4 o 8.5. Laravel 13 requiere PHP 8.3 o posterior; PHP 8.4 prioriza madurez y soporte, mientras PHP 8.5 es una opción válida tras comprobar dependencias. No continuar con Laravel 5.4 ni PHP 5.6.
- **Frontend:** Mithril 2.x (la documentación publica 2.3.6), con Node 24 LTS para herramientas de desarrollo y compilación. No continuar con Mithril 0.2, Brunch 2 ni Babel 6.
- **Build frontend:** reemplazar Brunch por Vite o esbuild, conservando JavaScript y Mithril. Usar módulos actuales, dependencias mantenidas, compilación reproducible y `npm ci`.
- **Persistencia:** PostgreSQL administrado por Railway es la opción inicial recomendada; actualizar el esquema y tipos de datos, no copiar literalmente las tablas MySQL de 2017.

Laravel 13 y versiones PHP/Node pueden cambiar durante el trabajo; confirmar soporte vigente en los archivos de dependencias y CI al fijar la primera versión ejecutable.

## Reutilización por repositorio

| Área | Qué conservar como conocimiento | Qué hacer con el código |
|---|---|---|
| Pedidos | Estados Pendiente/Confirmado/Cancelado/Entregado; domicilio o recoger; cantidades y observaciones por artículo; pedidos manuales desde administración. `app/Car/Order.php` guarda pedido y artículos dentro de una transacción. | Portar reglas a servicios/casos de uso Laravel nuevos. Añadir transiciones permitidas y snapshots de nombre, precio e impuesto por artículo. La implementación actual solo recibe producto, cantidad y observación para el artículo y borra/recrea las líneas al editar; no portar ese reemplazo destructivo sin revisar el caso de uso. |
| Catálogo | Producto con nombre, descripción, precio COP, IVA, disponibilidad e imagen. La imagen se convierte a base64 y se guarda en la base de datos (`app/Car/Product.php`). | Reusar el modelo conceptual y evaluar campos. Cambiar `float` monetario por enteros en unidades menores o `decimal` exacto; definir explícitamente IVA incluido/excluido. Guardar imágenes en almacenamiento de objetos, no como base64 en filas SQL. |
| Clientes | Datos de contacto, teléfono móvil, barrio y dirección para domicilio; email opcional en la compra histórica. | Reusar solo lo necesario para operar. No trasladar contraseñas, tokens, IDs de Facebook ni datos de identificación. Diseñar autenticación y consentimiento desde cero. |
| Cobertura | El piloto original cubría Cúcuta y mostraba un mapa; distinguir entrega y recogida. | La imagen `app/assets/images/image-coverage.png` sirve como referencia histórica. Confirmar vigencia y derechos; configurar zonas/costo de entrega, no asumir que el mapa o la cobertura siguen correctos. |
| Administración | Listados paginados de pedidos, clientes y productos; refresco periódico; crear pedidos manuales. | Conservar tareas y campos que aún aporten. Rediseñar pantallas y API; no portar componentes Mithril antiguos directamente. |
| Identidad visual | Fotografías, nombres, textos y señales del negocio solo si siguen representando al bistró. | No heredar plantilla, URLs, referencias a “Cars”, estilos viejos ni dependencias de Facebook sin revisión. Revisar licencias de fuentes e imágenes antes de publicar. |

### Referencias concretas del legado

- API Laravel 5.4: `composer.json`; rutas en `routes/api.php`; recursos y persistencia bajo `app/Car`, `app/Users`, `app/Factories` y `database/migrations`.
- Reglas revisadas: `app/Car/Order.php` usa transacción para persistir pedido y líneas, pero acepta los datos de artículo que llegan del cliente y no congela precio/nombre/impuesto; `app/Car/Product.php` convierte imágenes a base64 para guardarlas en SQL. Reutilizar los comportamientos requeridos, no estas implementaciones directamente.
- Cliente Mithril 0.2 y Brunch: `package.json`, `brunch-config.js`, `app/components/car/models.js`, `app/components/admin/models.js`, vistas bajo `app/components/car` y `app/components/admin`.
- Configuración expuesta a reemplazo: `app/config.js` contiene la dirección HTTP antigua de la API. `config/jwt.php` tiene una clave JWT de respaldo codificada. No copiar ninguna de las dos configuraciones.

La reutilización más segura está en los flujos y conceptos de dominio. No encontré un módulo aislado de reglas, con pruebas y contratos estables, que se pueda llevar intacto al nuevo producto; la mayor parte del backend mezcla acceso a datos, validación débil y formato de API del framework antiguo.

## Qué no se debe migrar

- Laravel 5.4, PHP 5.6, `tymon/jwt-auth` 0.5, Mithril 0.2, Brunch 2, Babel 6, Bootstrap 3 y plugins sin mantenimiento como dependencias productivas.
- El secreto JWT de respaldo, tokens en `localStorage`, endpoints de borrado por GET, direcciones HTTP/IP fijas, contraseñas ni datos personales históricos.
- El modelo de precios que recalcula pedidos pasados desde el precio actual del producto, `float` monetario, IVA ambiguo, autenticación de Facebook obligatoria o supuestos de cobertura sin validar.
- Clases con nombres heredados de autos cuando representan pedidos/comida; conviene una nomenclatura de dominio nueva y consistente.

## Arquitectura inicial en Railway

1. **Servicio `api`:** aplicación Laravel 13, despliegue PHP/Laravel administrado por Railpack, dominio público de API, health check, configuración por variables y PostgreSQL.
2. **Servicio `web`:** aplicación Mithril 2 compilada a archivos estáticos y publicada por un servidor web Node ligero o una imagen Caddy. Debe escuchar en el puerto `$PORT` de Railway y enviar llamadas a la URL HTTPS de la API.
3. **Autenticación:** diseñar junto con los dominios finales. Para una SPA, priorizar sesión/cookie segura con protección CSRF si se usa un dominio base compartido; si la topología obliga a separar dominios, documentar y revisar el mecanismo antes de elegir tokens. No guardar credenciales persistentes en `localStorage`.
4. **Almacenamiento de imágenes:** usar un bucket persistente compatible con S3 si se habilita carga de imágenes; el disco del contenedor no debe ser la fuente permanente.
5. **Procesos adicionales:** comenzar sin worker/cron si los flujos no lo necesitan; agregar servicios Railway separados para colas o tareas programadas cuando existan notificaciones u operaciones asíncronas reales.

Railway admite desplegar el frontend y backend como servicios separados, incluso desde directorios distintos de un monorepo. Mantener repos separados inicialmente conserva la organización actual; consolidar código solo tendría sentido si aparece una necesidad concreta de compartir contratos o liberar ambos juntos.

## Proyecto Railway de referencia

Tomar `/home/stivenson/projects/gestion-monitorias` como referencia práctica para la futura guía de despliegue y skill de Bistro Suite. En ese repositorio están `docs/railway.md`, `backend/railway.toml` y `frontend/railway.toml`; su guía demuestra un proyecto con servicios separados, directorios raíz, configuración declarativa, health check, uso de `$PORT`, variables por servicio y despliegue desde el checkout. El frontend usa `serve dist --single`, un patrón útil para servir una SPA con rutas cliente.

Adaptar esos patrones al stack de Bistro Suite:

- Documentar los servicios `api`, `web` y PostgreSQL, sus directorios/repos, variables requeridas y pasos de verificación.
- Usar el `railway.toml` del backend para declarar health check y arranque cuando la detección Laravel predeterminada no cubra el caso; no copiar el comando Uvicorn ni seleccionar Nixpacks sin verificar el builder vigente.
- Reutilizar el enfoque de fallback SPA para Mithril y comprobar rutas profundas, archivos estáticos y `$PORT` con el servidor elegido.
- Mantener variables de ejemplo sin secretos en Git; incluir una lista de variables de Railway y una prueba real de health check después del despliegue.
- Si se publica una demo abierta, estudiar el patrón de solo lectura de Monitorías (`DEMO_READ_ONLY`) y hacer cumplir esa restricción también en el servidor. No convertirlo en comportamiento general del SaaS.
- No copiar IDs de proyecto/servicio, dominios temporales, URLs, datos institucionales ni valores particulares de ese Railway. La configuración real de Bistro Suite tendrá sus propios entornos y credenciales.

La revisión encontró documentación y archivos de Railway en ese proyecto, pero ningún `AGENTS.md` o `SKILL.md` dentro del repositorio. Por eso es una referencia de guía/configuración, no una skill local que debamos instalar o duplicar literalmente.

## Secuencia para comenzar a reutilizar

1. Crear una aplicación Laravel limpia con PHP moderno y PostgreSQL, y una SPA Mithril 2 con compilación Vite/esbuild.
2. Extraer del legado las reglas del pedido, catálogo, modalidades, estados y datos realmente necesarios; escribirlas como requisitos/casos de uso antes de traducirlas.
3. Definir contratos de API y esquema nuevo, incluyendo tenant/bistró, autorización por negocio y snapshots históricos de pedido.
4. Portar primero un flujo vertical: publicar menú, agregar producto/cantidad/observación, elegir entrega o recogida y registrar el pedido; después administración de estado y pedido manual.
5. Contrastar cada comportamiento con el legado y con decisiones del bistró; validar dependencias, seguridad, build y despliegue de prueba en Railway antes de migrar más pantallas.

## Fuentes oficiales consultadas

- [Railway: lenguajes y frameworks compatibles](https://docs.railway.com/languages-frameworks)
- [Railway: despliegue de Laravel](https://docs.railway.com/guides/laravel)
- [Railway: despliegue de monorepos](https://docs.railway.com/deployments/monorepo)
- [Laravel: versiones y soporte](https://laravel.com/framework/docs/releases)
- [Mithril: instalación, versión 2.x](https://mithril.js.org/installation.html)
- [PHP: versiones con soporte](https://www.php.net/supported-versions.php)
- [Node.js: calendario y estado de versiones](https://nodejs.org/en/about/previous-releases)
