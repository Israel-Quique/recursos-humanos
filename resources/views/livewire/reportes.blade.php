<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Filtro de reportes</p>
        <h3 class="section-title">Consulta por mes, sucursal y personal</h3>
        <p class="section-copy-sm">Resumen mensual cargado: {{ $monthLabel }}.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div>
        <label class="form-label" for="report-reference-month">Mes de reporte</label>
        <input id="report-reference-month" type="month" wire:model.live="referenceMonth" class="form-input">
      </div>
      <div>
        <label class="form-label" for="report-branch">Sucursal</label>
        <select id="report-branch" wire:model.live="selectedBranch" class="form-input">
          <option value="">Todas las sucursales</option>
          @foreach($branches as $branch)
            <option value="{{ $branch }}">{{ $branch }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label" for="report-employee">Personal</label>
        <select id="report-employee" wire:model.live="selectedEmployeeId" class="form-input">
          <option value="">Todos / sin seleccionar</option>
          @foreach($employees as $employee)
            <option value="{{ $employee['id'] }}">{{ $employee['nombre'] }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </section>

  <section class="metric-grid metric-grid-3">
    @foreach($metrics as $metric)
      <article class="metric-card metric-card-centered">
        <span class="metric-icon metric-icon-{{ $metric['tone'] }}"></span>
        <p class="metric-label mt-6">{{ $metric['label'] }}</p>
        <strong class="metric-value">{{ $metric['value'] }}</strong>
        <p class="metric-copy">{{ $metric['detail'] }}</p>
      </article>
    @endforeach
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Reporte mensual</p>
        <h3 class="section-title">Frecuencias y cierre del mes</h3>
      </div>
      <p class="section-copy-sm">{{ $monthLabel }}</p>
    </div>

    <div class="attendance-chart">
      @foreach($frequency as $bar)
        <div class="attendance-bar-group">
          <div class="attendance-bar {{ $bar['active'] ? 'attendance-bar-active' : '' }}" style="height: {{ $bar['height'] }};"></div>
          <span class="attendance-bar-label {{ $bar['active'] ? 'attendance-bar-label-active' : '' }}">{{ $bar['label'] }}</span>
          <span class="mt-2 text-xs text-slate-400">{{ $bar['count'] }}</span>
        </div>
      @endforeach
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      @foreach($monthlyReport['metrics'] as $metric)
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">{{ $metric['label'] }}</p>
          <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $metric['value'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="mt-8 rounded-[1.4rem] border border-slate-200 bg-white px-5 py-5">
      <div class="flex items-center justify-between gap-4">
        <h4 class="text-lg font-semibold text-slate-900">Personal con mayor retraso del mes</h4>
        <span class="status-badge status-warning">{{ $monthlyReport['late_days'] }} dias tarde</span>
      </div>

      <div class="report-scroll-list mt-5 space-y-3">
        @forelse($monthlyReport['top_employees'] as $employee)
          <div class="rounded-xl bg-slate-50 px-4 py-3">
            <p class="font-semibold text-slate-900">{{ $employee['nombre'] }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $employee['sucursal'] }} | {{ $employee['dias_tarde'] }} dias tarde | {{ $employee['retraso'] }}</p>
          </div>
        @empty
          <p class="text-sm text-slate-400">No hay retrasos acumulados en el mes seleccionado.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Incidencias filtradas</p>
        <h3 class="section-title">Incidencias del mes seleccionado</h3>
      </div>
      <p class="section-copy-sm">{{ $monthLabel }}</p>
    </div>

    <div class="diagnostic-grid mt-8">
      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Incidencias justificadas</h4>
          <span class="status-badge status-available">{{ count($incidents['permisos']) }}</span>
        </div>
        <div class="report-scroll-list mt-4 space-y-3">
          @forelse($incidents['permisos'] as $item)
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
              <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
            </div>
          @empty
            <p class="text-sm text-slate-400">No hay incidencias justificadas en el rango.</p>
          @endforelse
        </div>
      </div>

      <div class="diagnostic-card">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Ausencias injustificadas</h4>
          <span class="status-badge status-warning">{{ count($incidents['faltas']) }}</span>
        </div>
        <div class="report-scroll-list mt-4 space-y-3">
          @forelse($incidents['faltas'] as $item)
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
              <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
            </div>
          @empty
            <p class="text-sm text-slate-400">No hay faltas injustificadas en el rango.</p>
          @endforelse
        </div>
      </div>

      <div class="diagnostic-card xl:col-span-2">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-base font-semibold text-slate-900">Olvidos de marcar</h4>
          <span class="status-badge status-danger">{{ count($incidents['olvidos']) }}</span>
        </div>
        <div class="report-scroll-list mt-4 grid gap-4 md:grid-cols-2">
          @forelse($incidents['olvidos'] as $item)
            <div class="rounded-xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ $item['nombre'] }}</p>
              <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
            </div>
          @empty
            <p class="text-sm text-slate-400">No existen olvidos de marcacion en el rango.</p>
          @endforelse
        </div>
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Reporte personalizado</p>
        <h3 class="section-title">Detalle por personal</h3>
      </div>
      <p class="section-copy-sm">
        @if($personalReport)
          {{ $personalReport['empleado']['nombre'] }} | {{ $monthLabel }}
        @else
          Selecciona un personal para ver su reporte individual del mes.
        @endif
      </p>
    </div>

    @if($personalReport)
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Codigo</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['codigo'] }}</p>
        </div>
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Sucursal</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['sucursal'] }}</p>
        </div>
        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4 md:col-span-2">
          <p class="metric-label">Horario programado</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['horario'] }}</p>
        </div>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        @foreach($personalReport['metrics'] as $metric)
          <div class="rounded-[1.2rem] border border-slate-200 bg-white px-5 py-4">
            <p class="metric-label">{{ $metric['label'] }}</p>
            <p class="mt-3 text-xl font-semibold text-slate-900">{{ $metric['value'] }}</p>
          </div>
        @endforeach
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Horas</th>
              <th>Retraso</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($personalReport['rows'] as $row)
              <tr>
                <td>{{ $row['fecha'] }}</td>
                <td>{{ $row['entrada'] }}</td>
                <td>{{ $row['salida'] }}</td>
                <td>{{ $row['horas'] }}</td>
                <td>{{ $row['retraso'] }}</td>
                <td>{{ $row['estado'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400">No hay registros del personal en el rango seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @else
      <div class="mt-8 rounded-[1.4rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-500">
        Elige un personal en el filtro superior para cargar su reporte individual por fechas.
      </div>
    @endif
  </section>
</div>
