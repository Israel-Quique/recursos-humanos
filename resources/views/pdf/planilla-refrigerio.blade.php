<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Planilla de Descuento de Refrigerio - {{ $periodoLabel }}</title>
  <style>
    @page {
      margin: 18px 20px 20px 20px;
      size: a4 landscape;
    }
    body {
      font-family: DejaVu Sans, sans-serif;
      color: #0f172a;
      font-size: 8px;
      line-height: 1.3;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border-bottom: 2px solid #1e3a8a;
      padding-bottom: 6px;
    }
    .org-title {
      font-size: 8px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #1e3a8a;
      font-weight: bold;
    }
    .report-title {
      font-size: 12px;
      font-weight: bold;
      color: #0f172a;
      margin: 2px 0;
    }
    .report-subtitle {
      font-size: 7.5px;
      color: #475569;
    }
    .meta-box {
      text-align: right;
      font-size: 8px;
    }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 7.5px;
    }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-rose { background: #ffe4e6; color: #9f1239; }

    /* Métricas ejecutivas */
    .metrics-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .metrics-table td {
      padding: 4px 6px;
      border: 1px solid #cbd5e1;
      text-align: center;
      background: #f8fafc;
    }
    .metrics-label {
      font-size: 6.5px;
      text-transform: uppercase;
      color: #64748b;
      font-weight: bold;
    }
    .metrics-val {
      font-size: 11px;
      font-weight: bold;
      color: #0f172a;
      margin-top: 1px;
    }

    /* Tabla principal */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .data-table th {
      background: #1e3a8a;
      color: #ffffff;
      padding: 5px 4px;
      font-size: 7.5px;
      font-weight: bold;
      border: 1px solid #1e3a8a;
      text-align: center;
    }
    .data-table td {
      padding: 4px 4px;
      border: 1px solid #cbd5e1;
      font-size: 7.5px;
    }
    .data-table tr:nth-child(even) {
      background: #f8fafc;
    }
    .data-table tr.has-discount {
      background: #fff1f2;
    }
    .total-row td {
      background: #f1f5f9;
      font-weight: bold;
      font-size: 8px;
      border-top: 2px solid #0f172a;
      border-bottom: 2px solid #0f172a;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: bold; }
    .text-rose { color: #991b1b; }
    .text-amber { color: #b45309; }

    /* Firmas */
    .signatures {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    .signatures td {
      width: 33.33%;
      text-align: center;
      font-size: 7.5px;
      padding: 0 20px;
    }
    .sign-line {
      border-top: 1px solid #0f172a;
      padding-top: 4px;
      font-weight: bold;
    }
    .footer-note {
      margin-top: 10px;
      font-size: 6.5px;
      color: #94a3b8;
      text-align: right;
    }
  </style>
</head>
<body>

  {{-- Membrete Oficial --}}
  <table class="header-table">
    <tr>
      <td style="width: 70%;">
        <div class="org-title">Agencia Boliviana de Correos · Recursos Humanos</div>
        <div class="report-title">PLANILLA DE DÍAS QUE NO CORRESPONDE PAGAR REFRIGERIO / COMIDA</div>
        <div class="report-subtitle">
          Control institucional de inasistencias, omisiones de registro, bajas médicas y comisiones de viaje
        </div>
      </td>
      <td class="meta-box" style="width: 30%;">
        <div><strong>Período:</strong> <span class="badge badge-blue">{{ $periodoLabel }}</span></div>
        <div style="margin-top: 2px;"><strong>Sucursal:</strong> {{ $sucursalLabel }}</div>
        <div style="margin-top: 2px;"><strong>Tarifa:</strong> <span class="badge badge-rose">Bs. {{ number_format($tarifaDiaria, 2) }} / día</span></div>
        <div style="margin-top: 2px; color: #64748b; font-size: 7px;">Emisión: {{ $emision }}</div>
      </td>
    </tr>
  </table>

  {{-- Cuadro de Resumen Ejecutivo --}}
  <table class="metrics-table">
    <tr>
      <td>
        <div class="metrics-label">Total Personal</div>
        <div class="metrics-val">{{ $metricas['total_personal'] }}</div>
      </td>
      <td>
        <div class="metrics-label">Personal con Descuento</div>
        <div class="metrics-val" style="color: #b91c1c;">{{ $metricas['personal_con_descuento'] }}</div>
      </td>
      <td>
        <div class="metrics-label">Faltas (Días)</div>
        <div class="metrics-val" style="color: #991b1b;">{{ $metricas['total_faltas'] }}</div>
      </td>
      <td>
        <div class="metrics-label">Omisiones (Días)</div>
        <div class="metrics-val" style="color: #d97706;">{{ $metricas['total_omisiones'] }}</div>
      </td>
      <td>
        <div class="metrics-label">Bajas Médicas (Días)</div>
        <div class="metrics-val" style="color: #2563eb;">{{ $metricas['total_bajas_medicas'] }}</div>
      </td>
      <td>
        <div class="metrics-label">Comisión Viaje (Días)</div>
        <div class="metrics-val" style="color: #7c3aed;">{{ $metricas['total_comisiones_viaje'] }}</div>
      </td>
      <td style="background: #e2e8f0;">
        <div class="metrics-label" style="color: #0f172a;">Gran Total Días Desc.</div>
        <div class="metrics-val" style="color: #0f172a;">{{ $metricas['gran_total_dias'] }} días</div>
      </td>
      <td style="background: #fee2e2;">
        <div class="metrics-label" style="color: #991b1b;">Gran Total a No Pagar</div>
        <div class="metrics-val" style="color: #991b1b;">Bs. {{ number_format($metricas['gran_total_monto'], 2) }}</div>
      </td>
    </tr>
  </table>

  {{-- Tabla de Datos Detallada --}}
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 20px;">N°</th>
        <th style="width: 50px;">Código</th>
        <th class="text-left" style="width: 170px;">Funcionario</th>
        <th class="text-left" style="width: 110px;">Cargo / Área</th>
        <th style="width: 65px;">Sucursal</th>
        <th style="width: 45px; background: #881337;">Faltas<br>(Días)</th>
        <th style="width: 50px; background: #78350f;">Omisiones<br>(Días)</th>
        <th style="width: 55px; background: #1e3a8a;">Bajas Méd.<br>(Días)</th>
        <th style="width: 55px; background: #4c1d95;">Comisión<br>(Días)</th>
        <th style="width: 55px; background: #0f172a;">Total Días<br>Descuento</th>
        <th style="width: 50px; background: #0f172a;">Tarifa<br>(Bs./Día)</th>
        <th style="width: 70px; background: #991b1b;">Total a No<br>Pagar (Bs.)</th>
        <th class="text-left">Observaciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $i => $item)
        @php
          $totalDias = (int) ($item['total_dias'] ?? 0);
          $hasDiscount = $totalDias > 0;
        @endphp
        <tr class="{{ $hasDiscount ? 'has-discount' : '' }}">
          <td class="text-center">{{ $i + 1 }}</td>
          <td class="text-center font-bold">{{ $item['codigo'] }}</td>
          <td class="text-left font-bold">{{ $item['nombre'] }}</td>
          <td class="text-left">{{ $item['cargo'] }}</td>
          <td class="text-center">{{ $item['sucursal'] }}</td>
          <td class="text-center font-bold {{ $item['faltas'] > 0 ? 'text-rose' : '' }}">{{ (int) ($item['faltas'] ?? 0) }}</td>
          <td class="text-center font-bold {{ $item['omisiones'] > 0 ? 'text-amber' : '' }}">{{ (int) ($item['omisiones'] ?? 0) }}</td>
          <td class="text-center font-bold">{{ (int) ($item['bajas_medicas'] ?? 0) }}</td>
          <td class="text-center font-bold">{{ (int) ($item['comisiones_viaje'] ?? 0) }}</td>
          <td class="text-center font-bold {{ $hasDiscount ? 'text-rose' : '' }}" style="background: {{ $hasDiscount ? '#fee2e2' : 'transparent' }};">
            {{ $totalDias }}
          </td>
          <td class="text-right">{{ number_format($item['tarifa_diaria'] ?? $tarifaDiaria, 2) }}</td>
          <td class="text-right font-bold {{ $hasDiscount ? 'text-rose' : '' }}" style="background: {{ $hasDiscount ? '#fecdd3' : 'transparent' }};">
            Bs. {{ number_format($item['total_monto'] ?? 0, 2) }}
          </td>
          <td class="text-left">{{ $item['observaciones'] ?? '' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="13" class="text-center" style="padding: 20px;">No se encontraron funcionarios registrados.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="5" class="text-right">TOTALES GENERALES:</td>
        <td class="text-center text-rose">{{ $metricas['total_faltas'] }}</td>
        <td class="text-center text-amber">{{ $metricas['total_omisiones'] }}</td>
        <td class="text-center">{{ $metricas['total_bajas_medicas'] }}</td>
        <td class="text-center">{{ $metricas['total_comisiones_viaje'] }}</td>
        <td class="text-center text-rose" style="background: #fecdd3; font-size: 9px;">{{ $metricas['gran_total_dias'] }} d</td>
        <td></td>
        <td class="text-right text-rose" style="background: #fecdd3; font-size: 9px;">Bs. {{ number_format($metricas['gran_total_monto'], 2) }}</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  {{-- Sección de Firmas Institucionales --}}
  <table class="signatures">
    <tr>
      <td>
        <div class="sign-line">
          ELABORADO POR<br>
          <span style="font-weight: normal; color: #475569;">Encargado de Asistencia y Planillas</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          REVISADO POR<br>
          <span style="font-weight: normal; color: #475569;">Jefatura de Recursos Humanos</span>
        </div>
      </td>
      <td>
        <div class="sign-line">
          APROBADO POR<br>
          <span style="font-weight: normal; color: #475569;">Dirección Administrativa Financiera</span>
        </div>
      </td>
    </tr>
  </table>

  <div class="footer-note">
    Documento oficial generado por el Sistema de Control de Recursos Humanos · Correos de Bolivia
  </div>

</body>
</html>
