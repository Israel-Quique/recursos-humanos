<div class="page-stack">
  @if ($showEditModal)
    <div class="app-modal-backdrop" wire:click="closeEditModal">
      <div class="app-modal-card" x-on:click.stop>
        <button type="button" wire:click="closeEditModal" class="app-modal-close app-modal-close-corner" aria-label="Cerrar modal">X</button>
        <div class="app-modal-head">
          <div>
            <p class="section-kicker">Horario regional</p>
            <h3 class="section-title app-modal-title">Actualizar entrada y salida</h3>
            <p class="section-copy-sm">Configura el horario general que se aplicara a todo el personal de la sucursal seleccionada.</p>
          </div>
        </div>

        <form wire:submit="saveHorario" class="mt-8 grid gap-5 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="form-label">Sucursal</label>
            <input type="text" value="{{ $editingSucursal }}" class="form-input" disabled>
          </div>
          <div>
            <label class="form-label">Hora de entrada</label>
            <input type="time" wire:model="editHoraEntrada" class="form-input">
            @error('editHoraEntrada') <p class="form-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="form-label">Hora de salida</label>
            <input type="time" wire:model="editHoraSalida" class="form-input">
            @error('editHoraSalida') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2 app-modal-actions">
            <button type="submit" class="login-submit app-modal-submit">Guardar horario</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <section>
    <article class="surface-card">
      <div class="section-head-row">
        <div>
          <p class="section-kicker">Gestion operativa</p>
          <h3 class="section-title">Horarios por sucursal</h3>
          <p class="section-copy-sm">Administra una sola hora de entrada y salida para todo el personal de cada regional o sucursal.</p>
        </div>
      </div>

      <div class="history-table-shell history-table-shell-personal">
        <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-1">
          <div class="space-y-2">
            <label for="horario-search" class="form-label">Buscar por sucursal</label>
            <input
              id="horario-search"
              type="text"
              wire:model.live.debounce.300ms="search"
              class="form-input"
              placeholder="Escribe una sucursal o regional"
              autocomplete="off"
            >
          </div>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Sucursal</th>
              <th>Personal asignado</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($horarios as $horario)
              <tr>
                <td>{{ $horario->sucursal }}</td>
                <td>{{ $horario->empleados }}</td>
                <td>{{ $horario->hora_entrada }}</td>
                <td>{{ $horario->hora_salida }}</td>
                <td>
                  <button type="button" wire:click="openEditModal('{{ str_replace("'", "\\'", $horario->sucursal) }}')" class="table-action-button">Modificar horario</button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-slate-400">No hay sucursales disponibles para configurar horarios.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($horarios->hasPages())
        <div class="table-pagination-shell">
          <div class="table-pagination-bar">
            <p class="table-pagination-copy">
              Mostrando {{ $horarios->firstItem() }} a {{ $horarios->lastItem() }} de {{ $horarios->total() }} registros
            </p>

            <div class="table-pagination-actions">
              <button
                type="button"
                wire:click="previousPage"
                @disabled($horarios->onFirstPage())
                class="table-pagination-button {{ $horarios->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
              >
                Anterior
              </button>

              @foreach (range(max(1, $horarios->currentPage() - 2), min($horarios->lastPage(), $horarios->currentPage() + 2)) as $page)
                <button
                  type="button"
                  wire:click="gotoPage({{ $page }})"
                  class="table-pagination-button {{ $page === $horarios->currentPage() ? 'table-pagination-button-active' : '' }}"
                >
                  {{ $page }}
                </button>
              @endforeach

              <button
                type="button"
                wire:click="nextPage"
                @disabled(! $horarios->hasMorePages())
                class="table-pagination-button {{ ! $horarios->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
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
