# Recursos Humanos - Asistencia Biométrica

Proyecto Recursos Humanos con diseño "Premium Light Minimalist" (Teal / Slate / Off-White) y módulo "Estructura & Código Laravel".

Requisitos locales:
- PHP 8.1+
- Composer
- Node.js + npm
- Python 3.8+, pandas, openpyxl

Instalación básica:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run dev

# Preparar Python
python -m venv .venv
.venv/bin/pip install pandas openpyxl
```

Uso del explorador de código (Livewire): agregar la ruta al `web.php` y montar el componente Livewire `EstructuraCodigoPage`.

Script Python de preprocesamiento: `scripts/process_biometrics.py`.

Notas:
- Este repositorio es un scaffold independiente. Para integrarlo en producción, ejecutar los pasos de instalación y migraciones.
