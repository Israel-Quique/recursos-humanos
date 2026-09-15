<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Reporte de Marcaciones - {{ $empleadoInfo['nombre_completo'] ?? 'Personal' }}</title>
  <style>
    @page {
      margin: 24px 28px;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #1e293b;
      font-size: 10.5px;
      line-height: 1.35;
      margin: 0;
      padding: 0;
    }

    h1,
    h2,
    h3,
    h4,
    p {
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
      font-size: 18px;
      font-weight: bold;
      color: #0f172a;
      margin-top: 3px;
    }

    .meta-right {
      text-align: right;
      font-size: 9px;
      color: #475569;
    }

    .info-box {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      background-color: #f8fafc;
      margin-bottom: 12px;
      border-collapse: collapse;
    }

    .info-box td {
      padding: 6px 10px;
      vertical-align: top;
      font-size: 9.5px;
    }

    .info-label {
      font-size: 8px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #64748b;
      font-weight: bold;
      display: block;
      margin-bottom: 2px;
    }

    .info-value {
      font-size: 11px;
      font-weight: bold;
      color: #0f172a;
    }

    .stats-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
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
      font-size: 14px;
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
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #0f172a;
      border-bottom: 1px solid #cbd5e1;
      padding-bottom: 4px;
      margin-bottom: 8px;
      margin-top: 4px;
    }

    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 9.5px;
    }

    .data-table th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-size: 8.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      border-top: 1px solid #94a3b8;
      border-bottom: 1.5px solid #64748b;
      padding: 6px 8px;
      text-align: left;
    }

    .data-table th.center,
    .data-table td.center {
      text-align: center;
    }

    .data-table td {
      border-bottom: 1px solid #e2e8f0;
      padding: 5.5px 8px;
      vertical-align: middle;
    }

    .data-table tr:nth-child(even) {
      background-color: #f8fafc;
    }

    .font-mono {
      font-family: 'DejaVu Sans Mono', monospace, sans-serif;
      font-size: 9px;
    }

    .badge-ok {
      font-weight: normal;
      color: #111827;
      background: #f3f4f6;
      border: 1px solid #9ca3af;
      padding: 1.5px 5px;
      border-radius: 2px;
      font-size: 8px;
      display: inline-block;
    }

    .badge-late {
      font-weight: bold;
      color: #000000;
      background: #e5e7eb;
      border: 1px solid #4b5563;
      padding: 1.5px 5px;
      border-radius: 2px;
      font-size: 8px;
      display: inline-block;
    }

    .badge-absent,
    .badge-omision {
      font-weight: bold;
      color: #000000;
      background: #e2e8f0;
      border: 1.5px solid #0f172a;
      padding: 1.5px 5px;
      border-radius: 2px;
      font-size: 8px;
      display: inline-block;
    }

    .badge-warn {
      font-weight: bold;
      color: #000000;
      background: #f3f4f6;
      border: 1px dashed #374151;
      padding: 1.5px 5px;
      border-radius: 2px;
      font-size: 8px;
      display: inline-block;
    }

    .row-omision {
      background-color: #fff7ed !important;
    }

    .badge-omision-alert {
      font-weight: bold;
      color: #9a3412;
      background-color: #ffedd5;
      border: 1px solid #f97316;
      padding: 1.5px 6px;
      border-radius: 3px;
      font-size: 8px;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .badge-omision-missing {
      font-weight: bold;
      color: #c2410c;
      background-color: #ffedd5;
      border: 1px dashed #ea580c;
      padding: 1.5px 5px;
      border-radius: 3px;
      font-size: 8px;
      display: inline-block;
    }

    .footer-signatures {
      width: 100%;
      margin-top: 30px;
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
      margin-top: 45px;
      padding-top: 4px;
      font-size: 9px;
      font-weight: bold;
      color: #334155;
    }

    .sign-sub {
      font-size: 8px;
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
        <p class="kicker">CORREOS DE BOLIVIA · RECURSOS HUMANOS</p>
        <h1 class="title">Reporte de Marcaciones de Asistencia</h1>
        <p style="font-size: 9px; color: #475569; margin-top: 2px;">
          Período consultado: <strong>{{ $periodoLabel }}</strong>
        </p>
      </td>
      <td class="meta-right" style="vertical-align: middle; width: 35%;">
        <p><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>Generado por:</strong> {{ auth()->user()?->name ?? 'Sistema' }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($filterEstado) }}</p>
      </td>
    </tr>
  </table>

  {{-- INFORMACIÓN DEL PERSONAL (SI HAY EMPLEADO FILTRADO) --}}
  @if ($empleadoInfo)
    <table class="info-box">
      <tr>
        <td width="38%">
          <span class="info-label">Personal</span>
          <span class="info-value">{{ $empleadoInfo['nombre_completo'] }}</span>
        </td>
        <td width="20%">
          <span class="info-label">Código Biométrico</span>
          <span class="info-value">{{ $empleadoInfo['codigo'] }}</span>
        </td>
        <td width="22%">
          <span class="info-label">Sucursal</span>
          <span class="info-value">{{ $empleadoInfo['sucursal'] }}</span>
        </td>
        <td width="20%">
          <span class="info-label">Área</span>
          <span class="info-value">{{ $empleadoInfo['area'] ?: 'General' }}</span>
        </td>
      </tr>
    </table>

    {{-- CUADRO DE MÉTRICAS RESUMEN --}}
    @if (!empty($stats))
      <table class="stats-table">
        <tr>
          <td class="stat-cell">
            <div class="stat-title">Horas Acumuladas</div>
            <div class="stat-num">{{ $stats['horas_trabajadas_formateado'] ?? '0h 00m' }}</div>
            <div class="stat-sub">{{ $stats['dias_con_marcacion'] ?? 0 }} días laborados</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Retraso Total</div>
            <div class="stat-num" style="color: #0f172a;">
              {{ $stats['minutos_atraso_totales'] ?? 0 }} min
            </div>
            <div class="stat-sub">{{ $stats['total_atrasos'] ?? 0 }} día(s) con retraso</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Omisiones y Faltas</div>
            <div class="stat-num">
              {{ $stats['total_omisiones'] ?? 0 }} <span
                style="font-size: 9px; font-weight: normal; color: #64748b;">om.</span> /
              {{ $stats['total_faltas'] ?? 0 }} <span
                style="font-size: 9px; font-weight: normal; color: #64748b;">faltas</span>
            </div>
            <div class="stat-sub">Registros incompletos / ausencias</div>
          </td>
          <td class="stat-cell">
            <div class="stat-title">Tolerancia Mensual</div>
            <div class="stat-num" style="font-size: 11px; margin-top: 4px; color: #0f172a;">
              {{ $stats['estado_tolerancia'] ?? 'Dentro de tolerancia' }}
            </div>
            <div class="stat-sub">{{ $stats['saldo_tolerancia'] ?? '' }}</div>
          </td>
        </tr>
      </table>
    @endif
  @endif

  {{-- TABLA DE DETALLE DE MARCACIONES --}}
  <div class="section-heading">
    Detalle de Registros de Marcación ({{ count($registros) }} registros encontrados)
  </div>

  <table class="data-table">
    <thead>
      <tr>
        @if (!$empleadoInfo)
          <th style="width: 28%;">Personal</th>
        @endif
        <th class="center" style="width: {{ !$empleadoInfo ? '13%' : '17%' }};">Fecha</th>
        <th class="center" style="width: {{ !$empleadoInfo ? '11%' : '15%' }};">Día</th>
        <th class="center" style="width: {{ !$empleadoInfo ? '12%' : '17%' }};">Hora Entrada</th>
        <th class="center" style="width: {{ !$empleadoInfo ? '12%' : '17%' }};">Hora Salida</th>
        <th class="center" style="width: {{ !$empleadoInfo ? '12%' : '17%' }};">Horas Trab.</th>
        <th class="center" style="width: {{ !$empleadoInfo ? '12%' : '17%' }};">Estado / Retraso</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($registros as $row)
        @php
          $entradaStr = $row->hora_entrada ?? '--:--';
          $salidaStr = $row->hora_salida ?? '--:--';
          $tieneEntrada = filled($entradaStr) && $entradaStr !== '--:--';
          $tieneSalida = filled($salidaStr) && $salidaStr !== '--:--';
          $esOmision = ($tieneEntrada xor $tieneSalida);
          $faltaEntrada = !$tieneEntrada && $tieneSalida;
          $faltaSalida = $tieneEntrada && !$tieneSalida;
          $sinMarcacion = !$tieneEntrada && !$tieneSalida;
        @endphp
        <tr class="{{ $esOmision ? 'row-omision' : '' }}">
          @if (!$empleadoInfo)
            <td>
              <strong>{{ $row->empleado?->nombre_completo ?? 'Sin nombre' }}</strong>
              <span style="display: block; font-size: 7.5px; color: #475569;">Cód:
                {{ $row->codigo ?? $row->empleado?->codigo_biometrico }}</span>
            </td>
          @endif
          <td class="center">
            <strong>{{ $row->fecha_formateada ?? (\Carbon\Carbon::parse($row->fecha)->format('d/m/Y')) }}</strong>
          </td>
          <td class="center" style="text-transform: capitalize; color: #475569;">
            {{ $row->dia ?? (\Carbon\Carbon::parse($row->fecha)->locale('es')->isoFormat('dddd')) }}
          </td>
          <td class="center font-mono">
            @if($tieneEntrada)
              <strong style="color: #0f172a;">{{ $row->hora_entrada }}</strong>
            @elseif($faltaEntrada)
              <span class="badge-omision-missing">Omisión Entrada</span>
            @else
              <span
                style="font-weight: bold; color: #64748b; background: #f1f5f9; padding: 1px 3px; border: 1px solid #cbd5e1; font-size: 8px;">[Sin
                marcar]</span>
            @endif
          </td>
          <td class="center font-mono">
            @if($tieneSalida)
              <strong style="color: #0f172a;">{{ $row->hora_salida }}</strong>
            @elseif($faltaSalida)
              <span class="badge-omision-missing">Omisión Salida</span>
            @else
              <span
                style="font-weight: bold; color: #64748b; background: #f1f5f9; padding: 1px 3px; border: 1px solid #cbd5e1; font-size: 8px;">[Sin
                marcar]</span>
            @endif
          </td>
          <td class="center font-mono">
            @if(($row->horas_trabajadas ?? '--:--') !== '--:--')
              <strong>{{ $row->horas_trabajadas }}</strong>
            @else
              <span style="color: #64748b;">--:--</span>
            @endif
          </td>
          <td class="center">
            @if($faltaEntrada)
              <span class="badge-omision-alert">Omisión Entrada</span>
            @elseif($faltaSalida)
              <span class="badge-omision-alert">Omisión Salida</span>
              @if(($row->minutos_retraso ?? 0) > 0)
                <div style="margin-top: 2px;"><span class="badge-late">+{{ $row->minutos_retraso }} min</span></div>
              @endif
            @elseif($sinMarcacion)
              <span class="badge-absent">Sin marcación</span>
            @elseif(($row->minutos_retraso ?? 0) > 0)
              <span class="badge-late">+{{ $row->minutos_retraso }} min</span>
            @else
              <span class="badge-ok">Puntual</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ !$empleadoInfo ? '7' : '6' }}" style="text-align: center; padding: 18px; color: #94a3b8;">
            No se encontraron registros de marcación para los criterios seleccionados.
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
        <div class="sign-line">Conformidad del Personal / Supervisor</div>
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