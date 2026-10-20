<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Comisión - Permiso Particular</title>
  <style>
    /* Hoja carta (215.9 x 279.4 mm): la boleta ocupa exactamente la mitad superior (139.7 mm) */
    @page {
      margin: 7mm 12mm 0 12mm;
      size: letter portrait;
    }
    * {
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }
    body {
      margin: 0;
      padding: 0;
      font-size: 8.5pt;
      color: #000;
    }
    .papeleta-container {
      width: 100%;
      border: 2px solid #0f172a;
      border-radius: 6px;
      padding: 10px 18px;
    }
    .cut-line {
      position: absolute;
      top: 132.7mm; /* 139.7mm (mitad exacta) - 7mm margen superior */
      left: -12mm;
      right: -12mm;
      border-top: 1px dashed #94a3b8;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .header-logo {
      vertical-align: middle;
    }
    .header-titles {
      text-align: center;
      vertical-align: middle;
    }
    .title-main {
      font-size: 11.5pt;
      font-weight: bold;
      margin: 0 0 3px 0;
      letter-spacing: 0.5px;
      color: #0f172a;
    }
    .title-sub {
      font-size: 9.5pt;
      font-weight: bold;
      margin: 0 0 2px 0;
      color: #334155;
    }
    .title-dept {
      font-size: 9pt;
      font-weight: bold;
      margin: 0 0 5px 0;
      color: #475569;
    }
    .title-doc {
      font-size: 10.5pt;
      font-weight: bold;
      text-decoration: underline;
      margin: 4px 0 0 0;
      color: #0f172a;
    }
    
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
    }
    .data-table td {
      padding: 3px 3px;
      vertical-align: middle;
    }
    .label-cell {
      font-size: 8.5pt;
      font-weight: bold;
      white-space: nowrap;
      color: #1e293b;
    }
    .value-box {
      border-bottom: 1px solid #000;
      font-size: 9pt;
      padding: 3px 6px;
      min-height: 18px;
    }

    /* Grid de Fechas y Horas */
    .schedule-table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0;
    }
    .schedule-table td, .schedule-table th {
      border: 1.5px solid #0f172a;
      text-align: center;
      padding: 4px 6px;
      font-size: 8.5pt;
    }
    .schedule-table th {
      background-color: #f1f5f9;
      font-weight: bold;
      color: #0f172a;
    }

    /* Checkboxes de tipo */
    .type-table {
      width: 100%;
      border-collapse: collapse;
      margin: 4px 0 6px 0;
    }
    .type-table td {
      vertical-align: middle;
      font-size: 9pt;
      font-weight: bold;
    }
    .check-square {
      display: inline-block;
      width: 18px;
      height: 18px;
      border: 1.5px solid #000;
      text-align: center;
      line-height: 16px;
      font-size: 11pt;
      font-weight: bold;
      margin-left: 6px;
    }

    /* Firmas */
    .signatures-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 42px;
    }
    .signatures-table td {
      width: 33.33%;
      text-align: center;
      vertical-align: top;
      padding: 0 12px;
    }
    .signature-line {
      border-top: 1.5px solid #000;
      padding-top: 6px;
      font-size: 8pt;
      font-weight: bold;
      color: #0f172a;
    }

    /* Footer fecha y nota */
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .city-date-text {
      text-align: right;
      font-size: 8.5pt;
      font-weight: bold;
      padding-bottom: 4px;
      color: #1e293b;
    }
    .note-text {
      font-size: 7.5pt;
      font-style: italic;
      border-top: 0.5px dashed #64748b;
      padding-top: 5px;
      margin: 0;
      color: #475569;
    }
  </style>
</head>
<body>

  <div class="papeleta-container">
    <table class="header-table">
      <tr>
        <td class="header-logo" style="width: 175px; text-align: left; vertical-align: middle;">
          @php
            $logoObrasPath = public_path('images/logo-mopsv-boleta.png');
            $logoObrasBase64 = file_exists($logoObrasPath)
              ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoObrasPath))
              : null;
          @endphp
          @if ($logoObrasBase64)
            <img src="{{ $logoObrasBase64 }}" height="42" style="height: 42px; width: auto; max-width: 170px;" alt="Ministerio de Obras Públicas, Servicios y Vivienda">
          @else
            <div style="font-size: 8pt; font-weight: bold; color: #334155; line-height: 1.15;">
              ESTADO PLURINACIONAL DE BOLIVIA<br>
              <span style="font-size: 7pt; color: #b45309;">MINISTERIO DE OBRAS PÚBLICAS</span>
            </div>
          @endif
        </td>
        <td class="header-titles">
          <div class="title-main">AGENCIA BOLIVIANA DE CORREOS</div>
          <div class="title-sub">DIRECCIÓN ADMINISTRATIVA FINANCIERA</div>
          <div class="title-dept">RECURSOS HUMANOS</div>
          <div class="title-doc">PAPELETA DE COMISIÓN - PERMISO PARTICULAR</div>
        </td>
        <td class="header-logo" style="width: 145px; text-align: right; vertical-align: middle;">
          @php
            $logoCorreosPath = public_path('images/logo-correos-nuevo.png');
            $logoCorreosBase64 = file_exists($logoCorreosPath)
              ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoCorreosPath))
              : null;
          @endphp
          @if ($logoCorreosBase64)
            <img src="{{ $logoCorreosBase64 }}" height="40" style="height: 40px; width: auto; max-width: 135px;" alt="Correos de Bolivia">
          @else
            <strong style="font-size: 13pt; color: #1e60c6;">CORREOS DE BOLIVIA</strong>
          @endif
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
          <div class="value-box">{{ mb_strtoupper($boleta['cargo'] ?? '') }}</div>
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
    <table class="schedule-table">
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
            <div><strong style="font-size: 9.5pt;">{{ $boleta['desde_fecha'] ?? '' }}</strong></div>
            <div style="margin-top: 3px; color: #334155; font-size: 9pt;">Hora: <strong>{{ $boleta['desde_hora'] ?? '--:--' }}</strong></div>
          </td>
          <td>
            <div><strong style="font-size: 9.5pt;">{{ $boleta['hasta_fecha'] ?? '' }}</strong></div>
            <div style="margin-top: 3px; color: #334155; font-size: 9pt;">Hora: <strong>{{ $boleta['hasta_hora'] ?? '--:--' }}</strong></div>
          </td>
          <td style="vertical-align: middle;">
            <strong style="font-size: 10.5pt; color: #0f172a;">{{ mb_strtoupper($boleta['tiempo_solicitado'] ?? '') }}</strong>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- Tipo de Permiso (Modelo 2: Comisión, Particular, Art. 45 RIP, Médico) --}}
    <table class="type-table">
      <tr>
        <td style="width: 22%;">
          COMISIÓN
          <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'comision' ? 'X' : '' }}</span>
        </td>
        <td style="width: 24%; text-align: center;">
          PARTICULAR
          <span class="check-square">{{ in_array(($boleta['tipo'] ?? ''), ['particular']) ? 'X' : '' }}</span>
        </td>
        <td style="width: 36%; text-align: center;">
          Art. 45, parág. III del R.I.P.
          <span class="check-square">{{ in_array(($boleta['tipo'] ?? ''), ['rip', 'omision', 'retraso']) ? 'X' : '' }}</span>
        </td>
        <td style="width: 18%; text-align: right;">
          MÉDICO
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

  {{-- Línea de corte: mitad exacta de la hoja carta --}}
  <div class="cut-line"></div>

</body>
</html>
