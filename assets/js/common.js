// assets/js/common.js
const CURRENCY_SYMBOLS = { USD: '$', NIO: 'C$', MXN: 'Mex$', EUR: '\u20AC' };
let _currencyCode = 'USD';

function formatCurrency(value) {
    const sym = CURRENCY_SYMBOLS[_currencyCode] || '$';
    return sym + Number(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function showToast(title, message, bgClass) {
    const toastEl = document.getElementById('loginToast');
    if (!toastEl) return;
    const toastHeader = document.getElementById('toastHeader');
    const toastTitle = document.getElementById('toastTitle');
    const toastBody = document.getElementById('toastBody');
    if (toastHeader) toastHeader.className = 'toast-header text-white ' + bgClass;
    if (toastTitle) toastTitle.innerText = title;
    if (toastBody) toastBody.innerText = message;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Loader global automático para peticiones al backend
(function() {
    const MIN_LOADER_MS = 500;
    const loader = document.getElementById('globalApiLoader');
    let activeRequests = 0;
    const originalFetch = window.fetch;

    function isApiUrl(url) {
        return typeof url === 'string' && url.includes('api.php');
    }

    window.fetch = function() {
        const url = arguments[0];
        const isApi = isApiUrl(url);
        const options = arguments[1] || {};
        const method = (options.method || 'GET').toUpperCase();

        if (isApi && method !== 'GET') {
            const metaCsrf = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = metaCsrf ? metaCsrf.getAttribute('content') : '';
            if (csrfToken) {
                options.headers = options.headers || {};
                options.headers['X-CSRF-Token'] = csrfToken;
            }
            arguments[1] = options;
        }

        if (isApi) {
            if (activeRequests === 0 && loader) loader.style.display = '';
            activeRequests++;
        }

        const startTime = Date.now();

        return originalFetch.apply(this, arguments).then(function(response) {
            if (!isApi) return response;
            activeRequests--;
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, MIN_LOADER_MS - elapsed);

            if (remaining > 0) {
                setTimeout(function() {
                    if (activeRequests === 0 && loader) loader.style.display = 'none';
                }, remaining);
            } else {
                if (activeRequests === 0 && loader) loader.style.display = 'none';
            }

            return response;
        }).catch(function(err) {
            if (isApi) {
                activeRequests--;
                if (activeRequests === 0 && loader) loader.style.display = 'none';
            }
            throw err;
        });
    };
})();

document.addEventListener('DOMContentLoaded', async () => {
    const path = window.location.pathname.toLowerCase();
    if (path.includes('login') || path.includes('forgot_password') || path.includes('reset_password')) {
        return;
    }

    try {
        const [sessRes, empRes] = await Promise.all([
            fetch('../backend/api.php?route=check_session'),
            fetch('../backend/api.php?route=get_datos_empresa')
        ]);
        const sessData = await sessRes.json();

        if (!sessData.logged_in) {
            window.location.href = 'login.php';
            return;
        }

        const userNameEl = document.querySelector('.top-navbar .user-name-display');
        if (userNameEl && sessData.user) {
            userNameEl.textContent = sessData.user.nombre;
        }

        const empData = await empRes.json();
        if (empData.success && empData.data && empData.data.moneda) {
            _currencyCode = empData.data.moneda;
        }
    } catch (e) {
        console.error('Error initializing:', e);
    }
});

function logout() {
    showConfirm('¿Desea cerrar la sesión?', 'Cerrar Sesión').then(confirmed => {
        if (confirmed) {
            fetch('../backend/api.php?route=logout').then(() => {
                window.location.href = 'login.php';
            }).catch(() => {
                window.location.href = 'login.php';
            });
        }
    });
}

function renderPagination(total, limit, page, containerId, callback) {
    const totalPages = Math.ceil(total / limit);
    const nav = document.getElementById(containerId);
    if (!nav) return;
    nav.innerHTML = '';
    if (totalPages <= 1) return;
    const maxVisible = 5;
    let html = '<ul class="pagination justify-content-center mb-0">';
    const prevDisabled = page <= 1 ? ' disabled' : '';
    html += `<li class="page-item${prevDisabled}"><a class="page-link" href="#" onclick="${callback}(${page - 1}); return false;">&laquo;</a></li>`;
    let start = Math.max(1, page - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages, start + maxVisible - 1);
    if (end - start < maxVisible - 1) start = Math.max(1, end - maxVisible + 1);
    for (let i = start; i <= end; i++) {
        const active = i === page ? ' active' : '';
        html += `<li class="page-item${active}"><a class="page-link" href="#" onclick="${callback}(${i}); return false;">${i}</a></li>`;
    }
    const nextDisabled = page >= totalPages ? ' disabled' : '';
    html += `<li class="page-item${nextDisabled}"><a class="page-link" href="#" onclick="${callback}(${page + 1}); return false;">&raquo;</a></li>`;
    html += '</ul>';
    nav.innerHTML = html;
}

// Funciones globales de Interfaz (Alertas y Confirmaciones)
window.showAlert = function(message, type = 'info', title = null) {
    return new Promise((resolve) => {
        const container = document.getElementById('sysToastContainer');
        if (!container) { alert(message); resolve(); return; }

        let bgClass = 'text-bg-info';
        let iconClass = 'fas fa-info-circle';
        let defaultTitle = 'Información';

        if (type === 'success') {
            bgClass = 'text-bg-success';
            iconClass = 'fas fa-check-circle';
            defaultTitle = '¡Éxito!';
        } else if (type === 'error') {
            bgClass = 'text-bg-danger';
            iconClass = 'fas fa-times-circle';
            defaultTitle = 'Error';
        } else if (type === 'warning') {
            bgClass = 'text-bg-warning text-dark';
            iconClass = 'fas fa-exclamation-triangle';
            defaultTitle = 'Advertencia';
        }

        const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
        const toastHtml = `
            <div id="${toastId}" class="toast ${bgClass} border-0 mb-2 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="toast-header ${bgClass} border-bottom-0 rounded-top-3">
                    <i class="${iconClass} me-2" style="font-size: 1.2rem;"></i>
                    <strong class="me-auto fs-6">${title || defaultTitle}</strong>
                    <button type="button" class="btn-close ${type !== 'warning' ? 'btn-close-white' : ''} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body fw-medium" style="font-size: 1rem; opacity: 0.95;">
                    ${message}
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        
        const toast = new bootstrap.Toast(toastEl);
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
            resolve();
        });

        toast.show();
    });
};

window.showConfirm = function(message, title = 'Confirmación') {
    return new Promise((resolve) => {
        const modalEl = document.getElementById('sysModal');
        if (!modalEl) { resolve(confirm(message)); return; } // Fallback
        
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) modal = new bootstrap.Modal(modalEl);
        
        const iconContainer = document.getElementById('sysModalIcon');
        const titleEl = document.getElementById('sysModalTitle');
        const msgEl = document.getElementById('sysModalMessage');
        const btnCancel = document.getElementById('sysModalBtnCancel');
        const btnConfirm = document.getElementById('sysModalBtnConfirm');
        
        btnCancel.style.display = 'inline-block';
        btnConfirm.textContent = 'Confirmar';
        btnConfirm.className = 'btn btn-primary px-4 fw-bold';
        btnCancel.className = 'btn btn-outline-secondary px-4 fw-bold';
        
        iconContainer.innerHTML = '<i class="fas fa-question-circle text-primary" style="font-size: 3.5rem;"></i>';
        titleEl.textContent = title;
        msgEl.textContent = message;
        
        const onConfirm = () => { cleanup(); resolve(true); };
        const onCancel = () => { cleanup(); resolve(false); };
        
        btnConfirm.addEventListener('click', onConfirm);
        btnCancel.addEventListener('click', onCancel);
        
        const cleanup = () => {
            btnConfirm.removeEventListener('click', onConfirm);
            btnCancel.removeEventListener('click', onCancel);
            modal.hide();
        };
        
        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            btnConfirm.removeEventListener('click', onConfirm);
            btnCancel.removeEventListener('click', onCancel);
            resolve(false);
        }, { once: true });
        
        modal.show();
    });
};
