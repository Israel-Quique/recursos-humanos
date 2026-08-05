<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Modulo mensual</p>
        <h3 class="section-title">No marcados y personal atrasado</h3>
        <p class="section-copy-sm">Consulta consolidada del mes: {{ $monthLabel }}.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
      <div>
        <label class="form-label" for="monthly-report-reference-month">Mes de reporte</label>
        <input id="monthly-report-reference-month" type="month" wire:model.live="referenceMonth" class="form-input">
      </div>
      <div>
        <label class="form-label" for="monthly-report-branch">Sucursal</label>
        <select id="monthly-report-branch" wire:model.live="selectedBranch" class="form-input">
          <option value="">Todas las sucursales</option>
          @foreach($branches as $branch)
            <option value="{{ $branch }}">{{ $branch }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </section>

  <section class="metric-grid metric-grid-4">
    @foreach($report['metrics'] as $metric)
      <article class="metric-card metric-card-centered">
        <p class="metric-label">{{ $metric['label'] }}</p>
        <strong class="metric-value">{{ $metric['value'] }}</strong>
      </article>
    @endforeach
  </section>

  <section class="surface-card">
    <div class="flex flex-wrap gap-3">
      <button
        type="button"
        wire:click="showTable('resumen_atrasos')"
        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $activeTable === 'resumen_atrasos' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}"
      >
        Resumen de atrasos
      </button>
      <button
        type="button"
        wire:click="showTable('atrasos')"
        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $activeTable === 'atrasos' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}"
      >
        Detalle de atrasos
      </button>
      <button
        type="button"
        wire:click="showTable('no_marcados')"
        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $activeTable === 'no_marcados' ? 'bg-slate-900 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}"
      >
        No marcados
      </button>
    </div>

    @if($activeTable === 'resumen_atrasos')
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Resumen de atrasos</p>
          <h3 class="section-title">Personal que se atraso en el mes</h3>
        </div>
        <p class="section-copy-sm">
          {{ $monthLabel }} · {{ $lateSummaryPagination['from'] }}-{{ $lateSummaryPagination['to'] }} de {{ $lateSummaryPagination['total'] }}
        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Dias tarde</th>
              <th>Retraso acumulado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($report['resumen_atrasos'] as $item)
              <tr>
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['sucursal'] }}</td>
                <td>{{ $item['dias_tarde'] }}</td>
                <td>{{ $item['retraso'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-slate-400">No hay atrasos registrados en el mes seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($lateSummaryPagination['last_page'] > 1)
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina {{ $lateSummaryPagination['page'] }} de {{ $lateSummaryPagination['last_page'] }}</span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToLateSummaryPage({{ $lateSummaryPagination['page'] - 1 }})" @disabled($lateSummaryPagination['page'] === 1) class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToLateSummaryPage({{ $lateSummaryPagination['page'] + 1 }})" @disabled($lateSummaryPagination['page'] === $lateSummaryPagination['last_page']) class="table-action-button">Siguiente</button>
          </div>
        </div>
      @endif
    @endif

    @if($activeTable === 'atrasos')
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Detalle de atrasos</p>
          <h3 class="section-title">Marcaciones tardias del mes</h3>
        </div>
        <p class="section-copy-sm">
          {{ $lateDetailsPagination['from'] }}-{{ $lateDetailsPagination['to'] }} de {{ $lateDetailsPagination['total'] }} registros
        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Hora programada</th>
              <th>Hora marcada</th>
              <th>Retraso</th>
            </tr>
          </thead>
          <tbody>
            @forelse($report['atrasos'] as $item)
              <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['sucursal'] }}</td>
                <td>{{ $item['entrada_programada'] }}</td>
                <td>{{ $item['entrada_real'] }}</td>
                <td>{{ $item['retraso'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400">No existen atrasos en el rango seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($lateDetailsPagination['last_page'] > 1)
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina {{ $lateDetailsPagination['page'] }} de {{ $lateDetailsPagination['last_page'] }}</span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToLateDetailsPage({{ $lateDetailsPagination['page'] - 1 }})" @disabled($lateDetailsPagination['page'] === 1) class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToLateDetailsPage({{ $lateDetailsPagination['page'] + 1 }})" @disabled($lateDetailsPagination['page'] === $lateDetailsPagination['last_page']) class="table-action-button">Siguiente</button>
          </div>
        </div>
      @endif
    @endif

    @if($activeTable === 'no_marcados')
      <div class="history-header mt-8">
        <div>
          <p class="section-kicker">Detalle de no marcados</p>
          <h3 class="section-title">Registros incompletos del mes</h3>
        </div>
        <p class="section-copy-sm">
          {{ $forgotMarksPagination['from'] }}-{{ $forgotMarksPagination['to'] }} de {{ $forgotMarksPagination['total'] }} registros
        </p>
      </div>

      <div class="history-table-shell mt-8">
        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Observacion</th>
            </tr>
          </thead>
          <tbody>
            @forelse($report['no_marcados'] as $item)
              <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['sucursal'] }}</td>
                <td>{{ $item['entrada'] }}</td>
                <td>{{ $item['salida'] }}</td>
                <td>{{ $item['detalle'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400">No hay registros no marcados en el mes seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($forgotMarksPagination['last_page'] > 1)
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
          <span>Pagina {{ $forgotMarksPagination['page'] }} de {{ $forgotMarksPagination['last_page'] }}</span>
          <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="goToForgotMarksPage({{ $forgotMarksPagination['page'] - 1 }})" @disabled($forgotMarksPagination['page'] === 1) class="table-action-button">Anterior</button>
            <button type="button" wire:click="goToForgotMarksPage({{ $forgotMarksPagination['page'] + 1 }})" @disabled($forgotMarksPagination['page'] === $forgotMarksPagination['last_page']) class="table-action-button">Siguiente</button>
          </div>
        </div>
      @endif
    @endif
  </section>
</div>
