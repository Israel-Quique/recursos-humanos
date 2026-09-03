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

  {{-- MODAL ELIMINAR MARCACIÓN ESPECIAL --}}
  @if ($showDeleteRegistroModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteRegistroModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmación</p>
            <h3 class="section-title app-modal-title">Eliminar marcación especial</h3>
            <p class="section-copy-sm">¿Seguro que deseas eliminar este registro de asistencia? Esta acción quedará registrada en auditoría.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteRegistroLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteRegistroModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteRegistro" class="table-action-button table-action-button-danger">Sí, eliminar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- MODAL REGISTRAR / EDITAR MARCACIÓN ESPECIAL --}}
  @if ($showRegistroModal)
    <div class="app-modal-backdrop" wire:click="closeRegistroModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeRegistroModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Operación manual RRHH</p>
            <h3 class="section-title app-modal-title">{{ $editingRegistroId ? 'Editar marcación especial' : 'Registrar entrada y salida especial' }}</h3>
            <p class="section-copy-sm">Ingresa la fecha, hora de entrada y hora de salida. Estos datos se sincronizarán directamente con los reportes del sistema.</p>
          </div>
        </div>

        <form wire:submit="saveRegistro" class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label font-semibold text-slate-800">Personal especial <span class="text-rose-500">*</span></label>
            <select wire:model="empleadoId" class="form-input" {{ $editingRegistroId ? 'disabled' : '' }}>
              <option value="">Selecciona el personal</option>
              @foreach($empleadosEspecialesList as $emp)
                <option value="{{ $emp->id }}">
                  {{ $emp->nombre_completo }} — {{ $emp->sucursal ?: 'Sin sucursal' }} ({{ $emp->codigo_biometrico ?: 'Sin biométrico' }})
                </option>
              @endforeach
            </select>
            @error('empleadoId') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Fecha de asistencia <span class="text-rose-500">*</span></label>
            <input type="date" wire:model="fecha" class="form-input">
            @error('fecha') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Hora de entrada <span class="text-rose-500">*</span></label>
            <input type="time" wire:model="horaEntrada" class="form-input font-mono">
            @error('horaEntrada') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Hora de salida</label>
            <input type="time" wire:model="horaSalida" class="form-input font-mono">
            <span class="text-[11px] text-slate-400">Opcional si la persona sigue en jornada.</span>
            @error('horaSalida') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="form-label font-semibold text-slate-800">Observación / Justificación</label>
            <input type="text" wire:model="observacion" class="form-input" placeholder="Ej. Entrada y salida autorizada por RRHH - Comisión especial">
            @error('observacion') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 mt-4 app-modal-actions">
            <button type="button" wire:click="closeRegistroModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">
              {{ $editingRegistroId ? 'Guardar cambios' : 'Registrar marcación' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL CREAR NUEVO PERSONAL ESPECIAL --}}
  @if ($showCreateEmpleadoModal)
    <div class="app-modal-backdrop" wire:click="closeCreateEmpleadoModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeCreateEmpleadoModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Alta de personal</p>
            <h3 class="section-title app-modal-title">Registrar nuevo personal especial</h3>
            <p class="section-copy-sm">Crea una ficha para quien no marca regularmente en biométricos o tiene horario especial.</p>
          </div>
        </div>

        <form wire:submit="saveNuevoEmpleadoEspecial" class="mt-6 grid gap-4 md:grid-cols-2">
          <div>
            <label class="form-label font-semibold text-slate-800">Nombre <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="nuevoNombre" class="form-input" placeholder="Nombres">
            @error('nuevoNombre') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Apellido <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="nuevoApellido" class="form-input" placeholder="Apellidos">
            @error('nuevoApellido') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Código / CI (Opcional)</label>
            <input type="text" wire:model="nuevoCodigoBiometrico" class="form-input" placeholder="Ej. 6543210 (opcional)">
            <span class="text-[11px] text-slate-400">Puede dejarse en blanco si no tiene biométrico.</span>
            @error('nuevoCodigoBiometrico') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Área / Departamento <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="nuevaArea" class="form-input" placeholder="Ej. Gerencia, Operaciones, Chofer">
            @error('nuevaArea') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Sucursal <span class="text-rose-500">*</span></label>
            <select wire:model="nuevaSucursal" class="form-input">
              <option value="">Selecciona sucursal</option>
              @foreach($sucursales as $sucursalOption)
                @if($sucursalOption !== 'TODAS')
                  <option value="{{ $sucursalOption }}">{{ $sucursalOption }}</option>
                @endif
              @endforeach
            </select>
            @error('nuevaSucursal') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label font-semibold text-slate-800">Horario de referencia (Entrada / Salida)</label>
            <div class="grid grid-cols-2 gap-2">
              <input type="time" wire:model="nuevaHoraEntrada" class="form-input font-mono" title="Entrada referencial">
              <input type="time" wire:model="nuevaHoraSalida" class="form-input font-mono" title="Salida referencial">
            </div>
          </div>

          <div class="md:col-span-2 mt-4 app-modal-actions">
            <button type="button" wire:click="closeCreateEmpleadoModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Crear personal especial</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- MODAL VINCULAR PERSONAL EXISTENTE COMO ESPECIAL --}}
  @if ($showVincularModal)
    <div class="app-modal-backdrop" wire:click="closeVincularModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeVincularModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Designación de régimen</p>
            <h3 class="section-title app-modal-title">Designar personal existente como especial</h3>
            <p class="section-copy-sm">Permite que un empleado ya registrado sea gestionado bajo el régimen de asistencia manual especial.</p>
          </div>
        </div>

        <form wire:submit="vincularEmpleadoComoEspecial" class="mt-6 space-y-4">
          <div>
            <label class="form-label font-semibold text-slate-800">Seleccionar empleado</label>
            <select wire:model="vincularEmpleadoId" class="form-input">
              <option value="">Selecciona un empleado existente...</option>
              @foreach($empleadosParaVincular as $emp)
                <option value="{{ $emp->id }}">
                  {{ $emp->nombre_completo }} ({{ $emp->sucursal }} — {{ $emp->area }})
                </option>
              @endforeach
            </select>
            @error('vincularEmpleadoId') <p class="form-error text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="mt-4 app-modal-actions">
            <button type="button" wire:click="closeVincularModal" class="app-modal-secondary">Cancelar</button>
            <button type="submit" class="login-submit app-modal-submit">Designar como especial</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- TARJETAS DE RESUMEN SUPERIORES --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-2">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Personal Especial</p>
        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $totalEspeciales }}</h4>
        <span class="text-xs text-indigo-600 font-medium">Bajo régimen manual</span>
      </div>
      <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl font-bold">
        👥
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Marcaciones en Período</p>
        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $registros->total() }}</h4>
        <span class="text-xs text-emerald-600 font-medium">Registros listados</span>
      </div>
      <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl font-bold">
        📅
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Registros de Hoy</p>
        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $registrosHoyCount }}</h4>
        <span class="text-xs text-amber-600 font-medium">{{ now()->translatedFormat('d F Y') }}</span>
      </div>
      <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl font-bold">
        ⏱️
      </div>
    </div>
  </div>

  {{-- SECCIÓN PRINCIPAL --}}
  <section class="surface-card">
    <div class="section-head-row flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <p class="section-kicker">Operación Laboral</p>
        <h3 class="section-title">Personal Especial y Asistencia Manual</h3>
        <p class="section-copy-sm">Gestión de personal que no marca en biométricos (La Paz, El Alto u otros) con registro directo de hora de entrada y salida.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button type="button" wire:click="openRegistroModal" class="section-action-button flex items-center gap-2">
          <span>+</span>
          <span>Registrar Entrada / Salida</span>
        </button>
        <button type="button" wire:click="openCreateEmpleadoModal" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors shadow-xs">
          + Nuevo Personal Especial
        </button>
        <button type="button" wire:click="openVincularModal" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-xs">
          Vincular Existente
        </button>
      </div>
    </div>

    {{-- BARRA DE PESTAÑAS --}}
    <div class="px-6 pt-4 border-b border-slate-100 flex gap-6">
      <button type="button" wire:click="setTab('marcaciones')"
        class="pb-3 text-sm font-bold transition-colors border-b-2 {{ $tab === 'marcaciones' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        🕒 Marcaciones y Asistencias Especiales ({{ $registros->total() }})
      </button>
      <button type="button" wire:click="setTab('personal')"
        class="pb-3 text-sm font-bold transition-colors border-b-2 {{ $tab === 'personal' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        📋 Directorio de Personal Especial ({{ $personalEspecial->total() }})
      </button>
    </div>

    {{-- FILTROS --}}
    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-5 grid gap-3 px-6 pt-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative">
          <label class="form-label text-xs">Buscar personal (Nombre o Carnet)</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Escribe nombre o carnet/CI...">
          
          {{-- Sugerencias de vinculación directa si el personal aún no es especial --}}
          @if($candidatosVincular->isNotEmpty())
            <div class="absolute left-0 right-0 z-30 mt-1 max-h-60 overflow-y-auto rounded-xl border border-indigo-200 bg-white p-2 shadow-xl">
              <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-indigo-700 flex items-center justify-between border-b border-indigo-50 mb-1">
                <span>Personal encontrado (Sin régimen especial)</span>
                <span class="text-[10px] text-slate-400">Clic para vincular</span>
              </div>
              @foreach($candidatosVincular as $cand)
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-indigo-50/70 transition-colors gap-2">
                  <div class="truncate">
                    <strong class="text-xs font-bold text-slate-900 block truncate">{{ $cand->nombre_completo }}</strong>
                    <span class="text-[11px] text-slate-500 font-mono block truncate">CI: {{ $cand->codigo_biometrico ?: 'Sin carnet' }} · {{ $cand->sucursal ?: 'Sin sucursal' }}</span>
                  </div>
                  <button type="button" wire:click="vincularDirecto({{ $cand->id }})" class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-1 px-2.5 rounded-lg shadow-xs transition-colors">
                    + Vincular
                  </button>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <div>
          <label class="form-label text-xs">Sucursal</label>
          <select wire:model.live="sucursalFiltro" class="form-input">
            <option value="">Todas las sucursales</option>
            @foreach($sucursales as $suc)
              @if($suc !== 'TODAS')
                <option value="{{ $suc }}">{{ $suc }}</option>
              @endif
            @endforeach
          </select>
        </div>

        <div>
          <label class="form-label text-xs">Tipo de filtro de fecha</label>
          <select wire:model.live="tipoRangoFiltro" class="form-input">
            <option value="dia">Por día</option>
            <option value="rango">Por rango de fechas</option>
          </select>
        </div>

        @if($tipoRangoFiltro === 'dia')
          <div>
            <label class="form-label text-xs">Fecha específica</label>
            <input type="date" wire:model.live="fechaDiaFiltro" class="form-input">
          </div>
        @else
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="form-label text-xs">Desde</label>
              <input type="date" wire:model.live="fechaInicioFiltro" class="form-input">
            </div>
            <div>
              <label class="form-label text-xs">Hasta</label>
              <input type="date" wire:model.live="fechaFinFiltro" class="form-input">
            </div>
          </div>
        @endif
      </div>

      {{-- BANNER DE VINCULACIÓN DIRECTA SI HAY COINCIDENCIAS NO ESPECIALES --}}
      @if($candidatosVincular->isNotEmpty())
        <div class="mx-6 mb-4 rounded-xl border border-indigo-200 bg-indigo-50/90 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
          <div class="flex items-center gap-2.5">
            <span class="text-xl">⭐</span>
            <div>
              <p class="text-xs font-bold text-indigo-950">Se encontró personal que coincide con tu búsqueda pero aún no tiene régimen especial:</p>
              <p class="text-[11px] text-indigo-700">Puedes vincularlo directamente con un clic para registrar sus entradas y salidas:</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            @foreach($candidatosVincular as $cand)
              <button type="button" wire:click="vincularDirecto({{ $cand->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors">
                <span>+ Vincular:</span>
                <span class="underline">{{ $cand->nombre_completo }}</span>
                <span class="font-mono text-[10px] opacity-90">({{ $cand->codigo_biometrico ?: 'Sin CI' }})</span>
              </button>
            @endforeach
          </div>
        </div>
      @endif

      {{-- PESTAÑA 1: MARCACIONES ESPECIALES --}}
      @if ($tab === 'marcaciones')
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal Especial</th>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Tipo / Estado</th>
              <th class="text-center" style="min-width: 140px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($registros as $reg)
              <tr wire:key="registro-row-{{ $reg->id }}" class="hover:bg-indigo-50/30 transition-colors">
                <td>
                  <strong class="font-bold text-slate-900 block">{{ $reg->empleado?->nombre_completo ?? 'Sin empleado' }}</strong>
                  <span class="text-[11px] text-slate-400 font-mono">
                    CI: {{ $reg->empleado?->codigo_biometrico ?: 'Sin biométrico' }} · {{ $reg->empleado?->sucursal ?? 'Sin sucursal' }} ({{ $reg->empleado?->area ?? 'General' }})
                  </span>
                </td>
                <td>
                  <div class="font-bold text-xs text-slate-800">
                    {{ $reg->fecha?->format('d/m/Y') ?? '--/--/----' }}
                  </div>
                  <span class="text-[11px] text-slate-400 font-medium">
                    {{ $reg->fecha ? ucfirst($reg->fecha->locale('es')->isoFormat('dddd')) : '' }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $reg->hora_entrada ? substr($reg->hora_entrada, 0, 5) : '--:--' }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold {{ $reg->hora_salida ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                    {{ $reg->hora_salida ? substr($reg->hora_salida, 0, 5) : '--:--' }}
                  </span>
                </td>
                <td>
                  <span class="inline-block px-2.5 py-1 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    {{ $reg->tipo_verificacion ?: 'Especial' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button type="button" wire:click="openRegistroModal({{ $reg->id }})" class="table-action-button" title="Editar horario">
                      Editar
                    </button>
                    <button type="button" wire:click="openDeleteRegistroModal({{ $reg->id }})" class="table-action-button table-action-button-danger" title="Eliminar registro">
                      Eliminar
                    </button>
                    <a wire:navigate href="{{ route('reportes') }}" class="table-action-button text-[11px] text-slate-500 hover:text-indigo-600" title="Ver en módulo de reportes">
                      Reportes
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-10 text-slate-400">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-3xl mb-2">📋</span>
                    <p class="font-bold text-slate-700">No hay registros especiales para el período seleccionado.</p>
                    <p class="text-xs text-slate-400 mt-1">Usa el botón "Registrar Entrada / Salida" para agregar asistencias manuales.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="px-6 py-4 border-t border-slate-100">
          {{ $registros->links() }}
        </div>

      {{-- PESTAÑA 2: DIRECTORIO DE PERSONAL ESPECIAL --}}
      @else
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Área</th>
              <th>Código / CI</th>
              <th>Horario de Referencia</th>
              <th>Asistencias en Período</th>
              <th>Régimen</th>
              <th class="text-center" style="min-width: 150px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($personalEspecial as $pe)
              <tr wire:key="pe-row-{{ $pe->id }}" class="hover:bg-indigo-50/30 transition-colors">
                <td>
                  <strong class="font-bold text-slate-900 block">{{ $pe->nombre_completo }}</strong>
                  <span class="text-[11px] text-slate-400 font-medium">Contratado: {{ $pe->fecha_contratacion?->format('d/m/Y') ?? 'S/F' }}</span>
                </td>
                <td>
                  <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                    {{ $pe->sucursal ?: 'Sin sucursal' }}
                  </span>
                </td>
                <td>
                  <span class="text-xs text-slate-700 font-medium">{{ $pe->area ?: 'General' }}</span>
                </td>
                <td>
                  <span class="font-mono text-xs text-slate-700">{{ $pe->codigo_biometrico ?: 'Sin biométrico' }}</span>
                </td>
                <td>
                  <span class="font-mono text-xs text-slate-600">
                    {{ $pe->hora_entrada_programada ? substr($pe->hora_entrada_programada, 0, 5) : '--:--' }} - 
                    {{ $pe->hora_salida_programada ? substr($pe->hora_salida_programada, 0, 5) : '--:--' }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                    {{ $pe->asistencias_count }} asistencias
                  </span>
                </td>
                <td>
                  <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                    ⭐ Régimen Especial
                  </span>
                </td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button type="button" wire:click="openRegistroModal(null, {{ $pe->id }})" class="table-action-button text-indigo-600 font-bold" title="Añadir marcación a este empleado">
                      + Marcación
                    </button>
                    <button type="button" wire:click="toggleEspecial({{ $pe->id }})" wire:confirm="¿Deseas remover el régimen especial para {{ $pe->nombre_completo }}?" class="table-action-button text-slate-500 hover:text-rose-600" title="Quitar régimen especial">
                      Quitar
                    </button>
                    <a wire:navigate href="{{ route('personal', ['vista' => 'marcaciones']) }}" class="table-action-button text-[11px] text-slate-500 hover:text-indigo-600">
                      Historial
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-10 text-slate-400">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-3xl mb-2">👥</span>
                    <p class="font-bold text-slate-700">No se encontró personal especial registrado.</p>
                    <p class="text-xs text-slate-400 mt-1">Usa "+ Nuevo Personal Especial" o "Vincular Existente" para designar personal especial.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="px-6 py-4 border-t border-slate-100">
          {{ $personalEspecial->links() }}
        </div>
      @endif
    </div>
  </section>
</div>
