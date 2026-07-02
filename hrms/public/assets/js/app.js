/* PTS HRMS — shared frontend behaviour. */
(function () {
  'use strict';

  // ---------------------------------------------------------------- dark mode
  const html = document.documentElement;
  const stored = localStorage.getItem('pts-theme');
  if (stored) html.setAttribute('data-bs-theme', stored);
  const themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    const icon = themeToggle.querySelector('i');
    const sync = () => {
      const dark = html.getAttribute('data-bs-theme') === 'dark';
      icon.className = dark ? 'bi bi-sun' : 'bi bi-moon-stars';
    };
    sync();
    themeToggle.addEventListener('click', () => {
      const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-bs-theme', next);
      localStorage.setItem('pts-theme', next);
      sync();
    });
  }

  // ---------------------------------------------------------------- sidebar (mobile)
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if (sidebar && sidebar.classList.contains('open')
        && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });

  // ---------------------------------------------------------------- flash → SweetAlert2 toast
  const flash = document.querySelector('[data-flash-message]');
  if (flash && window.Swal) {
    Swal.fire({
      toast: true, position: 'top-end', timer: 3800, showConfirmButton: false,
      timerProgressBar: true,
      icon: flash.dataset.flashType === 'error' ? 'error' : 'success',
      title: flash.dataset.flashMessage
    });
  }

  // ---------------------------------------------------------------- delete confirmations
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: form.dataset.confirm,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, proceed'
      }).then((r) => { if (r.isConfirmed) form.submit(); });
    });
  });

  // ---------------------------------------------------------------- DataTables
  if (window.jQuery && jQuery.fn.dataTable) {
    jQuery('table.datatable').each(function () {
      jQuery(this).DataTable({
        pageLength: 25,
        order: [],
        language: { search: '', searchPlaceholder: 'Filter…' }
      });
    });
  }

  // ---------------------------------------------------------------- global search ( / )
  const search = document.getElementById('globalSearch');
  if (search) {
    document.addEventListener('keydown', (e) => {
      if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement.tagName)) {
        e.preventDefault();
        search.focus();
      }
    });
    search.addEventListener('input', () => {
      const dt = window.jQuery && jQuery('table.datatable').first();
      if (dt && dt.length) {
        dt.DataTable().search(search.value).draw();
        return;
      }
      // Fallback: filter any visible table rows on the page.
      const q = search.value.toLowerCase();
      document.querySelectorAll('table tbody tr').forEach((tr) => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ---------------------------------------------------------------- GPS capture for clock in/out
  document.querySelectorAll('form[data-gps]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (form.dataset.gpsDone) return;
      if (!navigator.geolocation) return; // submit without coordinates
      e.preventDefault();
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          form.querySelector('[name=latitude]').value = pos.coords.latitude;
          form.querySelector('[name=longitude]').value = pos.coords.longitude;
          form.dataset.gpsDone = '1';
          form.submit();
        },
        () => { form.dataset.gpsDone = '1'; form.submit(); },
        { timeout: 4000 }
      );
    });
  });

  // ---------------------------------------------------------------- Chart.js defaults + renderers
  if (window.Chart) {
    const palette = ['#0b3d66', '#10b981', '#f97316', '#14548a', '#64748b', '#7dd3fc', '#dc2626', '#cbd5e1'];
    Chart.defaults.font.family = '"Segoe UI", system-ui, sans-serif';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
      const cfg = JSON.parse(canvas.dataset.chart);
      const type = canvas.dataset.type || 'bar';
      new Chart(canvas, {
        type,
        data: {
          labels: cfg.labels,
          datasets: [{
            label: cfg.label || '',
            data: cfg.values,
            backgroundColor: (type === 'doughnut' || type === 'pie')
              ? palette : (type === 'line' ? 'rgba(16,185,129,.15)' : palette[0]),
            borderColor: type === 'line' ? '#10b981' : undefined,
            borderWidth: type === 'line' ? 2 : 0,
            fill: type === 'line',
            tension: 0.35
          }]
        },
        options: {
          maintainAspectRatio: false,
          plugins: { legend: { display: type === 'doughnut' || type === 'pie' } },
          scales: (type === 'doughnut' || type === 'pie') ? {} :
            { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    });
  }
})();
