<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Control de Asistencia del Personal - {{ $periodoLabel }}</title>
  <style>
    @page {
      margin: 12px 14px 14px 14px;
      size: a4 landscape;
    }
    body {
      font-family: DejaVu Sans, sans-serif;
      color: #0f172a;
      font-size: 7px;
      line-height: 1.2;
      margin: 0;
      padding: 0;
    }
    .header-banner {
      width: 100%;
      background-color: #0f4c81;
      color: #ffffff;
      padding: 6px 10px;
      border-radius: 4px;
      margin-bottom: 6px;
    }
    .banner-title {
      font-size: 11px;
      font-weight: bold;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }
    .banner-badge {
      float: right;
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.4);
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 7.5px;
      font-weight: bold;
    }

    /* Tabla principal */
    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .matrix-table th {
      background-color: #e2e8f0;
      color: #1e293b;
      padding: 2px 1px;
      font-size: 6.5px;
      font-weight: bold;
      border: 1px solid #cbd5e1;
      text-align: center;
      vertical-align: middle;
    }
    .matrix-table td {
      padding: 2px 1px;
      border: 1px solid #cbd5e1;
      font-size: 6.5px;
      text-align: center;
      vertical-align: middle;
    }
    .cell-p { background-color: #dcfce7; color: #15803d; font-weight: bold; }
    .cell-f { background-color: #fee2e2; color: #991b1b; font-weight: bold; }
    .cell-o { background-color: #fef3c7; color: #9a3412; font-weight: bold; }
    .cell-bm { background-color: #dbeafe; color: #1d4ed8; font-weight: bold; }
    .cell-cv { background-color: #ede9fe; color: #6d28d9; font-weight: bold; }

    .total-row td {
      background: #f1f5f9;
      font-weight: bold;
      font-size: 7px;
      border-top: 1.5px solid #0f4c81;
      border-bottom: 1.5px solid #0f4c81;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: bold; }

    /* Simbología */
    .legend-banner {
      background-color: #0f4c81;
      color: #ffffff;
      padding: 3px 8px;
      font-size: 7.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-radius: 3px 3px 0 0;
    }
    .legend-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      margin-bottom: 8px;
    }
    .legend-table td {
      width: 20%;
      padding: 4px 6px;
      border-right: 1px solid #cbd5e1;
      vertical-align: top;
    }
    .legend-badge {
      display: inline-block;
      width: 14px;
      height: 14px;
      line-height: 14px;
      text-align: center;
      border-radius: 3px;
      font-weight: bold;
      font-size: 7.5px;
      margin-right: 4px;
    }
    .legend-title {
      font-weight: bold;
      font-size: 7px;
      color: #0f172a;
    }
    .legend-desc {
      font-size: 6px;
      color: #475569;
      margin-top: 2px;
      line-height: 1.2;
    }

    /* Firmas */
    .signatures {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    .signatures td {
      width: 33.33%;
      text-align: center;
      font-size: 7px;
      padding: 0 15px;
    }
    .sign-line {
      border-top: 1px solid #0f172a;
      padding-top: 3px;
      font-weight: bold;
    }
    .footer-note {
      margin-top: 6px;
      font-size: 6px;
      color: #64748b;
      text-align: right;
    }
  </style>
</head>
<body>

  {{-- Cabecera idéntica a la imagen --}}
  <table class="header-banner">
    <tr>
      <td style="vertical-align: middle;">
        <div class="banner-title">CONTROL DE ASISTENCIA DEL PERSONAL</div>
        <div style="font-size: 6.5px; opacity: 0.9; margin-top: 1px;">
          Planilla de Refrigerio / Comida · Sucursal: {{ $sucursalLabel }} · Tarifa: Bs. {{ number_format($tarifaDiaria, 2) }}/día
        </div>
      </td>
      <td style="text-align: right; vertical-align: middle;">
        <span class="banner-badge">Mes: {{ $periodoLabel }}</span>
      </td>
    </tr>
  </table>

  {{-- Tabla de Matriz de Asistencia (Lunes a Viernes) --}}
  <table class="matrix-table">
    <thead>
      <tr>
        <th style="width: 14px;">N°</th>
        <th class="text-left" style="width: 125px; padding-left: 4px;">Nombre y Apellido</th>

        @foreach($diasMes as $dia)
          <th style="width: 19px; background-color: #e0f2fe;">
            <div>{{ $dia['dia'] }}/{{ substr($dia['fecha_corta'], 3, 2) }}</div>
            <div style="font-size: 6px; color: #334155; margin-top: 1px;">{{ $dia['dia_nombre'] }}</div>
          </th>
        @endforeach

        <th style="width: 28px; background-color: #fde68a; color: #78350f;">Total Días</th>
        <th style="width: 48px; background-color: #fecdd3; color: #9f1239;">A No Pagar (Bs.)</th>
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
              $st = strtolower($diasItem[$dia['fecha']] ?? 'p');
              $cellClass = match($st) {
                'p' => 'cell-p',
                'f' => 'cell-f',
                'o' => 'cell-o',
                'bm' => 'cell-bm',
                'cv' => 'cell-cv',
                default => '',
              };
              $cellCode = match($st) {
                'p' => 'P',
                'f' => 'F',
                'o' => 'O',
                'bm' => 'Bm',
                'cv' => 'Cv',
                default => 'P',
              };
            @endphp
            <td class="{{ $cellClass }}">{{ $cellCode }}</td>
          @endforeach

          <td class="font-bold" style="background: {{ $hasDiscount ? '#fef3c7' : 'transparent' }};">
            {{ $totalDias }} d
          </td>
          <td class="text-right font-bold" style="background: {{ $hasDiscount ? '#fee2e2' : 'transparent' }}; padding-right: 3px;">
            Bs. {{ number_format($item['total_monto'] ?? 0, 2) }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ count($diasMes) + 4 }}" class="text-center" style="padding: 12px;">No se encontraron registros de personal.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="2" class="text-right font-bold" style="padding-right: 4px;">TOTALES:</td>
        <td colspan="{{ count($diasMes) }}"></td>
        <td class="text-center" style="background: #fde68a;">{{ $metricas['gran_total_dias'] }} d</td>
        <td class="text-right font-bold" style="background: #fecdd3; padding-right: 3px;">Bs. {{ number_format($metricas['gran_total_monto'], 2) }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- Sección SIMBOLOGÍA según la imagen --}}
  <div class="legend-banner">SIMBOLOGÍA</div>
  <table class="legend-table">
    <tr>
      <td>
        <span class="legend-badge cell-p">P</span>
        <span class="legend-title">Presente</span>
        <div class="legend-desc">El colaborador realizó su jornada laboral.</div>
      </td>
      <td>
        <span class="legend-badge cell-f">F</span>
        <span class="legend-title">Falta</span>
        <div class="legend-desc">No asistió a su jornada.</div>
      </td>
      <td>
        <span class="legend-badge cell-o">O</span>
        <span class="legend-title">Omisión</span>
        <div class="legend-desc">No registró entrada o salida.</div>
      </td>
      <td>
        <span class="legend-badge cell-bm">Bm</span>
        <span class="legend-title">Baja médica</span>
        <div class="legend-desc">Incapacidad médica o reposo.</div>
      </td>
      <td style="border-right: none;">
        <span class="legend-badge cell-cv">Cv</span>
        <span class="legend-title">Comisión de viaje</span>
        <div class="legend-desc">En comisión de trabajo o viaje laboral.</div>
      </td>
    </tr>
  </table>

  {{-- Firmas --}}
  <table class="signatures">
    <tr>
      <td>
        <div class="sign-line">
          ELABORADO POR<br>
          <span style="font-weight: normal; color: #475569; font-size: 6px;">Encargado de Asistencia y Planillas</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          REVISADO POR<br>
          <span style="font-weight: normal; color: #475569; font-size: 6px;">Jefatura de Recursos Humanos</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          APROBADO POR<br>
          <span style="font-weight: normal; color: #475569; font-size: 6px;">Dirección Administrativa Financiera</span>
        </div>
      </td>
    </tr>
  </table>

  <div class="footer-note">
    Nota: La presente planilla es el control de asistencia para el cálculo y descuento de refrigerio / comida del personal institucional · Emisión: {{ $emision }}
  </div>

</body>
</html>
