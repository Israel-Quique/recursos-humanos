<div class="page-stack page-stack-calendar">
  <section class="surface-card surface-card-calendar">
    <div class="calendar-header">
      <div>
        <p class="section-kicker">Planificacion de turnos</p>
        <h3 class="section-title">Calendario de Asistencia</h3>
      </div>

      <div class="calendar-controls">
        <button type="button" class="calendar-chip" wire:click="goToCurrentMonth">Hoy</button>
        <div class="calendar-month-box">
          <button type="button" class="calendar-arrow" wire:click="goToPreviousMonth" aria-label="Mes anterior">&lsaquo;</button>
          <span>{{ $calendar['month_label'] }}</span>
          <button type="button" class="calendar-arrow" wire:click="goToNextMonth" aria-label="Mes siguiente">&rsaquo;</button>
        </div>
      </div>
    </div>

    <div class="calendar-legend">
      <div class="calendar-legend-item">
        <span class="calendar-event-dot calendar-event-dot-red"></span>
        <span>Llegada tarde</span>
      </div>
      <div class="calendar-legend-item">
        <span class="calendar-event-dot calendar-event-dot-black"></span>
        <span>Excedio tolerancia mensual</span>
      </div>
    </div>

    <div class="calendar-layout">
      <div class="calendar-grid-main">
        <div class="calendar-shell">
          <div class="calendar-weekdays">
            @foreach($calendar['weekdays'] as $weekday)
              <div>{{ strtoupper($weekday) }}</div>
            @endforeach
          </div>

          <div class="calendar-weeks">
            @foreach($calendar['weeks'] as $week)
              <div class="calendar-week-row">
                @foreach($week as $day)
                  <article
                    wire:click="selectDate('{{ $day['date'] }}')"
                    class="calendar-day-card calendar-day-card-interactive {{ $day['is_current_month'] ? '' : 'calendar-day-card-muted' }} {{ $day['is_today'] ? 'calendar-day-card-today' : '' }} {{ $selectedDay['date'] === $day['date'] ? 'calendar-day-card-selected' : '' }}"
                  >
                    <div class="calendar-day-top">
                      <span class="calendar-day-number">{{ $day['label'] }}</span>
                    </div>

                    <div class="calendar-day-events">
                      @if($day['summary']['red'] > 0)
                        <div class="calendar-day-counter" title="Llegadas tarde en el dia">
                          <span class="calendar-event-dot calendar-event-dot-red"></span>
                          <span>{{ $day['summary']['red'] }}</span>
                        </div>
                      @endif

                      @if($day['summary']['black'] > 0)
                        <div class="calendar-day-counter calendar-day-counter-dark" title="Excedieron tolerancia mensual">
                          <span class="calendar-event-dot calendar-event-dot-black"></span>
                          <span>{{ $day['summary']['black'] }}</span>
                        </div>
                      @endif
                    </div>
                  </article>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <aside class="calendar-side-panel">
        <div class="calendar-side-head">
          <div>
            <p class="section-kicker">Detalle del dia</p>
            <h4 class="section-title calendar-side-day-title">{{ $selectedDay['day_label'] }}</h4>
            <p class="section-copy-sm">{{ $selectedDay['date_label'] }}</p>
          </div>
          @if($selectedDay['is_today'])
            <span class="calendar-side-badge">Hoy</span>
          @endif
        </div>

        <div class="calendar-side-body">
          <div class="calendar-side-metrics">
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Marcaciones</span>
              <strong class="calendar-side-metric-value">{{ $selectedDay['totals']['marcaciones'] }}</strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Tardanzas</span>
              <strong class="calendar-side-metric-value">{{ $selectedDay['totals']['tardanzas'] }}</strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Excedidos</span>
              <strong class="calendar-side-metric-value">{{ $selectedDay['totals']['excedidos'] }}</strong>
            </div>
            <div class="calendar-side-metric">
              <span class="calendar-side-metric-label">Retraso total</span>
              <strong class="calendar-side-metric-value">{{ $selectedDay['totals']['minutos_retraso_formateado'] }}</strong>
            </div>
          </div>

          @if($selectedDay['is_saturday'])
            <div class="calendar-side-note">
              El dia seleccionado cae en fin de semana. Esa fecha no entra al conteo mensual de tardanzas.
            </div>
          @endif

          <div class="calendar-side-block">
            <button type="button" wire:click="toggleLateEmployees" class="calendar-side-toggle">
              <div class="calendar-side-block-head calendar-side-block-head-static">
                <h5 class="calendar-side-block-title">Personal con atraso</h5>
                <div class="calendar-side-toggle-meta">
                  <span class="calendar-side-block-count">{{ count($selectedDay['events']) }}</span>
                  <span class="calendar-side-toggle-icon">{{ $showLateEmployees ? '-' : '+' }}</span>
                </div>
              </div>
            </button>

            @if($showLateEmployees)
              <div class="calendar-side-list">
                @forelse($selectedDay['events'] as $event)
                  <div class="calendar-side-item">
                    <span class="calendar-event-dot calendar-event-dot-{{ $event['tone'] }}"></span>
                    <div class="calendar-side-item-copy">
                      <strong>{{ $event['nombre'] }}</strong>
                      <span>{{ $event['detalle'] }} | Entrada {{ $event['entrada'] }}</span>
                      <span>{{ $event['sucursal'] }} | {{ $event['estado'] }}</span>
                    </div>
                  </div>
                @empty
                  <div class="calendar-side-empty">No se registraron tardanzas para esta fecha.</div>
                @endforelse
              </div>
            @endif
          </div>

          <div class="calendar-side-block">
            <button type="button" wire:click="toggleMarkedEmployees" class="calendar-side-toggle">
              <div class="calendar-side-block-head calendar-side-block-head-static">
                <h5 class="calendar-side-block-title">Marcaciones del dia</h5>
                <div class="calendar-side-toggle-meta">
                  <span class="calendar-side-block-count">{{ count($selectedDay['marcaciones']) }}</span>
                  <span class="calendar-side-toggle-icon">{{ $showMarkedEmployees ? '-' : '+' }}</span>
                </div>
              </div>
            </button>

            @if($showMarkedEmployees)
              <div class="calendar-side-list">
                @forelse($selectedDay['marcaciones'] as $registro)
                  <div class="calendar-side-item calendar-side-item-neutral">
                    <div class="calendar-side-item-copy">
                      <strong>{{ $registro['nombre'] }}</strong>
                      <span>Entrada {{ $registro['entrada'] }} | Salida {{ $registro['salida'] }}</span>
                      <span>{{ $registro['sucursal'] }} | {{ $registro['estado'] }}</span>
                    </div>
                  </div>
                @empty
                  <div class="calendar-side-empty">No hay marcaciones cargadas para este dia.</div>
                @endforelse
              </div>
            @endif
          </div>
        </div>
      </aside>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      @foreach($calendar['milestones'] as $milestone)
        <div class="rounded-[1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          {{ $milestone }}
        </div>
      @endforeach
    </div>
  </section>
</div>
