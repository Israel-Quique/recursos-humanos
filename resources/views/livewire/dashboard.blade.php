<div class="page-stack">
  <section wire:poll.60s>
    <article class="surface-card surface-card-map">
      <p class="section-kicker">Mapa operativo</p>
      <h3 class="section-title">Asistencia por departamento</h3>
      <p class="section-copy-sm max-w-3xl">
        Haz clic sobre un departamento para ver cuantas personas ya marcaron y cuantas siguen en su puesto de trabajo.
      </p>

      @php
        $initialDepartment = collect($departmentStats)->first();
      @endphp

      <div class="bolivia-map-card" data-bolivia-map-root>
        <div class="bolivia-map-shell">
          <div class="bolivia-map-canvas" data-bolivia-map-canvas aria-label="Mapa de Bolivia por departamentos"></div>
          <script type="application/json" data-departments-json>@json($departmentStats)</script>
        </div>

        <aside class="department-bubble" data-department-bubble>
          <p class="department-bubble-kicker">Departamento seleccionado</p>
          <h4 class="department-bubble-title" data-department-name>{{ $initialDepartment['name'] }}</h4>
          <div class="mt-3" data-department-subregion-shell hidden>
            <label for="department-subregion-select" class="form-label">Regional</label>
            <select id="department-subregion-select" class="form-input" data-department-subregion-select></select>
          </div>
          <p class="department-bubble-copy" data-department-branch>{{ $initialDepartment['branch'] }}</p>
          <p class="department-bubble-copy mt-1">
            Actualizado a las <strong data-department-updated-at>{{ $initialDepartment['updated_at'] ?? now()->format('H:i') }}</strong>
          </p>
          <p class="department-bubble-copy mt-1" data-department-sync-label>{{ $initialDepartment['sync_label'] ?? 'Sin sincronizacion automatica registrada' }}</p>
          <div class="department-bubble-grid">
            <div class="department-bubble-stat">
              <span>Marcaron</span>
              <strong data-department-marked>{{ $initialDepartment['marked'] }}</strong>
            </div>
            <div class="department-bubble-stat">
              <span>En puesto</span>
              <strong data-department-working>{{ $initialDepartment['working'] }}</strong>
            </div>
            <div class="department-bubble-stat">
              <span>Personal activo</span>
              <strong data-department-employees>{{ $initialDepartment['employees'] ?? 0 }}</strong>
            </div>
            <div class="department-bubble-stat department-bubble-stat-alert">
              <span>Sin marcar</span>
              <strong data-department-missing>{{ $initialDepartment['missing'] }}</strong>
            </div>
          </div>

          <div class="department-presence-box">
            <div class="department-presence-head">
              <div>
                <p class="department-presence-kicker">Presencia actual</p>
                <h5 class="department-presence-title">Siguen en la agencia</h5>
              </div>
              <span class="department-presence-total" data-department-presence-total>{{ $initialDepartment['people_in_agency_total'] ?? 0 }}</span>
            </div>

            <div class="department-presence-list" data-department-presence-list>
              @forelse (($initialDepartment['people_in_agency'] ?? []) as $person)
                <article class="department-presence-item">
                  <strong>{{ $person['name'] }}</strong>
                  <span>{{ $person['area'] }} | {{ $person['status'] }}</span>
                </article>
              @empty
                <p class="department-presence-empty">No hay personal dentro de la agencia en este momento.</p>
              @endforelse
            </div>
          </div>
        </aside>
      </div>
    </article>
  </section>
</div>
