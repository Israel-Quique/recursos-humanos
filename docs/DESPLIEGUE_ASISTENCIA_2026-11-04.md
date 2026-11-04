# Actualización de asistencia — 4 de noviembre de 2026

Guía para el ingeniero que administra el servidor. Subir los cambios a GitHub no actualiza por sí solo la aplicación desplegada.

## Cambios incluidos

- Totales de omisiones y faltas actualizados junto con las marcaciones y sus detalles.
- Salida anticipada como omisión, conservando la hora real y respetando horarios especiales y permisos aprobados.
- Marcas desde la hora de salida sin entrada válida como omisión de entrada, sin atrasos falsos. La regla utiliza el horario de cada sucursal y también se aplica al consultar registros existentes.
- Lectura de Excel con mensaje específico cuando falta ZIP y errores de importación que muestran la causa real.
- Mayor tiempo de lectura de los biométricos y mensajes que distinguen un fallo de conexión de un fallo al descargar el historial.
- CSS compilado incluido en `public/css/app.css`.

Estos cambios no agregan migraciones ni modifican dependencias Composer o npm. Conservar el `.env`, la base de datos y los archivos de producción.

## 1. Actualizar el código

Desde la carpeta del proyecto en el servidor, comprobar que no existan cambios locales pendientes y actualizar la rama de producción:

```bash
git status --short
git pull --ff-only origin master
```

Si existen modificaciones propias del servidor, revisarlas antes de actualizar; no descartarlas con un reset.

## 2. Comprobar ZIP en PHP

```bash
php --ini
php -r "echo class_exists('ZipArchive') ? 'ZIP disponible' : 'Falta ZIP';"
```

El proceso PHP que atiende la web y el PHP de consola deben tener ZIP habilitado. El Excel puede generarse aunque la lectura de `.xlsx` falle por falta de esta extensión.

- Windows/XAMPP: habilitar `extension=zip` en el `php.ini` correspondiente.
- Linux: instalar o habilitar la extensión ZIP de la misma versión de PHP que utiliza la aplicación.
- Docker: incluir ZIP en la imagen PHP y reconstruir/recrear el servicio con el procedimiento habitual del proyecto.

Después de habilitar ZIP, reiniciar el proceso que sirve PHP: Apache, PHP-FPM, contenedor o `php artisan serve`, según la instalación. La configuración ZIP corregida en la computadora local no se transfiere por Git.

## 3. Revisar tiempos y limpiar cachés

Actualizar estas variables en el `.env` del servidor si todavía tienen los tiempos anteriores:

```dotenv
BIOMETRICO_SYNC_TIMEOUT=120
BIOMETRICO_SOCKET_TIMEOUT=30
BIOMETRICO_EXPORT_TIMEOUT=180
```

`SYNC_TIMEOUT` es el límite total de extracción automática; `SOCKET_TIMEOUT` limita cada espera de red. No cambiar IP, contraseña ni demás valores propios del servidor.

```bash
php artisan optimize:clear
php artisan config:cache
```

La limpieza permite que los reportes recalculen inmediatamente los atrasos y omisiones existentes. Reiniciar también los procesos PHP persistentes que conserven código anterior, mediante el mecanismo habitual de despliegue.

## 4. Sincronización automática

No se necesita un servicio nuevo para la importación manual o para las nuevas reglas de asistencia.

Para la sincronización automática debe existir una sola configuración del scheduler de Laravel:

- Linux: una entrada de cron cada minuto que ejecute `php /ruta/al/proyecto/artisan schedule:run` con la ruta real del servidor.
- Windows: una tarea o proceso administrado que mantenga `php artisan schedule:work` desde la carpeta del proyecto. El repositorio incluye `scripts/start-scheduler.ps1`.
- Si el scheduler ya funciona, conservarlo y reiniciarlo si corresponde al procedimiento de despliegue. No levantar una segunda instancia.

El mismo intérprete configurado en `PYTHON_BIN` debe tener instalado `pyzk`. Comprobarlo usando ese intérprete, por ejemplo:

```bash
python -c "import zk; print('pyzk disponible')"
```

Para comprobar los equipos desde el servidor sin importar marcaciones:

```bash
php artisan biometrico:diagnosticar
```

La conectividad debe existir desde el servidor de la aplicación hacia los relojes, no solamente desde la computadora del usuario.

## 5. Comprobación funcional

1. Importar el mismo Excel de Santa Cruz y comprobar el resultado por archivo.
2. Con salida programada a las 16:30, unas marcas a las 17:00 y 18:09 sin entrada válida deben mostrar entrada vacía, salida 18:09, una omisión de entrada y cero minutos de atraso.
3. Una entrada real de la mañana junto con su salida debe conservarse, incluso al reimportar un archivo que solo contiene salidas.
4. Una salida anterior al horario vigente debe contar como omisión y conservar su hora. Si la fecha especial autoriza salida a las 14:00, salir a las 14:00 cumple ese horario.
5. Verificar que el resumen, el desplegable de omisiones, el perfil y los reportes muestran el mismo criterio.

La corrección al consultar registros existentes no requiere volver a importar todo el historial.

## Validación conocida

Se verificaron importaciones, reimportaciones, horarios regionales y especiales, permisos, reportes, refrigerio y el extractor Python. En una revisión adicional se reprodujeron tres fallos preexistentes de `PersonalEspecialTest` por claves `hora_salida` faltantes en la edición manual; también ocurren usando el servicio original y no forman parte de esta actualización.
