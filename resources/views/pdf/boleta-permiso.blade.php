<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Comisión - Permiso Particular</title>
  <style>
    @page {
      margin: 15mm 15mm;
      size: letter portrait;
    }
    * {
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }
    body {
      margin: 0;
      padding: 0;
      font-size: 9pt;
      color: #000;
    }
    .papeleta-container {
      border: 1.5px solid #000;
      padding: 14px 18px;
      margin-bottom: 25px;
      position: relative;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .header-logo {
      width: 85px;
      vertical-align: middle;
    }
    .header-titles {
      text-align: center;
      vertical-align: middle;
    }
    .title-main {
      font-size: 11pt;
      font-weight: bold;
      margin: 0 0 2px 0;
      letter-spacing: 0.5px;
    }
    .title-sub {
      font-size: 9pt;
      font-weight: bold;
      margin: 0 0 2px 0;
    }
    .title-dept {
      font-size: 8.5pt;
      font-weight: bold;
      margin: 0 0 4px 0;
    }
    .title-doc {
      font-size: 9.5pt;
      font-weight: bold;
      text-decoration: underline;
      margin: 2px 0 0 0;
    }
    
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .data-table td {
      padding: 4px 2px;
      vertical-align: middle;
    }
    .label-cell {
      font-size: 8pt;
      font-weight: bold;
      white-space: nowrap;
    }
    .value-box {
      border-bottom: 1px solid #000;
      font-size: 8.5pt;
      padding: 2px 6px;
      min-height: 16px;
    }
    .value-box-solid {
      border: 1px solid #000;
      font-size: 8.5pt;
      padding: 3px 6px;
      min-height: 16px;
    }

    /* Grid de Fechas y Horas */
    .schedule-table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0;
    }
    .schedule-table td, .schedule-table th {
      border: 1px solid #000;
      text-align: center;
      padding: 4px;
      font-size: 8pt;
    }
    .schedule-table th {
      background-color: #f1f5f9;
      font-weight: bold;
    }

    /* Checkboxes de tipo */
    .type-table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0 14px 0;
    }
    .type-table td {
      vertical-align: middle;
      font-size: 8.5pt;
      font-weight: bold;
    }
    .check-square {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 1.5px solid #000;
      text-align: center;
      line-height: 15px;
      font-size: 11pt;
      font-weight: bold;
      margin-left: 6px;
    }

    /* Firmas */
    .signatures-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    .signatures-table td {
      width: 33.33%;
      text-align: center;
      vertical-align: top;
      padding: 0 8px;
    }
    .signature-line {
      border-top: 1px solid #000;
      padding-top: 4px;
      font-size: 7.5pt;
      font-weight: bold;
    }

    /* Footer fecha y nota */
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    .city-date-text {
      text-align: right;
      font-size: 8pt;
      font-weight: bold;
      padding-bottom: 6px;
    }
    .note-text {
      font-size: 6.8pt;
      font-style: italic;
      border-top: 0.5px dashed #64748b;
      padding-top: 3px;
      margin: 0;
      color: #334155;
    }
    .cut-line {
      border-bottom: 1px dashed #94a3b8;
      margin: 18px 0 18px 0;
      text-align: center;
      position: relative;
    }
    .cut-label {
      position: absolute;
      top: -8px;
      left: 48%;
      background: #fff;
      padding: 0 6px;
      font-size: 7pt;
      color: #64748b;
    }
  </style>
</head>
<body>

  {{-- Generamos 2 copias por hoja como el formato oficial de papeletas --}}
  @for ($copy = 1; $copy <= 2; $copy++)
    <div class="papeleta-container">
      <table class="header-table">
        <tr>
          <td class="header-logo">
            @php
              $logoPath = public_path('images/menu-logo.png');
            @endphp
            @if (file_exists($logoPath))
              <img src="{{ $logoPath }}" style="max-width: 75px; max-height: 45px;" alt="Logo">
            @else
              <strong style="font-size: 14pt; color: #1e60c6;">AGBC</strong>
            @endif
          </td>
          <td class="header-titles">
            <div class="title-main">AGENCIA BOLIVIANA DE CORREOS</div>
            <div class="title-sub">DIRECCION ADMINISTRATIVA FINANCIERA</div>
            <div class="title-dept">RECURSOS HUMANOS</div>
            <div class="title-doc">PAPELETA DE COMISION - PERMISO PARTICULAR</div>
          </td>
          <td style="width: 75px; text-align: right; font-size: 7.5pt; color: #64748b; vertical-align: top;">
            {{ $copy === 1 ? 'ORIGINAL' : 'COPIA' }}
          </td>
        </tr>
      </table>

      <table class="data-table">
        <tr>
          <td class="label-cell" style="width: 25%;">NOMBRE DEL FUNCIONARIO:</td>
          <td style="width: 48%;">
            <div class="value-box"><strong>{{ mb_strtoupper($boleta['nombre'] ?? '') }}</strong></div>
          </td>
          <td class="label-cell" style="width: 6%; text-align: right;">C.I.:</td>
          <td style="width: 21%;">
            <div class="value-box"><strong>{{ $boleta['ci'] ?? '' }}</strong></div>
          </td>
        </tr>
        <tr>
          <td class="label-cell">CARGO:</td>
          <td colspan="3">
            <div class="value-box">{{ mb_strtoupper($boleta['cargo'] ?? 'PERSONAL') }}</div>
          </td>
        </tr>
        <tr>
          <td class="label-cell">MOTIVO:</td>
          <td colspan="3">
            <div class="value-box">{{ mb_strtoupper($boleta['motivo'] ?? '') }}</div>
          </td>
        </tr>
      </table>

      {{-- Horarios y Fechas --}}
      <table class="schedule-table" style="margin-top: 10px;">
        <thead>
          <tr>
            <th style="width: 35%;">DESDE FECHA Y HORA</th>
            <th style="width: 35%;">HASTA FECHA Y HORA</th>
            <th style="width: 30%;">TIEMPO SOLICITADO</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div><strong>{{ $boleta['desde_fecha'] ?? '' }}</strong></div>
              <div style="margin-top: 2px; color: #1e293b;">Hora: {{ $boleta['desde_hora'] ?? '--:--' }}</div>
            </td>
            <td>
              <div><strong>{{ $boleta['hasta_fecha'] ?? '' }}</strong></div>
              <div style="margin-top: 2px; color: #1e293b;">Hora: {{ $boleta['hasta_hora'] ?? '--:--' }}</div>
            </td>
            <td style="vertical-align: middle;">
              <strong style="font-size: 9pt; color: #0f172a;">{{ mb_strtoupper($boleta['tiempo_solicitado'] ?? '') }}</strong>
            </td>
          </tr>
        </tbody>
      </table>

      {{-- Tipo de Permiso --}}
      <table class="type-table">
        <tr>
          <td style="width: 33.3%;">
            COMISION
            <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'comision' ? 'X' : '' }}</span>
          </td>
          <td style="width: 33.3%; text-align: center;">
            PARTICULAR
            <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'particular' ? 'X' : '' }}</span>
          </td>
          <td style="width: 33.3%; text-align: right;">
            MEDICO
            <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'medico' ? 'X' : '' }}</span>
          </td>
        </tr>
      </table>

      {{-- Firmas --}}
      <table class="signatures-table">
        <tr>
          <td>
            <div class="signature-line">FIRMA FUNCIONARIO (A)</div>
          </td>
          <td>
            <div class="signature-line">FIRMA SELLO DEL INMEDIATO SUPERIOR</div>
          </td>
          <td>
            <div class="signature-line">FECHA Y SELLO DE RR.HH.</div>
          </td>
        </tr>
      </table>

      {{-- Lugar, Fecha y Nota --}}
      <table class="footer-table">
        <tr>
          <td class="city-date-text">
            {{ mb_strtoupper($boleta['lugar_fecha'] ?? '') }}
          </td>
        </tr>
        <tr>
          <td>
            <p class="note-text">NOTA: EL PRESENTE FORMULARIO NO DEBE CONTENER BORRONES, ENMIENDAS Y/O CORRECCIONES.</p>
          </td>
        </tr>
      </table>
    </div>

    @if ($copy === 1)
      <div class="cut-line">
        <span class="cut-label">✂ Cortar aquí</span>
      </div>
    @endif
  @endfor

</body>
</html>
