<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $asunto }}</title>
  <style type="text/css">
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; height: 100% !important; }
  </style>
</head>

<body bgcolor="#f0f4f8" style="margin: 0; padding: 0; background-color: #f0f4f8; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased;">

  @php
    $nombre = $empleado->nombre_completo ?: 'Estimado(a) colaborador(a)';
    $sucursal = $empleado->sucursal ?: 'Sede Central';
    $area = $empleado->area ?: 'General';
    $logoEmail = public_path('images/menu-logo.png');
  @endphp

  <!-- CONTENEDOR PRINCIPAL EXTERIOR -->
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f0f4f8" style="background-color: #f0f4f8; width: 100%; border-collapse: collapse;">
    <tr>
      <td align="center" style="padding: 28px 12px;">

        <!-- TARJETA DEL CORREO -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #cbd5e1; border-top: 4px solid #f7c931; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 18px rgba(10, 61, 124, 0.08);">
          
          <!-- CABECERA INSTITUCIONAL AZUL CORREOS DE BOLIVIA -->
          <tr>
            <td align="center" bgcolor="#0a3d7c" style="background-color: #0a3d7c; background: linear-gradient(135deg, #0a3d7c 0%, #004ea2 100%); padding: 26px 24px 22px 24px; text-align: center; color: #ffffff;">
              
              @if (file_exists($logoEmail))
                <div style="margin-bottom: 14px; text-align: center;">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                    <tr>
                      <td bgcolor="#ffffff" style="background-color: #ffffff; padding: 6px 18px; border-radius: 8px; border-bottom: 2px solid #f7c931;">
                        <img src="{{ $message->embed($logoEmail) }}" alt="Correos de Bolivia" height="38" style="height: 38px; width: auto; display: block; border: 0;" />
                      </td>
                    </tr>
                  </table>
                </div>
              @endif

              <!-- DISTINTIVO DE ALCANCE AMARILLO Y AZUL -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 10px auto;">
                <tr>
                  <td align="center" style="padding: 4px 16px; border-radius: 20px; font-size: 11px; font-weight: bold; letter-spacing: 0.06em; text-transform: uppercase; background-color: #f7c931; color: #0a3d7c; font-family: Arial, sans-serif; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                    {{ $alcanceLabel }}
                  </td>
                </tr>
              </table>

              <!-- ASUNTO / TÍTULO DE LA CABECERA -->
              <h1 style="margin: 0 0 6px 0; font-size: 20px; font-weight: bold; color: #ffffff; line-height: 1.35; font-family: Arial, sans-serif;">
                {{ $asunto }}
              </h1>
              <p style="margin: 0; font-size: 12px; color: #e0edff; font-family: Arial, sans-serif;">
                Unidad de Recursos Humanos &middot; Empresa Pública de Correos de Bolivia
              </p>
            </td>
          </tr>

          <!-- BANDA DECORATIVA AMARILLA INSTITUCIONAL -->
          <tr>
            <td height="4" bgcolor="#f7c931" style="background-color: #f7c931; font-size: 1px; line-height: 1px;">&nbsp;</td>
          </tr>

          <!-- CUERPO DEL CORREO -->
          <tr>
            <td bgcolor="#ffffff" style="background-color: #ffffff; padding: 28px 26px;">

              <!-- SALUDO AL FUNCIONARIO -->
              <p style="margin: 0 0 16px 0; font-size: 15px; color: #1e293b; line-height: 1.5; font-family: Arial, sans-serif;">
                Estimado(a) <strong style="color: #0a3d7c;">{{ $nombre }}</strong>,
              </p>

              <!-- FICHA RÁPIDA DEL DESTINATARIO -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f0f7ff" style="background-color: #f0f7ff; border: 1px solid #bfdbfe; border-left: 4px solid #004ea2; border-radius: 6px; margin-bottom: 22px;">
                <tr>
                  <td style="padding: 10px 14px; font-size: 12px; color: #334155; font-family: Arial, sans-serif;">
                    <strong style="color: #0a3d7c;">Sucursal:</strong> {{ $sucursal }} &nbsp;&bull;&nbsp;
                    <strong style="color: #0a3d7c;">Área:</strong> {{ $area }}
                    @if($empleado->codigo_biometrico)
                      &nbsp;&bull;&nbsp; <strong style="color: #0a3d7c;">Código:</strong> {{ $empleado->codigo_biometrico }}
                    @endif
                  </td>
                </tr>
              </table>

              <!-- MENSAJE / CONTENIDO PRINCIPAL -->
              <div style="font-size: 14.5px; color: #334155; line-height: 1.65; font-family: Arial, sans-serif; white-space: normal;">
                {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" style="color: #004ea2; font-weight: bold; text-decoration: underline; word-break: break-all;">$1</a>', nl2br(e($mensaje))) !!}
                
                @if(str_contains($mensaje, 'http://') || str_contains($mensaje, 'https://'))
                  @php
                    preg_match('/https?:\/\/[^\s]+/', $mensaje, $matchedEmailUrls);
                    $urlEmailEnlace = $matchedEmailUrls[0] ?? null;
                  @endphp
                  @if($urlEmailEnlace)
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 24px auto 14px auto;">
                      <tr>
                        <td align="center" bgcolor="#0a3d7c" style="background-color: #0a3d7c; border-radius: 8px; border-bottom: 3px solid #f7c931; box-shadow: 0 4px 12px rgba(10,61,124,0.22);">
                          <a href="{{ $urlEmailEnlace }}" target="_blank" style="font-family: Arial, sans-serif; font-size: 14px; color: #ffffff; font-weight: bold; text-decoration: none; padding: 12px 26px; display: inline-block;">
                            Ingresar al Portal de Autoconsulta &rarr;
                          </a>
                        </td>
                      </tr>
                    </table>
                  @endif
                @endif
              </div>

              <!-- SEPARADOR -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 26px; margin-bottom: 22px;">
                <tr>
                  <td style="border-top: 1px solid #e2e8f0; height: 1px;"></td>
                </tr>
              </table>

              <!-- FIRMA INSTITUCIONAL -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="font-size: 13px; color: #475569; font-family: Arial, sans-serif; line-height: 1.5;">
                    <p style="margin: 0 0 3px 0; font-weight: bold; color: #0a3d7c; font-size: 14px;">
                      Unidad de Recursos Humanos
                    </p>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">
                      Empresa Pública de Correos de Bolivia &middot; La Paz, Bolivia
                    </p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- PIE DE PÁGINA INSTITUCIONAL -->
          <tr>
            <td bgcolor="#f8fafc" style="background-color: #f8fafc; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11.5px; color: #64748b; font-family: Arial, sans-serif; line-height: 1.45;">
              <p style="margin: 0 0 4px 0;">
                Este correo electrónico ha sido emitido oficialmente por el Sistema de Recursos Humanos para la plataforma institucional <strong>Zimbra</strong> de Correos de Bolivia.
              </p>
              <p style="margin: 0; font-size: 10.5px; color: #94a3b8;">
                Fecha de emisión: {{ now()->translatedFormat('d \d\e F \d\e Y - H:i') }}
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
