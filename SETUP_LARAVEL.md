# Setup Laravel - Recursos Humanos

## Estado actual

Desde el 18 de julio de 2026 este proyecto ya incluye la base minima de Laravel para poder arrancar.

## Orden correcto de instalacion

1. Verificar herramientas:
   - `php -v`
   - `composer -V`
   - `node -v`
   - `npm -v`

2. Ejecutar setup automatico:

```powershell
.\scripts\setup-project.ps1
```

Eso hace automaticamente:

- crea `.env` desde `.env.example` si no existe
- crea `database/database.sqlite`
- asegura carpetas de `storage`, `bootstrap/cache`, `public/css` y `public/js`
- ejecuta `composer install`
- genera `APP_KEY`
- corre migraciones
- instala dependencias de Node
- compila Tailwind a `public/css/app.css`

## Levantar el proyecto

```powershell
.\scripts\start-project.ps1
```

Luego abre:

```text
http://127.0.0.1:8000
```

## Si quieres hacerlo manual

```powershell
copy .env.example .env
ni database\database.sqlite -ItemType File -Force
composer install
php artisan key:generate --force
php artisan migrate --force
npm install
npm run build
php artisan serve
```

## Notas

- El proyecto usa SQLite por defecto para evitar problemas iniciales de MySQL.
- Si quieres conectar MySQL despues, solo cambia `DB_CONNECTION` y credenciales en `.env`.
- La integracion directa con biometrico esta documentada en `INTEGRACION_BIOMETRICO.md`.
