/* RestoCRM — Main JS */

// ── Sidebar toggle (mobile) ───────────────────────────────────
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarClose   = document.getElementById('sidebarClose');

function openSidebar() {
    sidebar?.classList.add('open');
    sidebarOverlay?.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    sidebar?.classList.remove('open');
    sidebarOverlay?.classList.remove('show');
    document.body.style.overflow = '';
}

sidebarToggle?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

// Close sidebar on nav link click (mobile)
document.querySelectorAll('.nav-item').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 768) closeSidebar();
    });
});

// ── Auto-dismiss alerts ───────────────────────────────────────
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity .4s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 400);
    }, 4000);
});

// ── Confirm delete helper ─────────────────────────────────────
function confirmDelete(url, msg) {
    if (!confirm(msg || 'Are you sure? This cannot be undone.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const csrf = document.createElement('input');
    csrf.type  = 'hidden';
    csrf.name  = document.querySelector('meta[name="csrf-token"]')?.dataset.name || 'csrf_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}

// ── Toast notification ────────────────────────────────────────
function showToast(msg, type = 'info') {
    const colors = {
        success: '#38A169', error: '#E53E3E',
        warning: '#D69E2E', info:  '#3182CE'
    };
    const t = document.createElement('div');
    t.style.cssText = `
        position:fixed; bottom:1.5rem; right:1rem; z-index:9999;
        background:${colors[type] || colors.info}; color:#fff;
        padding:.65rem 1.25rem; border-radius:20px;
        font-size:.85rem; font-weight:600;
        box-shadow:0 4px 16px rgba(0,0,0,.2);
        animation: slideUp .25s ease;
        font-family: 'Plus Jakarta Sans', sans-serif;
        max-width: 300px; word-wrap: break-word;
    `;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3000);
}

// ── Modal helpers ─────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});

// Close modal on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
});

// ── CSRF helper for fetch ─────────────────────────────────────
function getCsrf() {
    return {
        name:  document.querySelector('meta[name="csrf-token"]')?.dataset.name  || 'csrf_token',
        token: document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
}

function postFetch(url, data = {}) {
    const csrf = getCsrf();
    const body = new URLSearchParams({ [csrf.name]: csrf.token, ...data });
    return fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf.token },
        body
    }).then(r => r.json());
}

// ── Table search ──────────────────────────────────────────────
const tableSearch = document.getElementById('tableSearch');
if (tableSearch) {
    tableSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// ── Slideup keyframe ──────────────────────────────────────────
const style = document.createElement('style');
style.textContent = `@keyframes slideUp { from { transform:translateY(12px); opacity:0; } to { transform:none; opacity:1; } }`;
document.head.appendChild(style);
