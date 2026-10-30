<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Acceso personal</p>
        <h3 class="section-title">Mis horas marcadas</h3>
        <p class="section-copy-sm">Consulta tus marcaciones y boletas organizadas por mes y por día.</p>
      </div>
    </div>

    @if($empleado)
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.2rem] border border-blue-200 bg-blue-50/50 px-5 py-4">
          <label class="metric-label text-blue-700" for="my-hours-month">Mes de consulta</label>
          <select id="my-hours-month" wire:model.live="referenceMonth" class="form-input mt-2 bg-white font-semibold">
            @foreach($monthOptions as $option)
              <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
          </select>
        </div>

        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Personal</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['nombre'] }}</p>
        </div>

        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Codigo</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['codigo'] }}</p>
        </div>

        <div class="rounded-[1.2rem] border border-slate-200 bg-slate-50 px-5 py-4">
          <p class="metric-label">Sucursal</p>
          <p class="mt-3 text-lg font-semibold text-slate-900">{{ $personalReport['empleado']['sucursal'] }}</p>
        </div>
      </div>
    @else
      <div class="mt-8 rounded-[1.4rem] border border-dashed border-amber-300 bg-amber-50 px-5 py-5 text-sm text-amber-900">
        Tu usuario todavia no esta vinculado a un trabajador. Cuando RRHH asigne tu perfil, aqui podras ver tus horas por mes y por dia.
      </div>
    @endif
  </section>

  @if($personalReport)
    <section class="metric-grid metric-grid-3">
      @foreach($personalReport['metrics'] as $metric)
        <article class="metric-card metric-card-centered">
          <p class="metric-label">{{ $metric['label'] }}</p>
          <strong class="metric-value">{{ $metric['value'] }}</strong>
        </article>
      @endforeach
    </section>

    {{-- ── SECCIÓN DE BOLETAS DEL MES EN CARDS ────────────────── --}}
    @php
      $listaBoletas = $boletasMes ?? collect();
      $totalBoletasMes = $listaBoletas->count();
    @endphp
    <section class="surface-card" aria-labelledby="mis-boletas-mes-titulo">
      <div class="history-header">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <p class="section-kicker">Permisos y papeletas</p>
            <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
              📅 {{ $monthLabel }}
            </span>
          </div>
          <h3 id="mis-boletas-mes-titulo" class="section-title mt-1">Mis boletas de {{ $monthLabel }}</h3>
          <p class="section-copy-sm">Mostrando únicamente tus boletas correspondientes al mes seleccionado ({{ $monthLabel }}).</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-700">
          📄 {{ $totalBoletasMes }} {{ $totalBoletasMes === 1 ? 'boleta registrada' : 'boletas registradas' }}
        </span>
      </div>

      @if($totalBoletasMes > 0)
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          @foreach($listaBoletas as $solicitud)
            @php
              $estadoKey = strtolower($solicitud->estado ?? 'pendiente');
              $topBorder = match($estadoKey) {
                'aprobado'  => 'border-t-4 border-t-emerald-500',
                'rechazado' => 'border-t-4 border-t-rose-500',
                default     => 'border-t-4 border-t-amber-500',
              };
              $statusBadge = match($estadoKey) {
                'aprobado'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'rechazado' => 'bg-rose-50 text-rose-800 border-rose-200',
                default     => 'bg-amber-50 text-amber-800 border-amber-200',
              };
              $statusIcon = match($estadoKey) {
                'aprobado'  => '✓',
                'rechazado' => '✕',
                default     => '⏳',
              };
              $tipoKey = $solicitud->tipo_boleta;
              $tipoBadge = match($tipoKey) {
                'comision' => ['label' => '🏛️ Comisión', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                'medico'   => ['label' => '🩺 Médico', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                'rip', 'omision', 'retraso' => ['label' => '⚖️ Art. 45 R.I.P.', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                default    => ['label' => '👤 Particular', 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
              };
            @endphp

            <article wire:key="mis-horas-boleta-{{ $solicitud->id }}" class="flex flex-col justify-between rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-2xs transition hover:-translate-y-0.5 hover:shadow-md {{ $topBorder }}">
              <div class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide {{ $tipoBadge['class'] }}">
                      {{ $tipoBadge['label'] }}
                    </span>
                    <span class="font-mono text-xs font-bold text-slate-400">
                      #{{ str_pad((string) $solicitud->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                  </div>
                  <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadge }}">
                    <span>{{ $statusIcon }}</span>
                    <span>{{ ucfirst($solicitud->estado) }}</span>
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Fecha solicitada</p>
                    <p class="mt-1 text-xs font-extrabold text-slate-900">
                      {{ $solicitud->fecha_inicio?->format('d/m/Y') }} — {{ $solicitud->fecha_fin?->format('d/m/Y') }}
                    </p>
                  </div>
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Horario</p>
                    <p class="mt-1 text-xs font-extrabold text-slate-900">
                      {{ $solicitud->hora_inicio ? substr($solicitud->hora_inicio, 0, 5) : 'Día completo' }} {{ $solicitud->hora_fin ? '— '.substr($solicitud->hora_fin, 0, 5) : '' }}
                    </p>
                  </div>
                </div>

                <div class="border-l-2 border-slate-300 pl-3">
                  <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Motivo / Justificación</p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $solicitud->motivo_sin_prefijo }}</p>
                </div>
              </div>

              <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3.5">
                <button
                  type="button"
                  wire:click="descargarBoletaPdf({{ $solicitud->id }})"
                  wire:loading.attr="disabled"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-2xs transition hover:bg-blue-700"
                >
                  <span>⬇ Descargar PDF</span>
                </button>
              </div>
            </article>
          @endforeach
        </div>
      @else
        <div class="mt-6 rounded-[1.25rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center">
          <p class="text-sm font-bold text-slate-700">No tienes boletas registradas en {{ $monthLabel }}.</p>
          <p class="mt-1 text-xs text-slate-500">Solo se muestran las boletas correspondientes al mes que estás consultando actualmente.</p>
        </div>
      @endif
    </section>

    <section class="surface-card">
      <div class="history-header">
        <div>
          <p class="section-kicker">Detalle del mes</p>
          <h3 class="section-title">Marcaciones de {{ $monthLabel }}</h3>
        </div>
        <p class="section-copy-sm">{{ $personalReport['empleado']['horario'] }}</p>
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
              <th>Biometrico</th>
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
                <td>{{ $row['estado_biometrico'] }} | {{ $row['evento_biometrico'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-slate-400">No tienes marcaciones registradas en el mes seleccionado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endif
</div>
