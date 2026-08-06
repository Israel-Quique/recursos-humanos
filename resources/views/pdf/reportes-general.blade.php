<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte general de asistencia</title>
    <style>
      body { font-family: DejaVu Sans, sans-serif; color: #0f172a; margin: 28px; font-size: 12px; }
      h1, h2, h3, h4, p { margin: 0; }
      .header { border-bottom: 2px solid #dbe4f0; padding-bottom: 14px; margin-bottom: 18px; }
      .kicker { font-size: 10px; letter-spacing: 0.28em; text-transform: uppercase; color: #2563eb; font-weight: bold; }
      .title { margin-top: 8px; font-size: 26px; font-weight: bold; }
      .copy { margin-top: 8px; color: #475569; line-height: 1.6; }
      .meta { margin-top: 12px; }
      .meta strong { display: inline-block; min-width: 90px; }
      .metrics { width: 100%; border-collapse: separate; border-spacing: 10px; margin: 0 -10px 12px -10px; }
      .card { border: 1px solid #dbe4f0; border-radius: 14px; padding: 12px 14px; background: #fff; vertical-align: top; }
      .label { font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase; color: #64748b; font-weight: bold; }
      .value { margin-top: 8px; font-size: 16px; font-weight: bold; }
      .section { margin-top: 18px; border: 1px solid #dbe4f0; border-radius: 16px; padding: 14px; }
      .section-title { margin-top: 4px; margin-bottom: 12px; font-size: 18px; font-weight: bold; }
      table.data-table { width: 100%; border-collapse: collapse; }
      .data-table th, .data-table td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; vertical-align: top; }
      .data-table th { background: #f8fafc; color: #475569; font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; }
      .data-table tr:last-child td { border-bottom: none; }
      .empty { color: #94a3b8; text-align: center; }
    </style>
  </head>
  <body>
    <div class="header">
      <p class="kicker">Correos de Bolivia</p>
      <h1 class="title">Reporte general de asistencia</h1>
      <p class="copy">Resumen mensual con personal que llego tarde, registros no marcados e incidencias detectadas.</p>
      <div class="meta">
        <p><strong>Mes:</strong> {{ $monthLabel }}</p>
        <p><strong>Sucursal:</strong> {{ $branchLabel }}</p>
      </div>
    </div>

    <table class="metrics">
      <tr>
        @foreach(($report['metrics'] ?? []) as $metric)
          <td class="card">
            <div class="label">{{ $metric['label'] }}</div>
            <div class="value">{{ $metric['value'] }}</div>
          </td>
        @endforeach
      </tr>
    </table>

    <div class="section">
      <p class="kicker">Resumen de atrasos</p>
      <h3 class="section-title">Personal que llego tarde</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Personal</th>
            <th>Sucursal</th>
            <th>Dias tarde</th>
            <th>Retraso acumulado</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($report['resumen_atrasos'] ?? []) as $item)
            <tr>
              <td>{{ $item['nombre'] }}</td>
              <td>{{ $item['sucursal'] }}</td>
              <td>{{ $item['dias_tarde'] }}</td>
              <td>{{ $item['retraso'] }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="empty">No hay atrasos registrados en el mes seleccionado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <p class="kicker">Detalle de atrasos</p>
      <h3 class="section-title">Marcaciones tardias del mes</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Personal</th>
            <th>Sucursal</th>
            <th>Hora programada</th>
            <th>Hora marcada</th>
            <th>Retraso</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($report['atrasos'] ?? []) as $item)
            <tr>
              <td>{{ $item['fecha'] }}</td>
              <td>{{ $item['nombre'] }}</td>
              <td>{{ $item['sucursal'] }}</td>
              <td>{{ $item['entrada_programada'] }}</td>
              <td>{{ $item['entrada_real'] }}</td>
              <td>{{ $item['retraso'] }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No existen atrasos en el rango seleccionado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="section">
      <p class="kicker">No marcados</p>
      <h3 class="section-title">Personal que no marco completamente</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Personal</th>
            <th>Sucursal</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Observacion</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($report['no_marcados'] ?? []) as $item)
            <tr>
              <td>{{ $item['fecha'] }}</td>
              <td>{{ $item['nombre'] }}</td>
              <td>{{ $item['sucursal'] }}</td>
              <td>{{ $item['entrada'] }}</td>
              <td>{{ $item['salida'] }}</td>
              <td>{{ $item['detalle'] }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No hay registros no marcados en el mes seleccionado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </body>
</html>
