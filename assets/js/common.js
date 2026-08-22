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
