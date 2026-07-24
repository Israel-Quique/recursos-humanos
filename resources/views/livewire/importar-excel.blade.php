<div class="page-stack">
  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar importacion</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta importacion y todos sus registros asociados?</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteImportacionNombre }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteImportacion" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  @endif

  <section class="surface-card relative">
    <div wire:loading wire:target="importFile" class="loading-overlay">
      <div class="loading-spinner" role="status" aria-live="polite" aria-label="Importando archivo">
        <svg class="loading-spinner-icon" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
          <circle class="loading-spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
        </svg>
        <span class="loading-spinner-text">Importando, por favor espera...</span>
      </div>
    </div>

    <p class="section-kicker">Modulo de integracion</p>
    <h3 class="section-title">Carga de Planilla Biometrica</h3>
    <p class="section-copy-sm max-w-3xl">
      Cargue el archivo de reporte generado por el reloj biometrico de Correos de Bolivia para sincronizar
      las marcas de ingreso y salida.
    </p>

    <form wire:submit="importFile" class="mt-8">
      <label class="upload-dropzone">
        <input
          type="file"
          wire:model="archivo"
          class="upload-dropzone-input"
          accept=".xls,.xlsx,.csv"
        >

        <div class="upload-badge">
          <svg viewBox="0 0 48 48" aria-hidden="true" class="upload-badge-icon">
            <path d="M14 6h14l10 10v22a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4Z" fill="currentColor" opacity=".14"/>
            <path d="M28 6v10h10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 30h14M17 24h7M17 18h7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>

        <h4 class="upload-title">Arrastra aqui tu archivo o haz clic para elegirlo</h4>
        <p class="upload-copy">Admite archivos `.xls`, `.xlsx` y `.csv` del biometrico para generar asistencias reales.</p>

        <div class="upload-actions">
          <span class="upload-action-button">Seleccionar archivo</span>
          <span class="upload-hint">Tambien puedes soltar el Excel directamente en esta zona.</span>
        </div>

        <span class="upload-format">
          {{ $archivo ? $archivo->getClientOriginalName() : 'Formato sugerido: planilla_asistencia_2026.xlsx' }}
        </span>

        <span wire:loading wire:target="archivo" class="upload-loading">Cargando archivo...</span>
      </label>
      @error('archivo') <p class="form-error mt-3">{{ $message }}</p> @enderror

      <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="login-submit max-w-[22rem]" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="importFile">Importar y generar registros</span>
          <span wire:loading wire:target="importFile">Procesando archivo...</span>
        </button>
      </div>
    </form>

    @if ($summary)
      <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="metric-card">
          <p class="metric-label">Registros creados</p>
          <strong class="metric-value">{{ $summary['registros_generados'] ?? 0 }}</strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Registros actualizados</p>
          <strong class="metric-value">{{ $summary['registros_actualizados'] ?? 0 }}</strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Empleados detectados</p>
          <strong class="metric-value">{{ $summary['empleados_detectados'] ?? 0 }}</strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Personal creado</p>
          <strong class="metric-value">{{ $summary['empleados_creados'] ?? 0 }}</strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Olvidos de marcacion</p>
          <strong class="metric-value">{{ $summary['olvidos_marcacion'] ?? 0 }}</strong>
        </div>
        <div class="metric-card">
          <p class="metric-label">Marcas omitidas</p>
          <strong class="metric-value">{{ $summary['marcas_omitidas'] ?? 0 }}</strong>
        </div>
      </div>

      <p class="section-copy-sm mt-4">
        El sistema importa las marcaciones y ahora tambien puede crear automaticamente al personal nuevo usando el codigo, nombre completo y sucursal detectados desde la planilla.
      </p>

      @if(! empty($summary['empleados_no_registrados']))
        <div class="device-alert-box mt-6">
          <p class="device-alert-title">Filas que no pudieron convertirse en personal automaticamente</p>
          <div class="device-alert-list">
            @foreach($summary['empleados_no_registrados'] as $empleadoNoRegistrado)
              <span class="device-alert-pill">{{ $empleadoNoRegistrado }}</span>
            @endforeach
          </div>
        </div>
      @endif
    @endif
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <p class="section-kicker">Monitoreo por IP</p>
        <h3 class="section-title">Estado de conexion de biometricos</h3>
        <p class="section-copy-sm">Cuando un biometrico este conectado, sus asistencias podran registrarse directo en el sistema.</p>
      </div>
      <div class="history-pill">
        <span class="hero-status-icon"></span>
        <span>{{ collect($connections)->where('connected', true)->count() }} equipos conectados</span>
      </div>
    </div>

    <div class="device-grid">
      @foreach($connections as $device)
        <article class="device-card {{ $device['connected'] ? 'device-card-online' : 'device-card-offline' }}">
          <div class="device-card-head">
            <div>
              <p class="device-card-kicker">{{ $device['department'] }}</p>
              <h4 class="device-card-title">{{ $device['branch'] }}</h4>
            </div>
            <span class="status-badge {{ $device['connected'] ? 'status-available' : 'status-danger' }}">
              {{ $device['connected'] ? 'Conectado' : 'Desconectado' }}
            </span>
          </div>
          <div class="device-card-meta">
            <span>IP: <strong>{{ $device['ip'] }}</strong></span>
            <span>{{ $device['last_sync'] }}</span>
          </div>
        </article>
      @endforeach
    </div>

    <div class="device-alert-box">
      <p class="device-alert-title">Sucursales sin conexion directa</p>
      <div class="device-alert-list">
        @foreach(collect($connections)->where('connected', false) as $device)
          <span class="device-alert-pill">{{ $device['branch'] }} - {{ $device['department'] }}</span>
        @endforeach
      </div>
    </div>
  </section>

  <section class="surface-card">
    <div class="history-header">
      <div>
        <h3 class="section-title">Historial de Importaciones</h3>
        <p class="section-copy-sm">Ultimas cargas procesadas por el modulo de sincronizacion.</p>
      </div>
      <div class="history-pill">
        <span class="hero-status-icon"></span>
        <span>{{ count($history) }} archivos listos</span>
      </div>
    </div>

    <div class="history-table-shell">
      <table class="history-table">
        <thead>
          <tr>
            <th>Archivo</th>
            <th>Registros</th>
            <th>Fecha de carga</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($history as $row)
            <tr>
              <td>{{ $row['file'] }}</td>
              <td>{{ $row['records'] }}</td>
              <td>{{ $row['date'] }}</td>
              <td><span class="status-badge {{ $row['status'] === 'Completado' ? 'status-available' : ($row['status'] === 'Error' ? 'status-danger' : 'status-info') }}">{{ $row['status'] }}</span></td>
              <td>
                <button
                  type="button"
                  wire:click="openDeleteModal({{ $row['id'] }}, '{{ addslashes($row['file']) }}')"
                  class="table-action-button table-action-button-danger"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-slate-400">Todavia no existen importaciones reales procesadas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
