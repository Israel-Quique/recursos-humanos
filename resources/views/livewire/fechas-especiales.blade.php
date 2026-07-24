<div class="page-stack">
  @if ($showDeleteModal)
    <div class="app-modal-backdrop" wire:click="closeDeleteModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeDeleteModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Confirmacion</p>
            <h3 class="section-title app-modal-title">Eliminar fecha especial</h3>
            <p class="section-copy-sm">Seguro que quieres eliminar esta fecha especial? Esta accion tambien quedara registrada en auditoria.</p>
          </div>
        </div>

        <div class="mt-6 rounded-[1.2rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
          <strong>{{ $pendingDeleteFechaLabel }}</strong>
        </div>

        <div class="mt-6 app-modal-actions">
          <button type="button" wire:click="closeDeleteModal" class="app-modal-secondary">Cancelar</button>
          <button type="button" wire:click="deleteFechaEspecial" class="table-action-button table-action-button-danger">Si, eliminar</button>
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
            <p class="section-kicker">Programacion especial</p>
            <h3 class="section-title app-modal-title">Registrar feriado, paro u horario especial</h3>
            <p class="section-copy-sm">Configura dias no laborables globales o por sucursal, y jornadas con entrada y salida distintas a la regla normal.</p>
          </div>
        </div>

        <form wire:submit="saveFechaEspecial" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Fecha</label>
            <input type="date" wire:model="fecha" class="form-input">
            @error('fecha') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="tipo" class="form-input">
              <option value="feriado">Feriado</option>
              <option value="paro">Paro</option>
              <option value="horario_especial">Horario especial</option>
            </select>
            @error('tipo') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Sucursal afectada</label>
            <select wire:model="sucursal" class="form-input">
              @foreach($sucursales as $item)
                <option value="{{ $item }}">{{ $item === 'TODAS' ? 'Todas las sucursales' : $item }}</option>
              @endforeach
            </select>
            @error('sucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Nombre</label>
            <input type="text" wire:model="nombre" class="form-input" placeholder="Ej. Dia del Trabajo, Paro regional o Jornada reducida">
            @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de entrada especial</label>
            <input type="time" wire:model.live="horaEntrada" class="form-input">
            @error('horaEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de salida especial</label>
            <input type="time" wire:model.live="horaSalida" class="form-input">
            @error('horaSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <p class="section-copy-sm">
              @if ($tipo === 'feriado')
                Si la fecha queda como feriado, las horas no se tomaran en cuenta y aplicara a la sucursal elegida.
              @elseif ($tipo === 'paro')
                El paro deja esa fecha no laborable solo para la sucursal seleccionada; las demas pueden seguir operando.
              @else
                Define la hora de entrada y la hora de salida que se aplicaran en esa fecha para la sucursal elegida.
              @endif
            </p>
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Descripcion</label>
            <textarea wire:model="descripcion" rows="3" class="form-input" placeholder="Opcional: detalle operativo del dia"></textarea>
            @error('descripcion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar programacion</button>
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
            <p class="section-kicker">Editar programacion</p>
            <h3 class="section-title app-modal-title">Actualizar fecha especial</h3>
            <p class="section-copy-sm">Ajusta si el dia sera feriado, paro por sucursal o una jornada especial para la regional seleccionada.</p>
          </div>
        </div>

        <form wire:submit="updateFechaEspecial" class="mt-8 grid gap-5 md:grid-cols-2">
          <div>
            <label class="form-label">Fecha</label>
            <input type="date" wire:model="editFecha" class="form-input">
            @error('editFecha') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Tipo</label>
            <select wire:model.live="editTipo" class="form-input">
              <option value="feriado">Feriado</option>
              <option value="paro">Paro</option>
              <option value="horario_especial">Horario especial</option>
            </select>
            @error('editTipo') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Sucursal afectada</label>
            <select wire:model="editSucursal" class="form-input">
              @foreach($sucursales as $item)
                <option value="{{ $item }}">{{ $item === 'TODAS' ? 'Todas las sucursales' : $item }}</option>
              @endforeach
            </select>
            @error('editSucursal') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Nombre</label>
            <input type="text" wire:model="editNombre" class="form-input">
            @error('editNombre') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de entrada especial</label>
            <input type="time" wire:model.live="editHoraEntrada" class="form-input">
            @error('editHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de salida especial</label>
            <input type="time" wire:model.live="editHoraSalida" class="form-input">
            @error('editHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div class="md:col-span-2">
            <p class="section-copy-sm">
              @if ($editTipo === 'feriado')
                Si mantienes esta fecha como feriado, las horas se ignoraran al guardar.
              @elseif ($editTipo === 'paro')
                Si mantienes esta fecha como paro, la sucursal elegida quedara no laborable.
              @else
                Ajusta aqui la entrada y la salida especial de esa jornada.
              @endif
            </p>
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Descripcion</label>
            <textarea wire:model="editDescripcion" rows="3" class="form-input"></textarea>
            @error('editDescripcion') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Control institucional</p>
          <h3 class="section-title">Fechas especiales, paros y jornadas reducidas</h3>
          <p class="section-copy-sm">Programa feriados, paros por sucursal y dias con horarios excepcionales para que el sistema calcule correctamente tardanzas, faltas y horas trabajadas.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="section-action-button">Agregar fecha</button>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-3">
          <div class="space-y-2">
            <label for="fecha-especial-search" class="form-label">Buscar por nombre</label>
            <input
              id="fecha-especial-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Ej. Feriado, paro o jornada"
              autocomplete="off"
            >
          </div>
          <div class="space-y-2">
            <label for="fecha-especial-tipo" class="form-label">Filtrar por tipo</label>
            <select id="fecha-especial-tipo" wire:model.live="tipoFiltro" class="form-input">
              <option value="">Todos</option>
              <option value="feriado">Feriados</option>
              <option value="paro">Paros</option>
              <option value="horario_especial">Horarios especiales</option>
            </select>
          </div>
          <div class="space-y-2">
            <label for="fecha-especial-sucursal" class="form-label">Filtrar por sucursal</label>
            <select id="fecha-especial-sucursal" wire:model.live="sucursalFiltro" class="form-input">
              <option value="">Todas</option>
              @foreach($sucursales as $item)
                <option value="{{ $item }}">{{ $item === 'TODAS' ? 'Todas las sucursales' : $item }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Sucursal</th>
              <th>Tipo</th>
              <th>Nombre</th>
              <th>Horario aplicado</th>
              <th>Descripcion</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($fechas as $item)
              <tr>
                <td>{{ $item->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                <td>{{ $item->sucursal_label }}</td>
                <td>{{ $item->tipo === 'feriado' ? 'Feriado' : ($item->tipo === 'paro' ? 'Paro' : 'Horario especial') }}</td>
                <td>{{ $item->nombre }}</td>
                <td>
                  @if (in_array($item->tipo, ['feriado', 'paro'], true))
                    No laborable
                  @else
                    {{ $item->hora_entrada ? substr($item->hora_entrada, 0, 5) : 'Horario base' }}
                    -
                    {{ $item->hora_salida ? substr($item->hora_salida, 0, 5) : 'Horario base' }}
                  @endif
                </td>
                <td>{{ $item->descripcion ?: 'Sin detalle adicional' }}</td>
                <td class="table-actions-cell">
                  <div class="table-actions-group">
                    <button type="button" wire:click="openEditModal({{ $item->id }})" class="table-action-button">
                      Editar
                    </button>
                    <button type="button" wire:click="openDeleteModal({{ $item->id }})" class="table-action-button table-action-button-danger">
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-slate-400">No hay fechas especiales programadas todavia.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($fechas->hasPages())
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando {{ $fechas->firstItem() }} a {{ $fechas->lastItem() }} de {{ $fechas->total() }} registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                @disabled($fechas->onFirstPage())
                class="table-pagination-button {{ $fechas->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
              >
                Anterior
              </button>

              @foreach (range(max(1, $fechas->currentPage() - 2), min($fechas->lastPage(), $fechas->currentPage() + 2)) as $page)
                <button
                  type="button"
                  wire:click="gotoPage({{ $page }})"
                  class="table-pagination-button {{ $page === $fechas->currentPage() ? 'table-pagination-button-active' : '' }}"
                >
                  {{ $page }}
                </button>
              @endforeach

              <button
                type="button"
                wire:click="nextPage"
                @disabled(! $fechas->hasMorePages())
                class="table-pagination-button {{ ! $fechas->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      @endif
    </article>
  </section>
</div>
