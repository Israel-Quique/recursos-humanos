<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estado de Solicitud de Boleta</title>
  <style type="text/css">
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; height: 100% !important; }
  </style>
</head>

<body bgcolor="#f1f5f9" style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased;">

  @php
    $esAprobado = ($estado === 'aprobado');
    $empleado = $permiso->empleado;
    $nombre = $empleado?->nombre_completo ?? 'Personal';
    $ci = $empleado?->codigo_biometrico ?: (string) ($empleado?->id ?? 'S/D');

    $fechaInicio = $permiso->fecha_inicio?->format('d/m/Y') ?? '';
    $fechaFin = $permiso->fecha_fin?->format('d/m/Y') ?? $fechaInicio;
    $rangoFechas = ($fechaInicio === $fechaFin) ? $fechaInicio : "{$fechaInicio} al {$fechaFin}";

    $esPorHoras = ($permiso->alcance === 'horas' && $permiso->hora_inicio && $permiso->hora_fin);
    if ($esPorHoras) {
      $horaIni = substr($permiso->hora_inicio, 0, 5);
      $horaFin = substr($permiso->hora_fin, 0, 5);
      $horarioTexto = "{$horaIni} a {$horaFin}";
      $minutos = (int) ($permiso->minutos_contabilizados ?? 0);
      $tiempoTexto = $minutos >= 60
        ? (intdiv($minutos, 60) . ' h ' . ($minutos % 60 ? ($minutos % 60 . ' min') : ''))
        : "{$minutos} min";
    } else {
      $horarioTexto = 'Jornada Completa';
      $dias = (int) ($permiso->fecha_inicio && $permiso->fecha_fin
        ? ($permiso->fecha_inicio->diffInDays($permiso->fecha_fin) + 1)
        : 1);
      $tiempoTexto = $dias === 1 ? '1 día' : "{$dias} días";
    }

    $headerBg = $esAprobado ? '#059669' : '#e11d48';
    $statusText = $esAprobado ? '✓ SOLICITUD APROBADA' : '✕ SOLICITUD RECHAZADA';
    $statusTitle = $esAprobado ? 'Solicitud de Boleta Aprobada' : 'Solicitud de Boleta Rechazada';
  @endphp

  <!-- CONTENEDOR PRINCIPAL EXTERIOR -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f1f5f9" style="background-color: #f1f5f9; width: 100%; border-collapse: collapse;">
    <tr>
      <td align="center" style="padding: 30px 12px;">

        <!-- TARJETA DEL CORREO (ANCHO FIJO COMPATIBLE CON ZIMBRA Y OUTLOOK) -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="580" bgcolor="#ffffff" style="max-width: 580px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
          
          <!-- CABECERA INSTITUCIONAL CON COLOR DE FONDO DIRECTO -->
          <tr>
            <td align="center" bgcolor="{{ $headerBg }}" style="background-color: {{ $headerBg }}; padding: 26px 20px 22px 20px; text-align: center; color: #ffffff;">
              
              @php
                $logoEmail = public_path('images/menu-logo.png');
              @endphp
              @if (file_exists($logoEmail))
                <div style="margin-bottom: 14px; text-align: center;">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                    <tr>
                      <td bgcolor="#ffffff" style="background-color: #ffffff; padding: 4px 14px; border-radius: 8px;">
                        <img src="{{ $message->embed($logoEmail) }}" alt="Correos de Bolivia" height="34" style="height: 34px; width: auto; display: block; border: 0;" />
                      </td>
                    </tr>
                  </table>
                </div>
              @endif

              <!-- DISTINTIVO DE ESTADO -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 10px auto;">
                <tr>
                  <td align="center" style="padding: 5px 16px; border-radius: 20px; font-size: 11px; font-weight: bold; letter-spacing: 0.05em; text-transform: uppercase; background-color: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.45); color: #ffffff; font-family: Arial, sans-serif;">
                    {{ $statusText }}
                  </td>
                </tr>
              </table>

              <!-- TÍTULO DE LA CABECERA -->
              <h1 style="margin: 0 0 6px 0; font-size: 20px; font-weight: bold; color: #ffffff; line-height: 1.3; font-family: Arial, sans-serif;">
                {{ $statusTitle }}
              </h1>
              <p style="margin: 0; font-size: 12.5px; color: #ffffff; opacity: 0.95; font-family: Arial, sans-serif;">
                Unidad de Recursos Humanos &middot; Correos de Bolivia
              </p>
            </td>
          </tr>

          <!-- CUERPO DEL CORREO -->
          <tr>
            <td bgcolor="#ffffff" style="background-color: #ffffff; padding: 26px 24px;">

              <!-- SALUDO -->
              <div style="font-size: 15px; font-weight: bold; color: #0f172a; margin-bottom: 14px; font-family: Arial, sans-serif;">
                Estimado/a {{ $nombre }},
              </div>

              <!-- MENSAJE PRINCIPAL -->
              @if ($esAprobado)
                <div style="font-size: 13.5px; color: #334155; line-height: 1.6; margin-bottom: 20px; font-family: Arial, sans-serif;">
                  Te comunicamos que tu solicitud de boleta de permiso/comisión ha sido <strong style="color: #059669;">revisada y aprobada satisfactoriamente</strong> por el Departamento de Recursos Humanos.
                </div>
              @else
                <div style="font-size: 13.5px; color: #334155; line-height: 1.6; margin-bottom: 18px; font-family: Arial, sans-serif;">
                  Te informamos que tu solicitud de boleta de permiso/comisión <strong style="color: #e11d48;">ha sido rechazada</strong> luego de la evaluación de Recursos Humanos.
                </div>

                <!-- CUADRO DESTACADO CON MOTIVO DE RECHAZO -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff1f2" style="background-color: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 8px; margin-bottom: 22px; width: 100%;">
                  <tr>
                    <td style="padding: 14px 16px;">
                      <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #9f1239; margin-bottom: 6px; font-family: Arial, sans-serif;">
                        ⚠️ Motivo o Justificación del Rechazo indicada por RR.HH.:
                      </div>
                      <div style="font-size: 13.5px; font-weight: bold; color: #881337; line-height: 1.5; font-family: Arial, sans-serif;">
                        "{{ $motivoRechazo ?: 'No especificado por el administrador.' }}"
                      </div>
                    </td>
                  </tr>
                </table>
              @endif

              <!-- RESUMEN DE LA SOLICITUD -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; width: 100%;">
                <tr>
                  <td style="padding: 16px 18px;">
                    
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; color: #1e60c6; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; font-family: Arial, sans-serif;">
                      Resumen de la Solicitud
                    </div>

                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; border-collapse: collapse;">
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif; width: 40%;">Funcionario:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ $nombre }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">C.I. / Carnet:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ $ci }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Tipo de Permiso:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ mb_strtoupper($permiso->tipo_label ?? 'PERMISO') }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Fecha(s):</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ $rangoFechas }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Horario / Jornada:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ $horarioTexto }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Tiempo Solicitado:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ mb_strtoupper($tiempoTexto) }}</td>
                      </tr>
                      @if ($permiso->motivo)
                        <tr>
                          <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Motivo Declarado:</td>
                          <td style="padding: 5px 0; font-size: 12.5px; color: #0f172a; font-weight: bold; text-align: right; font-family: Arial, sans-serif;">{{ $permiso->motivo }}</td>
                        </tr>
                      @endif
                      <tr>
                        <td style="padding: 5px 0; font-size: 12.5px; color: #64748b; font-family: Arial, sans-serif;">Estado:</td>
                        <td style="padding: 5px 0; font-size: 12.5px; font-weight: bold; text-align: right; font-family: Arial, sans-serif; color: {{ $esAprobado ? '#059669' : '#e11d48' }};">
                          {{ $esAprobado ? 'APROBADO' : 'RECHAZADO' }}
                        </td>
                      </tr>
                    </table>

                  </td>
                </tr>
              </table>

              <!-- NOTA INFORMATIVA FINAL -->
              <div style="font-size: 12px; color: #64748b; line-height: 1.5; font-family: Arial, sans-serif;">
                @if ($esAprobado)
                  Puedes imprimir tu boleta firmada para tus registros o presentarla a tu jefatura inmediata si corresponde.
                @else
                  Si tienes dudas o necesitas subsanar la documentación requerida, puedes comunicarte con la Unidad de Recursos Humanos.
                @endif
              </div>

            </td>
          </tr>

          <!-- PIE DE PÁGINA INSTITUCIONAL -->
          <tr>
            <td align="center" bgcolor="#f8fafc" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 20px; text-align: center; font-size: 11.5px; color: #64748b; font-family: Arial, sans-serif; line-height: 1.5;">
              <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">
                Correos de Bolivia &middot; Sistema de Recursos Humanos
              </div>
              <div>
                Este es un correo automático de notificación institucional. Por favor no respondas a este mensaje.
              </div>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>

</html>