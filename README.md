# Filament Skeleton

Aplicación base para arrancar proyectos internos sobre Laravel 13 y Filament 5 con autenticación, administración de usuarios, roles/permisos, observabilidad y utilidades de desarrollo ya preparadas.

No es un proyecto de producto final. Es una base para clonar, renombrar y extender.

## Arranque rápido

La vía recomendada es:

```bash
composer run setup
```
Esto hace lo siguiente:

- Instala dependencias PHP.
- Crea `.env` a partir de `.env.example` si no existe.
- Genera `APP_KEY`.
- Crea una base de datos si quieres en el mismo proceso, y corre migraciones y seed.
- Instala dependencias frontend.
- Compila assets.

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

### Acceso inicial

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


## Configuración relevante

### Filament

En [config/filament.php] puedes controlar:

- `DEVELOPER_LOGIN_ENABLED`
- `FILAMENT_HAS_EMAIL_VERIFICATION`
- `FILAMENT_MFA_ENABLED`
- `FILAMENT_MFA_EMAIL`
- `FILAMENT_MFA_APP`
- `FILAMENT_MFA_REQUIRED`

### App

En [config/app.php] existe:

- `APP_ENABLE_HELPER_MODEL`

Si está activo en local, tras migraciones se regeneran helper models para IDE Helper.

## Características e información adicional

### Requisitos

Antes de arrancar el proyecto, asegúrate de tener:

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- MySQL corriendo y una base de datos creada.
- Redis disponible si vas a usar Horizon y parte de la observabilidad como está pensada en este skeleton.

### Stack incluido

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

## Seeders incluidos

- `ShieldPermissionsSeeder`: genera permisos para las entidades descubiertas por Shield en el panel `admin`.
- `AdminPanelSeeder`: crea el usuario admin inicial, el rol `super_admin` y asigna permisos.
- `DatabaseSeeder`: ejecuta ambos.


## Redis, Horizon y Pulse

Este skeleton deja preparada la capa operativa, pero hay una diferencia importante:

- El `.env.example` usa `QUEUE_CONNECTION=database`.
- Horizon está configurado para trabajar con Redis.

Si el proyecto va a usar Horizon de verdad, debes configurar Redis correctamente y alinear la estrategia de colas del entorno. Documentarlo desde el inicio evita inconsistencias entre desarrollo y despliegue.

Pulse también forma parte del stack base del proyecto. Si el nuevo proyecto va a apoyarse en observabilidad desde el principio, merece la pena decidir pronto cómo se va a exponer y monitorizar en cada entorno.
