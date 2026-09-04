@php
  $retrasoResumen = $personalReport['retraso_resumen'] ?? [
    'total_minutos'      => 0,
    'total_formateado'   => '0 min',
    'dias_tarde'         => 0,
    'tolerancia_minutos' => 30,
    'exceso_minutos'     => 0,
    'exceso_formateado'  => '0 min',
    'excedio_tolerancia' => false,
    'porcentaje_uso'     => 0,
  ];
  $excedio     = $retrasoResumen['excedio_tolerancia'];
  $pct         = min(100, $retrasoResumen['porcentaje_uso']);
  $totalRows   = count($personalReport['rows'] ?? []);
  $emp         = $personalReport['empleado'];
  $initials    = strtoupper(substr($emp['nombre'] ?? 'E', 0, 2));
@endphp

<div>
<style>
/* ─── PERFIL STANDALONE ───────────────────────────────── */
.ph-root {
  min-height: 100vh;
  background:
    radial-gradient(ellipse at 0% 0%, rgba(15,103,192,.10) 0%, transparent 55%),
    radial-gradient(ellipse at 100% 100%, rgba(16,185,129,.08) 0%, transparent 50%),
    #f1f5f9;
  font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
}

/* top nav bar */
.ph-topbar {
  position: sticky;
  top: 0;
  z-index: 30;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: .75rem 2rem;
  background: rgba(255,255,255,.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(226,232,240,.8);
  box-shadow: 0 1px 3px rgba(0,0,0,.05);
}

.ph-topbar-brand {
  display: flex;
  align-items: center;
  gap: .625rem;
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: #0f67c0;
}

.ph-topbar-brand-dot {
  width: .5rem; height: .5rem;
  border-radius: 9999px;
  background: #0f67c0;
  box-shadow: 0 0 0 4px rgba(15,103,192,.15);
}

.ph-btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .45rem .9rem;
  border-radius: .7rem;
  border: 1px solid #e2e8f0;
  background: #fff;
  font-size: .75rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all .15s;
}
.ph-btn-ghost:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }

.ph-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .45rem 1rem;
  border-radius: .7rem;
  background: #0f67c0;
  font-size: .75rem;
  font-weight: 700;
  color: #fff;
  border: none;
  cursor: pointer;
  transition: background .15s;
}
.ph-btn-primary:hover { background: #0d58a4; }

/* ─── WRAPPER ─────────────────────────────────────────── */
.ph-wrapper {
  max-width: 1440px;
  margin: 0 auto;
  padding: 2rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

/* ─── HERO ROW: empleado + KPIs ─────────────────────── */
.ph-hero {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}
@media(min-width:960px) {
  .ph-hero { grid-template-columns: minmax(0,1fr) auto; align-items: stretch; }
}

.ph-emp-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1.5rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 4px 24px rgba(15,23,42,.06);
}

.ph-emp-top {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
}

.ph-avatar {
  width: 3.5rem; height: 3.5rem;
  border-radius: 1rem;
  background: linear-gradient(135deg, #0f67c0, #38bdf8);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  font-weight: 800;
  color: #fff;
  flex-shrink: 0;
  box-shadow: 0 6px 18px rgba(15,103,192,.25);
}

.ph-emp-name {
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.25;
}

.ph-emp-meta {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  margin-top: .35rem;
}

.ph-tag {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .25rem .7rem;
  border-radius: 9999px;
  font-size: .72rem;
  font-weight: 600;
  border: 1px solid;
}
.ph-tag-blue  { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.ph-tag-slate { background:#f8fafc; color:#475569; border-color:#e2e8f0; font-family:ui-monospace,monospace; }
.ph-tag-green { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.ph-tag-violet{ background:#faf5ff; color:#6d28d9; border-color:#ddd6fe; }

.ph-emp-bottom {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  flex-wrap: wrap;
  padding-top: .85rem;
  border-top: 1px solid #f1f5f9;
}

.ph-month-label {
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: #94a3b8;
  margin-bottom: .3rem;
}
.ph-select {
  appearance: none;
  padding: .5rem 2.25rem .5rem .85rem;
  border-radius: .75rem;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2394a3b8' d='M1 1l5 5 5-5'/%3E%3C/svg%3E") no-repeat right .7rem center;
  font-size: .82rem;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  transition: border-color .15s;
}
.ph-select:focus { outline: none; border-color: #0f67c0; }

/* right side panel */
.ph-right-col {
  display: flex;
  flex-direction: column;
  justify-content: stretch;
}

/* ─── KPI ROW: 2 mini cards ─────────────────────────── */
.ph-kpi-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(160px, 185px));
  gap: 1rem;
  height: 100%;
}
@media(max-width:639px) {
  .ph-kpi-row { grid-template-columns: 1fr 1fr; }
}

.ph-kpi {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1.5rem;
  padding: 1.25rem 1.35rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: .35rem;
  box-shadow: 0 4px 24px rgba(15,23,42,.06);
  position: relative;
  overflow: hidden;
  transition: transform .15s, box-shadow .15s;
}
.ph-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,23,42,.09); }

.ph-kpi::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  border-radius: 1.5rem 1.5rem 0 0;
}
.ph-kpi-blue::before   { background: #0f67c0; }
.ph-kpi-violet::before { background: #7c3aed; }
.ph-kpi-amber::before  { background: #d97706; }
.ph-kpi-rose::before   { background: #e11d48; }
.ph-kpi-green::before  { background: #059669; }
.ph-kpi-slate::before  { background: #64748b; }

.ph-kpi-label {
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: #64748b;
  white-space: nowrap;
}
.ph-kpi-value {
  font-size: 1.6rem;
  font-weight: 800;
  line-height: 1.2;
}
.ph-kpi-sub {
  font-size: .74rem;
  color: #94a3b8;
  font-weight: 600;
  white-space: nowrap;
}
}

/* ─── RETRASO PANEL ───────────────────────────────────── */
.ph-delay-panel {
  border-radius: 1.5rem;
  padding: 0;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(15,23,42,.08);
  display: grid;
  grid-template-columns: 1fr;
}
@media(min-width:900px) { .ph-delay-panel { grid-template-columns: 1fr 1fr; } }

.ph-delay-left {
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  gap: .85rem;
  justify-content: center;
}

.ph-delay-left-ok   { background: linear-gradient(135deg, #f0fdf4, #eff6ff); border: 1px solid #bbf7d0; border-right: none; }
.ph-delay-left-warn { background: linear-gradient(135deg, #fff1f2, #fef9c3); border: 1px solid #fecdd3; border-right: none; }

.ph-delay-badge {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .3rem .85rem;
  border-radius: 9999px;
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: .06em;
  text-transform: uppercase;
  align-self: flex-start;
}
.ph-delay-badge-ok   { background: #059669; color: #fff; }
.ph-delay-badge-warn { background: #e11d48; color: #fff; }

.ph-delay-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.25; }
.ph-delay-desc  { font-size: .82rem; line-height: 1.6; color: #475569; }
.ph-delay-desc strong { color: #0f172a; }
.ph-delay-desc .warn-accent { color: #e11d48; font-weight: 800; }

/* progress bar */
.ph-progress-wrap { margin-top: .75rem; }
.ph-progress-head { display: flex; justify-content: space-between; font-size: .72rem; font-weight: 600; color: #64748b; margin-bottom: .35rem; }
.ph-progress-track { height: .625rem; background: #e2e8f0; border-radius: 9999px; overflow: hidden; }
.ph-progress-bar { height: 100%; border-radius: 9999px; transition: width .5s cubic-bezier(.4,0,.2,1); }
.ph-progress-ok   { background: linear-gradient(90deg, #059669, #34d399); }
.ph-progress-mid  { background: linear-gradient(90deg, #d97706, #fbbf24); }
.ph-progress-warn { background: linear-gradient(90deg, #d97706, #e11d48); }

/* right side: 4 mini-stats */
.ph-delay-right {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}

.ph-delay-right-ok   { background: #fff; border: 1px solid #e2e8f0; border-left: none; }
.ph-delay-right-warn { background: #fff; border: 1px solid #fecdd3; border-left: none; }

.ph-delay-stat {
  padding: 1.4rem 1.25rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: .2rem;
  border: 1px solid #f1f5f9;
  position: relative;
}
.ph-delay-stat-label {
  font-size: .68rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: #94a3b8;
}
.ph-delay-stat-val {
  font-size: 1.4rem;
  font-weight: 800;
  line-height: 1.1;
}
.ph-delay-stat-val-orange { color: #d97706; }
.ph-delay-stat-val-red    { color: #e11d48; }
.ph-delay-stat-val-slate  { color: #475569; }
.ph-delay-stat-val-blue   { color: #0f67c0; }
.ph-delay-stat-unit { font-size: .7rem; color: #94a3b8; font-weight: 500; }

/* ─── METRICS GRID (6-up) ─────────────────────────────── */
.ph-metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: .85rem;
}
@media(min-width:640px)  { .ph-metrics-grid { grid-template-columns: repeat(3, 1fr); } }
@media(min-width:1024px) { .ph-metrics-grid { grid-template-columns: repeat(6, 1fr); } }

.ph-metric {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1.1rem;
  padding: 1rem 1.1rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(15,23,42,.04);
}
.ph-metric-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
.ph-metric-value { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: .2rem; }

/* ─── TABLE SECTION ───────────────────────────────────── */
.ph-table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1.5rem;
  box-shadow: 0 4px 24px rgba(15,23,42,.06);
  overflow: hidden;
}

.ph-table-head {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.4rem 1.6rem 0;
}
@media(min-width:768px) {
  .ph-table-head { flex-direction: row; align-items: flex-start; justify-content: space-between; }
}

.ph-table-kicker { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #0f67c0; }
.ph-table-title  { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: .15rem; }

.ph-count-pill {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .3rem .8rem;
  border-radius: 9999px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  font-size: .72rem;
  font-weight: 700;
  color: #475569;
  white-space: nowrap;
  align-self: flex-start;
}

/* Filter bar */
.ph-filter-bar {
  display: flex;
  flex-direction: column;
  gap: .75rem;
  padding: 1rem 1.6rem;
  border-bottom: 1px solid #f1f5f9;
}
@media(min-width:1024px) { .ph-filter-bar { flex-direction: row; align-items: center; justify-content: space-between; } }

.ph-filter-btns { display: flex; flex-wrap: wrap; gap: .45rem; }

.ph-fbtn {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .38rem .85rem;
  border-radius: .65rem;
  font-size: .72rem;
  font-weight: 700;
  border: 1.5px solid;
  cursor: pointer;
  transition: all .15s;
  line-height: 1;
}
.ph-fbtn-all     { background:#f8fafc; border-color:#e2e8f0; color:#475569; }
.ph-fbtn-all.active, .ph-fbtn-all:hover   { background:#0f67c0; border-color:#0f67c0; color:#fff; }

.ph-fbtn-late    { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.ph-fbtn-late.active, .ph-fbtn-late:hover { background:#d97706; border-color:#d97706; color:#fff; }

.ph-fbtn-miss    { background:#fff7ed; border-color:#fed7aa; color:#9a3412; }
.ph-fbtn-miss.active, .ph-fbtn-miss:hover { background:#ea580c; border-color:#ea580c; color:#fff; }

.ph-fbtn-absent  { background:#fff1f2; border-color:#fecdd3; color:#9f1239; }
.ph-fbtn-absent.active, .ph-fbtn-absent:hover { background:#e11d48; border-color:#e11d48; color:#fff; }

.ph-fbtn-ok      { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
.ph-fbtn-ok.active, .ph-fbtn-ok:hover  { background:#059669; border-color:#059669; color:#fff; }

.ph-filter-right { display: flex; gap: .5rem; align-items: center; }

.ph-search-field {
  position: relative;
  flex: 1;
}
.ph-search-field input {
  width: 100%;
  padding: .45rem .75rem .45rem 2.2rem;
  border-radius: .7rem;
  border: 1.5px solid #e2e8f0;
  font-size: .78rem;
  color: #1e293b;
  background: #f8fafc;
  transition: border-color .15s;
  min-width: 13rem;
}
.ph-search-field input:focus { outline: none; border-color: #0f67c0; background: #fff; }
.ph-search-field svg { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 1rem; height: 1rem; }

.ph-sort-btn {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .45rem .8rem;
  border-radius: .7rem;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  font-size: .72rem;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
  transition: all .15s;
  white-space: nowrap;
}
.ph-sort-btn:hover { background: #fff; border-color: #0f67c0; color: #0f67c0; }

/* TABLE */
.ph-table-wrap { overflow-x: auto; }

.ph-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .8rem;
}

.ph-table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.ph-table th {
  padding: .85rem 1rem;
  font-size: .65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: #64748b;
  text-align: left;
  white-space: nowrap;
}
.ph-table th.center { text-align: center; }
.ph-table th.th-delay { background: #fef3c7; color: #78350f; border-left: 2px solid #fde68a; border-right: 2px solid #fde68a; }
.ph-table th.th-estado { background: #eff6ff; color: #1e40af; border-right: 2px solid #bfdbfe; }

.ph-table td {
  padding: .75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  white-space: nowrap;
}
.ph-table td.center { text-align: center; }
.ph-table td.td-delay  { background: #fffbeb; border-left: 2px solid #fde68a; border-right: 2px solid #fde68a; text-align: center; }
.ph-table td.td-estado { background: #eff6ff44; border-right: 2px solid #bfdbfe; text-align: center; }

/* ROW TONES */
.ph-row-ok     { }
.ph-row-ok:hover { background: #f8fafc; }
.ph-row-late   { background: #fffbeb; }
.ph-row-late:hover { background: #fef9c3; }
.ph-row-miss   { background: #fff7ed; }
.ph-row-miss:hover { background: #ffedd5; }
.ph-row-absent { background: #fff1f2; }
.ph-row-absent:hover { background: #ffe4e6; }

/* Left accent bar via box-shadow on first td */
.ph-row-ok td:first-child     { box-shadow: inset 3px 0 0 #10b981; }
.ph-row-late td:first-child   { box-shadow: inset 3px 0 0 #d97706; }
.ph-row-miss td:first-child   { box-shadow: inset 3px 0 0 #ea580c; }
.ph-row-absent td:first-child { box-shadow: inset 3px 0 0 #e11d48; }

/* day badge */
.ph-day-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem; height: 1.35rem;
  border-radius: .35rem;
  font-size: .65rem;
  font-weight: 800;
  background: #e2e8f0;
  color: #475569;
  flex-shrink: 0;
}

/* time chips */
.ph-time {
  display: inline-block;
  padding: .2rem .55rem;
  border-radius: .45rem;
  font-family: ui-monospace, monospace;
  font-size: .78rem;
  font-weight: 700;
}
.ph-time-in-ok   { background: #dcfce7; color: #166534; }
.ph-time-in-late { background: #fef3c7; color: #78350f; }
.ph-time-out     { background: #eff6ff; color: #1e40af; }
.ph-time-none    { color: #f43f5e; font-size: .75rem; }

/* delay badge */
.ph-delay-chip {
  display: inline-flex;
  align-items: center;
  gap: .25rem;
  padding: .22rem .65rem;
  border-radius: 9999px;
  font-size: .72rem;
  font-weight: 800;
}
.ph-delay-chip-val  { background: linear-gradient(90deg,#d97706,#ef4444); color: #fff; }
.ph-delay-chip-zero { background: #dcfce7; color: #166534; }

/* estado badge */
.ph-estado {
  display: inline-flex;
  align-items: center;
  gap: .25rem;
  padding: .22rem .65rem;
  border-radius: 9999px;
  font-size: .7rem;
  font-weight: 700;
}
.ph-estado-ok     { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.ph-estado-late   { background: #fef3c7; color: #78350f; border: 1px solid #fde68a; }
.ph-estado-miss   { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.ph-estado-absent { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }

/* table footer */
.ph-table tfoot tr { background: #f8fafc; border-top: 2px solid #e2e8f0; }
.ph-table tfoot td { padding: .75rem 1rem; font-size: .75rem; font-weight: 700; color: #374151; }

/* PRINT */
@media print {
  .ph-topbar, .ph-filter-bar, .ph-sort-btn, .no-print { display: none !important; }
  .ph-root { background: #fff; }
  .ph-table-card, .ph-emp-card, .ph-delay-panel, .ph-kpi { box-shadow: none; border: 1px solid #e5e7eb; }
}

/* DROPZONE COMPROBANTE */
.ph-dropzone {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  min-height: 230px !important;
  background: #ffffff !important;
  border: 2.5px dashed #6366f1 !important;
  border-radius: 1.25rem !important;
  padding: 2.5rem 1.5rem !important;
  text-align: center !important;
  cursor: pointer !important;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.08) !important;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-sizing: border-box !important;
}
.ph-dropzone:hover {
  border-color: #4338ca !important;
  background: #f8faff !important;
  box-shadow: 0 8px 24px rgba(79, 70, 229, 0.16) !important;
  transform: translateY(-2px) !important;
}
.ph-dropzone-active {
  border-color: #3730a3 !important;
  background: #eef2ff !important;
  box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.3) !important;
  transform: scale(1.02) !important;
}
</style>

<div class="ph-root">
  {{-- ── TOP NAV BAR ─────────────────────────────────────────── --}}
  <div class="ph-topbar no-print">
    <div class="ph-topbar-brand">
      <span class="ph-topbar-brand-dot"></span>
      Portal de Asistencia · Consulta por Carnet
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;">
      <button wire:click="abrirBoletaModal" type="button" class="ph-btn-primary" style="background:#0f67c0;color:#fff;font-weight:700;box-shadow:0 2px 4px rgba(15,103,192,0.25);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>📄 Generar Boleta</span>
      </button>
      <a
        href="{{ route('consulta-carnet') }}"
        onclick="if(window.history.length>1){event.preventDefault();window.history.back();}"
        class="ph-btn-ghost"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
        Volver
      </a>
      <button onclick="window.print()" type="button" class="ph-btn-ghost">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Imprimir
      </button>
    </div>
  </div>

  <div class="ph-wrapper">

    @if (session('status'))
      <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:.75rem 1.25rem;border-radius:.75rem;font-size:.82rem;font-weight:700;">
        {{ session('status') }}
      </div>
    @endif

    {{-- ── HERO: EMPLEADO + BÚSQUEDA ──────────────────────────── --}}
    <div class="ph-hero">

      {{-- Tarjeta empleado --}}
      <div class="ph-emp-card">
        <div class="ph-emp-top">
          <div class="ph-avatar">{{ $initials }}</div>
          <div style="flex:1; min-width:0;">
            <div class="ph-emp-name">{{ $emp['nombre'] }}</div>
            <div class="ph-emp-meta">
              <span class="ph-tag ph-tag-slate">🪪 {{ $emp['codigo'] }}</span>
              <span class="ph-tag ph-tag-blue">🏢 {{ $emp['sucursal'] }}</span>
              <span class="ph-tag ph-tag-green">⏰ {{ $emp['horario'] }}</span>
              @if(!empty($emp['cargo']))
                <span class="ph-tag ph-tag-violet">💼 {{ $emp['cargo'] }}</span>
              @endif
              @if(!empty($emp['email']))
                <span class="ph-tag ph-tag-slate" title="Correo para notificaciones (Solo modificable por Administración)">✉️ {{ $emp['email'] }}</span>
              @else
                <span class="ph-tag ph-tag-amber" title="Sin correo registrado">✉️ Sin correo registrado</span>
              @endif
            </div>
          </div>
        </div>
        <div class="ph-emp-bottom">
          <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div>
              <div class="ph-month-label">Mes de consulta</div>
              <select id="shared-profile-month" wire:model.live="referenceMonth" class="ph-select">
                @foreach($monthOptions as $option)
                  <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
              </select>
            </div>
            <button wire:click="abrirBoletaModal" type="button" class="ph-btn-ghost" style="color:#0f67c0;border-color:#bfdbfe;background:#eff6ff;font-weight:700;margin-top:.85rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              <span>Generar Boleta Oficial</span>
            </button>
          </div>
          <div style="font-size:.78rem;color:#64748b;">
            <strong style="color:#0f172a;">{{ $filteredCount }}</strong> de <strong style="color:#0f172a;">{{ $totalRows }}</strong> registros mostrados
          </div>
        </div>
      </div>

      {{-- Panel derecho: solo KPIs rápidos --}}
      <div class="ph-right-col">
        {{-- Mini KPIs rápidos --}}
        <div class="ph-kpi-row">
          <div class="ph-kpi ph-kpi-amber">
            <div class="ph-kpi-label">Retraso total</div>
            <div class="ph-kpi-value" style="color:{{ $retrasoResumen['total_minutos'] > 0 ? '#d97706' : '#059669' }};">
              {{ $retrasoResumen['total_formateado'] }}
            </div>
            <div class="ph-kpi-sub">{{ $retrasoResumen['dias_tarde'] }} día(s) tarde</div>
          </div>
          <div class="ph-kpi {{ $excedio ? 'ph-kpi-rose' : 'ph-kpi-green' }}">
            <div class="ph-kpi-label">Exceso / Margen</div>
            <div class="ph-kpi-value" style="color:{{ $excedio ? '#e11d48' : '#059669' }};">
              {{ $excedio ? $retrasoResumen['exceso_formateado'] : '✓ OK' }}
            </div>
            <div class="ph-kpi-sub">Tolerancia: {{ $retrasoResumen['tolerancia_minutos'] }} min</div>
          </div>
        </div>
      </div>
    </div>


    {{-- ── MÉTRICAS GENERALES (6 recuadros) ────────────────────── --}}
    <div class="ph-metrics-grid">
      @foreach($personalReport['metrics'] as $i => $metric)
        @php
          $colors = ['#0f67c0','#7c3aed','#d97706','#e11d48','#059669','#475569'];
          $col = $colors[$i] ?? '#0f67c0';
        @endphp
        <div class="ph-metric" style="border-top:3px solid {{ $col }};">
          <div class="ph-metric-label">{{ $metric['label'] }}</div>
          <div class="ph-metric-value" style="color:{{ $col }};">{{ $metric['value'] }}</div>
        </div>
      @endforeach
    </div>

    {{-- ── TABLA DE MARCACIONES ─────────────────────────────────── --}}
    <div class="ph-table-card">

      {{-- Head --}}
      <div class="ph-table-head">
        <div>
          <div class="ph-table-kicker">Detalle diario de asistencia</div>
          <div class="ph-table-title">Marcaciones de {{ $monthLabel }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
          <span class="ph-count-pill">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            {{ $filteredCount }} de {{ $totalRows }}
          </span>
        </div>
      </div>

      {{-- Filtros --}}
      <div class="ph-filter-bar no-print">
        <div class="ph-filter-btns">
          <button wire:click="setFilterState('todos')" class="ph-fbtn ph-fbtn-all {{ $filterState==='todos' ? 'active' : '' }}">
            Todos ({{ $totalRows }})
          </button>
          <button wire:click="setFilterState('retrasos')" class="ph-fbtn ph-fbtn-late {{ $filterState==='retrasos' ? 'active' : '' }}">
            ⏱ Retrasos
          </button>
          <button wire:click="setFilterState('omisiones')" class="ph-fbtn ph-fbtn-miss {{ $filterState==='omisiones' ? 'active' : '' }}">
            ⚠ Omisiones
          </button>
          <button wire:click="setFilterState('faltas')" class="ph-fbtn ph-fbtn-absent {{ $filterState==='faltas' ? 'active' : '' }}">
            ✕ Faltas
          </button>
          <button wire:click="setFilterState('puntuales')" class="ph-fbtn ph-fbtn-ok {{ $filterState==='puntuales' ? 'active' : '' }}">
            ✓ Puntuales
          </button>
        </div>

        <div class="ph-filter-right">
          <div class="ph-search-field">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input
              type="text"
              wire:model.live.debounce.250ms="searchQuery"
              placeholder="Buscar fecha, estado…"
            />
          </div>
          <button wire:click="toggleSortDirection" class="ph-sort-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
            {{ $sortDirection === 'asc' ? '1 → 31' : '31 → 1' }}
          </button>
        </div>
      </div>

      {{-- Tabla --}}
      <div class="ph-table-wrap">
        <table class="ph-table">
          <thead>
            <tr>
              <th>Fecha / Día</th>
              <th class="center">Horario</th>
              <th class="center">Entrada</th>
              <th class="center">Salida</th>
              <th class="center">Horas</th>
              <th class="center th-delay">⏱ RETRASO</th>
              <th class="center th-estado">ESTADO</th>
              <th>Biométrico</th>
              <th class="center" style="width: 140px;">Acción Boleta</th>
            </tr>
          </thead>
          <tbody>
            @forelse($filteredRows as $row)
              @php
                $tone      = $row['row_tone']      ?? 'default';
                $retMin    = $row['retraso_minutos'] ?? 0;
                $esFalta   = $row['es_falta']       ?? false;
                $esOmision = $row['es_omision']     ?? false;
                $esRetraso = $row['es_retraso']     ?? false;

                $trClass = match($tone) {
                  'danger'  => 'ph-row-absent',
                  'warning' => 'ph-row-miss',
                  'late'    => 'ph-row-late',
                  default   => 'ph-row-ok',
                };

                $inClass = $retMin > 0 ? 'ph-time-in-late' : 'ph-time-in-ok';
              @endphp
              <tr class="{{ $trClass }}">
                {{-- Fecha / Día --}}
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem;">
                    @if(!empty($row['dia_semana']))
                      <span class="ph-day-badge">{{ $row['dia_semana'] }}</span>
                    @endif
                    <span style="font-weight:700;color:#1e293b;">{{ $row['fecha'] }}</span>
                  </div>
                </td>

                {{-- Horario --}}
                <td class="center" style="font-family:ui-monospace,monospace;font-size:.73rem;color:#64748b;">
                  {{ $row['horario_programado'] ?? $emp['horario'] }}
                </td>

                {{-- Entrada --}}
                <td class="center">
                  @if($row['entrada'] !== '--:--')
                    <span class="ph-time {{ $inClass }}">{{ $row['entrada'] }}</span>
                  @else
                    <span class="ph-time-none" style="font-family:ui-monospace,monospace;font-weight:700;">--:--</span>
                  @endif
                </td>

                {{-- Salida --}}
                <td class="center">
                  @if($row['salida'] !== '--:--')
                    <span class="ph-time ph-time-out">{{ $row['salida'] }}</span>
                  @else
                    <span class="ph-time-none" style="font-family:ui-monospace,monospace;font-weight:700;">--:--</span>
                  @endif
                </td>

                {{-- Horas --}}
                <td class="center" style="font-weight:600;color:#374151;">{{ $row['horas'] }}</td>

                {{-- Retraso --}}
                <td class="td-delay">
                  @if($retMin > 0)
                    <span class="ph-delay-chip ph-delay-chip-val">{{ $row['retraso'] }}</span>
                  @else
                    <span class="ph-delay-chip ph-delay-chip-zero">0 min</span>
                  @endif
                </td>

                {{-- Estado --}}
                <td class="td-estado">
                  @if($tone === 'danger')
                    <span class="ph-estado ph-estado-absent">✕ {{ $row['estado'] }}</span>
                  @elseif($tone === 'warning')
                    <span class="ph-estado ph-estado-miss">⚠ {{ $row['estado'] }}</span>
                  @elseif($tone === 'late')
                    <span class="ph-estado ph-estado-late">⏱ {{ $row['estado'] }}</span>
                  @else
                    <span class="ph-estado ph-estado-ok">✓ {{ $row['estado'] }}</span>
                  @endif
                </td>

                {{-- Biométrico --}}
                <td>
                  <div style="font-size:.77rem;font-weight:600;color:#334155;">{{ $row['estado_biometrico'] }}</div>
                  <div style="font-size:.68rem;color:#94a3b8;margin-top:.1rem;">{{ $row['evento_biometrico'] }}</div>
                </td>

                {{-- Acción Boleta / Justificación --}}
                <td class="center" style="vertical-align:middle;padding:.5rem .75rem;">
                  @if($esOmision || $filterState === 'omisiones')
                    <button
                      type="button"
                      wire:click="abrirBoletaParaOmision('{{ $row['fecha'] }}', '{{ $row['entrada'] }}', '{{ $row['salida'] }}', '{{ $row['horario_programado'] ?? '' }}')"
                      style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .75rem;border-radius:.6rem;background:linear-gradient(to right, #0f67c0, #4f46e5);color:#fff;font-size:.72rem;font-weight:800;border:none;box-shadow:0 2px 4px rgba(79,70,229,0.25);cursor:pointer;"
                      title="Generar boleta de justificación para esta omisión (precarga fecha y hora)"
                    >
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                      <span>Justificar Omisión</span>
                    </button>
                  @else
                    <button
                      type="button"
                      wire:click="abrirBoletaModal('{{ $row['fecha'] }}')"
                      style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .55rem;border-radius:.5rem;background:#f8fafc;color:#475569;border:1px solid #cbd5e1;font-size:.68rem;font-weight:700;cursor:pointer;"
                      title="Pedir boleta o permiso para este día"
                    >
                      <span>+ Boleta</span>
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" style="text-align:center;padding:2.5rem 1rem;color:#94a3b8;font-size:.82rem;">
                  No hay registros que coincidan con los filtros seleccionados.
                </td>
              </tr>
            @endforelse
          </tbody>

          @if($filteredCount > 0)
          <tfoot>
            <tr>
              <td colspan="5" style="color:#64748b;">
                Totales filtrados: <strong style="color:#0f172a;">{{ $filteredCount }}</strong> registro(s)
                · Omisiones: <strong style="color:#ea580c;">{{ $filteredOmisionesCount }}</strong>
                · Faltas: <strong style="color:#e11d48;">{{ $filteredFaltasCount }}</strong>
              </td>
              <td class="td-delay" style="font-weight:800;color:#78350f;font-size:.78rem;">
                Total: {{ $filteredRetrasoMinutos }} min
              </td>
              <td class="td-estado">—</td>
              <td>—</td>
              <td>—</td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>

  </div>{{-- .ph-wrapper --}}

  {{-- POPUP MODAL: SOLICITAR CORREO SI EL FUNCIONARIO NO TIENE UNO --}}
  @if ($showPedirEmailModal)
    <div class="app-modal-backdrop no-print" wire:click="cerrarPedirEmailModal" style="position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);z-index:100000;display:flex;align-items:center;justify-content:center;padding:1rem;">
      <div class="app-modal-card" x-on:click.stop style="background:#fff;border-radius:1.25rem;max-width:28rem;width:100%;padding:1.75rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;">
        
        <div style="text-align:center;">
          <div style="margin:0 auto 1rem auto;height:3.5rem;width:3.5rem;border-radius:1rem;background:#fef3c7;border:1px solid #fde68a;color:#d97706;display:flex;align-items:center;justify-content:center;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
          </div>
          
          <h3 style="font-size:1.15rem;font-weight:900;color:#0f172a;margin:0 0 .5rem 0;">¿Dónde te llegará el estado de tu boleta?</h3>
          <p style="font-size:.78rem;font-weight:600;color:#64748b;margin:0;line-height:1.5;">
            Estimado/a <strong style="color:#1e293b;">{{ $boletaNombre }}</strong>, detectamos que aún no tienes un correo registrado en el sistema.
          </p>
        </div>

        <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1rem;">
          <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.75rem;padding:.75rem;font-size:.75rem;font-weight:500;color:#1e40af;display:flex;align-items:flex-start;gap:.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#2563eb;flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>Ingresa tu correo institucional o personal para recibir la respuesta y confirmación de Recursos Humanos.</span>
          </div>

          <div>
            <label style="display:block;font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
              Correo Electrónico *
            </label>
            <input
              type="email"
              wire:model="boletaEmail"
              wire:keydown.enter="confirmarEmailYDescargar"
              placeholder="ejemplo@correos.gob.bo o correo@gmail.com"
              autofocus
              style="width:100%;border-radius:.75rem;border:1px solid #cbd5e1;background:#fff;padding:.6rem .85rem;font-size:.875rem;font-weight:600;color:#1e293b;"
            >
            @error('boletaEmail') <p style="font-size:.72rem;color:#e11d48;font-weight:bold;margin:.35rem 0 0 0;">{{ $message }}</p> @enderror
            <p style="font-size:.68rem;color:#64748b;margin:.35rem 0 0 0;font-weight:600;">🔒 Se guardará en tu ficha. Por seguridad, luego solo el administrador podrá modificarlo.</p>
          </div>

          <div style="margin-top:.5rem;display:flex;align-items:center;justify-content:flex-end;gap:.75rem;">
            <button
              type="button"
              wire:click="cerrarPedirEmailModal"
              style="padding:.5rem 1rem;font-size:.75rem;font-weight:700;color:#475569;background:#f1f5f9;border:none;border-radius:.75rem;cursor:pointer;"
            >
              Volver
            </button>
            <button
              type="button"
              wire:click="confirmarEmailYDescargar"
              wire:loading.attr="disabled"
              style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;border-radius:.75rem;background:linear-gradient(to right, #0f67c0, #4f46e5);color:#fff;font-weight:800;font-size:.75rem;border:none;cursor:pointer;box-shadow:0 10px 15px -3px rgba(79,70,229,0.3);"
            >
              <span wire:loading.remove wire:target="confirmarEmailYDescargar">Guardar correo y Enviar</span>
              <span wire:loading wire:target="confirmarEmailYDescargar">Enviando...</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  @endif

  {{-- MODAL DE GENERACIÓN DE BOLETA / PAPELETA EN PERFIL DE HORAS --}}
  @if ($showBoletaModal)
    <div class="app-modal-backdrop no-print" wire:click="cerrarBoletaModal" style="position:fixed;inset:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(4px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:1rem;">
      <div class="app-modal-card" x-on:click.stop style="background:#fff;border-radius:1.25rem;max-width:56rem;width:100%;max-height:90vh;overflow-y:auto;padding:1.75rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;">
        
        <div style="display:flex;align-items:flex-start;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:1rem;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="height:2.75rem;width:2.75rem;border-radius:.75rem;background:#eff6ff;color:#1e60c6;display:flex;align-items:center;justify-content:center;font-weight:900;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
            </div>
            <div>
              <p style="font-size:.7rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:0;">Agencia Boliviana de Correos</p>
              <h2 style="font-size:1.2rem;font-weight:900;color:#0f172a;margin:0;letter-spacing:-.02em;">Papeleta de Comisión - Permiso Particular</h2>
            </div>
          </div>
          <button type="button" wire:click="cerrarBoletaModal" style="color:#94a3b8;background:transparent;border:none;padding:.5rem;border-radius:.5rem;cursor:pointer;font-size:1.2rem;font-weight:bold;">
            ✕
          </button>
        </div>

        {{-- FORMULARIO --}}
        <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:1.25rem;">

          {{-- DATOS DEL FUNCIONARIO --}}
          <div style="background:rgba(248,250,252,0.8);border-radius:.75rem;padding:1rem;border:1px solid rgba(226,232,240,0.8);">
            <h3 style="font-size:.75rem;font-weight:900;text-transform:uppercase;color:#1e60c6;margin:0 0 .75rem 0;display:flex;align-items:center;gap:.4rem;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Datos del Funcionario
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:.85rem;">
              <div style="grid-column: span 2;">
                <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">
                  Nombre del Funcionario <span style="font-size:.68rem;color:#94a3b8;font-weight:600;">🔒 (No editable)</span>
                </label>
                <input type="text" wire:model="boletaNombre" readonly style="width:100%;border-radius:.75rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;font-weight:700;color:#334155;cursor:not-allowed;">
              </div>

              <div>
                <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">
                  C.I. / Carnet <span style="font-size:.68rem;color:#94a3b8;font-weight:600;">🔒 (No editable)</span>
                </label>
                <input type="text" wire:model="boletaCi" readonly style="width:100%;border-radius:.75rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;font-weight:700;color:#334155;cursor:not-allowed;">
              </div>

              <div style="grid-column: span 3;">
                <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">Cargo *</label>
                <input type="text" wire:model="boletaCargo" style="width:100%;border-radius:.75rem;border:1px solid #cbd5e1;background:#fff;padding:.5rem .75rem;font-size:.85rem;font-weight:600;color:#1e293b;">
                @error('boletaCargo') <p style="font-size:.7rem;color:#e11d48;font-weight:bold;margin:.25rem 0 0 0;">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          {{-- MOTIVO Y TIPO --}}
          <div style="background:rgba(248,250,252,0.8);border-radius:.75rem;padding:1rem;border:1px solid rgba(226,232,240,0.8);display:flex;flex-direction:column;gap:.85rem;">
            <h3 style="font-size:.75rem;font-weight:900;text-transform:uppercase;color:#1e60c6;margin:0;display:flex;align-items:center;gap:.4rem;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
              Detalle de la Solicitud
            </h3>

            <div>
              <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">Motivo / Justificación * (Editable)</label>
              <input type="text" wire:model.live.debounce.300ms="boletaMotivo" placeholder="Ej: Justificación de omisión de entrada / salida" style="width:100%;border-radius:.75rem;border:1px solid #cbd5e1;background:#fff;padding:.5rem .75rem;font-size:.85rem;font-weight:600;color:#1e293b;">
              @error('boletaMotivo') <p style="font-size:.75rem;color:#e11d48;font-weight:700;margin:.25rem 0 0 0;">{{ $message }}</p> @enderror
            </div>

            <div>
              <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.5rem;">Tipo de Permiso *</label>
              <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:.75rem;">
                <label style="display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;border-radius:.75rem;border:1px solid {{ $boletaTipo === 'particular' ? '#1e60c6' : '#cbd5e1' }};background:{{ $boletaTipo === 'particular' ? '#eff6ff' : '#fff' }};cursor:pointer;font-size:.75rem;font-weight:700;color:{{ $boletaTipo === 'particular' ? '#1e60c6' : '#334155' }};">
                  <input type="radio" wire:model.live="boletaTipo" value="particular">
                  <span>PARTICULAR</span>
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;border-radius:.75rem;border:1px solid {{ $boletaTipo === 'comision' ? '#1e60c6' : '#cbd5e1' }};background:{{ $boletaTipo === 'comision' ? '#eff6ff' : '#fff' }};cursor:pointer;font-size:.75rem;font-weight:700;color:{{ $boletaTipo === 'comision' ? '#1e60c6' : '#334155' }};">
                  <input type="radio" wire:model.live="boletaTipo" value="comision">
                  <span>COMISIÓN</span>
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;border-radius:.75rem;border:1px solid {{ $boletaTipo === 'medico' ? '#1e60c6' : '#cbd5e1' }};background:{{ $boletaTipo === 'medico' ? '#eff6ff' : '#fff' }};cursor:pointer;font-size:.75rem;font-weight:700;color:{{ $boletaTipo === 'medico' ? '#1e60c6' : '#334155' }};">
                  <input type="radio" wire:model.live="boletaTipo" value="medico">
                  <span>MÉDICO</span>
                </label>
              </div>
            </div>
          </div>

          {{-- FECHAS Y HORARIOS (EDITABLES) --}}
          <div style="background:rgba(248,250,252,0.8);border-radius:.75rem;padding:1rem;border:1px solid rgba(226,232,240,0.8);display:flex;flex-direction:column;gap:.75rem;">
            <h3 style="font-size:.75rem;font-weight:900;text-transform:uppercase;color:#1e60c6;margin:0;display:flex;align-items:center;gap:.4rem;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
              Fechas y Horarios (Totalmente Editables)
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:.85rem;">
              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;">
                <span style="display:block;font-size:.7rem;font-weight:900;text-transform:uppercase;color:#64748b;margin-bottom:.5rem;">Desde</span>
                <label style="font-size:.68rem;color:#94a3b8;font-weight:700;">Fecha</label>
                <input type="date" wire:model.live="boletaDesdeFecha" style="width:100%;border-radius:.5rem;border:1px solid #cbd5e1;padding:.35rem .5rem;font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:.5rem;">
                <label style="font-size:.68rem;color:#94a3b8;font-weight:700;">Hora</label>
                @if ($this->esRangoDias)
                  <div style="border-radius:.5rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.35rem .5rem;font-size:.75rem;font-weight:700;color:#94a3b8;">
                    🔒 No requerida
                  </div>
                @else
                  <input type="time" wire:model.live="boletaDesdeHora" style="width:100%;border-radius:.5rem;border:1px solid #cbd5e1;padding:.35rem .5rem;font-size:.8rem;font-weight:700;color:#1e293b;">
                @endif
              </div>

              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;">
                <span style="display:block;font-size:.7rem;font-weight:900;text-transform:uppercase;color:#64748b;margin-bottom:.5rem;">Hasta</span>
                <label style="font-size:.68rem;color:#94a3b8;font-weight:700;">Fecha</label>
                <input type="date" wire:model.live="boletaHastaFecha" min="{{ $boletaDesdeFecha }}" style="width:100%;border-radius:.5rem;border:1px solid #cbd5e1;padding:.35rem .5rem;font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:.5rem;">
                <label style="font-size:.68rem;color:#94a3b8;font-weight:700;">Hora</label>
                @if ($this->esRangoDias)
                  <div style="border-radius:.5rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.35rem .5rem;font-size:.75rem;font-weight:700;color:#94a3b8;">
                    🔒 No requerida
                  </div>
                @else
                  <input type="time" wire:model.live="boletaHastaHora" style="width:100%;border-radius:.5rem;border:1px solid #cbd5e1;padding:.35rem .5rem;font-size:.8rem;font-weight:700;color:#1e293b;">
                @endif
              </div>

              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                  <span style="display:block;font-size:.7rem;font-weight:900;text-transform:uppercase;color:#64748b;margin-bottom:.5rem;">
                    Tiempo Solicitado <span style="font-size:.65rem;color:#94a3b8;font-weight:600;">🔒 (No editable)</span>
                  </span>
                  <input type="text" wire:model="boletaTiempoSolicitado" readonly style="width:100%;border-radius:.5rem;border:1px solid #e2e8f0;background:#f8fafc;padding:.35rem .5rem;font-size:.8rem;font-weight:800;color:#4338ca;cursor:not-allowed;">
                </div>
                @if ($this->esRangoDias)
                  <p style="font-size:.68rem;color:#4338ca;margin:.4rem 0 0 0;font-weight:700;">Rango de varios días (Jornada completa sin horas)</p>
                @else
                  <p style="font-size:.68rem;color:#94a3b8;margin:.4rem 0 0 0;">Se calcula automáticamente con el horario ingresado.</p>
                @endif
              </div>
            </div>
          </div>

          {{-- CIUDAD (SUCURSAL READONLY) Y FECHA EMISIÓN READONLY --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
            <div>
              <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">
                Ciudad / Sucursal <span style="font-size:.68rem;color:#94a3b8;font-weight:600;">🔒 (No editable)</span>
              </label>
              <input type="text" wire:model="boletaCiudad" readonly style="width:100%;border-radius:.75rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;font-weight:700;color:#334155;cursor:not-allowed;">
            </div>
            <div>
              <label style="display:block;font-size:.75rem;font-weight:700;color:#334155;margin-bottom:.25rem;">
                Fecha de Emisión <span style="font-size:.68rem;color:#94a3b8;font-weight:600;">🔒 (No editable)</span>
              </label>
              <input type="text" wire:model="boletaFechaTexto" readonly style="width:100%;border-radius:.75rem;border:1px solid #e2e8f0;background:#f1f5f9;padding:.5rem .75rem;font-size:.85rem;font-weight:700;color:#334155;cursor:not-allowed;">
            </div>
          </div>

          {{-- SUBIDA OBLIGATORIA DE COMPROBANTE CON DRAG & DROP --}}
          <div style="border-radius:1.25rem;border:1.5px solid #c7d2fe;background:#eff6ff;padding:1.25rem;box-shadow:0 2px 8px rgba(99,102,241,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
              <h3 style="font-size:.8rem;font-weight:900;text-transform:uppercase;color:#312e81;margin:0;display:flex;align-items:center;gap:.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Foto del Comprobante / Justificación
                <span style="background:#ffe4e6;color:#be123c;font-size:.65rem;padding:.15rem .5rem;border-radius:.25rem;font-weight:800;border:1px solid #fecdd3;">OBLIGATORIO</span>
              </h3>
            </div>
            <p style="font-size:.75rem;color:#475569;margin:0 0 1rem 0;line-height:1.4;">Sube la foto del certificado médico, boleta de atención o justificativo que respalde la omisión o permiso (JPG, PNG, WEBP, máx 5MB).</p>

            @if ($comprobante)
              <div style="display:flex;align-items:center;gap:1.25rem;background:#fff;padding:1rem;border-radius:1rem;border:1.5px solid #e0e7ff;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="width:5.5rem;height:5.5rem;border-radius:.75rem;overflow:hidden;background:#f8fafc;flex-shrink:0;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                  <img src="{{ $comprobante->temporaryUrl() }}" alt="Vista previa" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div style="flex:1;min-width:0;">
                  <p style="font-size:.85rem;font-weight:800;color:#1e293b;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $comprobante->getClientOriginalName() }}</p>
                  <p style="font-size:.72rem;color:#64748b;margin:.25rem 0;">{{ number_format($comprobante->getSize() / 1024, 1) }} KB · Foto lista para adjuntar</p>
                  <p style="font-size:.72rem;color:#047857;font-weight:700;margin:0 0 .5rem 0;">✓ Se guardará en la Base de Datos</p>
                  <button type="button" wire:click="quitarComprobante" style="color:#e11d48;background:#fff1f2;border:1px solid #fecdd3;font-size:.75rem;font-weight:700;cursor:pointer;padding:.3rem .75rem;border-radius:.5rem;">✕ Cambiar foto</button>
                </div>
              </div>
            @else
              <div
                x-data="{ isDropping: false }"
                x-on:dragover.prevent="isDropping = true"
                x-on:dragleave.prevent="isDropping = false"
                x-on:drop.prevent="isDropping = false; if ($event.dataTransfer.files.length > 0) { @this.upload('comprobante', $event.dataTransfer.files[0]) }"
                style="width:100%;"
              >
                <label 
                  :class="isDropping ? 'ph-dropzone-active' : ''"
                  class="ph-dropzone"
                >
                  <div style="width:4.25rem;height:4.25rem;border-radius:1rem;background:#eef2ff;border:1.5px solid #c7d2fe;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;pointer-events:none;box-shadow:0 2px 6px rgba(99,102,241,0.12);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  </div>
                  <span style="font-size:1rem;font-weight:800;color:#0f172a;pointer-events:none;margin-bottom:.35rem;">Arrastra y suelta aquí la foto del comprobante</span>
                  <span style="font-size:.82rem;font-weight:700;color:#4f46e5;text-decoration:underline;pointer-events:none;margin-bottom:1.25rem;">o haz clic en cualquier parte de este espacio para buscar en tu dispositivo</span>
                  <div style="display:flex;align-items:center;justify-content:center;gap:.6rem;flex-wrap:wrap;pointer-events:none;">
                    <span style="font-size:.72rem;font-weight:700;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;padding:.3rem .75rem;border-radius:.6rem;">Formatos: JPG, PNG, WEBP</span>
                    <span style="font-size:.72rem;font-weight:700;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;padding:.3rem .75rem;border-radius:.6rem;">Hasta 5 MB</span>
                    <span style="font-size:.72rem;font-weight:700;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:.3rem .75rem;border-radius:.6rem;">✓ Se guarda en Base de Datos</span>
                  </div>
                  <input type="file" wire:model="comprobante" accept="image/*" style="display:none;">
                </label>
              </div>
            @endif

            <div wire:loading wire:target="comprobante" style="font-size:.75rem;color:#4338ca;font-weight:700;margin-top:.4rem;">
              ⏳ Subiendo comprobante...
            </div>

            @error('comprobante')
              <p style="font-size:.75rem;color:#e11d48;font-weight:700;margin:.4rem 0 0 0;">{{ $message }}</p>
            @enderror
          </div>

        </div>

        @php
          $requisitosCumplidos = filled(trim($boletaNombre))
            && filled(trim($boletaCi))
            && filled(trim($boletaMotivo))
            && filled(trim($boletaTipo))
            && filled(trim($boletaDesdeFecha))
            && ($this->esRangoDias || filled(trim($boletaDesdeHora)))
            && filled(trim($boletaHastaFecha))
            && ($this->esRangoDias || filled(trim($boletaHastaHora)))
            && filled(trim($boletaTiempoSolicitado))
            && !empty($comprobante);
        @endphp

        {{-- BOTONES (SOLO APARECE CUANDO SE CUMPLEN TODOS LOS REQUISITOS) --}}
        <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
          <button type="button" wire:click="cerrarBoletaModal" style="padding:.5rem 1rem;font-size:.75rem;font-weight:700;color:#475569;background:#f1f5f9;border:none;border-radius:.75rem;cursor:pointer;">
            Cancelar
          </button>

          @if ($requisitosCumplidos)
            <button
              type="button"
              wire:click="descargarPdf"
              wire:loading.attr="disabled"
              style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.4rem;border-radius:.75rem;background:linear-gradient(135deg, #0f67c0, #4f46e5);color:#fff;font-weight:800;font-size:.8rem;border:none;box-shadow:0 4px 12px rgba(79,70,229,0.3);cursor:pointer;transition:all .2s ease;"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
              <span wire:loading.remove wire:target="descargarPdf">Enviar a RR.HH. y Descargar Boleta PDF</span>
              <span wire:loading wire:target="descargarPdf">Generando documento oficial...</span>
            </button>
          @else
            <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.75rem;background:#fef3c7;border:1px solid #fde68a;color:#92400e;font-size:.75rem;font-weight:700;">
              <span>⚠️ Completa el motivo y sube la foto del comprobante para habilitar el envío</span>
            </div>
          @endif
        </div>

      </div>
    </div>
  @endif

</div>{{-- .ph-root --}}
</div>
