<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Detalle individual de asistencia - {{ $detailEmployeeReport['empleado']['nombre'] ?? 'Personal' }}</title>
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
        font-size: 17px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .meta-box {
        text-align: right;
        font-size: 9px;
        color: #475569;
      }

      /* Ficha del empleado */
      .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .info-cell {
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        background: #f8fafc;
        width: 25%;
      }
      .info-label {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
        font-weight: bold;
      }
      .info-value {
        font-size: 11px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 2px;
      }

      /* Métricas resumen */
      .metrics-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
      }
      .metric-cell {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        text-align: center;
        background: #ffffff;
      }
      .metric-label {
        font-size: 8px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: bold;
      }
      .metric-value {
        font-size: 14px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }

      /* Tablas de detalle */
      .section-header {
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        color: #0f172a;
        border-bottom: 1.5px solid #64748b;
        padding-bottom: 4px;
        margin-top: 14px;
        margin-bottom: 6px;
      }
      table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .data-table th, .data-table td {
        border: 1px solid #cbd5e1;
        padding: 5px 8px;
        text-align: left;
        vertical-align: middle;
      }
      .data-table th {
        background: #f8fafc;
        color: #334155;
        font-size: 8px;
        text-transform: uppercase;
        font-weight: bold;
      }
      .text-center { text-align: center !important; }
      .text-right { text-align: right !important; }
      .font-mono { font-family: monospace; }
      .font-bold { font-weight: bold; }
      .empty-row td {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        padding: 8px;
      }
      .badge-omision {
        font-weight: bold;
        color: #000000;
        background: #e2e8f0;
        border: 1px solid #0f172a;
        padding: 1.5px 5px;
        border-radius: 2px;
        display: inline-block;
      }

      .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #0f172a;
        font-size: 8px;
        color: #475569;
        text-align: right;
      }
    </style>
  </head>
  <body>

    <!-- Encabezado -->
    <table class="header-table">
      <tr>
        <td style="width: 70%;">
          <p class="org-name">Correos de Bolivia · Recursos Humanos</p>
          <h1 class="report-title">Detalle Individual de Asistencia</h1>
          <p style="font-size: 9.5px; color: #475569; margin-top: 2px;">Informe mensual individual con registro de atrasos y omisiones</p>
        </td>
        <td class="meta-box" style="width: 30%;">
          <p><strong>Periodo:</strong> {{ $monthLabel }}</p>
          <p><strong>Fecha emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </td>
      </tr>
    </table>

    <!-- Ficha de Empleado -->
    <table class="info-table">
      <tr>
        <td class="info-cell">
          <div class="info-label">Funcionario</div>
          <div class="info-value">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Sin nombre' }}</div>
        </td>
        <td class="info-cell">
          <div class="info-label">Código / CI</div>
          <div class="info-value font-mono">{{ $detailEmployeeReport['empleado']['codigo'] ?? '-' }}</div>
        </td>
        <td class="info-cell">
          <div class="info-label">Sucursal</div>
          <div class="info-value">{{ $detailEmployeeReport['empleado']['sucursal'] ?? '-' }}</div>
        </td>
        <td class="info-cell">
          <div class="info-label">Horario Asignado</div>
          <div class="info-value">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</div>
        </td>
      </tr>
    </table>

    <!-- Métricas Resumen -->
    <table class="metrics-table">
      <tr>
        @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
          <td class="metric-cell">
            <div class="metric-label">{{ $metric['label'] }}</div>
            <div class="metric-value">{{ $metric['value'] }}</div>
          </td>
        @endforeach
      </tr>
    </table>

    @if(!empty($evaluacionReglamento))
      <!-- Control y Liquidación Reglamentaria (Art. 45 y Art. 48) -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; border: 1.5px solid {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#e11d48' : '#10b981' }};">
        <tr style="background-color: {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#fff1f2' : '#f0fdf4' }};">
          <td style="padding: 7px 10px; border-bottom: 1px solid {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#fecdd3' : '#bbf7d0' }};" colspan="4">
            <div style="font-size: 10px; font-weight: bold; color: {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#9f1239' : '#166534' }};">
              CONTROL DE CUMPLIMIENTO REGLAMENTARIO Y DESCUENTOS APLICABLES (ART. 45 Y ART. 48)
            </div>
            <div style="font-size: 8px; color: #475569; margin-top: 2px;">
              Evaluación institucional de puntualidad, asistencia y escala formal de sanciones del Reglamento Interno de Personal.
            </div>
          </td>
        </tr>
        <tr>
          <td style="padding: 6px 10px; width: 25%; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; text-align: center; background: #ffffff;">
            <div style="font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">Atraso Acumulado</div>
            <div style="font-size: 13px; font-weight: bold; color: {{ $evaluacionReglamento['minutos_atraso'] > 30 ? '#be123c' : '#0f172a' }}; margin-top: 2px;">
              {{ $evaluacionReglamento['minutos_atraso'] }} min
            </div>
            <div style="font-size: 7.5px; color: #64748b;">(Tolerancia: hasta 30 min)</div>
          </td>
          <td style="padding: 6px 10px; width: 25%; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; text-align: center; background: #ffffff;">
            <div style="font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">Días que Llegó Tarde</div>
            <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 2px;">
              {{ $evaluacionReglamento['dias_tarde'] }} días
            </div>
            <div style="font-size: 7.5px; color: #64748b;">En el mes de evaluación</div>
          </td>
          <td style="padding: 6px 10px; width: 25%; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; text-align: center; background: #ffffff;">
            <div style="font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold;">Omisiones / Faltas</div>
            <div style="font-size: 13px; font-weight: bold; color: {{ $evaluacionReglamento['omisiones_count'] > 0 ? '#be123c' : '#0f172a' }}; margin-top: 2px;">
              {{ $evaluacionReglamento['omisiones_count'] }}
            </div>
            <div style="font-size: 7.5px; color: #64748b;">Entrada/Salida sin registro</div>
          </td>
          <td style="padding: 6px 10px; width: 25%; border-bottom: 1px solid #cbd5e1; text-align: center; background: {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#fff1f2' : '#f0fdf4' }};">
            <div style="font-size: 8px; color: {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#9f1239' : '#166534' }}; text-transform: uppercase; font-weight: bold;">Total Días a Descontar</div>
            <div style="font-size: 13px; font-weight: bold; color: {{ $evaluacionReglamento['total_dias_descuento'] > 0 ? '#be123c' : '#166534' }}; margin-top: 2px;">
              {{ $evaluacionReglamento['total_dias_descuento_texto'] }}
            </div>
            <div style="font-size: 7.5px; font-weight: bold; color: {{ $evaluacionReglamento['se_pasa_reglamento'] ? '#be123c' : '#166534' }};">
              {{ $evaluacionReglamento['se_pasa_reglamento'] ? 'EXCEDE REGLAMENTO' : 'DENTRO DE TOLERANCIA' }}
            </div>
          </td>
        </tr>
        @if(!empty($evaluacionReglamento['desglose_sanciones']))
          <tr>
            <td colspan="4" style="padding: 6px 10px; background: #fff5f5; font-size: 8px; color: #881337;">
              <strong>Detalle de liquidación de descuentos:</strong>
              @foreach($evaluacionReglamento['desglose_sanciones'] as $sancion)
                <span style="display: inline-block; margin-right: 10px; font-weight: bold;">• {{ $sancion }}</span>
              @endforeach
            </td>
          </tr>
        @endif
      </table>
    @endif

    <!-- Tabla 1: Tardanzas -->
    <div class="section-header">Registro de Tardanzas / Llegadas Tarde</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 25px;" class="text-center">#</th>
          <th style="width: 90px;">Fecha</th>
          <th style="width: 80px;" class="text-center">Entrada Marcada</th>
          <th style="width: 80px;" class="text-center">Salida Marcada</th>
          <th style="width: 100px;" class="text-center">Retraso</th>
          <th class="text-center">Estado</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $i => $item)
          <tr>
            <td class="text-center" style="color: #475569;">{{ $i + 1 }}</td>
            <td class="font-bold">{{ $item['fecha'] }}</td>
            <td class="text-center font-bold">{{ $item['entrada'] }}</td>
            <td class="text-center">{{ $item['salida'] }}</td>
            <td class="text-center font-bold" style="color: #0f172a;">{{ $item['retraso_minutos'] ?? (int)$item['retraso'] }} min</td>
            <td class="text-center">{{ $item['estado'] }}</td>
          </tr>
        @empty
          <tr class="empty-row">
            <td colspan="6">No tiene tardanzas registradas en este periodo.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Tabla 2: Omisiones (olvidos de marcar o ausencias sin justificar) -->
    @php
      $omisionesCombinadas = array_merge(
        $detailEmployeeReport['no_marcados'] ?? [],
        $detailEmployeeReport['faltas'] ?? []
      );
    @endphp

    <div class="section-header">Registro de Omisiones (Olvidos de Marcación y Días sin Marcar)</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 25px;" class="text-center">#</th>
          <th style="width: 90px;">Fecha</th>
          <th style="width: 80px;" class="text-center">Entrada</th>
          <th style="width: 80px;" class="text-center">Salida</th>
          <th style="width: 90px;" class="text-center">Estado</th>
          <th>Observación / Detalle</th>
        </tr>
      </thead>
      <tbody>
        @forelse($omisionesCombinadas as $i => $item)
          <tr>
            <td class="text-center" style="color: #475569;">{{ $i + 1 }}</td>
            <td class="font-bold">{{ $item['fecha'] }}</td>
            <td class="text-center">
              @if(blank($item['entrada'] ?? '') || ($item['entrada'] ?? '') === '--:--')
                <span style="font-weight: bold; color: #000; background: #e2e8f0; padding: 1px 3px; border: 1px solid #475569; font-size: 8px;">[Sin marcar]</span>
              @else
                <span class="font-mono font-bold">{{ $item['entrada'] }}</span>
              @endif
            </td>
            <td class="text-center">
              @if(blank($item['salida'] ?? '') || ($item['salida'] ?? '') === '--:--')
                <span style="font-weight: bold; color: #000; background: #e2e8f0; padding: 1px 3px; border: 1px solid #475569; font-size: 8px;">[Sin marcar]</span>
              @else
                <span class="font-mono font-bold">{{ $item['salida'] }}</span>
              @endif
            </td>
            <td class="text-center"><span class="badge-omision">Omisión</span></td>
            <td><strong>{{ $item['detalle'] ?? 'Marcación incompleta o día sin registro' }}</strong></td>
          </tr>
        @empty
          <tr class="empty-row">
            <td colspan="6">No registra omisiones en este periodo.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="footer">
      Documento de control individual · Correos de Bolivia
    </div>

  </body>
</html>
