<div class="login-stage">
  @php
    $loginLogo = file_exists(public_path('images/menu-logo.png'))
      ? asset('images/menu-logo.png')
      : null;
  @endphp

  {{-- MODAL DE GENERACIÓN DE BOLETA / PAPELETA --}}
  @if ($showBoletaModal)
    <div class="app-modal-backdrop" wire:click="cerrarBoletaModal" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 9999;">
      <div class="app-modal-card !max-w-4xl !w-full !p-6 sm:!p-8 bg-white rounded-2xl shadow-2xl border border-slate-200" x-on:click.stop style="max-height: 90vh; overflow-y: auto;">
        
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-blue-50 text-[#1e60c6] flex items-center justify-center font-black">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-black uppercase tracking-wider text-slate-500">Agencia Boliviana de Correos</p>
              <h2 class="text-xl font-black text-slate-900 tracking-tight">Papeleta de Comisión - Permiso Particular</h2>
            </div>
          </div>
          <button type="button" wire:click="cerrarBoletaModal" class="text-slate-400 hover:text-slate-700 p-2 rounded-lg transition" aria-label="Cerrar modal">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
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
                <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Funcionario *</label>
                <input type="text" wire:model="boletaNombre" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
                @error('boletaNombre') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">C.I. / Carnet *</label>
                <input type="text" wire:model="boletaCi" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
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
              <input type="text" wire:model="boletaMotivo" placeholder="Ej: REUNIÓN DE COORDINACIÓN INSTITUCIONAL" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-xs focus:border-[#1e60c6] focus:ring-2 focus:ring-[#1e60c6]/20">
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
                  <label class="text-[10px] font-bold text-slate-400">Fecha (DD/MM/AAAA)</label>
                  <input type="text" wire:model.live.debounce.300ms="boletaDesdeFecha" placeholder="DD/MM/AAAA" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800">
                </div>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Hora</label>
                  <input type="time" wire:model.live="boletaDesdeHora" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800">
                </div>
              </div>

              {{-- Hasta --}}
              <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2">
                <span class="block text-[11px] font-black uppercase text-slate-500">Hasta Fecha y Hora</span>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Fecha (DD/MM/AAAA)</label>
                  <input type="text" wire:model.live.debounce.300ms="boletaHastaFecha" placeholder="DD/MM/AAAA" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800">
                </div>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Hora</label>
                  <input type="time" wire:model.live="boletaHastaHora" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-800">
                </div>
              </div>

              {{-- Tiempo Calculado --}}
              <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2 flex flex-col justify-between">
                <span class="block text-[11px] font-black uppercase text-slate-500">Tiempo Solicitado</span>
                <div>
                  <label class="text-[10px] font-bold text-slate-400">Texto / Tiempo</label>
                  <input type="text" wire:model="boletaTiempoSolicitado" placeholder="Ej: 30 MIN o 2 HORAS" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-extrabold text-indigo-700">
                </div>
                <p class="text-[10px] text-slate-400">Se autocalcula con el horario y es editable.</p>
              </div>
            </div>
          </div>

          {{-- SECCIÓN: LUGAR Y FECHA --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Ciudad / Sucursal</label>
              <input type="text" wire:model="boletaCiudad" placeholder="Ej: LA PAZ" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-800">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Emisión</label>
              <input type="text" wire:model="boletaFechaTexto" placeholder="Ej: 31 DE AGOSTO DE 2026" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-800">
            </div>
          </div>

        </div>

        {{-- BOTONES DE ACCIÓN (DESCARGA PDF Y EXCEL) --}}
        <div class="mt-8 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
          <button type="button" wire:click="cerrarBoletaModal" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
            Cancelar
          </button>

          <div class="flex items-center gap-3">
            <button
              type="button"
              wire:click="descargarExcel"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition cursor-pointer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
              <span>Descargar Excel (.xlsx)</span>
            </button>

            <button
              type="button"
              wire:click="descargarPdf"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0f67c0] hover:bg-[#0d59a7] text-white font-extrabold text-xs shadow-md transition cursor-pointer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
              <span>Descargar PDF</span>
            </button>
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
                  wire:model.live="carnet"
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

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                <button type="submit" class="login-submit !w-full justify-center">
                  <span>Consultar Asistencia</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="login-submit-icon">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                  </svg>
                </button>

                <button
                  type="button"
                  wire:click="abrirBoletaModal"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-extrabold text-sm px-4 py-3 shadow-md transition cursor-pointer"
                >
                  <svg class="h-4 w-4 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                  </svg>
                  <span>Generar Boleta</span>
                </button>
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
