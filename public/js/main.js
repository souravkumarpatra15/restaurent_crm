/* ============================================================
   RestOne — Main JS
   Sidebar, Modals, Toasts, CSRF, Table search
   ============================================================ */

(function () {
  'use strict';

  // ── Sidebar ──────────────────────────────────────────────
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggle');
  const closeBtn = document.getElementById('sidebarClose');

  function openSidebar() {
    sidebar?.classList.add('open');
    overlay?.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('show');
    document.body.style.overflow = '';
  }

  toggleBtn?.addEventListener('click', openSidebar);
  closeBtn?.addEventListener('click', closeSidebar);
  overlay?.addEventListener('click', closeSidebar);

  // Close on nav click (mobile)
  document.querySelectorAll('.nav-item a, a.nav-item').forEach(link => {
    link.addEventListener('click', () => { if (window.innerWidth < 768) closeSidebar(); });
  });

  // ── Auto-dismiss alerts ───────────────────────────────────
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s, transform .5s';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-8px)';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

  // ── Modal helpers ─────────────────────────────────────────
  window.openModal = function (id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = function (id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
  };

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => {
        m.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
  });

  // ── Toast ─────────────────────────────────────────────────
  window.showToast = function (msg, type = 'info') {
    const colors = {
      success: '#38A169', error: '#E53E3E',
      warning: '#D69E2E', info: '#3182CE'
    };
    const icons = {
      success: 'fa-check-circle', error: 'fa-circle-exclamation',
      warning: 'fa-triangle-exclamation', info: 'fa-circle-info'
    };
    const t = document.createElement('div');
    t.style.cssText = `
      position:fixed; bottom:1.5rem; right:1rem; z-index:9999;
      background:${colors[type] || colors.info}; color:#fff;
      padding:.65rem 1.25rem; border-radius:12px; font-size:.85rem; font-weight:600;
      box-shadow:0 4px 20px rgba(0,0,0,.2); display:flex; align-items:center; gap:.5rem;
      max-width:320px; animation:slideUp .25s ease; font-family:inherit;
    `;
    t.innerHTML = `<i class="fa ${icons[type] || icons.info}"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => {
      t.style.transition = 'opacity .3s, transform .3s';
      t.style.opacity = '0';
      t.style.transform = 'translateY(8px)';
      setTimeout(() => t.remove(), 300);
    }, 3500);
  };

  // ── CSRF helpers ──────────────────────────────────────────
  window.getCsrfToken = function () {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  };
  window.getCsrfName = function () {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('data-name') : 'csrf_token';
  };
  window.postFetch = function (url, data = {}) {
    const body = new URLSearchParams({
      [getCsrfName()]: getCsrfToken(), ...data
    });
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': getCsrfToken() },
      body
    }).then(r => r.json());
  };

  // ── Table search ──────────────────────────────────────────
  const tableSearch = document.getElementById('tableSearch');
  if (tableSearch) {
    tableSearch.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── Confirm delete ────────────────────────────────────────
  window.confirmDelete = function (url, msg) {
    if (!confirm(msg || 'Are you sure? This cannot be undone.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = `<input type="hidden" name="${getCsrfName()}" value="${getCsrfToken()}">`;
    document.body.appendChild(form);
    form.submit();
  };

  // ── Toggle switch (AJAX) ──────────────────────────────────
  window.ajaxToggle = function (url, successMsg) {
    postFetch(url).then(d => {
      if (d.success) showToast(successMsg || 'Updated', 'success');
      else showToast('Failed to update', 'error');
    });
  };

  // ── Inject slideUp animation ──────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideUp { from { transform:translateY(12px); opacity:0; } to { transform:none; opacity:1; } }
  `;
  document.head.appendChild(style);

})();
