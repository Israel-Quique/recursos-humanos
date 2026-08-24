<div class="page-stack">
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
          <div>
            <p class="section-kicker">Ficha del personal</p>
            <h3 class="section-title app-modal-title">{{ $detailEmpleado['nombre_completo'] ?? 'Detalle del personal' }}</h3>
            <p class="section-copy-sm">Resumen del perfil, horario regional asignado y detalle mensual de marcaciones.</p>
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

  @if ($vista === 'personal')
  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Personal registrado</p>
          <h3 class="section-title">Plantilla activa de RRHH</h3>
          <p class="section-copy-sm">Aqui solo se muestra personal activo. Si una persona pasa 30 dias sin marcar, se mueve automaticamente a Inactivos.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar personal</button>
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
            <label for="personal-sucursal" class="form-label">Filtrar por sucursal</label>
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
                <td>{{ $empleado->nombre_completo }}</td>
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
                      wire:click="openDetailModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Ver detalle del personal"
                      title="Ver detalle"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Ver</span>
                    </button>
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
                    <button
                      type="button"
                      wire:click="openPdfModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Exportar ficha del personal en PDF"
                      title="PDF"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h8M8 17h5"/>
                      </svg>
                      <span>PDF</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openDeleteModal({{ $empleado->id }})"
                      class="table-action-button table-action-button-danger"
                      aria-label="Eliminar personal"
                      title="Eliminar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                      </svg>
                      <span>Eliminar</span>
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
          <p class="section-kicker">Personal inactivo</p>
          <h3 class="section-title">Sin marcaciones en los ultimos 30 dias</h3>
          <p class="section-copy-sm">Este modulo se actualiza automaticamente segun la ultima marcacion registrada de cada persona.</p>
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
                <td>{{ $empleado->nombre_completo }}</td>
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
                      wire:click="openDetailModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Ver detalle del personal inactivo"
                      title="Ver detalle"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Ver</span>
                    </button>
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
                    <button
                      type="button"
                      wire:click="openPdfModal({{ $empleado->id }})"
                      class="table-action-button"
                      aria-label="Exportar ficha del personal inactivo en PDF"
                      title="PDF"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h8M8 17h5"/>
                      </svg>
                      <span>PDF</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openDeleteModal({{ $empleado->id }})"
                      class="table-action-button table-action-button-danger"
                      aria-label="Eliminar personal inactivo"
                      title="Eliminar"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                      </svg>
                      <span>Eliminar</span>
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
      <div class="section-head-row section-head-row-spacious">
        <div class="history-panel-intro">
          <p class="section-kicker">Dias marcados</p>
          <h3 class="section-title">Historial reciente de marcaciones</h3>
          <p class="section-copy-sm">Lista de los ultimos dias en los que se registro una marcacion, con fecha y dia.</p>
        </div>
      </div>

      <div class="history-table-shell">
        <div class="history-filters-grid md:grid-cols-3">
          <div>
            <label for="marcaciones-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="marcaciones-search"
              type="search"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Ej. 10909669 o ABEL ROJAS"
            >
          </div>
          <div>
            <label for="marcaciones-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="marcaciones-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal }}">{{ $sucursal }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="marcaciones-orden" class="form-label">Ordenar por</label>
            <select id="marcaciones-orden" wire:model.live="ordenMarcaciones" class="form-input">
              <option value="fecha_reciente">Fecha mas reciente</option>
              <option value="fecha_antigua">Fecha mas antigua</option>
              <option value="hora_asc">Hora mas temprana</option>
              <option value="hora_desc">Hora mas tarde</option>
              <option value="nombre_asc">Empleado A-Z</option>
              <option value="nombre_desc">Empleado Z-A</option>
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Empleado</th>
              <th>Sucursal</th>
              <th>Fecha</th>
              <th>Dia</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($registros as $registro)
              <tr>
                <td>{{ $registro->empleado?->nombre_completo ?? 'Sin empleado' }}</td>
                <td>{{ $registro->empleado?->sucursal ?? 'Sin sucursal' }}</td>
                <td>{{ $registro->fecha_formateada ?? 'Sin fecha' }}</td>
                <td>{{ ucfirst($registro->dia ?? 'Sin dia') }}</td>
                <td>{{ $registro->hora_entrada ? substr($registro->hora_entrada, 0, 5) : '--:--' }}</td>
                <td>{{ $registro->hora_salida ? substr($registro->hora_salida, 0, 5) : '--:--' }}</td>
                <td>{{ $registro->estado_marcacion ?? 'Sin registro' }}</td>
                <td class="table-actions-cell">
                  @if ($registro->empleado)
                    <div class="table-actions-group">
                      <button
                        type="button"
                        wire:click="openDetailModal({{ $registro->empleado->id }})"
                        class="table-action-button"
                        title="Ver perfil del personal"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                          <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span>Ver</span>
                      </button>
                      <button
                        type="button"
                        wire:click="openEditModal({{ $registro->empleado->id }})"
                        class="table-action-button"
                        title="Editar personal"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                        </svg>
                        <span>Editar</span>
                      </button>
                      <button
                        type="button"
                        wire:click="openDeleteRegistroModal({{ $registro->id }})"
                        class="table-action-button table-action-button-danger"
                        title="Eliminar marcacion"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                        </svg>
                        <span>Eliminar</span>
                      </button>
                    </div>
                  @else
                    <span class="text-slate-400">Sin acciones</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-slate-400">No hay marcaciones recientes para mostrar.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </article>
  </section>

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

  @if ($vista === 'control')
  <section>
    <article class="surface-card">
      <div class="history-panel-intro">
        <p class="section-kicker">Control mensual</p>
        <h3 class="section-title">Horas y consumo de tolerancia</h3>
        <p class="section-copy-sm">
          Regla aplicada: se usa el horario regional de cada sucursal, una tolerancia maxima de 35 minutos por mes y tambien se respetan los feriados o jornadas especiales programadas.
        </p>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="history-filters-grid md:grid-cols-3">
          <div>
            <label for="control-search" class="form-label">Buscar por codigo o nombre</label>
            <input
              id="control-search"
              type="search"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Ej. 10909669 o ABEL ROJAS"
            >
          </div>
          <div>
            <label for="control-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="control-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas las sucursales</option>
              @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal }}">{{ $sucursal }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="control-tolerancia" class="form-label">Filtrar por tolerancia</label>
            <select id="control-tolerancia" wire:model.live="toleranciaFiltro" class="form-input">
              <option value="">Todas las tolerancias</option>
              <option value="dentro">Dentro de tolerancia</option>
              <option value="excedido">Excedido</option>
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Personal</th>
              <th>Sucursal</th>
              <th>Horas acumuladas</th>
              <th>Minutos usados</th>
              <th>Saldo mensual</th>
              <th>Tolerancia</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($empleados as $empleado)
              <tr>
                <td>{{ $empleado->nombre_completo }}</td>
                <td>{{ $empleado->sucursal }}</td>

                <td>{{ $empleado->resumen_asistencia['horas_mes'] }}</td>
                <td>{{ $empleado->resumen_asistencia['retraso_mes_formateado'] }}</td>
                <td>{{ $empleado->resumen_asistencia['saldo_retraso_formateado'] }}</td>
                <td>
                  <span class="status-badge {{ $empleado->resumen_asistencia['estado_retraso'] === 'Excedido' ? 'status-danger' : 'status-available' }}">
                    {{ $empleado->resumen_asistencia['estado_retraso'] }}
                  </span>
                </td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button
                      type="button"
                      wire:click="openDetailModal({{ $empleado->id }})"
                      class="table-action-button"
                      title="Ver detalle del personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Ver</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openEditModal({{ $empleado->id }})"
                      class="table-action-button"
                      title="Editar personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.5 4 4L7 21l-4 1 1-4L16.5 3.5Z"/>
                      </svg>
                      <span>Editar</span>
                    </button>
                    <button
                      type="button"
                      wire:click="openDeleteModal({{ $empleado->id }})"
                      class="table-action-button table-action-button-danger"
                      title="Eliminar personal"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="table-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                      </svg>
                      <span>Eliminar</span>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-slate-400">Aun no existen horas acumuladas para mostrar.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($empleados->hasPages())
        <div class="table-pagination-shell mt-4">
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
</div>
