<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte Disciplinario de Reglamento y Sanciones</title>
    <style>
      @page {
        margin: 18mm 15mm 18mm 15mm;
        size: A4 portrait;
      }
      body {
        font-family: DejaVu Sans, sans-serif;
        color: #0f172a;
        margin: 0;
        padding: 0;
        font-size: 9px;
        line-height: 1.35;
      }
      h1, h2, h3, h4, p { margin: 0; }
      
      .header-table {
        width: 100%;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 8px;
        margin-bottom: 12px;
      }
      .org-name {
        font-size: 8px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
      }
      .report-title {
        font-size: 16px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .report-subtitle {
        font-size: 9px;
        color: #475569;
        margin-top: 2px;
      }
      .meta-box {
        text-align: right;
        font-size: 8.5px;
        color: #475569;
      }
      
      /* Tarjetas de Resumen */
      .kpi-table {
        width: 100%;
        margin-bottom: 14px;
        border-collapse: separate;
        border-spacing: 6px 0;
      }
      .kpi-card {
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        padding: 8px 10px;
        text-align: center;
        border-radius: 4px;
      }
      .kpi-card-alert {
        border-color: #fde68a;
        background-color: #fefce8;
      }
      .kpi-card-danger {
        border-color: #fecdd3;
        background-color: #fff1f2;
      }
      .kpi-card-purple {
        border-color: #ddd6fe;
        background-color: #f5f3ff;
      }
      .kpi-title {
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
      }
      .kpi-value {
        font-size: 16px;
        font-weight: bold;
        color: #0f172a;
        margin-top: 3px;
      }
      .kpi-desc {
        font-size: 7.5px;
        color: #64748b;
        margin-top: 2px;
      }

      /* Secciones y Tablas */
      .section-header {
        background-color: #f1f5f9;
        border-left: 3px solid #0f172a;
        padding: 4px 8px;
        margin-top: 14px;
        margin-bottom: 6px;
        font-size: 10px;
        font-weight: bold;
        color: #0f172a;
      }
      .section-kicker {
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        color: #64748b;
      }
      
      .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
      }
      .data-table th {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        color: #334155;
        text-align: left;
      }
      .data-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        font-size: 8px;
        color: #1e293b;
      }
      .text-center { text-align: center; }
      .text-right { text-align: right; }
      .font-bold { font-weight: bold; }
      .font-mono { font-family: monospace; }
      
      .badge {
        display: inline-block;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 7.5px;
        font-weight: bold;
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
      .badge-purple {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #d8b4fe;
      }
      
      .footer-signatures {
        margin-top: 28px;
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

    <!-- Encabezado Formal -->
    <table class="header-table">
      <tr>
        <td style="width: 65%;">
          <div class="org-name">Agencia Boliviana de Correos · Recursos Humanos</div>
          <div class="report-title">Informe Disciplinario y Control de Reglamento</div>
          <div class="report-subtitle">Evaluación de Sanciones, Alertas Preventivas y Reincidencias (Art. 45 y Art. 48)</div>
        </td>
        <td class="meta-box" style="width: 35%;">
          <div><strong>Periodo:</strong> {{ $monthLabel }}</div>
          <div><strong>Sucursal:</strong> {{ $branchLabel }}</div>
          <div><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </td>
      </tr>
    </table>

    <!-- Resumen Ejecutivo Superior con 5 KPIs -->
    <table class="kpi-table">
      <tr>
        <td class="kpi-card kpi-card-alert" style="width: 20%;">
          <div class="kpi-title">Zona de Alerta</div>
          <div class="kpi-value">{{ $reporte['metricas']['en_alerta_preventiva'] ?? 0 }}</div>
          <div class="kpi-desc">A punto de ser sancionados</div>
        </td>
        <td class="kpi-card" style="width: 20%;">
          <div class="kpi-title">Con Sanción (Art. 45)</div>
          <div class="kpi-value">{{ $reporte['metricas']['con_sancion_economica'] ?? 0 }}</div>
          <div class="kpi-desc">Descuento en planilla</div>
        </td>
        <td class="kpi-card kpi-card-danger" style="width: 20%;">
          <div class="kpi-title">Riesgo Crítico (Art. 48)</div>
          <div class="kpi-value">{{ $reporte['metricas']['riesgo_critico'] ?? 0 }}</div>
          <div class="kpi-desc">Faltas gravísimas</div>
        </td>
        <td class="kpi-card kpi-card-purple" style="width: 20%;">
          <div class="kpi-title">Concurrencia (45+48)</div>
          <div class="kpi-value">{{ $reporte['metricas']['concurrencia_articulos'] ?? 0 }}</div>
          <div class="kpi-desc">Infringe ambos artículos</div>
        </td>
        <td class="kpi-card" style="width: 20%;">
          <div class="kpi-title">Días a Deducir</div>
          <div class="kpi-value">{{ $reporte['metricas']['total_dias_sancion_formato'] ?? '0 días' }}</div>
          <div class="kpi-desc">Cómputo DAF</div>
        </td>
      </tr>
    </table>

    <!-- Nota de Auditoría Normativa y Contingencias -->
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-left: 3px solid #2563eb; padding: 6px 10px; border-radius: 4px; margin-bottom: 12px; font-size: 8px; color: #1e3a8a;">
      <strong>NOTA DE AUDITORÍA Y CONTINGENCIAS ADMINISTRATIVAS:</strong> Debido a bloqueos de vías, conflictos de transporte y feriados o tolerancias que pudieran no estar registrados oportunamente en la base de datos, los casos con concurrencia de artículos o ausencias sucesivas deben ser auditados cotejando boletas físicas de justificación y reportes de jefatura inmediata antes de proceder con sanciones definitivas o procesos de destitución.
    </div>

    <!-- SECCIÓN 1: PERSONAL EN ZONA DE ALERTA (A PUNTO DE SER SANCIONADO) -->
    <div class="section-header">
      <span class="section-kicker">Prevención y Control Temprano · </span>
      1. Personal a Punto de ser Sancionado (Zona de Alerta)
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 20px;" class="text-center">#</th>
          <th>Personal</th>
          <th style="width: 55px;">CI / Código</th>
          <th>Sucursal / Área</th>
          <th style="width: 65px;" class="text-center">Tolerancia Usada</th>
          <th style="width: 65px;" class="text-center">Falta p/ Sanción</th>
          <th>Sanción Inminente / Motivo</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reporte['personal_en_alerta'] ?? [] as $i => $emp)
          <tr>
            <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
            <td class="font-bold">{{ $emp['nombre'] }}</td>
            <td class="font-mono">{{ $emp['codigo'] }}</td>
            <td>{{ $emp['sucursal'] }} - {{ $emp['area'] }}</td>
            <td class="text-center font-bold" style="color: #92400e;">
              {{ $emp['minutos_atraso'] }} min
            </td>
            <td class="text-center font-bold" style="color: #b91c1c;">
              {{ $emp['distancia_umbral'] }}
            </td>
            <td>{{ $emp['motivo_principal'] }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center" style="padding: 10px; color: #64748b;">
              No se registran funcionarios en zona de riesgo preventivo en este periodo.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- SECCIÓN 2: PERSONAL MÁS SANCIONADO (TOP SANCIONADOS) -->
    <div class="section-header">
      <span class="section-kicker">Descuentos por Infracción Administrativa (Art. 45) · </span>
      2. Personal Sancionado con Descuento en Planilla
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 20px;" class="text-center">#</th>
          <th>Personal Sancionado</th>
          <th style="width: 55px;">CI / Código</th>
          <th>Sucursal / Área</th>
          <th style="width: 65px;" class="text-center">Atraso Acumulado</th>
          <th style="width: 45px;" class="text-center">Omisiones</th>
          <th style="width: 75px;" class="text-center">Días a Descontar</th>
          <th>Desglose Oficial</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reporte['mas_sancionados'] ?? [] as $i => $sancionado)
          <tr>
            <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
            <td class="font-bold">
              {{ $sancionado['nombre'] }}
              @if($sancionado['es_destitucion'])
                <span class="badge badge-rose">Art. 48</span>
              @endif
            </td>
            <td class="font-mono">{{ $sancionado['codigo'] }}</td>
            <td>{{ $sancionado['sucursal'] }} - {{ $sancionado['area'] }}</td>
            <td class="text-center font-bold" style="color: #92400e;">
              {{ $sancionado['minutos_atraso'] > 0 ? $sancionado['minutos_atraso'] . ' min' : '0 min' }}
            </td>
            <td class="text-center">{{ $sancionado['omisiones'] }}</td>
            <td class="text-center font-bold" style="color: #b91c1c;">
              {{ $sancionado['es_destitucion'] ? 'Destitución' : $sancionado['total_dias_sancion_texto'] }}
            </td>
            <td>{{ implode('; ', $sancionado['desglose']) ?: 'Sin descuento directo' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
              No se registraron sanciones aplicables en el periodo seleccionado.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- SECCIÓN 3: CONCURRENCIA DE LEYES Y PROPUESTA DE RESOLUCIÓN -->
    @if(count($reporte['concurrentes'] ?? []) > 0)
      <div class="section-header" style="background-color: #f5f3ff; border-left-color: #7c3aed;">
        <span class="section-kicker" style="color: #6d28d9;">Concurrencia Normativa (Art. 45 y Art. 48) · </span>
        3. Casos con Doble Infracción y Propuesta de Resolución Administrativa
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 20px;" class="text-center">#</th>
            <th style="width: 130px;">Personal</th>
            <th style="width: 60px;">CI / Código</th>
            <th style="width: 100px;">Sucursal / Área</th>
            <th style="width: 150px;">Infracciones Detectadas</th>
            <th>Propuesta Administrativa de Resolución</th>
          </tr>
        </thead>
        <tbody>
          @foreach($reporte['concurrentes'] as $i => $concurrente)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">
                {{ $concurrente['nombre'] }}
                <span class="badge badge-purple" style="margin-top: 2px; display: block; width: fit-content;">Art. 45 + 48</span>
              </td>
              <td class="font-mono">{{ $concurrente['codigo'] }}</td>
              <td>{{ $concurrente['sucursal'] }}<br><span style="color: #64748b;">{{ $concurrente['area'] }}</span></td>
              <td>
                <div style="color: #92400e; font-size: 7.5px; margin-bottom: 2px;"><strong>• Art. 45:</strong> {{ $concurrente['infraccion_art45'] }}</div>
                <div style="color: #9f1239; font-size: 7.5px;"><strong>• Art. 48:</strong> {{ $concurrente['infraccion_art48'] }}</div>
              </td>
              <td style="background-color: #faf5ff; color: #4c1d95; font-size: 8px;">
                {{ $concurrente['propuesta_resolucion'] }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <!-- SECCIÓN 4: CASOS CRÍTICOS Y REINCIDENTES (ART. 48) -->
    @if(count($reporte['casos_criticos'] ?? []) > 0 || count($reporte['reincidentes'] ?? []) > 0)
      <div class="section-header">
        <span class="section-kicker">Normativa Disciplinaria Art. 48 · </span>
        4. Casos Críticos y Reincidentes (Gestión {{ $reporte['gestion'] ?? '' }})
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 25px;" class="text-center">#</th>
            <th>Personal</th>
            <th style="width: 60px;">CI / Código</th>
            <th>Sucursal / Área</th>
            <th style="width: 90px;" class="text-center">Condición Legal</th>
            <th>Causal / Detalle de Reincidencia</th>
          </tr>
        </thead>
        <tbody>
          @foreach($reporte['casos_criticos'] ?? [] as $i => $critico)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ $i + 1 }}</td>
              <td class="font-bold">{{ $critico['nombre'] }}</td>
              <td class="font-mono">{{ $critico['codigo'] }}</td>
              <td>{{ $critico['sucursal'] }} - {{ $critico['area'] }}</td>
              <td class="text-center">
                <span class="badge badge-rose">Destitución (Art. 48)</span>
              </td>
              <td class="font-bold" style="color: #9f1239;">{{ $critico['causal_principal'] }}</td>
            </tr>
          @endforeach

          @foreach($reporte['reincidentes'] ?? [] as $i => $reincidente)
            <tr>
              <td class="text-center font-bold" style="color: #64748b;">{{ count($reporte['casos_criticos'] ?? []) + $i + 1 }}</td>
              <td class="font-bold">{{ $reincidente['nombre'] }}</td>
              <td class="font-mono">{{ $reincidente['codigo'] }}</td>
              <td>{{ $reincidente['sucursal'] }} - {{ $reincidente['area'] }}</td>
              <td class="text-center">
                <span class="badge badge-amber">{{ $reincidente['meses_graves_gestion'] }}x en gestión</span>
              </td>
              <td>{{ $reincidente['detalle_reincidencia'] }}</td>
            </tr>
          @endforeach
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
