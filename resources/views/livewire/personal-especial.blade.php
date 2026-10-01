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

  {{-- NUEVO MODAL: MARCAR ENTRADA Y SALIDA ESPECIAL (MENSUAL) --}}
  @if ($showModalEspecial)
    <div class="app-modal-backdrop" wire:click="closeModalEspecial">
      <div class="app-modal-card max-w-7xl w-full p-6 sm:p-8" x-on:click.stop style="max-height: 94vh; width: 96vw; display: flex; flex-direction: column;">
        <button type="button" wire:click="closeModalEspecial" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>
        
        {{-- CABECERA MODAL --}}
        <div class="app-modal-head mb-4 border-b border-slate-100 pb-3 flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-indigo-200">
              🕒
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="section-title app-modal-title text-lg sm:text-xl font-black text-slate-900">Control Mensual de Marcaciones - Personal Especial</h3>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                  Operación RRHH
                </span>
              </div>
              <p class="section-copy-sm text-xs text-slate-500 mt-0.5">
                Registra o ajusta manualmente las horas de entrada y salida del personal autorizado por RRHH para salir sin marcar biométrico.
              </p>
            </div>
          </div>
        </div>

        {{-- ALERTAS DENTRO DEL MODAL --}}
        @if (session()->has('modal_status'))
          <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
              <span class="text-sm">✓</span>
              <span>{{ session('modal_status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 font-bold hover:text-emerald-900">✕</button>
          </div>
        @endif
        @if (session()->has('modal_warning'))
          <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-bold text-amber-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
              <span class="text-sm">⚠️</span>
              <span>{{ session('modal_warning') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-amber-700 font-bold hover:text-amber-900">✕</button>
          </div>
        @endif

        {{-- CONTENIDO CON SCROLL FLEXIBLE --}}
        <div class="overflow-y-auto pr-1 space-y-4" style="flex: 1 1 auto;">
          {{-- SECCIÓN SUPERIOR: SELECCIÓN O FICHA DEL PERSONAL --}}
          @if (! $selectedEmpleado)
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs">
              <div class="max-w-2xl mx-auto text-center">
                <span class="text-4xl block mb-2">🔍</span>
                <h4 class="text-base font-black text-slate-900">Buscar personal para gestionar marcaciones</h4>
                <p class="text-xs text-slate-500 mt-1 mb-4">
                  Ingresa el nombre, apellido, carnet de identidad o código biométrico para abrir su mes de trabajo:
                </p>

                <div class="relative">
                  <input type="text"
                    wire:model.live.debounce.250ms="modalSearch"
                    class="form-input text-sm w-full py-2.5 pl-4 pr-10 rounded-xl border-slate-300 focus:border-indigo-600 focus:ring-indigo-500 shadow-xs"
                    placeholder="Escribe el nombre o carnet/código (ej. marco, lucia)..."
                    autofocus>
                  
                  @if(filled($modalSearch))
                    <button type="button" wire:click="$set('modalSearch', '')" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</button>
                  @endif
                </div>

                {{-- RESULTADOS DE BÚSQUEDA O SUGERENCIAS DIRECTAS --}}
                @if($candidatosModal->isNotEmpty())
                  <div class="mt-4">
                    <div class="text-[11px] font-bold text-slate-500 mb-2 text-left uppercase tracking-wider">
                      {{ filled($modalSearch) ? 'Resultados encontrados (' . $candidatosModal->count() . '):' : 'Sugerencias - Personal Especial:' }}
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 text-left max-h-72 overflow-y-auto">
                      @foreach($candidatosModal as $candidato)
                        <div wire:key="candidato-modal-{{ $candidato->id }}"
                          wire:click="selectEmpleado({{ $candidato->id }})"
                          class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:border-indigo-400 hover:bg-indigo-50/70 cursor-pointer transition-all gap-3 shadow-xs">
                          <div class="flex items-center gap-3 truncate">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                              {{ substr($candidato->nombre, 0, 1) }}{{ substr($candidato->apellido, 0, 1) }}
                            </div>
                            <div class="truncate">
                              <strong class="text-xs font-bold text-slate-900 block truncate">{{ $candidato->nombre_completo }}</strong>
                              <span class="text-[11px] text-slate-500 font-mono block truncate">
                                CI: {{ $candidato->codigo_biometrico ?: 'Sin carnet' }} · {{ $candidato->sucursal ?: 'Sin sucursal' }}
                              </span>
                            </div>
                          </div>
                          <div class="shrink-0 flex items-center gap-1.5">
                            @if($candidato->es_especial)
                              <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                ⭐ Especial
                              </span>
                            @endif
                            <span class="text-xs text-indigo-600 font-black">→</span>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @elseif(filled($modalSearch))
                  <div class="p-5 text-center text-xs text-slate-500 mt-3 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    No se encontraron empleados coincidentes con "{{ $modalSearch }}".
                  </div>
                @endif
              </div>
            </div>
          @else
            {{-- FICHA COMPACTA DEL EMPLEADO SELECCIONADO --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-indigo-700 to-indigo-500 text-white flex items-center justify-center font-black text-sm uppercase shadow-sm">
                  {{ substr($selectedEmpleado->nombre, 0, 1) }}{{ substr($selectedEmpleado->apellido, 0, 1) }}
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <h4 class="text-base font-black text-slate-900">{{ $selectedEmpleado->nombre_completo }}</h4>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $selectedEmpleado->es_especial ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                      {{ $selectedEmpleado->es_especial ? '⭐ Régimen Especial Activo' : 'Se activará Régimen Especial' }}
                    </span>
                  </div>
                  <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono font-bold text-[11px]">
                      CI: {{ $selectedEmpleado->codigo_biometrico ?: 'Sin carnet' }}
                    </span>
                    <span>·</span>
                    <span>{{ $selectedEmpleado->sucursal ?: 'Sin sucursal' }} ({{ $selectedEmpleado->area ?: 'General' }})</span>
                    <span>·</span>
                    <span class="text-slate-600">
                      Horario habitual: <strong class="font-mono text-slate-800">{{ substr($selectedEmpleado->hora_entrada_programada ?: '08:30', 0, 5) }} - {{ substr($selectedEmpleado->hora_salida_programada ?: '17:30', 0, 5) }}</strong>
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button type="button"
                  wire:click="deseleccionarEmpleado"
                  class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-3 py-1.5 text-xs font-semibold shadow-xs transition-colors">
                  🔄 Cambiar de personal
                </button>
              </div>
            </div>

            {{-- BARRA DEL MES Y ACCIONES RÁPIDAS --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs">
              {{-- NAVEGADOR DE MES --}}
              <div class="flex items-center gap-2">
                <button type="button" wire:click="irMesAnterior" class="h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-colors">
                  ◀ Anterior
                </button>
                <div class="text-xs font-black text-indigo-950 min-w-[150px] text-center uppercase tracking-wide bg-indigo-50/70 py-1.5 px-3 rounded-lg border border-indigo-100">
                  {{ \Carbon\Carbon::parse($mesSeleccionado . '-01')->translatedFormat('F Y') }}
                </div>
                <button type="button" wire:click="irMesSiguiente" class="h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-colors">
                  Siguiente ▶
                </button>
                <button type="button" wire:click="irMesActual" class="h-8 px-2.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-bold transition-colors">
                  Mes Actual
                </button>
              </div>

              {{-- CONTEOS EN TARJETITAS --}}
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold text-xs">
                  <span>✓ Completas:</span> <strong class="font-bold">{{ $statsMesEmpleado['completas'] }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-semibold text-xs" title="Tiene entrada pero no registró salida">
                  <span>⚠️ Sin salida:</span> <strong class="font-bold">{{ $statsMesEmpleado['soloEntrada'] }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">
                  <span>Sin registro:</span> <strong class="font-bold">{{ $statsMesEmpleado['sinRegistro'] }}</strong>
                </span>
              </div>

              {{-- BOTONES DE ACCIÓN MASIVA --}}
              <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                  wire:click="aplicarSalidaHabitualPendientes"
                  class="rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-900 px-3.5 py-1.5 text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5"
                  title="Autocompleta la salida para todos los días con entrada pero sin salida">
                  <span>⚡</span>
                  <span>Rellenar Salidas Pendientes</span>
                </button>

                <button type="button"
                  wire:click="aplicarHorarioLaborablesMes"
                  class="rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-900 px-3 py-1.5 text-xs font-bold shadow-xs transition-colors"
                  title="Rellena horario habitual a todos los días de lunes a viernes">
                  📅 Llenar Lun-Vie
                </button>

                <button type="button"
                  wire:click="guardarTodoElMes"
                  wire:loading.attr="disabled"
                  class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 text-xs font-black shadow-xs transition-colors flex items-center gap-1.5">
                  <span wire:loading.remove wire:target="guardarTodoElMes">💾 Guardar todo el mes</span>
                  <span wire:loading wire:target="guardarTodoElMes">⏳ Guardando...</span>
                </button>
              </div>
            </div>

            {{-- TABLA AMPLIA Y ELEGANTE DE DÍAS DEL MES --}}
            <div class="rounded-2xl border border-slate-200/90 overflow-hidden bg-white shadow-xs">
              <div class="overflow-x-auto" style="max-height: 480px;">
                <table class="history-table w-full text-xs" style="margin: 0;">
                  <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-xs z-10 border-b border-slate-200 text-slate-600">
                    <tr>
                      <th style="min-width: 140px;">Día Laboral</th>
                      <th style="min-width: 200px;">Registro en Sistema</th>
                      <th style="min-width: 150px; text-align: center;">Hora Entrada</th>
                      <th style="min-width: 150px; text-align: center;">Hora Salida</th>
                      <th style="min-width: 170px; text-align: center;">Acción por Día</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    @foreach($diasMes as $f => $dia)
                      <tr wire:key="dia-row-{{ $f }}"
                        class="{{ $dia['es_hoy'] ? 'bg-indigo-50/40 font-semibold' : 'hover:bg-indigo-50/20' }} transition-colors">
                        
                        {{-- DÍA LABORAL (LUNES A VIERNES) --}}
                        <td class="py-3 px-4">
                          <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-xl text-xs font-black {{ $dia['es_hoy'] ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-800' }}">
                              {{ $dia['dia_numero'] }}
                            </span>
                            <div>
                              <span class="font-bold text-slate-900 text-xs block leading-tight">{{ $dia['dia_nombre'] }}</span>
                            </div>
                            @if($dia['es_hoy'])
                              <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-indigo-600 text-white">Hoy</span>
                            @endif
                          </div>
                        </td>

                        {{-- REGISTRO ACTUAL --}}
                        <td class="py-3 px-4">
                          @if($dia['tiene_registro'])
                            <div class="flex flex-wrap items-center gap-1.5">
                              @if($dia['hora_entrada_original'] && $dia['hora_salida_original'])
                                <span class="px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                  <span>✓</span>
                                  <span>{{ $dia['hora_entrada_original'] }} - {{ $dia['hora_salida_original'] }}</span>
                                </span>
                              @elseif($dia['hora_entrada_original'] && ! $dia['hora_salida_original'])
                                <span class="px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1" title="Entrada marcada en biométrico o manual, salida pendiente">
                                  <span>{{ $dia['hora_entrada_original'] }}</span>
                                  <span class="text-[10px] font-sans font-bold bg-amber-200 text-amber-900 px-1 rounded">⚠️ Sin salida</span>
                                </span>
                              @elseif(! $dia['hora_entrada_original'] && $dia['hora_salida_original'])
                                <span class="px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                                  Salida: {{ $dia['hora_salida_original'] }}
                                </span>
                              @endif

                              @if($dia['es_especial'])
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200" title="Marcación registrada como Personal Especial">
                                  ⭐ Especial
                                </span>
                              @endif
                            </div>
                          @else
                            <span class="text-[11px] text-slate-400 italic">Sin marcación registrada</span>
                          @endif
                        </td>

                        {{-- INPUT HORA ENTRADA --}}
                        <td class="py-3 px-4 text-center">
                          <div class="max-w-[140px] mx-auto">
                            <input type="time"
                              wire:model="diasMes.{{ $f }}.hora_entrada"
                              class="form-input text-xs font-mono font-bold text-center py-1.5 px-2.5 h-9 w-full rounded-xl {{ $dia['hora_entrada'] !== $dia['hora_entrada_original'] ? 'border-amber-400 bg-amber-50/40 text-amber-950 font-black' : 'bg-slate-50' }}"
                              title="Hora de entrada">
                            @error("diasMes.{$f}.hora_entrada")
                              <span class="text-[10px] text-rose-600 block mt-0.5 font-bold">{{ $message }}</span>
                            @enderror
                          </div>
                        </td>

                        {{-- INPUT HORA SALIDA --}}
                        <td class="py-3 px-4 text-center">
                          <div class="max-w-[140px] mx-auto">
                            <input type="time"
                              wire:model="diasMes.{{ $f }}.hora_salida"
                              class="form-input text-xs font-mono font-bold text-center py-1.5 px-2.5 h-9 w-full rounded-xl {{ $dia['hora_salida'] !== $dia['hora_salida_original'] ? 'border-amber-400 bg-amber-50/40 text-amber-950 font-black' : 'bg-slate-50' }}"
                              title="Hora de salida">
                            @error("diasMes.{$f}.hora_salida")
                              <span class="text-[10px] text-rose-600 block mt-0.5 font-bold">{{ $message }}</span>
                            @enderror
                          </div>
                        </td>

                        {{-- ACCIONES RÁPIDAS POR FILA --}}
                        <td class="py-3 px-4 text-center">
                          <div class="flex items-center justify-center gap-1.5">
                            <button type="button"
                              wire:click="aplicarHorarioHabitualDia('{{ $f }}')"
                              class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-indigo-600 transition-colors"
                              title="Rellenar horario habitual de referencia">
                              🕒 Habitual
                            </button>

                            <button type="button"
                              wire:click="guardarMarcacionDia('{{ $f }}')"
                              wire:loading.attr="disabled"
                              class="px-3 py-1.5 rounded-lg text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                              title="Guardar marcación para este día">
                              ✓ Guardar
                            </button>

                            @if($dia['tiene_registro'])
                              <button type="button"
                                wire:click="limpiarMarcacionDia('{{ $f }}')"
                                wire:confirm="¿Seguro que deseas eliminar la marcación del {{ $dia['dia_nombre'] }} {{ $dia['dia_numero'] }}?"
                                class="px-2 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                title="Eliminar marcación de este día">
                                🗑️
                              </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </div>

        {{-- PIE DEL MODAL --}}
        <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="text-xs text-slate-500 flex items-center gap-2">
            <span class="text-base">🛡️</span>
            <span>Las marcaciones de <strong>Personal Especial</strong> quedan protegidas y <strong>no serán sobreescritas</strong> en futuras importaciones biométricas.</span>
          </div>

          <div class="flex items-center gap-2.5 self-end sm:self-center">
            <button type="button" wire:click="closeModalEspecial" class="app-modal-secondary text-xs">
              Cerrar
            </button>
            @if($selectedEmpleado)
              <button type="button"
                wire:click="guardarTodoElMes"
                wire:loading.attr="disabled"
                class="login-submit app-modal-submit text-xs font-black px-5 py-2">
                <span wire:loading.remove wire:target="guardarTodoElMes">💾 Guardar todo el mes</span>
                <span wire:loading wire:target="guardarTodoElMes">⏳ Guardando todo el mes...</span>
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- NUEVO MODAL: RESUMEN DE CAMBIOS Y MODIFICACIONES REALIZADAS --}}
  @if ($showResumenCambiosModal)
    <div class="app-modal-backdrop" wire:click="closeResumenCambiosModal">
      <div class="app-modal-card max-w-4xl w-full p-6 sm:p-7" x-on:click.stop style="max-height: 90vh; width: 94vw; display: flex; flex-direction: column;">
        <button type="button" wire:click="closeResumenCambiosModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">✕</button>

        {{-- CABECERA MODAL --}}
        <div class="app-modal-head mb-4 border-b border-slate-100 pb-3 flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-indigo-100">
              📋
            </div>
            <div>
              <h3 class="section-title app-modal-title text-base sm:text-lg font-black text-slate-900">
                Resumen de Cambios y Marcaciones Registradas
              </h3>
              <p class="section-copy-sm text-xs text-slate-500 mt-0.5">
                Historial de asistencias especiales creadas o editadas manualmente por RRHH.
              </p>
            </div>
          </div>
          
          @if($empleadoResumen)
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 text-indigo-800 text-xs font-bold border border-indigo-200">
                <span>👤 {{ $empleadoResumen->nombre_completo }}</span>
              </span>
              <button type="button" wire:click="$set('resumenEmpleadoId', null)" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold underline">
                Ver todos los cambios
              </button>
            </div>
          @endif
        </div>

        {{-- CONTENIDO SCROLLABLE --}}
        <div class="overflow-y-auto pr-1" style="flex: 1 1 auto; max-height: 520px;">
          @if($resumenCambios->isNotEmpty())
            <table class="history-table w-full text-xs">
              <thead class="sticky top-0 bg-slate-50 z-10 border-b border-slate-200 text-slate-600">
                <tr>
                  <th>Fecha / Día</th>
                  <th>Personal</th>
                  <th class="text-center">Hora Entrada</th>
                  <th class="text-center">Hora Salida</th>
                  <th>Observación / Autorización</th>
                  <th>Modificado por</th>
                  <th class="text-right">Último Cambio</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @foreach($resumenCambios as $cambio)
                  <tr class="hover:bg-indigo-50/20 transition-colors">
                    <td class="py-2.5 px-3">
                      <strong class="text-slate-900 block font-bold">
                        {{ $cambio->fecha ? ucfirst($cambio->fecha->locale('es')->isoFormat('dddd')) : '--' }}
                      </strong>
                      <span class="text-[11px] text-slate-400 font-mono">
                        {{ $cambio->fecha?->format('d/m/Y') }}
                      </span>
                    </td>
                    <td class="py-2.5 px-3">
                      <strong class="text-slate-900 block font-bold">{{ $cambio->empleado?->nombre_completo }}</strong>
                      <span class="text-[10px] text-slate-400 font-mono">CI: {{ $cambio->empleado?->codigo_biometrico ?: 'Sin CI' }} · {{ $cambio->empleado?->sucursal ?: 'Sin sucursal' }}</span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $cambio->hora_entrada ? substr($cambio->hora_entrada, 0, 5) : '--:--' }}
                      </span>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      @if($cambio->hora_salida)
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                          {{ substr($cambio->hora_salida, 0, 5) }}
                        </span>
                      @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                          ⚠️ Sin salida
                        </span>
                      @endif
                    </td>
                    <td class="py-2.5 px-3 text-slate-600 max-w-[200px] truncate" title="{{ $cambio->observacion }}">
                      {{ $cambio->observacion ?: 'Autorizado por RRHH' }}
                    </td>
                    <td class="py-2.5 px-3">
                      <span class="text-xs font-semibold text-slate-700">
                        {{ $cambio->actualizadoPor?->name ?? ($cambio->creador?->name ?? 'Sistema') }}
                      </span>
                    </td>
                    <td class="py-2.5 px-3 text-right">
                      <span class="text-slate-500 font-mono text-[11px] block" title="{{ $cambio->updated_at?->format('d/m/Y H:i') }}">
                        {{ $cambio->updated_at ? $cambio->updated_at->diffForHumans() : 'Reciente' }}
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <div class="py-12 text-center text-slate-400">
              <span class="text-4xl block mb-2">📋</span>
              <p class="font-bold text-slate-700">No hay cambios o marcaciones especiales registradas todavía.</p>
              <p class="text-xs text-slate-400 mt-1">Usa "Marcar Entrada y Salida Especial" para guardar asistencias del personal.</p>
            </div>
          @endif
        </div>

        {{-- PIE DEL MODAL --}}
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
          <div class="text-[11px] text-slate-400">
            Mostrando las últimas {{ $resumenCambios->count() }} modificaciones registradas en el sistema.
          </div>
          <button type="button" wire:click="closeResumenCambiosModal" class="app-modal-secondary text-xs">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- TARJETAS DE RESUMEN SUPERIORES (ENFOQUE GLOBAL Y MENSUAL) --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-2">
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
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Marcaciones del Mes</p>
        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $registrosMesCount }}</h4>
        <span class="text-xs text-emerald-600 font-medium capitalize">{{ \Carbon\Carbon::parse($mesActivo . '-01')->translatedFormat('F Y') }}</span>
      </div>
      <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl font-bold">
        📅
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Salidas Pendientes</p>
        <h4 class="text-2xl font-black {{ $salidasPendientesMesCount > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
          {{ $salidasPendientesMesCount }}
        </h4>
        <span class="text-xs {{ $salidasPendientesMesCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-500' }}">
          {{ $salidasPendientesMesCount > 0 ? '⚠️ Requieren salida' : '✓ Al día este mes' }}
        </span>
      </div>
      <div class="h-12 w-12 rounded-xl {{ $salidasPendientesMesCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-xl font-bold">
        ⚠️
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex items-center justify-between cursor-pointer hover:border-indigo-400 transition-colors"
      wire:click="openResumenCambiosModal()">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Historial de Cambios</p>
        <h4 class="text-sm sm:text-base font-black text-indigo-700 mt-1 flex items-center gap-1.5">
          <span>📋 Ver Resumen</span>
          <span class="text-xs">→</span>
        </h4>
        <span class="text-xs text-slate-500 font-medium">Modificaciones de RRHH</span>
      </div>
      <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl font-bold">
        🛡️
      </div>
    </div>
  </div>

  {{-- SECCIÓN PRINCIPAL --}}
  <section class="surface-card">
    <div class="section-head-row flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <p class="section-kicker">Operación Laboral RRHH</p>
        <h3 class="section-title">Personal Especial y Asistencia Manual</h3>
        <p class="section-copy-sm">Gestión mensual de personal que no marca en biométricos con control de ingresos y salidas directas.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <button type="button" wire:click="openResumenCambiosModal()" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-3.5 py-2 text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5">
          <span>📋</span>
          <span>Ver Resumen de Cambios</span>
        </button>

        <button type="button" wire:click="openModalEspecial" class="section-action-button flex items-center gap-2">
          <span class="text-base">🕒</span>
          <span>Marcar Entrada y Salida Especial</span>
        </button>
      </div>
    </div>

    {{-- BARRA DE PESTAÑAS --}}
    <div class="px-6 pt-4 border-b border-slate-100 flex gap-6">
      <button type="button" wire:click="setTab('personal')"
        class="pb-3 text-sm font-bold transition-colors border-b-2 {{ $tab === 'personal' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        👥 Personal en Régimen Especial ({{ $personalEspecial->total() }})
      </button>
      <button type="button" wire:click="setTab('marcaciones')"
        class="pb-3 text-sm font-bold transition-colors border-b-2 {{ $tab === 'marcaciones' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        🕒 Registro Global del Mes ({{ $registros->total() }})
      </button>
    </div>

    {{-- FILTROS GLOBALES (LIMPIOS Y MENSUALES, SIN FECHAS RESTRICTIVAS) --}}
    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-5 grid gap-3 px-6 pt-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="relative">
          <label class="form-label text-xs">Buscar personal (Nombre o Carnet/CI)</label>
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
          <label class="form-label text-xs">Mes de Gestión</label>
          <input type="month" wire:model.live="mesFiltroGlobal" class="form-input font-bold text-xs">
        </div>
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

      {{-- PESTAÑA 1: DIRECTORIO DE PERSONAL ESPECIAL (VISTA PRINCIPAL) --}}
      @if ($tab === 'personal')
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Código / CI</th>
              <th>Horario de Referencia</th>
              <th>Marcaciones este Mes</th>
              <th class="text-center" style="min-width: 100px;">Acciones</th>
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
                  <span class="font-mono text-xs text-slate-700">{{ $pe->codigo_biometrico ?: 'Sin biométrico' }}</span>
                </td>
                <td>
                  <span class="font-mono text-xs text-slate-600">
                    {{ $pe->hora_entrada_programada ? substr($pe->hora_entrada_programada, 0, 5) : '--:--' }} - 
                    {{ $pe->hora_salida_programada ? substr($pe->hora_salida_programada, 0, 5) : '--:--' }}
                  </span>
                </td>
                <td>
                  <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pe->asistencias_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-500' }}">
                      {{ $pe->asistencias_count }} días
                    </span>
                    @if($pe->pendientes_salida_mes_count > 0)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Días con entrada pero sin salida registrada">
                        ⚠️ {{ $pe->pendientes_salida_mes_count }} sin salida
                      </span>
                    @elseif($pe->asistencias_count > 0)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        ✓ Al día
                      </span>
                    @endif
                  </div>
                </td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1">
                    <button type="button" wire:click="openModalEspecial({{ $pe->id }})" class="table-action-button !px-2 !py-1 text-indigo-600" title="Gestionar marcaciones del mes">
                      🕒
                    </button>
                    <button type="button" wire:click="openResumenCambiosModal({{ $pe->id }})" class="table-action-button !px-2 !py-1 text-slate-600" title="Ver resumen de cambios">
                      📋
                    </button>
                    <a wire:navigate href="{{ route('personal', ['vista' => 'marcaciones']) }}" class="table-action-button !px-2 !py-1 text-slate-400 hover:text-indigo-600" title="Ver historial en personal">
                      📂
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-10 text-slate-400">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-3xl mb-2">👥</span>
                    <p class="font-bold text-slate-700">No se encontró personal especial registrado.</p>
                    <p class="text-xs text-slate-400 mt-1">Usa "Marcar Entrada y Salida Especial" para buscar y gestionar el mes de cualquier personal.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="px-6 py-4 border-t border-slate-100">
          {{ $personalEspecial->links() }}
        </div>

      {{-- PESTAÑA 2: REGISTRO GLOBAL DE MARCACIONES DEL MES --}}
      @else
        <table class="history-table">
          <thead>
            <tr>
              <th>Personal Especial</th>
              <th>Día Laboral / Fecha</th>
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
                    {{ $reg->fecha ? ucfirst($reg->fecha->locale('es')->isoFormat('dddd')) : '' }}
                  </div>
                  <span class="text-[11px] text-slate-400 font-mono">
                    {{ $reg->fecha?->format('d/m/Y') ?? '--/--/----' }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $reg->hora_entrada ? substr($reg->hora_entrada, 0, 5) : '--:--' }}
                  </span>
                </td>
                <td>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold {{ $reg->hora_salida ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    {{ $reg->hora_salida ? substr($reg->hora_salida, 0, 5) : '⚠️ Sin salida' }}
                  </span>
                </td>
                <td>
                  <span class="inline-block px-2.5 py-1 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                    {{ $reg->tipo_verificacion ?: 'Especial' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="flex items-center justify-center gap-1">
                    <button type="button" wire:click="openModalEspecial({{ $reg->empleado_id }})" class="table-action-button !px-2 !py-1 text-indigo-600" title="Gestionar mes completo">
                      🕒
                    </button>
                    <button type="button" wire:click="openRegistroModal({{ $reg->id }})" class="table-action-button !px-2 !py-1" title="Editar marcación">
                      ✏️
                    </button>
                    <a wire:navigate href="{{ route('reportes') }}" class="table-action-button !px-2 !py-1 text-slate-500 hover:text-indigo-600" title="Ver en reportes">
                      📊
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-10 text-slate-400">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-3xl mb-2">📋</span>
                    <p class="font-bold text-slate-700">No hay registros especiales para este mes.</p>
                    <p class="text-xs text-slate-400 mt-1">Usa el botón "Marcar Entrada y Salida Especial" para registrar asistencias del personal.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="px-6 py-4 border-t border-slate-100">
          {{ $registros->links() }}
        </div>
      @endif
    </div>
  </section>
</div>
