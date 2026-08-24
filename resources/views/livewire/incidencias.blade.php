<div class="page-stack">
  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar incidencia</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta incidencia? Esta accion quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteIncidenciaLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteIncidencia" class="table-action-button table-action-button-danger">Si, eliminar</button>
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
            <p class="section-kicker">Registro operativo</p>
            <h3 class="section-title app-modal-title">Nueva incidencia laboral</h3>
            <p class="section-copy-sm">Registra permisos, incidencias, cumpleanos o faltas por horas, media jornada o dia completo.</p>
          </div>
        </div>

        <form wire:submit="saveIncidencia" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Personal</label>
            <input
              type="search"
              wire:model.live.debounce.300ms="empleadoSearch"
              class="form-input mb-3"
              placeholder="Buscar por nombre o codigo"
            >
            <select wire:model="empleadoId" class="form-input">
              <option value="">Selecciona</option>
              @foreach($empleadosFormulario as $empleado)
                <option value="{{ $empleado->id }}">{{ $empleado->nombre_completo }} | {{ $empleado->sucursal ?: 'Sin sucursal' }}</option>
              @endforeach
            </select>
            @if($empleadosFormulario->isEmpty())
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            @endif
            @error('empleadoId') <p class="form-error">{{ $message }}</p> @enderror
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
              @foreach($tipo === 'cumpleanos' ? $alcancesCumpleanos : $alcances as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
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
            <input type="date" wire:model="fechaInicio" class="form-input">
            @error('fechaInicio') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="fechaFin" class="form-input" @disabled($tipo === 'cumpleanos')>
            @error('fechaFin') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          @if($alcance === 'horas')
            <div>
              <label class="form-label">Hora inicio</label>
              <input type="time" wire:model="horaInicio" class="form-input">
              @error('horaInicio') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label">Hora fin</label>
              <input type="time" wire:model="horaFin" class="form-input">
              @error('horaFin') <p class="form-error">{{ $message }}</p> @enderror
            </div>
          @endif
          <div class="md:col-span-2">
            <label class="form-label">Motivo / detalle</label>
            <textarea wire:model="motivo" rows="3" class="form-input" placeholder="Ej. permiso por salud, incidencia interna o falta parcial"></textarea>
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
              Cumpleanos se registra en el mismo dia y puede asignarse a la manana o a la tarde. Feriados y paros se programan en fechas especiales. Las faltas y permisos se contabilizan por horas reales o por bloque segun la seleccion.
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
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
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
            <input
              type="search"
              wire:model.live.debounce.300ms="editEmpleadoSearch"
              class="form-input mb-3"
              placeholder="Buscar por nombre o codigo"
            >
            <select wire:model="editEmpleadoId" class="form-input">
              <option value="">Selecciona</option>
              @foreach($empleadosEdicion as $empleado)
                <option value="{{ $empleado->id }}">{{ $empleado->nombre_completo }} | {{ $empleado->sucursal ?: 'Sin sucursal' }}</option>
              @endforeach
            </select>
            @if($empleadosEdicion->isEmpty())
              <p class="form-error">No se encontro personal con ese nombre o codigo.</p>
            @endif
            @error('editEmpleadoId') <p class="form-error">{{ $message }}</p> @enderror
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
              @foreach($editTipo === 'cumpleanos' ? $alcancesCumpleanos : $alcances as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
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
            <input type="date" wire:model="editFechaInicio" class="form-input">
            @error('editFechaInicio') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Fecha fin</label>
            <input type="date" wire:model="editFechaFin" class="form-input" @disabled($editTipo === 'cumpleanos')>
            @error('editFechaFin') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          @if($editAlcance === 'horas')
            <div>
              <label class="form-label">Hora inicio</label>
              <input type="time" wire:model="editHoraInicio" class="form-input">
              @error('editHoraInicio') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label">Hora fin</label>
              <input type="time" wire:model="editHoraFin" class="form-input">
              @error('editHoraFin') <p class="form-error">{{ $message }}</p> @enderror
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
        <p class="section-copy-sm">Programa permisos por horas, incidencias por manana o tarde, cumpleanos por la manana o la tarde, y faltas con tiempo contabilizado.</p>
      </div>
      <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar incidencia</button>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-3">
        <div class="space-y-2">
          <label class="form-label">Buscar por nombre o codigo</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Ej. Juana o 123456">
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
            <th>Alcance</th>
            <th>Periodo</th>
            <th>Horas contabilizadas</th>
            <th>Estado</th>
            <th>Detalle</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($incidencias as $item)
            <tr>
              <td>{{ $item->empleado?->nombre_completo ?? 'Sin personal' }}</td>
              <td>{{ $item->tipo_label }}</td>
              <td>{{ $item->alcance_label }}</td>
              <td>
                {{ $item->fecha_inicio?->format('d/m/Y') ?? '--/--/----' }}
                -
                {{ $item->fecha_fin?->format('d/m/Y') ?? '--/--/----' }}
                @if($item->hora_inicio && $item->hora_fin)
                  <div class="mt-1 text-xs text-slate-400">{{ substr($item->hora_inicio, 0, 5) }} - {{ substr($item->hora_fin, 0, 5) }}</div>
                @endif
              </td>
              <td>{{ sprintf('%02d:%02d', intdiv((int) ($item->minutos_contabilizados ?? 0), 60), (int) ($item->minutos_contabilizados ?? 0) % 60) }}</td>
              <td>{{ ucfirst($item->estado) }}</td>
              <td>{{ $item->motivo ?: 'Sin detalle adicional' }}</td>
              <td class="table-actions-cell">
                <div class="table-actions-group">
                  <button type="button" wire:click="openEditModal({{ $item->id }})" class="table-action-button">Editar</button>
                  <button type="button" wire:click="openDeleteModal({{ $item->id }})" class="table-action-button table-action-button-danger">Eliminar</button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-slate-400">No hay incidencias registradas para el filtro actual.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($incidencias->hasPages())
      <div class="table-pagination-shell">
        <div class="table-pagination-bar">
          <p class="table-pagination-copy">
            Mostrando {{ $incidencias->firstItem() }} a {{ $incidencias->lastItem() }} de {{ $incidencias->total() }} registros
          </p>
          <div class="table-pagination-actions">
            <button type="button" wire:click="previousPage" @disabled($incidencias->onFirstPage()) class="table-pagination-button {{ $incidencias->onFirstPage() ? 'table-pagination-button-disabled' : '' }}">Anterior</button>
            @foreach (range(max(1, $incidencias->currentPage() - 2), min($incidencias->lastPage(), $incidencias->currentPage() + 2)) as $page)
              <button type="button" wire:click="gotoPage({{ $page }})" class="table-pagination-button {{ $page === $incidencias->currentPage() ? 'table-pagination-button-active' : '' }}">{{ $page }}</button>
            @endforeach
            <button type="button" wire:click="nextPage" @disabled(! $incidencias->hasMorePages()) class="table-pagination-button {{ ! $incidencias->hasMorePages() ? 'table-pagination-button-disabled' : '' }}">Siguiente</button>
          </div>
        </div>
      </div>
    @endif
  </section>
</div>
