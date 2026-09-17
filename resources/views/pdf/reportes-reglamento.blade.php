<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    @php
      $categoria = $categoria ?? 'todos';

      $infoCategoria = match ($categoria) {
          'atrasos' => [
              'titulo' => 'Reporte Especializado de Atrasos y Sanciones Salariales',
              'subtitulo' => 'Cómputo de minutos de retraso y días de descuento según el Artículo 45.I del Reglamento Interno',
              'articulo' => 'Artículo 45.I',
              'badge_class' => 'badge-amber',
          ],
          'omisiones' => [
              'titulo' => 'Reporte Especializado de Omisiones en el Registro de Asistencia',
              'subtitulo' => 'Evaluación de faltas de marcación (Art. 45.III) y causales de destitución (Art. 48.IV)',
              'articulo' => 'Art. 45.III y Art. 48.IV',
              'badge_class' => 'badge-rose',
          ],
          'faltas' => [
              'titulo' => 'Reporte Especializado de Faltas e Inasistencias Injustificadas',
              'subtitulo' => 'Descuento salarial al doble (Art. 45.II) y causales de retiro/abandono (Art. 48.II y 48.III)',
              'articulo' => 'Art. 45.II y Art. 48.II/III',
              'badge_class' => 'badge-red',
          ],
          'alertas' => [
              'titulo' => 'Reporte de Zona de Peligro y Alertas Preventivas',
              'subtitulo' => 'Personal próximo a sanciones por atraso (20-30 min) y límites de causales disciplinarias (Art. 45 y Art. 48)',
              'articulo' => 'Art. 45 y Art. 48 (Zona de Peligro)',
              'badge_class' => 'badge-amber',
          ],
          'reincidentes' => [
              'titulo' => 'Reporte de Control Anual de Personal Reincidente',
              'subtitulo' => 'Gestión Anual ' . ($reporte['gestion'] ?? date('Y')) . ' · Superaron 30 min (>2 meses) u Omisiones Reiteradas',
              'articulo' => 'Art. 48.I / Control Reincidencia',
              'badge_class' => 'badge-purple',
          ],
          'concurrente' => [
              'titulo' => 'Reporte de Casos con Concurrencia Normativa (Art. 45 y Art. 48)',
              'subtitulo' => 'Infracciones simultáneas: Descuento salarial (Art. 45) y causal disciplinaria grave (Art. 48)',
              'articulo' => 'Art. 45 + Art. 48 Simultáneos',
              'badge_class' => 'badge-purple',
          ],
          default => [
              'titulo' => 'Detalle Consolidado de Atrasos, Omisiones y Sanciones de Reglamento',
              'subtitulo' => 'Cómputo oficial de faltas, descuentos de haber y control de reincidencias (Art. 45 y Art. 48)',
              'articulo' => 'Reglamento Interno Integral',
              'badge_class' => 'badge-blue',
          ],
      };
    @endphp
    <title>{{ $infoCategoria['titulo'] }} - {{ $monthLabel }}</title>
    <style>
      @page {
        margin: 18px 20px 20px 20px;
        size: a4 portrait;
      }
      body {
        font-family: DejaVu Sans, sans-serif;
        color: #1e293b;
        font-size: 8.5px;
        line-height: 1.35;
      }
      .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        border-bottom: 2px solid #0f67c0;
        padding-bottom: 8px;
      }
      .org-name {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #0f67c0;
        font-weight: bold;
      }
      .report-title {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 1px;
      }
      .report-subtitle {
        font-size: 8px;
        color: #64748b;
        margin-top: 2px;
      }
      .meta-box {
        text-align: right;
        font-size: 8px;
        color: #334155;
      }
      .meta-box strong {
        color: #0f172a;
      }

      /* KPI Cards */
      .kpi-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px 0;
        margin-bottom: 14px;
      }
      .kpi-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 8px;
        text-align: center;
      }
      .kpi-card-alert {
        background-color: #fffbeb;
        border-color: #fde68a;
      }
      .kpi-card-danger {
        background-color: #fff1f2;
        border-color: #fecdd3;
      }
      .kpi-card-purple {
        background-color: #f5f3ff;
        border-color: #ddd6fe;
      }
      .kpi-title {
        font-size: 7.2px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: bold;
      }
      .kpi-value {
        font-size: 13px;
        font-weight: bold;
        color: #0f172a;
        margin: 2px 0;
      }
      .kpi-desc {
        font-size: 7px;
        color: #64748b;
      }

      /* Data Tables */
      .section-header {
        font-size: 9.5px;
        font-weight: bold;
        color: #0f172a;
        margin: 12px 0 6px 0;
        padding: 4px 8px;
        background-color: #f1f5f9;
        border-left: 3px solid #0f67c0;
        text-transform: uppercase;
        letter-spacing: 0.04em;
      }
      .section-header-amber {
        border-left-color: #d97706;
        background-color: #fef3c7;
        color: #92400e;
      }
      .section-header-rose {
        border-left-color: #e11d48;
        background-color: #ffe4e6;
        color: #9f1239;
      }
      .section-header-red {
        border-left-color: #b91c1c;
        background-color: #fee2e2;
        color: #7f1d1d;
      }
      .section-header-purple {
        border-left-color: #7c3aed;
        background-color: #f3e8ff;
        color: #6b21a8;
      }

      .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .data-table th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: bold;
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 5px 6px;
        border: 1px solid #cbd5e1;
        text-align: left;
      }
      .data-table td {
        padding: 4.5px 6px;
        border: 1px solid #e2e8f0;
        font-size: 8px;
        vertical-align: top;
      }
      .data-table tr:nth-child(even) td {
        background-color: #fcfdfe;
      }

      .text-center { text-align: center; }
      .text-right { text-align: right; }
      .font-bold { font-weight: bold; }
      .font-mono { font-family: monospace; font-size: 7.5px; }

      .badge {
        display: inline-block;
        padding: 1.5px 5px;
        border-radius: 4px;
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
      }
      .badge-amber {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
      }
      .badge-rose {
        background-color: #ffe4e6;
        color: #9f1239;
        border: 1px solid #fecdd3;
      }
      .badge-red {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
      }
      .badge-purple {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #d8b4fe;
      }
      .badge-blue {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
      }
      .badge-emerald {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
      }

      .fechas-list {
        font-size: 7.2px;
        color: #475569;
        line-height: 1.35;
      }
      
      .footer-signatures {
        margin-top: 24px;
        width: 100%;
        page-break-inside: avoid;
      }
      .sig-line {
        border-top: 1px solid #475569;
        width: 180px;
        margin: 0 auto;
        padding-top: 4px;
        text-align: center;
        font-size: 8px;
        color: #334155;
      }
    </style>
  </head>
  <body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
      <tr>
        <td style="width: 65%;">
          <div class="org-name">Agencia Boliviana de Correos · Recursos Humanos</div>
          <div class="report-title">{{ $infoCategoria['titulo'] }}</div>
          <div class="report-subtitle">{{ $infoCategoria['subtitulo'] }}</div>
        </td>
        <td class="meta-box" style="width: 35%;">
          <div><strong>Periodo:</strong> {{ $monthLabel }}</div>
          <div><strong>Sucursal:</strong> {{ $branchLabel }}</div>
          <div><strong>Normativa:</strong> {{ $infoCategoria['articulo'] }}</div>
          <div><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </td>
      </tr>
    </table>

    <!-- Resumen Ejecutivo (KPIs según Categoría) -->
    <table class="kpi-table">
      <tr>
        @if($categoria === 'atrasos')
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Personal con Sanción</div>
            <div class="kpi-value">{{ count($reporte['detalle_atrasos'] ?? []) }}</div>
            <div class="kpi-desc">Art. 45.I (Superaron tolerancia)</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Minutos Totales</div>
            <div class="kpi-value">{{ collect($reporte['detalle_atrasos'] ?? [])->sum('minutos_atraso') }} min</div>
            <div class="kpi-desc">Acumulado fuera de tolerancia</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Con Descuento Salarial</div>
            <div class="kpi-value">{{ count($reporte['detalle_atrasos'] ?? []) }}</div>
            <div class="kpi-desc">Superaron 30 min de tolerancia</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Días Descuento DAF</div>
            <div class="kpi-value">{{ collect($reporte['detalle_atrasos'] ?? [])->sum('dias_descuento') }} días</div>
            <div class="kpi-desc">Según escala de Art. 45.I</div>
          </td>

        @elseif($categoria === 'omisiones')
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Personal con Omisión</div>
            <div class="kpi-value">{{ count($reporte['detalle_omisiones'] ?? []) }}</div>
            <div class="kpi-desc">Sin marcar entrada o salida</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Total Omisiones</div>
            <div class="kpi-value">{{ collect($reporte['detalle_omisiones'] ?? [])->sum('total_omisiones') }}</div>
            <div class="kpi-desc">Registradas en el periodo</div>
          </td>
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Con Descuento Salarial</div>
            <div class="kpi-value">{{ collect($reporte['detalle_omisiones'] ?? [])->where('es_sancionado', true)->count() }}</div>
            <div class="kpi-desc">Art. 45.III (Sanciones económicas)</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Límite Destitución</div>
            <div class="kpi-value">{{ collect($reporte['detalle_omisiones'] ?? [])->where('total_omisiones', '>=', 4)->count() }}</div>
            <div class="kpi-desc">Art. 48.IV (>= 4 omisiones)</div>
          </td>

        @elseif($categoria === 'faltas')
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Personal con Faltas</div>
            <div class="kpi-value">{{ count($reporte['detalle_faltas'] ?? []) }}</div>
            <div class="kpi-desc">Días sin marcación / inasistencia</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Total Faltas</div>
            <div class="kpi-value">{{ collect($reporte['detalle_faltas'] ?? [])->sum('total_faltas') }} días</div>
            <div class="kpi-desc">Inasistencias injustificadas</div>
          </td>
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Días Descuento (Doble)</div>
            <div class="kpi-value">{{ collect($reporte['detalle_faltas'] ?? [])->sum('dias_descuento') }} días</div>
            <div class="kpi-desc">Art. 45.II (Día no trabajado + sanción)</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Causales de Retiro</div>
            <div class="kpi-value">{{ collect($reporte['detalle_faltas'] ?? [])->where('es_critico', true)->count() }}</div>
            <div class="kpi-desc">Art. 48.II/III (>= 3 faltas)</div>
          </td>

        @elseif($categoria === 'alertas')
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Zona de Peligro Total</div>
            <div class="kpi-value">{{ count($reporte['personal_en_alerta'] ?? []) }}</div>
            <div class="kpi-desc">Personal próximo a sanción</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Alerta Art. 45 (20-30 min)</div>
            <div class="kpi-value">{{ count($reporte['art_45']['alertas'] ?? []) }}</div>
            <div class="kpi-desc">A punto de descuento de 1/2 día</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Alerta Art. 48 (Destitución)</div>
            <div class="kpi-value">{{ count($reporte['art_48']['alertas'] ?? []) }}</div>
            <div class="kpi-desc">A 1 omisión o 1 mes grave</div>
          </td>
          <td class="kpi-card kpi-card-purple" style="width: 25%;">
            <div class="kpi-title">Concurrencia (45+48)</div>
            <div class="kpi-value">{{ $reporte['metricas']['concurrencia_articulos'] ?? 0 }}</div>
            <div class="kpi-desc">Ambos artículos simultáneos</div>
          </td>

        @elseif($categoria === 'reincidentes')
          <td class="kpi-card kpi-card-purple" style="width: 25%;">
            <div class="kpi-title">Casos Reincidentes</div>
            <div class="kpi-value">{{ count($reporte['detalle_reincidentes'] ?? []) }}</div>
            <div class="kpi-desc">Gestión {{ $reporte['gestion'] ?? date('Y') }}</div>
          </td>
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Reincidentes Atrasos</div>
            <div class="kpi-value">{{ count($reporte['reincidentes_atrasos'] ?? []) }}</div>
            <div class="kpi-desc">> 30 min en > 2 meses</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Reincidentes Omisiones</div>
            <div class="kpi-value">{{ count($reporte['reincidentes_omisiones'] ?? []) }}</div>
            <div class="kpi-desc">Omisiones reiteradas (>= 2)</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Gestión Evaluada</div>
            <div class="kpi-value">{{ $reporte['gestion'] ?? date('Y') }}</div>
            <div class="kpi-desc">Cómputo anual acumulado</div>
          </td>

        @elseif($categoria === 'concurrente')
          <td class="kpi-card kpi-card-purple" style="width: 25%;">
            <div class="kpi-title">Casos Concurrentes</div>
            <div class="kpi-value">{{ count($reporte['concurrentes'] ?? []) }}</div>
            <div class="kpi-desc">Art. 45 + Art. 48 simultáneos</div>
          </td>
          <td class="kpi-card kpi-card-alert" style="width: 25%;">
            <div class="kpi-title">Con Descuento Salarial</div>
            <div class="kpi-value">{{ collect($reporte['concurrentes'] ?? [])->filter(fn($c) => ($c['dias_sancion_total'] ?? 0) > 0)->count() }}</div>
            <div class="kpi-desc">Deducción de haberes (Art. 45)</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 25%;">
            <div class="kpi-title">Causal Destitución</div>
            <div class="kpi-value">{{ collect($reporte['concurrentes'] ?? [])->where('es_destitucion', true)->count() }}</div>
            <div class="kpi-desc">Proceso interno (Art. 48)</div>
          </td>
          <td class="kpi-card" style="width: 25%;">
            <div class="kpi-title">Días Totales Deducibles</div>
            <div class="kpi-value">{{ collect($reporte['concurrentes'] ?? [])->sum('dias_sancion_total') }} días</div>
            <div class="kpi-desc">Liquidación acumulada</div>
          </td>

        @else
          {{-- Consolidado General --}}
          <td class="kpi-card kpi-card-alert" style="width: 20%;">
            <div class="kpi-title">Con Atraso</div>
            <div class="kpi-value">{{ $reporte['metricas']['total_con_atraso'] ?? count($reporte['detalle_atrasos'] ?? []) }}</div>
            <div class="kpi-desc">Art. 45.I</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 20%;">
            <div class="kpi-title">Con Omisión</div>
            <div class="kpi-value">{{ $reporte['metricas']['total_con_omision'] ?? count($reporte['detalle_omisiones'] ?? []) }}</div>
            <div class="kpi-desc">Art. 45.III / 48.IV</div>
          </td>
          <td class="kpi-card kpi-card-danger" style="width: 20%;">
            <div class="kpi-title">Con Faltas</div>
            <div class="kpi-value">{{ $reporte['metricas']['total_con_faltas'] ?? count($reporte['detalle_faltas'] ?? []) }}</div>
            <div class="kpi-desc">Art. 45.II / 48.II</div>
          </td>
          <td class="kpi-card kpi-card-purple" style="width: 20%;">
            <div class="kpi-title">Reincidentes</div>
            <div class="kpi-value">{{ $reporte['metricas']['total_reincidentes'] ?? count($reporte['detalle_reincidentes'] ?? []) }}</div>
            <div class="kpi-desc">Gestión {{ $reporte['gestion'] ?? date('Y') }}</div>
          </td>
          <td class="kpi-card" style="width: 20%;">
            <div class="kpi-title">Días Descuento</div>
            <div class="kpi-value">{{ $reporte['metricas']['total_dias_sancion_formato'] ?? '0 días' }}</div>
            <div class="kpi-desc">Total DAF</div>
          </td>
        @endif
      </tr>
    </table>

    <!-- ============================================================ -->
    <!-- 1. TABLA: DETALLE DE ATRASOS Y SANCIONES (ART. 45.I)          -->
    <!-- ============================================================ -->
    @if($categoria === 'todos' || $categoria === 'atrasos')
      <div class="section-header section-header-amber">
        1. Detalle de Atrasos Sancionables y Días a Descontar (Artículo 45.I - Exceso de Tolerancia)
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 130px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 60px;" class="text-center">Atraso</th>
            <th style="width: 65px;" class="text-center">Días Tarde</th>
            <th style="width: 75px;" class="text-center">Descuento</th>
            <th>Detalle de Fechas (Minutos por día)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['detalle_atrasos'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td class="text-center font-bold" style="color: #92400e;">
                {{ $item['minutos_texto'] }}
              </td>
              <td class="text-center font-bold" style="color: #0f172a;">
                {{ $item['dias_tarde_texto'] }}
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['dias_descuento_texto'] }}
              </td>
              <td class="fechas-list">
                {{ $item['fechas_texto'] }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
                No se registran funcionarios con sanciones por atraso en este periodo (todos se encuentran dentro de tolerancia).
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- ============================================================ -->
    <!-- 2. TABLA: DETALLE DE OMISIONES DE MARCACIÓN                  -->
    <!-- ============================================================ -->
    @if($categoria === 'todos' || $categoria === 'omisiones')
      <div class="section-header section-header-rose">
        2. Detalle de Omisiones de Marcación (Artículo 45.III y Artículo 48.IV)
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 130px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 65px;" class="text-center">Omisiones</th>
            <th style="width: 85px;" class="text-center">Descuento</th>
            <th>Detalle de Fechas de Omisión</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['detalle_omisiones'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['total_omisiones_texto'] }}
              </td>
              <td class="text-center font-bold" style="color: {{ $item['es_sancionado'] ? '#b91c1c' : '#475569' }};">
                {{ $item['dias_descuento_texto'] }}
              </td>
              <td class="fechas-list">
                {{ $item['fechas_texto'] }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center" style="padding: 10px; color: #64748b;">
                No se registran funcionarios con omisiones de marcación en este periodo.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- ============================================================ -->
    <!-- 3. TABLA: DETALLE DE FALTAS E INASISTENCIAS (ART. 45.II / 48) -->
    <!-- ============================================================ -->
    @if($categoria === 'todos' || $categoria === 'faltas')
      <div class="section-header section-header-red">
        3. Detalle de Faltas e Inasistencias (Artículo 45.II y Artículo 48.II/III)
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 125px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 60px;" class="text-center">Faltas</th>
            <th style="width: 80px;" class="text-center">Descuento (Doble)</th>
            <th style="width: 100px;">Causal Disciplinaria</th>
            <th>Fechas de Inasistencia</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['detalle_faltas'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['total_faltas_texto'] }}
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['dias_descuento_texto'] }}
              </td>
              <td>
                @if(!empty($item['es_critico']))
                  <span class="badge badge-red">Art. 48 Destitución</span>
                @else
                  <span class="badge badge-amber">Art. 45.II Sanción</span>
                @endif
                <div style="font-size: 7px; color: #475569; margin-top: 1px;">{{ $item['causal_disciplinaria'] }}</div>
              </td>
              <td class="fechas-list">
                {{ $item['fechas_texto'] }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
                No se registran faltas o inasistencias injustificadas en este periodo.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- ============================================================ -->
    <!-- 4. TABLA: REPORTE DE ZONA DE PELIGRO Y ALERTAS PREVENTIVAS   -->
    <!-- ============================================================ -->
    @if($categoria === 'alertas' || ($categoria === 'todos' && count($reporte['personal_en_alerta'] ?? []) > 0))
      <div class="section-header section-header-amber">
        4. Personal en Zona de Peligro y Alertas Preventivas (Art. 45 y Art. 48)
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 125px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 65px;">Artículo</th>
            <th style="width: 100px;">Margen Restante</th>
            <th>Diagnóstico Preventivo y Sanción Inminente</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['personal_en_alerta'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td>
                @if(!empty($item['es_concurrente']))
                  <span class="badge badge-purple">Art. 45 + 48</span>
                @elseif(($item['articulo'] ?? '') === 'Art. 48')
                  <span class="badge badge-rose">Art. 48 Destitución</span>
                @else
                  <span class="badge badge-amber">Art. 45 Tolerancia</span>
                @endif
              </td>
              <td class="font-bold" style="color: #92400e;">
                {{ $item['distancia_umbral'] ?? 'Próximo al límite' }}
              </td>
              <td class="fechas-list">
                <strong>{{ $item['motivo_principal'] ?? '' }}</strong>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center" style="padding: 10px; color: #64748b;">
                Ningún funcionario se encuentra en zona de peligro o alerta preventiva en este periodo.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- ============================================================ -->
    <!-- 5. TABLA: REPORTE DE REINCIDENTES (>30 MIN EN >2 MESES / OMIS.) -->
    <!-- ============================================================ -->
    @if($categoria === 'todos' || $categoria === 'reincidentes')
      <div class="section-header section-header-purple">
        5. Reporte de Reincidentes (Gestión {{ $reporte['gestion'] ?? date('Y') }})
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 125px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 100px;">Tipo de Reincidencia</th>
            <th style="width: 75px;" class="text-center">Frecuencia</th>
            <th style="width: 75px;" class="text-center">Descuento Actual</th>
            <th>Detalle de Fechas y Meses Afectados</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['detalle_reincidentes'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td>
                @if($item['tipo'] === 'atrasos')
                  <span class="badge badge-amber">{{ $item['tipo_etiqueta'] }}</span>
                @else
                  <span class="badge badge-rose">{{ $item['tipo_etiqueta'] }}</span>
                @endif
              </td>
              <td class="text-center font-bold" style="color: #0f172a;">
                {{ $item['frecuencia'] }}
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['sancion_texto'] }}
              </td>
              <td class="fechas-list">
                @if($item['tipo'] === 'atrasos')
                  <div><strong>Meses > 30 min:</strong> {{ $item['detalle_texto'] }}</div>
                  <div><strong>Fechas mes actual:</strong> {{ $item['fechas_texto'] }}</div>
                @else
                  <div><strong>Fechas omisiones:</strong> {{ $item['fechas_texto'] }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
                No se registran funcionarios reincidentes en el periodo evaluado.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- ============================================================ -->
    <!-- 6. TABLA: CASOS CON CONCURRENCIA DE LEYES (ART. 45 + 48)     -->
    <!-- ============================================================ -->
    @if($categoria === 'concurrente' || ($categoria === 'todos' && count($reporte['concurrentes'] ?? []) > 0))
      <div class="section-header section-header-purple">
        6. Casos con Concurrencia de Leyes (Art. 45 y Art. 48 Simultáneos)
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 125px;">Nombre</th>
            <th style="width: 60px;">Código</th>
            <th style="width: 80px;">Sucursal / Área</th>
            <th style="width: 65px;" class="text-center">Atraso</th>
            <th style="width: 55px;" class="text-center">Faltas</th>
            <th style="width: 55px;" class="text-center">Omis.</th>
            <th style="width: 80px;" class="text-center">Total Deducible</th>
            <th>Diagnóstico Disciplinario Concurrente</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reporte['concurrentes'] ?? [] as $i => $item)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $item['nombre'] }}</td>
              <td class="font-mono font-bold">{{ $item['codigo'] }}</td>
              <td>{{ $item['sucursal'] }} - {{ $item['area'] }}</td>
              <td class="text-center font-bold" style="color: #92400e;">
                {{ $item['minutos_atraso'] ?? 0 }} min
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['faltas'] ?? 0 }}
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['omisiones'] ?? 0 }}
              </td>
              <td class="text-center font-bold" style="color: #b91c1c;">
                {{ $item['total_dias_sancion_texto'] }}
              </td>
              <td class="fechas-list">
                @if(!empty($item['es_destitucion']))
                  <span class="badge badge-rose">Destitución (Art. 48)</span>
                @else
                  <span class="badge badge-amber">Descuento Salarial (Art. 45)</span>
                @endif
                <div style="margin-top: 2px;">{{ $item['infraccion_art48'] ?? $item['resumen_sancion'] }}</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center" style="padding: 10px; color: #64748b;">
                No se registran casos con concurrencia normativa en este periodo.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <!-- Firmas Formales -->
    <table class="footer-signatures">
      <tr>
        <td style="width: 50%;">
          <div class="sig-line">
            Responsable de Recursos Humanos<br>
            Agencia Boliviana de Correos
          </div>
        </td>
        <td style="width: 50%;">
          <div class="sig-line">
            Dirección Administrativa Financiera<br>
            Agencia Boliviana de Correos
          </div>
        </td>
      </tr>
    </table>

  </body>
</html>
