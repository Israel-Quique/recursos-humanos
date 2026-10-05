<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Control de Asistencia del Personal - {{ $periodoLabel }}</title>
  <style>
    @page {
      margin: 10mm 10mm 10mm 10mm;
      size: a4 landscape;
    }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #000000;
      font-size: 7px;
      line-height: 1.2;
      margin: 0;
      padding: 0;
    }

    /* Membrete optimizado para impresión en tóner / monocromático */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 2px solid #000000;
      padding-bottom: 4px;
      margin-bottom: 6px;
    }
    .header-table td {
      border: none;
      padding: 0;
    }
    .banner-kicker {
      font-size: 6px;
      font-weight: bold;
      color: #475569;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .banner-title {
      font-size: 11px;
      font-weight: 900;
      color: #000000;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      margin: 2px 0 1px 0;
    }
    .banner-subtitle {
      font-size: 6.8px;
      color: #1e293b;
    }
    .period-box {
      border: 1.5px solid #000000;
      background: #f8fafc;
      padding: 3px 8px;
      text-align: center;
      display: inline-block;
    }
    .period-label {
      font-size: 5.5px;
      font-weight: bold;
      color: #475569;
      letter-spacing: 0.06em;
    }
    .period-value {
      font-size: 8.5px;
      font-weight: 900;
      color: #000000;
    }

    /* Tabla principal de asistencia */
    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .matrix-table thead {
      display: table-header-group;
    }
    .matrix-table tfoot {
      display: table-row-group;
    }
    .matrix-table tr {
      page-break-inside: avoid;
    }
    .matrix-table th {
      background-color: #f1f5f9;
      color: #000000;
      padding: 2.5px 1px;
      font-size: 6.5px;
      font-weight: bold;
      border: 1px solid #475569;
      text-align: center;
      vertical-align: middle;
    }
    .matrix-table th.th-day {
      background-color: #f8fafc;
      width: 19px;
    }
    .matrix-table th.th-summary-dias {
      width: 32px;
      background-color: #e2e8f0;
      border-left: 2px solid #000000;
    }
    .matrix-table th.th-summary-monto {
      width: 54px;
      background-color: #cbd5e1;
    }
    .matrix-table td {
      padding: 2.5px 1px;
      border: 1px solid #94a3b8;
      font-size: 6.5px;
      text-align: center;
      vertical-align: middle;
    }

    /* Celdas con alto contraste para impresión en tóner / blanco y negro */
    /* A: Asistencia - Celda blanca limpia, máximo ahorro de tóner */
    .cell-a-asistencia {
      background-color: #ffffff;
      color: #475569;
      font-weight: normal;
    }

    /* P: ya no se usa, pero se mantiene por compatibilidad */
    .cell-p {
      background-color: #ffffff;
      color: #475569;
      font-weight: normal;
    }

    /* Perm: Permiso dinámico genérico */
    .cell-perm {
      background-color: #f1f5f9 !important;
      color: #000000 !important;
      font-weight: bold !important;
      border: 1.5px solid #94a3b8 !important;
    }

    /* F: Falta injustificada - Invertido negro sólido 100% con letra blanca bold (máximo impacto visual) */
    .cell-f {
      background-color: #000000 !important;
      color: #ffffff !important;
      font-weight: 900 !important;
      border: 1px solid #000000 !important;
    }

    /* O: Omisión de marcado - Trama gris medio (~40%) con borde continuo oscuro y texto negro bold */
    .cell-o {
      background-color: #94a3b8 !important;
      color: #000000 !important;
      font-weight: 900 !important;
      border: 1.5px solid #1e293b !important;
    }

    .cell-oe { background-color: #d1d5db !important; color: #000000 !important; font-weight: 900 !important; border: 2px solid #111827 !important; }
    .cell-os { background-color: #e5e7eb !important; color: #000000 !important; font-weight: 900 !important; border: 1.5px dashed #111827 !important; }

    /* A: Atraso - Celda blanca con subrayado marcador grueso inferior */
    .cell-a {
      background-color: #ffffff !important;
      color: #000000 !important;
      font-weight: 900 !important;
      border: 1px solid #94a3b8 !important;
      border-bottom: 2.5px solid #000000 !important;
    }

    /* Bm: Baja médica - Gris claro (~15%) enmarcado con doble línea negra */
    .cell-bm {
      background-color: #e2e8f0 !important;
      color: #000000 !important;
      font-weight: bold !important;
      border: 2px double #000000 !important;
    }

    /* Cv: Comisión de viaje - Celda con borde discontinuo (dashed) y texto en cursiva */
    .cell-cv {
      background-color: #f8fafc !important;
      color: #000000 !important;
      font-weight: bold !important;
      font-style: italic !important;
      border: 1.5px dashed #000000 !important;
    }

    /* Fe: Feriado o asueto - gris con borde grueso */
    .cell-fe {
      background-color: #cbd5e1 !important;
      color: #000000 !important;
      font-weight: 900 !important;
      border: 2px solid #475569 !important;
    }

    /* Filas de totales */
    .total-row td {
      background-color: #f8fafc;
      font-weight: 900;
      font-size: 7.5px;
      color: #000000;
      border-top: 2px solid #000000;
      border-bottom: 2.5px double #000000;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: bold; }

    /* Simbología explicativa para lectura en tóner */
    .legend-box {
      page-break-inside: avoid;
      margin-bottom: 6px;
    }
    .legend-banner {
      background-color: #1e293b;
      color: #ffffff;
      padding: 2.5px 6px;
      font-size: 6.8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border: 1px solid #1e293b;
      border-bottom: none;
    }
    .legend-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #475569;
      background: #ffffff;
    }
    .legend-table td {
      width: 16.66%;
      padding: 3.5px 4.5px;
      border-right: 1px solid #cbd5e1;
      vertical-align: top;
    }
    .legend-badge {
      display: inline-block;
      width: 15px;
      height: 14px;
      line-height: 14px;
      text-align: center;
      font-weight: bold;
      font-size: 7.5px;
      margin-right: 3px;
      vertical-align: middle;
    }
    .legend-title {
      font-weight: bold;
      font-size: 6.8px;
      color: #000000;
    }
    .legend-desc {
      font-size: 5.6px;
      color: #334155;
      margin-top: 2px;
      line-height: 1.15;
    }

    /* Firmas institucionales */
    .signatures {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      page-break-inside: avoid;
    }
    .signatures td {
      width: 33.33%;
      text-align: center;
      font-size: 6.8px;
      padding: 0 20px;
      border: none;
    }
    .sign-line {
      border-top: 1px solid #000000;
      padding-top: 3px;
      font-weight: bold;
      color: #000000;
    }
    .sign-sub {
      font-weight: normal;
      color: #475569;
      font-size: 5.8px;
    }

    .footer-note {
      margin-top: 6px;
      font-size: 6px;
      color: #475569;
      text-align: right;
      border-top: 0.5px solid #cbd5e1;
      padding-top: 3px;
    }
  </style>
</head>
<body>

  {{-- Membrete institucional de alto contraste (Ahorro de tóner) --}}
  <table class="header-table">
    <tr>
      <td style="vertical-align: middle;">
        <div class="banner-kicker">AGENCIA BOLIVIANA DE CORREOS · RECURSOS HUMANOS</div>
        <div class="banner-title">CONTROL DE ASISTENCIA DEL PERSONAL · PLANILLA DE REFRIGERIO</div>
        <div class="banner-subtitle">
          Sucursal / Ciudad: <strong>{{ $sucursalLabel }}</strong> &nbsp;|&nbsp;
          Tarifa Diaria: <strong>Bs. {{ number_format($tarifaDiaria, 2) }} / día</strong> &nbsp;|&nbsp;
          Emisión: <strong>{{ $emision }}</strong>
        </div>
      </td>
      <td style="width: 140px; text-align: right; vertical-align: middle;">
        <div class="period-box">
          <div class="period-label">MES / PERÍODO</div>
          <div class="period-value">{{ mb_strtoupper($periodoLabel) }}</div>
        </div>
      </td>
    </tr>
  </table>

  {{-- Tabla de Matriz de Asistencia (Lunes a Viernes) --}}
  <table class="matrix-table">
    <thead>
      <tr>
        <th style="width: 14px;">N°</th>
        <th class="text-left" style="width: 120px; padding-left: 4px;">Nombre y Apellido</th>

        @foreach($diasMes as $dia)
          <th class="th-day">
            <div>{{ $dia['dia'] }}</div>
            <div style="font-size: 5.8px; color: #475569; margin-top: 1px; font-weight: normal;">{{ $dia['dia_nombre'] }}</div>
          </th>
        @endforeach

        <th class="th-summary-dias">Días Desc.</th>
        <th class="th-summary-monto">A No Pagar (Bs.)</th>
        <th class="th-summary-dias">Días Pag.</th>
        <th class="th-summary-monto">A Pagar (Bs.)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $i => $item)
        @php
          $totalDias = (int) ($item['total_dias'] ?? 0);
          $hasDiscount = $totalDias > 0;
          $diasItem = $item['dias'] ?? [];
        @endphp
        <tr>
          <td>{{ $i + 1 }}</td>
          <td class="text-left font-bold" style="padding-left: 4px;">{{ $item['nombre'] }}</td>

          @foreach($diasMes as $dia)
            @php
              $st = strtolower($diasItem[$dia['fecha']] ?? '');
              $cellClass = match($st) {
                ''   => 'cell-a-asistencia', // sin dato: celda vacía
                'a'  => 'cell-a-asistencia',
                'p'  => 'cell-a-asistencia', // compatibilidad con datos viejos
                'f'  => 'cell-f',
                'o'  => 'cell-o',
                'oe' => 'cell-oe',
                'os' => 'cell-os',
                'bm' => 'cell-bm',
                'cv' => 'cell-cv',
                'fe' => 'cell-fe',
                default => 'cell-perm',
              };
              $cellCode = match($st) {
                ''   => '',  // sin dato biométrico: celda en blanco
                'a'  => 'A',
                'p'  => 'A', // compatibilidad con datos viejos
                'f'  => 'F',
                'o'  => 'O',
                'oe' => 'Oe',
                'os' => 'Os',
                'bm' => 'Bm',
                'cv' => 'Cv',
                'fe' => 'Fe',
                default => strtoupper(substr($st,0,2)),
              };
            @endphp
            <td class="{{ $cellClass }}">{{ $cellCode }}</td>
          @endforeach

          <td class="font-bold text-center" style="background: {{ $hasDiscount ? '#e2e8f0' : 'transparent' }}; border-left: 2px solid #000000; color: {{ $hasDiscount ? '#000000' : '#94a3b8' }};">
            {{ $totalDias }} d
          </td>
          <td class="text-right font-bold" style="background: {{ $hasDiscount ? '#f1f5f9' : 'transparent' }}; padding-right: 3px; color: {{ $hasDiscount ? '#000000' : '#94a3b8' }};">
            Bs. {{ number_format($item['total_monto'] ?? 0, 2) }}
          </td>
          <td class="font-bold text-center" style="background: #f0fdf4; color: #166534;">{{ $item['dias_pagados'] ?? 0 }} d</td>
          <td class="text-right font-bold" style="background: #f0fdf4; color: #166534; padding-right: 3px;">Bs. {{ number_format($item['monto_pagado'] ?? 0, 2) }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ count($diasMes) + 6 }}" class="text-center" style="padding: 12px;">No se encontraron registros de personal.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="2" class="text-right font-bold" style="padding-right: 4px;">TOTALES GENERALES:</td>
        <td colspan="{{ count($diasMes) }}"></td>
        <td class="text-center font-bold" style="background: #e2e8f0; border-left: 2px solid #000000;">{{ $metricas['gran_total_dias'] }} d</td>
        <td class="text-right font-bold" style="background: #cbd5e1; padding-right: 3px;">Bs. {{ number_format($metricas['gran_total_monto'], 2) }}</td>
        <td class="text-center font-bold" style="background: #dcfce7;">{{ $metricas['gran_total_dias_pagados'] ?? 0 }} d</td>
        <td class="text-right font-bold" style="background: #bbf7d0; padding-right: 3px;">Bs. {{ number_format($metricas['gran_total_monto_pagado'] ?? 0, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- Sección SIMBOLOGÍA para lectura en blanco y negro / tóner --}}
  <div class="legend-box">
    <div class="legend-banner">SIMBOLOGÍA Y CONVENCIONES PARA IMPRESIÓN (ALTO CONTRASTE / TÓNER)</div>
    <table class="legend-table">
      <tr>
        <td>
          <span class="legend-badge cell-a-asistencia">A</span>
          <span class="legend-title">Asistencia</span>
          <div class="legend-desc">Jornada normal asistida. Celda limpia sin recargo de tóner.</div>
        </td>
        <td>
          <span class="legend-badge cell-f">F</span>
          <span class="legend-title">Falta</span>
          <div class="legend-desc">Inasistencia injustificada. 1 día no pagado. Fondo negro invertido.</div>
        </td>
        <td>
          <span class="legend-badge cell-oe">Oe</span>
          <span class="legend-badge cell-os">Os</span>
          <span class="legend-title">Omisión</span>
          <div class="legend-desc">Oe = sin entrada; Os = sin salida. Una boleta aprobada elimina la omisión.</div>
        </td>
        <td>
          <span class="legend-badge cell-bm">Bm</span>
          <span class="legend-title">Baja médica</span>
          <div class="legend-desc">Reposo o incapacidad médica. 1 día no pagado. Doble línea.</div>
        </td>
        <td>
          <span class="legend-badge cell-cv">Cv</span>
          <span class="legend-title">Comisión viaje</span>
          <div class="legend-desc">Comisión o viaje laboral. 1 día no pagado. Borde discontinuo.</div>
        </td>
        <td>
          <span class="legend-badge cell-fe">Fe</span>
          <span class="legend-title">Feriado / asueto</span>
          <div class="legend-desc">Día no laborable. No genera pago de refrigerio.</div>
        </td>
        <td style="border-right: none;">
          <span class="legend-badge cell-perm">Px</span>
          <span class="legend-title">Permiso</span>
          <div class="legend-desc">Cualquier permiso autorizado. 1 día no pagado.</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- Firmas institucionales --}}
  <table class="signatures">
    <tr>
      <td>
        <div class="sign-line">
          ELABORADO POR<br>
          <span class="sign-sub">Encargado de Asistencia y Planillas</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          REVISADO POR<br>
          <span class="sign-sub">Jefatura de Recursos Humanos</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          APROBADO POR<br>
          <span class="sign-sub">Dirección Administrativa Financiera</span>
        </div>
      </td>
    </tr>
  </table>

  <div class="footer-note">
    Nota: Planilla oficial de control de asistencia para descuento de refrigerio institucional · Válida para fines administrativos y auditoría interna · Emisión: {{ $emision }}
  </div>

</body>
</html>
