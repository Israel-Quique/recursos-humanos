<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Detalle mensual del personal</title>
    <style>
      body {
        font-family: DejaVu Sans, sans-serif;
        color: #0f172a;
        margin: 28px;
        font-size: 12px;
      }

      h1, h2, h3, h4, p {
        margin: 0;
      }

      .sheet {
        display: block;
      }

      .header {
        border-bottom: 2px solid #dbe4f0;
        padding-bottom: 14px;
        margin-bottom: 18px;
      }

      .kicker {
        font-size: 10px;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: #2563eb;
        font-weight: bold;
      }

      .title {
        margin-top: 8px;
        font-size: 26px;
        font-weight: bold;
      }

      .copy {
        margin-top: 8px;
        color: #475569;
      }

      .badge {
        margin-top: 12px;
        display: inline-block;
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        padding: 8px 12px;
        background: #f8fafc;
      }

      .badge-label {
        font-size: 9px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: bold;
      }

      .badge-value {
        margin-top: 4px;
        font-size: 14px;
        font-weight: bold;
      }

      .grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 10px;
        margin: 0 -10px 14px -10px;
      }

      .card {
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        padding: 12px 14px;
        vertical-align: top;
        background: #ffffff;
      }

      .label {
        font-size: 9px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: bold;
      }

      .value {
        margin-top: 8px;
        font-size: 16px;
        font-weight: bold;
      }

      .value-sm {
        margin-top: 8px;
        font-size: 14px;
        font-weight: bold;
      }

      .table-shell {
        margin-top: 18px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        padding: 14px;
      }

      .section-title {
        margin-top: 4px;
        margin-bottom: 12px;
        font-size: 18px;
        font-weight: bold;
      }

      table.data-table {
        width: 100%;
        border-collapse: collapse;
      }

      .data-table th,
      .data-table td {
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 10px;
        text-align: left;
        vertical-align: top;
      }

      .data-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 9px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
      }

      .data-table tr:last-child td {
        border-bottom: none;
      }

      .empty {
        color: #94a3b8;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="sheet">
      <div class="header">
        <p class="kicker">Correos de Bolivia</p>
        <h1 class="title">Detalle mensual del personal</h1>
        <p class="copy">Tardanzas, no marcados y faltas del mes seleccionado.</p>
        <div class="badge">
          <div class="badge-label">Mes</div>
          <div class="badge-value">{{ $monthLabel }}</div>
        </div>
      </div>

      <table class="grid">
        <tr>
          <td class="card" width="50%">
            <div class="label">Nombre</div>
            <div class="value">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Sin nombre' }}</div>
          </td>
          <td class="card" width="50%">
            <div class="label">Codigo</div>
            <div class="value">{{ $detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo' }}</div>
          </td>
        </tr>
        <tr>
          <td class="card" width="50%">
            <div class="label">Sucursal</div>
            <div class="value">{{ $detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal' }}</div>
          </td>
          <td class="card" width="50%">
            <div class="label">Horario</div>
            <div class="value">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</div>
          </td>
        </tr>
      </table>

      <table class="grid">
        <tr>
          @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
            <td class="card">
              <div class="label">{{ $metric['label'] }}</div>
              <div class="value-sm">{{ $metric['value'] }}</div>
            </td>
          @endforeach
        </tr>
      </table>

      <div class="table-shell">
        <p class="kicker">Tardanzas</p>
        <h3 class="section-title">Dias tarde</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Retraso</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $item)
              <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['entrada'] }}</td>
                <td>{{ $item['salida'] }}</td>
                <td>{{ $item['retraso'] }}</td>
                <td>{{ $item['estado'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="empty">No tiene tardanzas en el mes.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-shell">
        <p class="kicker">No marcados</p>
        <h3 class="section-title">Registros incompletos</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($detailEmployeeReport['no_marcados'] ?? []) as $item)
              <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['entrada'] }}</td>
                <td>{{ $item['salida'] }}</td>
                <td>{{ $item['estado'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="empty">No tiene no marcados en el mes.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-shell">
        <p class="kicker">Faltas</p>
        <h3 class="section-title">Ausencias del mes</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Detalle</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($detailEmployeeReport['faltas'] ?? []) as $item)
              <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['detalle'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="2" class="empty">No tiene faltas en el mes.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
