<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte de Control de Asistencia - {{ $sucursalLabel }}</title>
    <style>
      @page {
        margin: 24px 28px;
      }
      body {
        font-family: 'DejaVu Sans', sans-serif;
        color: #1e293b;
        font-size: 10px;
        line-height: 1.35;
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
        padding-bottom: 8px;
        margin-bottom: 12px;
      }
      .kicker {
        font-size: 8.5px;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .title {
        font-size: 17px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .meta-right {
        text-align: right;
        font-size: 9px;
        color: #475569;
      }
      .stats-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .stat-cell {
        width: 25%;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        padding: 6px 8px;
        text-align: center;
      }
      .stat-title {
        font-size: 7.5px;
        text-transform: uppercase;
        font-weight: bold;
        color: #64748b;
        letter-spacing: 0.08em;
      }
      .stat-num {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 2px;
      }
      .stat-sub {
        font-size: 7.5px;
        color: #64748b;
        margin-top: 1px;
      }
      .section-heading {
        font-size: 10.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #0f172a;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 4px;
        margin-bottom: 6px;
        margin-top: 2px;
      }
      table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
        font-size: 9px;
      }
      .data-table th {
        background-color: #f1f5f9;
        color: #0f172a;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-top: 1px solid #94a3b8;
        border-bottom: 1.5px solid #64748b;
        padding: 5.5px 6px;
        text-align: left;
      }
      .data-table th.center,
      .data-table td.center {
        text-align: center;
      }
      .data-table td {
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 6px;
        vertical-align: middle;
      }
      .data-table tr:nth-child(even) {
        background-color: #f8fafc;
      }
      .font-mono {
        font-family: 'DejaVu Sans Mono', monospace, sans-serif;
        font-size: 8.5px;
      }
      .badge-ok {
        font-weight: bold;
        color: #047857;
        background: #ecfdf5;
        border: 0.5px solid #a7f3d0;
        padding: 1.5px 5px;
        border-radius: 4px;
        font-size: 7.5px;
        display: inline-block;
      }
      .badge-late {
        font-weight: bold;
        color: #b45309;
        background: #fffbeb;
        border: 0.5px solid #fde68a;
        padding: 1.5px 5px;
        border-radius: 4px;
        font-size: 7.5px;
        display: inline-block;
      }
      .badge-absent {
        font-weight: bold;
        color: #be123c;
        background: #fff1f2;
        border: 0.5px solid #fecdd3;
        padding: 1.5px 5px;
        border-radius: 4px;
        font-size: 7.5px;
        display: inline-block;
      }
      .footer-signatures {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
      }
      .footer-signatures td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        padding: 10px 20px;
      }
      .sign-line {
        border-top: 1px solid #64748b;
        margin-top: 40px;
        padding-top: 4px;
        font-size: 8.5px;
        font-weight: bold;
        color: #334155;
      }
      .sign-sub {
        font-size: 7.5px;
        color: #64748b;
      }
      .page-footer {
        position: fixed;
        bottom: -15px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 8px;
        color: #94a3b8;
        border-top: 0.5px solid #e2e8f0;
        padding-top: 4px;
      }
    </style>
  </head>
  <body>
    {{-- ENCABEZADO INSTITUCIONAL --}}
    <table class="header-table">
      <tr>
        <td style="vertical-align: middle;">
          <p class="kicker">EMPRESA DE CORREOS DE BOLIVIA · RECURSOS HUMANOS</p>
          <h1 class="title">Control Mensual de Asistencia y Sucursales</h1>
          <p style="font-size: 9px; color: #475569; margin-top: 2px;">
            Mes: <strong>{{ $mesLabel }}</strong> | Sucursal: <strong>{{ $sucursalLabel }}</strong>
          </p>
        </td>
        <td class="meta-right" style="vertical-align: middle; width: 35%;">
          <p><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
          <p><strong>Generado por:</strong> {{ auth()->user()?->name ?? 'Sistema' }}</p>
          <p><strong>Personal evaluado:</strong> {{ count($empleados) }} integrantes</p>
        </td>
      </tr>
    </table>

    {{-- CUADRO DE MÉTRICAS / KPIS DE SUCURSAL --}}
    @if (!empty($sucursalKpis))
      <table class="stats-table">
        <tr>
          <td class="stat-cell">
            <div class="stat-title">Tasa de Puntualidad</div>
            <div class="stat-num" style="color: #047857;">{{ $sucursalKpis['porcentaje_sin_atrasos'] ?? 0 }}%</div>
            <div class="stat-sub">{{ $sucursalKpis['sin_atrasos'] ?? 0 }} de {{ $sucursalKpis['total_empleados'] ?? 0 }} puntuales</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Cumplimiento Marcación</div>
            <div class="stat-num" style="color: #0f67c0;">{{ $sucursalKpis['porcentaje_sin_omisiones'] ?? 0 }}%</div>
            <div class="stat-sub">{{ $sucursalKpis['sin_omisiones'] ?? 0 }} sin omisiones</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Tolerancia Mensual</div>
            <div class="stat-num" style="color: {{ ($sucursalKpis['porcentaje_dentro_tolerancia'] ?? 0) < 100 ? '#b45309' : '#047857' }};">
              {{ $sucursalKpis['porcentaje_dentro_tolerancia'] ?? 0 }}%
            </div>
            <div class="stat-sub">{{ $sucursalKpis['dentro_tolerancia'] ?? 0 }} dentro de tolerancia</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Horas Trabajadas</div>
            <div class="stat-num">{{ $sucursalKpis['total_horas_trabajadas'] ?? '0h 0m' }}</div>
            <div class="stat-sub">Promedio: {{ $sucursalKpis['promedio_horas_empleado'] ?? '0h 0m' }}/persona</div>
          </td>
        </tr>
      </table>
    @endif

    {{-- TABLA DE DETALLE DE PERSONAL --}}
    <div class="section-heading">
      Detalle de Asistencia por Personal ({{ count($empleados) }} registros)
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 26%;">Personal</th>
          <th class="center" style="width: 14%;">Sucursal</th>
          <th class="center" style="width: 12%;">Horas Trab.</th>
          <th class="center" style="width: 14%;">Retraso Mes</th>
          <th class="center" style="width: 10%;">Omisiones</th>
          <th class="center" style="width: 11%;">Saldo Tolerancia</th>
          <th class="center" style="width: 13%;">Estado</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($empleados as $emp)
          @php
            $res = $emp->resumen_asistencia ?? [];
            $estadoRetraso = $res['estado_retraso'] ?? 'Dentro de tolerancia';
            $excedido = $estadoRetraso === 'Excedido';
            $retrasoMin = $res['retraso_mes'] ?? 0;
            $diasTarde = $res['dias_tarde'] ?? 0;
            $olvidos = $res['olvidos_marcacion'] ?? 0;
            $saldoMin = $res['saldo_retraso_formateado'] ?? '0 min';
            $horasMes = $res['horas_mes'] ?? '0h 0m';
          @endphp
          <tr>
            <td>
              <strong>{{ $emp->nombre_completo }}</strong>
              <span style="display: block; font-size: 7.5px; color: #64748b;">Cód: {{ $emp->codigo_biometrico ?: '—' }}</span>
            </td>
            <td class="center" style="color: #475569;">{{ $emp->sucursal ?? '—' }}</td>
            <td class="center font-mono">
              <strong style="color: #0f172a;">{{ $horasMes }}</strong>
            </td>
            <td class="center font-mono">
              @if ($retrasoMin > 0)
                <span class="badge-late">{{ $res['retraso_mes_formateado'] ?? ($retrasoMin . ' min') }} ({{ $diasTarde }} d)</span>
              @else
                <span class="badge-ok">0 min</span>
              @endif
            </td>
            <td class="center font-mono">
              @if ($olvidos > 0)
                <span class="badge-absent">{{ $olvidos }}</span>
              @else
                <span style="color: #64748b;">0</span>
              @endif
            </td>
            <td class="center font-mono">
              <span style="color: {{ $excedido ? '#94a3b8' : '#047857' }}; font-weight: bold;">
                {{ $saldoMin }}
              </span>
            </td>
            <td class="center">
              @if ($excedido)
                <span class="badge-absent">Excedido</span>
              @else
                <span class="badge-ok">En tolerancia</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="text-align: center; padding: 18px; color: #94a3b8;">
              No se encontraron registros de personal para la sucursal seleccionada.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- FIRMAS DE RESPONSABILIDAD --}}
    <table class="footer-signatures">
      <tr>
        <td>
          <div class="sign-line">Responsable de Recursos Humanos</div>
          <div class="sign-sub">Firma y Sello</div>
        </td>
        <td>
          <div class="sign-line">Supervisor / Encargado Regional</div>
          <div class="sign-sub">Firma y Aclaración</div>
        </td>
      </tr>
    </table>

    {{-- PIE DE PÁGINA --}}
    <div class="page-footer">
      Documento oficial emitido por el Sistema de Asistencia de Recursos Humanos · Correos de Bolivia
    </div>
  </body>
</html>
