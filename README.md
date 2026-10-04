# Bistro Suite

Bistro Suite moderniza la antigua aplicación Cars Admin para ofrecer una plataforma de administración y pedidos para bistrós. Este repositorio conserva la aplicación anterior y sus documentos; el API Laravel vive en `modern-api/`. El cliente Mithril vive en el [repositorio web de Bistro Suite](https://github.com/bistro-suite/bistro-suite-web/tree/main/modern-web).

**Demo pública:** [bistro-web-production.up.railway.app](https://bistro-web-production.up.railway.app/). La carta pública, el panel y el API se despliegan desde la rama `main` en servicios separados de Railway. El API no tiene dominio público: la web reenvía `/api`, `/sanctum` y `/storage` por la red privada. No se publican aquí las credenciales del panel demo.

## Stack actual

- API: Laravel 13 y PHP 8.3 o superior.
- Cliente: Mithril 2, Vite 8 y Node.js 24.
- Desarrollo local: Docker, Docker Compose y Whaler (opcional, como panel gráfico).
- Producción: servicios separados en Railway. El cliente hace proxy privado al API para conservar un solo origen en el navegador y usar cookies Sanctum de forma segura. El Compose de este repositorio es solo para desarrollo local.

La tienda consume el menú público de Laravel y conserva búsqueda, filtros, modales y carrito. El panel `/admin` permite administrar productos, atender pedidos y configurar domicilio y recogida. La bandeja resume cada pedido, permite buscar por referencia o datos del cliente y filtrar por estado; cada resumen abre el detalle con contacto, destino, artículos, notas y desglose del total. El checkout de muestra acepta pedidos como invitado, conserva observaciones por producto y exige nombre y teléfono; Laravel vuelve a validar disponibilidad, cobertura y precios, y calcula el domicilio con la tarifa vigente. Cada pedido conserva una copia de la tarifa o dirección de recogida aplicada. El demo no procesa pagos ni calcula impuestos. Las zonas, tarifa y dirección actuales son datos ficticios de Cúcuta; cámbialos desde **Entrega y recogida** antes de mostrar el demo como operación real. Revisa las decisiones pendientes para el piloto en `PRODUCT_SPEC.md`.

## Requisitos para el entorno local

Instala Docker Engine y Compose. Se recomienda Compose v2, que se invoca como `docker compose`. En Zorin OS/Ubuntu 24.04, el paquete disponible en los repositorios del sistema es:

```sh
sudo apt update
sudo apt install docker-compose-v2
```

En el equipo de desarrollo ya existe Docker 29.1.3 y `docker-compose` 1.29.2. La versión 1 puede iniciar un stack nuevo, pero al probar la recreación de contenedores con Docker 29 falló con `KeyError: 'ContainerConfig'`. Para recrear servicios de forma fiable, usa Compose v2.

Para que Docker funcione sin `sudo`, agrega tu usuario al grupo `docker` y vuelve a iniciar sesión en el escritorio:

```sh
sudo usermod -aG docker "$USER"
```

Comprueba que el grupo está activo y que el cliente llega al daemon:

```sh
id -nG
docker info
```

El grupo `docker` permite control prácticamente equivalente a root. No expongas el socket Docker por red ni concedas este acceso a usuarios que no deban administrar el equipo.

## Instalar y abrir Whaler (opcional)

Whaler es una interfaz gráfica para iniciar y detener contenedores, ver logs y administrar aplicaciones Compose. La versión Flatpak solicita acceso al socket local de Docker. Si Flathub aún no está agregado al perfil de usuario, configura el remoto e instala Whaler:

```sh
flatpak remote-add --user --if-not-exists flathub https://dl.flathub.org/repo/flathub.flatpakrepo
flatpak install --user flathub com.github.sdv43.whaler
flatpak run com.github.sdv43.whaler
```

En Whaler, selecciona el archivo `compose.yaml` de este repositorio para ver los servicios y sus logs. La aplicación necesita que Docker esté iniciado y que el usuario tenga acceso al socket. [Whaler en Flathub](https://flathub.org/en/apps/com.github.sdv43.whaler) · [Código fuente](https://github.com/sdv43/whaler).

## Iniciar Bistro Suite en local

Los repositorios deben estar lado a lado, dentro de la misma carpeta:

```text
projects/
├── cars-admin-api/
└── cars-admin-client/
```

Desde la raíz de `cars-admin-api`, inicia los dos servicios:

```sh
docker compose up -d
```

Si solo tienes Compose v1 instalado, el comando equivalente para el primer inicio es `docker-compose up -d`; considera instalar Compose v2 antes de recrear los contenedores.

En el primer inicio, el servicio API crea `modern-api/.env` desde `.env.example`, instala dependencias con Composer y genera una clave local. El servicio web instala dependencias con `npm ci`. Docker conserva `node_modules` en un volumen nombrado y monta el código de ambos repositorios para reflejar cambios durante el desarrollo.

Verifica los servicios y sus logs:

```sh
docker compose ps
docker compose logs -f api web
```

URLs locales:

- Cliente: <http://localhost:5173/>
- API: <http://localhost:8000/health>
- Menú del bistró demo: <http://localhost:8000/api/v1/public/bistros/demo-bistro/menu>
- Administración del catálogo: <http://localhost:5173/admin> (cuenta demo local documentada en `modern-api/README.md`)
- Carrito y checkout de muestra: agrega un plato desde la carta pública para registrar un pedido de prueba.

Detén los servicios sin borrar dependencias ni datos locales:

```sh
docker compose stop
```

Para retirarlos junto con la red del proyecto, desde la raíz del API ejecuta `docker compose down`. Evita `docker compose down -v` salvo que quieras borrar también el volumen de dependencias.

## Estructura y guías

- `compose.yaml`: entorno Docker local para API y web.
- `DOCKER_LOCAL.md`: referencia breve al flujo de desarrollo local.
- `modern-api/README.md`: API Laravel, configuración local y despliegue Railway.
- [README del cliente web](https://github.com/bistro-suite/bistro-suite-web/blob/main/modern-web/README.md): cliente Mithril, demo pública y scripts de desarrollo.
- [Procedencia de assets](https://github.com/bistro-suite/bistro-suite-web/blob/main/modern-web/ASSET_PROVENANCE.md): recursos visuales recuperados del sistema anterior.
- `API_DOMAIN_DRAFT.md`: evidencia del dominio heredado y contrato inicial del menú.
- `PRODUCT_SPEC.md`, `MVP_BLUEPRINT.md` y documentos `WIREFRAME_*`: definición y diseño del producto.

Railway no usa este Compose: API, cliente y Postgres se configuran como servicios independientes. El API solo acepta tráfico desde la red privada; la web pública reenvía `/api`, `/sanctum` y `/storage` al API. Las imágenes subidas al panel se guardan en un volumen persistente. El endpoint público de la carta demo devuelve 16 platos en cinco categorías.
# Bistro Suite API

The modernization API lives in [`modern-api/`](modern-api/README.md). Its local Compose setup provides PostgreSQL persistence and a bistro-scoped, fictional sample menu for the demo client. Legacy API files and historical docs in this repository remain available for reference.
