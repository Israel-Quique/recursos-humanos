<div class="page-stack page-stack-calendar">
  {{-- Modal Eliminar --}}
  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmación</p>
            <h3 class="section-title app-modal-title">Eliminar fecha especial</h3>
            <p class="section-copy-sm">¿Seguro que quieres eliminar esta fecha especial? Esta acción quedará registrada en auditoría.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteFechaLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteFechaEspecial" class="table-action-button table-action-button-danger">Sí, eliminar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal Crear --}}
  @if ($showCreateModal)
    <div class="app-modal-backdrop" wire:click="closeCreateModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Programación especial</p>
            <h3 class="section-title app-modal-title">Registrar feriado, paro u horario especial</h3>
            <p class="section-copy-sm">Configura días no laborables globales o por sucursal, y jornadas con horario ajustado.</p>
          </div>
        </div>

        {{-- Formulario Crear --}}
        <form wire:submit="saveFechaEspecial" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Tipo de jornada</label>
            <select wire:model.live="tipo" class="form-input">
              <option value="feriado">Feriado (No laborable)</option>
              <option value="paro">Paro / Huelga (No laborable)</option>
              <option value="horario_especial">Horario especial (Jornada ajustada)</option>
            </select>
            @error('tipo') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Sucursal afectada</label>
            <select wire:model="sucursal" class="form-input">
              @foreach($sucursales as $item)
                <option value="{{ $item }}">{{ $item === 'TODAS' ? 'Todas las sucursales' : $item }}</option>
              @endforeach
            </select>
            @error('sucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Fecha Inicio</label>
            <input type="date" wire:model.live="fecha" class="form-input">
            @error('fecha') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label {{ in_array($tipo, ['paro', 'horario_especial']) ? 'text-slate-400' : '' }}">
              Fecha Fin
              @if(in_array($tipo, ['paro', 'horario_especial']))
                <span class="text-xs text-slate-400 font-medium ml-1">(Un solo día)</span>
              @else
                <span class="text-xs text-slate-400 font-normal">(Opcional para rango)</span>
              @endif
            </label>
            <input type="date" wire:model="fechaFin" 
              class="form-input {{ in_array($tipo, ['paro', 'horario_especial']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($tipo, ['paro', 'horario_especial']))>
            @error('fechaFin') <p class="form-error">{{ $message }}</p> @enderror
          </div>



          {{-- Horarios (Bloqueados y Oscurecidos si es feriado o paro) --}}
          <div class="{{ in_array($tipo, ['feriado', 'paro']) ? 'opacity-50' : '' }}">
            <label class="form-label {{ in_array($tipo, ['feriado', 'paro']) ? 'text-slate-400' : '' }}">
              Hora de entrada especial
              @if(in_array($tipo, ['feriado', 'paro']))
                <span class="text-xs text-rose-500 font-medium ml-1">(No aplicable)</span>
              @endif
            </label>
            <input type="time" wire:model.live="horaEntrada" 
              class="form-input {{ in_array($tipo, ['feriado', 'paro']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($tipo, ['feriado', 'paro']))>
            @error('horaEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="{{ in_array($tipo, ['feriado', 'paro']) ? 'opacity-50' : '' }}">
            <label class="form-label {{ in_array($tipo, ['feriado', 'paro']) ? 'text-slate-400' : '' }}">
              Hora de salida especial
              @if(in_array($tipo, ['feriado', 'paro']))
                <span class="text-xs text-rose-500 font-medium ml-1">(No aplicable)</span>
              @endif
            </label>
            <input type="time" wire:model.live="horaSalida" 
              class="form-input {{ in_array($tipo, ['feriado', 'paro']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($tipo, ['feriado', 'paro']))>
            @error('horaSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            @if (in_array($tipo, ['feriado', 'paro']))
              <div class="p-3 rounded-lg border border-slate-300 bg-slate-100 text-xs text-slate-500 flex items-center gap-2">
                <span class="text-base">🔒</span>
                <span><strong>Día no laborable:</strong> En feriados o paros los horarios de entrada y salida están deshabilitados porque no se trabaja.</span>
              </div>
            @else
              <div class="p-3 rounded-lg border border-amber-200 bg-amber-50 text-xs text-amber-800 flex items-center gap-2">
                <span class="text-base">⏰</span>
                <span><strong>Horario especial:</strong> Define las horas exactas de entrada y salida para esta jornada reducida o modificada.</span>
              </div>
            @endif
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Nombre del feriado / evento</label>
            <input type="text" wire:model="nombre" class="form-input" placeholder="Ej. Día de la Independencia, Paro cívico, Jornada continua">
            @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>


          <div class="md:col-span-2">
            <label class="form-label">Descripción o detalle</label>
            <textarea wire:model="descripcion" rows="2" class="form-input" placeholder="Opcional: Decreto o motivo institucional"></textarea>
            @error('descripcion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar programación</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- Modal Editar --}}
  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Editar programación</p>
            <h3 class="section-title app-modal-title">Actualizar fecha especial</h3>
            <p class="section-copy-sm">Ajusta los detalles de la fecha seleccionada.</p>
          </div>
        </div>

        <form wire:submit="updateFechaEspecial" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Tipo de jornada</label>
            <select wire:model.live="editTipo" class="form-input">
              <option value="feriado">Feriado (No laborable)</option>
              <option value="paro">Paro / Huelga (No laborable)</option>
              <option value="horario_especial">Horario especial (Jornada ajustada)</option>
            </select>
            @error('editTipo') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Sucursal afectada</label>
            <select wire:model="editSucursal" class="form-input">
              @foreach($sucursales as $item)
                <option value="{{ $item }}">{{ $item === 'TODAS' ? 'Todas las sucursales' : $item }}</option>
              @endforeach
            </select>
            @error('editSucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label">Fecha Inicio</label>
            <input type="date" wire:model.live="editFecha" class="form-input">
            @error('editFecha') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label {{ in_array($editTipo, ['paro', 'horario_especial']) ? 'text-slate-400' : '' }}">
              Fecha Fin
              @if(in_array($editTipo, ['paro', 'horario_especial']))
                <span class="text-xs text-slate-400 font-medium ml-1">(Un solo día)</span>
              @else
                <span class="text-xs text-slate-400 font-normal">(Opcional para rango)</span>
              @endif
            </label>
            <input type="date" wire:model="editFechaFin" 
              class="form-input {{ in_array($editTipo, ['paro', 'horario_especial']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($editTipo, ['paro', 'horario_especial']))>
            @error('editFechaFin') <p class="form-error">{{ $message }}</p> @enderror
          </div>



          <div class="{{ in_array($editTipo, ['feriado', 'paro']) ? 'opacity-50' : '' }}">
            <label class="form-label {{ in_array($editTipo, ['feriado', 'paro']) ? 'text-slate-400' : '' }}">
              Hora de entrada especial
              @if(in_array($editTipo, ['feriado', 'paro']))
                <span class="text-xs text-rose-500 font-medium ml-1">(No aplicable)</span>
              @endif
            </label>
            <input type="time" wire:model.live="editHoraEntrada" 
              class="form-input {{ in_array($editTipo, ['feriado', 'paro']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($editTipo, ['feriado', 'paro']))>
            @error('editHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="{{ in_array($editTipo, ['feriado', 'paro']) ? 'opacity-50' : '' }}">
            <label class="form-label {{ in_array($editTipo, ['feriado', 'paro']) ? 'text-slate-400' : '' }}">
              Hora de salida especial
              @if(in_array($editTipo, ['feriado', 'paro']))
                <span class="text-xs text-rose-500 font-medium ml-1">(No aplicable)</span>
              @endif
            </label>
            <input type="time" wire:model.live="editHoraSalida" 
              class="form-input {{ in_array($editTipo, ['feriado', 'paro']) ? '!bg-slate-200 !text-slate-400 !border-slate-300 cursor-not-allowed select-none' : '' }}" 
              @disabled(in_array($editTipo, ['feriado', 'paro']))>
            @error('editHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label">Nombre del feriado / evento</label>
            <input type="text" wire:model="editNombre" class="form-input">
            @error('editNombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>


          <div class="md:col-span-2">
            <label class="form-label">Descripción</label>
            <textarea wire:model="editDescripcion" rows="2" class="form-input"></textarea>
            @error('editDescripcion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif


  {{-- Vista Principal: Calendario Interactivo --}}
  <section class="surface-card surface-card-calendar">
    <div class="calendar-header">
      <div>
        <p class="section-kicker">Programación Laboral</p>
        <h3 class="section-title">Calendario de Fechas Especiales</h3>
        <p class="section-copy-sm">Selecciona cualquier día del calendario para registrar o gestionar feriados, paros u horarios especiales.</p>
      </div>

      <div class="calendar-controls">
        <button type="button" class="calendar-chip" wire:click="goToCurrentMonth">Hoy</button>
        <div class="calendar-month-box">
          <button type="button" class="calendar-arrow" wire:click="goToPreviousMonth" aria-label="Mes anterior">&lsaquo;</button>
          <span>{{ $monthLabel }}</span>
          <button type="button" class="calendar-arrow" wire:click="goToNextMonth" aria-label="Mes siguiente">&rsaquo;</button>
        </div>
        <button type="button" wire:click="openCreateModal('{{ $selectedDate }}')" class="section-action-button">
          + Agregar fecha
        </button>
      </div>
    </div>

    {{-- Leyenda del Calendario --}}
    <div class="calendar-legend">
      <div class="calendar-legend-item">
        <span class="inline-block h-3 w-3 rounded-full bg-purple-600"></span>
        <span>Feriado Nacional / Regional</span>
      </div>
      <div class="calendar-legend-item">
        <span class="inline-block h-3 w-3 rounded-full bg-rose-600"></span>
        <span>Paro / Huelga</span>
      </div>
      <div class="calendar-legend-item">
        <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span>
        <span>Horario Especial</span>
      </div>
    </div>

    <div class="calendar-layout">
      {{-- Grilla del Calendario --}}
      <div class="calendar-grid-main">
        <div class="calendar-shell">
          <div class="calendar-weekdays">
            @foreach($weekdays as $weekday)
              <div>{{ strtoupper($weekday) }}</div>
            @endforeach
          </div>

          <div class="calendar-weeks">
            @foreach($calendar as $week)
              <div class="calendar-week-row">
                @foreach($week as $day)
                  <article
                    wire:click="selectDay('{{ $day['date'] }}')"
                    class="calendar-day-card calendar-day-card-interactive 
                      {{ $day['is_current_month'] ? '' : 'calendar-day-card-muted' }} 
                      {{ $day['is_today'] ? 'calendar-day-card-today' : '' }} 
                      {{ $day['is_selected'] ? 'calendar-day-card-selected' : '' }}
                      {{ $day['is_holiday'] ? 'border-purple-300 bg-purple-50/50' : '' }}
                      {{ $day['is_paro'] ? 'border-rose-300 bg-rose-50/50' : '' }}
                      {{ $day['has_special'] && !$day['is_holiday'] && !$day['is_paro'] ? 'border-amber-300 bg-amber-50/40' : '' }}"
                  >
                    <div class="calendar-day-top">
                      <span class="calendar-day-number {{ $day['has_any'] ? 'font-bold' : '' }}">{{ $day['label'] }}</span>
                      
                      @if($day['is_holiday'])
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-purple-600" title="Feriado"></span>
                      @elseif($day['is_paro'])
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-600" title="Paro"></span>
                      @elseif($day['has_special'])
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-500" title="Horario Especial"></span>
                      @endif
                    </div>

                    <div class="calendar-day-events mt-auto">
                      @if($day['has_any'])
                        <span class="text-[10px] font-semibold text-slate-600 bg-white/80 px-1.5 py-0.5 rounded border border-slate-200">
                          {{ $day['items_count'] }} {{ $day['items_count'] === 1 ? 'evento' : 'eventos' }}
                        </span>
                      @endif
                    </div>
                  </article>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Panel Lateral del Día Seleccionado --}}
      <aside class="calendar-side-panel">
        <div class="calendar-side-head">
          <div>
            <p class="section-kicker">Día Seleccionado</p>
            <h4 class="section-title calendar-side-day-title">
              {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
            </h4>
            <p class="section-copy-sm capitalize">
              {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd') }}
            </p>
          </div>
        </div>

        <div class="calendar-side-body">
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Eventos programados</span>
            <button type="button" wire:click="openCreateModal('{{ $selectedDate }}')" class="text-xs font-semibold text-purple-600 hover:text-purple-800">
              + Agregar en este día
            </button>
          </div>

          @forelse($dayRecords as $rec)
            <div class="rounded-xl border p-3.5 mb-3 space-y-2
              {{ $rec['tipo'] === 'feriado' ? 'border-purple-200 bg-purple-50/70' : ($rec['tipo'] === 'paro' ? 'border-rose-200 bg-rose-50/70' : 'border-amber-200 bg-amber-50/70') }}">
              <div class="flex items-start justify-between">
                <div>
                  <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded
                    {{ $rec['tipo'] === 'feriado' ? 'bg-purple-200 text-purple-800' : ($rec['tipo'] === 'paro' ? 'bg-rose-200 text-rose-800' : 'bg-amber-200 text-amber-800') }}">
                    {{ $rec['tipo'] === 'feriado' ? 'Feriado' : ($rec['tipo'] === 'paro' ? 'Paro' : 'Horario Especial') }}
                  </span>
                  <h5 class="font-bold text-slate-800 text-sm mt-1">{{ $rec['nombre'] }}</h5>
                </div>
              </div>

              <div class="text-xs text-slate-600 space-y-1">
                <p>📍 <strong>Sucursal:</strong> {{ $rec['sucursal'] }}</p>
                @if(in_array($rec['tipo'], ['feriado', 'paro']))
                  <p>🔒 <strong>Jornada:</strong> No laborable</p>
                @else
                  <p>⏰ <strong>Horario:</strong> {{ $rec['hora_entrada'] ?: 'Normal' }} - {{ $rec['hora_salida'] ?: 'Normal' }}</p>
                @endif
                @if($rec['descripcion'])
                  <p class="italic text-slate-500">"{{ $rec['descripcion'] }}"</p>
                @endif
              </div>

              <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60">
                <button type="button" wire:click="openEditModal({{ $rec['id'] }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                  Editar
                </button>
                <span class="text-slate-300">|</span>
                <button type="button" wire:click="openDeleteModal({{ $rec['id'] }})" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                  Eliminar
                </button>
              </div>
            </div>
          @empty
            <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-400">
              <p class="text-xs">No hay feriados ni eventos registrados para este día.</p>
              <button type="button" wire:click="openCreateModal('{{ $selectedDate }}')" class="mt-3 inline-flex items-center text-xs font-bold text-purple-600 hover:text-purple-800">
                + Programar fecha especial
              </button>
            </div>
          @endforelse
        </div>
      </aside>
    </div>
  </section>
</div>
