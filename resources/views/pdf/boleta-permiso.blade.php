<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Comisión - Permiso Particular</title>
  <style>
    @page {
      margin: 18mm 18mm;
      size: letter portrait;
    }
    * {
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }
    body {
      margin: 0;
      padding: 0;
      font-size: 9.5pt;
      color: #000;
    }
    .papeleta-container {
      border: 2px solid #0f172a;
      border-radius: 6px;
      padding: 24px 26px;
      margin: 0 auto;
      position: relative;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
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
      margin-top: 10px;
    }
    .data-table td {
      padding: 6px 3px;
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
      margin: 16px 0;
    }
    .schedule-table td, .schedule-table th {
      border: 1.5px solid #0f172a;
      text-align: center;
      padding: 8px 6px;
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
      margin: 14px 0 22px 0;
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
      margin-top: 55px;
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
      margin-top: 30px;
    }
    .city-date-text {
      text-align: right;
      font-size: 8.5pt;
      font-weight: bold;
      padding-bottom: 8px;
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
        <td class="header-logo" style="width: 170px; text-align: left; vertical-align: middle;">
          @php
            $logoObrasPath = public_path('images/obrasPublicas.png');
            $logoObrasBase64 = file_exists($logoObrasPath)
              ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoObrasPath))
              : null;
          @endphp
          @if ($logoObrasBase64)
            <img src="{{ $logoObrasBase64 }}" height="56" style="height: 56px; width: auto; max-width: 160px;" alt="Ministerio de Obras Públicas">
          @else
            <div style="font-size: 8.5pt; font-weight: bold; color: #334155; line-height: 1.15;">
              ESTADO PLURINACIONAL<br>DE BOLIVIA
            </div>
          @endif
        </td>
        <td class="header-titles">
          <div class="title-main">AGENCIA BOLIVIANA DE CORREOS</div>
          <div class="title-sub">DIRECCIÓN ADMINISTRATIVA FINANCIERA</div>
          <div class="title-dept">RECURSOS HUMANOS</div>
          <div class="title-doc">PAPELETA DE COMISIÓN - PERMISO PARTICULAR</div>
        </td>
        <td class="header-logo" style="width: 170px; text-align: right; vertical-align: middle;">
          @php
            $logoCorreosPath = public_path('images/menu-logo.png');
            $logoCorreosBase64 = file_exists($logoCorreosPath)
              ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoCorreosPath))
              : null;
          @endphp
          @if ($logoCorreosBase64)
            <img src="{{ $logoCorreosBase64 }}" height="52" style="height: 52px; width: auto; max-width: 160px;" alt="Correos de Bolivia">
          @else
            <strong style="font-size: 15pt; color: #1e60c6;">CORREOS</strong>
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

    {{-- Tipo de Permiso --}}
    <table class="type-table">
      <tr>
        <td style="width: 33.3%;">
          COMISIÓN
          <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'comision' ? 'X' : '' }}</span>
        </td>
        <td style="width: 33.3%; text-align: center;">
          PARTICULAR
          <span class="check-square">{{ ($boleta['tipo'] ?? '') === 'particular' ? 'X' : '' }}</span>
        </td>
        <td style="width: 33.3%; text-align: right;">
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

</body>
</html>
