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

## Códigos de autenticación para boletas

Al desplegar, ejecutar `php artisan migrate --force` para crear `boleta_codigos`.
No se requieren nuevas cuentas de usuario ni contraseñas de empleados.

En la consulta por carnet o en el perfil de horas, abrir la boleta y pulsar
**Enviar / reenviar código a mi correo**. El código aleatorio de seis dígitos
se envía desde el remitente de RRHH configurado en `MAIL_FROM_ADDRESS`, mediante
el mismo correo del sistema. Escribirlo en el formulario y generar la boleta.
Cada código autoriza una sola generación; PDF y Excel requieren autenticación.

El destinatario se obtiene de la ficha del empleado en **Personal** y no puede
registrarse ni cambiarse desde el portal público. Si falta o es incorrecto,
RRHH debe corregirlo antes de solicitar el código. Se admite el correo válido
que RRHH haya registrado, incluido el institucional de Zimbra.

El código vence a los diez minutos, se bloquea tras cinco intentos incorrectos
y solo funciona para ese empleado y la sesión que lo solicitó. Se puede pedir
otro después de un minuto; el nuevo invalida el anterior. La base de datos
guarda el hash, y el código se consume junto con la generación del documento
en una transacción para impedir su reutilización.

Configurar `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
`MAIL_PASSWORD`, `MAIL_ENCRYPTION` y el remitente de RRHH con credenciales
vigentes de Zimbra, y ejecutar `php artisan config:clear` después de cambiarlas.
Los modos `log` y `array` no envían mensajes y no permiten solicitar códigos
fuera de las pruebas. Ante un fallo SMTP se muestra un error y no se habilita
la generación. No se necesita activar las notificaciones de aprobación/rechazo
de boletas para enviar estos códigos.

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
Los fallos de certificado se registran como `motivo=certificado_tls`, junto con
el tipo de proceso PHP y su archivo de autoridades; no se registran códigos
ni credenciales. Un código cuyo envío falló no se guarda y no puede verificarse.

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
