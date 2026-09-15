<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte General de Asistencia y Puntualidad</title>
    <style>
      @page {
        margin: 12mm 10mm 12mm 10mm;
        size: A4 portrait;
      }
      * {
        box-sizing: border-box;
      }
      body {
        font-family: DejaVu Sans, sans-serif;
        color: #1e293b;
        margin: 0;
        padding: 0;
        font-size: 9px;
        line-height: 1.35;
      }
      h1, h2, h3, h4, p { margin: 0; }
      
      /* Encabezado formal */
      .header-table {
        width: 100%;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 6px;
        margin-bottom: 10px;
      }
      .org-name {
        font-size: 8px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .report-title {
        font-size: 16px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 2px;
      }
      .report-subtitle {
        font-size: 9px;
        color: #475569;
        margin-top: 2px;
      }
      .meta-box {
        text-align: right;
        font-size: 8.5px;
        color: #475569;
        vertical-align: top;
      }
      .meta-box strong {
        color: #0f172a;
      }

      /* Resumen Ejecutivo Superior */
      .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .summary-card {
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        text-align: center;
        background: #f8fafc;
      }
      .summary-label {
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: bold;
      }
      .summary-value {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 2px;
      }
      .summary-subtext {
        font-size: 7.5px;
        color: #64748b;
        margin-top: 1px;
      }

      /* Bloques por Sucursal */
      .branch-block {
        margin-top: 10px;
        margin-bottom: 8px;
      }
      .branch-header-table {
        width: 100%;
        border-collapse: collapse;
        background: #f1f5f9;
        border: 1px solid #94a3b8;
        border-left: 4px solid #0f172a;
        margin-bottom: 3px;
        page-break-after: avoid;
      }
      .branch-header-table td {
        padding: 5px 8px;
        vertical-align: middle;
      }
      .branch-header-title {
        font-size: 10.5px;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
      }
      .branch-header-meta {
        text-align: right;
        font-size: 8px;
        color: #475569;
      }
      .branch-header-meta strong {
        color: #0f172a;
      }

      /* Tablas de datos sobrias para impresión */
      table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
        page-break-inside: auto;
      }
      table.data-table thead {
        display: table-header-group;
      }
      table.data-table tfoot {
        display: table-footer-group;
      }
      table.data-table tr {
        page-break-inside: avoid;
      }
      .data-table th, .data-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 5px;
        text-align: left;
        vertical-align: middle;
        font-size: 8px;
      }
      .data-table th {
        background: #f8fafc;
        color: #334155;
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: bold;
      }
      .text-center { text-align: center !important; }
      .text-right { text-align: right !important; }
      .font-mono { font-family: monospace; }
      .font-bold { font-weight: bold; }
      .badge-omision {
        font-weight: bold;
        color: #000000;
        background-color: #e2e8f0;
        border: 1px solid #0f172a;
        padding: 1px 4px;
        border-radius: 2px;
        display: inline-block;
      }
      .subtotal-row td {
        background: #f8fafc;
        font-weight: bold;
        border-top: 1.5px solid #0f172a;
        border-bottom: 1.5px solid #0f172a;
        font-size: 8px;
        color: #000000;
      }
      .empty-row td {
        text-align: center;
        color: #64748b;
        font-style: italic;
        padding: 8px;
      }

      /* Pie de página */
      .footer {
        margin-top: 14px;
        padding-top: 6px;
        border-top: 1px solid #0f172a;
        font-size: 7.5px;
        color: #475569;
        text-align: right;
      }
    </style>
  </head>
  <body>

    <!-- Encabezado -->
    <table class="header-table">
      <tr>
        <td style="width: 65%;">
          <p class="org-name">Correos de Bolivia · Recursos Humanos</p>
          <h1 class="report-title">Reporte General de Asistencia</h1>
          <p class="report-subtitle">Resumen consolidado por sucursales · Cómputo de puntualidad, atrasos y omisiones</p>
        </td>
        <td class="meta-box" style="width: 35%;">
          <p><strong>Periodo:</strong> {{ $monthLabel }}</p>
          <p><strong>Filtro:</strong> {{ $branchLabel }}</p>
          <p><strong>Fecha emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
          @if(!empty($reporteSucursales['es_mes_en_curso']))
            <p style="font-size: 7.5px; color: #0f172a; font-weight: bold; margin-top: 2px;">
              * Mes en curso: {{ $reporteSucursales['dias_laborables_transcurridos'] ?? 1 }} de {{ $reporteSucursales['dias_laborables_mes'] ?? 22 }} días hábiles a la fecha
            </p>
          @endif
        </td>
      </tr>
    </table>
    <!-- Resumen Ejecutivo Superior -->
    <table class="summary-table">
      <tr>
        <td class="summary-card" style="width: 20%;">
          <div class="summary-label">Personal evaluado</div>
          <div class="summary-value">{{ $reporteSucursales['total_empleados'] ?? 0 }}</div>
          <div class="summary-subtext">Activos en el periodo</div>
        </td>
        <td class="summary-card" style="width: 20%;">
          <div class="summary-label">Atrasos acumulados</div>
          <div class="summary-value">{{ number_format($reporteSucursales['total_minutos_atraso'] ?? 0) }} min</div>
          <div class="summary-subtext">Total acumulado en minutos</div>
        </td>
        <td class="summary-card" style="width: 20%;">
          <div class="summary-label">Días con atraso</div>
          <div class="summary-value">{{ $reporteSucursales['total_dias_atraso'] ?? 0 }}</div>
          <div class="summary-subtext">Llegadas tardías totales</div>
        </td>
        <td class="summary-card" style="width: 20%;">
          <div class="summary-label">Total Omisiones</div>
          <div class="summary-value">{{ $reporteSucursales['total_omisiones'] ?? 0 }}</div>
          <div class="summary-subtext">Solo entrada o salida</div>
        </td>
        <td class="summary-card" style="width: 20%;">
          <div class="summary-label">Total Faltas</div>
          <div class="summary-value">{{ $reporteSucursales['total_faltas'] ?? 0 }}</div>
          <div class="summary-subtext">Días sin marcación</div>
        </td>
      </tr>
    </table>

    <!-- Listado Agrupado por Sucursales con títulos -->
    @php
      $sucursalesList = $reporteSucursales['sucursales'] ?? [];
    @endphp

    @forelse($sucursalesList as $sucursal)
      <div class="branch-block">
        <table class="branch-header-table">
          <tr>
            <td style="width: 40%;">
              <span class="branch-header-title">{{ strtoupper($sucursal['sucursal']) }}</span>
            </td>
            <td class="branch-header-meta" style="width: 60%;">
              <strong>{{ $sucursal['total_empleados'] }}</strong> personal &bull; 
              Atrasos: <strong>{{ number_format($sucursal['total_minutos_atraso']) }} min</strong> &bull; 
              Omisiones: <strong>{{ $sucursal['total_omisiones'] }}</strong> &bull; 
              Faltas: <strong>{{ $sucursal['total_faltas'] ?? 0 }}</strong>
            </td>
          </tr>
        </table>

        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 20px;" class="text-center">#</th>
              <th style="width: 60px;">Código / CI</th>
              <th>Apellidos y Nombres</th>
              <th style="width: 75px;">Sucursal</th>
              <th style="width: 70px;" class="text-center">Atraso total</th>
              <th style="width: 45px;" class="text-center">Días tarde</th>
              <th style="width: 48px;" class="text-center">Omisiones</th>
              <th style="width: 45px;" class="text-center">Faltas</th>
              <th style="width: 78px;" class="text-center">Asistencia</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sucursal['empleados'] as $idx => $emp)
              <tr>
                <td class="text-center" style="color: #475569;">{{ $idx + 1 }}</td>
                <td class="font-mono">{{ $emp['codigo'] ?: '-' }}</td>
                <td class="font-bold">{{ $emp['nombre'] }}</td>
                <td>{{ $emp['sucursal'] }}</td>
                <td class="text-center">
                  @if($emp['minutos_atraso'] > 0)
                    <strong>{{ $emp['minutos_atraso'] }} min</strong>
                  @else
                    <span style="color: #64748b;">0 min</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($emp['dias_atraso'] > 0)
                    <strong>{{ $emp['dias_atraso'] }}</strong>
                  @else
                    <span style="color: #64748b;">0</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($emp['omisiones'] > 0)
                    <span class="badge-omision">{{ $emp['omisiones'] }}</span>
                  @else
                    <span style="color: #64748b;">0</span>
                  @endif
                </td>
                <td class="text-center">
                  @if(($emp['faltas'] ?? 0) > 0)
                    <strong style="color: #b91c1c;">{{ $emp['faltas'] }}</strong>
                  @else
                    <span style="color: #64748b;">0</span>
                  @endif
                </td>
                <td class="text-center" style="font-size: 8px;">
                  <strong>{{ $emp['dias_asistidos'] }} / {{ $emp['dias_laborables_transcurridos'] }}</strong>
                  <div style="font-size: 7.5px; font-weight: bold; color: #0f172a;">
                    {{ $emp['porcentaje_asistencia'] }}%
                    @if(!empty($emp['es_mes_en_curso']))
                      <span style="font-size: 6.5px; color: #475569; font-weight: normal;">(de {{ $emp['dias_laborables_mes'] }} d.)</span>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="9">No se registraron empleados activos en esta sucursal.</td>
              </tr>
            @endforelse

            <!-- Fila de subtotal por sucursal -->
            @if(count($sucursal['empleados']) > 0)
              <tr class="subtotal-row">
                <td colspan="4" class="text-right">SUBTOTAL {{ strtoupper($sucursal['sucursal']) }}:</td>
                <td class="text-center">
                  {{ number_format($sucursal['total_minutos_atraso']) }} min
                </td>
                <td class="text-center">{{ $sucursal['total_dias_atraso'] }}</td>
                <td class="text-center">{{ $sucursal['total_omisiones'] }}</td>
                <td class="text-center">{{ $sucursal['total_faltas'] ?? 0 }}</td>
                <td class="text-center">-</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    @empty
      <p style="text-align: center; color: #94a3b8; padding: 20px;">No se encontraron registros de sucursales para el periodo seleccionado.</p>
    @endforelse

    <div class="footer">
      Documento institucional oficial · Cómputo verificado por Recursos Humanos · Correos de Bolivia
    </div>

  </body>
</html>
