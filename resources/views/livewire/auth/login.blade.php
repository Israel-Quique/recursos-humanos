<div class="login-stage">
  <div class="login-shell">
    <section class="login-welcome">
      <div class="login-welcome-bubble login-welcome-bubble-top"></div>
      <div class="login-welcome-bubble login-welcome-bubble-bottom"></div>

      <div class="login-welcome-content">
        <div class="login-welcome-brand">
          <div class="login-brand-badge">CB</div>
          <div>
            <p class="login-brand-kicker">Recursos Humanos</p>
            <h1 class="login-brand-title">Bienvenido</h1>
          </div>
        </div>

        <p class="login-welcome-copy">
          Ingresa al sistema interno de recursos humanos de Correos de Bolivia para controlar asistencia,
          revisar personal y administrar la operacion diaria.
        </p>

        @if (session('status'))
          <div class="login-status-ok">
            {{ session('status') }}
          </div>
        @endif

        <form wire:submit="login" class="mt-8 space-y-5">
          <div>
            <label for="email" class="form-label login-form-label">Usuario o correo</label>
            <div class="login-input-shell">
              <span class="login-input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                  <path d="M4 7.5 12 13l8-5.5"/>
                  <rect x="3" y="6" width="18" height="12" rx="2"/>
                </svg>
              </span>
              <input
                wire:model.live="email"
                id="email"
                type="text"
                class="form-input login-input"
                placeholder="admin"
                autocomplete="username"
              >
            </div>
            @error('email')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <div class="flex items-center justify-between gap-4">
              <label for="password" class="form-label login-form-label !mb-0">Contrasena</label>
              <span class="login-link-hint">Acceso interno seguro</span>
            </div>
            <div class="login-input-shell">
              <span class="login-input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                  <rect x="5" y="11" width="14" height="9" rx="2"/>
                  <path d="M8 11V8a4 4 0 1 1 8 0v3"/>
                </svg>
              </span>
              <input
                wire:model.live="password"
                id="password"
                type="{{ $showPassword ? 'text' : 'password' }}"
                class="form-input login-input"
                placeholder="Ingresa tu contrasena"
                autocomplete="current-password"
              >
              <button type="button" wire:click="togglePassword" class="login-eye">
                {{ $showPassword ? 'Ocultar' : 'Mostrar' }}
              </button>
            </div>
            @error('password')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <button type="submit" class="login-submit">
            <span>Ingresar</span>
          </button>

          <p class="login-forgot-copy">Acceso exclusivo para personal autorizado.</p>
        </form>
      </div>
    </section>

    <aside class="login-sidecard">
      <div class="login-sidecard-content">
        <p class="login-sidecard-kicker">Nuevo portal</p>
        <h2 class="login-sidecard-title">Sistema de Recursos Humanos</h2>
        <p class="login-sidecard-copy">
          Consulta asistencia, seguimiento del personal, reportes operativos e informacion institucional
          desde una sola plataforma interna.
        </p>

        <div class="login-sidecard-badge">
          <span class="login-sidecard-badge-dot"></span>
          <span>Correos de Bolivia</span>
        </div>
      </div>
    </aside>
  </div>
</div>
