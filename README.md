# Filament Skeleton

Skeleton base para arrancar proyectos internos sobre Laravel 13 y Filament 5 con autenticación, administración de usuarios, roles/permisos, observabilidad y utilidades de desarrollo ya preparadas.

## Objetivo

Este repositorio sirve como punto de partida para nuevos proyectos que necesiten:

- Panel de administración con Filament.
- Gestión de usuarios desde el primer día.
- Roles y permisos con Shield.
- Soporte inicial para localización.
- Infraestructura preparada para colas, monitorización y logs.
- Flujo de arranque rápido para empezar a construir encima.

No es un proyecto de producto final. Es una base para clonar, renombrar y extender.

## Stack incluido

- PHP `^8.3`
- Laravel `^13.0`
- Filament `^5.0`
- Filament Shield
- Filament Developer Logins
- Filament Delete Guard
- Filament Expiration Notice
- Laravel Horizon
- Laravel Pulse
- Opcodes Log Viewer
- Pest
- Laravel Pint
- IDE Helper

## Qué trae ya preparado

### Panel de administración

- Panel Filament principal con id `admin`.
- URL del panel: `/admin`.
- Login, reset de contraseña y perfil habilitados.
- Verificación de email configurable.
- MFA configurable por email y/o app autenticadora.
- Tema de Filament apuntando a `resources/css/filament/admin/theme.css`.

### Usuarios y permisos

- Recurso Filament para usuarios.
- Modelo `User` con UUIDs.
- Control de acceso al panel basado en `is_active`.
- Distinción de administrador de sistema mediante `is_admin`.
- Protección para evitar borrar usuarios `super_admin`.
- Roles y permisos con Spatie Permission + Shield.
- Seeder que genera permisos del panel y crea el super admin inicial.

### Observabilidad y operación

- Horizon incluido para colas.
- Pulse incluido en el proyecto como base de observabilidad.
- Log Viewer accesible para administradores de sistema.
- Enlaces de navegación a Horizon y Logs visibles solo para `is_admin = true`.

### Localización

- Locales soportados: `es` y `en`.
- Configuración de `filament-localization` ya incluida.
- Estructura de traducciones configurada como `panel-based`.

### Desarrollo

- Script `composer run setup` para bootstrap inicial.
- Script `composer run dev` para entorno local con servidor, cola, logs y Vite.
- Generación automática de helper models tras migraciones en local si `APP_ENABLE_HELPER_MODEL=true`.

## Decisiones de base del skeleton

Estas son las decisiones que este repositorio toma por defecto y que conviene revisar al arrancar un proyecto nuevo:

- Base de datos por defecto: MySQL.
- Nombre de base de datos de ejemplo: `filament_skeleton`.
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- Redis configurado y recomendado para Horizon.
- Idioma por defecto de Laravel en `.env.example`: `en`.
- Idioma por defecto de `filament-localization`: `es`.
- El usuario administrador inicial se crea por seeder.

## Requisitos

Antes de arrancar el proyecto, asegúrate de tener:

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- MySQL corriendo y una base de datos creada.
- Redis disponible si vas a usar Horizon y parte de la observabilidad como está pensada en este skeleton.

## Arranque rápido

La vía recomendada es:

```bash
composer run setup
php artisan db:seed
composer run dev
```

Esto hace lo siguiente:

- Instala dependencias PHP.
- Crea `.env` a partir de `.env.example` si no existe.
- Genera `APP_KEY`.
- Ejecuta migraciones.
- Instala dependencias frontend.
- Compila assets.

Después de eso, el seeder deja listo el acceso inicial al panel.

## Arranque manual

Si prefieres hacerlo paso a paso:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install --ignore-scripts
npm run build
php artisan db:seed
composer run dev
```

## Acceso inicial

Después de ejecutar seeders:

- Panel: `/admin`
- Usuario: `admin@example.com`
- Contraseña: `password`

Ese usuario:

- Tiene el rol `super_admin`.
- Recibe los permisos generados por Shield.
- Tiene `is_admin=true`.
- Tiene `is_active=true`.

Conviene cambiar estas credenciales al iniciar un proyecto real.

## Scripts útiles

```bash
composer run setup
composer run dev
composer run test
```

### Qué hace `composer run dev`

Levanta en paralelo:

- `php artisan serve`
- `php artisan queue:listen --tries=1 --timeout=0`
- `php artisan pail --timeout=0`
- `npm run dev`

## Seeders incluidos

- `ShieldPermissionsSeeder`: genera permisos para las entidades descubiertas por Shield en el panel `admin`.
- `AdminPanelSeeder`: crea el usuario admin inicial, el rol `super_admin` y asigna permisos.
- `DatabaseSeeder`: ejecuta ambos.

## Flujo recomendado para un proyecto nuevo

1. Clonar este repositorio como base.
2. Cambiar `APP_NAME`, URL, base de datos y credenciales del entorno.
3. Ejecutar `composer run setup`.
4. Ejecutar `php artisan db:seed`.
5. Acceder a `/admin`.
6. Cambiar la contraseña del admin inicial.
7. Ajustar branding, tema y navegación del panel.
8. Añadir recursos, páginas, widgets y políticas del proyecto.
9. Revisar si se mantiene MySQL/Redis y los drivers por defecto.
10. Revisar si el proyecto necesita MFA, verificación de email y developer login.

## Configuración relevante

### Filament

En [config/filament.php](/home/nemuru/Code/Own/filament-skeleton/config/filament.php) puedes controlar:

- `DEVELOPER_LOGIN_ENABLED`
- `FILAMENT_HAS_EMAIL_VERIFICATION`
- `FILAMENT_MFA_ENABLED`
- `FILAMENT_MFA_EMAIL`
- `FILAMENT_MFA_APP`
- `FILAMENT_MFA_REQUIRED`

### App

En [config/app.php](/home/nemuru/Code/Own/filament-skeleton/config/app.php) existe:

- `APP_ENABLE_HELPER_MODEL`

Si está activo en local, tras migraciones se regeneran helper models para IDE Helper.

### Panel admin

La configuración principal del panel está en [app/Providers/Filament/AdminPanelProvider.php](/home/nemuru/Code/Own/filament-skeleton/app/Providers/Filament/AdminPanelProvider.php).

Ahí están definidos:

- Path `/admin`
- Plugins del panel
- Visibilidad de navegación para logs y Horizon
- MFA
- Descubrimiento automático de resources, pages y widgets

## Redis, Horizon y Pulse

Este skeleton deja preparada la capa operativa, pero hay una diferencia importante:

- El `.env.example` usa `QUEUE_CONNECTION=database`.
- Horizon está configurado para trabajar con Redis.

Si el proyecto va a usar Horizon de verdad, debes configurar Redis correctamente y alinear la estrategia de colas del entorno. Documentarlo desde el inicio evita inconsistencias entre desarrollo y despliegue.

Pulse también forma parte del stack base del proyecto. Si el nuevo proyecto va a apoyarse en observabilidad desde el principio, merece la pena decidir pronto cómo se va a exponer y monitorizar en cada entorno.

## Notas sobre seguridad y uso interno

- Este repositorio está pensado como base pública para arrancar proyectos internos.
- Incluye credenciales por defecto únicamente para acelerar el bootstrap local.
- No conviene desplegar un entorno real sin rotar credenciales, revisar permisos y ajustar servicios externos.

## Primeras revisiones recomendadas al crear un proyecto

- Renombrar la aplicación y actualizar metadatos.
- Revisar `.env.example`.
- Decidir idioma por defecto final del proyecto.
- Revisar la estrategia de roles/permisos.
- Revisar si el admin del sistema debe seguir dependiendo de `is_admin`.
- Decidir si el acceso de desarrollador debe existir en local.
- Decidir si el proyecto usará MFA obligatorio.
- Alinear colas entre local, staging y producción.

## Estructura inicial relevante

- [composer.json](/home/nemuru/Code/Own/filament-skeleton/composer.json)
- [config/app.php](/home/nemuru/Code/Own/filament-skeleton/config/app.php)
- [config/filament.php](/home/nemuru/Code/Own/filament-skeleton/config/filament.php)
- [config/filament-shield.php](/home/nemuru/Code/Own/filament-skeleton/config/filament-shield.php)
- [config/filament-localization.php](/home/nemuru/Code/Own/filament-skeleton/config/filament-localization.php)
- [app/Providers/Filament/AdminPanelProvider.php](/home/nemuru/Code/Own/filament-skeleton/app/Providers/Filament/AdminPanelProvider.php)
- [database/seeders/DatabaseSeeder.php](/home/nemuru/Code/Own/filament-skeleton/database/seeders/DatabaseSeeder.php)
- [app/Filament/Resources/Users/UserResource.php](/home/nemuru/Code/Own/filament-skeleton/app/Filament/Resources/Users/UserResource.php)

## Estado actual del README

Este `README` describe el estado actual del skeleton en este repositorio. Si se añaden nuevos paneles, recursos, seeders o integraciones, conviene actualizarlo al mismo tiempo para que siga siendo útil como documentación de arranque.
