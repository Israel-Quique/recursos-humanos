<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte de Marcaciones por Sucursal - {{ $sucursalLabel ?? 'General' }}</title>
    <style>
      @page {
        margin: 16px 18px;
        size: letter portrait;
      }
      body {
        font-family: 'DejaVu Sans', sans-serif;
        color: #1e293b;
        font-size: 7.5px;
        line-height: 1.25;
        margin: 0;
        padding: 0;
      }
      h1, h2, h3, h4, p {
        margin: 0;
        padding: 0;
      }
      .header-table {
        width: 100%;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 4px;
        margin-bottom: 6px;
      }
      .kicker {
        font-size: 7px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .title {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 1px;
      }
      .meta-right {
        text-align: right;
        font-size: 7.5px;
        color: #475569;
      }
      .info-box {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: #f8fafc;
        margin-bottom: 8px;
        border-collapse: collapse;
      }
      .info-box td {
        padding: 3.5px 6px;
        vertical-align: top;
        font-size: 7.5px;
      }
      .info-label {
        font-size: 6.5px;
        text-transform: uppercase;
        font-weight: bold;
        color: #64748b;
        display: block;
        margin-bottom: 1px;
      }
      .info-val {
        font-weight: 600;
        color: #0f172a;
      }
      .sucursal-block {
        margin-bottom: 10px;
        page-break-inside: auto;
      }
      .sucursal-banner {
        width: 100%;
        background-color: #0f172a;
        color: #ffffff;
        border-collapse: collapse;
        margin-top: 5px;
      }
      .sucursal-banner td {
        padding: 4px 7px;
        vertical-align: middle;
      }
      .sucursal-title {
        font-size: 9.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #ffffff;
      }
      .sucursal-meta {
        text-align: right;
        font-size: 7.5px;
        color: #e2e8f0;
      }
      .sucursal-meta strong {
        color: #ffffff;
      }
      .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
      }
      .data-table thead {
        display: table-header-group;
      }
      .data-table tr {
        page-break-inside: avoid;
      }
      .data-table th {
        background-color: #1e293b;
        color: #ffffff;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 4px 3px;
        font-weight: bold;
        border: 1px solid #1e293b;
        text-align: center;
      }
      .data-table th.text-left {
        text-align: left;
        padding-left: 5px;
      }
      .data-table td {
        padding: 3px 3px;
        border: 1px solid #e2e8f0;
        font-size: 7px;
        text-align: center;
        vertical-align: middle;
      }
      .data-table td.text-left {
        text-align: left;
        padding-left: 5px;
      }
      .data-table tr:nth-child(even) td {
        background-color: #f8fafc;
      }
      .dia-sub {
        font-size: 6px;
        color: #64748b;
        text-transform: capitalize;
        display: block;
        margin-top: 1px;
      }
      .cod-sub {
        font-size: 6.5px;
        color: #475569;
        font-weight: normal;
        display: block;
        margin-top: 1px;
      }
      .badge {
        display: inline-block;
        padding: 1.5px 4px;
        border-radius: 3px;
        font-size: 6.5px;
        font-weight: bold;
        white-space: nowrap;
      }
      .badge-success {
        background-color: #dcfce7;
        color: #166534;
      }
      .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
      }
      .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        font-weight: 800;
        letter-spacing: 0.03em;
      }
      .badge-secondary {
        background-color: #f1f5f9;
        color: #475569;
      }
      .signatures {
        margin-top: 18px;
        width: 100%;
        page-break-inside: avoid;
      }
      .signatures td {
        width: 50%;
        text-align: center;
        padding: 0 30px;
      }
      .sign-line {
        border-top: 1px solid #64748b;
        margin-top: 30px;
        padding-top: 3px;
        font-weight: bold;
        font-size: 7.5px;
      }
      .sign-title {
        font-size: 6.5px;
        color: #64748b;
      }
    </style>
  </head>
  <body>
    <table class="header-table">
      <tr>
        <td>
          <p class="kicker">Empresa de Correos de Bolivia - RRHH</p>
          <h1 class="title">Registro de Marcaciones por Sucursal</h1>
        </td>
        <td class="meta-right">
          <p><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
          <p><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
        </td>
      </tr>
    </table>

    <table class="info-box">
      <tr>
        <td style="width: 25%;">
          <span class="info-label">Sucursal / Regional</span>
          <span class="info-val">{{ $sucursalLabel }}</span>
        </td>
        <td style="width: 25%;">
          <span class="info-label">Período Seleccionado</span>
          <span class="info-val">{{ $periodoLabel }}</span>
        </td>
        <td style="width: 25%;">
          <span class="info-label">Total Marcaciones</span>
          <span class="info-val">{{ $stats['total_marcaciones'] ?? count($registros) }} registros</span>
        </td>
        <td style="width: 25%;">
          <span class="info-label">Puntualidad</span>
          <span class="info-val">{{ $stats['total_puntuales'] ?? 0 }} ({{ $stats['pct_puntual'] ?? 0 }}%)</span>
        </td>
      </tr>
      <tr>
        <td>
          <span class="info-label">Con Retraso</span>
          <span class="info-val">{{ $stats['total_retrasos'] ?? 0 }} ({{ $stats['total_minutos_retraso'] ?? 0 }} min acum.)</span>
        </td>
        <td>
          <span class="info-label">Omisiones</span>
          <span class="info-val">{{ $stats['total_omisiones'] ?? 0 }} (sin entrada/salida)</span>
        </td>
        <td>
          <span class="info-label">Faltas Registradas</span>
          <span class="info-val" style="color: {{ ($stats['total_faltas'] ?? 0) > 0 ? '#b91c1c' : '#0f172a' }}; font-weight: bold;">
            {{ $stats['total_faltas'] ?? 0 }}
          </span>
        </td>
        <td>
          <span class="info-label">Personal Registrado</span>
          <span class="info-val">{{ $stats['total_empleados_unicos'] ?? 0 }} colaboradores</span>
        </td>
      </tr>
    </table>

    @forelse ($gruposPorSucursal as $nombreSucursal => $registrosSucursal)
      <div class="sucursal-block">
        {{-- Encabezado agrupado por sucursal con horario programado --}}
        <table class="sucursal-banner">
          <tr>
            <td style="width: 50%;">
              <span class="sucursal-title">SUCURSAL: {{ strtoupper($nombreSucursal) }}</span>
            </td>
            <td class="sucursal-meta" style="width: 50%;">
              <span><strong>Horario:</strong> {{ $registrosSucursal->pluck('horario_programado')->filter()->unique()->implode(', ') ?: '08:30 - 16:30' }}</span>
              &nbsp;&bull;&nbsp;
              <span><strong>Total:</strong> {{ count($registrosSucursal) }} registros</span>
            </td>
          </tr>
        </table>

        {{-- Tabla de datos de la sucursal (8 columnas ajustadas para formato vertical) --}}
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 12%;">Fecha</th>
              <th class="text-left" style="width: 28%;">Personal</th>
              <th style="width: 8%;">Entrada</th>
              <th style="width: 8%;">Salida</th>
              <th style="width: 9%;">Horas Trab.</th>
              <th style="width: 10%;">Retraso</th>
              <th style="width: 14%;">Omisiones</th>
              <th style="width: 11%;">Faltas</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($registrosSucursal as $row)
              <tr>
                <td>
                  <strong>{{ $row->fecha_formateada }}</strong>
                  <span class="dia-sub">{{ ucfirst($row->dia ?? '') }}</span>
                </td>
                <td class="text-left">
                  <strong>{{ $row->empleado?->nombre_completo ?? 'N/D' }}</strong>
                  <span class="cod-sub">Cód. {{ $row->codigo }}</span>
                </td>
                <td>
                  <strong>{{ $row->hora_entrada }}</strong>
                </td>
                <td>
                  <strong>{{ $row->hora_salida }}</strong>
                </td>
                <td>{{ $row->horas_trabajadas }}</td>
                <td>
                  @if(($row->minutos_retraso ?? 0) > 0)
                    <span style="color: #b91c1c; font-weight: bold;">+{{ $row->minutos_retraso }}m</span>
                  @elseif($row->hora_entrada !== '--:--')
                    <span style="color: #15803d; font-weight: 600;">0m</span>
                  @else
                    <span style="color: #94a3b8;">--</span>
                  @endif
                </td>
                <td>
                  @if($row->tipo_omision === 'sin_salida')
                    <span class="badge badge-warning">Sin Salida</span>
                  @elseif($row->tipo_omision === 'sin_entrada')
                    <span class="badge badge-warning">Sin Entrada</span>
                  @else
                    <span style="color: #94a3b8;">--</span>
                  @endif
                </td>
                <td>
                  @if($row->es_falta)
                    <span class="badge badge-danger">FALTA</span>
                  @else
                    <span style="color: #94a3b8;">--</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" style="padding: 10px; text-align: center; color: #64748b;">
                  No hay registros para esta sucursal.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @empty
      <table class="data-table" style="margin-top: 15px;">
        <tbody>
          <tr>
            <td colspan="8" style="padding: 20px; text-align: center; color: #64748b; font-size: 8.5px;">
              No se encontraron marcaciones para los filtros seleccionados.
            </td>
          </tr>
        </tbody>
      </table>
    @endforelse

    <table class="signatures">
      <tr>
        <td>
          <div class="sign-line">Responsable de Recursos Humanos</div>
          <div class="sign-title">Control y Validación de Asistencia</div>
        </td>
        <td>
          <div class="sign-line">Jefatura / Responsable de Sucursal</div>
          <div class="sign-title">Revisión de Asistencia Regional</div>
        </td>
      </tr>
    </table>
  </body>
</html>
