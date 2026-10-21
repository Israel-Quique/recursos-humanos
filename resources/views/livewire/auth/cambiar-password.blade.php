<div class="login-stage">
  <section class="surface-card mx-auto max-w-xl p-8">
    <h1 class="section-title">Cambia tu contraseña</h1>
    <p class="section-copy-sm">Antes de consultar tus horas, reemplaza la contraseña inicial por una propia de al menos 8 caracteres.</p>
    <form wire:submit="guardar" class="mt-6 space-y-4">
      <div><label for="current-password" class="form-label">Contraseña actual</label><input id="current-password" type="password" wire:model="current_password" autocomplete="current-password" class="form-input" required>@error('current_password')<p class="form-error">{{ $message }}</p>@enderror</div>
      <div><label for="new-password" class="form-label">Nueva contraseña</label><input id="new-password" type="password" wire:model="password" autocomplete="new-password" class="form-input" required>@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
      <div><label for="confirm-password" class="form-label">Confirma la nueva contraseña</label><input id="confirm-password" type="password" wire:model="password_confirmation" autocomplete="new-password" class="form-input" required></div>
      <button class="login-submit" wire:loading.attr="disabled">Guardar contraseña</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button type="submit">Cerrar sesión</button></form>
  </section>
</div>
