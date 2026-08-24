function initToastAutoHide() {
  const toast = document.querySelector('[data-app-toast]');
  if (!toast) {
    return;
  }

  toast.classList.remove('app-toast-hidden');

  window.clearTimeout(window.__rrhhToastTimer);
  window.__rrhhToastTimer = window.setTimeout(() => {
    toast.classList.add('app-toast-hidden');
  }, 1500);
}

function normalizeDepartmentName(value) {
  return (value || '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

function departmentKeyFromName(name) {
  const normalized = normalizeDepartmentName(name);
  const aliases = {
    'la paz': 'la-paz',
    'santa cruz': 'santa-cruz',
    cochabamba: 'cochabamba',
    chuquisaca: 'chuquisaca',
    potosi: 'potosi',
    oruro: 'oruro',
    tarija: 'tarija',
    beni: 'beni',
    pando: 'pando',
  };

  return aliases[normalized] || normalized.replace(/\s+/g, '-');
}

function loadBoliviaTopology() {
  if (!window.__boliviaTopologyPromise) {
    window.__boliviaTopologyPromise = fetch('/data/bolivia-topology.json', {
      headers: {
        Accept: 'application/json',
      },
    }).then((response) => {
      if (!response.ok) {
        throw new Error('No se pudo cargar el mapa de Bolivia.');
      }

      return response.json();
    });
  }

  return window.__boliviaTopologyPromise;
}

function initBoliviaMap() {
  const root = document.querySelector('[data-bolivia-map-root]');
  if (!root || !window.d3 || !window.topojson) {
    return;
  }

  const canvas = root.querySelector('[data-bolivia-map-canvas]');
  const dataScript = root.querySelector('[data-departments-json]');
  const bubbleName = root.querySelector('[data-department-name]');
  const bubbleBranch = root.querySelector('[data-department-branch]');
  const bubbleMarked = root.querySelector('[data-department-marked]');
  const bubbleWorking = root.querySelector('[data-department-working]');
  const bubbleEmployees = root.querySelector('[data-department-employees]');
  const bubbleMissing = root.querySelector('[data-department-missing]');
  const bubbleUpdatedAt = root.querySelector('[data-department-updated-at]');
  const bubbleSyncLabel = root.querySelector('[data-department-sync-label]');
  const bubblePresenceTotal = root.querySelector('[data-department-presence-total]');
  const bubblePresenceList = root.querySelector('[data-department-presence-list]');

  if (!canvas || !dataScript) {
    return;
  }

  let departmentStats = {};
  try {
    departmentStats = JSON.parse(dataScript.textContent || '{}');
  } catch (error) {
    console.error('No se pudo leer la informacion de departamentos.', error);
    return;
  }

  const palette = {
    pando: '#ffe4a8',
    beni: '#e5f2a6',
    'la-paz': '#fff6b5',
    oruro: '#ddee98',
    cochabamba: '#d9b8eb',
    'santa-cruz': '#fff6a3',
    potosi: '#f8c0c4',
    chuquisaca: '#ffdca0',
    tarija: '#e5f3a8',
  };

  const activePalette = {
    pando: '#ffc04d',
    beni: '#91c95d',
    'la-paz': '#ffd95a',
    oruro: '#9ccb53',
    cochabamba: '#9e6ad6',
    'santa-cruz': '#ffd038',
    potosi: '#ef7c88',
    chuquisaca: '#ffb95c',
    tarija: '#9ccf54',
  };

  const updateBubble = (payload) => {
    if (!payload) {
      return;
    }

    bubbleName.textContent = payload.name;
    bubbleBranch.textContent = payload.branch;
    bubbleMarked.textContent = payload.marked;
    bubbleWorking.textContent = payload.working;
    if (bubbleEmployees) {
      bubbleEmployees.textContent = payload.employees ?? 0;
    }
    bubbleMissing.textContent = payload.missing;
    if (bubbleUpdatedAt) {
      bubbleUpdatedAt.textContent = payload.updated_at || '--:--';
    }
    if (bubbleSyncLabel) {
      bubbleSyncLabel.textContent = payload.sync_label || 'Sin sincronizacion automatica registrada';
    }
    if (bubblePresenceTotal) {
      bubblePresenceTotal.textContent = payload.people_in_agency_total ?? 0;
    }
    if (bubblePresenceList) {
      const people = Array.isArray(payload.people_in_agency) ? payload.people_in_agency : [];

      bubblePresenceList.innerHTML = '';

      if (!people.length) {
        const empty = document.createElement('p');
        empty.className = 'department-presence-empty';
        empty.textContent = 'No hay personal dentro de la agencia en este momento.';
        bubblePresenceList.appendChild(empty);

        return;
      }

      people.forEach((person) => {
        const item = document.createElement('article');
        item.className = 'department-presence-item';

        const name = document.createElement('strong');
        name.textContent = person.name || 'Sin nombre';

        const detail = document.createElement('span');
        detail.textContent = `${person.area || 'Sin area'} | ${person.status || 'Dentro de agencia'}`;

        item.appendChild(name);
        item.appendChild(detail);
        bubblePresenceList.appendChild(item);
      });
    }
  };

  const renderToken = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  root.dataset.mapRenderToken = renderToken;
  canvas.innerHTML = '';

  loadBoliviaTopology()
    .then((topology) => {
      if (root.dataset.mapRenderToken !== renderToken) {
        return;
      }

      const featureCollection = window.topojson.feature(topology, topology.objects.level2);
      const features = featureCollection.features.filter((feature) => {
        const name = normalizeDepartmentName(feature?.properties?.name);
        return name && !['lago', 'salar'].includes(name);
      });

      if (!features.length) {
        return;
      }

      const availableWidth = Math.max(canvas.clientWidth || 0, 420);
      const width = Math.min(availableWidth, 940);
      const height = Math.max(360, Math.min(Math.round(width * 0.72), 520));
      const svg = window.d3
        .select(canvas)
        .append('svg')
        .attr('viewBox', `0 0 ${width} ${height}`)
        .attr('class', 'bolivia-map-svg')
        .attr('preserveAspectRatio', 'xMidYMid meet')
        .attr('role', 'img')
        .attr('aria-label', 'Mapa interactivo de Bolivia por departamentos');

      const projection = window.d3
        .geoTransverseMercator()
        .rotate([50, 55])
        .fitExtent(
          [
            [28, 20],
            [width - 28, height - 20],
          ],
          {
            type: 'FeatureCollection',
            features,
          },
        );

      const path = window.d3.geoPath(projection);
      const mapGroup = svg.append('g').attr('class', 'bolivia-map-layer');
      let activeNode = null;

      const activateFeature = (node, payload, key) => {
        if (activeNode) {
          const previousKey = activeNode.dataset.departmentKey;
          activeNode.classList.remove('is-active');
          activeNode.setAttribute('fill', palette[previousKey] || '#e5ecf5');
        }

        activeNode = node;
        node.classList.add('is-active');
        node.setAttribute('fill', activePalette[key] || '#0f67c0');
        updateBubble(payload);
      };

      features.forEach((feature) => {
        const departmentName = feature.properties?.name || '';
        const key = departmentKeyFromName(departmentName);
        const payload = departmentStats[key];

        if (!payload) {
          return;
        }

        const region = mapGroup
          .append('path')
          .datum(feature)
          .attr('d', path)
          .attr('class', 'bolivia-map-region')
          .attr('data-department-key', key)
          .attr('fill', palette[key] || '#e5ecf5')
          .attr('tabindex', 0)
          .attr('role', 'button')
          .attr('aria-label', `${payload.name}: ${payload.marked} marcaron, ${payload.missing} sin marcar`);

        const regionNode = region.node();

        if (!regionNode) {
          return;
        }

        regionNode.dataset.departmentKey = key;
        regionNode.addEventListener('click', () => activateFeature(regionNode, payload, key));
        regionNode.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            activateFeature(regionNode, payload, key);
          }
        });

        const centroid = path.centroid(feature);
        if (Number.isFinite(centroid[0]) && Number.isFinite(centroid[1])) {
          mapGroup
            .append('text')
            .attr('class', 'bolivia-map-label')
            .attr('x', centroid[0])
            .attr('y', centroid[1])
            .text(payload.name);
        }
      });

      const firstKey = Object.keys(departmentStats).find((key) => canvas.querySelector(`[data-department-key="${key}"]`));
      const firstNode = canvas.querySelector(`[data-department-key="${firstKey}"]`);
      if (firstNode && departmentStats[firstKey]) {
        activateFeature(firstNode, departmentStats[firstKey], firstKey);
      }
    })
    .catch((error) => {
      if (root.dataset.mapRenderToken !== renderToken) {
        return;
      }

      console.error(error);
      canvas.innerHTML = '<p class="map-fallback-copy">No se pudo cargar el mapa departamental.</p>';
    });
}

function initHumanResourcesUi() {
  initToastAutoHide();
  initBoliviaMap();
}

function printEmployeePdfFromModal() {
  const source = document.getElementById('employee-pdf-content');
  if (!source) {
    return;
  }

  const printWindow = window.open('', '_blank', 'width=1024,height=768');
  if (!printWindow) {
    return;
  }

  const styles = `
    <style>
      body { font-family: "Segoe UI", Arial, sans-serif; color: #0f172a; margin: 24px; background: #f8fafc; }
      h1, h2, h3, h4, p { margin: 0; }
      .pdf-shell { display: grid; gap: 24px; }
      .pdf-export-sheet { display: grid; gap: 24px; }
      .pdf-export-header { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; border-bottom: 2px solid #dbe4f0; padding-bottom: 18px; }
      .pdf-export-kicker { font-size: 11px; letter-spacing: 0.3em; text-transform: uppercase; color: #2563eb; font-weight: 800; }
      .pdf-export-title { margin-top: 8px; font-family: Georgia, serif; font-size: 34px; line-height: 1.05; }
      .pdf-export-copy { margin-top: 10px; color: #475569; font-size: 14px; max-width: 560px; }
      .pdf-export-badge { min-width: 160px; border: 1px solid #dbe4f0; border-radius: 18px; padding: 14px 18px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
      .pdf-export-badge-label { display: block; font-size: 10px; letter-spacing: 0.24em; text-transform: uppercase; color: #94a3b8; font-weight: 800; }
      .pdf-export-badge-value { display: block; margin-top: 8px; font-size: 18px; color: #0f172a; }
      .pdf-export-grid { display: grid; gap: 14px; }
      .pdf-export-grid-primary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .pdf-export-grid-secondary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
      .pdf-export-card { border: 1px solid #dbe4f0; border-radius: 18px; padding: 16px 18px; background: #ffffff; break-inside: avoid; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05); }
      .pdf-export-card-highlight { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); }
      .pdf-export-label { font-size: 10px; letter-spacing: 0.24em; text-transform: uppercase; color: #94a3b8; font-weight: 800; margin-bottom: 10px; }
      .pdf-export-value { font-size: 20px; font-weight: 800; line-height: 1.2; }
      .pdf-export-value-sm { font-size: 15px; font-weight: 600; line-height: 1.4; }
      .pdf-export-section-head { margin-bottom: 14px; }
      .pdf-export-table-shell { background: #ffffff; border: 1px solid #dbe4f0; border-radius: 22px; padding: 20px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05); }
      .pdf-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
      .pdf-table th, .pdf-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
      .pdf-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; letter-spacing: 0.16em; font-size: 10px; }
      .pdf-table tr:last-child td { border-bottom: none; }
      .history-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
      .history-table th, .history-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
      .history-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; letter-spacing: 0.16em; font-size: 10px; }
      .history-table tr:last-child td { border-bottom: none; }
      .section-kicker { font-size: 10px; letter-spacing: 0.24em; text-transform: uppercase; color: #2563eb; font-weight: 800; }
      .section-title { margin-top: 6px; font-family: Georgia, serif; font-size: 24px; line-height: 1.1; }
      .text-slate-400 { color: #94a3b8; }
      @media print {
        body { margin: 0; background: #ffffff; }
      }
    </style>
  `;

  printWindow.document.write(`
    <html lang="es">
      <head>
        <meta charset="utf-8">
        <title>Ficha del personal</title>
        ${styles}
      </head>
      <body>
        <div class="pdf-shell">${source.innerHTML}</div>
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  window.setTimeout(() => {
    printWindow.print();
  }, 300);
}

function printReportesPdf() {
  const source = document.getElementById('reportes-pdf-content');
  if (!source) {
    return;
  }

  const printWindow = window.open('', '_blank', 'width=1024,height=768');
  if (!printWindow) {
    return;
  }

  const styles = `
    <style>
      body { font-family: "Segoe UI", Arial, sans-serif; color: #0f172a; margin: 24px; background: #f8fafc; }
      h1, h2, h3, h4, p { margin: 0; }
      .pdf-shell { display: grid; gap: 24px; }
      .history-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
      .history-table th, .history-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
      .history-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; letter-spacing: 0.16em; font-size: 10px; }
      .history-table tr:last-child td { border-bottom: none; }
      .text-slate-400 { color: #94a3b8; }
    </style>
  `;

  printWindow.document.write(`
    <html lang="es">
      <head>
        <meta charset="utf-8">
        <title>Reporte mensual</title>
        ${styles}
        <link rel="stylesheet" href="${window.location.origin}/css/app.css">
      </head>
      <body>
        <div class="pdf-shell">${source.innerHTML}</div>
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  window.setTimeout(() => {
    printWindow.print();
  }, 300);
}

function printReporteDetalleEmpleadoPdf() {
  const source = document.getElementById('reportes-detalle-empleado-pdf-content');
  if (!source) {
    return;
  }

  const printWindow = window.open('', '_blank', 'width=1024,height=768');
  if (!printWindow) {
    return;
  }

  const styles = `
    <style>
      body { font-family: "Segoe UI", Arial, sans-serif; color: #0f172a; margin: 24px; background: #f8fafc; }
      h1, h2, h3, h4, p { margin: 0; }
      .pdf-shell { display: grid; gap: 24px; }
      .history-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
      .history-table th, .history-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
      .history-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; letter-spacing: 0.16em; font-size: 10px; }
      .history-table tr:last-child td { border-bottom: none; }
      .text-slate-400 { color: #94a3b8; }
    </style>
  `;

  printWindow.document.write(`
    <html lang="es">
      <head>
        <meta charset="utf-8">
        <title>Detalle mensual del personal</title>
        ${styles}
        <link rel="stylesheet" href="${window.location.origin}/css/app.css">
      </head>
      <body>
        <div class="pdf-shell">${source.innerHTML}</div>
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  window.setTimeout(() => {
    printWindow.print();
  }, 300);
}

document.addEventListener('DOMContentLoaded', initHumanResourcesUi);
document.addEventListener('livewire:navigated', initHumanResourcesUi);
document.addEventListener('livewire:init', () => {
  Livewire.on('print-empleado-pdf', printEmployeePdfFromModal);
  Livewire.on('print-reportes-pdf', printReportesPdf);
  Livewire.on('print-reporte-detalle-empleado-pdf', printReporteDetalleEmpleadoPdf);

  if (typeof Livewire.hook === 'function') {
    Livewire.hook('morph.updated', ({ el }) => {
      if (el?.querySelector?.('[data-bolivia-map-root]') || el?.matches?.('[data-bolivia-map-root]')) {
        initBoliviaMap();
      }
    });
  }
});
