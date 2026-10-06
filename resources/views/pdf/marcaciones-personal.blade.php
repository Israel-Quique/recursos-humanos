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
      margin: 14px 20px 12px 20px;
      size: a4 portrait;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #000000;
      font-size: 9.5px;
      line-height: 1.25;
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

    /* ENCABEZADO INSTITUCIONAL - FORMATO MONOCROMÁTICO DE ALTO CONTRASTE */
    .header-table {
      width: 100%;
      border-bottom: 2px solid #000000;
      padding-bottom: 3px;
      margin-bottom: 5px;
    }

    .kicker {
      font-size: 8px;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #1a1a1a;
      font-weight: bold;
    }

    .title {
      font-size: 15px;
      font-weight: 900;
      color: #000000;
      margin-top: 1px;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    .meta-subtitle {
      font-size: 8.5px;
      color: #1a1a1a;
      margin-top: 2px;
    }

    .meta-right {
      text-align: right;
      font-size: 8px;
      line-height: 1.3;
      color: #1a1a1a;
    }

    /* CAJA DE INFORMACIÓN DEL PERSONAL */
    .info-box {
      width: 100%;
      border: 1.5px solid #000000;
      background-color: #ffffff;
      margin-bottom: 5px;
      border-collapse: collapse;
    }

    .info-box td {
      padding: 3.5px 6px;
      vertical-align: middle;
      border-right: 1px solid #9ca3af;
    }

    .info-box td:last-child {
      border-right: none;
    }

    .info-label {
      font-size: 7px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #374151;
      font-weight: bold;
      display: block;
      margin-bottom: 1px;
    }

    .info-value {
      font-size: 11px;
      font-weight: 900;
      color: #000000;
    }

    /* CUADRO DE MÉTRICAS RESUMEN */
    .stats-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 5px;
    }

    .stat-cell {
      width: 25%;
      border: 1px solid #000000;
      background: #ffffff;
      padding: 3.5px 5px;
      text-align: center;
    }

    .stat-title {
      font-size: 7.5px;
      text-transform: uppercase;
      font-weight: bold;
      color: #1f2937;
      letter-spacing: 0.04em;
    }

    .stat-num {
      font-size: 14px;
      font-weight: 900;
      color: #000000;
      margin-top: 1px;
    }

    .stat-sub {
      font-size: 7.5px;
      color: #374151;
      margin-top: 0px;
    }

    /* TÍTULO DE SECCIÓN */
    .section-heading {
      font-size: 9px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #000000;
      border-bottom: 1.5px solid #000000;
      padding-bottom: 2px;
      margin-bottom: 3px;
      margin-top: 2px;
    }

    /* TABLA PRINCIPAL DE MARCACIONES */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2px;
      line-height: 1.25;
    }

    .data-table th {
      background-color: #1f2937;
      color: #ffffff;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      border: 1px solid #1f2937;
      text-align: left;
    }

    .data-table th.center,
    .data-table td.center {
      text-align: center;
    }

    .data-table td {
      border: 1px solid #9ca3af;
      vertical-align: middle;
      color: #000000;
    }

    .data-table tr:nth-child(even) td {
      background-color: #fafafa;
    }

    .font-mono {
      font-family: 'DejaVu Sans Mono', monospace, sans-serif;
    }

    /* VARIACIONES DINÁMICAS: TIPOGRAFÍA MÁS GRANDE Y AJUSTE A 1 PLANA EXACTA */
    .tabla-densa th {
      padding: 3.5px 4px;
      font-size: 8.5px;
    }
    .tabla-densa td {
      padding: 3.2px 4px;
      font-size: 9.2px;
    }
    .tabla-densa .font-mono {
      font-size: 9.2px;
    }
    .tabla-densa .badge-ok,
    .tabla-densa .badge-late,
    .tabla-densa .badge-absent,
    .tabla-densa .badge-falta,
    .tabla-densa .badge-feriado,
    .tabla-densa .badge-feriado-trabajado,
    .tabla-densa .badge-permiso-autorizado,
    .tabla-densa .badge-omision-alert,
    .tabla-densa .badge-omision-missing {
      font-size: 7.5px;
      padding: 1.5px 4px;
    }

    .tabla-media th {
      padding: 4.8px 5px;
      font-size: 9.2px;
    }
    .tabla-media td {
      padding: 4.8px 5px;
      font-size: 10.0px;
    }
    .tabla-media .font-mono {
      font-size: 10.0px;
    }
    .tabla-media .badge-ok,
    .tabla-media .badge-late,
    .tabla-media .badge-absent,
    .tabla-media .badge-falta,
    .tabla-media .badge-feriado,
    .tabla-media .badge-feriado-trabajado,
    .tabla-media .badge-permiso-autorizado,
    .tabla-media .badge-omision-alert,
    .tabla-media .badge-omision-missing {
      font-size: 8.2px;
      padding: 2px 5px;
    }

    .tabla-amplia th {
      padding: 6.5px 6px;
      font-size: 10.0px;
    }
    .tabla-amplia td {
      padding: 7.5px 6px;
      font-size: 11.0px;
    }
    .tabla-amplia .font-mono {
      font-size: 11.0px;
    }
    .tabla-amplia .badge-ok,
    .tabla-amplia .badge-late,
    .tabla-amplia .badge-absent,
    .tabla-amplia .badge-falta,
    .tabla-amplia .badge-feriado,
    .tabla-amplia .badge-feriado-trabajado,
    .tabla-amplia .badge-permiso-autorizado,
    .tabla-amplia .badge-omision-alert,
    .tabla-amplia .badge-omision-missing {
      font-size: 9.0px;
      padding: 2.5px 6px;
    }

    /* BADGES MONOCROMÁTICOS (100% APTOS PARA TÓNER Y FOTOCOPIADORA) */
    .badge-ok,
    .badge-late,
    .badge-absent,
    .badge-falta,
    .badge-feriado,
    .badge-feriado-trabajado,
    .badge-permiso-autorizado,
    .badge-omision-alert,
    .badge-omision-missing {
      display: inline-block;
      line-height: 1.15;
      font-weight: bold;
      text-transform: uppercase;
      white-space: nowrap;
      border-radius: 2px;
    }

    .badge-ok {
      font-weight: normal;
      color: #000000;
      background: #ffffff;
      border: 1px solid #6b7280;
    }

    .badge-late {
      color: #000000;
      background: #ffffff;
      border: 1.5px solid #000000;
      font-weight: bold;
    }

    .badge-absent {
      color: #4b5563;
      background: #ffffff;
      border: 1px dotted #9ca3af;
      font-weight: normal;
    }

    .row-omision {
      background-color: #ffffff !important;
    }

    .badge-omision-alert {
      color: #000000;
      background-color: #ffffff;
      border: 1.5px dashed #000000;
      font-weight: bold;
    }

    .badge-omision-missing {
      color: #000000;
      background-color: #ffffff;
      border-bottom: 1.5px dashed #000000;
      font-style: italic;
      font-weight: bold;
      border-radius: 0;
      padding: 0 1px;
    }

    .row-falta {
      background-color: #f3f4f6 !important;
    }

    .badge-falta {
      color: #ffffff;
      background-color: #000000;
      border: 1px solid #000000;
      font-weight: 900;
      letter-spacing: 0.05em;
    }

    .row-feriado {
      background-color: #f3f4f6 !important;
    }

    .badge-feriado {
      color: #000000;
      background-color: #e5e7eb;
      border: 1.5px solid #000000;
      font-weight: bold;
    }

    .badge-feriado-trabajado {
      color: #000000;
      background-color: #ffffff;
      border: 1.5px solid #000000;
      font-weight: bold;
    }

    .row-permiso-autorizado {
      background-color: #ffffff !important;
    }

    .badge-permiso-autorizado {
      color: #000000;
      background-color: #f3f4f6;
      border: 1.5px solid #000000;
      font-weight: bold;
    }

    /* FIRMAS DE RESPONSABILIDAD */
    .footer-signatures {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
      page-break-inside: avoid;
    }

    .tabla-densa + .footer-signatures {
      margin-top: 12px;
    }

    .tabla-media + .footer-signatures {
      margin-top: 22px;
    }

    .tabla-amplia + .footer-signatures {
      margin-top: 32px;
    }

    .footer-signatures td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      padding: 2px 30px;
    }

    .sign-line {
      border-top: 1.5px solid #000000;
      padding-top: 3px;
      font-size: 8px;
      font-weight: bold;
      color: #000000;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .sign-sub {
      font-size: 7px;
      color: #374151;
      margin-top: 1px;
    }

    .page-footer {
      position: fixed;
      bottom: -6px;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7.5px;
      color: #6b7280;
      border-top: 0.5px solid #9ca3af;
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
      $cantRegs = count($empRegistros);

      // Determinación dinámica de espaciado y tipografía para llenar 1 plana exacta
      if ($cantRegs > 25) {
          $denseClass = 'tabla-densa';
      } elseif ($cantRegs > 17) {
          $denseClass = 'tabla-media';
      } else {
          $denseClass = 'tabla-amplia';
      }
    @endphp

    <div class="ficha-personal" style="{{ !$esUltimaFicha ? 'page-break-after: always;' : '' }}">
      {{-- ENCABEZADO INSTITUCIONAL --}}
      <table class="header-table">
        <tr>
          <td style="vertical-align: middle;">
            <p class="kicker">CORREOS DE BOLIVIA · RECURSOS HUMANOS</p>
            <h1 class="title">Reporte de Marcaciones de Asistencia</h1>
            <p class="meta-subtitle">
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
                <div class="stat-num">
                  {{ $empStats['minutos_atraso_totales'] ?? 0 }} min
                </div>
                <div class="stat-sub">{{ $empStats['total_atrasos'] ?? 0 }} día(s) con retraso</div>
              </td>
              <td class="stat-cell">
                <div class="stat-title">Omisiones / Faltas / Permisos</div>
                <div class="stat-num">
                  {{ $empStats['total_omisiones'] ?? 0 }} <span style="font-size: 8.5px; font-weight: normal; color: #4b5563;">om.</span> /
                  {{ $empStats['total_faltas'] ?? 0 }} <span style="font-size: 8.5px; font-weight: normal; color: #4b5563;">fal.</span> /
                  {{ $empStats['total_permisos'] ?? 0 }} <span style="font-size: 8.5px; font-weight: normal; color: #1f2937;">perm.</span>
                </div>
                <div class="stat-sub">
                  @if(!empty($empStats['total_feriados']) && $empStats['total_feriados'] > 0)
                    <strong>{{ $empStats['total_feriados'] }} feriado(s)</strong> &bull;
                  @endif
                  Registros del período
                </div>
              </td>
              <td class="stat-cell">
                <div class="stat-title">Tolerancia Mensual</div>
                <div class="stat-num" style="font-size: 11px; margin-top: 2px;">
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

      <table class="data-table {{ $denseClass }}">
        <thead>
          <tr>
            @if (!$empInfo)
              <th style="width: 26%;">Personal</th>
            @endif
            <th class="center" style="width: {{ !$empInfo ? '13%' : '14%' }};">Fecha</th>
            <th class="center" style="width: {{ !$empInfo ? '11%' : '13%' }};">Día</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '15%' }};">Hora Entrada</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '15%' }};">Hora Salida</th>
            <th class="center" style="width: {{ !$empInfo ? '12%' : '15%' }};">Horas Trab.</th>
            <th class="center" style="width: {{ !$empInfo ? '16%' : '28%' }};">Estado / Retraso</th>
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
                  <span style="display: block; font-size: 7.5px; color: #4b5563;">Cód: {{ $row->codigo ?? $row->empleado?->codigo_biometrico }}</span>
                </td>
              @endif
              <td class="center">
                <strong>{{ $row->fecha_formateada ?? (\Carbon\Carbon::parse($row->fecha)->format('d/m/Y')) }}</strong>
              </td>
              <td class="center" style="text-transform: capitalize; color: #374151;">
                {{ $row->dia ?? (\Carbon\Carbon::parse($row->fecha)->locale('es')->isoFormat('dddd')) }}
              </td>
              <td class="center font-mono">
                @if($tieneEntrada)
                  <strong>{{ $row->hora_entrada }}</strong>
                @elseif($esFeriado || $esFalta || $esPermisoAutorizado)
                  <span style="color: #6b7280;">--:--</span>
                @elseif($faltaEntrada)
                  <span class="badge-omision-missing">Sin Entrada</span>
                @else
                  <span style="color: #6b7280;">--:--</span>
                @endif
              </td>
              <td class="center font-mono">
                @if($tieneSalida)
                  <strong>{{ $row->hora_salida }}</strong>
                @elseif($esFeriado || $esFalta || $esPermisoAutorizado)
                  <span style="color: #6b7280;">--:--</span>
                @elseif($faltaSalida)
                  <span class="badge-omision-missing">Sin Salida</span>
                @else
                  <span style="color: #6b7280;">--:--</span>
                @endif
              </td>
              <td class="center font-mono">
                @if($esFeriado && !$tieneEntrada && !$tieneSalida)
                  <span style="color: #4b5563;">0h 00m</span>
                @elseif($esFalta)
                  <strong>0h 00m</strong>
                @elseif($esPermisoAutorizado && !$tieneEntrada && !$tieneSalida)
                  <strong>Permiso</strong>
                @elseif(($row->horas_trabajadas ?? '--:--') !== '--:--')
                  <strong>{{ $row->horas_trabajadas }}</strong>
                @else
                  <span style="color: #4b5563;">--:--</span>
                @endif
              </td>
              <td class="center">
                @if($esFeriado)
                  @if($tieneEntrada && $tieneSalida)
                    <span class="badge-feriado-trabajado">Feriado Trab.</span>
                  @else
                    <span class="badge-feriado">Feriado{{ (!empty($nombreFeriado) && $nombreFeriado !== 'Feriado') ? ': ' . $nombreFeriado : '' }}</span>
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
              <td colspan="{{ !$empInfo ? '7' : '6' }}" style="text-align: center; padding: 14px; color: #6b7280;">
                No se encontraron registros de marcación para los criterios seleccionados.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- FIRMAS DE RESPONSABILIDAD: Mantenidas en reporte personal, omitidas en sucursal/global --}}
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