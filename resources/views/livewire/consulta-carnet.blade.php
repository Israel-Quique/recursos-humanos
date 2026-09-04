<div class="login-stage">
  @php
    $loginLogo = file_exists(public_path('images/menu-logo.png'))
      ? asset('images/menu-logo.png')
      : null;
  @endphp

  {{-- POPUP MODAL: SOLICITAR CORREO SI EL FUNCIONARIO NO TIENE UNO --}}
  @if ($showPedirEmailModal)
    <div class="app-modal-backdrop" wire:click="cerrarPedirEmailModal" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); z-index: 100000; position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 1rem;">
      <div class="app-modal-card !max-w-md !w-full !p-6 bg-white rounded-2xl shadow-2xl border border-slate-200" x-on:click.stop>
        
        <div class="text-center space-y-3">
          <div class="mx-auto h-14 w-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shadow-xs">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
          </div>
          
          <div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight">¿Dónde te llegará el estado de tu boleta?</h3>
            <p class="text-xs font-semibold text-slate-500 mt-1 leading-relaxed">
              Estimado/a <span class="font-bold text-slate-800">{{ $boletaNombre }}</span>, detectamos que aún no tienes un correo registrado en el sistema.
            </p>
          </div>
        </div>

        <div class="mt-5 space-y-4">
          <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3 text-xs font-medium text-blue-900 flex items-start gap-2.5">
            <svg class="h-4 w-4 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>Ingresa tu correo institucional o personal para recibir la respuesta (aprobación/rechazo) de Recursos Humanos.</span>
          </div>

          <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
              Correo Electrónico *
            </label>
            <input
              type="email"
              wire:model="boletaEmail"
              wire:keydown.enter="confirmarEmailYDescargar"
              placeholder="ejemplo@correos.gob.bo o correo@gmail.com"
              autofocus
              class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20 transition"
            >
            @error('boletaEmail') <p class="text-xs text-rose-600 font-bold mt-1.5">{{ $message }}</p> @enderror
            <p class="text-[11px] text-slate-500 mt-1.5 font-medium flex items-center gap-1">
              <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Se guardará en tu ficha. Por seguridad, luego solo el administrador podrá modificarlo.</span>
            </p>
          </div>

          <div class="pt-2 flex items-center justify-end gap-3">
            <button
              type="button"
              wire:click="cerrarPedirEmailModal"
              class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer"
            >
              Volver
            </button>
            <button
              type="button"
              wire:click="confirmarEmailYDescargar"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0f67c0] to-indigo-600 hover:from-[#0d59a7] hover:to-indigo-700 text-white font-extrabold text-xs shadow-lg shadow-indigo-500/20 transition-all cursor-pointer"
            >
              <span wire:loading.remove wire:target="confirmarEmailYDescargar">Guardar correo y Enviar boleta</span>
              <span wire:loading wire:target="confirmarEmailYDescargar">Enviando boleta...</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  @endif

  {{-- MODAL DE GENERACIÓN DE BOLETA / PAPELETA --}}
  @if ($showBoletaModal)
    <div class="app-modal-backdrop" wire:click="cerrarBoletaModal" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 9999;">
      <div class="app-modal-card !max-w-4xl !w-full !p-6 sm:!p-8 bg-white rounded-2xl shadow-2xl border border-slate-200" x-on:click.stop style="max-height: 90vh; overflow-y: auto;">
        
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-3.5">
            @if ($loginLogo)
              <img src="{{ $loginLogo }}" alt="Agencia Boliviana de Correos" class="h-11 w-auto object-contain max-w-[140px] drop-shadow-xs">
            @else
              <div class="h-11 w-11 rounded-xl bg-blue-50 text-[#1e60c6] flex items-center justify-center font-black">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
              </div>
            @endif
            <div>
              <p class="text-xs font-black uppercase tracking-wider text-slate-500">Agencia Boliviana de Correos</p>
              <h2 class="text-xl font-black text-slate-900 tracking-tight">Papeleta de Comisión - Permiso Particular</h2>
            </div>
          </div>
          <div class="flex items-center gap-3">
            @if (file_exists(public_path('images/obrasPublicas.png')))
              <img src="{{ asset('images/obrasPublicas.png') }}" alt="Ministerio de Obras Públicas" class="hidden sm:block h-10 w-auto object-contain opacity-90">
            @endif
            <button type="button" wire:click="cerrarBoletaModal" class="text-slate-400 hover:text-slate-700 p-2 rounded-lg transition hover:bg-slate-100" aria-label="Cerrar modal">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>

        {{-- CUERPO FORMULARIO DE LA PAPELETA --}}
        <div class="mt-6 space-y-6">
          
          {{-- SECCIÓN: DATOS DEL FUNCIONARIO --}}
          <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-[#1e60c6] flex items-center gap-1.5">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Datos del Funcionario
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                  <span>Nombre del Funcionario</span>
                  <span class="text-[10px] text-slate-400 font-semibold">🔒 (No editable)</span>
                </label>
                <input type="text" wire:model="boletaNombre" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100/90 px-3.5 py-2 text-sm font-bold text-slate-700 cursor-not-allowed select-none">
                @error('boletaNombre') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                  <span>C.I. / Carnet</span>
                  <span class="text-[10px] text-slate-400 font-semibold">🔒 (No editable)</span>
                </label>
                <input type="text" wire:model="boletaCi" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100/90 px-3.5 py-2 text-sm font-bold text-slate-700 cursor-not-allowed select-none">
                @error('boletaCi') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1">Cargo *</label>
                <input type="text" wire:model="boletaCargo" placeholder="Ej: ENCARGADO DE AREA" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                @error('boletaCargo') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          {{-- SECCIÓN: MOTIVO Y TIPO DE PERMISO --}}
          <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-[#1e60c6] flex items-center gap-1.5">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
              Detalle y Tipo de Solicitud
            </h3>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Motivo del Permiso / Comisión *</label>
              <input type="text" wire:model.live.debounce.300ms="boletaMotivo" placeholder="Ej: REUNIÓN DE COORDINACIÓN INSTITUCIONAL" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
              @error('boletaMotivo') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">Tipo de Permiso *</label>
              <div class="grid grid-cols-3 gap-3">
                <label class="flex items-center gap-2.5 p-3 rounded-xl border {{ $boletaTipo === 'comision' ? 'border-[#1e60c6] bg-blue-50/60 font-black text-[#1e60c6]' : 'border-slate-300 bg-white text-slate-700' }} cursor-pointer transition">
                  <input type="radio" wire:model.live="boletaTipo" value="comision" class="text-[#1e60c6] focus:ring-[#1e60c6]">
                  <span class="text-xs font-bold">COMISIÓN</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border {{ $boletaTipo === 'particular' ? 'border-[#1e60c6] bg-blue-50/60 font-black text-[#1e60c6]' : 'border-slate-300 bg-white text-slate-700' }} cursor-pointer transition">
                  <input type="radio" wire:model.live="boletaTipo" value="particular" class="text-[#1e60c6] focus:ring-[#1e60c6]">
                  <span class="text-xs font-bold">PARTICULAR</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border {{ $boletaTipo === 'medico' ? 'border-[#1e60c6] bg-blue-50/60 font-black text-[#1e60c6]' : 'border-slate-300 bg-white text-slate-700' }} cursor-pointer transition">
                  <input type="radio" wire:model.live="boletaTipo" value="medico" class="text-[#1e60c6] focus:ring-[#1e60c6]">
                  <span class="text-xs font-bold">MÉDICO</span>
                </label>
              </div>
            </div>
          </div>

          {{-- SECCIÓN: FECHAS, HORAS Y TIEMPO --}}
          <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-[#1e60c6] flex items-center gap-1.5">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
              Horarios y Tiempo Solicitado
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              {{-- Desde --}}
              <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2">
                <span class="block text-[11px] font-black uppercase text-slate-500">Desde Fecha y Hora</span>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Fecha</label>
                  <input type="date" wire:model.live="boletaDesdeFecha" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                </div>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Hora</label>
                  @if ($this->esRangoDias)
                    <div class="rounded-lg bg-slate-100 border border-slate-200 px-2.5 py-1.5 text-[11px] font-bold text-slate-400 flex items-center gap-1.5">
                      <span>🔒 No requerida</span>
                    </div>
                  @else
                    <input type="time" wire:model.live="boletaDesdeHora" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                  @endif
                </div>
              </div>

              {{-- Hasta --}}
              <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2">
                <span class="block text-[11px] font-black uppercase text-slate-500">Hasta Fecha y Hora</span>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Fecha</label>
                  <input type="date" wire:model.live="boletaHastaFecha" min="{{ $boletaDesdeFecha }}" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                </div>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Hora</label>
                  @if ($this->esRangoDias)
                    <div class="rounded-lg bg-slate-100 border border-slate-200 px-2.5 py-1.5 text-[11px] font-bold text-slate-400 flex items-center gap-1.5">
                      <span>🔒 No requerida</span>
                    </div>
                  @else
                    <input type="time" wire:model.live="boletaHastaHora" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                  @endif
                </div>
              </div>

              {{-- Tiempo Calculado --}}
              <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2 flex flex-col justify-between">
                <span class="block text-[11px] font-black uppercase text-slate-500">Tiempo Solicitado</span>
                <div>
                  <label class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                    <span>Cálculo automático</span>
                    <span class="text-[10px] text-slate-400 font-semibold">🔒 (No editable)</span>
                  </label>
                  <input type="text" wire:model="boletaTiempoSolicitado" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1.5 text-xs font-extrabold text-indigo-700 cursor-not-allowed select-none">
                </div>
                @if ($this->esRangoDias)
                  <p class="text-[10px] font-bold text-indigo-600 flex items-center gap-1">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    <span>Rango de varios días (Jornada completa sin horas)</span>
                  </p>
                @else
                  <p class="text-[10px] text-slate-400">Se calcula automáticamente con el horario ingresado.</p>
                @endif
              </div>
            </div>
          </div>

          {{-- SECCIÓN: LUGAR Y FECHA --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                <span>Ciudad / Sucursal</span>
                <span class="text-[10px] text-slate-400 font-semibold">🔒 (No editable)</span>
              </label>
              <input type="text" wire:model="boletaCiudad" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100/90 px-3.5 py-2 text-xs font-bold text-slate-700 cursor-not-allowed select-none">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                <span>Fecha de Emisión</span>
                <span class="text-[10px] text-slate-400 font-semibold">🔒 (No editable)</span>
              </label>
              <input type="text" wire:model="boletaFechaTexto" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100/90 px-3.5 py-2 text-xs font-bold text-slate-700 cursor-not-allowed select-none">
            </div>
          </div>

          {{-- SECCIÓN: COMPROBANTE / FOTO DE JUSTIFICACIÓN OBLIGATORIA --}}
          <div class="rounded-xl border-2 {{ $errors->has('comprobante') ? 'border-rose-300 bg-rose-50/40' : 'border-indigo-200 bg-indigo-50/30' }} p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                  <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <span>Foto del Comprobante / Respaldo</span>
                  <span class="rounded bg-rose-100 text-rose-700 text-[10px] px-1.5 py-0.5 font-extrabold uppercase">Obligatorio</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Sube una foto clara del certificado médico, boleta de atención, justificación de atraso u orden de comisión (JPG, PNG o WEBP, máx 5MB).</p>
              </div>
            </div>

            @if ($comprobante)
              <div class="flex flex-col sm:flex-row items-center gap-4 bg-white p-3 rounded-xl border border-indigo-100 shadow-xs">
                <div class="relative w-28 h-28 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 shrink-0">
                  <img src="{{ $comprobante->temporaryUrl() }}" alt="Vista previa del comprobante" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1 space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $comprobante->getClientOriginalName() }}</p>
                  </div>
                  <p class="text-[11px] text-slate-500">{{ number_format($comprobante->getSize() / 1024, 1) }} KB · Imagen lista para adjuntar</p>
                  <p class="text-[11px] text-emerald-700 font-semibold">✓ Esta foto se guardará en la base de datos para la verificación de RR.HH.</p>
                  
                  <div class="pt-1">
                    <button type="button" wire:click="quitarComprobante" class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 font-bold cursor-pointer">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      <span>Cambiar o quitar foto</span>
                    </button>
                  </div>
                </div>
              </div>
            @else
              <div
                x-data="{ isDropping: false }"
                x-on:dragover.prevent="isDropping = true"
                x-on:dragleave.prevent="isDropping = false"
                x-on:drop.prevent="isDropping = false; if ($event.dataTransfer.files.length > 0) { @this.upload('comprobante', $event.dataTransfer.files[0]) }"
                class="w-full"
              >
                <label
                  :class="isDropping ? 'border-indigo-600 bg-indigo-50/95 ring-4 ring-indigo-500/25 scale-[1.01]' : 'border-indigo-300 hover:border-indigo-500 bg-white hover:bg-indigo-50/20'"
                  class="flex flex-col items-center justify-center w-full min-h-[220px] py-8 px-6 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200 text-center group shadow-xs"
                >
                  <div class="flex flex-col items-center justify-center pointer-events-none max-w-md">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50/80 border border-indigo-100 flex items-center justify-center text-indigo-600 mb-3 shadow-xs group-hover:scale-110 group-hover:bg-indigo-100 transition-all duration-200">
                      <svg class="w-8 h-8 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                      </svg>
                    </div>
                    <p class="text-sm font-black text-slate-800 tracking-tight">
                      Arrastra y suelta aquí la foto del comprobante
                    </p>
                    <p class="text-xs text-indigo-600 font-bold mt-1 group-hover:underline">
                      o haz clic en cualquier parte de este espacio para buscar en tu dispositivo
                    </p>
                    <div class="flex items-center justify-center gap-2 mt-4 flex-wrap">
                      <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-bold text-slate-600 border border-slate-200/60">Formatos: JPG, PNG, WEBP</span>
                      <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-[11px] font-bold text-slate-600 border border-slate-200/60">Hasta 5 MB</span>
                      <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-[11px] font-bold text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Se guarda en Base de Datos</span>
                      </span>
                    </div>
                  </div>
                  <input type="file" wire:model="comprobante" accept="image/*" class="hidden">
                </label>
              </div>
            @endif

            <div wire:loading wire:target="comprobante" class="text-xs text-indigo-700 font-bold flex items-center gap-2 pt-1">
              <svg class="animate-spin h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Subiendo y procesando comprobante...</span>
            </div>

            @error('comprobante')
              <p class="text-xs text-rose-600 font-bold mt-1 flex items-center gap-1">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $message }}</span>
              </p>
            @enderror
          </div>

        </div>

        @php
          $requisitosCumplidos = filled(trim($boletaNombre))
            && filled(trim($boletaCi))
            && filled(trim($boletaMotivo))
            && filled(trim($boletaTipo))
            && filled(trim($boletaDesdeFecha))
            && ($this->esRangoDias || filled(trim($boletaDesdeHora)))
            && filled(trim($boletaHastaFecha))
            && ($this->esRangoDias || filled(trim($boletaHastaHora)))
            && filled(trim($boletaTiempoSolicitado))
            && !empty($comprobante);
        @endphp

        {{-- BOTONES DE ACCIÓN (SOLO APARECE AL CUMPLIR TODOS LOS REQUISITOS) --}}
        <div class="mt-8 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
          <button type="button" wire:click="cerrarBoletaModal" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
            Cancelar
          </button>

          <div class="flex items-center gap-3">
            @if ($requisitosCumplidos)
              <button
                type="button"
                wire:click="descargarPdf"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0f67c0] to-indigo-600 hover:from-[#0d59a7] hover:to-indigo-700 text-white font-extrabold text-xs shadow-lg shadow-indigo-500/20 transition-all transform hover:scale-[1.02] cursor-pointer animate-in fade-in"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                <span wire:loading.remove wire:target="descargarPdf">Enviar a RR.HH. y Descargar Boleta PDF</span>
                <span wire:loading wire:target="descargarPdf">Generando Boleta Oficial...</span>
              </button>
            @else
              <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                <svg class="h-4 w-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Completa el motivo y sube la foto del comprobante para habilitar el envío</span>
              </div>
            @endif
          </div>
        </div>

      </div>
    </div>
  @endif

  <div class="login-shell">
    <section class="login-welcome login-welcome-public">
      <div class="login-welcome-content login-welcome-content-public">
        <div class="login-public-stack">
          <div class="login-public-hero">
            <div class="login-brand-badge">
              @if ($loginLogo)
                <img src="{{ $loginLogo }}" alt="Correos de Bolivia" class="login-brand-badge-image">
              @else
                <span>CB</span>
              @endif
            </div>

            <div class="login-public-hero-copy">
              <p class="login-brand-kicker">Consulta y Boletas</p>
              <h1 class="login-brand-title login-brand-title-public">Buscar por carnet</h1>
              <p class="login-welcome-copy login-welcome-copy-public">
                Accede a tu resumen mensual de asistencia o genera tu papeleta de permiso oficial escribiendo tu carnet o código biométrico.
              </p>
            </div>
          </div>

          @if (session('status'))
            <div class="login-status-ok">
              {{ session('status') }}
            </div>
          @endif

          <div class="login-public-card">
            <form wire:submit="buscar" class="login-public-form">
              <label for="carnet" class="login-form-label">Carnet o código</label>
              <div class="login-input-shell">
                <span class="login-input-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.5-3.5"/>
                  </svg>
                </span>
                <input
                  wire:model.live.debounce.300ms="carnet"
                  id="carnet"
                  type="text"
                  class="form-input login-input"
                  placeholder="Ingresa tu carnet (ej: 10909669)"
                  autocomplete="off"
                >
              </div>
              @error('carnet')
                <p class="form-error form-error-dark">{{ $message }}</p>
              @enderror

              {{-- DATOS INICIALES DEL FUNCIONARIO AL ESCRIBIR EL CARNET --}}
              @if ($empleadoEncontrado)
                <div class="mt-4 p-4 rounded-2xl bg-white/95 border border-slate-200 shadow-sm text-left space-y-3 animate-in fade-in">
                  <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <div class="h-9 w-9 rounded-xl bg-blue-50 text-[#1e60c6] flex items-center justify-center font-black text-xs shrink-0 border border-blue-100">
                        {{ strtoupper(substr($empleadoEncontrado->nombre_completo, 0, 2)) }}
                      </div>
                      <div class="min-w-0">
                        <h4 class="text-xs font-black text-slate-900 truncate leading-tight">{{ $empleadoEncontrado->nombre_completo }}</h4>
                        <p class="text-[11px] font-semibold text-slate-500">Carnet: {{ $empleadoEncontrado->codigo_biometrico }}</p>
                      </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider shrink-0 {{ !empty($empleadoEncontrado->sucursal) ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-100 text-slate-600' }}">
                      {{ $empleadoEncontrado->sucursal ?: 'La Paz' }}
                    </span>
                  </div>

                  <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                      <span class="font-medium text-slate-500">Cargo / Función:</span>
                      <span class="font-bold text-slate-800 text-right truncate ml-2">{{ $empleadoEncontrado->cargo ?: ($empleadoEncontrado->area ? 'Área de ' . $empleadoEncontrado->area : 'Personal') }}</span>
                    </div>

                    <div class="flex items-start justify-between pt-2 border-t border-slate-100 gap-2">
                      <span class="font-medium text-slate-500 shrink-0">Correo de notificación:</span>
                      <div class="text-right">
                        @if (filled($empleadoEncontrado->email))
                          <span class="font-black text-slate-900 break-all text-[12px]">{{ $empleadoEncontrado->email }}</span>
                          <div class="mt-0.5">
                            <span class="inline-flex items-center gap-1 text-[9px] font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                              ✓ Registrado (Solo editable por Administración)
                            </span>
                          </div>
                        @else
                          <span class="font-bold text-amber-700 text-[12px]">Sin correo registrado</span>
                          <div class="mt-0.5">
                            <span class="inline-flex items-center gap-1 text-[9px] font-extrabold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                              ⚠️ Pendiente (Se solicitará al generar boleta)
                            </span>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              @endif

              <div class="space-y-3 mt-4">
                <button type="submit" class="login-submit !w-full justify-center">
                  <span>Consultar Asistencia</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="login-submit-icon">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                  </svg>
                </button>

                @if (filled(trim($carnet)))
                  <button
                    type="button"
                    wire:click="abrirBoletaModal"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#0f67c0] to-indigo-700 hover:from-[#0d59a7] hover:to-indigo-800 text-white font-extrabold text-sm px-4 py-3 shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5 cursor-pointer"
                  >
                    <svg class="h-4 w-4 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                      <line x1="16" y1="13" x2="8" y2="13"/>
                      <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span>📄 Generar Boleta de Permiso / Comisión</span>
                  </button>
                @endif
              </div>
            </form>
            <div class="login-public-divider"></div>

            <div class="login-public-benefits">
              <article class="login-public-benefit">
                <strong>Consulta directa</strong>
                <span>Historial de marcaciones y horas acumuladas.</span>
              </article>
              <article class="login-public-benefit">
                <strong>Boleta oficial</strong>
                <span>Papeleta de comisión y permisos en PDF y Excel.</span>
              </article>
            </div>
          </div>
        </div>
      </div>
    </section>

    <aside class="login-sidecard">
      <div class="login-sidecard-content">
        <p class="login-sidecard-kicker">Portal institucional</p>
        <h2 class="login-sidecard-title">Sistema de Recursos Humanos</h2>
        <p class="login-sidecard-copy">
          Consulta asistencia, seguimiento del personal, generación de boletas de permiso e información institucional
          desde una sola plataforma interna.
        </p>

        <ul class="login-feature-list">
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Consulta rápida por carnet</span>
          </li>
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Generación de papeletas oficiales</span>
          </li>
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Historial del mes en una sola vista</span>
          </li>
        </ul>

        <div class="login-sidecard-badge">
          <span class="login-sidecard-badge-dot"></span>
          <span>Correos de Bolivia</span>
        </div>
      </div>

      <p class="login-sidecard-footer">Terminal de operación segura &middot; Seguridad AES-256</p>
    </aside>
  </div>
</div>
