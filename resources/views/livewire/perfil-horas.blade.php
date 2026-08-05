<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Perfil compartido</p>
        <h3 class="section-title">Horas marcadas del personal</h3>
        <p class="section-copy-sm">Consulta las marcaciones por mes y por dia.</p>
      </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div>
        <label class="form-label" for="shared-profile-month">Mes</label>
        <select id="shared-profile-month" wire:model.live="referenceMonth" class="form-input">
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
  </section>

  <section class="metric-grid metric-grid-3">
    @foreach($personalReport['metrics'] as $metric)
      <article class="metric-card metric-card-centered">
        <p class="metric-label">{{ $metric['label'] }}</p>
        <strong class="metric-value">{{ $metric['value'] }}</strong>
      </article>
    @endforeach
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
              <td colspan="7" class="text-center text-slate-400">No hay marcaciones registradas en el mes seleccionado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
