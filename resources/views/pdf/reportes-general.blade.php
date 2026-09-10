<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte general de asistencia</title>
    <style>
      @page {
        margin: 20mm 15mm 20mm 15mm;
        size: A4 portrait;
      }
      body {
        font-family: DejaVu Sans, sans-serif;
        color: #1e293b;
        margin: 0;
        padding: 0;
        font-size: 10px;
        line-height: 1.4;
      }
      h1, h2, h3, h4, p { margin: 0; }
      
      /* Encabezado formal */
      .header-table {
        width: 100%;
        border-bottom: 2px solid #334155;
        padding-bottom: 8px;
        margin-bottom: 12px;
      }
      .org-name {
        font-size: 9px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .report-title {
        font-size: 18px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .report-subtitle {
        font-size: 10px;
        color: #475569;
        margin-top: 2px;
      }
      .meta-box {
        text-align: right;
        font-size: 9px;
        color: #475569;
      }
      .meta-box strong {
        color: #0f172a;
      }

      /* Resumen Ejecutivo Superior */
      .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
      }
      .summary-card {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        text-align: center;
        background: #f8fafc;
      }
      .summary-label {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
        font-weight: bold;
      }
      .summary-value {
        font-size: 14px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .summary-subtext {
        font-size: 8px;
        color: #64748b;
        margin-top: 1px;
      }

      /* Bloques por Sucursal */
      .branch-block {
        margin-top: 14px;
        page-break-inside: avoid;
      }
      .branch-header {
        background: #f1f5f9;
        border: 1px solid #94a3b8;
        border-left: 4px solid #0f172a;
        padding: 6px 10px;
        margin-bottom: 4px;
      }
      .branch-header-title {
        font-size: 12px;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
        display: inline-block;
      }
      .branch-header-meta {
        float: right;
        font-size: 9px;
        color: #475569;
      }

      /* Tablas de datos sobrias para impresión */
      table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
      }
      .data-table th, .data-table td {
        border: 1px solid #cbd5e1;
        padding: 5px 6px;
        text-align: left;
        vertical-align: middle;
      }
      .data-table th {
        background: #f8fafc;
        color: #334155;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: bold;
      }
      .text-center { text-align: center !important; }
      .text-right { text-align: right !important; }
      .font-mono { font-family: monospace; }
      .font-bold { font-weight: bold; }
      .badge-omision {
        font-weight: bold;
        color: #b91c1c;
      }
      .subtotal-row td {
        background: #f8fafc;
        font-weight: bold;
        border-top: 1.5px solid #64748b;
        border-bottom: 1.5px solid #64748b;
      }
      .empty-row td {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 10px;
      }

      /* Pie de página */
      .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #cbd5e1;
        font-size: 8px;
        color: #94a3b8;
        text-align: right;
      }
    </style>
  </head>
  <body>

    <!-- Encabezado -->
    <table class="header-table">
      <tr>
        <td style="width: 70%;">
          <p class="org-name">Agencia Boliviana de Correos · Recursos Humanos</p>
          <h1 class="report-title">Reporte de Asistencia y Puntualidad</h1>
          <p class="report-subtitle">Resumen consolidado por sucursal · Total de atrasos en minutos y control de omisiones</p>
        </td>
        <td class="meta-box" style="width: 30%;">
          <p><strong>Periodo:</strong> {{ $monthLabel }}</p>
          <p><strong>Filtro:</strong> {{ $branchLabel }}</p>
          <p><strong>Fecha emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </td>
      </tr>
    </table>

    <!-- Resumen Ejecutivo Superior -->
    <table class="summary-table">
      <tr>
        <td class="summary-card" style="width: 25%;">
          <div class="summary-label">Personal evaluado</div>
          <div class="summary-value">{{ $reporteSucursales['total_empleados'] ?? 0 }}</div>
          <div class="summary-subtext">Activos en el periodo</div>
        </td>
        <td class="summary-card" style="width: 25%;">
          <div class="summary-label">Atrasos acumulados</div>
          <div class="summary-value">{{ number_format($reporteSucursales['total_minutos_atraso'] ?? 0) }} min</div>
          <div class="summary-subtext">{{ $reporteSucursales['total_minutos_formato'] ?? '0 min' }}</div>
        </td>
        <td class="summary-card" style="width: 25%;">
          <div class="summary-label">Días con atraso</div>
          <div class="summary-value">{{ $reporteSucursales['total_dias_atraso'] ?? 0 }}</div>
          <div class="summary-subtext">Incidencias de llegada tardía</div>
        </td>
        <td class="summary-card" style="width: 25%;">
          <div class="summary-label">Total Omisiones</div>
          <div class="summary-value">{{ $reporteSucursales['total_omisiones'] ?? 0 }}</div>
          <div class="summary-subtext">Días no marcados y sin marcar</div>
        </td>
      </tr>
    </table>

    <!-- Listado Agrupado por Sucursales con títulos -->
    @php
      $sucursalesList = $reporteSucursales['sucursales'] ?? [];
    @endphp

    @forelse($sucursalesList as $sucursal)
      <div class="branch-block">
        <div class="branch-header">
          <span class="branch-header-title">{{ strtoupper($sucursal['sucursal']) }}</span>
          <span class="branch-header-meta">
            {{ $sucursal['total_empleados'] }} empleados &bull; 
            Atrasos: <strong>{{ number_format($sucursal['total_minutos_atraso']) }} min</strong> ({{ $sucursal['total_minutos_formato'] }}) &bull; 
            Omisiones: <strong>{{ $sucursal['total_omisiones'] }}</strong>
          </span>
          <div style="clear: both;"></div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 25px;" class="text-center">#</th>
              <th style="width: 65px;">Código / CI</th>
              <th>Apellidos y Nombres</th>
              <th style="width: 110px;">Área / Cargo</th>
              <th style="width: 85px;" class="text-center">Atraso total</th>
              <th style="width: 60px;" class="text-center">Días tarde</th>
              <th style="width: 65px;" class="text-center">Omisiones</th>
              <th style="width: 70px;" class="text-center">Asistencia</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sucursal['empleados'] as $idx => $emp)
              <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="font-mono">{{ $emp['codigo'] ?: '-' }}</td>
                <td class="font-bold">{{ $emp['nombre'] }}</td>
                <td>{{ $emp['area'] }}</td>
                <td class="text-center">
                  @if($emp['minutos_atraso'] > 0)
                    <strong>{{ $emp['minutos_atraso'] }} min</strong>
                    <div style="font-size: 7.5px; color: #64748b;">({{ $emp['minutos_atraso_formato'] }})</div>
                  @else
                    <span style="color: #64748b;">0 min</span>
                  @endif
                </td>
                <td class="text-center">{{ $emp['dias_atraso'] }}</td>
                <td class="text-center">
                  @if($emp['omisiones'] > 0)
                    <span class="badge-omision">{{ $emp['omisiones'] }}</span>
                  @else
                    <span style="color: #64748b;">0</span>
                  @endif
                </td>
                <td class="text-center" style="font-size: 8px;">
                  {{ $emp['dias_asistidos'] }} / {{ $emp['dias_laborables'] }}
                </td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="8">No se registraron empleados activos en esta sucursal.</td>
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
      Documento institucional oficial · Impresión optimizada para archivo y supervisión · Correos de Bolivia
    </div>

  </body>
</html>
