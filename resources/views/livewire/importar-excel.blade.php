<div class="page-stack">
  @if (session('status'))
    <div class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
      {{ session('status') }}
    </div>
  @endif

  @if ($showBiometricoModal)
    <div class="app-modal-backdrop" wire:click="closeBiometricoModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeBiometricoModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Registro de equipos</p>
            <h3 class="section-title app-modal-title">{{ $editingBiometricoId ? 'Editar biometrico' : 'Agregar biometrico' }}</h3>
            <p class="section-copy-sm">
              {{ $editingBiometricoId ? 'Actualiza la IP, puerto o modo de conexion del biometrico seleccionado.' : 'Registra las IPs, puertos y modo de conexion de La Paz y de los demas departamentos.' }}
            </p>
          </div>
        </div>

        <form wire:submit="saveBiometrico" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Departamento</label>
            <input type="text" wire:model="deviceDepartment" class="form-input" placeholder="Ej. La Paz">
            @error('deviceDepartment') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Sucursal o biometrico</label>
            <input type="text" wire:model="deviceBranch" class="form-input" placeholder="Ej. Oficina Central La Paz">
            @error('deviceBranch') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">IP</label>
            <input type="text" wire:model="deviceIp" class="form-input" placeholder="Ej. 172.65.14.108">
            @error('deviceIp') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Puerto</label>
            <input type="number" wire:model="devicePort" class="form-input" min="1" max="65535" placeholder="4370">
            @error('devicePort') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Modo de conexion</label>
            <select wire:model="deviceConnectionMode" class="form-input">
              @foreach ($connectionModes as $connectionMode)
                <option value="{{ $connectionMode }}">{{ $connectionMode }}</option>
              @endforeach
            </select>
            @error('deviceConnectionMode') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Contrasena de comunicacion</label>
            <input type="text" wire:model="deviceCommunicationPassword" class="form-input" placeholder="Opcional">
            @error('deviceCommunicationPassword') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeBiometricoModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">
              {{ $editingBiometricoId ? 'Guardar cambios' : 'Guardar biometrico' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

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
    <div wire:loading.flex wire:target="importFiles" class="loading-overlay">
      <div class="loading-spinner" role="status" aria-live="polite" aria-label="Importando archivo">
        <div class="loading-spinner-orbit">
          <svg class="loading-spinner-icon" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle class="loading-spinner-track" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
            <circle class="loading-spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
          </svg>
          <span class="loading-spinner-dot"></span>
        </div>
        <div class="loading-spinner-body">
          <p class="loading-spinner-kicker">Sincronizando biometrico</p>
          <p class="loading-spinner-text">Importando registros y preparando asistencias</p>
          <p class="loading-spinner-copy">El sistema procesa el lote archivo por archivo hasta completar la carga.</p>
        </div>
        <div class="loading-progress" aria-hidden="true">
          <span class="loading-progress-bar"></span>
        </div>
      </div>
    </div>

    <p class="section-kicker">Modulo de integracion</p>
    <h3 class="section-title">Carga de Planilla Biometrica</h3>
    <p class="section-copy-sm max-w-3xl">
      Cargue el archivo de reporte generado por el reloj biometrico de Correos de Bolivia para sincronizar
      las marcas de ingreso y salida.
    </p>

    <form wire:submit="importFiles" class="mt-8">
      <label class="upload-dropzone">
        <input
          type="file"
          wire:model="archivos"
          class="upload-dropzone-input"
          accept=".xls,.xlsx,.csv,.txt"
          multiple
        >

        <div class="upload-badge">
          <div wire:loading.flex wire:target="archivos,importFiles" class="h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <span class="inline-flex h-10 w-10 animate-spin rounded-full border-4 border-emerald-500 border-t-transparent"></span>
          </div>

          <div wire:loading.remove wire:target="archivos,importFiles">
            @if (count($archivos) > 0 || collect($uploadBatchStatus)->contains(fn ($item) => ($item['status'] ?? null) === 'completed'))
              <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-9 w-9" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.261a1 1 0 0 1-1.42.008l-3.2-3.2a1 1 0 0 1 1.414-1.415l2.49 2.49 6.493-6.548a1 1 0 0 1 1.417-.01Z" clip-rule="evenodd" />
                </svg>
              </div>
            @else
              <svg viewBox="0 0 48 48" aria-hidden="true" class="upload-badge-icon">
                <path d="M14 6h14l10 10v22a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4Z" fill="currentColor" opacity=".14"/>
                <path d="M28 6v10h10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17 30h14M17 24h7M17 18h7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              </svg>
            @endif
          </div>
        </div>

        <h4 class="upload-title">Arrastra aqui tus archivos o haz clic para elegirlos</h4>
        <p class="upload-copy">Admite archivos `.xls`, `.xlsx` y `.csv` del biometrico para generar asistencias reales, uno por uno.</p>

        <div class="upload-actions">
          <span class="upload-action-button">Seleccionar archivos</span>
          <span class="upload-hint">Tambien puedes soltar varios Excel directamente en esta zona.</span>
        </div>

        <span class="upload-format">
          @if (count($archivos) > 0)
            {{ count($archivos) }} archivo(s) listo(s) para importar
          @else
            Formato sugerido: planilla_asistencia_2026.xlsx
          @endif
        </span>

        <span wire:loading wire:target="archivos" class="upload-loading">Cargando archivos...</span>
      </label>
      @error('archivos') <p class="form-error mt-3">{{ $message }}</p> @enderror
      @error('archivos.*') <p class="form-error mt-3">{{ $message }}</p> @enderror

      @if (count($uploadBatchStatus) > 0)
        <div class="mt-6 space-y-3">
          @foreach ($uploadBatchStatus as $item)
            <div class="flex items-center justify-between gap-4 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3">
              <div class="min-w-0">
                <p class="truncate font-semibold text-slate-900">{{ $item['name'] }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $item['message'] }}</p>
              </div>
              <div class="shrink-0">
                @if ($item['status'] === 'completed')
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" title="Completado">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6">
                      <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.261a1 1 0 0 1-1.42.008l-3.2-3.2a1 1 0 0 1 1.414-1.415l2.49 2.49 6.493-6.548a1 1 0 0 1 1.417-.01Z" clip-rule="evenodd" />
                    </svg>
                  </span>
                @elseif ($item['status'] === 'error')
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-700 text-xl font-bold" title="Error">!</span>
                @elseif ($item['status'] === 'processing')
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm font-semibold" title="Procesando">...</span>
                @else
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-slate-600 text-sm font-semibold" title="Pendiente">...</span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="login-submit max-w-[22rem]" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="importFiles">Importar y generar registros</span>
          <span wire:loading wire:target="importFiles">Procesando archivos...</span>
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

  <section class="surface-card" wire:poll.30s>
    <div class="history-header">
      <div>
        <p class="section-kicker">Monitoreo por IP</p>
        <h3 class="section-title">Estado de conexion de biometricos</h3>
        <p class="section-copy-sm">Cuando un biometrico este conectado, sus asistencias podran registrarse directo en el sistema.</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="button" wire:click="openBiometricoModal" class="table-action-button">
          Agregar biometrico
        </button>
          <button type="button" wire:click="reconectarTodos" wire:loading.attr="disabled" wire:target="reconectarTodos" class="table-action-button">
            Reconectar todos
          </button>
        <div class="history-pill">
          <span class="hero-status-icon"></span>
          <span>{{ collect($connections)->where('connected', true)->count() }} equipos conectados</span>
        </div>
      </div>
      
      @if($reconnecting)
        <div class="loading-overlay" aria-live="polite">
          <div class="loading-spinner">
            <div class="loading-spinner-orbit">
              <svg class="loading-spinner-icon" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle class="loading-spinner-track" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
                <circle class="loading-spinner-circle" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
              </svg>
            </div>
            <div class="loading-spinner-body">
              <p class="loading-spinner-kicker">Reconectando con las sucursales</p>
              <p class="loading-spinner-text">Se intentará la conexión una por una. Esto puede tardar.</p>
            </div>
          </div>

          <div class="mt-4 max-h-64 overflow-auto bg-white/80 p-4 rounded-md">
            @if(count($reconnectProgress) === 0)
              <p class="text-sm text-slate-600">Preparando reconexión...</p>
            @endif
            <ul class="space-y-2">
              @foreach($reconnectProgress as $item)
                <li class="flex items-center justify-between">
                  <div>
                    <strong class="text-sm">{{ $item['branch'] }}</strong>
                    <div class="text-xs text-slate-500">{{ $item['ip'] ?? '' }}</div>
                  </div>
                  <div>
                    @if($item['status'] === 'probando')
                      <span class="text-sm text-amber-600">Probando…</span>
                    @elseif($item['status'] === 'ok')
                      <span class="text-sm text-emerald-600">Conectado</span>
                    @else
                      <span class="text-sm text-rose-600">Error</span>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div>
        <label for="export-year" class="form-label">Filtrar por anio</label>
        <select id="export-year" wire:model.live="exportYear" class="form-input">
          @foreach ($exportYearOptions as $year)
            <option value="{{ $year }}">{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="export-month" class="form-label">Filtrar por mes</label>
        <select id="export-month" wire:model.live="exportMonth" class="form-input">
          @foreach ($exportMonthOptions as $monthOption)
            <option value="{{ $monthOption['value'] }}">{{ $monthOption['label'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2 xl:col-span-2 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Periodo de extraccion:
        <strong class="text-slate-900">
          {{ collect($exportMonthOptions)->firstWhere('value', $exportMonth)['label'] ?? $exportMonth }}/{{ $exportYear }}
        </strong>
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
            <span>Puerto: <strong>{{ $device['port'] ?? 4370 }}</strong></span>
            <span>Modo: <strong>{{ $device['connection_mode'] ?? 'TCP/IP' }}</strong></span>
            <span>{{ $device['last_sync'] }}</span>
          </div>
          <div class="mt-5 flex flex-wrap gap-3">
            <button
              type="button"
              wire:click="probarConexion({{ $loop->index }})"
              wire:loading.attr="disabled"
              wire:target="probarConexion({{ $loop->index }})"
              class="table-action-button"
            >
              <span wire:loading.remove wire:target="probarConexion({{ $loop->index }})">Probar conexion</span>
              <span wire:loading wire:target="probarConexion({{ $loop->index }})">Probando...</span>
            </button>
            <button
              type="button"
              wire:click="extraerExcel({{ $loop->index }})"
              wire:loading.attr="disabled"
              wire:target="extraerExcel({{ $loop->index }})"
              class="table-action-button"
            >
              <span wire:loading.remove wire:target="extraerExcel({{ $loop->index }})">Extraer Excel</span>
              <span wire:loading wire:target="extraerExcel({{ $loop->index }})">Extrayendo...</span>
            </button>
            <button
              type="button"
              wire:click="extraerExcelCompleto({{ $loop->index }})"
              wire:loading.attr="disabled"
              wire:target="extraerExcelCompleto({{ $loop->index }})"
              class="table-action-button"
            >
              <span wire:loading.remove wire:target="extraerExcelCompleto({{ $loop->index }})">Extraer todo</span>
              <span wire:loading wire:target="extraerExcelCompleto({{ $loop->index }})">Extrayendo todo...</span>
            </button>
            <button
              type="button"
              wire:click="{{ ! empty($device['id']) ? 'openEditBiometricoModal('.$device['id'].')' : 'openEditBiometricoModalByIndex('.$loop->index.')' }}"
              class="table-action-button"
            >
              Editar
            </button>
            @if (! empty($device['id']))
              <button
                type="button"
                wire:click="deleteBiometrico({{ $device['id'] }})"
                class="table-action-button table-action-button-danger"
              >
                Eliminar
              </button>
            @endif
          </div>
        </article>
      @endforeach
    </div>

    @if (collect($connections)->where('connected', false)->isNotEmpty())
      <div class="device-alert-box">
        <p class="device-alert-title">Sucursales sin conexion directa</p>
        <div class="device-alert-list">
          @foreach(collect($connections)->where('connected', false) as $device)
            <span class="device-alert-pill">{{ $device['branch'] }} - {{ $device['department'] }}</span>
          @endforeach
        </div>
      </div>
    @endif
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

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div>
        <label for="history-year" class="form-label">Historial por anio</label>
        <select id="history-year" wire:model.live="historyYear" class="form-input">
          @foreach ($historyYearOptions as $year)
            <option value="{{ $year }}">{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="history-month" class="form-label">Historial por mes</label>
        <select id="history-month" wire:model.live="historyMonth" class="form-input">
          @foreach ($historyMonthOptions as $monthOption)
            <option value="{{ $monthOption['value'] }}">{{ $monthOption['label'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2 xl:col-span-2 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Historial visible:
        <strong class="text-slate-900">
          {{ collect($historyMonthOptions)->firstWhere('value', $historyMonth)['label'] ?? $historyMonth }}/{{ $historyYear }}
        </strong>
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
