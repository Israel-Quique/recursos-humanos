<div class="page-stack">
  <section class="surface-card">
    <p class="section-kicker">Organizacion interna</p>
    <h3 class="section-title">Estructura de Unidades de Asistencia</h3>
    <p class="section-copy-sm">Nivel jerarquico del sistema integrado y los centros de control habilitados.</p>

    <div class="org-chart">
      <div class="org-node org-node-main">
        <p class="org-node-kicker">{{ $structure['central']['label'] }}</p>
        <h4 class="org-node-title">{{ $structure['central']['title'] }}</h4>
      </div>

      <div class="org-connector"></div>

      <div class="org-branches">
        @foreach($structure['branches'] as $branch)
          <article class="org-node">
            <p class="org-node-kicker">{{ $branch['label'] }}</p>
            <h4 class="org-node-title">{{ $branch['title'] }}</h4>
            <p class="org-node-copy">{{ $branch['detail'] }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>
</div>
