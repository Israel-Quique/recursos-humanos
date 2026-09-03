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

  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner"
          aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar incidencia</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta incidencia? Esta accion quedara registrada en
              auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteIncidenciaLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteIncidencia" class="table-action-button table-action-button-danger">Si,
            eliminar</button>
        </div>
      </div>
    </div>
  @endif

  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateModal" class="app-modal-close app-modal-close-corner"
          aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Registro operativo</p>
            <h3 class="section-title app-modal-title">Nueva incidencia laboral</h3>
            <p class="section-copy-sm">Registra permisos, incidencias, cumpleanos o faltas por horas, media jornada o dia
              completo.</p>
          </div>
        </div>

        <form wire:submit="saveIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Personal</label>
            <div class="relative">
              <input type="search" wire:model.live.debounce.300ms="empleadoSearch" class="form-input"
                placeholder="Escribe nombre o codigo" autocomplete="off">
              @if(filled($empleadoSearch))
                <div
                  class="absolute z-20 mt-2 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                  @forelse($empleadosFormulario as $empleado)
                    <button type="button" wire:click="seleccionarEmpleado({{ $empleado->id }})"
                      class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                      <span class="font-semibold text-slate-900">{{ $empleado->nombre_completo }}</span>
                      <span class="block text-xs text-slate-500">{{ $empleado->codigo_biometrico ?: 'Sin codigo' }} |
                        {{ $empleado->sucursal ?: 'Sin sucursal' }}</span>
                    </button>
                  @empty
                    <p class="px-3 py-2 text-sm text-slate-500">No se encontro personal con ese nombre o codigo.</p>
                  @endforelse
                </div>
              @endif
            </div>
            <input type="hidden" wire:model="empleadoId">
            @if(filled($empleadoSearch) && $empleadosFormulario->isEmpty())
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            @endif
            @error('empleadoId')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="tipo" class="form-input">
              @foreach($tipos as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">Alcance</label>
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
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select wire:model="estado" class="form-input">
              <option value="aprobado">Aprobado</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div>
            <label class="form-label">Fecha inicio</label>
            <input type="date" wire:model.live="fechaInicio" class="form-input">
            @error('fechaInicio')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="fechaFin" class="form-input" @disabled($tipo === 'cumpleanos')>
            @error('fechaFin')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>

          @if($alcance === 'horas')
            <div class="md:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50/60 p-4 grid gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-900">⏰ Definir horario del
                  permiso</span>
                <p class="text-xs text-indigo-700">Ingresa la hora exacta de salida y retorno para contabilizar el tiempo
                  retenido.</p>
              </div>
              <div>
                <label class="form-label text-indigo-900">Hora inicial (Salida)</label>
                <input type="time" wire:model="horaInicio" class="form-input border-indigo-300 focus:border-indigo-500">
                @error('horaInicio')
                <p class="form-error">{{ $message }}</p> @enderror
              </div>
              <div>
                <label class="form-label text-indigo-900">Hora final (Retorno)</label>
                <input type="time" wire:model="horaFin" class="form-input border-indigo-300 focus:border-indigo-500">
                @error('horaFin')
                <p class="form-error">{{ $message }}</p> @enderror
              </div>
            </div>
          @endif

          <div class="md:col-span-2">
            <label class="form-label">Motivo / detalle</label>
            <textarea wire:model="motivo" rows="3" class="form-input"
              placeholder="Ej. Permiso médico, trámite personal, asunto familiar o comisión laboral"></textarea>
          </div>
          @if($tipo === 'permiso')
            <div class="md:col-span-2">
              <label class="form-label">Tipo de permiso</label>
              <select wire:model.live="tipoPermiso" class="form-input">
                <option value="">Selecciona un tipo de permiso</option>
                @foreach($tiposPermiso as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @endif
          <div class="md:col-span-2">
            <p class="section-copy-sm">
              Selecciona <strong>Por días</strong> para ausencias de jornada completa o varios días consecutivos (ej.
              bajas médicas o licencias), o <strong>Por horas</strong> especificando la hora de salida y retorno.
            </p>
          </div>
          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar incidencia</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner"
          aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Ajuste operativo</p>
            <h3 class="section-title app-modal-title">Editar incidencia</h3>
            <p class="section-copy-sm">Actualiza el bloque de tiempo o el tipo de incidencia segun corresponda.</p>
          </div>
        </div>

        <form wire:submit="updateIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Personal</label>
            <div class="relative">
              <input type="search" wire:model.live.debounce.300ms="editEmpleadoSearch" class="form-input"
                placeholder="Escribe nombre o codigo" autocomplete="off">
              @if(filled($editEmpleadoSearch))
                <div
                  class="absolute z-20 mt-2 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                  @forelse($empleadosEdicion as $empleado)
                    <button type="button" wire:click="seleccionarEmpleadoEdicion({{ $empleado->id }})"
                      class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50">
                      <span class="font-semibold text-slate-900">{{ $empleado->nombre_completo }}</span>
                      <span class="block text-xs text-slate-500">{{ $empleado->codigo_biometrico ?: 'Sin codigo' }} |
                        {{ $empleado->sucursal ?: 'Sin sucursal' }}</span>
                    </button>
                  @empty
                    <p class="px-3 py-2 text-sm text-slate-500">No se encontro personal con ese nombre o codigo.</p>
                  @endforelse
                </div>
              @endif
            </div>
            <input type="hidden" wire:model="editEmpleadoId">
            @if(filled($editEmpleadoSearch) && $empleadosEdicion->isEmpty())
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            @endif
            @error('editEmpleadoId')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="editTipo" class="form-input">
              @foreach($tipos as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">Alcance</label>
            <select wire:model.live="editAlcance" class="form-input">
              @if($editTipo === 'cumpleanos')
                @foreach($this->alcancesCumpleanosDisponibles() as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              @else
                @foreach($this->alcancesDisponibles() as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select wire:model="editEstado" class="form-input">
              <option value="aprobado">Aprobado</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div>
            <label class="form-label">Fecha inicio</label>
            <input type="date" wire:model.live="editFechaInicio" class="form-input">
            @error('editFechaInicio')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="editFechaFin" class="form-input" @disabled($editTipo === 'cumpleanos')>
            @error('editFechaFin')
            <p class="form-error">{{ $message }}</p> @enderror
          </div>

          @if($editAlcance === 'horas')
            <div class="md:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 grid gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-900">⏰ Definir horario del
                  permiso</span>
                <p class="text-xs text-indigo-700">Ingresa la hora exacta de salida y retorno para contabilizar el tiempo
                  retenido.</p>
              </div>
              <div>
                <label class="form-label text-indigo-900">Hora inicial (Salida)</label>
                <input type="time" wire:model="editHoraInicio" class="form-input border-indigo-300 focus:border-indigo-500">
                @error('editHoraInicio')
                <p class="form-error">{{ $message }}</p> @enderror
              </div>
              <div>
                <label class="form-label text-indigo-900">Hora final (Retorno)</label>
                <input type="time" wire:model="editHoraFin" class="form-input border-indigo-300 focus:border-indigo-500">
                @error('editHoraFin')
                <p class="form-error">{{ $message }}</p> @enderror
              </div>
            </div>
          @endif
          <div class="md:col-span-2">
            <label class="form-label">Motivo / detalle</label>
            <textarea wire:model="editMotivo" rows="3" class="form-input"></textarea>
          </div>
          @if($editTipo === 'permiso')
            <div class="md:col-span-2">
              <label class="form-label">Tipo de permiso</label>
              <select wire:model.live="editTipoPermiso" class="form-input">
                <option value="">Selecciona un tipo de permiso</option>
                @foreach($tiposPermiso as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @endif
          @php
            $incidenciaEditActual = $editingIncidenciaId ? \App\Models\PermisoLaboral::with('comprobantePrincipal')->find($editingIncidenciaId) : null;
          @endphp
          @if ($incidenciaEditActual?->comprobantePrincipal)
            <div class="md:col-span-2 p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg overflow-hidden border border-indigo-200 bg-white shrink-0">
                  <img src="{{ $incidenciaEditActual->comprobantePrincipal->url }}" alt="Comprobante" class="w-full h-full object-cover">
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-800">{{ $incidenciaEditActual->comprobantePrincipal->nombre_original }}</p>
                  <p class="text-[11px] text-indigo-700 font-semibold">Comprobante / respaldo fotográfico adjunto</p>
                </div>
              </div>
              <button type="button" wire:click="verComprobante({{ $incidenciaEditActual->id }})" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-200 shadow-2xs cursor-pointer">
                Ampliar foto
              </button>
            </div>
          @endif

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Control de novedades</p>
        <h3 class="section-title">Incidencias, permisos y faltas</h3>
        <p class="section-copy-sm">Programa permisos por horas, incidencias por manana o tarde, cumpleanos por la manana
          o la tarde, y faltas con tiempo contabilizado.</p>
      </div>
      <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar incidencia</button>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-3">
        <div class="space-y-2">
          <label class="form-label">Buscar por nombre o codigo</label>
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
            <th>Boleta Oficial</th>
            <th>Estado</th>
            <th>Detalle</th>
            <th class="text-center" style="min-width: 130px;">Acciones</th>
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
                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
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

              {{-- Boleta Oficial PDF --}}
              <td>
                <button
                  type="button"
                  wire:click="descargarBoletaPdf({{ $item->id }})"
                  wire:loading.attr="disabled"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-800 font-extrabold text-xs shadow-2xs transition cursor-pointer"
                  title="Descargar Boleta Oficial en PDF"
                >
                  <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                  <span>PDF Boleta</span>
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

              <td class="max-w-[200px] truncate text-xs text-slate-600" title="{{ $item->motivo }}">
                {{ $item->motivo ?: 'Sin detalle adicional' }}
              </td>

              {{-- Acciones: 3 Íconos (Ojito, Check, Cruz) --}}
              <td class="table-actions-cell">
                <div class="flex items-center justify-center gap-1.5">
                  {{-- 1. Ojito (Solo Ver Comprobante) --}}
                  <button
                    type="button"
                    wire:click="verComprobante({{ $item->id }})"
                    class="h-8 w-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 flex items-center justify-center transition shadow-2xs cursor-pointer border border-indigo-200 hover:scale-105 active:scale-95"
                    title="Ver comprobante fotográfico"
                  >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>

                  {{-- 2. Check (Aprobar) con Popup y Bloqueo --}}
                  @if ($item->estado === 'pendiente')
                    <button
                      type="button"
                      wire:click="abrirConfirmacion({{ $item->id }}, 'aprobado')"
                      class="h-8 w-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 cursor-pointer border border-emerald-200 flex items-center justify-center transition shadow-2xs hover:scale-105 active:scale-95"
                      title="Aprobar solicitud de {{ $item->empleado?->nombre_completo ?? 'personal' }}"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </button>
                  @else
                    <button
                      type="button"
                      disabled
                      class="h-8 w-8 rounded-lg {{ $item->estado === 'aprobado' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-300 border border-slate-200' }} flex items-center justify-center cursor-not-allowed opacity-60 shadow-none"
                      title="Esta solicitud ya fue {{ $item->estado }} y no se puede volver a modificar"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </button>
                  @endif

                  {{-- 3. Cruz (Rechazar) con Popup y Bloqueo --}}
                  @if ($item->estado === 'pendiente')
                    <button
                      type="button"
                      wire:click="abrirConfirmacion({{ $item->id }}, 'rechazado')"
                      class="h-8 w-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 cursor-pointer border border-rose-200 flex items-center justify-center transition shadow-2xs hover:scale-105 active:scale-95"
                      title="Rechazar solicitud de {{ $item->empleado?->nombre_completo ?? 'personal' }}"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                      </svg>
                    </button>
                  @else
                    <button
                      type="button"
                      disabled
                      class="h-8 w-8 rounded-lg {{ $item->estado === 'rechazado' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-300 border border-slate-200' }} flex items-center justify-center cursor-not-allowed opacity-60 shadow-none"
                      title="Esta solicitud ya fue {{ $item->estado }} y no se puede volver a modificar"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                      </svg>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-slate-400 py-6">No hay incidencias registradas para el filtro actual.</td>
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
              <p style="font-size: 0.78rem; color: #94a3b8; margin: 0.35rem 0 0 0;">(Solo las solicitudes enviadas con fotografía adjunta disponen de imagen en este visor).</p>
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

  {{-- POPUP MODAL DE CONFIRMACIÓN PARA APROBAR / RECHAZAR --}}
  @if ($showConfirmModal)
    <div class="app-modal-backdrop" wire:click="cancelarConfirmacion" style="position:fixed;inset:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(6px);z-index:999999;display:flex;align-items:center;justify-content:center;padding:1.25rem;">
      <div class="app-modal-card" x-on:click.stop style="background:#fff;border-radius:1.5rem;max-width:28rem;width:100%;padding:2rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);border:1px solid #e2e8f0;text-align:center;">
        
        @if ($confirmandoNuevoEstado === 'aprobado')
          <div style="width:4.5rem;height:4.5rem;border-radius:50%;background:#ecfdf5;border:2px solid #a7f3d0;color:#059669;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem auto;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3 style="font-size:1.25rem;font-weight:900;color:#065f46;margin:0 0 .5rem 0;">¿Aprobar esta solicitud?</h3>
        @else
          <div style="width:4.5rem;height:4.5rem;border-radius:50%;background:#fff1f2;border:2px solid #fecdd3;color:#e11d48;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem auto;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          <h3 style="font-size:1.25rem;font-weight:900;color:#9f1239;margin:0 0 .5rem 0;">¿Rechazar esta solicitud?</h3>
        @endif

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1rem;margin:1.25rem 0;text-align:left;">
          <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:800;margin:0 0 .25rem 0;">Funcionario</p>
          <p style="font-size:.95rem;font-weight:800;color:#0f172a;margin:0 0 .6rem 0;">{{ $confirmandoEmpleadoNombre }}</p>
          <p style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:800;margin:0 0 .25rem 0;">Detalle de la Solicitud</p>
          <p style="font-size:.82rem;font-weight:600;color:#334155;margin:0;">{{ $confirmandoDetalle }}</p>
        </div>

        <p style="font-size:.78rem;color:#64748b;margin:0 0 1.5rem 0;line-height:1.45;">
          ⚠️ <strong>Aviso importante:</strong> Una vez confirmada como <span style="font-weight:900;text-transform:uppercase;color:{{ $confirmandoNuevoEstado === 'aprobado' ? '#059669' : '#e11d48' }};">{{ $confirmandoNuevoEstado }}</span>, la decisión quedará registrada de forma definitiva y ya no se podrá modificar.
        </p>

        <div style="display:flex;gap:.75rem;justify-content:center;">
          <button type="button" wire:click="cancelarConfirmacion" style="flex:1;padding:.75rem 1rem;border-radius:.75rem;border:1.5px solid #cbd5e1;background:#fff;color:#475569;font-weight:700;font-size:.85rem;cursor:pointer;">
            Cancelar
          </button>
          @if ($confirmandoNuevoEstado === 'aprobado')
            <button type="button" wire:click="confirmarAccion" style="flex:1;padding:.75rem 1rem;border-radius:.75rem;border:none;background:#059669;color:#fff;font-weight:800;font-size:.85rem;cursor:pointer;box-shadow:0 4px 12px rgba(5,150,105,0.35);">
              ✓ Sí, Aprobar
            </button>
          @else
            <button type="button" wire:click="confirmarAccion" style="flex:1;padding:.75rem 1rem;border-radius:.75rem;border:none;background:#e11d48;color:#fff;font-weight:800;font-size:.85rem;cursor:pointer;box-shadow:0 4px 12px rgba(225,29,72,0.35);">
              ✕ Sí, Rechazar
            </button>
          @endif
        </div>
      </div>
    </div>
  @endif
</div>