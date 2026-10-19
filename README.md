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

## Biometricos en el servidor de la empresa

Los equipos se agregan desde **Importar > Agregar biometrico** en el sistema
desplegado. Los registros se guardan en su base de datos; Git despliega el codigo,
no copia la base de datos local ni el archivo `.env`. Mantener la base de datos
de produccion entre despliegues y ejecutar `php artisan migrate --force`.
No usar `migrate:fresh` ni sustituir la base de datos de produccion por la local.

En **Importar**, el boton **Sincronizar historial** del equipo importa sus
marcaciones antiguas y recientes para los reportes. **Extraer Excel** solo
descarga un archivo. La primera sincronizacion automatica tambien toma el
historial completo. Las siguientes revisan la ventana reciente.
Los reportes se actualizan al terminar una importacion, sin esperar la cache.
Si un codigo del reloj ya pertenece a otra regional, el nuevo trabajador se
registra con un codigo diferenciado, por ejemplo `pando:1`. El trabajador de la
otra regional conserva sus datos. Si existe una coincidencia unica de nombre
en Pando, se reutiliza ese trabajador y se conserva su codigo registrado.

Para Pando registrar departamento `Pando`, sucursal `Sucursal Pando`, IP
`172.65.26.43`, puerto `4370`, modo `TCP/IP` y la contrasena de comunicacion
del reloj si tiene una configurada.

La conexion sale del servidor Laravel, no del navegador. Para el sistema en
`http://172.65.10.55:8129`, ejecutar en una terminal de **ese servidor**, desde
la carpeta del proyecto (o dentro del contenedor PHP si se utiliza Docker):

```bash
php artisan biometrico:diagnosticar 172.65.26.43
php artisan biometrico:diagnosticar 172.65.26.43 --sesion
```

Sin IP, el comando revisa todos los equipos registrados. No importa marcaciones
ni modifica la configuracion de los equipos. `--sesion` abre y cierra una sesion
ZKTeco para verificar tambien el extractor y la autenticacion.

- Si TCP falla, revisar la ruta de ida y retorno y las reglas de firewall entre
  el servidor de la aplicacion y `172.65.26.43:4370`. La prueba desde otra PC no
  confirma acceso desde el servidor. Si hay contenedor, revisar tambien su red.
- Si TCP funciona pero la sesion falla, revisar Python, `pyzk`, la contrasena de
  comunicacion y si el reloj tiene otra sesion ocupada.
- Si no aparecen equipos, agregarlos en la aplicacion desplegada y comprobar que
  el despliegue sigue utilizando la misma base de datos.

El servidor necesita Python y el paquete `pyzk` en el mismo interprete que usa
`PYTHON_BIN`. Por ejemplo, si utiliza `python`, instalar con
`python -m pip install pyzk` y definir `PYTHON_BIN=python` en el `.env` del servidor.
Tambien se puede usar la ruta absoluta de su interprete. Una ruta de Windows de
otra computadora no funciona en un servidor Linux. Despues de cambiar el `.env`,
ejecutar `php artisan config:cache`. No publicar el `.env` en Git.
