<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte de Marcaciones por Sucursal - {{ $sucursalLabel ?? 'General' }}</title>
    <style>
      @page {
        margin: 20px 24px;
        size: letter landscape;
      }
      body {
        font-family: 'DejaVu Sans', sans-serif;
        color: #1e293b;
        font-size: 9px;
        line-height: 1.3;
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
        padding-bottom: 6px;
        margin-bottom: 10px;
      }
      .kicker {
        font-size: 8px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .title {
        font-size: 15px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 2px;
      }
      .meta-right {
        text-align: right;
        font-size: 8.5px;
        color: #475569;
      }
      .info-box {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: #f8fafc;
        margin-bottom: 10px;
        border-collapse: collapse;
      }
      .info-box td {
        padding: 5px 8px;
        vertical-align: top;
        font-size: 8.5px;
      }
      .info-label {
        font-size: 7.5px;
        text-transform: uppercase;
        font-weight: bold;
        color: #64748b;
        display: block;
        margin-bottom: 2px;
      }
      .info-val {
        font-weight: 600;
        color: #0f172a;
      }
      .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
      }
      .data-table th {
        background-color: #0f172a;
        color: #ffffff;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 5px 6px;
        font-weight: bold;
        border: 1px solid #0f172a;
        text-align: center;
      }
      .data-table th.text-left {
        text-align: left;
      }
      .data-table td {
        padding: 4px 6px;
        border: 1px solid #e2e8f0;
        font-size: 8px;
        text-align: center;
      }
      .data-table td.text-left {
        text-align: left;
      }
      .data-table tr:nth-child(even) td {
        background-color: #f8fafc;
      }
      .badge {
        display: inline-block;
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 7.5px;
        font-weight: bold;
      }
      .badge-success {
        background-color: #dcfce7;
        color: #166534;
      }
      .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
      }
      .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
      }
      .badge-secondary {
        background-color: #f1f5f9;
        color: #475569;
      }
      .signatures {
        margin-top: 25px;
        width: 100%;
      }
      .signatures td {
        width: 50%;
        text-align: center;
        padding: 0 40px;
      }
      .sign-line {
        border-top: 1px solid #64748b;
        margin-top: 40px;
        padding-top: 4px;
        font-weight: bold;
        font-size: 8.5px;
      }
      .sign-title {
        font-size: 7.5px;
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
          <span class="info-label">Sucursal</span>
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
          <span class="info-label">Incompletas / Sin salida</span>
          <span class="info-val">{{ $stats['total_incompletas'] ?? 0 }}</span>
        </td>
        <td>
          <span class="info-label">Personal Registrado</span>
          <span class="info-val">{{ $stats['total_empleados_unicos'] ?? 0 }} colaboradores</span>
        </td>
        <td>
          <span class="info-label">Filtro de Estado</span>
          <span class="info-val">{{ ucfirst($filtroEstado ?? 'Todos') }}</span>
        </td>
      </tr>
    </table>

    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 60px;">Fecha</th>
          <th style="width: 50px;">Día</th>
          <th class="text-left" style="width: 150px;">Personal</th>
          <th style="width: 50px;">Código</th>
          <th style="width: 70px;">Sucursal</th>
          <th style="width: 80px;">Horario Prog.</th>
          <th style="width: 50px;">Entrada</th>
          <th style="width: 50px;">Salida</th>
          <th style="width: 60px;">Horas Trab.</th>
          <th style="width: 60px;">Retraso</th>
          <th style="width: 85px;">Estado</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($registros as $row)
          <tr>
            <td>{{ $row->fecha_formateada }}</td>
            <td>{{ ucfirst(substr($row->dia ?? '', 0, 3)) }}</td>
            <td class="text-left">
              <strong>{{ $row->empleado?->nombre_completo ?? 'N/D' }}</strong>
              @if($row->empleado?->area)
                <br><span style="font-size: 7px; color: #64748b;">{{ $row->empleado->area }}</span>
              @endif
            </td>
            <td>{{ $row->codigo }}</td>
            <td>{{ $row->empleado?->sucursal ?? 'N/D' }}</td>
            <td>{{ $row->horario_programado ?? '--' }}</td>
            <td><strong>{{ $row->hora_entrada }}</strong></td>
            <td><strong>{{ $row->hora_salida }}</strong></td>
            <td>{{ $row->horas_trabajadas }}</td>
            <td>
              @if(($row->minutos_retraso ?? 0) > 0)
                <span style="color: #b91c1c; font-weight: bold;">+{{ $row->minutos_retraso }}m</span>
              @else
                <span style="color: #15803d;">0m</span>
              @endif
            </td>
            <td>
              @if($row->tipo_estado === 'puntual')
                <span class="badge badge-success">Puntual</span>
              @elseif($row->tipo_estado === 'retraso')
                <span class="badge badge-warning">{{ $row->estado_marcacion }}</span>
              @elseif($row->tipo_estado === 'incompleto')
                <span class="badge badge-danger">{{ $row->estado_marcacion }}</span>
              @else
                <span class="badge badge-secondary">{{ $row->estado_marcacion }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="11" style="padding: 20px; text-align: center; color: #64748b;">
              No se encontraron marcaciones para los filtros seleccionados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

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
