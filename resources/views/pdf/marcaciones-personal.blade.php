<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>
    @if(isset($modoReporte) && $modoReporte === 'sucursal')
      Reporte de Marcaciones - Sucursal {{ $sucursalReporteLabel ?? 'Regional' }}
    @elseif(isset($modoReporte) && $modoReporte === 'global')
      Reporte de Marcaciones - General Nacional
    @else
      Reporte de Marcaciones - {{ $empleadoInfo['nombre_completo'] ?? ($fichas[0]['empleadoInfo']['nombre_completo'] ?? 'Personal') }}
    @endif
  </title>
  <style>
    @page {
      margin: 12px 16px;
      size: a4 portrait;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #1e293b;
      font-size: 7.5px;
      line-height: 1.15;
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

    .ficha-personal {
      width: 100%;
      page-break-inside: avoid;
    }

    .header-table {
      width: 100%;
      border-bottom: 1.5px solid #0f172a;
      padding-bottom: 3px;
      margin-bottom: 4px;
    }

    .kicker {
      font-size: 6.5px;
      letter-spacing: 0.15em;
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
      font-size: 7px;
      line-height: 1.25;
      color: #475569;
    }

    .info-box {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      background-color: #f8fafc;
      margin-bottom: 4px;
      border-collapse: collapse;
    }

    .info-box td {
      padding: 2.5px 6px;
      vertical-align: middle;
      font-size: 7.5px;
    }

    .info-label {
      font-size: 6px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #64748b;
      font-weight: bold;
      display: block;
      margin-bottom: 1px;
    }

    .info-value {
      font-size: 9px;
      font-weight: bold;
      color: #0f172a;
    }

    .stats-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }

    .stat-cell {
      width: 25%;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      padding: 2.5px 5px;
      text-align: center;
    }

    .stat-title {
      font-size: 6.5px;
      text-transform: uppercase;
      font-weight: bold;
      color: #64748b;
      letter-spacing: 0.05em;
    }

    .stat-num {
      font-size: 11px;
      font-weight: bold;
      color: #0f172a;
      margin-top: 1px;
    }

    .stat-sub {
      font-size: 6.5px;
      color: #64748b;
      margin-top: 0px;
    }

    .section-heading {
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #0f172a;
      border-bottom: 1px solid #cbd5e1;
      padding-bottom: 2px;
      margin-bottom: 2px;
      margin-top: 2px;
    }

    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
      font-size: 7.5px;
      line-height: 1.15;
    }

    .data-table th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-size: 7px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-top: 1px solid #94a3b8;
      border-bottom: 1.5px solid #64748b;
      padding: 2.5px 4px;
      text-align: left;
    }

    .data-table th.center,
    .data-table td.center {
      text-align: center;
    }

    .data-table td {
      border-bottom: 1px solid #e2e8f0;
      padding: 1.8px 4px;
      vertical-align: middle;
      font-size: 7.5px;
    }

    .data-table tr:nth-child(even) {
      background-color: #f8fafc;
    }

    .font-mono {
      font-family: 'DejaVu Sans Mono', monospace, sans-serif;
      font-size: 7.5px;
    }

    .badge-ok,
    .badge-late,
    .badge-absent,
    .badge-omision,
    .badge-falta,
    .badge-feriado,
    .badge-feriado-trabajado,
    .badge-permiso-autorizado,
    .badge-omision-alert,
    .badge-omision-missing {
      display: inline-block;
      padding: 1px 3.5px;
      border-radius: 2px;
      font-size: 6.5px;
      line-height: 1;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.02em;
      white-space: nowrap;
    }

    .badge-ok {
      font-weight: normal;
      color: #111827;
      background: #f3f4f6;
      border: 1px solid #9ca3af;
    }

    .badge-late {
      color: #b91c1c;
      background: #fee2e2;
      border: 1px solid #ef4444;
    }

    .badge-absent {
      color: #475569;
      background: #f1f5f9;
      border: 1px solid #94a3b8;
    }

    .row-omision {
      background-color: #fff7ed !important;
    }

    .badge-omision-alert {
      color: #9a3412;
      background-color: #ffedd5;
      border: 1px solid #f97316;
    }

    .badge-omision-missing {
      color: #c2410c;
      background-color: #ffedd5;
      border: 1px dashed #ea580c;
    }

    .row-falta {
      background-color: #fef2f2 !important;
    }

    .badge-falta {
      color: #991b1b;
      background-color: #fee2e2;
      border: 1px solid #ef4444;
    }

    .row-feriado {
      background-color: #f5f3ff !important;
    }

    .badge-feriado {
      color: #5b21b6;
      background-color: #ede9fe;
      border: 1px solid #8b5cf6;
    }

    .badge-feriado-trabajado {
      color: #4338ca;
      background-color: #e0e7ff;
      border: 1px solid #6366f1;
    }

    .row-permiso-autorizado {
      background-color: #f0fdf4 !important;
    }

    .badge-permiso-autorizado {
      color: #166534;
      background-color: #dcfce7;
      border: 1px solid #22c55e;
    }

    .footer-signatures {
      width: 100%;
      margin-top: 8px;
      border-collapse: collapse;
      page-break-inside: avoid;
    }

    .footer-signatures td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      padding: 2px 25px;
    }

    .sign-line {
      border-top: 1px solid #64748b;
      margin-top: 22px;
      padding-top: 2px;
      font-size: 7px;
      font-weight: bold;
      color: #334155;
    }

    .sign-sub {
      font-size: 6px;
      color: #64748b;
    }

    .page-footer {
      position: fixed;
      bottom: -10px;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7px;
      color: #94a3b8;
      border-top: 0.5px solid #e2e8f0;
      padding-top: 2px;
    }
  </style>
</head>

<body>
  @php
    $listaFichas = $fichas ?? [];
    if (empty($listaFichas)) {
        if (!empty($empleadoInfo)) {
            $listaFichas = [
                [
                    'empleadoInfo' => $empleadoInfo,
                    'stats' => $stats ?? [],
                    'registros' => $registros ?? [],
                ]
            ];
        } elseif (!empty($registros) && count($registros) > 0) {
            $grupos = collect($registros)->groupBy(fn($r) => $r->empleado_id ?? ($r->empleado?->id ?? 'gen'));
            $listaFichas = [];
            foreach ($grupos as $empId => $regs) {
                $primer = $regs->first();
                $empObj = $primer->empleado ?? null;
                $empInfo = $empObj ? [
                    'nombre_completo' => $empObj->nombre_completo,
                    'codigo' => $empObj->codigo_biometrico ?: (string)$empObj->id,
                    'sucursal' => $empObj->sucursal ?: 'General',
                    'area' => $empObj->area ?: 'General',
                ] : null;
                $listaFichas[] = [
                    'empleadoInfo' => $empInfo,
                    'stats' => $stats ?? [],
                    'registros' => $regs,
                ];
            }
        } else {
            $listaFichas = [
                [
                    'empleadoInfo' => null,
                    'stats' => [],
                    'registros' => [],
                ]
            ];
        }
    }
    $totalFichas = count($listaFichas);
  @endphp

  @foreach ($listaFichas as $index => $ficha)
    @php
      $empInfo = $ficha['empleadoInfo'] ?? null;
      $empStats = $ficha['stats'] ?? [];
      $empRegistros = $ficha['registros'] ?? [];
      $esUltimaFicha = ($index === $totalFichas - 1);
    @endphp

    <div class="ficha-personal" style="{{ !$esUltimaFicha ? 'page-break-after: always;' : '' }}">
      {{-- ENCABEZADO INSTITUCIONAL --}}
      <table class="header-table">
        <tr>
          <td style="vertical-align: middle;">
            <p class="kicker">CORREOS DE BOLIVIA · RECURSOS HUMANOS</p>
            <h1 class="title">Reporte de Marcaciones de Asistencia</h1>
            <p style="font-size: 7.5px; color: #475569; margin-top: 1px;">
              Período: <strong>{{ $periodoLabel }}</strong>
              @if(!empty($empInfo['sucursal']))
                &nbsp;&bull;&nbsp; Sucursal: <strong>{{ $empInfo['sucursal'] }}</strong>
              @elseif(isset($sucursalReporteLabel) && $sucursalReporteLabel)
                &nbsp;&bull;&nbsp; Sucursal: <strong>{{ $sucursalReporteLabel }}</strong>
              @endif
            </p>
          </td>
          <td class="meta-right" style="vertical-align: middle; width: 35%;">
            <p><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Por:</strong> {{ auth()->user()?->name ?? 'Sistema' }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($filterEstado ?? 'Todos') }}</p>
            @if($totalFichas > 1)
              <p><strong>Funcionario:</strong> {{ $index + 1 }} de {{ $totalFichas }}</p>
            @endif
          </td>
        </tr>
      </table>

      {{-- INFORMACIÓN DEL PERSONAL (SI HAY EMPLEADO FILTRADO) --}}
      @if ($empInfo)
        <table class="info-box">
          <tr>
            <td width="38%">
              <span class="info-label">Personal</span>
              <span class="info-value">{{ $empInfo['nombre_completo'] ?? '' }}</span>
            </td>
            <td width="20%">
              <span class="info-label">Código Biométrico</span>
              <span class="info-value">{{ $empInfo['codigo'] ?? $empInfo['codigo_biometrico'] ?? '' }}</span>
            </td>
            <td width="22%">
              <span class="info-label">Sucursal</span>
              <span class="info-value">{{ $empInfo['sucursal'] ?? '' }}</span>
            </td>
            <td width="20%">
              <span class="info-label">Área</span>
              <span class="info-value">{{ !empty($empInfo['area']) ? $empInfo['area'] : 'General' }}</span>
            </td>
          </tr>
        </table>

        {{-- CUADRO DE MÉTRICAS RESUMEN --}}
        @if (!empty($empStats))
          <table class="stats-table">
            <tr>
              <td class="stat-cell">
                <div class="stat-title">Horas Acumuladas</div>
                <div class="stat-num">{{ $empStats['horas_trabajadas_formateado'] ?? '0h 00m' }}</div>
                <div class="stat-sub">{{ $empStats['dias_con_marcacion'] ?? 0 }} días laborados</div>
              </td>
              <td class="stat-cell">
                <div class="stat-title">Retraso Total</div>
                <div class="stat-num" style="color: #0f172a;">
                  {{ $empStats['minutos_atraso_totales'] ?? 0 }} min
                </div>
                <div class="stat-sub">{{ $empStats['total_atrasos'] ?? 0 }} día(s) con retraso</div>
              </td>
              <td class="stat-cell">
                <div class="stat-title">Omisiones / Faltas / Permisos</div>
                <div class="stat-num">
                  {{ $empStats['total_omisiones'] ?? 0 }} <span style="font-size: 7.5px; font-weight: normal; color: #64748b;">om.</span> /
                  {{ $empStats['total_faltas'] ?? 0 }} <span style="font-size: 7.5px; font-weight: normal; color: #64748b;">fal.</span> /
                  {{ $empStats['total_permisos'] ?? 0 }} <span style="font-size: 7.5px; font-weight: normal; color: #166534;">perm.</span>
                </div>
                <div class="stat-sub">
                  @if(!empty($empStats['total_feriados']) && $empStats['total_feriados'] > 0)
                    <span style="color: #5b21b6; font-weight: bold;">{{ $empStats['total_feriados'] }} feriado(s)</span> &bull;
                  @endif
                  Registros del período
                </div>
              </td>
              <td class="stat-cell">
                <div class="stat-title">Tolerancia Mensual</div>
                <div class="stat-num" style="font-size: 9.5px; margin-top: 2px; color: #0f172a;">
                  {{ $empStats['estado_tolerancia'] ?? 'Dentro de tolerancia' }}
                </div>
                <div class="stat-sub">{{ $empStats['saldo_tolerancia'] ?? '' }}</div>
              </td>
            </tr>
          </table>
        @endif
      @endif

      {{-- TABLA DE DETALLE DE MARCACIONES --}}
      <div class="section-heading">
        Detalle de Registros de Marcación ({{ count($empRegistros) }} registros)
      </div>

      <table class="data-table">
        <thead>
          <tr>
            @if (!$empInfo)
              <th style="width: 26%;">Personal</th>
            @endif
            <th class="center" style="width: {{ !$empInfo ? '13%' : '15%' }};">Fecha</th>
            <th class="center" style="width: {{ !$empInfo ? '11%' : '14%' }};">Día</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '17%' }};">Hora Entrada</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '17%' }};">Hora Salida</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '15%' }};">Horas Trab.</th>
            <th class="center" style="width: {{ !$empInfo ? '14%' : '22%' }};">Estado / Retraso</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($empRegistros as $row)
            @php
              $esFeriado = !empty($row->es_feriado) || ($row->estado_marcacion ?? '') === 'Feriado' || str_contains(strtolower((string)($row->estado_marcacion ?? '')), 'feriado');
              $nombreFeriado = $row->nombre_feriado ?? ($esFeriado ? ($row->observacion ?: 'Feriado') : null);
              $esFalta = !$esFeriado && (!empty($row->es_falta) || ($row->estado_marcacion ?? '') === 'Falta' || str_contains(strtolower((string)($row->estado_marcacion ?? '')), 'falta'));
              $esPermisoAutorizado = !$esFeriado && !$esFalta && !empty($row->permiso_autorizado);
              $entradaStr = $row->hora_entrada ?? '--:--';
              $salidaStr = $row->hora_salida ?? '--:--';
              $tieneEntrada = filled($entradaStr) && $entradaStr !== '--:--';
              $tieneSalida = filled($salidaStr) && $salidaStr !== '--:--';
              $esOmision = !$esFeriado && !$esFalta && !$esPermisoAutorizado && ($tieneEntrada xor $tieneSalida);
              $faltaEntrada = !$esFeriado && !$esFalta && !$esPermisoAutorizado && !$tieneEntrada && $tieneSalida;
              $faltaSalida = !$esFeriado && !$esFalta && !$esPermisoAutorizado && $tieneEntrada && !$tieneSalida;
              $sinMarcacion = !$esFeriado && !$esFalta && !$esPermisoAutorizado && !$tieneEntrada && !$tieneSalida;
              $tipoPermisoLabel = $row->tipo_permiso_label ?? ($esPermisoAutorizado ? 'Permiso' : null);
            @endphp
            <tr class="{{ $esFeriado ? 'row-feriado' : ($esFalta ? 'row-falta' : ($esPermisoAutorizado ? 'row-permiso-autorizado' : ($esOmision ? 'row-omision' : ''))) }}">
              @if (!$empInfo)
                <td>
                  <strong>{{ $row->empleado?->nombre_completo ?? 'Sin nombre' }}</strong>
                  <span style="display: block; font-size: 6.5px; color: #475569;">Cód: {{ $row->codigo ?? $row->empleado?->codigo_biometrico }}</span>
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
                @elseif($esFeriado || $esFalta || $esPermisoAutorizado)
                  <span style="color: #94a3b8;">--:--</span>
                @elseif($faltaEntrada)
                  <span class="badge-omision-missing">Sin Entrada</span>
                @else
                  <span style="color: #94a3b8;">--:--</span>
                @endif
              </td>
              <td class="center font-mono">
                @if($tieneSalida)
                  <strong style="color: #0f172a;">{{ $row->hora_salida }}</strong>
                @elseif($esFeriado || $esFalta || $esPermisoAutorizado)
                  <span style="color: #94a3b8;">--:--</span>
                @elseif($faltaSalida)
                  <span class="badge-omision-missing">Sin Salida</span>
                @else
                  <span style="color: #94a3b8;">--:--</span>
                @endif
              </td>
              <td class="center font-mono">
                @if($esFeriado && !$tieneEntrada && !$tieneSalida)
                  <span style="color: #64748b;">0h 00m</span>
                @elseif($esFalta)
                  <span style="color: #991b1b; font-weight: bold;">0h 00m</span>
                @elseif($esPermisoAutorizado && !$tieneEntrada && !$tieneSalida)
                  <span style="color: #166534; font-weight: bold;">Permiso</span>
                @elseif(($row->horas_trabajadas ?? '--:--') !== '--:--')
                  <strong>{{ $row->horas_trabajadas }}</strong>
                @else
                  <span style="color: #64748b;">--:--</span>
                @endif
              </td>
              <td class="center">
                @if($esFeriado)
                  @if($tieneEntrada && $tieneSalida)
                    <span class="badge-feriado-trabajado">Feriado Trab.</span>
                  @else
                    <span class="badge-feriado">Feriado{{ (!empty($nombreFeriado) && $nombreFeriado !== 'Feriado') ? ': ' . \Illuminate\Support\Str::limit($nombreFeriado, 14) : '' }}</span>
                  @endif
                @elseif($esFalta)
                  <span class="badge-falta">Falta</span>
                @elseif($esPermisoAutorizado)
                  <span class="badge-permiso-autorizado">{{ $tipoPermisoLabel ?? 'Permiso' }}</span>
                @elseif($faltaEntrada)
                  <span class="badge-omision-alert">Omisión Entrada</span>
                @elseif($faltaSalida)
                  <span class="badge-omision-alert">Omisión Salida</span>
                  @if(($row->minutos_retraso ?? 0) > 0)
                    <span class="badge-late">+{{ $row->minutos_retraso }}m</span>
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
              <td colspan="{{ !$empInfo ? '7' : '6' }}" style="text-align: center; padding: 10px; color: #94a3b8;">
                No se encontraron registros de marcación para los criterios seleccionados.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- FIRMAS DE RESPONSABILIDAD: Omitidas en reportes globales y por sucursal por restricción de espacio --}}
      @if (!in_array($modoReporte ?? '', ['sucursal', 'global'], true) && ($totalFichas ?? 1) === 1)
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
      @endif
    </div>
  @endforeach

  {{-- PIE DE PÁGINA --}}
  <div class="page-footer">
    Documento oficial emitido por el Sistema de Asistencia de Recursos Humanos · Correos de Bolivia
  </div>
</body>
</html>