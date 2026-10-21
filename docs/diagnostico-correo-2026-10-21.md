# Diagnóstico de entrega de códigos de boleta

Fecha: 21 de octubre de 2026. Horario: Bolivia (UTC−04).

## Resultado confirmado

La conexión de la aplicación con Zimbra funciona con TLS obligatorio,
certificado validado y autenticación correcta. Se corrigió la configuración
del proceso web, que seguía usando el almacén de certificados anterior.

Se envió una única prueba autorizada a `cakeryja@gmail.com`. Zimbra aceptó
el mensaje, pero después devolvió un aviso de no entrega. Gmail lo rechazó
con `550 5.7.1` indicando reputación muy baja del dominio emisor.
No es un mensaje pendiente de revisar en Spam ni un error de la contraseña
SMTP. Cambiar únicamente la cuenta dentro del mismo dominio no resuelve
este rechazo por reputación.

La prueba verificó el código y rechazó su reutilización, sin crear una boleta.
El código del correo técnico no debe usarse en el formulario.

## Datos para localizar el envío en Zimbra

- MTA emisor: `zimbra.correos.gob.bo`.
- IP pública de salida identificada por Gmail: `181.115.157.86`.
- Fecha de recepción en Zimbra: `2026-10-21 11:01:34 -0400`.
- Identificador de cola Postfix: `8FA4830CFE941`.
- Message-ID: `<ac12b8704994dedf64370b8186aa60ca@correos.gob.bo>`.
- Destinatario: `cakeryja@gmail.com`.
- MTA remoto: `gmail-smtp-in.l.google.com`.
- Resultado DSN: `Action=failed`, `Status=5.7.1`.

Estos datos proceden de la devolución correspondiente a la prueba, consultada
en modo de solo lectura. No se incluyen contraseña SMTP ni códigos de acceso.

## Comprobaciones públicas realizadas

- SPF publicado e incluye `181.115.157.86`.
- DMARC publicado, con política `quarantine`.
- El mensaje tiene firma DKIM para `correos.gob.bo`, selector
  `1019dkimcorreos`; su registro DNS está publicado.
- El PTR de `181.115.157.86` devuelve `zimbra.correos.gob.bo`.

La presencia de estos registros no certifica por sí sola que todos los
mensajes superen la autenticación. La causa explícita de este rechazo es
la reputación del dominio, según la respuesta de Gmail.

## Trabajo necesario de TI / administrador del correo

1. Buscar la cola y el Message-ID anteriores en `/var/log/zimbra.log` y
   comprobar el alcance de los rechazos hacia Gmail.
2. Revisar las cuentas comprometidas y detener cualquier envío abusivo;
   revocar sesiones y accesos de aplicación y mantener Zimbra actualizado.
3. Verificar autenticación y alineación SPF/DKIM/DMARC y consultar la
   reputación del dominio/IP en Google Postmaster Tools.
4. Recuperar la reputación y realizar una nueva prueba autorizada. Mientras
   tanto, comprobar por separado la entrega a una cuenta institucional Zimbra.

La aplicación no puede retirar este bloqueo ni administrar la reputación
de Google con las credenciales SMTP. No se modificaron el DNS, las colas
del servidor ni las configuraciones de Gmail.

Para futuros envíos, el sistema registra el Message-ID de aceptación SMTP
sin registrar el código, para que TI pueda seguir cada mensaje en los logs.
La aceptación por Zimbra no constituye confirmación de entrega al destinatario.

## Prueba interna autorizada

Se envió una única prueba a `marco.espinoza@correos.gob.bo`. Zimbra la aceptó
con identificador SMTP `22B4730CFE942`. La validación del código y el rechazo
de su reutilización funcionaron. No se creó ninguna boleta ni se modificó
la ficha del empleado. El destinatario confirmó que recibió el mensaje.

Conclusión: la entrega interna a Zimbra y la validación del código funcionan.
La entrega de la prueba a Gmail quedó bloqueada por reputación del dominio,
según la devolución identificada anteriormente.

Referencias oficiales:

- [Bloqueo de mensajes en Gmail](https://support.google.com/mail/answer/188131?hl=es).
- [Directrices para remitentes de Gmail](https://support.google.com/mail/answer/81126?hl=es).
- [Identificadores de cola y Message-ID en Zimbra](https://wiki.zimbra.com/index.php?title=Postfix-Queue-ID-vs.-message-id-MTA).
