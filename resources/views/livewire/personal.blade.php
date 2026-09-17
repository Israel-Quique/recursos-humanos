<div class="page-stack">
  {{-- ALERTAS DE ESTADO Y ADVERTENCIA --}}
  @if (session()->has('status'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">✓</span>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
    </div>
  @endif

  @if (session()->has('warning'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">⚠️</span>
        <span>{{ session('warning') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-900 font-bold text-xs cursor-pointer">✕</button>
    </div>
  @endif

  @if (session()->has('error'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">✕</span>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold text-xs cursor-pointer">✕</button>
    </div>
  @endif

  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar personal</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar este registro del personal? Esta accion tambien quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteEmpleadoNombre }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteEmpleado" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  @endif

  @if ($showDeleteRegistroModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteRegistroModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar marcacion</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta marcacion? Esta accion tambien quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteRegistroLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteRegistroAsistencia" class="table-action-button table-action-button-danger">Si, eliminar</button>
        </div>
      </div>
    </div>
  @endif

  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Alta de personal</p>
            <h3 class="section-title app-modal-title">Registrar nuevo integrante</h3>
            <p class="section-copy-sm">Guarda nombre, apellido, codigo biometrico, fecha de nacimiento, area y sucursal del personal.</p>
          </div>
        </div>

        <form wire:submit="saveEmpleado" class="mt-8 grid gap-5 md:grid-cols-2">
          {{-- FOTOGRAFÍA DEL PERSONAL --}}
          <div class="md:col-span-2 flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
            <div class="emp-photo-preview-box" style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; max-width: 80px; max-height: 80px; border-radius: 1rem; overflow: hidden; position: relative; flex-shrink: 0;">
              @if ($fotoNueva)
                <img src="{{ $fotoNueva->temporaryUrl() }}" alt="Vista previa" width="80" height="80" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px; object-fit: cover; object-position: center top; display: block; border-radius: 0.9rem;" class="emp-photo-img border-2 border-indigo-500 shadow-md">
              @else
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 0.9rem; color: #94a3b8;">
                  <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 28px; height: 28px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  <span style="font-size: 9px; font-weight: 700; margin-top: 4px;">Sin foto</span>
                </div>
              @endif
              <div wire:loading wire:target="fotoNueva" class="absolute inset-0 bg-white/80 rounded-2xl flex items-center justify-center" style="position: absolute; inset: 0; background-color: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center;">
                <span class="text-xs text-indigo-600 font-bold animate-pulse">Cargando...</span>
              </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <label class="block text-xs font-bold text-slate-700 mb-1">Fotografía del personal (Opcional)</label>
              <p class="text-[11px] text-slate-500 mb-2">Formato JPG, PNG o WEBP (máx. 4MB). Visible en el portal de horas y credencial.</p>
              <div class="flex items-center justify-center sm:justify-start gap-2">
                <label for="create-foto-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 cursor-pointer transition-colors shadow-2xs">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <span>{{ $fotoNueva ? 'Cambiar foto' : 'Subir foto' }}</span>
                </label>
                <input type="file" id="create-foto-input" wire:model="fotoNueva" accept="image/jpeg,image/png,image/webp" class="hidden">
                @if ($fotoNueva)
                  <button type="button" wire:click="quitarFoto" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold cursor-pointer">
                    ✕ Quitar
                  </button>
                @endif
              </div>
              @error('fotoNueva') <p class="form-error mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          <div>
            <label class="form-label">Nombre del personal</label>
            <input type="text" wire:model="nombre" class="form-input" placeholder="Ej. Maria">
            @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Apellido</label>
            <input type="text" wire:model="apellido" class="form-input" placeholder="Ej. Perez">
            @error('apellido') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Codigo biometrico</label>
            <input type="text" wire:model="codigoBiometrico" class="form-input" placeholder="Ej. 1045">
            @error('codigoBiometrico') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Correo electronico</label>
            <input type="email" wire:model="email" class="form-input" placeholder="Ej. nombre.apellido@correos.gob.bo">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Area</label>
            <input type="text" wire:model="area" class="form-input" placeholder="Opcional">
            @error('area') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Sucursal</label>
            <input type="text" wire:model="sucursal" class="form-input" placeholder="Ej. La Paz">
            @error('sucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" wire:model="fechaNacimiento" class="form-input">
            @error('fechaNacimiento') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Registrar personal</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL DE COMUNICADOS INSTITUCIONALES Y VISTA PREVIA DE CORREOS --}}
  @if ($showEmailModal)
    @php
      $destEjemplo = $emailEmpleadoEjemplo ?? (new \App\Models\Empleado([
        'nombre' => 'María Elena',
        'apellido' => 'Flores Quispe',
        'area' => 'Operaciones y Logística',
        'sucursal' => $emailSucursalSeleccionada ?: 'Sede Central - La Paz',
        'codigo_biometrico' => '1045',
        'email' => 'm.flores@correos.gob.bo',
      ]));
      $alcanceEtiqueta = $emailTipoDestinatario === 'sucursal' && filled($emailSucursalSeleccionada)
        ? "Sucursal {$emailSucursalSeleccionada}"
        : 'Institucional Masivo';
      $logoUrl = file_exists(public_path('images/menu-logo.png'))
        ? asset('images/menu-logo.png')
        : (file_exists(public_path('images/correos-bolivia-brand.svg')) ? asset('images/correos-bolivia-brand.svg') : null);
    @endphp

    <div class="app-modal-backdrop" wire:click="closeEmailModal">
      <div class="app-modal-card {{ $emailModalTab === 'preview' ? 'max-w-4xl' : 'max-w-3xl' }} transition-all" x-on:click.stop>
        <button type="button" wire:click="closeEmailModal" class="app-modal-close app-modal-close-corner cursor-pointer" aria-label="Cerrar modal">✕</button>
        
        {{-- Cabecera con selector de Pestañas --}}
        <div class="app-modal-head border-b border-slate-100 pb-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full pr-8">
            <div>
              <p class="section-kicker">Comunicación Oficial de RRHH</p>
              <h3 class="section-title app-modal-title flex items-center gap-2">
                <span>Centro de Comunicados y Correos</span>
              </h3>
            </div>

            {{-- Pestañas del Modal --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
              <button
                type="button"
                wire:click="setEmailModalTab('redactar')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $emailModalTab === 'redactar' ? 'bg-white text-blue-700 shadow-xs ring-1 ring-slate-200/80 font-black' : 'text-slate-600 hover:text-slate-900' }}"
              >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                </svg>
                <span>Redactar</span>
              </button>
              <button
                type="button"
                wire:click="setEmailModalTab('preview')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $emailModalTab === 'preview' ? 'bg-blue-600 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900' }}"
              >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <span>Vista previa del correo</span>
                <span class="inline-block w-2 h-2 rounded-full {{ filled($emailAsunto) || filled($emailMensaje) ? 'bg-emerald-400 animate-pulse' : 'bg-slate-300' }}"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PESTAÑA 1: REDACCIÓN DE COMUNICADO --}}
        {{-- ========================================================================= --}}
        @if ($emailModalTab === 'redactar')
          <div class="mt-5 space-y-4">
            
            {{-- Atajos de Plantillas Institucionales --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
              <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                  </svg>
                  <span>Plantillas de formato rápido:</span>
                </span>
                <span class="text-[11px] text-slate-400">Haz clic para cargar un formato base oficial</span>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  wire:click="cargarPlantillaEjemplo('invitacion_sistema')"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-teal-300 bg-teal-50/80 px-2.5 py-1.5 text-xs font-bold text-teal-800 hover:border-teal-400 hover:bg-teal-100 transition cursor-pointer shadow-2xs"
                  title="Cargar formato de invitación y recordatorio al sistema de autoconsulta"
                >
                  <svg class="h-3.5 w-3.5 text-teal-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                  </svg>
                  <span>Invitación al portal (Autoconsulta)</span>
                </button>
                <button
                  type="button"
                  wire:click="cargarPlantillaEjemplo('comunicado_general')"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700 transition cursor-pointer shadow-2xs"
                  title="Cargar formato de comunicado institucional"
                >
                  <svg class="h-3.5 w-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
                  </svg>
                  <span>Comunicado general</span>
                </button>
                <button
                  type="button"
                  wire:click="cargarPlantillaEjemplo('horario_especial')"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700 transition cursor-pointer shadow-2xs"
                  title="Cargar formato de aviso de horario"
                >
                  <svg class="h-3.5 w-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                  </svg>
                  <span>Aviso de horario</span>
                </button>
                <button
                  type="button"
                  wire:click="cargarPlantillaEjemplo('capacitacion')"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700 transition cursor-pointer shadow-2xs"
                  title="Cargar formato de convocatoria o capacitación"
                >
                  <svg class="h-3.5 w-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                  </svg>
                  <span>Convocatoria / Capacitación</span>
                </button>
                <button
                  type="button"
                  wire:click="cargarPlantillaEjemplo('mantenimiento')"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700 transition cursor-pointer shadow-2xs"
                  title="Cargar formato de aviso de mantenimiento"
                >
                  <svg class="h-3.5 w-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                  </svg>
                  <span>Mantenimiento biométrico</span>
                </button>
              </div>
            </div>

            {{-- Selector de Alcance --}}
            <div>
              <label class="form-label font-bold text-slate-800">Alcance de destinatarios</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1.5">
                <label class="relative flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition-all {{ $emailTipoDestinatario === 'masivo' ? 'border-blue-600 bg-blue-50/60 ring-2 ring-blue-500/20 shadow-xs' : 'border-slate-200 hover:bg-slate-50' }}">
                  <input type="radio" wire:model.live="emailTipoDestinatario" value="masivo" class="text-blue-600 focus:ring-blue-500">
                  <div>
                    <span class="block text-sm font-bold text-slate-900">🌐 Todo el personal (Masivo)</span>
                    <span class="block text-xs text-slate-500">Toda la plantilla con correo registrado</span>
                  </div>
                </label>
                <label class="relative flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition-all {{ $emailTipoDestinatario === 'sucursal' ? 'border-blue-600 bg-blue-50/60 ring-2 ring-blue-500/20 shadow-xs' : 'border-slate-200 hover:bg-slate-50' }}">
                  <input type="radio" wire:model.live="emailTipoDestinatario" value="sucursal" class="text-blue-600 focus:ring-blue-500">
                  <div>
                    <span class="block text-sm font-bold text-slate-900">🏢 Por sucursal</span>
                    <span class="block text-xs text-slate-500">Solo personal de una sucursal específica</span>
                  </div>
                </label>
              </div>
            </div>

            {{-- Selector de Sucursal si aplica --}}
            @if ($emailTipoDestinatario === 'sucursal')
              <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-3.5 space-y-2">
                <label for="email-sucursal-select" class="form-label font-bold text-slate-800">Seleccionar sucursal de destino *</label>
                <select id="email-sucursal-select" wire:model.live="emailSucursalSeleccionada" class="form-input bg-white">
                  <option value="">-- Elige una sucursal --</option>
                  @foreach ($sucursales as $sucursalOpt)
                    <option value="{{ $sucursalOpt }}">{{ $sucursalOpt }}</option>
                  @endforeach
                </select>
                @error('emailSucursalSeleccionada') <p class="form-error">{{ $message }}</p> @enderror
              </div>
            @endif

            {{-- Checkbox de Solo Activos --}}
            <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs">
              <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                <input type="checkbox" wire:model.live="emailSoloActivos" class="rounded text-blue-600 focus:ring-blue-500">
                <span>Solo colaboradores activos en servicio (con marcaciones en los últimos 30 días)</span>
              </label>
            </div>

            {{-- Resumen y previsualización de destinatarios --}}
            <div class="rounded-xl border p-3.5 {{ $emailDestinatariosLista->count() > 0 ? 'border-blue-200 bg-blue-50/60 text-blue-950' : 'border-amber-200 bg-amber-50 text-amber-950' }}">
              <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2 text-sm font-bold">
                  <span>📬 Destinatarios disponibles:</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-white border shadow-2xs {{ $emailDestinatariosLista->count() > 0 ? 'text-blue-700 border-blue-300' : 'text-amber-700 border-amber-300' }}">
                    {{ $emailDestinatariosLista->count() }} colaboradores con correo
                  </span>
                </div>
                @if($emailDestinatariosLista->count() > 0)
                  <button type="button" wire:click="toggleEmailPreviewRecipients" class="text-xs font-bold text-blue-700 hover:underline cursor-pointer">
                    {{ $showEmailPreviewRecipients ? 'Ocultar lista' : 'Ver lista (' . $emailDestinatariosLista->count() . ')' }}
                  </button>
                @endif
              </div>

              @if($emailSinCorreoConteo > 0)
                <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1">
                  <span>ℹ️</span>
                  <span>Hay <strong>{{ $emailSinCorreoConteo }}</strong> colaborador(es) en este alcance sin correo registrado (se omitirán automáticamente).</span>
                </p>
              @endif

              @if($showEmailPreviewRecipients && $emailDestinatariosLista->count() > 0)
                <div class="mt-3 max-h-40 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2 text-xs divide-y divide-slate-100">
                  @foreach($emailDestinatariosLista as $dest)
                    <div class="py-1.5 flex items-center justify-between gap-2">
                      <span class="font-medium text-slate-800">{{ $dest->nombre_completo }}</span>
                      <span class="text-slate-400 font-mono text-[11px]">{{ $dest->email }}</span>
                      <span class="text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded font-medium">{{ $dest->sucursal }}</span>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            {{-- Asunto --}}
            <div>
              <div class="flex items-center justify-between mb-1">
                <label for="email-asunto-input" class="form-label font-bold text-slate-800 !mb-0">Asunto del correo *</label>
                <span class="text-[11px] text-slate-400 font-mono">{{ strlen($emailAsunto) }}/200</span>
              </div>
              <input
                id="email-asunto-input"
                type="text"
                wire:model.live="emailAsunto"
                maxlength="200"
                class="form-input"
                placeholder="Ej. Convocatoria a reunión institucional o Comunicado de RRHH"
              >
              @error('emailAsunto') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            {{-- Mensaje --}}
            <div>
              <div class="flex items-center justify-between mb-1">
                <label for="email-mensaje-textarea" class="form-label font-bold text-slate-800 !mb-0">Cuerpo del comunicado institucional *</label>
                <span class="text-[11px] text-slate-400 font-mono">{{ strlen($emailMensaje) }} caracteres</span>
              </div>
              <textarea
                id="email-mensaje-textarea"
                wire:model.live="emailMensaje"
                rows="6"
                class="form-input font-sans text-sm leading-relaxed"
                placeholder="Escribe aquí el contenido del mensaje que se enviará al personal..."
              ></textarea>
              @error('emailMensaje') <p class="form-error">{{ $message }}</p> @enderror
              <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                <span>Los saltos de línea se preservarán automáticamente con el membrete institucional.</span>
                <button
                  type="button"
                  wire:click="setEmailModalTab('preview')"
                  class="font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 cursor-pointer"
                >
                  <span>👁️ Ver cómo se verá este correo →</span>
                </button>
              </div>
            </div>

            {{-- Acciones del Modal en Redactar --}}
            <div class="app-modal-actions mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
              <button
                type="button"
                wire:click="setEmailModalTab('preview')"
                class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-800 hover:bg-blue-100 transition cursor-pointer"
              >
                <span>👁️ Vista previa del correo</span>
              </button>

              <div class="flex items-center gap-3">
                <button type="button" wire:click="closeEmailModal" class="app-modal-secondary cursor-pointer">Cancelar</button>
                <button
                  type="button"
                  wire:click="enviarCorreosPersonal"
                  wire:loading.attr="disabled"
                  wire:target="enviarCorreosPersonal"
                  @disabled($emailDestinatariosLista->count() === 0)
                  class="login-submit app-modal-submit inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <span wire:loading.remove wire:target="enviarCorreosPersonal">
                    ✉️ Enviar a {{ $emailDestinatariosLista->count() }} destinatarios
                  </span>
                  <span wire:loading wire:target="enviarCorreosPersonal" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Enviando correos...</span>
                  </span>
                </button>
              </div>
            </div>
          </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- PESTAÑA 2: VISTA PREVIA Y FORMATO OFICIAL DEL CORREO --}}
        {{-- ========================================================================= --}}
        @if ($emailModalTab === 'preview')
          <div class="mt-5 space-y-4">
            
            {{-- Barra de herramientas de la vista previa --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700">Simulación:</span>
                
                {{-- Toggle de Dispositivo (Desktop vs Mobile) --}}
                <div class="inline-flex rounded-lg bg-white p-0.5 border border-slate-200 shadow-2xs text-xs">
                  <button
                    type="button"
                    wire:click="setEmailPreviewDevice('desktop')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md transition cursor-pointer {{ $emailPreviewDevice === 'desktop' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:text-slate-900' }}"
                    title="Vista de escritorio (600px)"
                  >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>
                    </svg>
                    <span>Escritorio</span>
                  </button>
                  <button
                    type="button"
                    wire:click="setEmailPreviewDevice('mobile')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md transition cursor-pointer {{ $emailPreviewDevice === 'mobile' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:text-slate-900' }}"
                    title="Vista móvil (360px)"
                  >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>
                    </svg>
                    <span>Móvil</span>
                  </button>
                </div>
              </div>

              {{-- Selector de Destinatario de Muestra si hay lista --}}
              @if($emailDestinatariosLista->count() > 1)
                <div class="flex items-center gap-2">
                  <label for="preview-empleado-select" class="text-xs font-semibold text-slate-600">Ver como:</label>
                  <select
                    id="preview-empleado-select"
                    wire:model.live="emailPreviewEmpleadoId"
                    class="form-input text-xs py-1 px-2 h-8 bg-white max-w-[220px]"
                  >
                    @foreach($emailDestinatariosLista as $destOp)
                      <option value="{{ $destOp->id }}">{{ $destOp->nombre_completo }} ({{ $destOp->sucursal }})</option>
                    @endforeach
                  </select>
                </div>
              @else
                <span class="text-[11px] text-slate-500 font-medium">
                  Destinatario de muestra: <strong>{{ $destEjemplo->nombre_completo }}</strong>
                </span>
              @endif
            </div>

            {{-- Mockup del Cliente de Correo Zimbra (Zimbra Collaboration Suite Window) --}}
            <div class="rounded-2xl border border-slate-300 shadow-md overflow-hidden" style="background-color: #f8fafc;">
              
              {{-- Barra superior de Zimbra Web Client --}}
              <div class="px-4 py-2.5 flex items-center justify-between text-xs" style="background-color: #1e3a8a; color: #ffffff;">
                <div class="flex items-center gap-2.5">
                  <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full inline-block" style="background-color: #f7c931;"></span>
                    <span class="font-black tracking-wide text-sm" style="color: #ffffff; letter-spacing: 0.05em;">zimbra</span>
                  </div>
                  <span class="text-xs font-medium hidden sm:inline" style="color: #bfdbfe;">&middot; Web Client | Correos de Bolivia</span>
                </div>
                <div class="flex items-center gap-2 font-mono text-[11px]" style="color: #bfdbfe;">
                  <span class="hidden md:inline">{{ $destEjemplo->email ?: 'colaborador@correos.gob.bo' }}</span>
                  <span class="px-2 py-0.5 rounded font-sans text-[10.5px] font-bold" style="background-color: rgba(255,255,255,0.15); color: #ffffff;">Bandeja de Entrada</span>
                </div>
              </div>

              {{-- Pestañas de navegación corporativa Zimbra --}}
              <div class="px-3 py-1.5 flex items-center gap-1.5 text-xs overflow-x-auto" style="background-color: #e2e8f0; border-bottom: 1px solid #cbd5e1;">
                <span class="px-3 py-1 rounded shadow-2xs flex items-center gap-1.5" style="background-color: #ffffff; color: #0a3d7c; font-weight: bold; border: 1px solid #cbd5e1;">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                  </svg>
                  <span>Correo</span>
                </span>
                <span class="px-3 py-1 rounded text-slate-600 transition cursor-default flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  <span>Contactos</span>
                </span>
                <span class="px-3 py-1 rounded text-slate-600 transition cursor-default flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <span>Calendario</span>
                </span>
                <span class="px-3 py-1 rounded text-slate-600 transition cursor-default flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                  <span>Tareas</span>
                </span>
                <span class="px-3 py-1 rounded text-slate-600 transition cursor-default flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l-.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                  <span>Preferencias</span>
                </span>
              </div>

              {{-- Cabecera del mensaje en Zimbra --}}
              <div class="px-5 py-3 text-xs" style="background-color: #ffffff; border-bottom: 1px solid #e2e8f0;">
                <div class="flex items-center justify-between pb-2" style="border-bottom: 1px solid #f1f5f9;">
                  <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="h-4 w-4" style="color: #004ea2;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                    <span>Mensaje recibido en Zimbra</span>
                  </span>
                  <span class="text-[10.5px] text-slate-500 font-mono">{{ now()->translatedFormat('d \d\e M \d\e Y, H:i') }}</span>
                </div>

                <div class="mt-2.5 space-y-1.5 font-mono text-[11.5px]">
                  <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16 font-sans">De:</span>
                    <span class="font-bold font-sans" style="color: #0a3d7c;">Unidad de Recursos Humanos</span>
                    <span class="text-slate-400">&lt;rrhh@correos.gob.bo&gt;</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16 font-sans">Para:</span>
                    <span class="font-bold text-slate-800 font-sans">{{ $destEjemplo->nombre_completo }}</span>
                    <span class="text-slate-400">&lt;{{ $destEjemplo->email ?: 'colaborador@correos.gob.bo' }}&gt;</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16 font-sans">Asunto:</span>
                    <span class="font-bold text-slate-900 font-sans">
                      {{ filled($emailAsunto) ? $emailAsunto : '(Sin asunto ingresado aún)' }}
                    </span>
                  </div>
                </div>
              </div>

              {{-- Contenedor del Cuerpo de Correo renderizado con fondo gris contrastante --}}
              <div class="p-4 sm:p-7 flex justify-center overflow-x-auto max-h-[540px] overflow-y-auto" style="background-color: #e2e8f0;">
                
                {{-- Tarjeta de Correo Oficial con ancho responsive simulado --}}
                <div class="w-full transition-all duration-300 {{ $emailPreviewDevice === 'mobile' ? 'max-w-[360px]' : 'max-w-[580px]' }}" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-top: 5px solid #f7c931; border-radius: 14px; box-shadow: 0 10px 25px -5px rgba(10, 61, 124, 0.18); overflow: hidden; color: #1e293b;">
                  
                  {{-- 1. CABECERA AZUL INSTITUCIONAL CORREOS DE BOLIVIA --}}
                  <div style="background-color: #0a3d7c; background: linear-gradient(135deg, #0a3d7c 0%, #004ea2 100%); color: #ffffff; padding: 26px 20px 22px 20px; text-align: center;">
                    
                    {{-- Logo o Membrete --}}
                    <div style="margin-bottom: 14px; display: flex; justify-content: center;">
                      @if ($logoUrl)
                        <div style="background-color: #ffffff; padding: 6px 18px; border-radius: 8px; border-bottom: 3px solid #f7c931; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                          <img src="{{ $logoUrl }}" alt="Correos de Bolivia" style="height: 38px; width: auto; max-height: 38px; object-fit: contain; display: block;" />
                        </div>
                      @else
                        <div style="background-color: #f7c931; color: #0a3d7c; padding: 6px 16px; border-radius: 8px; font-weight: 900; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; display: inline-block;">
                          Correos de Bolivia &middot; RRHH
                        </div>
                      @endif
                    </div>

                    {{-- Badge de Alcance Amarillo Institucional --}}
                    <div style="display: inline-block; margin-bottom: 12px;">
                      <span style="background-color: #f7c931; color: #0a3d7c; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; padding: 4px 16px; border-radius: 20px; display: inline-block; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                        {{ $alcanceEtiqueta }}
                      </span>
                    </div>

                    {{-- Asunto / Título en la cabecera --}}
                    <h1 style="color: #ffffff; font-size: 19px; font-weight: bold; line-height: 1.35; margin: 0 0 6px 0;">
                      {{ filled($emailAsunto) ? $emailAsunto : 'Título del comunicado institucional' }}
                    </h1>
                    <p style="color: #e0edff; font-size: 12px; margin: 0;">
                      Unidad de Recursos Humanos &middot; Empresa Pública de Correos de Bolivia
                    </p>
                  </div>

                  {{-- BANDA AMARILLA DECORATIVA --}}
                  <div style="height: 4px; background-color: #f7c931; width: 100%;"></div>

                  {{-- 2. CUERPO DEL CORREO --}}
                  <div style="background-color: #ffffff; padding: 26px 24px;">
                    
                    {{-- Saludo al funcionario --}}
                    <p style="font-size: 14.5px; color: #1e293b; line-height: 1.5; margin: 0 0 16px 0;">
                      Estimado(a) <strong style="color: #0a3d7c;">{{ $destEjemplo->nombre_completo ?: 'Colaborador(a)' }}</strong>,
                    </p>

                    {{-- Ficha rápida del destinatario con acento azul y amarillo --}}
                    <div style="background-color: #f0f7ff; border: 1px solid #bfdbfe; border-left: 4px solid #004ea2; border-radius: 8px; padding: 11px 15px; margin-bottom: 20px; font-size: 12px; color: #334155;">
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <div><strong style="color: #0a3d7c;">Sucursal:</strong> {{ $destEjemplo->sucursal ?: 'Sede Central' }}</div>
                        <div style="color: #93c5fd;">&bull;</div>
                        <div><strong style="color: #0a3d7c;">Área:</strong> {{ $destEjemplo->area ?: 'General' }}</div>
                        @if($destEjemplo->codigo_biometrico)
                          <div style="color: #93c5fd;">&bull;</div>
                          <div><strong style="color: #0a3d7c;">Cód:</strong> {{ $destEjemplo->codigo_biometrico }}</div>
                        @endif
                      </div>
                    </div>

                    {{-- Contenido del mensaje redactado --}}
                    <div style="font-size: 14px; color: #334155; line-height: 1.65; white-space: pre-line; border-left: 3px solid #004ea2; padding: 8px 14px; background-color: #f8fafc; border-radius: 0 8px 8px 0; margin-bottom: 22px;">
                      @if(filled($emailMensaje))
                        {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" style="color: #004ea2; font-weight: bold; text-decoration: underline; word-break: break-all;">$1</a>', nl2br(e($emailMensaje))) !!}
                        @if(str_contains($emailMensaje, 'http://') || str_contains($emailMensaje, 'https://'))
                          @php
                            preg_match('/https?:\/\/[^\s]+/', $emailMensaje, $matchedUrls);
                            $urlEnlace = $matchedUrls[0] ?? null;
                          @endphp
                          @if($urlEnlace)
                            <div style="margin-top: 18px; margin-bottom: 6px; text-align: center;">
                              <a href="{{ $urlEnlace }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background-color: #0a3d7c; color: #ffffff; font-weight: bold; font-size: 13.5px; padding: 10px 22px; border-radius: 8px; text-decoration: none; border-bottom: 3px solid #f7c931; box-shadow: 0 4px 10px rgba(10,61,124,0.25);">
                                <span>Ingresar al Portal de Autoconsulta &rarr;</span>
                              </a>
                            </div>
                          @endif
                        @endif
                      @else
                        <span style="color: #94a3b8; font-style: italic;">
                          (Aquí se mostrará el cuerpo del mensaje que redactes. Puedes volver a la pestaña "✍️ Redactar" o hacer clic en una plantilla de formato rápido).
                        </span>
                      @endif
                    </div>

                    {{-- Separador institucional --}}
                    <div style="border-top: 1px solid #e2e8f0; margin: 22px 0;"></div>

                    {{-- Firma institucional --}}
                    <div style="font-size: 12.5px; color: #475569; line-height: 1.5;">
                      <p style="color: #0a3d7c; font-weight: bold; font-size: 14px; margin: 0 0 3px 0;">Unidad de Recursos Humanos</p>
                      <p style="color: #64748b; margin: 0; font-size: 12px;">Empresa Pública de Correos de Bolivia &middot; La Paz, Bolivia</p>
                    </div>

                  </div>

                  {{-- 3. PIE DE PÁGINA OFICIAL ZIMBRA --}}
                  <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 15px 20px; text-align: center; font-size: 11px; color: #64748b; line-height: 1.45;">
                    <p style="margin: 0 0 3px 0;">Este correo electrónico ha sido emitido oficialmente por el Sistema de Recursos Humanos para la plataforma institucional <strong>Zimbra</strong> de Correos de Bolivia.</p>
                    <p style="margin: 0; font-family: monospace; font-size: 10px; color: #94a3b8;">Fecha de emisión: {{ now()->translatedFormat('d \d\e F \d\e Y - H:i') }}</p>
                  </div>

                </div>

              </div>
            </div>

            {{-- Acciones del Modal en Vista Previa --}}
            <div class="app-modal-actions mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
              <button
                type="button"
                wire:click="setEmailModalTab('redactar')"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs"
              >
                <span>← Volver a editar texto</span>
              </button>

              <div class="flex items-center gap-3">
                <button type="button" wire:click="closeEmailModal" class="app-modal-secondary cursor-pointer">Cerrar</button>
                <button
                  type="button"
                  wire:click="enviarCorreosPersonal"
                  wire:loading.attr="disabled"
                  wire:target="enviarCorreosPersonal"
                  @disabled($emailDestinatariosLista->count() === 0)
                  class="login-submit app-modal-submit inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <span wire:loading.remove wire:target="enviarCorreosPersonal">
                    ✉️ Enviar a {{ $emailDestinatariosLista->count() }} destinatarios
                  </span>
                  <span wire:loading wire:target="enviarCorreosPersonal" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Enviando correos...</span>
                  </span>
                </button>
              </div>
            </div>

          </div>
        @endif

      </div>
    </div>
  @endif

  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Editar personal</p>
            <h3 class="section-title app-modal-title">Actualizar datos del integrante</h3>
            <p class="section-copy-sm">Modifica nombre, codigo biometrico, fecha de nacimiento, area y sucursal del personal.</p>
          </div>
          <button type="button" wire:click="closeEditModal" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        <form wire:submit="updateEmpleado" class="mt-8 grid gap-5 md:grid-cols-2">
          {{-- FOTOGRAFÍA DEL PERSONAL --}}
          <div class="md:col-span-2 flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
            <div class="emp-photo-preview-box" style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; max-width: 80px; max-height: 80px; border-radius: 1rem; overflow: hidden; position: relative; flex-shrink: 0;">
              @if ($fotoNueva)
                <img src="{{ $fotoNueva->temporaryUrl() }}" alt="Nueva foto" width="80" height="80" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px; object-fit: cover; object-position: center top; display: block; border-radius: 0.9rem;" class="emp-photo-img border-2 border-indigo-500 shadow-md">
              @elseif ($editFotoActual && !$eliminarFoto)
                <img src="{{ $editFotoActual }}" alt="Foto actual" width="80" height="80" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px; object-fit: cover; object-position: center top; display: block; border-radius: 0.9rem;" class="emp-photo-img border-2 border-slate-200 shadow-sm">
              @else
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 0.9rem; color: #94a3b8;">
                  <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 28px; height: 28px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  <span style="font-size: 9px; font-weight: 700; margin-top: 4px;">Sin foto</span>
                </div>
              @endif
              <div wire:loading wire:target="fotoNueva" class="absolute inset-0 bg-white/80 rounded-2xl flex items-center justify-center" style="position: absolute; inset: 0; background-color: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center;">
                <span class="text-xs text-indigo-600 font-bold animate-pulse">Cargando...</span>
              </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <label class="block text-xs font-bold text-slate-700 mb-1">Fotografía del personal</label>
              <p class="text-[11px] text-slate-500 mb-2">Formato JPG, PNG o WEBP (máx. 4MB). Visible en el portal de horas y credencial.</p>
              <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                <label for="edit-foto-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 cursor-pointer transition-colors shadow-2xs">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <span>{{ ($editFotoActual && !$eliminarFoto) || $fotoNueva ? 'Cambiar foto' : 'Subir foto' }}</span>
                </label>
                <input type="file" id="edit-foto-input" wire:model="fotoNueva" accept="image/jpeg,image/png,image/webp" class="hidden">
                @if (($editFotoActual && !$eliminarFoto) || $fotoNueva)
                  <button type="button" wire:click="quitarFoto" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold cursor-pointer">
                    ✕ Quitar foto
                  </button>
                @endif
                @if ($eliminarFoto)
                  <span class="text-[11px] text-rose-600 font-bold bg-rose-50 px-2 py-1 rounded-md border border-rose-200">Foto marcada para eliminar al guardar</span>
                @endif
              </div>
              @error('fotoNueva') <p class="form-error mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          <div>
            <label class="form-label">Nombre del personal</label>
            <input type="text" wire:model="editNombre" class="form-input" placeholder="Ej. Maria">
            @error('editNombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Apellido</label>
            <input type="text" wire:model="editApellido" class="form-input" placeholder="Ej. Perez">
            @error('editApellido') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Codigo biometrico</label>
            <input type="text" wire:model="editCodigoBiometrico" class="form-input" placeholder="Ej. 1045">
            @error('editCodigoBiometrico') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Correo electronico</label>
            <input type="email" wire:model="editEmail" class="form-input" placeholder="Ej. nombre.apellido@correos.gob.bo">
            @error('editEmail') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Area</label>
            <input type="text" wire:model="editArea" class="form-input" placeholder="Opcional">
            @error('editArea') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Sucursal</label>
            <input type="text" wire:model="editSucursal" class="form-input" placeholder="Ej. La Paz">
            @error('editSucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" wire:model="editFechaNacimiento" class="form-input">
            @error('editFechaNacimiento') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeEditModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  @if ($showDetailModal)
    <div class="app-modal-backdrop" wire:click="closeDetailModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <button type="button" wire:click="closeDetailModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div class="flex items-center gap-3">
            @if(!empty($detailEmpleado['foto_url']))
              <div class="emp-photo-detail-box" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; border-radius: 1.1rem; overflow: hidden; flex-shrink: 0; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);">
                <img src="{{ $detailEmpleado['foto_url'] }}" alt="{{ $detailEmpleado['nombre_completo'] ?? '' }}" width="56" height="56" style="width: 56px; height: 56px; min-width: 56px; max-width: 56px; min-height: 56px; max-height: 56px; object-fit: cover; object-position: center top; display: block;" class="emp-photo-detail-img">
              </div>
            @else
              <div class="emp-photo-detail-box" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; border-radius: 1.1rem; background: #eef2ff; border: 1.5px solid #c7d2fe; color: #4338ca; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                {{ strtoupper(substr($detailEmpleado['nombre_completo'] ?? 'P', 0, 2)) }}
              </div>
            @endif
            <div>
              <p class="section-kicker">Ficha del personal</p>
              <h3 class="section-title app-modal-title">{{ $detailEmpleado['nombre_completo'] ?? 'Detalle del personal' }}</h3>
              <p class="section-copy-sm">Resumen del perfil, horario regional asignado y detalle mensual de marcaciones.</p>
            </div>
          </div>
          <div class="flex flex-col gap-3 md:items-end">
            <div class="w-full min-w-[16rem] md:w-auto">
              <label for="detail-reference-month" class="form-label">Filtrar por mes</label>
              <select id="detail-reference-month" wire:model.live="detailReferenceMonth" class="form-input min-w-[16rem]">
                @foreach ($detailMonthOptions as $option)
                  <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        @php
          $detailMarcaciones = collect($detailEmpleado['marcaciones_mes'] ?? []);
          $detailAtrasos = $detailMarcaciones->filter(function (array $item) {
              $retraso = trim((string) ($item['retraso'] ?? '0 min'));

              return $retraso !== '' && $retraso !== '0 min' && $retraso !== '0';
          })->values();
          $detailOmisiones = $detailMarcaciones->filter(function (array $item) {
              $entrada = trim((string) ($item['entrada'] ?? '--:--'));
              $estado = mb_strtolower((string) ($item['estado'] ?? ''));
              $estadoBiometrico = mb_strtolower((string) ($item['estado_biometrico'] ?? ''));

              return $entrada === '--:--'
                  || str_contains($estado, 'olvido')
                  || str_contains($estadoBiometrico, 'olvido')
                  || str_contains($estadoBiometrico, 'sin entrada');
          })->values();
        @endphp

        <div
          class="mt-5"
          x-data="{ detailTab: 'resumen' }"
        >
          <div class="report-tab-nav" role="tablist" aria-label="Vista detallada del personal">
            <button type="button" role="tab" :aria-selected="detailTab === 'resumen'" @click="detailTab = 'resumen'" :class="detailTab === 'resumen' ? 'report-tab-button-active' : ''" class="report-tab-button" id="detail-tab-resumen">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
              <span>Resumen</span>
            </button>
            <button type="button" role="tab" :aria-selected="detailTab === 'atrasos'" @click="detailTab = 'atrasos'" :class="detailTab === 'atrasos' ? 'report-tab-button-active' : ''" class="report-tab-button" id="detail-tab-atrasos">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
              <span>Atrasos</span>
              @if($detailAtrasos->count() > 0)
                <span class="report-tab-badge report-tab-badge-amber">{{ $detailAtrasos->count() }}</span>
              @endif
            </button>
            <button type="button" role="tab" :aria-selected="detailTab === 'omisiones'" @click="detailTab = 'omisiones'" :class="detailTab === 'omisiones' ? 'report-tab-button-active' : ''" class="report-tab-button" id="detail-tab-omisiones">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/><circle cx="12" cy="16" r="1.5"/></svg>
              <span>Omisiones</span>
              @if($detailOmisiones->count() > 0)
                <span class="report-tab-badge report-tab-badge-rose">{{ $detailOmisiones->count() }}</span>
              @endif
            </button>
            <button type="button" role="tab" :aria-selected="detailTab === 'todo'" @click="detailTab = 'todo'" :class="detailTab === 'todo' ? 'report-tab-button-active' : ''" class="report-tab-button" id="detail-tab-todo">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
              <span>Todo</span>
            </button>
          </div>

          <div x-show="detailTab === 'resumen'" x-transition.opacity.duration.200ms role="tabpanel">
            <div class="mt-5 rounded-[1.1rem] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Mostrando resumen mensual de:
              <strong class="text-slate-900">{{ $detailEmpleado['mes_referencia'] ?? '-' }}</strong>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Codigo</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['codigo_biometrico'] ?? 'Sin asignar' }}</strong>
              </div>
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Sucursal</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['sucursal'] ?? 'Sin sucursal' }}</strong>
              </div>
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Horario</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['hora_entrada_programada'] ?? '--:--' }} - {{ $detailEmpleado['hora_salida_programada'] ?? '--:--' }}</strong>
              </div>
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Dias tarde</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['dias_tarde'] ?? 0 }}</strong>
              </div>
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Estado</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['estado_laboral'] ?? 'Activo' }}</strong>
              </div>
              <div class="metric-card metric-card-detail">
                <p class="metric-label">Ultima marcacion</p>
                <strong class="metric-value metric-value-detail">{{ $detailEmpleado['ultima_marcacion'] ?? 'Sin marcaciones' }}</strong>
              </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
              <div class="detail-info-card md:col-span-2">
                <p class="metric-label">Correo institucional</p>
                <p class="detail-info-value font-mono text-sm text-[#0f67c0]">{{ $detailEmpleado['email'] ?? 'Sin correo asignado' }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Area</p>
                <p class="detail-info-value">{{ $detailEmpleado['area'] ?? 'Sin area' }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Nacimiento</p>
                <p class="detail-info-value">{{ $detailEmpleado['fecha_nacimiento'] ?? 'Sin fecha' }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Horas del mes</p>
                <p class="detail-info-value">{{ $detailEmpleado['horas_mes'] ?? '00:00' }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Retraso del mes</p>
                <p class="detail-info-value">{{ $detailEmpleado['retraso_mes'] ?? '0 min' }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Olvidos del mes</p>
                <p class="detail-info-value">{{ $detailEmpleado['olvidos_marcacion'] ?? 0 }}</p>
              </div>
              <div class="detail-info-card">
                <p class="metric-label">Saldo de tolerancia</p>
                <p class="detail-info-value">{{ $detailEmpleado['saldo_mes'] ?? '0 min' }}</p>
              </div>
            </div>
          </div>

          <div x-show="detailTab === 'atrasos'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="section-head-row">
              <div>
                <p class="section-kicker">Atrasos del mes</p>
                <h4 class="section-title text-2xl">Dias que llegaron tarde</h4>
              </div>
            </div>

            <div class="history-table-shell mt-4">
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Dia</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Retraso</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($detailAtrasos as $tardanza)
                    <tr>
                      <td>{{ $tardanza['fecha'] }}</td>
                      <td>{{ $tardanza['dia'] }}</td>
                      <td>{{ $tardanza['entrada'] }}</td>
                      <td>{{ $tardanza['salida'] }}</td>
                      <td>{{ $tardanza['retraso'] }}</td>
                      <td>{{ $tardanza['estado'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-slate-400">No se registraron atrasos en el mes de referencia.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div x-show="detailTab === 'omisiones'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="section-head-row">
              <div>
                <p class="section-kicker">Omisiones del mes</p>
                <h4 class="section-title text-2xl">Dias sin marcacion de entrada</h4>
              </div>
            </div>

            <div class="history-table-shell mt-4">
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Dia</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Biometrico</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($detailOmisiones as $omision)
                    <tr>
                      <td>{{ $omision['fecha'] }}</td>
                      <td>{{ $omision['dia'] }}</td>
                      <td>{{ $omision['entrada'] }}</td>
                      <td>{{ $omision['salida'] }}</td>
                      <td>{{ $omision['estado'] }}</td>
                      <td>{{ $omision['estado_biometrico'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-slate-400">No se registraron omisiones en el mes de referencia.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div x-show="detailTab === 'todo'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="detail-marking-filter-row">
              <button
                type="button"
                wire:click="setDetailMarkingFilter('salida')"
                class="detail-marking-filter-button {{ $detailMarkingFilter === 'salida' ? 'detail-marking-filter-button-active' : '' }}"
              >
                Salida
              </button>
              <button
                type="button"
                wire:click="setDetailMarkingFilter('entrada')"
                class="detail-marking-filter-button {{ $detailMarkingFilter === 'entrada' ? 'detail-marking-filter-button-active' : '' }}"
              >
                Entrada
              </button>
            </div>

            <div class="mt-8">
              <div class="section-head-row">
                <div>
                  <p class="section-kicker">Marcaciones del mes</p>
                  <h4 class="section-title text-2xl">Detalle completo</h4>
                </div>
              </div>

              <div class="history-table-shell mt-4">
                <table class="history-table">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Dia</th>
                      <th>Entrada</th>
                      <th>Salida</th>
                      <th>Retraso</th>
                      <th>Estado</th>
                      <th>Biometrico</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse (($detailEmpleado['marcaciones_mes'] ?? []) as $tardanza)
                      <tr>
                        <td>{{ $tardanza['fecha'] }}</td>
                        <td>{{ $tardanza['dia'] }}</td>
                        <td>{{ $tardanza['entrada'] }}</td>
                        <td>{{ $tardanza['salida'] }}</td>
                        <td>{{ $tardanza['retraso'] }}</td>
                        <td>{{ $tardanza['estado'] }}</td>
                        <td>{{ $tardanza['estado_biometrico'] }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center text-slate-400">No se registraron marcaciones en el mes de referencia.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @if ($showPdfModal)
    <div class="app-modal-backdrop" wire:click="closePdfModal">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <div class="app-modal-head pdf-modal-head">
          <div>
            <p class="section-kicker">Exportacion del personal</p>
            <h3 class="section-title app-modal-title">Ficha lista para PDF</h3>
            <p class="section-copy-sm">Revisa la informacion consolidada del personal y usa el boton de PDF para guardarla o imprimirla.</p>
          </div>
          <div class="flex flex-col gap-5 md:items-end pdf-modal-controls">
            <div class="w-full min-w-[16rem] md:w-auto">
              <label for="pdf-reference-month" class="form-label">Seleccionar mes</label>
              <select id="pdf-reference-month" wire:model.live="pdfReferenceMonth" class="form-input min-w-[16rem]">
                @foreach ($pdfMonthOptions as $option)
                  <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="app-modal-actions">
              <button type="button" wire:click="descargarPdfEmpleado" class="table-action-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
                </svg>
                <span>PDF</span>
              </button>
              <button type="button" wire:click="closePdfModal" class="app-modal-close" aria-label="Cerrar modal">X</button>
            </div>
          </div>
        </div>

        @php
          $pdfMarcaciones = collect($pdfEmpleado['marcaciones_mes'] ?? []);
          $pdfAtrasos = $pdfMarcaciones->filter(function (array $item) {
              $retraso = trim((string) ($item['retraso'] ?? '0 min'));

              return $retraso !== '' && $retraso !== '0 min' && $retraso !== '0';
          })->values();
          $pdfOmisiones = $pdfMarcaciones->filter(function (array $item) {
              $entrada = trim((string) ($item['entrada'] ?? '--:--'));
              $estado = mb_strtolower((string) ($item['estado'] ?? ''));
              $estadoBiometrico = mb_strtolower((string) ($item['estado_biometrico'] ?? ''));

              return $entrada === '--:--'
                  || str_contains($estado, 'olvido')
                  || str_contains($estadoBiometrico, 'olvido')
                  || str_contains($estadoBiometrico, 'sin entrada');
          })->values();
        @endphp

        <div class="mt-8" x-data="{ pdfTab: 'resumen' }">
          <div class="report-tab-nav pdf-tab-nav" role="tablist" aria-label="Vista de exportacion del personal">
            <button type="button" role="tab" :aria-selected="pdfTab === 'resumen'" @click="pdfTab = 'resumen'" :class="pdfTab === 'resumen' ? 'report-tab-button-active' : ''" class="report-tab-button" id="pdf-tab-resumen">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
              <span>Resumen</span>
            </button>
            <button type="button" role="tab" :aria-selected="pdfTab === 'atrasos'" @click="pdfTab = 'atrasos'" :class="pdfTab === 'atrasos' ? 'report-tab-button-active' : ''" class="report-tab-button" id="pdf-tab-atrasos">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
              <span>Atrasos</span>
              @if($pdfAtrasos->count() > 0)
                <span class="report-tab-badge report-tab-badge-amber">{{ $pdfAtrasos->count() }}</span>
              @endif
            </button>
            <button type="button" role="tab" :aria-selected="pdfTab === 'omisiones'" @click="pdfTab = 'omisiones'" :class="pdfTab === 'omisiones' ? 'report-tab-button-active' : ''" class="report-tab-button" id="pdf-tab-omisiones">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V6a3 3 0 0 1 6 0v5"/><rect x="5" y="11" width="14" height="11" rx="2"/><circle cx="12" cy="16" r="1.5"/></svg>
              <span>Omisiones</span>
              @if($pdfOmisiones->count() > 0)
                <span class="report-tab-badge report-tab-badge-rose">{{ $pdfOmisiones->count() }}</span>
              @endif
            </button>
            <button type="button" role="tab" :aria-selected="pdfTab === 'todo'" @click="pdfTab = 'todo'" :class="pdfTab === 'todo' ? 'report-tab-button-active' : ''" class="report-tab-button" id="pdf-tab-todo">
              <svg xmlns="http://www.w3.org/2000/svg" class="report-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
              <span>Todo</span>
            </button>
          </div>

          <div x-show="pdfTab === 'resumen'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="pdf-export-grid pdf-export-grid-primary">
              <div class="pdf-export-card pdf-export-card-highlight">
                <p class="pdf-export-label">Nombre</p>
                <strong class="pdf-export-value">{{ $pdfEmpleado['nombre_completo'] ?? 'Sin nombre' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Codigo</p>
                <strong class="pdf-export-value">{{ $pdfEmpleado['codigo_biometrico'] ?? 'Sin asignar' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Sucursal</p>
                <strong class="pdf-export-value">{{ $pdfEmpleado['sucursal'] ?? 'Sin sucursal' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Estado mensual</p>
                <strong class="pdf-export-value">{{ $pdfEmpleado['estado_retraso'] ?? 'Sin estado' }}</strong>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Estado laboral</p>
                <strong class="pdf-export-value">{{ $pdfEmpleado['estado_laboral'] ?? 'Activo' }}</strong>
              </div>
            </div>

            <div class="pdf-export-grid pdf-export-grid-secondary mt-6">
              <div class="pdf-export-card">
                <p class="pdf-export-label">Horas del mes</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['horas_mes'] ?? '00:00' }}</p>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Retraso del mes</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['retraso_mes'] ?? '0 min' }}</p>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Olvidos del mes</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['olvidos_marcacion'] ?? 0 }}</p>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Saldo de tolerancia</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['saldo_mes'] ?? '0 min' }}</p>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Dias tarde</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['dias_tarde'] ?? 0 }}</p>
              </div>
              <div class="pdf-export-card">
                <p class="pdf-export-label">Verificacion hoy</p>
                <p class="pdf-export-value-sm">{{ $pdfEmpleado['verificacion_hoy'] ?? 'Sin registro' }}</p>
              </div>
            </div>
          </div>

          <div x-show="pdfTab === 'atrasos'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="history-table-shell pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Atrasos del mes</p>
                  <h4 class="section-title text-2xl">Dias que llegaron tarde</h4>
                </div>
              </div>

              <table class="history-table mt-4">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Dia</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Retraso</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($pdfAtrasos as $tardanza)
                    <tr>
                      <td>{{ $tardanza['fecha'] }}</td>
                      <td>{{ $tardanza['dia'] }}</td>
                      <td>{{ $tardanza['entrada'] }}</td>
                      <td>{{ $tardanza['salida'] }}</td>
                      <td>{{ $tardanza['retraso'] }}</td>
                      <td>{{ $tardanza['estado'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-slate-400">No existen atrasos registrados para exportar.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div x-show="pdfTab === 'omisiones'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-6">
            <div class="history-table-shell pdf-export-table-shell">
              <div class="section-head-row pdf-export-section-head">
                <div>
                  <p class="section-kicker">Omisiones del mes</p>
                  <h4 class="section-title text-2xl">Dias sin marcacion de entrada</h4>
                </div>
              </div>

              <table class="history-table mt-4">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Dia</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Biometrico</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($pdfOmisiones as $omision)
                    <tr>
                      <td>{{ $omision['fecha'] }}</td>
                      <td>{{ $omision['dia'] }}</td>
                      <td>{{ $omision['entrada'] }}</td>
                      <td>{{ $omision['salida'] }}</td>
                      <td>{{ $omision['estado'] }}</td>
                      <td>{{ $omision['estado_biometrico'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-slate-400">No existen omisiones registradas para exportar.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div x-show="pdfTab === 'todo'" x-transition.opacity.duration.200ms role="tabpanel" class="mt-8">
            <div id="employee-pdf-content" class="pdf-export-sheet mt-8 space-y-8">
          <header class="pdf-export-header">
            <div>
              <p class="pdf-export-kicker">Correos de Bolivia</p>
              <h2 class="pdf-export-title">Ficha individual del personal</h2>
              <p class="pdf-export-copy">Resumen operativo del perfil, horario, consumo de tolerancia y registro de tardanzas.</p>
            </div>
            <div class="pdf-export-badge">
              <span class="pdf-export-badge-label">Mes</span>
              <strong class="pdf-export-badge-value">{{ $pdfEmpleado['mes_referencia'] ?? '-' }}</strong>
            </div>
          </header>

          <div class="pdf-export-grid pdf-export-grid-primary">
            <div class="pdf-export-card pdf-export-card-highlight">
              <p class="pdf-export-label">Nombre</p>
              <strong class="pdf-export-value">{{ $pdfEmpleado['nombre_completo'] ?? 'Sin nombre' }}</strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Codigo</p>
              <strong class="pdf-export-value">{{ $pdfEmpleado['codigo_biometrico'] ?? 'Sin asignar' }}</strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Sucursal</p>
              <strong class="pdf-export-value">{{ $pdfEmpleado['sucursal'] ?? 'Sin sucursal' }}</strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Estado mensual</p>
              <strong class="pdf-export-value">{{ $pdfEmpleado['estado_retraso'] ?? 'Sin estado' }}</strong>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Estado laboral</p>
              <strong class="pdf-export-value">{{ $pdfEmpleado['estado_laboral'] ?? 'Activo' }}</strong>
            </div>
          </div>

          <div class="pdf-export-grid pdf-export-grid-secondary">
            <div class="pdf-export-card">
              <p class="pdf-export-label">Contratacion</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['fecha_contratacion'] ?? 'Sin fecha' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Nacimiento</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['fecha_nacimiento'] ?? 'Sin fecha' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Ultima marcacion</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['ultima_marcacion'] ?? 'Sin marcaciones' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Horario regional</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['hora_entrada_programada'] ?? '--:--' }} - {{ $pdfEmpleado['hora_salida_programada'] ?? '--:--' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Horas del mes</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['horas_mes'] ?? '00:00' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Retraso del mes</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['retraso_mes'] ?? '0 min' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Olvidos del mes</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['olvidos_marcacion'] ?? 0 }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Saldo de tolerancia</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['saldo_mes'] ?? '0 min' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Tolerancia mensual</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['tolerancia_mensual'] ?? '0 min' }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Dias tarde</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['dias_tarde'] ?? 0 }}</p>
            </div>
            <div class="pdf-export-card">
              <p class="pdf-export-label">Verificacion hoy</p>
              <p class="pdf-export-value-sm">{{ $pdfEmpleado['verificacion_hoy'] ?? 'Sin registro' }}</p>
            </div>
          </div>

          <div class="history-table-shell pdf-export-table-shell">
            <div class="section-head-row pdf-export-section-head">
              <div>
                <p class="section-kicker">Marcaciones del mes</p>
                <h4 class="section-title text-2xl">Detalle para exportacion</h4>
              </div>
            </div>

            <table class="history-table mt-4">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Dia</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Retraso</th>
                  <th>Estado</th>
                  <th>Biometrico</th>
                </tr>
              </thead>
              <tbody>
                @forelse (($pdfEmpleado['marcaciones_mes'] ?? []) as $tardanza)
                  <tr>
                    <td>{{ $tardanza['fecha'] }}</td>
                    <td>{{ $tardanza['dia'] }}</td>
                    <td>{{ $tardanza['entrada'] }}</td>
                    <td>{{ $tardanza['salida'] }}</td>
                    <td>{{ $tardanza['retraso'] }}</td>
                    <td>{{ $tardanza['estado'] }}</td>
                    <td>{{ $tardanza['estado_biometrico'] }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-slate-400">No existen marcaciones registradas para exportar.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ========================================================================= --}}
  {{-- MODAL 1: ATRASOS DEL PERSONAL --}}
  {{-- ========================================================================= --}}
  @if ($showModalAtrasos)
    <div class="app-modal-backdrop" wire:click="closeModalAtrasos">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Historial de Puntualidad</p>
            <h3 class="section-title app-modal-title">Detalle de Atrasos</h3>
            <p class="section-copy-sm">Listado detallado de ingresos con retraso para el período consultado.</p>
          </div>
          <button type="button" wire:click="closeModalAtrasos" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        @if($marcacionesEmpleadoInfo)
          <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50/70 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Personal</p>
                <h4 class="text-sm font-bold text-slate-900">{{ $marcacionesEmpleadoInfo['nombre_completo'] }}</h4>
                <span class="font-mono text-slate-500">Cód: <strong>{{ $marcacionesEmpleadoInfo['codigo'] }}</strong> | Sucursal: <strong>{{ $marcacionesEmpleadoInfo['sucursal'] }}</strong></span>
              </div>
              <div class="flex items-center gap-3">
                <div class="text-right">
                  <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Total atrasos</p>
                  <span class="text-base font-extrabold text-amber-700">{{ $marcacionesStats['total_atrasos'] ?? 0 }} días</span>
                </div>
                <div class="text-right border-l border-amber-200 pl-3">
                  <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Tiempo acumulado</p>
                  <span class="text-base font-extrabold text-amber-700">{{ $marcacionesStats['retraso_acumulado_formateado'] ?? '0 min' }}</span>
                </div>
              </div>
            </div>
          </div>
        @endif

        <div class="history-table-shell mt-4 max-h-[400px] overflow-y-auto">
          <table class="history-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th class="text-center">Hora Entrada</th>
                <th class="text-center">Hora Programada</th>
                <th class="text-center">Retraso</th>
              </tr>
            </thead>
            <tbody>
              @forelse (($marcacionesStats['lista_atrasos'] ?? []) as $item)
                <tr>
                  <td><strong class="text-slate-900 font-bold">{{ $item['fecha'] }}</strong></td>
                  <td class="capitalize">{{ $item['dia'] }}</td>
                  <td class="text-center">
                    <span class="inline-block rounded-md bg-amber-100 border border-amber-300 px-2 py-0.5 font-mono text-xs font-bold text-amber-900">
                      {{ $item['entrada'] }}
                    </span>
                  </td>
                  <td class="text-center font-mono text-xs text-slate-600">{{ $item['hora_programada'] }}</td>
                  <td class="text-center">
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                      +{{ $item['retraso_formateado'] }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="py-8 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">¡Excelente puntualidad!</p>
                    <p class="text-[11px] text-slate-400">No se registraron atrasos en el período seleccionado.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-5 app-modal-actions">
          <button type="button" wire:click="closeModalAtrasos" class="app-modal-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- ========================================================================= --}}
  {{-- MODAL 2: OMISIONES DEL PERSONAL --}}
  {{-- ========================================================================= --}}
  @if ($showModalOmisiones)
    <div class="app-modal-backdrop" wire:click="closeModalOmisiones">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Control de Marcación</p>
            <h3 class="section-title app-modal-title">Detalle de Omisiones</h3>
            <p class="section-copy-sm">Marcaciones incompletas u olvidos de registro de entrada o salida.</p>
          </div>
          <button type="button" wire:click="closeModalOmisiones" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        @if($marcacionesEmpleadoInfo)
          <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50/70 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Personal</p>
                <h4 class="text-sm font-bold text-slate-900">{{ $marcacionesEmpleadoInfo['nombre_completo'] }}</h4>
                <span class="font-mono text-slate-500">Cód: <strong>{{ $marcacionesEmpleadoInfo['codigo'] }}</strong> | Sucursal: <strong>{{ $marcacionesEmpleadoInfo['sucursal'] }}</strong></span>
              </div>
              <div class="text-right">
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Total omisiones</p>
                <span class="text-base font-extrabold text-rose-700">{{ $marcacionesStats['total_omisiones'] ?? 0 }} registros</span>
              </div>
            </div>
          </div>
        @endif

        <div class="history-table-shell mt-4 max-h-[400px] overflow-y-auto">
          <table class="history-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th class="text-center">Hora Entrada</th>
                <th class="text-center">Hora Salida</th>
                <th>Tipo de Omisión</th>
              </tr>
            </thead>
            <tbody>
              @forelse (($marcacionesStats['lista_omisiones'] ?? []) as $item)
                <tr>
                  <td><strong class="text-slate-900 font-bold">{{ $item['fecha'] }}</strong></td>
                  <td class="capitalize">{{ $item['dia'] }}</td>
                  <td class="text-center font-mono text-xs">
                    @if($item['entrada'] !== '--:--')
                      <span class="inline-block rounded-md bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-emerald-800 font-bold">{{ $item['entrada'] }}</span>
                    @else
                      <span class="inline-block rounded-md bg-rose-100 border border-rose-200 px-2 py-0.5 text-rose-700 font-bold">Sin marcar</span>
                    @endif
                  </td>
                  <td class="text-center font-mono text-xs">
                    @if($item['salida'] !== '--:--')
                      <span class="inline-block rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 text-slate-700 font-bold">{{ $item['salida'] }}</span>
                    @else
                      <span class="inline-block rounded-md bg-rose-100 border border-rose-200 px-2 py-0.5 text-rose-700 font-bold">Sin marcar</span>
                    @endif
                  </td>
                  <td>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                      {{ $item['tipo_omision'] }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="py-8 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">¡Sin omisiones!</p>
                    <p class="text-[11px] text-slate-400">Todas las jornadas cuentan con marcaciones completas.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-5 app-modal-actions">
          <button type="button" wire:click="closeModalOmisiones" class="app-modal-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- ========================================================================= --}}
  {{-- MODAL 3: FALTAS DEL PERSONAL --}}
  {{-- ========================================================================= --}}
  @if ($showModalFaltas)
    <div class="app-modal-backdrop" wire:click="closeModalFaltas">
      <div class="app-modal-card app-modal-card-detail" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Asistencia Laboral</p>
            <h3 class="section-title app-modal-title">Detalle de Faltas</h3>
            <p class="section-copy-sm">Días laborables sin registro de marcación ni permiso justificado.</p>
          </div>
          <button type="button" wire:click="closeModalFaltas" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        @if($marcacionesEmpleadoInfo)
          <div class="mt-4 rounded-xl border border-red-200 bg-red-50/70 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-red-800">Personal</p>
                <h4 class="text-sm font-bold text-slate-900">{{ $marcacionesEmpleadoInfo['nombre_completo'] }}</h4>
                <span class="font-mono text-slate-500">Cód: <strong>{{ $marcacionesEmpleadoInfo['codigo'] }}</strong> | Sucursal: <strong>{{ $marcacionesEmpleadoInfo['sucursal'] }}</strong></span>
              </div>
              <div class="text-right">
                <p class="text-[11px] font-bold uppercase tracking-wider text-red-800">Total inasistencias</p>
                <span class="text-base font-extrabold text-red-700">{{ $marcacionesStats['total_faltas'] ?? 0 }} días</span>
              </div>
            </div>
          </div>
        @endif

        <div class="history-table-shell mt-4 max-h-[400px] overflow-y-auto">
          <table class="history-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th>Horario Esperado</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              @forelse (($marcacionesStats['lista_faltas'] ?? []) as $item)
                <tr>
                  <td><strong class="text-slate-900 font-bold">{{ $item['fecha'] }}</strong></td>
                  <td class="capitalize">{{ $item['dia'] }}</td>
                  <td class="font-mono text-xs text-slate-700">{{ $item['horario_esperado'] }}</td>
                  <td>
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                      {{ $item['estado'] }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="py-8 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">¡Asistencia 100% cumplida!</p>
                    <p class="text-[11px] text-slate-400">No se detectaron faltas en el período seleccionado.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-5 app-modal-actions">
          <button type="button" wire:click="closeModalFaltas" class="app-modal-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- ========================================================================= --}}
  {{-- MODAL 4: RESUMEN GLOBAL (HORAS Y ATRASOS ACUMULADOS) --}}
  {{-- ========================================================================= --}}
  @if ($showModalGlobal)
    <div class="app-modal-backdrop" wire:click="closeModalGlobal">
      <div class="app-modal-card app-modal-card-detail max-w-4xl" x-on:click.stop>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Consolidado General</p>
            <h3 class="section-title app-modal-title">Horas y Atrasos Acumulados</h3>
            <p class="section-copy-sm">Resumen consolidado de horas efectivas laboradas, tiempo acumulado de retraso y tolerancia.</p>
          </div>
          <button type="button" wire:click="closeModalGlobal" class="app-modal-close" aria-label="Cerrar modal">X</button>
        </div>

        @if($marcacionesEmpleadoInfo)
          <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50/70 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Personal</p>
                <h4 class="text-sm font-bold text-slate-900">{{ $marcacionesEmpleadoInfo['nombre_completo'] }}</h4>
                <span class="font-mono text-slate-500">Cód: <strong>{{ $marcacionesEmpleadoInfo['codigo'] }}</strong> | Sucursal: <strong>{{ $marcacionesEmpleadoInfo['sucursal'] }}</strong></span>
              </div>
              <div class="flex items-center gap-2">
                <span class="rounded-lg bg-white border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-900">
                  Área: <strong>{{ $marcacionesEmpleadoInfo['area'] }}</strong>
                </span>
              </div>
            </div>
          </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
          <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Horas acumuladas</p>
            <p class="text-lg font-extrabold text-[#0f67c0] mt-0.5">{{ $marcacionesStats['horas_trabajadas_formateado'] ?? '0h 00m' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Retraso acumulado</p>
            <p class="text-lg font-extrabold {{ ($marcacionesStats['minutos_atraso_totales'] ?? 0) > 30 ? 'text-rose-600' : 'text-amber-600' }} mt-0.5">
              {{ $marcacionesStats['retraso_acumulado_formateado'] ?? '0 min' }}
            </p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tolerancia mensual</p>
            <p class="text-xs font-bold {{ ($marcacionesStats['estado_tolerancia'] ?? '') === 'Excedido' ? 'text-rose-700' : 'text-emerald-700' }} mt-1">
              {{ $marcacionesStats['estado_tolerancia'] ?? 'Dentro de tolerancia' }}
            </p>
            <span class="text-[10px] text-slate-500 font-medium block">{{ $marcacionesStats['saldo_tolerancia'] ?? '' }}</span>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Días laborados</p>
            <p class="text-lg font-extrabold text-slate-800 mt-0.5">{{ $marcacionesStats['dias_con_marcacion'] ?? 0 }} días</p>
          </div>
        </div>

        <div class="history-table-shell mt-4 max-h-[350px] overflow-y-auto">
          <table class="history-table">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th class="text-center">Entrada</th>
                <th class="text-center">Salida</th>
                <th class="text-center">Horas Día</th>
                <th class="text-center">Retraso</th>
                <th class="text-center">Estado</th>
              </tr>
            </thead>
            <tbody>
              @forelse (($marcacionesStats['desglose_global'] ?? []) as $item)
                <tr>
                  <td><strong class="text-slate-900 font-bold">{{ $item['fecha'] }}</strong></td>
                  <td class="capitalize">{{ $item['dia'] }}</td>
                  <td class="text-center font-mono text-xs">{{ $item['entrada'] }}</td>
                  <td class="text-center font-mono text-xs">{{ $item['salida'] }}</td>
                  <td class="text-center font-mono text-xs font-bold text-slate-800">{{ $item['horas_trabajadas'] }}</td>
                  <td class="text-center font-mono text-xs">
                    @if($item['retraso'] !== 'Puntual' && $item['retraso'] !== '—')
                      <span class="text-rose-700 font-bold">{{ $item['retraso'] }}</span>
                    @else
                      <span class="text-slate-400">{{ $item['retraso'] }}</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($item['estado'] === 'Completo')
                      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[11px] font-bold">Completo</span>
                    @elseif($item['estado'] === 'Falta')
                      <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-[11px] font-bold">Falta</span>
                    @else
                      <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-bold">{{ $item['estado'] }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-8 text-center text-slate-500">No hay registros para este período.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-5 app-modal-actions">
          <button type="button" wire:click="closeModalGlobal" class="app-modal-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  @if ($showModalSancionados2Dias)
    <div class="app-modal-backdrop" wire:click="closeModalSancionados2Dias">
      <div class="app-modal-card max-w-4xl" x-on:click.stop>
        <button type="button" wire:click="closeModalSancionados2Dias" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        
        <div class="app-modal-head border-b border-slate-100 pb-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200 px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wider">
                🏛️ Art. 45 - Faltas con Sanción Económica
              </span>
              <span class="text-xs text-slate-400 font-mono">{{ $mes_resumen ?? '' }}</span>
            </div>
            <h3 class="section-title app-modal-title mt-2 text-slate-900 flex items-center gap-2">
              <span>Personal con Sanción por Tolerancia Excedida</span>
            </h3>
            <p class="section-copy-sm mt-1 text-slate-600">
              Listado de funcionarios que han superado el límite de tolerancia mensual y acumulan sanciones económicas deducibles de la remuneración mensual mediante Memorándum formal de la Dirección Administrativa Financiera (DAF).
            </p>
          </div>
        </div>

        {{-- Pestañas de filtrado dentro del modal --}}
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2">
            <button
              type="button"
              wire:click="setFiltroModalSancion('acumulativo_2')"
              class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition cursor-pointer {{ $filtroModalSancion === 'acumulativo_2' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
              <span>🚨 Acumulativo 2 (2+ días descuento)</span>
              <span class="rounded-full px-1.5 py-0.2 text-[10px] font-black {{ $filtroModalSancion === 'acumulativo_2' ? 'bg-white text-rose-700' : 'bg-rose-200 text-rose-800' }}">
                {{ $totalSancionadosAcumulativo2 ?? 0 }}
              </span>
            </button>

            <button
              type="button"
              wire:click="setFiltroModalSancion('todos')"
              class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition cursor-pointer {{ $filtroModalSancion === 'todos' ? 'bg-[#0f67c0] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
              <span>⚠️ Todos con Sanción Económica</span>
              <span class="rounded-full px-1.5 py-0.2 text-[10px] font-black {{ $filtroModalSancion === 'todos' ? 'bg-white text-[#0f67c0]' : 'bg-slate-200 text-slate-800' }}">
                {{ $totalSancionadosGeneral ?? 0 }}
              </span>
            </button>
          </div>

          <span class="text-[11px] text-slate-500 font-medium">
            Mostrando <strong>{{ count($empleadosSancionadosModal ?? []) }}</strong> funcionarios
          </span>
        </div>

        {{-- Métricas resumen del modal --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
          <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800">Funcionarios Sancionados</span>
            <p class="text-xl font-extrabold text-rose-950 mt-1">{{ count($empleadosSancionadosModal ?? []) }}</p>
            <span class="text-[10px] text-rose-700 block mt-0.5">En el criterio seleccionado</span>
          </div>
          <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Total Días de Descuento</span>
            <p class="text-xl font-extrabold text-amber-950 mt-1">
              {{ array_sum(array_map(fn($e) => (float)($e->resumen_asistencia['sancion_regla']['dias_sancion'] ?? 0), ($empleadosSancionadosModal ?? collect([]))->all())) }} días
            </p>
            <span class="text-[10px] text-amber-700 block mt-0.5">Haber mensual acumulado</span>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Total Retraso Acumulado</span>
            <p class="text-xl font-extrabold text-slate-900 mt-1">
              {{ array_sum(array_map(fn($e) => (int)($e->resumen_asistencia['retraso_mes'] ?? 0), ($empleadosSancionadosModal ?? collect([]))->all())) }} min
            </p>
            <span class="text-[10px] text-slate-500 block mt-0.5">En exceso de horario</span>
          </div>
          <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-3">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-900">Acción Requerida</span>
            <p class="text-xs font-bold text-purple-950 mt-1 leading-snug">Memorándum DAF</p>
            <span class="text-[10px] text-purple-700 block mt-0.5">Deducción en planilla</span>
          </div>
        </div>

        {{-- Tabla de Funcionarios Sancionados --}}
        <div class="history-table-shell mt-4 max-h-[380px] overflow-y-auto">
          <table class="history-table text-xs">
            <thead>
              <tr>
                <th>Personal / Código</th>
                <th>Sucursal</th>
                <th class="text-center">Retraso Acumulado</th>
                <th class="text-center">Tolerancia</th>
                <th class="text-center">Exceso</th>
                <th class="text-center">Sanción Aplicable (Art. 45)</th>
                <th class="text-center">Estado</th>
              </tr>
            </thead>
            <tbody>
              @forelse (($empleadosSancionadosModal ?? []) as $empSancionado)
                @php
                  $sanc = $empSancionado->resumen_asistencia['sancion_regla'] ?? [];
                  $retraso = (int)($empSancionado->resumen_asistencia['retraso_mes'] ?? 0);
                  $toler = (int)($empSancionado->resumen_asistencia['tolerancia_mensual'] ?? 35);
                  $exceso = max(0, $retraso - $toler);
                  $esAcum2 = $sanc['es_acumulativo_2'] ?? false;
                @endphp
                <tr class="{{ $esAcum2 ? 'bg-rose-50/50' : 'bg-amber-50/30' }}">
                  <td>
                    <div class="font-bold text-slate-900">{{ $empSancionado->nombre_completo }}</div>
                    <div class="text-[11px] text-slate-500 font-mono">
                      Cód: <strong>{{ $empSancionado->codigo_biometrico ?: '—' }}</strong>
                      @if($empSancionado->area) | {{ $empSancionado->area }} @endif
                    </div>
                  </td>
                  <td class="font-semibold text-slate-700">{{ $empSancionado->sucursal ?? '—' }}</td>
                  <td class="text-center font-mono font-bold text-rose-700">{{ $retraso }} min</td>
                  <td class="text-center font-mono text-slate-500">{{ $toler }} min</td>
                  <td class="text-center font-mono font-extrabold text-rose-800">+{{ $exceso }} min</td>
                  <td class="text-center">
                    @if($esAcum2)
                      <span class="inline-flex items-center gap-1 rounded-full bg-rose-600 text-white px-2.5 py-0.5 text-[11px] font-black shadow-xs">
                        🚨 {{ $sanc['sancion_texto'] ?? 'Dos (2) días' }} (-{{ $sanc['dias_sancion'] ?? 2 }}d)
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 rounded-full bg-amber-500 text-white px-2.5 py-0.5 text-[11px] font-bold shadow-xs">
                        ⚠️ {{ $sanc['sancion_texto'] ?? 'Medio día' }} (-{{ $sanc['dias_sancion'] ?? 0.5 }}d)
                      </span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="inline-flex items-center rounded-md bg-white border border-slate-300 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                      Emitir Memorándum
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">No hay funcionarios en este tramo de sanción</p>
                    <p class="text-[11px] text-slate-400">Todos los colaboradores se encuentran dentro de los márgenes aceptables.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
          <span class="text-xs text-slate-500">
            * Sanciones calculadas de acuerdo a la escala oficial vigente del <strong>Reglamento Interno de Personal (Art. 45)</strong>.
          </span>
          <button type="button" wire:click="closeModalSancionados2Dias" class="app-modal-secondary cursor-pointer">
            Cerrar
          </button>
        </div>

      </div>
    </div>
  @endif

  @if ($vista === 'personal')
  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <p class="section-kicker !mb-0">Personal registrado</p>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 px-2.5 py-0.5 text-xs font-black">
              {{ $totalActivosSistema ?? 93 }} colaboradores activos
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-xs font-medium">
              {{ $totalPadronSistema ?? 185 }} en padrón total
            </span>
          </div>
          <h3 class="section-title">Plantilla activa de RRHH</h3>
          <p class="section-copy-sm">Aquí solo se muestra el personal activo en servicio (con marcaciones en los últimos 30 días o personal especial). Si una persona supera 30 días de inactividad, se traslada a <a wire:navigate href="{{ route('personal', ['vista' => 'inactivos']) }}" class="text-[#0f67c0] font-bold underline">Personal Inactivo</a>.</p>
        </div>
        <div class="flex items-center gap-2">
          {{-- Estilos para botones compactos con texto expandible en hover --}}
          <style>
            .btn-action-expandable {
              display: inline-flex;
              align-items: center;
              justify-content: center;
              height: 40px;
              min-width: 40px;
              padding: 0 11px;
              border-radius: 12px;
              color: #ffffff;
              font-size: 12.5px;
              font-weight: 700;
              text-decoration: none;
              cursor: pointer;
              border: none;
              box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.06);
              transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
              overflow: hidden;
              white-space: nowrap;
            }
            .btn-action-expandable:hover {
              transform: translateY(-2px);
              box-shadow: 0 6px 16px rgba(0,0,0,0.16);
              padding: 0 16px;
            }
            .btn-action-expandable .btn-action-label {
              max-width: 0;
              opacity: 0;
              overflow: hidden;
              white-space: nowrap;
              margin-left: 0;
              transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, margin-left 0.3s ease;
              display: inline-block;
              vertical-align: middle;
            }
            .btn-action-expandable:hover .btn-action-label {
              max-width: 220px;
              opacity: 1;
              margin-left: 8px;
            }
            .btn-action-expandable svg {
              flex-shrink: 0;
              transition: transform 0.25s ease;
            }
            .btn-action-expandable:hover svg {
              transform: scale(1.08);
            }
          </style>

          {{-- Botón 1: Invitación al Sistema --}}
          <button
            type="button"
            wire:click="openInvitacionModal"
            class="btn-action-expandable"
            style="background-color: #0f766e;"
            onmouseover="this.style.backgroundColor='#0d9488'"
            onmouseout="this.style.backgroundColor='#0f766e'"
            title="Invitación al sistema"
            aria-label="Invitación al sistema"
          >
            <svg class="h-4 w-4 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m22 2-7 20-4-9-9-4Z"/>
              <path d="M22 2 11 13"/>
            </svg>
            <span class="btn-action-label">Invitación al sistema</span>
          </button>

          {{-- Botón 2: Comunicados y Correos --}}
          <button
            type="button"
            wire:click="openEmailModal"
            class="btn-action-expandable"
            style="background-color: #1e40af;"
            onmouseover="this.style.backgroundColor='#1d4ed8'"
            onmouseout="this.style.backgroundColor='#1e40af'"
            title="Comunicados y correos"
            aria-label="Comunicados y correos"
          >
            <svg class="h-4 w-4 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <span class="btn-action-label">Comunicados y correos</span>
          </button>

          {{-- Botón 3: Agregar personal --}}
          <button
            type="button"
            wire:click="openCreateModal"
            class="btn-action-expandable"
            style="background-color: #0f67c0;"
            onmouseover="this.style.backgroundColor='#0d58a4'"
            onmouseout="this.style.backgroundColor='#0f67c0'"
            title="Agregar personal"
            aria-label="Agregar personal"
          >
            <svg class="h-4 w-4 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <line x1="19" x2="19" y1="8" y2="14"/>
              <line x1="22" x2="16" y1="11" y2="11"/>
            </svg>
            <span class="btn-action-label">Agregar personal</span>
          </button>
        </div>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-2">
          <div class="space-y-2">
            <label for="personal-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="personal-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Escribe un codigo, nombre o apellido"
              autocomplete="off"
            >
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label for="personal-sucursal" class="form-label">Filtrar por sucursal</label>
              @if(filled($sucursalFiltro))
                <button
                  type="button"
                  wire:click="openEmailModal('{{ $sucursalFiltro }}')"
                  class="text-[11px] font-bold text-blue-600 hover:text-blue-800 underline inline-flex items-center gap-1 cursor-pointer"
                  title="Enviar correo solo a personal de {{ $sucursalFiltro }}"
                >
                  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  <span>Enviar correo a {{ $sucursalFiltro }}</span>
                </button>
              @endif
            </div>
            <select id="personal-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              @foreach ($sucursales as $sucursalOption)
                <option value="{{ $sucursalOption }}">{{ $sucursalOption }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Sucursal</th>
              <th>Codigo</th>
              <th>Estado</th>
              <th>Ultima marcacion</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($empleados as $empleado)
              <tr>
                <td>
                  <div class="flex items-center gap-3">
                    @php
                      $empInitials = strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido ?: $empleado->nombre, 0, 1));
                    @endphp
                    @if($empleado->foto_url)
                      <img src="{{ $empleado->foto_url }}" alt="{{ $empleado->nombre_completo }}" width="38" height="38" style="width: 38px; height: 38px; min-width: 38px; max-width: 38px; min-height: 38px; max-height: 38px; border-radius: 0.85rem; object-fit: cover; object-position: center top; display: block; flex-shrink: 0;" class="emp-photo-table-thumb">
                    @else
                      <div class="h-10 w-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; border-radius: 0.85rem;">
                        {{ $empInitials }}
                      </div>
                    @endif
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 truncate">{{ $empleado->nombre_completo }}</div>
                      @if($empleado->email)
                        <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1 mt-0.5 truncate" title="Correo institucional">
                          <svg class="h-3 w-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                          <span>{{ $empleado->email }}</span>
                        </div>
                      @endif
                    </div>
                  </div>
                </td>
                <td>{{ $empleado->sucursal }}</td>
                <td>{{ $empleado->codigo_biometrico ?: 'Sin asignar' }}</td>
                <td>
                  <span class="status-badge {{ $empleado->estado_laboral === 'Activo' ? 'status-available' : 'status-warning' }}">
                    {{ $empleado->estado_laboral }}
                  </span>
                </td>
                <td>{{ $empleado->ultima_marcacion_label ?? 'Sin marcaciones' }}</td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button
                      type="button"
                      wire:click="openEditModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Editar personal"
                      title="Editar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                      </svg>
                      <span>Editar</span>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400">Todavia no hay personal activo para mostrar.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($empleados->hasPages())
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando {{ $empleados->firstItem() }} a {{ $empleados->lastItem() }} de {{ $empleados->total() }} registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                @disabled($empleados->onFirstPage())
                class="table-pagination-button {{ $empleados->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
              >
                Anterior
              </button>

              @foreach (range(max(1, $empleados->currentPage() - 2), min($empleados->lastPage(), $empleados->currentPage() + 2)) as $page)
                <button
                  type="button"
                  wire:click="gotoPage({{ $page }})"
                  class="table-pagination-button {{ $page === $empleados->currentPage() ? 'table-pagination-button-active' : '' }}"
                >
                  {{ $page }}
                </button>
              @endforeach

              <button
                type="button"
                wire:click="nextPage"
                @disabled(! $empleados->hasMorePages())
                class="table-pagination-button {{ ! $empleados->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      @endif
    </article>
  </section>
  @endif

  @if ($vista === 'inactivos')
  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <p class="section-kicker !mb-0">Personal inactivo</p>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 px-2.5 py-0.5 text-xs font-black">
              {{ $totalInactivosSistema ?? 92 }} colaboradores inactivos
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-xs font-medium">
              sin marcaciones recientes (&gt;30 días)
            </span>
          </div>
          <h3 class="section-title">Colaboradores sin marcaciones en los últimos 30 días</h3>
          <p class="section-copy-sm">Personal histórico o en baja que no registra asistencia en los últimos 30 días. Puedes consultar su historial previo o reactivarlos con una nueva marcación.</p>
        </div>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-2">
          <div class="space-y-2">
            <label for="inactivos-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="inactivos-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Escribe un codigo, nombre o apellido"
              autocomplete="off"
            >
          </div>
          <div class="space-y-2">
            <label for="inactivos-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="inactivos-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              @foreach ($sucursales as $sucursalOption)
                <option value="{{ $sucursalOption }}">{{ $sucursalOption }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Sucursal</th>
              <th>Codigo</th>
              <th>Estado</th>
              <th>Ultima marcacion</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($empleados as $empleado)
              <tr>
                <td>
                  <div class="flex items-center gap-3">
                    @php
                      $empInitials = strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido ?: $empleado->nombre, 0, 1));
                    @endphp
                    @if($empleado->foto_url)
                      <img src="{{ $empleado->foto_url }}" alt="{{ $empleado->nombre_completo }}" width="38" height="38" style="width: 38px; height: 38px; min-width: 38px; max-width: 38px; min-height: 38px; max-height: 38px; border-radius: 0.85rem; object-fit: cover; object-position: center top; display: block; flex-shrink: 0; filter: grayscale(100%); opacity: 0.8;" class="emp-photo-table-thumb">
                    @else
                      <div class="h-10 w-10 rounded-2xl bg-slate-100 border border-slate-200 text-slate-500 font-bold text-xs flex items-center justify-center shrink-0" style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; border-radius: 0.85rem;">
                        {{ $empInitials }}
                      </div>
                    @endif
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 truncate">{{ $empleado->nombre_completo }}</div>
                      @if($empleado->email)
                        <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1 mt-0.5 truncate" title="Correo institucional">
                          <svg class="h-3 w-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                          <span>{{ $empleado->email }}</span>
                        </div>
                      @endif
                    </div>
                  </div>
                </td>
                <td>{{ $empleado->sucursal }}</td>
                <td>{{ $empleado->codigo_biometrico ?: 'Sin asignar' }}</td>
                <td>
                  <span class="status-badge status-warning">{{ $empleado->estado_laboral }}</span>
                </td>
                <td>{{ $empleado->ultima_marcacion_label ?? 'Sin marcaciones' }}</td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button
                      type="button"
                      wire:click="openEditModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Editar personal inactivo"
                      title="Editar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                      </svg>
                      <span>Editar</span>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-slate-400">No hay personal inactivo en este momento.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($empleados->hasPages())
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando {{ $empleados->firstItem() }} a {{ $empleados->lastItem() }} de {{ $empleados->total() }} registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                @disabled($empleados->onFirstPage())
                class="table-pagination-button {{ $empleados->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
              >
                Anterior
              </button>

              @foreach (range(max(1, $empleados->currentPage() - 2), min($empleados->lastPage(), $empleados->currentPage() + 2)) as $page)
                <button
                  type="button"
                  wire:click="gotoPage({{ $page }})"
                  class="table-pagination-button {{ $page === $empleados->currentPage() ? 'table-pagination-button-active' : '' }}"
                >
                  {{ $page }}
                </button>
              @endforeach

              <button
                type="button"
                wire:click="nextPage"
                @disabled(! $empleados->hasMorePages())
                class="table-pagination-button {{ ! $empleados->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      @endif
    </article>
  </section>
  @endif

  @if ($vista === 'marcaciones')
  <section>
    <article class="surface-card">
      <div class="section-head-row section-head-row-spacious flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="history-panel-intro">
          <p class="section-kicker">Control de Marcaciones</p>
          <h3 class="section-title">Historial de Marcaciones de Asistencia</h3>
          <p class="section-copy-sm">Consulta registros biométricos de entrada y salida del personal por fecha, código/nombre o sucursal.</p>
        </div>
        @if ($marcacionesSearchPerformed)
          <div class="flex items-center gap-2">
            <button
              type="button"
              wire:click="descargarPdfMarcaciones"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 disabled:opacity-50 cursor-pointer"
              title="Descargar reporte completo en formato PDF"
            >
              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
              </svg>
              <span wire:loading.remove wire:target="descargarPdfMarcaciones">Descargar Reporte PDF</span>
              <span wire:loading wire:target="descargarPdfMarcaciones">Generando PDF...</span>
            </button>
          </div>
        @endif
      </div>

      {{-- Formulario de Búsqueda de Marcaciones por Personal y Fechas --}}
      <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form wire:submit.prevent="aplicarBusquedaMarcaciones" class="flex flex-col gap-4">
          
          <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            {{-- 1. Filtro Código / Nombre (Principal) --}}
            <div class="md:col-span-5">
              <label for="marcaciones-filter-search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                Personal (Código o Nombre)
              </label>
              <div class="relative">
                <input
                  type="text"
                  id="marcaciones-filter-search"
                  wire:model="inputMarcacionesSearch"
                  placeholder="Ej. Juan Pérez o Cód. 10024..."
                  class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3 py-2 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
                >
                <div class="absolute left-3 top-2.5 text-slate-400">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
              </div>
            </div>

            {{-- Selector Modo Fecha (Rango vs Mes) --}}
            <div class="md:col-span-2">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                Tipo de fecha
              </label>
              <select
                wire:model.live="inputMarcacionesTipoFecha"
                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
              >
                <option value="rango">Rango de fechas</option>
                <option value="mes">Por mes</option>
              </select>
            </div>

            {{-- Modo 1: Rango de fechas (Fecha Inicio - Fecha Fin) --}}
            @if ($inputMarcacionesTipoFecha === 'rango')
              <div class="md:col-span-3 flex items-center gap-2">
                <div class="flex-1">
                  <label for="marcaciones-filter-inicio" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                    Desde
                  </label>
                  <input
                    type="date"
                    id="marcaciones-filter-inicio"
                    wire:model="inputMarcacionesFechaInicio"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
                  >
                </div>
                <div class="flex-1">
                  <label for="marcaciones-filter-fin" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                    Hasta
                  </label>
                  <input
                    type="date"
                    id="marcaciones-filter-fin"
                    wire:model="inputMarcacionesFechaFin"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
                  >
                </div>
              </div>
            {{-- Modo 2: Por Mes --}}
            @else
              <div class="md:col-span-3">
                <label for="marcaciones-filter-mes" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  Mes seleccionado
                </label>
                <input
                  type="month"
                  id="marcaciones-filter-mes"
                  wire:model="inputMarcacionesMes"
                  class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-[#0f67c0] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
                >
              </div>
            @endif

            {{-- Botones Buscar y Limpiar --}}
            <div class="md:col-span-2 flex items-center gap-2">
              <button
                type="submit"
                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0f67c0] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0d59a7] focus:outline-none focus:ring-2 focus:ring-[#0f67c0]/40"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Buscar</span>
              </button>
              
              @if($marcacionesSearchPerformed || filled($inputMarcacionesSearch) || filled($inputMarcacionesFechaInicio) || filled($inputMarcacionesFechaFin) || filled($inputMarcacionesMes))
                <button
                  type="button"
                  wire:click="limpiarFiltrosMarcaciones"
                  class="inline-flex items-center justify-center gap-1 rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-200"
                  title="Limpiar filtros"
                >
                  <span>Limpiar</span>
                </button>
              @endif
            </div>
          </div>

        </form>

        {{-- Resumen de filtros aplicados --}}
        @if($marcacionesSearchPerformed)
          <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-600">
            <span class="font-bold text-slate-400 uppercase text-[10px]">Criterios aplicados:</span>
            
            @if(filled($appliedMarcacionesSearch))
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-blue-800 font-semibold">
                Personal: <strong>{{ $appliedMarcacionesSearch }}</strong>
              </span>
            @else
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-slate-600">
                Todo el personal
              </span>
            @endif

            @if($appliedMarcacionesTipoFecha === 'mes' && filled($appliedMarcacionesMes))
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-emerald-800 font-semibold">
                Mes: <strong>{{ \Carbon\Carbon::parse($appliedMarcacionesMes . '-01')->translatedFormat('F Y') }}</strong>
              </span>
            @elseif(filled($appliedMarcacionesFechaInicio) || filled($appliedMarcacionesFechaFin))
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-emerald-800 font-semibold">
                Rango: 
                <strong>
                  {{ filled($appliedMarcacionesFechaInicio) ? \Carbon\Carbon::parse($appliedMarcacionesFechaInicio)->format('d/m/Y') : 'Inicio' }}
                  al
                  {{ filled($appliedMarcacionesFechaFin) ? \Carbon\Carbon::parse($appliedMarcacionesFechaFin)->format('d/m/Y') : 'Hoy' }}
                </strong>
              </span>
            @else
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-slate-600">
                Todas las fechas
              </span>
            @endif
          </div>
        @endif
      </div>

      {{-- ESTADO 1: ANTES DE BUSCAR (TABLA VACÍA CON MENSAJE EXPLICATIVO) --}}
      @if (! $marcacionesSearchPerformed)
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-12 text-center my-6">
          <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-[#0f67c0] shadow-sm border border-blue-100">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <h4 class="text-base font-bold text-slate-800">Presiona "Buscar" para consultar las marcaciones</h4>
          <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
            Puedes filtrar por <strong>Fecha</strong>, <strong>Código/Nombre</strong> o <strong>Sucursal</strong>. Si dejas todos los campos vacíos y presionas "Buscar", se listarán todas las marcaciones registradas.
          </p>
          <div class="mt-5">
            <button
              type="button"
              wire:click="aplicarBusquedaMarcaciones"
              class="inline-flex items-center gap-1.5 rounded-xl bg-[#0f67c0] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0d59a7]"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <span>Consultar todo el historial</span>
            </button>
          </div>
        </div>

      {{-- ESTADO 2: DESPUÉS DE BUSCAR (TABLA COMPLETA CON FILTROS Y ORDENACIÓN) --}}
      @else
        {{-- Selector de coincidencias múltiples cuando varios funcionarios coinciden con la búsqueda --}}
        @if (count($matchingEmpleados) > 1)
          <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3.5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2.5">
              <div class="flex items-center gap-2">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100 text-[#0f67c0] text-xs font-bold">
                  👥
                </span>
                <span class="text-xs font-bold text-slate-800">
                  Se encontraron <strong>{{ count($matchingEmpleados) }}</strong> personas con el criterio "<strong>{{ $appliedMarcacionesSearch }}</strong>":
                </span>
              </div>
              <button
                type="button"
                wire:click="seleccionarEmpleadoMarcaciones(null)"
                class="text-[11px] font-bold text-[#0f67c0] hover:underline self-start sm:self-auto cursor-pointer"
              >
                {{ $selectedMarcacionesEmpleadoId ? 'Mostrar todas las marcaciones combinadas' : '✓ Mostrando todas combinadas' }}
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              @foreach ($matchingEmpleados as $matched)
                @php
                  $isSelected = ($selectedMarcacionesEmpleadoId === $matched['id']);
                  $isActivo = $matched['es_activo'];
                @endphp
                <button
                  type="button"
                  wire:click="seleccionarEmpleadoMarcaciones({{ $matched['id'] }})"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer {{ $isSelected ? 'bg-[#0f67c0] text-white border-[#0f67c0] shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 hover:border-slate-300' }}"
                >
                  <span>{{ $matched['nombre_completo'] }}</span>
                  <span class="font-mono text-[10px] {{ $isSelected ? 'text-blue-100' : 'text-slate-400' }}">Cód: {{ $matched['codigo'] }}</span>
                  <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $isSelected ? ($isActivo ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white') : ($isActivo ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">
                    {{ $matched['estado_laboral'] }}
                  </span>
                </button>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Tarjeta superior con datos del personal encontrado --}}
        @if ($marcacionesEmpleadoInfo)
          <div class="mt-5 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50/90 via-slate-50 to-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0f67c0] text-white font-bold text-base shadow-sm">
                  {{ mb_substr($marcacionesEmpleadoInfo['nombre_completo'], 0, 2) }}
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <h4 class="text-base font-bold text-slate-900">{{ $marcacionesEmpleadoInfo['nombre_completo'] }}</h4>
                    <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wide {{ $marcacionesEmpleadoInfo['estado_laboral'] === 'Activo' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                      {{ $marcacionesEmpleadoInfo['estado_laboral'] }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 font-mono mt-0.5">
                    Código biométrico: <strong class="text-slate-700 font-bold">{{ $marcacionesEmpleadoInfo['codigo'] }}</strong>
                  </p>
                  @if ($marcacionesEmpleadoInfo['estado_laboral'] === 'Inactivo')
                    <p class="text-[11px] font-bold text-rose-600 mt-1 flex items-center gap-1">
                      <span>⚠️ Personal en estado <strong>INACTIVO / DADO DE BAJA</strong> (Sin marcaciones en más de 30 días o desvinculado).</span>
                    </p>
                  @endif
                </div>
              </div>
              
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs">
                  <svg class="h-3.5 w-3.5 text-[#0f67c0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  Sucursal: <strong class="text-slate-900">{{ $marcacionesEmpleadoInfo['sucursal'] }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs">
                  <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
                  Área: <strong class="text-slate-900">{{ $marcacionesEmpleadoInfo['area'] }}</strong>
                </span>
              </div>
            </div>

            {{-- Barra acumulativa y Alerta de Sanción Económica Reglamentaria (Art. 45) --}}
            @php
              $marcRetrasoMin = (int)($marcacionesStats['minutos_atraso_totales'] ?? 0);
              $marcToleranciaMin = (int)($marcacionesStats['tolerancia_mensual'] ?? 35);
              $marcPorcentaje = (int)($marcacionesStats['porcentaje_tolerancia'] ?? ($marcToleranciaMin > 0 ? min(100, round(($marcRetrasoMin / $marcToleranciaMin) * 100)) : 100));
              $marcSancion = $marcacionesStats['sancion_regla'] ?? null;
              $marcExcedido = ($marcacionesStats['estado_tolerancia'] ?? '') === 'Excedido';
              $marcEsAcum2 = $marcSancion['es_acumulativo_2'] ?? false;
            @endphp
            <div class="mt-4 rounded-2xl border p-4 transition-all {{ $marcEsAcum2 ? 'border-rose-300 bg-rose-50/70' : ($marcExcedido ? 'border-amber-300 bg-amber-50/70' : 'border-emerald-200 bg-emerald-50/50') }}">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $marcEsAcum2 ? 'text-rose-900' : ($marcExcedido ? 'text-amber-900' : 'text-emerald-900') }}">
                      🏛️ Control de Tolerancia Mensual y Sanciones Económicas (Art. 45)
                    </span>
                    @if($marcEsAcum2)
                      <span class="inline-flex items-center gap-1 rounded-full bg-rose-600 text-white px-2 py-0.5 text-[10px] font-black animate-pulse">
                        🚨 ALCANZÓ ACUMULATIVO 2
                      </span>
                    @elseif($marcExcedido)
                      <span class="inline-flex items-center gap-1 rounded-full bg-amber-600 text-white px-2 py-0.5 text-[10px] font-bold">
                        ⚠️ TOLERANCIA EXCEDIDA
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-600 text-white px-2 py-0.5 text-[10px] font-bold">
                        ✓ EN TOLERANCIA
                      </span>
                    @endif
                  </div>

                  <p class="text-xs {{ $marcEsAcum2 ? 'text-rose-950 font-bold' : ($marcExcedido ? 'text-amber-950 font-medium' : 'text-slate-700 font-medium') }}">
                    @if($marcRetrasoMin === 0)
                      El funcionario no registra minutos de atraso en el período evaluado.
                    @elseif(!$marcExcedido)
                      El funcionario acumula <strong>{{ $marcRetrasoMin }} min</strong> de retraso de un límite de <strong>{{ $marcToleranciaMin }} min</strong> ({{ $marcacionesStats['saldo_tolerancia'] ?? '' }}). Sin sanción económica.
                    @elseif($marcacionesStats['en_periodo_transicion'] ?? false)
                      Ha acumulado <strong>{{ $marcRetrasoMin }} min</strong> de atraso superando la tolerancia de <strong>{{ $marcToleranciaMin }} min</strong>. No obstante, el reglamento de sanciones para este período se encuentra en <strong>marcha blanca / entra en vigencia posterior</strong> ({{ $marcacionesStats['vigencia_reglamento_detalle'] ?? 'No aplica este mes' }}), por lo que <strong>no corresponde descuento económico</strong>.
                    @elseif($marcEsAcum2)
                      Ha acumulado <strong>{{ $marcRetrasoMin }} min</strong> de atraso superando la tolerancia de <strong>{{ $marcToleranciaMin }} min</strong>. Sanción aplicable: <strong class="underline decoration-rose-500 font-black text-rose-900">{{ $marcSancion['sancion_texto'] ?? 'Dos (2) días' }} (-{{ $marcSancion['dias_sancion'] ?? 2 }} días de haber mensual)</strong> por memorándum DAF.
                    @else
                      Ha acumulado <strong>{{ $marcRetrasoMin }} min</strong> de atraso superando la tolerancia de <strong>{{ $marcToleranciaMin }} min</strong>. Sanción aplicable: <strong class="font-bold text-amber-950">{{ $marcSancion['sancion_texto'] ?? 'Medio día' }} (-{{ $marcSancion['dias_sancion'] ?? 0.5 }} día de haber mensual)</strong>.
                    @endif
                  </p>
                </div>

                @if($marcExcedido)
                  <div class="shrink-0">
                    <button
                      type="button"
                      wire:click="openModalSancionados2Dias('{{ $marcEsAcum2 ? 'acumulativo_2' : 'todos' }}')"
                      class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer {{ $marcEsAcum2 ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-600 hover:bg-amber-700' }}"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                      <span>Ver Normativa y Sancionados</span>
                    </button>
                  </div>
                @endif
              </div>

              {{-- Barra acumulativa de progreso --}}
              <div class="mt-3">
                <div class="flex items-center justify-between text-[11px] font-bold mb-1 {{ $marcEsAcum2 ? 'text-rose-900' : ($marcExcedido ? 'text-amber-900' : 'text-slate-600') }}">
                  <span>Consumo de Tolerancia Mensual: <strong>{{ $marcPorcentaje }}%</strong></span>
                  <span>{{ $marcRetrasoMin }} min registrados / {{ $marcToleranciaMin }} min tolerancia</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-slate-200/90 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-500 {{ $marcPorcentaje >= 100 ? 'bg-rose-600' : ($marcPorcentaje >= 75 ? 'bg-amber-500' : ($marcPorcentaje > 0 ? 'bg-emerald-500' : 'bg-transparent')) }}"
                    style="width: {{ $marcPorcentaje }}%"
                  ></div>
                </div>
              </div>
            </div>

            {{-- 4 Botones / Tarjetas interactivas que abren modales separados --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-200/60">
              
              {{-- Botón Modal 1: Atrasos --}}
              <button
                type="button"
                wire:click="openModalAtrasos"
                class="group flex flex-col items-start justify-between rounded-2xl border border-amber-200 bg-amber-50/60 p-3.5 transition-all hover:bg-amber-100/70 hover:shadow-md hover:border-amber-300 text-left cursor-pointer"
              >
                <div class="flex items-center justify-between w-full">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Atrasos</span>
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-800 text-xs font-bold group-hover:scale-110 transition-transform">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  </span>
                </div>
                <div class="mt-2">
                  <span class="text-2xl font-extrabold text-amber-900">{{ $marcacionesStats['total_atrasos'] ?? 0 }}</span>
                  <span class="text-xs text-amber-700 font-medium ml-1">días tarde</span>
                </div>
                <p class="text-[11px] text-amber-800 mt-2 flex items-center gap-1 font-semibold group-hover:underline">
                  <span>Ver detalle</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </p>
              </button>

              {{-- Botón Modal 2: Omisiones --}}
              <button
                type="button"
                wire:click="openModalOmisiones"
                class="group flex flex-col items-start justify-between rounded-2xl border border-rose-200 bg-rose-50/60 p-3.5 transition-all hover:bg-rose-100/70 hover:shadow-md hover:border-rose-300 text-left cursor-pointer"
              >
                <div class="flex items-center justify-between w-full">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Omisiones</span>
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-800 text-xs font-bold group-hover:scale-110 transition-transform">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                  </span>
                </div>
                <div class="mt-2">
                  <span class="text-2xl font-extrabold text-rose-900">{{ $marcacionesStats['total_omisiones'] ?? 0 }}</span>
                  <span class="text-xs text-rose-700 font-medium ml-1">olvidos</span>
                </div>
                <p class="text-[11px] text-rose-800 mt-2 flex items-center gap-1 font-semibold group-hover:underline">
                  <span>Ver detalle</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </p>
              </button>

              {{-- Botón Modal 3: Faltas --}}
              <button
                type="button"
                wire:click="openModalFaltas"
                class="group flex flex-col items-start justify-between rounded-2xl border border-red-200 bg-red-50/60 p-3.5 transition-all hover:bg-red-100/70 hover:shadow-md hover:border-red-300 text-left cursor-pointer"
              >
                <div class="flex items-center justify-between w-full">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-red-800">Faltas</span>
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 text-red-800 text-xs font-bold group-hover:scale-110 transition-transform">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                  </span>
                </div>
                <div class="mt-2">
                  <span class="text-2xl font-extrabold text-red-900">{{ $marcacionesStats['total_faltas'] ?? 0 }}</span>
                  <span class="text-xs text-red-700 font-medium ml-1">inasistencias</span>
                </div>
                <p class="text-[11px] text-red-800 mt-2 flex items-center gap-1 font-semibold group-hover:underline">
                  <span>Ver detalle</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </p>
              </button>

              {{-- Botón Modal 4: Horas y Atrasos Acumulados --}}
              <button
                type="button"
                wire:click="openModalGlobal"
                class="group flex flex-col items-start justify-between rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 to-indigo-50/60 p-3.5 transition-all hover:from-blue-100/90 hover:to-indigo-100/80 hover:shadow-md hover:border-blue-300 text-left cursor-pointer"
              >
                <div class="flex items-center justify-between w-full">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Horas y Atrasos</span>
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-[#0f67c0] text-xs font-bold group-hover:scale-110 transition-transform">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </span>
                </div>
                <div class="mt-2">
                  <span class="text-2xl font-extrabold text-[#0f67c0]">{{ $marcacionesStats['horas_trabajadas_formateado'] ?? '0h 00m' }}</span>
                  <span class="text-xs text-slate-600 block mt-0.5">Retraso: <strong class="text-slate-800">{{ $marcacionesStats['retraso_acumulado_formateado'] ?? '0 min' }}</strong></span>
                </div>
                <p class="text-[11px] text-[#0f67c0] mt-2 flex items-center gap-1 font-semibold group-hover:underline">
                  <span>Resumen global</span>
                  <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                </p>
              </button>

            </div>
          </div>
        @endif

        <div class="history-table-shell mt-4">
          
          {{-- Accesos rápidos de ordenación y filtros de estado --}}
          <div class="mb-4 flex flex-wrap items-center justify-between gap-2 text-xs px-1">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Ordenar:</span>
              
              @if (!$marcacionesEmpleadoInfo)
                {{-- Botón A-Z --}}
                <button
                  type="button"
                  wire:click="sortByMarcaciones('nombre')"
                  class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenMarcaciones, ['nombre_asc', 'nombre_desc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                  <span>Personal (A-Z)</span>
                  @if($ordenMarcaciones === 'nombre_asc') <span>↓</span> @elseif($ordenMarcaciones === 'nombre_desc') <span>↑</span> @endif
                </button>
              @endif

              {{-- Botón Fecha Reciente / Antigua --}}
              <button
                type="button"
                wire:click="sortByMarcaciones('fecha')"
                class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenMarcaciones, ['fecha_reciente', 'fecha_antigua']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
              >
                <span>Fecha {{ $ordenMarcaciones === 'fecha_reciente' ? '(Reciente)' : ($ordenMarcaciones === 'fecha_antigua' ? '(Antigua)' : '') }}</span>
                @if($ordenMarcaciones === 'fecha_reciente') <span>↓</span> @elseif($ordenMarcaciones === 'fecha_antigua') <span>↑</span> @endif
              </button>

              {{-- Botón Entrada Temprano / Tarde --}}
              <button
                type="button"
                wire:click="sortByMarcaciones('entrada')"
                class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenMarcaciones, ['hora_asc', 'hora_desc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
              >
                <span>Entrada {{ $ordenMarcaciones === 'hora_asc' ? '(Temprano)' : ($ordenMarcaciones === 'hora_desc' ? '(Tarde)' : '') }}</span>
                @if($ordenMarcaciones === 'hora_asc') <span>↓</span> @elseif($ordenMarcaciones === 'hora_desc') <span>↑</span> @endif
              </button>

              {{-- Botón Salida Temprano / Tarde --}}
              <button
                type="button"
                wire:click="sortByMarcaciones('salida')"
                class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenMarcaciones, ['salida_asc', 'salida_desc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
              >
                <span>Salida {{ $ordenMarcaciones === 'salida_asc' ? '(Temprano)' : ($ordenMarcaciones === 'salida_desc' ? '(Tarde)' : '') }}</span>
                @if($ordenMarcaciones === 'salida_asc') <span>↓</span> @elseif($ordenMarcaciones === 'salida_desc') <span>↑</span> @endif
              </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              {{-- Filtro de Estado: Todos / Completos / Sin completar --}}
              <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                <button
                  type="button"
                  wire:click="setFilterEstadoMarcaciones('todos')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoMarcaciones === 'todos' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Todos
                </button>
                <button
                  type="button"
                  wire:click="setFilterEstadoMarcaciones('completo')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoMarcaciones === 'completo' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Completos
                </button>
                <button
                  type="button"
                  wire:click="setFilterEstadoMarcaciones('faltante')"
                  class="rounded-md px-2 py-0.5 text-[11px] font-bold transition {{ $filterEstadoMarcaciones === 'faltante' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                  Sin completar
                </button>
              </div>

              {{-- Botón Exportar Excel --}}
              <button
                type="button"
                wire:click="descargarExcelMarcaciones"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 shadow-xs hover:bg-emerald-100 transition disabled:opacity-50 cursor-pointer"
                title="Descargar reporte en formato Excel (.xlsx)"
              >
                <svg class="h-3.5 w-3.5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="8" y1="13" x2="16" y2="13"/>
                  <line x1="8" y1="17" x2="16" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span wire:loading.remove wire:target="descargarExcelMarcaciones">Exportar Excel</span>
                <span wire:loading wire:target="descargarExcelMarcaciones">Generando...</span>
              </button>

              {{-- Botón Exportar PDF --}}
              <button
                type="button"
                wire:click="descargarPdfMarcaciones"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition disabled:opacity-50 cursor-pointer"
                title="Descargar reporte en formato PDF"
              >
                <svg class="h-3.5 w-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
                </svg>
                <span wire:loading.remove wire:target="descargarPdfMarcaciones">Exportar PDF</span>
                <span wire:loading wire:target="descargarPdfMarcaciones">Generando...</span>
              </button>
            </div>
          </div>

          <table class="history-table">
            <thead>
              <tr>
                @if (!$marcacionesEmpleadoInfo)
                  <th class="cursor-pointer hover:bg-slate-100 transition select-none" wire:click="sortByMarcaciones('nombre')">
                    <div class="flex items-center gap-1.5">
                      <span>Personal</span>
                      @if(in_array($ordenMarcaciones, ['nombre_asc', 'nombre_desc']))
                        <span class="text-[#0f67c0]">{{ $ordenMarcaciones === 'nombre_asc' ? '▲' : '▼' }}</span>
                      @else
                        <span class="text-slate-300">⇅</span>
                      @endif
                    </div>
                  </th>
                @endif
                <th class="cursor-pointer hover:bg-slate-100 transition select-none" wire:click="sortByMarcaciones('fecha')">
                  <div class="flex items-center gap-1.5">
                    <span>Fecha / Día</span>
                    @if(in_array($ordenMarcaciones, ['fecha_reciente', 'fecha_antigua']))
                      <span class="text-[#0f67c0]">{{ $ordenMarcaciones === 'fecha_reciente' ? '▼' : '▲' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="text-center cursor-pointer hover:bg-slate-100 transition select-none" wire:click="sortByMarcaciones('entrada')">
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Hora Entrada</span>
                    @if(in_array($ordenMarcaciones, ['hora_asc', 'hora_desc']))
                      <span class="text-[#0f67c0]">{{ $ordenMarcaciones === 'hora_asc' ? '▲' : '▼' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="text-center cursor-pointer hover:bg-slate-100 transition select-none" wire:click="sortByMarcaciones('salida')">
                  <div class="flex items-center justify-center gap-1.5">
                    <span>Hora Salida</span>
                    @if(in_array($ordenMarcaciones, ['salida_asc', 'salida_desc']))
                      <span class="text-[#0f67c0]">{{ $ordenMarcaciones === 'salida_asc' ? '▲' : '▼' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="text-center select-none">
                  <span>Estado</span>
                </th>
              </tr>
            </thead>
            <tbody>
              @forelse ($registros as $registro)
                <tr>
                  @if (!$marcacionesEmpleadoInfo)
                    <td>
                      <strong class="text-slate-900 font-bold block">{{ $registro->empleado?->nombre_completo ?? 'Sin empleado' }}</strong>
                    </td>
                  @endif
                  <td>
                    <strong class="text-slate-800 font-semibold block">{{ $registro->fecha_formateada ?? 'Sin fecha' }}</strong>
                    <span class="text-[11px] text-slate-400 capitalize">{{ $registro->dia ?? '' }}</span>
                  </td>
                  <td class="text-center whitespace-nowrap">
                    @if($registro->hora_entrada !== '--:--')
                      <span class="inline-block rounded-md bg-emerald-50 border border-emerald-200 px-2 py-0.5 font-mono text-[11px] font-bold text-emerald-800">
                        {{ $registro->hora_entrada }}
                      </span>
                    @else
                      <span class="inline-block rounded-md bg-slate-100 text-slate-400 px-2 py-0.5 font-mono text-[11px]">
                        --:--
                      </span>
                    @endif
                  </td>
                  <td class="text-center whitespace-nowrap">
                    @if($registro->hora_salida !== '--:--')
                      <span class="inline-block rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 font-mono text-[11px] font-bold text-slate-700">
                        {{ $registro->hora_salida }}
                      </span>
                    @else
                      <span class="inline-block rounded-md bg-slate-100 text-slate-400 px-2 py-0.5 font-mono text-[11px]">
                        --:--
                      </span>
                    @endif
                  </td>
                  <td class="text-center whitespace-nowrap">
                    @if(($registro->tipo_estado ?? '') === 'completo')
                      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                        <svg class="h-3 w-3 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        <span>Completo</span>
                      </span>
                    @elseif(($registro->tipo_estado ?? '') === 'faltante')
                      <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold text-amber-800" title="{{ $registro->hora_entrada === '--:--' ? 'Falta marcar entrada' : 'Falta marcar salida' }}">
                        <svg class="h-3 w-3 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        <span>Sin completar</span>
                      </span>
                    @else
                      <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                        {{ $registro->estado_marcacion ?? 'Sin registro' }}
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $marcacionesEmpleadoInfo ? '4' : '5' }}" class="py-8 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-700">No se encontraron marcaciones para los filtros seleccionados.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($registros->hasPages())
          <div class="table-pagination-shell mt-4">
            <div class="table-pagination-bar">
              <p class="table-pagination-copy">
                Mostrando {{ $registros->firstItem() }} a {{ $registros->lastItem() }} de {{ $registros->total() }} marcaciones
              </p>

              <div class="table-pagination-actions">
                <button
                  type="button"
                  wire:click="previousPage('registrosPage')"
                  @disabled($registros->onFirstPage())
                  class="table-pagination-button {{ $registros->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
                >
                  Anterior
                </button>

                @foreach (range(max(1, $registros->currentPage() - 2), min($registros->lastPage(), $registros->currentPage() + 2)) as $page)
                  <button
                    type="button"
                    wire:click="gotoPage({{ $page }}, 'registrosPage')"
                    class="table-pagination-button {{ $page === $registros->currentPage() ? 'table-pagination-button-active' : '' }}"
                  >
                    {{ $page }}
                  </button>
                @endforeach

                <button
                  type="button"
                  wire:click="nextPage('registrosPage')"
                  @disabled(! $registros->hasMorePages())
                  class="table-pagination-button {{ ! $registros->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
                >
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        @endif
      @endif

    </article>
  </section>
  @endif

  @if ($vista === 'control')
  <section>
    <article class="surface-card">
      {{-- Encabezado --}}
      <div class="section-head-row section-head-row-spacious">
        <div class="history-panel-intro">
          <p class="section-kicker">Rendimiento Regional</p>
          <h3 class="section-title">Marcaciones y Estadísticas por Sucursal — {{ $mes_resumen }}</h3>
          <p class="section-copy-sm">
            Consulta los porcentajes de puntualidad, cumplimiento de marcaciones sin omisiones y control de tolerancia mensual por cada agencia regional.
          </p>
        </div>
        @if($controlSearchPerformed && !empty($sucursalKpis) && ($sucursalKpis['total_empleados'] ?? 0) > 0)
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 border border-blue-200 px-3 py-1.5 font-bold text-blue-900">
              <svg class="h-4 w-4 text-[#0f67c0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              <span>{{ filled($appliedControlSucursal) && $appliedControlSucursal !== 'todas' ? $appliedControlSucursal : 'Todas las sucursales' }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#0f67c0]/10 border border-[#0f67c0]/20 px-3 py-1.5 font-bold text-[#0f67c0]">
              <span>{{ $sucursalKpis['total_empleados'] }} empleados activos</span>
            </span>
          </div>
        @endif
      </div>



      {{-- Accesos Rápidos Departamentales y Filtros --}}
      <div class="mt-4 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50/80 via-white to-blue-50/40 p-4 shadow-sm">
        <div class="flex items-center justify-between gap-2 mb-3">
          <div class="flex items-center gap-2">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0f67c0] text-white">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            </div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Departamentos y Regionales:</span>
          </div>
          <span class="text-[11px] text-slate-400 font-semibold hidden sm:inline-block">Clic directo para consultar</span>
        </div>

        @php
          $deptOptions = [
            'TODAS' => 'Todas las sucursales',
            'LA PAZ' => 'La Paz',
            'SANTA CRUZ' => 'Santa Cruz',
            'COCHABAMBA' => 'Cochabamba',
            'ORURO' => 'Oruro',
            'POTOSI' => 'Potosí',
            'CHUQUISACA' => 'Chuquisaca',
            'TARIJA' => 'Tarija',
            'BENI' => 'Beni',
            'PANDO' => 'Pando',
            'EL ALTO' => 'El Alto',
          ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
          @foreach($deptOptions as $deptKey => $deptLabel)
            @php
              $normKey = \App\Support\SucursalNormalizer::normalize($deptLabel);
              $count = $deptKey === 'TODAS'
                ? array_sum($resumenSucursalesConteo ?? [])
                : ($resumenSucursalesConteo[$normKey] ?? $resumenSucursalesConteo[$deptLabel] ?? $resumenSucursalesConteo[$deptKey] ?? 0);
              $isActive = ($deptKey === 'TODAS' && (empty($appliedControlSucursal) || $appliedControlSucursal === 'todas')) ||
                ($appliedControlSucursal === $deptLabel || $appliedControlSucursal === $deptKey);
            @endphp
            <button
              type="button"
              wire:click="seleccionarSucursal('{{ $deptKey === 'TODAS' ? '' : $deptLabel }}')"
              class="group relative flex items-center justify-between rounded-2xl border p-3.5 transition-all duration-200 text-left cursor-pointer {{ $isActive && $controlSearchPerformed ? 'border-[#0f67c0] bg-blue-50/90 text-blue-900 shadow-md ring-2 ring-[#0f67c0]/25' : 'border-slate-200 bg-white hover:border-[#0f67c0]/50 hover:bg-slate-50/90 hover:shadow-sm text-slate-700' }}"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors {{ $isActive && $controlSearchPerformed ? 'bg-[#0f67c0] text-white shadow-xs' : 'bg-slate-100 text-slate-600 group-hover:bg-blue-100 group-hover:text-[#0f67c0]' }}">
                  @if($deptKey === 'TODAS')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                  @else
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  @endif
                </div>
                <div class="min-w-0">
                  <span class="block text-xs font-bold truncate {{ $isActive && $controlSearchPerformed ? 'text-[#0f67c0]' : 'text-slate-900' }}">
                    {{ $deptLabel }}
                  </span>
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold font-mono {{ $isActive && $controlSearchPerformed ? 'text-blue-700' : 'text-slate-500' }}">
                    <span>{{ $count }}</span>
                    <span>{{ $count === 1 ? 'empleado' : 'empleados' }}</span>
                  </span>
                </div>
              </div>

              <div class="shrink-0 ml-2">
                @if($isActive && $controlSearchPerformed)
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#0f67c0] text-white shadow-xs">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </span>
                @else
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-400 group-hover:bg-blue-50 group-hover:text-[#0f67c0] transition-colors">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                  </span>
                @endif
              </div>
            </button>
          @endforeach
        </div>

        {{-- Barra de Filtros (Mes + Año + Búsqueda de Personal) --}}
        <form wire:submit.prevent="aplicarBusquedaControl" class="mt-4 pt-3.5 border-t border-slate-200/80 flex flex-col gap-3 lg:flex-row lg:items-end">

          {{-- 1. Selector de Mes --}}
          <div class="w-full sm:w-48">
            <label for="control-filter-mes-numero" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              Mes
            </label>
            <div class="relative">
              <select
                id="control-filter-mes-numero"
                wire:model="inputControlMesNumero"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:border-[#0f67c0] focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
              >
                @php
                  $mesesNombres = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                  ];
                @endphp
                @foreach ($mesesNombres as $num => $mesNombre)
                  <option value="{{ $num }}">{{ $mesNombre }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- 2. Selector de Año --}}
          <div class="w-full sm:w-32">
            <label for="control-filter-anio" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              Año
            </label>
            <select
              id="control-filter-anio"
              wire:model="inputControlAnio"
              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:border-[#0f67c0] focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
            >
              @for ($y = (int) now()->year - 2; $y <= (int) now()->year + 1; $y++)
                <option value="{{ $y }}">{{ $y }}</option>
              @endfor
            </select>
          </div>

          {{-- 3. Código o Nombre Específico (Opcional) --}}
          <div class="flex-1 min-w-[200px]">
            <label for="control-filter-search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
              Empleado (Opcional)
            </label>
            <input
              type="text"
              id="control-filter-search"
              wire:model="inputControlSearch"
              placeholder="Buscar por código o nombre..."
              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:border-[#0f67c0] focus:outline-none focus:ring-1 focus:ring-[#0f67c0]"
            >
          </div>

          {{-- Botones --}}
          <div class="flex items-center gap-2 shrink-0">
            <button
              type="submit"
              class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0f67c0] px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#0d59a7] focus:outline-none focus:ring-2 focus:ring-[#0f67c0]/40 cursor-pointer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <span>Consultar</span>
            </button>

            @if($controlSearchPerformed || filled($inputControlSearch) || filled($inputControlSucursal))
              <button
                type="button"
                wire:click="limpiarFiltrosControl"
                class="inline-flex items-center justify-center gap-1 rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 cursor-pointer"
              >
                Limpiar
              </button>
            @endif
          </div>
        </form>
      </div>

      {{-- ESTADO 1: ANTES DE CONSULTAR --}}
      @if (! $controlSearchPerformed)
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center my-6">
          <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-[#0f67c0] shadow-sm border border-blue-100">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h4 class="text-base font-bold text-slate-800">Selecciona una sucursal para calcular los porcentajes</h4>
          <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
            Haz clic en cualquiera de los departamentos arriba o presiona <strong>Consultar todas las sucursales</strong> para ver el porcentaje de puntualidad, omisiones y tolerancia.
          </p>
          <div class="mt-5 flex items-center justify-center gap-2">
            <button
              type="button"
              wire:click="aplicarBusquedaControl"
              class="inline-flex items-center gap-1.5 rounded-xl bg-[#0f67c0] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0d59a7] cursor-pointer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <span>Consultar todas las sucursales</span>
            </button>
          </div>
        </div>

      {{-- ESTADO 2: DESPUÉS DE BUSCAR (PORCENTAJES Y ESTADÍSTICAS POR SUCURSAL) --}}
      @else

        {{-- 4 TARJETAS DE PORCENTAJES Y RENDIMIENTO POR SUCURSAL --}}
        @if(!empty($sucursalKpis))
          <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- KPI 1: Puntualidad / Sin Atrasos --}}
            <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-50/30 p-4 shadow-sm">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Tasa de Puntualidad</span>
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
              </div>
              
              <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-950">{{ $sucursalKpis['porcentaje_sin_atrasos'] }}%</span>
                <span class="text-xs font-bold text-emerald-700">Sin atrasos</span>
              </div>

              {{-- Barra de progreso --}}
              <div class="mt-2.5 h-2 w-full rounded-full bg-emerald-100 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-600 transition-all duration-500" style="width: {{ $sucursalKpis['porcentaje_sin_atrasos'] }}%"></div>
              </div>

              <div class="mt-3 pt-2.5 border-t border-emerald-100 flex items-center justify-between text-[11px]">
                <span class="text-emerald-900 font-bold">
                  {{ $sucursalKpis['sin_atrasos'] }} de {{ $sucursalKpis['total_empleados'] }} puntuales
                </span>
                <span class="text-amber-800 font-semibold">
                  {{ $sucursalKpis['con_atrasos'] }} tarde ({{ $sucursalKpis['porcentaje_con_atrasos'] }}%)
                </span>
              </div>
            </div>

            {{-- KPI 2: Marcaciones Sin Omisiones --}}
            <div class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-blue-50/30 p-4 shadow-sm">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Cumplimiento Marcación</span>
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-[#0f67c0] text-xs font-bold">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </span>
              </div>
              
              <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-[#0f67c0]">{{ $sucursalKpis['porcentaje_sin_omisiones'] }}%</span>
                <span class="text-xs font-bold text-blue-800">Completas</span>
              </div>

              {{-- Barra de progreso --}}
              <div class="mt-2.5 h-2 w-full rounded-full bg-blue-100 overflow-hidden">
                <div class="h-full rounded-full bg-[#0f67c0] transition-all duration-500" style="width: {{ $sucursalKpis['porcentaje_sin_omisiones'] }}%"></div>
              </div>

              <div class="mt-3 pt-2.5 border-t border-blue-100 flex items-center justify-between text-[11px]">
                <span class="text-blue-950 font-bold">
                  {{ $sucursalKpis['sin_omisiones'] }} de {{ $sucursalKpis['total_empleados'] }} sin omisiones
                </span>
                <span class="text-rose-700 font-semibold">
                  {{ $sucursalKpis['con_omisiones'] }} con omisión ({{ $sucursalKpis['porcentaje_con_omisiones'] }}%)
                </span>
              </div>
            </div>

            {{-- KPI 3: Control de Tolerancia Mensual --}}
            <div class="rounded-2xl border border-purple-200 bg-gradient-to-br from-purple-50/80 via-white to-purple-50/30 p-4 shadow-sm">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-900">En Tolerancia Mensual</span>
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-purple-800 text-xs font-bold">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
              </div>
              
              <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-purple-950">{{ $sucursalKpis['porcentaje_dentro_tolerancia'] }}%</span>
                <span class="text-xs font-bold text-purple-800">≤ 35 min</span>
              </div>

              {{-- Barra de progreso --}}
              <div class="mt-2.5 h-2 w-full rounded-full bg-purple-100 overflow-hidden">
                <div class="h-full rounded-full bg-purple-600 transition-all duration-500" style="width: {{ $sucursalKpis['porcentaje_dentro_tolerancia'] }}%"></div>
              </div>

              <div class="mt-3 pt-2.5 border-t border-purple-100 flex items-center justify-between text-[11px]">
                <span class="text-purple-950 font-bold">
                  {{ $sucursalKpis['dentro_tolerancia'] }} en tolerancia
                </span>
                <span class="text-rose-700 font-semibold">
                  {{ $sucursalKpis['excedidos_tolerancia'] }} excedidos ({{ $sucursalKpis['porcentaje_excedidos'] }}%)
                </span>
              </div>

              @if(($sucursalKpis['sancionados_acumulativo_2'] ?? 0) > 0)
                <button
                  type="button"
                  wire:click="openModalSancionados2Dias('acumulativo_2')"
                  class="mt-2.5 w-full flex items-center justify-between rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700 transition cursor-pointer"
                  title="Ver funcionarios con sanción de 2+ días de haber"
                >
                  <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                    <span>🚨 Acumulativo 2:</span>
                  </span>
                  <span class="rounded-full bg-white text-rose-700 px-2 py-0.5 text-[10px] font-black">
                    {{ $sucursalKpis['sancionados_acumulativo_2'] }} sancionados
                  </span>
                </button>
              @elseif(($sucursalKpis['sancionados_con_descuento'] ?? 0) > 0)
                <button
                  type="button"
                  wire:click="openModalSancionados2Dias('todos')"
                  class="mt-2.5 w-full flex items-center justify-between rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-amber-600 transition cursor-pointer"
                  title="Ver funcionarios con sanción económica"
                >
                  <span>⚠️ Con Sanción:</span>
                  <span class="rounded-full bg-white text-amber-800 px-2 py-0.5 text-[10px] font-black">
                    {{ $sucursalKpis['sancionados_con_descuento'] }} funcionarios
                  </span>
                </button>
              @endif
            </div>

            {{-- KPI 4: Horas Acumuladas de la Sucursal --}}
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50/90 via-white to-slate-50/40 p-4 shadow-sm">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Horas Trabajadas</span>
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
              </div>
              
              <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $sucursalKpis['total_horas_trabajadas'] }}</span>
              </div>

              <div class="mt-2.5 flex items-center justify-between text-xs text-slate-600 font-semibold">
                <span>Promedio por empleado:</span>
                <strong class="text-slate-900">{{ $sucursalKpis['promedio_horas_empleado'] }}</strong>
              </div>

              <div class="mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Retraso acumulado total:</span>
                <span class="text-amber-900 font-bold font-mono">{{ $sucursalKpis['total_retraso_formateado'] }}</span>
              </div>
            </div>

          </div>
        @endif

        {{-- Accesos rápidos de ordenación --}}
        <div class="mt-6 mb-3 flex flex-wrap items-center justify-between gap-2 px-1 text-xs">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Ordenar:</span>

            <button type="button" wire:click="sortByControl('nombre')"
              class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenControl, ['nombre_asc', 'nombre_desc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
              Personal (A-Z)
              @if($ordenControl === 'nombre_asc') <span>↓</span> @elseif($ordenControl === 'nombre_desc') <span>↑</span> @endif
            </button>

            <button type="button" wire:click="sortByControl('horas')"
              class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenControl, ['horas_desc', 'horas_asc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
              Horas {{ $ordenControl === 'horas_desc' ? '(Mayor)' : ($ordenControl === 'horas_asc' ? '(Menor)' : '') }}
              @if($ordenControl === 'horas_desc') <span>↓</span> @elseif($ordenControl === 'horas_asc') <span>↑</span> @endif
            </button>

            <button type="button" wire:click="sortByControl('retraso')"
              class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ in_array($ordenControl, ['retraso_desc', 'retraso_asc']) ? 'bg-[#0f67c0] text-white border-[#0f67c0]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
              Retraso {{ $ordenControl === 'retraso_desc' ? '(Mayor)' : ($ordenControl === 'retraso_asc' ? '(Menor)' : '') }}
              @if($ordenControl === 'retraso_desc') <span>↓</span> @elseif($ordenControl === 'retraso_asc') <span>↑</span> @endif
            </button>

            <button type="button" wire:click="sortByControl('excedido')"
              class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 font-semibold transition {{ $ordenControl === 'excedido_primero' ? 'bg-rose-600 text-white border-rose-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
              Excedidos primero
            </button>

            <button
              type="button"
              wire:click="openModalSancionados2Dias('acumulativo_2')"
              class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-800 px-2.5 py-1 text-xs font-bold transition shadow-2xs cursor-pointer"
              title="Abrir modal con personal que superó tolerancia y alcanzó el acumulativo 2 de descuento"
            >
              <span class="h-2 w-2 rounded-full bg-rose-600 animate-pulse"></span>
              <span>🚨 Acumulativo 2:</span>
              <span class="rounded-full bg-rose-600 text-white px-1.5 py-0.2 text-[10px] font-black">
                {{ $sucursalKpis['sancionados_acumulativo_2'] ?? 0 }}
              </span>
            </button>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            {{-- Botón Exportar Excel --}}
            <button
              type="button"
              wire:click="descargarExcelControl"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 shadow-xs hover:bg-emerald-100 transition disabled:opacity-50 cursor-pointer"
              title="Descargar reporte en formato Excel (.xlsx)"
            >
              <svg class="h-3.5 w-3.5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="8" y1="13" x2="16" y2="13"/>
                <line x1="8" y1="17" x2="16" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              <span wire:loading.remove wire:target="descargarExcelControl">Exportar Excel</span>
              <span wire:loading wire:target="descargarExcelControl">Generando...</span>
            </button>

            {{-- Botón Exportar PDF --}}
            <button
              type="button"
              wire:click="descargarPdfControl"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition disabled:opacity-50 cursor-pointer"
              title="Descargar reporte en formato PDF"
            >
              <svg class="h-3.5 w-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m7 11 5 5 5-5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14"/>
              </svg>
              <span wire:loading.remove wire:target="descargarPdfControl">Exportar PDF</span>
              <span wire:loading wire:target="descargarPdfControl">Generando...</span>
            </button>
          </div>
        </div>

        {{-- Tabla de personal por sucursal --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-xs">
          <table class="min-w-full text-xs">
            <thead>
              <tr class="bg-slate-50/90 border-b border-slate-200">
                <th class="px-4 py-3 text-left cursor-pointer hover:bg-slate-100 select-none" wire:click="sortByControl('nombre')">
                  <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    Personal / Código
                    @if(in_array($ordenControl, ['nombre_asc','nombre_desc']))
                      <span class="text-[#0f67c0]">{{ $ordenControl === 'nombre_asc' ? '▲' : '▼' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-600">
                  Sucursal
                </th>
                <th class="px-4 py-3 text-center cursor-pointer hover:bg-slate-100 select-none" wire:click="sortByControl('horas')">
                  <div class="flex items-center justify-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    Horas trabajadas
                    @if(in_array($ordenControl, ['horas_desc','horas_asc']))
                      <span class="text-[#0f67c0]">{{ $ordenControl === 'horas_desc' ? '▼' : '▲' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="px-4 py-3 text-center cursor-pointer hover:bg-slate-100 select-none" wire:click="sortByControl('retraso')">
                  <div class="flex items-center justify-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    Retraso mes
                    @if(in_array($ordenControl, ['retraso_desc','retraso_asc']))
                      <span class="text-[#0f67c0]">{{ $ordenControl === 'retraso_desc' ? '▼' : '▲' }}</span>
                    @else
                      <span class="text-slate-300">⇅</span>
                    @endif
                  </div>
                </th>
                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-600">
                  Saldo tolerancia
                </th>
                <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-600">
                  Estado tolerancia
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @forelse ($empleados as $empleado)
                @php
                  $estadoRetraso = $empleado->resumen_asistencia['estado_retraso'] ?? 'Dentro de tolerancia';
                  $excedido = $estadoRetraso === 'Excedido';
                  $saldoFormateado = $empleado->resumen_asistencia['saldo_retraso_formateado'] ?? '0 min';
                  $retrasoFormateado = $empleado->resumen_asistencia['retraso_mes_formateado'] ?? '0 min';
                  $horasMes = $empleado->resumen_asistencia['horas_mes'] ?? '0h 0m';
                  $codigo = $empleado->codigo_biometrico ?: '—';
                  $retrasoMinutos = (int)($empleado->resumen_asistencia['retraso_mes'] ?? 0);
                  $toleranciaMinutos = (int)($empleado->resumen_asistencia['tolerancia_mensual'] ?? 35);
                  $toleranciaFormateada = $empleado->resumen_asistencia['tolerancia_mensual_formateada'] ?? ($toleranciaMinutos . ' min');
                  $porcentajeTolerancia = (int)($empleado->resumen_asistencia['porcentaje_tolerancia'] ?? ($toleranciaMinutos > 0 ? min(100, round(($retrasoMinutos / $toleranciaMinutos) * 100)) : 100));
                  $enPeriodoTransicion = (bool)($empleado->resumen_asistencia['en_periodo_transicion'] ?? false);
                  $vigenciaDetalle = $empleado->resumen_asistencia['vigencia_reglamento_detalle'] ?? null;
                  $sancionRegla = $empleado->resumen_asistencia['sancion_regla'] ?? null;
                  $esAcumulativo2 = (!$enPeriodoTransicion) && ($sancionRegla['es_acumulativo_2'] ?? false);
                  $diasSancion = (float)($sancionRegla['dias_sancion'] ?? 0);
                  $sancionTexto = $sancionRegla['sancion_texto'] ?? 'Sin sanción';
                @endphp
                <tr class="{{ ($excedido && !$enPeriodoTransicion) ? 'bg-rose-50/40' : ($enPeriodoTransicion && $excedido ? 'bg-amber-50/20' : '') }} hover:bg-slate-50/80 transition-colors">

                  {{-- Personal --}}
                  <td class="px-4 py-3">
                    <div class="flex items-start gap-2.5">
                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#0f67c0]/10 text-[#0f67c0] font-bold text-xs uppercase select-none mt-0.5">
                        {{ mb_substr($empleado->nombre, 0, 1) }}{{ mb_substr($empleado->apellido, 0, 1) }}
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-900 truncate max-w-[220px]">{{ $empleado->nombre_completo }}</p>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5 truncate max-w-[240px]" title="Cód: {{ $codigo }}@if($empleado->email) | {{ $empleado->email }}@endif">
                          Cód: {{ $codigo }}@if($empleado->email)<span class="text-slate-300 mx-1">•</span><span class="text-slate-500">{{ $empleado->email }}</span>@endif
                        </p>

                        {{-- Barra acumulativa de tolerancia mensual y alerta de sanción económica según Art. 45 --}}
                        <div class="mt-2 w-full max-w-[240px]">
                          <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium mb-1">
                            <span>Tolerancia: <strong>{{ $toleranciaFormateada }}</strong></span>
                            <span class="{{ $excedido ? 'text-rose-700 font-bold' : 'text-slate-600' }}">
                              {{ $retrasoMinutos }}m / {{ $toleranciaMinutos }}m ({{ $porcentajeTolerancia }}%)
                            </span>
                          </div>
                          <div class="h-1.5 w-full rounded-full bg-slate-200/90 overflow-hidden" title="Consumo de tolerancia mensual: {{ $porcentajeTolerancia }}%">
                            <div
                              class="h-full rounded-full transition-all duration-300 {{ $porcentajeTolerancia >= 100 ? 'bg-rose-600' : ($porcentajeTolerancia >= 75 ? 'bg-amber-500' : ($porcentajeTolerancia > 0 ? 'bg-emerald-500' : 'bg-transparent')) }}"
                              style="width: {{ $porcentajeTolerancia }}%"
                            ></div>
                          </div>

                          {{-- Alerta de Sanción Económica --}}
                          <div class="mt-1 flex flex-wrap items-center gap-1">
                            @if($retrasoMinutos === 0)
                              <span class="inline-flex items-center gap-1 rounded bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Puntual • Sin retraso
                              </span>
                            @elseif(!$excedido)
                              <span class="inline-flex items-center gap-1 rounded bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                En tolerancia • Sin sanción
                              </span>
                            @elseif($enPeriodoTransicion)
                              <span class="inline-flex items-center gap-1 rounded bg-amber-50 border border-amber-300 px-1.5 py-0.5 text-[10px] font-bold text-amber-900 shadow-2xs" title="{{ $vigenciaDetalle ?? 'No aplica en este periodo' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                ⏳ Marcha blanca • Sin sanción
                              </span>
                            @elseif($esAcumulativo2)
                              <button
                                type="button"
                                wire:click="openModalSancionados2Dias('acumulativo_2')"
                                class="inline-flex items-center gap-1 rounded-md bg-rose-100 border border-rose-300 px-1.5 py-0.5 text-[10px] font-black text-rose-800 hover:bg-rose-200 transition cursor-pointer shadow-2xs"
                                title="Clic para ver detalle de sanción en el modal"
                              >
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                🚨 Acumulativo 2: {{ $sancionTexto }} (-{{ $diasSancion }}d)
                              </button>
                            @else
                              <button
                                type="button"
                                wire:click="openModalSancionados2Dias('todos')"
                                class="inline-flex items-center gap-1 rounded-md bg-amber-100 border border-amber-300 px-1.5 py-0.5 text-[10px] font-bold text-amber-900 hover:bg-amber-200 transition cursor-pointer shadow-2xs"
                                title="Clic para ver detalle de sanción en el modal"
                              >
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                ⚠️ Sanción: {{ $sancionTexto }} (-{{ $diasSancion }}d)
                              </button>
                            @endif
                          </div>
                        </div>

                      </div>
                    </div>
                  </td>

                  {{-- Sucursal --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 text-slate-700 font-semibold" title="{{ $empleado->sucursal }}">
                      <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                      {{ $empleado->sucursal ?? '—' }}
                    </span>
                  </td>

                  {{-- Horas mes --}}
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center rounded-lg bg-blue-50 border border-blue-100 px-2.5 py-1 font-mono text-xs font-bold text-blue-800">
                      {{ $horasMes }}
                    </span>
                  </td>

                  {{-- Minutos retraso --}}
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center rounded-lg {{ $excedido ? 'bg-rose-50 border border-rose-200 text-rose-800' : 'bg-amber-50 border border-amber-100 text-amber-800' }} px-2.5 py-1 font-mono text-xs font-bold">
                      {{ $retrasoFormateado }}
                    </span>
                  </td>

                  {{-- Saldo --}}
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center rounded-lg {{ $excedido ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 border border-emerald-100 text-emerald-800' }} px-2.5 py-1 font-mono text-xs font-bold">
                      {{ $saldoFormateado }}
                    </span>
                  </td>

                  {{-- Estado Tolerancia --}}
                  <td class="px-4 py-3 text-center">
                    @if($enPeriodoTransicion)
                      <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-2.5 py-0.5 text-[11px] font-bold text-amber-800" title="{{ $vigenciaDetalle ?? 'En marcha blanca / no aplica este mes' }}">
                        <span>⏳ En marcha blanca</span>
                      </span>
                    @elseif($excedido)
                      @if($esAcumulativo2)
                        <button
                          type="button"
                          wire:click="openModalSancionados2Dias('acumulativo_2')"
                          class="inline-flex items-center gap-1 rounded-full bg-rose-600 text-white px-2.5 py-0.5 text-[11px] font-black shadow-xs hover:bg-rose-700 transition cursor-pointer"
                          title="Clic para ver detalle de sanción de 2+ días"
                        >
                          <svg class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                          <span>Acumulativo 2 (-{{ $diasSancion }}d)</span>
                        </button>
                      @else
                        <button
                          type="button"
                          wire:click="openModalSancionados2Dias('todos')"
                          class="inline-flex items-center gap-1 rounded-full bg-rose-100 border border-rose-300 px-2.5 py-0.5 text-[11px] font-bold text-rose-800 hover:bg-rose-200 transition cursor-pointer"
                          title="Clic para ver detalle de sanción"
                        >
                          <svg class="h-3 w-3 text-rose-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                          <span>Excedido (-{{ $diasSancion }}d)</span>
                        </button>
                      @endif
                    @else
                      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                        <svg class="h-3 w-3 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>En tolerancia</span>
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-500">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-700">No se encontró personal para la sucursal seleccionada.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Paginación --}}
        @if ($empleados->hasPages())
          <div class="table-pagination-shell mt-4">
            <div class="table-pagination-bar">
              <p class="table-pagination-copy">
                Mostrando {{ $empleados->firstItem() }} a {{ $empleados->lastItem() }} de {{ $empleados->total() }} empleados
              </p>
              <div class="table-pagination-actions">
                <button type="button" wire:click="previousPage"
                  @disabled($empleados->onFirstPage())
                  class="table-pagination-button {{ $empleados->onFirstPage() ? 'table-pagination-button-disabled' : '' }}">
                  Anterior
                </button>
                @foreach(range(max(1, $empleados->currentPage() - 2), min($empleados->lastPage(), $empleados->currentPage() + 2)) as $p)
                  <button type="button" wire:click="gotoPage({{ $p }})"
                    class="table-pagination-button {{ $p === $empleados->currentPage() ? 'table-pagination-button-active' : '' }}">
                    {{ $p }}
                  </button>
                @endforeach
                <button type="button" wire:click="nextPage"
                  @disabled(! $empleados->hasMorePages())
                  class="table-pagination-button {{ ! $empleados->hasMorePages() ? 'table-pagination-button-disabled' : '' }}">
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        @endif

      @endif {{-- end @else (controlSearchPerformed) --}}

    </article>
  </section>
  @endif

  @if ($vista === 'sucursales')
  <section>
    <article class="surface-card">
      <div class="section-head-row flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <p class="section-kicker !mb-0">REGISTRO DE ASISTENCIA</p>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-800 px-2.5 py-0.5 text-xs font-bold">
              <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              {{ $sucursalSeleccionadaLabel }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-700 px-2.5 py-0.5 text-xs font-medium">
              <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              {{ $sucursalesPeriodoLabel }}
            </span>
          </div>
          <h3 class="section-title">Registro mensual de sucursales</h3>
          <p class="section-copy-sm">Listado completo de todas las marcaciones registradas para la sucursal seleccionada, con detalle de horarios programados, entradas, salidas, horas efectivas y cálculo de retrasos.</p>
        </div>

        <div class="flex items-center gap-2.5 self-stretch sm:self-auto justify-end">
          <button type="button" wire:click="descargarExcelSucursales" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-xl transition-all cursor-pointer shadow-2xs hover:shadow-xs">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Exportar Excel</span>
          </button>
          <button type="button" wire:click="descargarPdfSucursales" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-300 rounded-xl transition-all cursor-pointer shadow-2xs hover:shadow-xs">
            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6M9 17h6"/></svg>
            <span>Exportar PDF</span>
          </button>
        </div>
      </div>

      {{-- Filtros Mejorados (Sin buscador de personal redundante) --}}
      <div class="mt-6 rounded-2xl border border-slate-200/90 bg-gradient-to-b from-slate-50/90 to-slate-100/50 p-5 shadow-2xs">
        <form wire:submit.prevent="aplicarFiltroSucursales">
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- 1. Buscador / Selector de Sucursal --}}
            <div class="space-y-1.5">
              <label for="sucursales-sucursal" class="form-label text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Buscador de sucursal</span>
              </label>
              <div class="relative">
                <select
                  id="sucursales-sucursal"
                  wire:model="sucursalesSucursal"
                  class="form-input text-xs font-semibold text-slate-800 bg-white shadow-2xs !py-2.5">
                  <option value="">Todas las sucursales</option>
                  @foreach ($sucursalesLista as $sucursalNombre)
                    <option value="{{ $sucursalNombre }}">{{ $sucursalNombre }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- 2. Modalidad: Día o Mes --}}
            <div class="space-y-1.5">
              <label class="form-label text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Filtro de período</span>
              </label>
              <div class="flex rounded-xl border border-slate-300 bg-white p-1 shadow-2xs">
                <button
                  type="button"
                  wire:click="setSucursalesPeriodoTipo('mes')"
                  class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all text-center {{ $sucursalesPeriodoTipo === 'mes' ? 'bg-[#0f172a] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                  Por Mes
                </button>
                <button
                  type="button"
                  wire:click="setSucursalesPeriodoTipo('dia')"
                  class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all text-center {{ $sucursalesPeriodoTipo === 'dia' ? 'bg-[#0f172a] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                  Por Día
                </button>
              </div>
            </div>

            {{-- 3. Selector de Fecha (Día o Mes según la modalidad activa) --}}
            <div class="space-y-1.5 sm:col-span-2 lg:col-span-1">
              @if ($sucursalesPeriodoTipo === 'dia')
                <label for="sucursales-fecha-dia" class="form-label text-xs font-bold text-slate-800 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Fecha del día</span>
                </label>
                <input
                  id="sucursales-fecha-dia"
                  type="date"
                  wire:model="sucursalesFechaDia"
                  class="form-input text-xs font-semibold text-slate-800 bg-white shadow-2xs !py-2.5">
              @else
                <label for="sucursales-mes" class="form-label text-xs font-bold text-slate-800 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Mes a consultar</span>
                </label>
                <input
                  id="sucursales-mes"
                  type="month"
                  wire:model="sucursalesMes"
                  class="form-input text-xs font-semibold text-slate-800 bg-white shadow-2xs !py-2.5">
              @endif
            </div>
          </div>

          {{-- Fila Secundaria: Filtro de Estado, Ordenamiento y Botones de Acción --}}
          <div class="mt-4 pt-3.5 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
              <div class="flex items-center gap-2">
                <span class="text-xs text-slate-600 font-semibold">Estado:</span>
                <select wire:model="sucursalesEstadoFiltro" class="form-input !py-1.5 text-xs font-medium bg-white w-auto shadow-2xs">
                  <option value="todos">Todos los estados</option>
                  <option value="puntual">Solo puntuales</option>
                  <option value="retraso">Con retraso</option>
                  <option value="incompleto">Incompletas / Sin salida</option>
                </select>
              </div>

              <div class="flex items-center gap-2">
                <span class="text-xs text-slate-600 font-semibold">Orden:</span>
                <select wire:model="sucursalesOrden" class="form-input !py-1.5 text-xs font-medium bg-white w-auto shadow-2xs">
                  <option value="fecha_desc">Fecha más reciente</option>
                  <option value="fecha_asc">Fecha más antigua</option>
                  <option value="retraso_desc">Mayor retraso primero</option>
                  <option value="nombre_asc">Personal (A - Z)</option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
              <button
                type="button"
                wire:click="limpiarFiltrosSucursales"
                class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl transition-colors cursor-pointer shadow-2xs">
                Restablecer
              </button>
              <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 text-xs font-bold text-white bg-[#0f172a] hover:bg-[#1e293b] rounded-xl shadow-xs hover:shadow transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Consultar marcaciones</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      {{-- Barra informativa superior de la tabla --}}
      <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2">
          <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
          <span class="text-xs font-bold text-slate-700">Listado de Marcaciones</span>
          <span class="text-xs text-slate-400">•</span>
          <span class="text-xs font-medium text-slate-500">
            Total: <strong class="text-slate-800">{{ number_format($sucursalesRegistros->total()) }}</strong> marcaciones encontradas
          </span>
        </div>
        <div class="text-[11px] text-slate-500">
          Regional: <strong class="text-slate-700">{{ $sucursalSeleccionadaLabel }}</strong>
        </div>
      </div>

      {{-- Tabla Mejorada de Marcaciones --}}
      <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/90 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                <th class="py-3.5 px-4">Fecha y Día</th>
                <th class="py-3.5 px-4">Personal / Funcionario</th>
                <th class="py-3.5 px-4">Sucursal</th>
                <th class="py-3.5 px-4 text-center">Horario Programado</th>
                <th class="py-3.5 px-4 text-center">Hora Entrada</th>
                <th class="py-3.5 px-4 text-center">Hora Salida</th>
                <th class="py-3.5 px-4 text-center">Horas Trabajadas</th>
                <th class="py-3.5 px-4 text-center">Tardanza / Retraso</th>
                <th class="py-3.5 px-4 text-center">Estado de Asistencia</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse ($sucursalesRegistros as $row)
                <tr class="hover:bg-blue-50/30 transition-colors">
                  {{-- Fecha --}}
                  <td class="py-3 px-4 whitespace-nowrap">
                    <div class="font-bold text-slate-900">{{ $row->fecha_formateada }}</div>
                    <div class="text-[11px] text-slate-500 font-medium capitalize">{{ $row->dia }}</div>
                  </td>

                  {{-- Personal --}}
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-900 text-[12.5px] leading-tight">
                      {{ $row->empleado?->nombre_completo ?? 'Sin asignar' }}
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10.5px]">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono font-bold">
                        ID: {{ $row->codigo }}
                      </span>
                      @if ($row->empleado?->area)
                        <span class="text-slate-500 font-medium truncate max-w-[180px]">
                          {{ $row->empleado->area }}
                        </span>
                      @endif
                    </div>
                  </td>

                  {{-- Sucursal --}}
                  <td class="py-3 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100/90 border border-slate-200/80 px-2.5 py-1 text-[11px] font-bold text-slate-700">
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                      {{ $row->empleado?->sucursal ?? 'N/D' }}
                    </span>
                  </td>

                  {{-- Horario Programado --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-slate-600 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                      <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
                      {{ $row->horario_programado }}
                    </span>
                  </td>

                  {{-- Entrada --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    @if ($row->hora_entrada !== '--:--')
                      <span class="font-mono font-bold text-slate-800 text-[12px] bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                        {{ $row->hora_entrada }}
                      </span>
                    @else
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                        Sin entrada
                      </span>
                    @endif
                  </td>

                  {{-- Salida --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    @if ($row->hora_salida !== '--:--')
                      <span class="font-mono font-bold text-slate-800 text-[12px] bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                        {{ $row->hora_salida }}
                      </span>
                    @else
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                        Sin salida
                      </span>
                    @endif
                  </td>

                  {{-- Horas Trabajadas --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    <span class="font-mono font-bold text-slate-700 text-[11.5px]">
                      {{ $row->horas_trabajadas }}
                    </span>
                  </td>

                  {{-- Retraso --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    @if (($row->minutos_retraso ?? 0) > 0)
                      <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-[11px] font-bold text-rose-700">
                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        +{{ $row->minutos_retraso }} min
                      </span>
                    @elseif ($row->hora_entrada !== '--:--')
                      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                        <svg class="w-3 h-3 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Puntual
                      </span>
                    @else
                      <span class="text-slate-400 font-mono">—</span>
                    @endif
                  </td>

                  {{-- Estado --}}
                  <td class="py-3 px-4 text-center whitespace-nowrap">
                    @if ($row->tipo_estado === 'puntual')
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-0.5 text-[11px] font-bold text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $row->estado_marcacion }}
                      </span>
                    @elseif ($row->tipo_estado === 'retraso')
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-300 px-3 py-0.5 text-[11px] font-bold text-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        {{ $row->estado_marcacion }}
                      </span>
                    @elseif ($row->tipo_estado === 'en_curso')
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-3 py-0.5 text-[11px] font-medium text-blue-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        {{ $row->estado_marcacion }}
                      </span>
                    @elseif ($row->tipo_estado === 'incompleto')
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-200 px-3 py-0.5 text-[11px] font-bold text-rose-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        {{ $row->estado_marcacion }}
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-3 py-0.5 text-[11px] font-medium text-slate-600">
                        {{ $row->estado_marcacion }}
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="py-16 text-center text-slate-500">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">No se encontraron marcaciones para la sucursal o período seleccionado</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">Selecciona otra sucursal o ajusta la fecha para ver los registros de asistencia correspondientes.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Paginación --}}
        @if ($sucursalesRegistros->hasPages())
          <div class="table-pagination-shell border-t border-slate-200 px-5 py-3.5 bg-slate-50/70">
            <div class="table-pagination-bar flex flex-col sm:flex-row items-center justify-between gap-3">
              <p class="table-pagination-copy text-xs text-slate-500">
                Mostrando registros del <span class="font-bold text-slate-800">{{ $sucursalesRegistros->firstItem() }}</span> al <span class="font-bold text-slate-800">{{ $sucursalesRegistros->lastItem() }}</span> (Página {{ $sucursalesRegistros->currentPage() }} de {{ $sucursalesRegistros->lastPage() }})
              </p>
              <div class="table-pagination-actions flex items-center gap-1.5">
                <button
                  type="button"
                  wire:click="previousPage('sucursalesPage')"
                  @disabled($sucursalesRegistros->onFirstPage())
                  class="table-pagination-button {{ $sucursalesRegistros->onFirstPage() ? 'table-pagination-button-disabled opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-slate-100' }}">
                  Anterior
                </button>
                @foreach(range(max(1, $sucursalesRegistros->currentPage() - 2), min($sucursalesRegistros->lastPage(), $sucursalesRegistros->currentPage() + 2)) as $p)
                  <button
                    type="button"
                    wire:click="gotoPage({{ $p }}, 'sucursalesPage')"
                    class="table-pagination-button {{ $p === $sucursalesRegistros->currentPage() ? 'table-pagination-button-active !bg-[#0f172a] !text-white' : 'cursor-pointer hover:bg-slate-100' }}">
                    {{ $p }}
                  </button>
                @endforeach
                <button
                  type="button"
                  wire:click="nextPage('sucursalesPage')"
                  @disabled(! $sucursalesRegistros->hasMorePages())
                  class="table-pagination-button {{ ! $sucursalesRegistros->hasMorePages() ? 'table-pagination-button-disabled opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-slate-100' }}">
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        @endif
      </div>
    </article>
  </section>
  @endif

</div>
