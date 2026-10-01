<div class="page-stack">
  {{-- ALERTAS DE ESTADO Y ADVERTENCIA --}}
  @if (session()->has('status'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">✓</span>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs">✕</button>
    </div>
  @endif

  @if (session()->has('warning'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800 shadow-xs flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">⚠️</span>
        <span>{{ session('warning') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-900 font-bold text-xs">✕</button>
    </div>
  @endif

  {{-- MODAL CREAR INCIDENCIA (ALTAS) --}}
  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateModal" class="app-modal-close app-modal-close-corner"
          aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Registro operativo RRHH</p>
            <h3 class="section-title app-modal-title">Nueva incidencia o permiso laboral</h3>
            <p class="section-copy-sm">Registra permisos, incidencias, cumpleaños o faltas con opción de adjuntar comprobante o fotografía respaldo.</p>
          </div>
        </div>

        <form wire:submit="saveIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          {{-- Personal --}}
          <div class="md:col-span-2">
            <label class="form-label font-bold text-slate-800">Personal / Funcionario *</label>
            <div class="relative">
              <input type="search" wire:model.live.debounce.300ms="empleadoSearch" class="form-input @error('empleadoId') border-rose-400 bg-rose-50/20 @enderror"
                placeholder="Escribe nombre o código biométrico..." autocomplete="off">
              @if(filled($empleadoSearch) && blank($empleadoId))
                <div class="absolute z-20 mt-2 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-xl">
                  @forelse($empleadosFormulario as $empleado)
                    <button type="button" wire:click="seleccionarEmpleado({{ $empleado->id }})"
                      class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-indigo-50 transition-colors">
                      <span class="font-bold text-slate-900 block">{{ $empleado->nombre_completo }}</span>
                      <span class="text-xs text-slate-500">Cód: {{ $empleado->codigo_biometrico ?: 'Sin código' }} | Sucursal: {{ $empleado->sucursal ?: 'Sin sucursal' }}</span>
                    </button>
                  @empty
                    <p class="px-3 py-2 text-sm text-slate-500">No se encontró personal con ese nombre o código.</p>
                  @endforelse
                </div>
              @endif
            </div>
            <input type="hidden" wire:model="empleadoId">
            @if(filled($empleadoSearch) && $empleadosFormulario->isEmpty() && blank($empleadoId))
              <p class="text-xs font-semibold text-rose-600 mt-1">No se encontró personal con ese nombre o código.</p>
            @endif
            @error('empleadoId')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Tipo --}}
          <div>
            <label class="form-label font-bold text-slate-800">Tipo de Novedad *</label>
            <select wire:model.live="tipo" class="form-input">
              @foreach($tipos as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('tipo')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Tipo de Permiso Específico --}}
          @if($tipo === 'permiso')
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="form-label font-bold text-slate-800 mb-0">Especificación de Permiso</label>
                <button type="button" wire:click="openGestionTiposModal" class="text-xs font-extrabold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1 cursor-pointer">
                  <span>⚙️ Gestionar tipos</span>
                </button>
              </div>
              <select wire:model.live="tipoPermiso" class="form-input">
                <option value="">Selecciona un tipo de permiso</option>
                @foreach($tiposPermiso as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
              @error('tipoPermiso')
                <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
          @endif

          {{-- Alcance --}}
          <div>
            <label class="form-label font-bold text-slate-800">Alcance / Duración *</label>
            <select wire:model.live="alcance" class="form-input">
              @if($tipo === 'cumpleanos')
                @foreach($this->alcancesCumpleanosDisponibles() as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              @else
                @foreach($this->alcancesDisponibles() as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              @endif
            </select>
            @error('alcance')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Estado --}}
          <div>
            <label class="form-label font-bold text-slate-800">Estado inicial *</label>
            <select wire:model="estado" class="form-input">
              <option value="aprobado">Aprobado</option>
              <option value="pendiente">Pendiente</option>
            </select>
            @error('estado')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Fecha Inicio --}}
          <div>
            <label class="form-label font-bold text-slate-800">Fecha inicio *</label>
            <input type="date" wire:model.live="fechaInicio" class="form-input">
            @error('fechaInicio')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Fecha Fin --}}
          <div>
            <label class="form-label font-bold text-slate-800">Fecha fin *</label>
            <input type="date" wire:model="fechaFin" class="form-input" @disabled($tipo === 'cumpleanos')>
            @error('fechaFin')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Horario si alcance === 'horas' --}}
          @if($alcance === 'horas')
            <div class="md:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 grid gap-4 md:grid-cols-2 shadow-2xs">
              <div class="md:col-span-2">
                <span class="text-xs font-black uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">⏰ Definir bloque de horario exacto</span>
                <p class="text-xs text-indigo-700">Ingresa la hora exacta de salida y retorno del funcionario.</p>
              </div>
              <div>
                <label class="form-label text-indigo-900 font-bold">Hora salida *</label>
                <input type="time" wire:model="horaInicio" class="form-input border-indigo-300 focus:border-indigo-500 bg-white">
                @error('horaInicio')
                  <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label class="form-label text-indigo-900 font-bold">Hora retorno *</label>
                <input type="time" wire:model="horaFin" class="form-input border-indigo-300 focus:border-indigo-500 bg-white">
                @error('horaFin')
                  <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
              </div>
            </div>
          @endif

          {{-- Motivo --}}
          <div class="md:col-span-2">
            <label class="form-label font-bold text-slate-800">Motivo / Descripción de la solicitud</label>
            <textarea wire:model="motivo" rows="2" class="form-input"
              placeholder="Ej. Permiso médico, trámite personal, asunto familiar o comisión laboral..."></textarea>
            @error('motivo')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- SUBIR FOTO / COMPROBANTE --}}
          <div class="md:col-span-2 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
            <label class="form-label font-bold text-slate-800 flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <span>📸</span>
                <span>Foto o Comprobante de justificación (Opcional)</span>
              </span>
              <span class="text-xs text-slate-500 font-normal">(Imagen JPG, PNG, WEBP o PDF max 5MB)</span>
            </label>

            <input type="file" wire:model="comprobante" accept="image/*,.pdf"
              class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">

            <div wire:loading wire:target="comprobante" class="text-xs text-indigo-600 font-bold flex items-center gap-1.5">
              <span class="animate-spin inline-block">⏳</span> Carga de comprobante en proceso...
            </div>

            @if ($comprobante)
              <div class="mt-2 p-2 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2 overflow-hidden">
                  @if (str_contains($comprobante->getMimeType() ?? '', 'image'))
                    <img src="{{ $comprobante->temporaryUrl() }}" class="w-10 h-10 object-cover rounded-md border border-emerald-300">
                  @else
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-800 font-bold rounded-md flex items-center justify-center text-xs">PDF</div>
                  @endif
                  <span class="text-xs font-bold text-emerald-900 truncate">{{ $comprobante->getClientOriginalName() }}</span>
                </div>
                <button type="button" wire:click="$set('comprobante', null)" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1">Quitar</button>
              </div>
            @endif

            @error('comprobante')
              <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="button" wire:click="closeCreateModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Guardar incidencia</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL GESTIÓN DE TIPOS DE PERMISOS --}}
  @if ($showGestionTiposModal)
    <div class="app-modal-backdrop" wire:click="closeGestionTiposModal">
      <div class="app-modal-card max-w-xl" x-on:click.stop>
        <button type="button" wire:click="closeGestionTiposModal" class="app-modal-close app-modal-close-corner"
          aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Configuración RRHH</p>
            <h3 class="section-title app-modal-title">Tipos de Permisos Laborales</h3>
            <p class="section-copy-sm">Agrega nuevos tipos de permisos o edita sus nombres para el registro de altas.</p>
          </div>
        </div>

        @if (session()->has('tipo_status'))
          <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-800 flex items-center justify-between">
            <span>✓ {{ session('tipo_status') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700">✕</button>
          </div>
        @endif

        {{-- Formulario para Agregar Nuevo Tipo --}}
        <form wire:submit="crearTipoPermiso" class="mt-5 p-3.5 bg-indigo-50/60 border border-indigo-200 rounded-xl space-y-2">
          <label class="form-label text-indigo-900 font-bold text-xs uppercase tracking-wider">➕ Registrar nuevo tipo de permiso</label>
          <div class="flex gap-2">
            <input type="text" wire:model="nuevoTipoNombre" class="form-input bg-white text-sm" placeholder="Ej. Licencia por Paternidad, Duelo, etc.">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-2xs shrink-0 cursor-pointer transition">
              Agregar
            </button>
          </div>
          @error('nuevoTipoNombre')
            <p class="text-xs font-semibold text-rose-600 mt-0.5">{{ $message }}</p>
          @enderror
        </form>

        {{-- Lista de Tipos de Permisos Existentes --}}
        <div class="mt-6 space-y-2 max-h-80 overflow-y-auto pr-1">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tipos registrados actualmente:</p>

          @forelse($tiposPermisosModel as $tipoItem)
            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between gap-3 shadow-2xs hover:border-indigo-200 transition">
              @if ($editandoTipoId === $tipoItem->id)
                <div class="flex-1 flex items-center gap-2">
                  <input type="text" wire:model="editandoTipoNombre" class="form-input text-sm py-1.5">
                  <button type="button" wire:click="guardarEdicionTipoPermiso" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg cursor-pointer">
                    Guardar
                  </button>
                  <button type="button" wire:click="cancelarEditarTipoPermiso" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-lg cursor-pointer">
                    Cancelar
                  </button>
                </div>
              @else
                <div>
                  <span class="font-bold text-slate-900 text-sm block">{{ $tipoItem->nombre }}</span>
                  <span class="text-[11px] text-slate-400 font-mono">Clave interna: {{ $tipoItem->clave }}</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                  <button type="button" wire:click="iniciarEditarTipoPermiso({{ $tipoItem->id }})" class="p-1.5 text-xs text-indigo-600 hover:bg-indigo-50 rounded-lg font-bold border border-indigo-100 cursor-pointer" title="Editar nombre del tipo de permiso">
                    ✏️ Editar
                  </button>
                  <button type="button" wire:click="eliminarTipoPermiso({{ $tipoItem->id }})" wire:confirm="¿Seguro que deseas eliminar el tipo '{{ $tipoItem->nombre }}'?" class="p-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg font-bold border border-rose-100 cursor-pointer" title="Eliminar tipo de permiso">
                    🗑️
                  </button>
                </div>
              @endif
            </div>
          @empty
            <p class="text-xs text-slate-500 py-3 text-center">No hay tipos de permisos registrados.</p>
          @endforelse
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeGestionTiposModal" class="login-submit app-modal-submit !bg-slate-700">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- SECCIÓN PRINCIPAL --}}
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Control de novedades</p>
        <h3 class="section-title">Incidencias, permisos y faltas</h3>
        <p class="section-copy-sm">Programa permisos por horas o días completos, licencias con respaldos/comprobantes y faltas con tiempo contabilizado.</p>
      </div>
      <style>
        .btn-expandable {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          height: 2.5rem;
          padding: 0 0.85rem;
          border-radius: 0.85rem;
          cursor: pointer;
          overflow: hidden;
          white-space: nowrap;
          position: relative;
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
          flex-shrink: 0;
        }
        .btn-expandable:hover {
          box-shadow: 0 4px 14px rgba(15, 103, 192, 0.18);
          transform: translateY(-1px);
        }
        .btn-expandable-text {
          max-width: 0;
          opacity: 0;
          margin-left: 0;
          overflow: hidden;
          display: inline-block;
          vertical-align: middle;
          white-space: nowrap;
          transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, margin-left 0.25s ease;
          font-size: 0.82rem;
          font-weight: 800;
          letter-spacing: -0.01em;
        }
        .btn-expandable:hover .btn-expandable-text {
          max-width: 190px;
          opacity: 1;
          margin-left: 0.5rem;
        }
        .btn-expandable-gear {
          background-color: #ffffff;
          border: 1px solid #cbd5e1;
          color: #475569;
        }
        .btn-expandable-gear:hover {
          background-color: #f1f5f9;
          border-color: #0f67c0;
          color: #0f67c0;
        }
        .btn-expandable-gear svg {
          transition: transform 0.4s ease;
        }
        .btn-expandable-gear:hover svg {
          transform: rotate(90deg);
        }
        .btn-expandable-plus {
          background-color: #0f67c0;
          border: 1px solid #0f67c0;
          color: #ffffff;
        }
        .btn-expandable-plus:hover {
          background-color: #0d58a4;
          border-color: #0d58a4;
        }
        .btn-expandable-plus svg {
          transition: transform 0.25s ease;
        }
        .btn-expandable-plus:hover svg {
          transform: scale(1.18);
        }
      </style>

      <div class="flex items-center gap-2 shrink-0">
        {{-- Botón: Tipos de Permiso (Engranaje) --}}
        <button
          type="button"
          wire:click="openGestionTiposModal"
          class="btn-expandable btn-expandable-gear"
          title="Tipos de permiso"
        >
          <svg style="width:1.15rem;height:1.15rem;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.10a2 2 0 0 1-1-1.72v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
          <span class="btn-expandable-text">
            Tipos de permiso
          </span>
        </button>

        {{-- Botón: Agregar Incidencia (Más) --}}
        <button
          type="button"
          wire:click="openCreateModal"
          class="btn-expandable btn-expandable-plus"
          title="Agregar incidencia"
        >
          <svg style="width:1.15rem;height:1.15rem;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span class="btn-expandable-text">
            Agregar incidencia
          </span>
        </button>
      </div>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-3">
        <div class="space-y-2">
          <label class="form-label">Buscar por nombre o código</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input"
            placeholder="Ej. Juana o 123456">
        </div>
        <div class="space-y-2">
          <label class="form-label">Filtrar por tipo</label>
          <select wire:model.live="tipoFiltro" class="form-input">
            <option value="">Todos</option>
            @foreach($tipos as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Mes de referencia</label>
          <input type="month" wire:model.live="mesFiltro" class="form-input">
        </div>
      </div>

      <table class="history-table">
        <thead>
          <tr>
            <th>Personal</th>
            <th>Tipo</th>
            <th>Periodo</th>
            <th>Boleta</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($incidencias as $item)
            <tr wire:key="incidencia-row-{{ $item->id }}" class="hover:bg-indigo-50/30 transition-colors">
              <td>
                <strong class="font-bold text-slate-900 block">{{ $item->empleado?->nombre_completo ?? 'Sin personal' }}</strong>
                <span class="text-[11px] text-slate-400 font-mono">CI: {{ $item->empleado?->codigo_biometrico ?? 'S/D' }} · {{ $item->empleado?->sucursal ?? 'Sin sucursal' }}</span>
              </td>
              <td>
                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60" @if($item->motivo) title="{{ $item->motivo }}" @endif>
                  {{ $item->tipo_label }}
                </span>
              </td>
              <td>
                <div class="font-bold text-xs text-slate-800">
                  {{ $item->fecha_inicio?->format('d/m/Y') ?? '--/--/----' }}
                  @if($item->fecha_fin && $item->fecha_fin->ne($item->fecha_inicio))
                    - {{ $item->fecha_fin->format('d/m/Y') }}
                  @endif
                </div>
                @if($item->hora_inicio && $item->hora_fin)
                  <div class="mt-0.5 text-[11px] text-indigo-600 font-mono font-semibold">
                    {{ substr($item->hora_inicio, 0, 5) }} - {{ substr($item->hora_fin, 0, 5) }}
                  </div>
                @endif
              </td>

              {{-- Boleta PDF --}}
              <td>
                <button
                  type="button"
                  wire:click="descargarBoletaPdf({{ $item->id }})"
                  wire:loading.attr="disabled"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-800 font-extrabold text-xs shadow-2xs transition cursor-pointer"
                  title="Descargar Boleta en PDF"
                >
                  <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                  <span>Boleta</span>
                </button>
              </td>

              {{-- Estado --}}
              <td>
                @if ($item->estado === 'aprobado')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>Aprobado</span>
                  </span>
                @elseif ($item->estado === 'rechazado')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200/80 shadow-2xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    <span>Rechazado</span>
                  </span>
                @elseif ($item->estado === 'pendiente')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-800 border border-amber-200/80 shadow-2xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Pendiente</span>
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    {{ ucfirst($item->estado) }}
                  </span>
                @endif
              </td>

              {{-- Acciones --}}
              <td class="text-center">
                <div class="flex items-center justify-center gap-1.5">
                  {{-- Ver comprobante o foto respaldo --}}
                  <button
                    type="button"
                    wire:click="verComprobante({{ $item->id }})"
                    class="h-8 w-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 flex items-center justify-center transition shadow-2xs cursor-pointer border border-indigo-200 hover:scale-105 active:scale-95"
                    title="Ver comprobante o justificación"
                  >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>

                  {{-- Aprobar solicitud (solo icono check si está pendiente) --}}
                  @if ($item->estado === 'pendiente')
                    <button
                      type="button"
                      wire:click="abrirConfirmacion({{ $item->id }}, 'aprobado')"
                      wire:loading.attr="disabled"
                      wire:target="abrirConfirmacion({{ $item->id }}, 'aprobado')"
                      class="h-8 w-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white flex items-center justify-center transition shadow-2xs cursor-pointer hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                      title="Aprobar esta solicitud"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-slate-400 py-6">No hay incidencias registradas para el filtro actual.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($incidencias->hasPages())
      <div class="table-pagination-shell">
        <div class="table-pagination-bar">
          <p class="table-pagination-copy">
            Mostrando {{ $incidencias->firstItem() }} a {{ $incidencias->lastItem() }} de {{ $incidencias->total() }}
            registros
          </p>
          <div class="table-pagination-actions">
            <button type="button" wire:click="previousPage" @disabled($incidencias->onFirstPage())
              class="table-pagination-button {{ $incidencias->onFirstPage() ? 'table-pagination-button-disabled' : '' }}">Anterior</button>
            @foreach (range(max(1, $incidencias->currentPage() - 2), min($incidencias->lastPage(), $incidencias->currentPage() + 2)) as $page)
              <button type="button" wire:click="gotoPage({{ $page }})"
                class="table-pagination-button {{ $page === $incidencias->currentPage() ? 'table-pagination-button-active' : '' }}">{{ $page }}</button>
            @endforeach
            <button type="button" wire:click="nextPage" @disabled(!$incidencias->hasMorePages())
              class="table-pagination-button {{ !$incidencias->hasMorePages() ? 'table-pagination-button-disabled' : '' }}">Siguiente</button>
          </div>
        </div>
      </div>
    @endif
  </section>

  {{-- MODAL DE CONFIRMACIÓN APROBAR --}}
  @if ($showConfirmModal)
    <div class="app-modal-backdrop" wire:click="cancelarConfirmacion">
      <div class="app-modal-card max-w-lg" x-on:click.stop>
        <button type="button" wire:click="cancelarConfirmacion" class="app-modal-close app-modal-close-corner" aria-label="Cerrar">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Gestión de solicitudes</p>
            <h3 class="section-title app-modal-title">
              @if ($confirmandoNuevoEstado === 'aprobado')
                ✅ Confirmar Aprobación
              @else
                ❌ Confirmar Rechazo
              @endif
            </h3>
          </div>
        </div>

        <div class="mt-5 space-y-3">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-1">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Funcionario</p>
            <p class="font-bold text-slate-900 text-sm">{{ $confirmandoEmpleadoNombre }}</p>
            @if ($confirmandoDetalle)
              <p class="text-xs text-slate-500">{{ $confirmandoDetalle }}</p>
            @endif
          </div>

          @if ($confirmandoNuevoEstado === 'aprobado')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3 flex items-start gap-2.5">
              <span class="text-emerald-600 text-base mt-0.5">✓</span>
              <p class="text-sm text-emerald-800 font-semibold">La solicitud será marcada como <strong>Aprobada</strong>.</p>
            </div>
          @endif

          @error('motivoRechazo')
            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="cancelarConfirmacion" class="app-modal-secondary">Cancelar</button>
          <button
            type="button"
            wire:click="confirmarAccion"
            wire:loading.attr="disabled"
            wire:target="confirmarAccion"
            class="login-submit app-modal-submit {{ $confirmandoNuevoEstado === 'aprobado' ? '' : '!bg-rose-600 hover:!bg-rose-700' }}"
          >
            <span wire:loading.remove wire:target="confirmarAccion">
              @if ($confirmandoNuevoEstado === 'aprobado') ✅ Sí, aprobar @else ❌ Sí, rechazar @endif
            </span>
            <span wire:loading wire:target="confirmarAccion">Procesando...</span>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- MODAL VISOR DE COMPROBANTE --}}
  @if ($showComprobanteModal)
    <div class="app-modal-backdrop" wire:click="cerrarComprobanteModal" style="position:fixed;inset:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(4px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;">
      <div class="app-modal-card" x-on:click.stop style="background:#fff;border-radius:1.25rem;max-width:54rem;width:100%;max-height:90vh;overflow-y:auto;padding:1.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:.75rem;">
          <div>
            <h3 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0;">{{ $modalComprobanteTitulo }}</h3>
            <p style="font-size:.78rem;color:#64748b;margin:.25rem 0 0 0;">{{ $modalComprobanteDetalle }}</p>
          </div>
          <button type="button" wire:click="cerrarComprobanteModal" style="background:transparent;border:none;color:#94a3b8;font-size:1.2rem;font-weight:bold;cursor:pointer;padding:.25rem .5rem;">✕</button>
        </div>

        <div style="margin-top:1rem;text-align:center;background:#f8fafc;border-radius:.75rem;padding:1rem;border:1px solid #e2e8f0;display:flex;justify-content:center;align-items:center;min-height:300px;">
          @if ($modalComprobanteUrl)
            <img src="{{ $modalComprobanteUrl }}" alt="Comprobante" style="max-width:100%;max-height:68vh;object-fit:contain;border-radius:.5rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
          @else
            <div style="padding: 2.5rem 1rem; text-align: center; color: #64748b;">
              <p style="font-size: 2.2rem; margin: 0 0 0.5rem 0;">📷</p>
              <p style="font-size: 0.95rem; font-weight: 800; color: #334155; margin: 0;">Esta incidencia no cuenta con comprobante fotográfico adjunto.</p>
            </div>
          @endif
        </div>

        <div style="margin-top:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
          <span style="font-size:.75rem;color:#475569;font-weight:600;">🔒 Respaldo fotográfico almacenado en base de datos</span>
          <button type="button" wire:click="cerrarComprobanteModal" class="login-submit !w-auto !py-2 !px-5" style="background:#334155;">Cerrar visor</button>
        </div>
      </div>
    </div>
  @endif
</div>