<div class="page-stack">
  <section>
    <article class="surface-card surface-card-map">
      <p class="section-kicker">Mapa operativo</p>
      <h3 class="section-title">Asistencia por departamento</h3>
      <p class="section-copy-sm max-w-3xl">
        Haz clic sobre un departamento para ver cuantas personas ya marcaron y cuantas siguen en su puesto de trabajo.
      </p>

      <?php
        $initialDepartment = collect($departmentStats)->first();
      ?>

      <div class="bolivia-map-card" data-bolivia-map-root>
        <div class="bolivia-map-shell">
          <div class="bolivia-map-canvas" data-bolivia-map-canvas aria-label="Mapa de Bolivia por departamentos"></div>
          <script type="application/json" data-departments-json><?php echo json_encode($departmentStats, 15, 512) ?></script>
        </div>

        <aside class="department-bubble" data-department-bubble>
          <p class="department-bubble-kicker">Departamento seleccionado</p>
          <h4 class="department-bubble-title" data-department-name><?php echo e($initialDepartment['name']); ?></h4>
          <p class="department-bubble-copy" data-department-branch><?php echo e($initialDepartment['branch']); ?></p>
          <div class="department-bubble-grid">
            <div class="department-bubble-stat">
              <span>Marcaron</span>
              <strong data-department-marked><?php echo e($initialDepartment['marked']); ?></strong>
            </div>
            <div class="department-bubble-stat">
              <span>En puesto</span>
              <strong data-department-working><?php echo e($initialDepartment['working']); ?></strong>
            </div>
            <div class="department-bubble-stat">
              <span>Personal activo</span>
              <strong data-department-employees><?php echo e($initialDepartment['employees'] ?? 0); ?></strong>
            </div>
            <div class="department-bubble-stat department-bubble-stat-alert">
              <span>Sin marcar</span>
              <strong data-department-missing><?php echo e($initialDepartment['missing']); ?></strong>
            </div>
          </div>
        </aside>
      </div>
    </article>
  </section>
</div>
<?php /**PATH C:\Users\israe\OneDrive\Desktop\Pasantia\Proyectos\recursos-humanos\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>