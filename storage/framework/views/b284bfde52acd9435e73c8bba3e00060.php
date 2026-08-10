<div class="login-stage">
  <?php
    $loginLogo = file_exists(public_path('images/menu-logo.png'))
      ? asset('images/menu-logo.png')
      : null;
  ?>

  <div class="login-shell">
    <section class="login-welcome login-welcome-public">
      <div class="login-welcome-content login-welcome-content-public">
        <div class="login-public-stack">
          <div class="login-public-hero">
            <div class="login-brand-badge">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loginLogo): ?>
                <img src="<?php echo e($loginLogo); ?>" alt="Correos de Bolivia" class="login-brand-badge-image">
              <?php else: ?>
                <span>CB</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="login-public-hero-copy">
              <p class="login-brand-kicker">Consulta de asistencia</p>
              <h1 class="login-brand-title login-brand-title-public">Buscar por carnet</h1>
              <p class="login-welcome-copy login-welcome-copy-public">
                Accede a tu resumen mensual de asistencia escribiendo tu carnet o codigo biometrico.
              </p>
            </div>
          </div>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="login-status-ok">
              <?php echo e(session('status')); ?>

            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <div class="login-public-card">
            <form wire:submit="buscar" class="login-public-form">
              <label for="carnet" class="login-form-label">Carnet o codigo</label>
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
                  placeholder="Ingresa tu carnet"
                  autocomplete="off"
                >
              </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['carnet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="form-error form-error-dark"><?php echo e($message); ?></p>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

              <button type="submit" class="login-submit mt-4">
                <span>Buscar</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="login-submit-icon">
                  <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
              </button>
            </form>
            <div class="login-public-divider"></div>

            <div class="login-public-benefits">
              <article class="login-public-benefit">
                <strong>Consulta directa</strong>
                <span>Sin ingresar al panel administrativo.</span>
              </article>
              <article class="login-public-benefit">
                <strong>Resultado mensual</strong>
                <span>Horas, faltas y no marcados en una sola vista.</span>
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
            <span>Consulta rapida por carnet</span>
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

      <p class="login-sidecard-footer">Terminal de operacion segura &middot; Seguridad AES-256</p>
    </aside>
  </div>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/consulta-carnet.blade.php ENDPATH**/ ?>