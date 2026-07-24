<div class="page-stack">
  <section class="surface-card">
    <div class="section-head-row">
      <div>
        <p class="section-kicker">Control administrativo</p>
        <h3 class="section-title">Auditoria del sistema</h3>
        <p class="section-copy-sm">Revisa quien creo, edito, elimino o cambio accesos dentro del sistema.</p>
      </div>
    </div>

    <div class="history-table-shell history-table-shell-personal">
      <div class="mb-6 grid gap-4 px-6 pt-5 md:grid-cols-4">
        <div class="space-y-2">
          <label class="form-label">Buscar</label>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Modulo, actor o descripcion">
        </div>
        <div class="space-y-2">
          <label class="form-label">Modulo</label>
          <select wire:model.live="moduloFiltro" class="form-input">
            <option value="">Todos</option>
            @foreach ($modulos as $modulo)
              <option value="{{ $modulo }}">{{ $modulo }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Accion</label>
          <select wire:model.live="accionFiltro" class="form-input">
            <option value="">Todas</option>
            @foreach ($acciones as $accion)
              <option value="{{ $accion }}">{{ \Illuminate\Support\Str::headline($accion) }}</option>
            @endforeach
          </select>
        </div>
        <div class="space-y-2">
          <label class="form-label">Usuario</label>
          <select wire:model.live="actorFiltro" class="form-input">
            <option value="">Todos</option>
            @foreach ($actores as $actor)
              <option value="{{ $actor->id }}">{{ $actor->name }} - {{ $actor->email }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <table class="history-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Modulo</th>
            <th>Accion</th>
            <th>Descripcion</th>
            <th>Detalle</th>
            <th>Control</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($auditorias as $auditoria)
            <tr>
              <td>{{ $auditoria->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
              <td>{{ $auditoria->actor?->name ?? 'Sistema' }}</td>
              <td>{{ $auditoria->modulo }}</td>
              <td>
                <span class="status-badge {{ $auditoria->accion === 'eliminar' ? 'status-danger' : ($auditoria->accion === 'editar' || $auditoria->accion === 'cambiar_rol' ? 'status-warning' : 'status-info') }}">
                  {{ \Illuminate\Support\Str::headline($auditoria->accion) }}
                </span>
              </td>
              <td>{{ $auditoria->descripcion }}</td>
              <td class="audit-detail-cell">
                @if ($auditoria->antes)
                  <div class="audit-detail-block">
                    <strong>Antes:</strong>
                    <pre>{{ json_encode($auditoria->antes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                  </div>
                @endif
                @if ($auditoria->despues)
                  <div class="audit-detail-block">
                    <strong>Despues:</strong>
                    <pre>{{ json_encode($auditoria->despues, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                  </div>
                @endif
              </td>
              <td class="table-actions-cell audit-control-cell">
                <div class="table-actions-group">
                  @if ($this->canUndo($auditoria))
                    <button type="button" wire:click="deshacerAccion({{ $auditoria->id }})" class="table-action-button table-action-button-danger">
                      Deshacer
                    </button>
                  @endif
                  @if ($this->canRedo($auditoria))
                    <button type="button" wire:click="rehacerAccion({{ $auditoria->id }})" class="table-action-button">
                      Rehacer
                    </button>
                  @endif
                  @if (! $this->canUndo($auditoria) && ! $this->canRedo($auditoria))
                    <span class="text-xs text-slate-400">Sin accion</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-slate-400">Todavia no hay registros de auditoria.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($auditorias->hasPages())
      <div class="table-pagination-shell">
        <div class="table-pagination-bar">
          <p class="table-pagination-copy">
            Mostrando {{ $auditorias->firstItem() }} a {{ $auditorias->lastItem() }} de {{ $auditorias->total() }} registros
          </p>

          <div class="table-pagination-actions">
            <button
              type="button"
              wire:click="previousPage"
              @disabled($auditorias->onFirstPage())
              class="table-pagination-button {{ $auditorias->onFirstPage() ? 'table-pagination-button-disabled' : '' }}"
            >
              Anterior
            </button>

            @foreach (range(max(1, $auditorias->currentPage() - 2), min($auditorias->lastPage(), $auditorias->currentPage() + 2)) as $page)
              <button
                type="button"
                wire:click="gotoPage({{ $page }})"
                class="table-pagination-button {{ $page === $auditorias->currentPage() ? 'table-pagination-button-active' : '' }}"
              >
                {{ $page }}
              </button>
            @endforeach

            <button
              type="button"
              wire:click="nextPage"
              @disabled(! $auditorias->hasMorePages())
              class="table-pagination-button {{ ! $auditorias->hasMorePages() ? 'table-pagination-button-disabled' : '' }}"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    @endif
  </section>
</div>
