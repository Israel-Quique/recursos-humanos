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

## Acceso personal a Mis horas

En **Administración de accesos → Acceso de empleados a Mis horas**, RRHH puede
habilitar o bloquear todas las sucursales, una sucursal o personas seleccionadas
para un grupo de prueba. La excepción individual tiene prioridad sobre la sucursal,
y esta sobre la regla general. Sin reglas el acceso está bloqueado. Quitar una
excepción hace que se herede la regla superior; bloquear la regla general no
elimina las excepciones habilitadas.

El empleado ingresa desde **Consulta por carnet** (`/consulta-carnet`), con su
carnet o código biométrico y el campo de contraseña agregado en esa misma pantalla.
La contraseña inicial es su código biométrico.
La cuenta se crea al primer ingreso autorizado. Debe cambiar la contraseña antes
de consultar datos, usando al menos 8 caracteres y un valor diferente del código.
Solo puede consultar sus propias horas y generar sus boletas en la vista habitual
de perfil de horas. No necesita usar el formulario administrativo de inicio de sesión.
No tiene permisos de administración. Los bloqueos se comprueban también en las
sesiones abiertas. RRHH puede restablecer la contraseña al código biométrico;
se volverá a exigir el cambio. Las cuentas administrativas existentes se conservan.

Ejecutar `php artisan migrate --force` y `php artisan optimize:clear` al desplegar.
La migración agrega los campos de acceso personal y las reglas, y retira la tabla
de códigos de boletas, que dejó de utilizarse. No se habilita a nadie automáticamente.
El formulario de consulta por carnet sigue siendo público, pero exige contraseña
para consultar datos o acceder a boletas. No muestra la ficha al escribir el carnet.
Estas validaciones también se aplican si hay una sesión de RRHH abierta: el portal
exige las credenciales de la persona habilitada y el cambio de su contraseña inicial.
Los perfiles y enlaces anteriores requieren una sesión autorizada; conocer un
código o disponer de un enlace firmado no permite ver datos ajenos.

Se retiró la autenticación por código de correo para PDF/Excel. Se mantienen
el servicio de correo y su configuración SMTP en `.env` (`MAIL_MAILER=smtp`,
`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`
y el remitente). Los cambios de acceso no envían correos ni códigos.

### Protección del correo del sistema

SMTP exige TLS y un certificado válido, con 15 segundos de tiempo de espera.
Si PHP no reconoce la autoridad del certificado de Zimbra, configurar
`openssl.cafile` en el `php.ini` del servidor con un almacén de autoridades
confiables actualizado. No desactivar la verificación del certificado.
Después de modificar `php.ini`, reiniciar Apache o el proceso PHP que atiende
la aplicación. La cuenta SMTP y `MAIL_FROM_ADDRESS` deben corresponder al
remitente autorizado.

Si SMTP funciona desde consola pero falla en la página, comprobar el proceso
web: puede seguir usando el `openssl.cafile` anterior hasta reiniciarse.
Verificar la configuración TLS desde el proceso web y evitar registrar credenciales
en los mensajes de diagnóstico.

El archivo `.env` se conserva solo en el servidor y queda fuera del seguimiento
de Git. Retirarlo de Git no elimina versiones anteriores del historial:
revocar o cambiar cualquier contraseña que haya quedado publicada, incluidas
las de la cuenta anterior. La plantilla `.env.example` no incluye contraseña
SMTP; para el sistema usar una cuenta dedicada y, si el administrador de Zimbra
lo permite, una contraseña de aplicación revocable junto con 2FA en el webmail.

Una cuenta comprometida requiere que TI revoque sesiones y accesos de aplicación,
revise reglas de reenvío y dispositivos, y actualice Zimbra. Revisar y limpiar
el equipo afectado antes de introducir las nuevas credenciales. Estos cambios
de código no limpian malware ni recuperan por sí solos el buzón comprometido.

## Biometricos en el servidor de la empresa

Los equipos se agregan desde **Importar > Agregar biometrico** en el sistema
desplegado. Los registros se guardan en su base de datos; Git despliega el codigo,
no copia la base de datos local ni el archivo `.env`. Mantener la base de datos
de produccion entre despliegues y ejecutar `php artisan migrate --force`.
No usar `migrate:fresh` ni sustituir la base de datos de produccion por la local.

En **Importar**, el boton **Sincronizar historial** del equipo importa sus
marcaciones antiguas y recientes para los reportes. **Extraer Excel** solo
descarga un archivo Excel real `.xlsx`, con las mismas columnas de marcaciones,
filtros y encabezado fijo. Conserva los ceros iniciales de códigos y tarjetas.
La primera sincronizacion automatica tambien toma el
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
