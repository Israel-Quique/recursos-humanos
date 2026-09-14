<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Reporte de Reglamento y Sanciones - {{ $monthLabel }}</title>
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
        font-size: 14px;
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
        font-size: 7.5px;
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
      .badge-purple {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #d8b4fe;
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
          <div class="report-title">Detalle de Atrasos, Omisiones y Sanciones de Reglamento</div>
          <div class="report-subtitle">Cómputo Oficial de Faltas, Descuentos de Haber y Control de Reincidencias (Art. 45 y Art. 48)</div>
        </td>
        <td class="meta-box" style="width: 35%;">
          <div><strong>Periodo:</strong> {{ $monthLabel }}</div>
          <div><strong>Sucursal:</strong> {{ $branchLabel }}</div>
          <div><strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </td>
      </tr>
    </table>

    <!-- Resumen Ejecutivo Superior (KPIs) -->
    <table class="kpi-table">
      <tr>
        <td class="kpi-card kpi-card-alert" style="width: 25%;">
          <div class="kpi-title">Personal con Atraso</div>
          <div class="kpi-value">{{ $reporte['metricas']['total_con_atraso'] ?? count($reporte['detalle_atrasos'] ?? []) }}</div>
          <div class="kpi-desc">Art. 45.I (Escala de atrasos)</div>
        </td>
        <td class="kpi-card kpi-card-danger" style="width: 25%;">
          <div class="kpi-title">Personal con Omisión</div>
          <div class="kpi-value">{{ $reporte['metricas']['total_con_omision'] ?? count($reporte['detalle_omisiones'] ?? []) }}</div>
          <div class="kpi-desc">Art. 45.III / 48.IV</div>
        </td>
        <td class="kpi-card kpi-card-purple" style="width: 25%;">
          <div class="kpi-title">Reincidentes (>30 min / Omis.)</div>
          <div class="kpi-value">{{ $reporte['metricas']['total_reincidentes'] ?? count($reporte['detalle_reincidentes'] ?? []) }}</div>
          <div class="kpi-desc">Gestión {{ $reporte['gestion'] ?? date('Y') }}</div>
        </td>
        <td class="kpi-card" style="width: 25%;">
          <div class="kpi-title">Días a Descontar</div>
          <div class="kpi-value">{{ $reporte['metricas']['total_dias_sancion_formato'] ?? '0 días' }}</div>
          <div class="kpi-desc">Cómputo Total DAF</div>
        </td>
      </tr>
    </table>

    <!-- ============================================================ -->
    <!-- 1. TABLA: DETALLE DE ATRASOS Y SANCIONES (ART. 45.I)          -->
    <!-- ============================================================ -->
    <div class="section-header section-header-amber">
      1. Detalle de Atrasos y Días a Descontar (Art. 45.I)
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
            <td class="text-center font-bold" style="color: {{ $item['es_sancionado'] ? '#b91c1c' : '#059669' }};">
              {{ $item['dias_descuento_texto'] }}
            </td>
            <td class="fechas-list">
              {{ $item['fechas_texto'] }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
              No se registran funcionarios con atraso en este periodo.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- 2. TABLA: DETALLE DE OMISIONES DE MARCACIÓN                  -->
    <!-- ============================================================ -->
    <div class="section-header section-header-rose">
      2. Detalle de Omisiones de Marcación (Art. 45.III y Art. 48.IV)
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

    <!-- ============================================================ -->
    <!-- 3. TABLA: REPORTE DE REINCIDENTES (>30 MIN EN >2 MESES / OMIS.) -->
    <!-- ============================================================ -->
    <div class="section-header section-header-purple">
      3. Reporte de Reincidentes (Gestión {{ $reporte['gestion'] ?? date('Y') }})
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
