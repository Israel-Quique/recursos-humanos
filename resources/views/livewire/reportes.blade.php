<div class="page-stack">
  @if ($showEmployeeDetailModal)
    <div class="app-modal-backdrop" wire:click="closeEmployeeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeEmployeeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Detalle mensual</p>
            <h3 class="section-title app-modal-title">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Detalle del personal' }}</h3>
            <p class="section-copy-sm">Revision puntual de tardanzas, no marcados y faltas del mes seleccionado.</p>
          </div>
          <div class="app-modal-actions">
            <button type="button" wire:click="descargarPdfDetalleEmpleado" class="table-action-button">
              <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
              </svg>
              <span>PDF</span>
            </button>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Codigo</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo' }}</p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
            <p class="metric-label">Sucursal</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal' }}</p>
          </div>
          <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4 md:col-span-2">
            <p class="metric-label">Horario</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</p>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
            <div class="rounded-[1.2rem] border border-slate-200 bg-white px-5 py-4">
              <p class="metric-label">{{ $metric['label'] }}</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ $metric['value'] }}</p>
            </div>
          @endforeach
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-3">
          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">Dias tarde</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $item)
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                  <p class="mt-1 text-sm text-slate-500">Entrada {{ $item['entrada'] }} | Retraso {{ $item['retraso'] }}</p>
                </div>
              @empty
                <p class="text-sm text-slate-400">No tiene tardanzas en el mes.</p>
              @endforelse
            </div>
          </div>

          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">No marcados</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              @forelse(($detailEmployeeReport['no_marcados'] ?? []) as $item)
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                  <p class="mt-1 text-sm text-slate-500">Entrada {{ $item['entrada'] }} | Salida {{ $item['salida'] }}</p>
                </div>
              @empty
                <p class="text-sm text-slate-400">No tiene no marcados en el mes.</p>
              @endforelse
            </div>
          </div>

          <div class="rounded-[1.3rem] border border-slate-200 bg-white px-5 py-5">
            <h4 class="text-base font-semibold text-slate-900">Faltas</h4>
            <div class="report-scroll-list mt-4 space-y-3">
              @forelse(($detailEmployeeReport['faltas'] ?? []) as $item)
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                  <p class="font-semibold text-slate-900">{{ $item['fecha'] }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
                </div>
              @empty
                <p class="text-sm text-slate-400">No tiene faltas en el mes.</p>
              @endforelse
            </div>
          </div>
        </div>

        <div id="reportes-detalle-empleado-pdf-content" class="hidden">
          <div class="pdf-export-sheet space-y-8">
            <header class="pdf-export-header">
              <div>
                <p class="pdf-export-kicker">Correos de Bolivia</p>
                <h2 class="pdf-export-title">Detalle mensual del personal</h2>
                <p class="pdf-export-copy">Tardanzas, no marcados y faltas del mes seleccionado.</p>
              </div>
              <div class="pdf-export-badge">
                <span class="pdf-export-badge-label">Mes</span>
                <strong class="pdf-export-badge-value">{{ $monthLabel }}</strong>
              </div>
            </header>

            <div class="pdf-export-grid pdf-export-grid-primary">
              <div class="pdf-export-card pdf-export-card-highlight">
                <p class="pdf-export-label">Nombre</p>
                <strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['nombre'] ?? 'Sin nombre' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Codigo</p>
                <strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['codigo'] ?? 'Sin codigo' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Sucursal</p>
                <strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['sucursal'] ?? 'Sin sucursal' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Horario</p>
                <strong class="pdf-export-value">{{ $detailEmployeeReport['empleado']['horario'] ?? '--:-- - --:--' }}</strong>
              </div>
            </div>

            <div class="pdf-export-grid pdf-export-grid-secondary">
              @foreach(($detailEmployeeReport['metrics'] ?? []) as $metric)
                <div class="pdf-export-card">
                  <p class="pdf-export-label">{{ $metric['label'] }}</p>
                  <p class="pdf-export-value-sm">{{ $metric['value'] }}</p>
                </div>
              @endforeach
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Tardanzas</p>
                  <h3 class="section-title">Dias tarde</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Retraso</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse(($detailEmployeeReport['tardanzas'] ?? []) as $item)
                    <tr>
                      <td>{{ $item['fecha'] }}</td>
                      <td>{{ $item['entrada'] }}</td>
                      <td>{{ $item['salida'] }}</td>
                      <td>{{ $item['retraso'] }}</td>
                      <td>{{ $item['estado'] }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-slate-400">No tiene tardanzas en el mes.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">No marcados</p>
                  <h3 class="section-title">Registros incompletos</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse(($detailEmployeeReport['no_marcados'] ?? []) as $item)
                    <tr>
                      <td>{{ $item['fecha'] }}</td>
                      <td>{{ $item['entrada'] }}</td>
                      <td>{{ $item['salida'] }}</td>
                      <td>{{ $item['estado'] }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="4" class="text-center text-slate-400">No tiene no marcados en el mes.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Faltas</p>
                  <h3 class="section-title">Ausencias del mes</h3>
                </div>
              </div>
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Detalle</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse(($detailEmployeeReport['faltas'] ?? []) as $item)
                    <tr>
                      <td>{{ $item['fecha'] }}</td>
                      <td>{{ $item['detalle'] }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="2" class="text-center text-slate-400">No tiene faltas en el mes.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <div id="reportes-pdf-content" class="hidden">
    <div class="pdf-export-sheet space-y-8">
      <header class="pdf-export-header">
        <div>
          <p class="pdf-export-kicker">Correos de Bolivia</p>
          <h2 class="pdf-export-title">Reporte mensual de asistencia</h2>
          <p class="pdf-export-copy">Resumen ejecutivo con metricas, personal con mayor retraso e incidencias del mes.</p>
        </div>
        <div class="pdf-export-badge">
          <span class="pdf-export-badge-label">Mes</span>
          <strong class="pdf-export-badge-value">{{ $monthLabel }}</strong>
        </div>
      </header>

      <div class="pdf-export-grid pdf-export-grid-primary">
        @foreach($metrics as $metric)
          <div class="pdf-export-card">
            <p class="pdf-export-label">{{ $metric['label'] }}</p>
            <strong class="pdf-export-value">{{ $metric['value'] }}</strong>
            <p class="pdf-export-value-sm">{{ $metric['detail'] }}</p>
          </div>
        @endforeach
      </div>

      <div class="pdf-export-table-shell">
        <div class="section-head-row pdf-export-section-head">
          <div>
            <p class="section-kicker">Mayor retraso</p>
            <h3 class="section-title">Personal con mayor retraso del mes</h3>
          </div>
        </div>
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Dias tarde</th>
              <th>Retraso</th>
            </tr>
          </thead>
          <tbody>
            @forelse($monthlyReport['top_employees'] as $employee)
              <tr>
                <td>{{ $employee['nombre'] }}</td>
                <td>{{ $employee['sucursal'] }}</td>
                <td>{{ $employee['dias_tarde'] }}</td>
                <td>{{ $employee['retraso'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-slate-400">No hay retrasos acumulados en el mes seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Filtro de reportes</p>
        <h3 class="section-title">Consulta por mes, sucursal y personal</h3>
        <p class="section-copy-sm">Resumen mensual cargado: {{ $monthLabel }}.</p>
      </div>
      <button type="button" wire:click="descargarPdfReporte" class="section-action-button">PDF</button>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
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

    <div class="attendance-chart-shell">
      <div class="attendance-chart-summary">
        <div class="attendance-chart-pill">
          <span class="attendance-chart-pill-label">Mes actual</span>
          <strong class="attendance-chart-pill-value">{{ $frequency['summary']['current_count'] }}</strong>
        </div>
        <div class="attendance-chart-pill">
          <span class="attendance-chart-pill-label">Pico del periodo</span>
          <strong class="attendance-chart-pill-value">{{ $frequency['summary']['peak_label'] }} · {{ $frequency['summary']['peak_count'] }}</strong>
        </div>
      </div>

      <div class="attendance-chart-frame">
        <div class="attendance-chart-scale" aria-hidden="true">
          <span>{{ $frequency['scale']['max'] }}</span>
          <span>{{ $frequency['scale']['mid'] }}</span>
          <span>{{ $frequency['scale']['min'] }}</span>
        </div>

        <div class="attendance-chart">
          @foreach($frequency['bars'] as $bar)
            <div class="attendance-bar-group">
              <div class="attendance-bar-track">
                <div class="attendance-bar {{ $bar['active'] ? 'attendance-bar-active' : '' }} {{ $bar['is_peak'] ? 'attendance-bar-peak' : '' }}" style="height: {{ $bar['height'] }};"></div>
              </div>
              <span class="attendance-bar-label {{ $bar['active'] ? 'attendance-bar-label-active' : '' }}">{{ $bar['label'] }}</span>
              <span class="attendance-bar-count">{{ $bar['count'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
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
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="font-semibold text-slate-900">{{ $employee['nombre'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $employee['sucursal'] }} | {{ $employee['dias_tarde'] }} dias tarde | {{ $employee['retraso'] }}</p>
              </div>
              <button type="button" wire:click="openEmployeeDetailModal({{ $employee['empleado_id'] }})" class="table-action-button">Ver detalle</button>
            </div>
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

</div>
