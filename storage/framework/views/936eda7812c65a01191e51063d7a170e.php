<div class="login-stage">
  <?php
    $loginLogo = file_exists(public_path('images/menu-logo.png'))
      ? asset('images/menu-logo.png')
      : null;
  ?>

  <div class="login-shell">
    <section class="login-welcome">
      <div class="login-welcome-content">
        <div class="login-welcome-brand">
          <div class="login-brand-badge">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loginLogo): ?>
              <img src="<?php echo e($loginLogo); ?>" alt="Correos de Bolivia" class="login-brand-badge-image">
            <?php else: ?>
              <span>CB</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <p class="login-brand-kicker">Recursos Humanos</p>
            <h1 class="login-brand-title">Bienvenido</h1>
          </div>
        </div>

        <p class="login-welcome-copy">
          Ingresa al sistema interno de recursos humanos de Correos de Bolivia para controlar asistencia,
          revisar personal y administrar la operacion diaria.
        </p>

        <span class="login-security-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="5" y="11" width="14" height="9" rx="2"/>
            <path d="M8 11V8a4 4 0 1 1 8 0v3"/>
          </svg>
          Acceso exclusivo para personal autorizado
        </span>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
          <div class="login-status-ok">
            <?php echo e(session('status')); ?>

          </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form wire:submit="login" class="login-form">
          <div>
            <label for="email" class="login-form-label">Usuario o correo</label>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <p class="form-error form-error-dark"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          <div>
            <div class="login-field-head">
              <label for="password" class="login-form-label !mb-0">Contrasena</label>
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
                type="<?php echo e($showPassword ? 'text' : 'password'); ?>"
                class="form-input login-input"
                placeholder="Ingresa tu contrasena"
                autocomplete="current-password"
              >
              <button type="button" wire:click="togglePassword" class="login-eye">
                <?php echo e($showPassword ? 'Ocultar' : 'Mostrar'); ?>

              </button>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <p class="form-error form-error-dark"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>

          <button type="submit" class="login-submit">
            <span>Ingresar</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="login-submit-icon">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </button>
        </form>
      </div>
    </section>

    <aside class="login-sidecard">
      <div class="login-sidecard-content">
        <p class="login-sidecard-kicker">Portal institucional</p>
        <h2 class="login-sidecard-title">Sistema de Recursos Humanos</h2>
        <p class="login-sidecard-copy">
          Consulta asistencia, seguimiento del personal, reportes operativos e informacion institucional
          desde una sola plataforma interna.
        </p>

        <ul class="login-feature-list">
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Control de asistencia en tiempo real</span>
          </li>
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Gestion centralizada de personal</span>
          </li>
          <li class="login-feature-item">
            <span class="login-feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span>Reportes y auditoria operativa</span>
          </li>
        </ul>

        <div class="login-sidecard-badge">
          <span class="login-sidecard-badge-dot"></span>
          <span>Correos de Bolivia</span>
        </div>
      </div>

      <p class="login-sidecard-footer">Terminal de operacion segura &middot; Seguridad AES-256</p>
    </aside>
  </div>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/auth/login.blade.php ENDPATH**/ ?>