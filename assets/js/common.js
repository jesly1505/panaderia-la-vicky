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
    if (confirm('¿Desea cerrar la sesión?')) {
        fetch('../backend/api.php?route=logout').then(() => {
            window.location.href = 'login.php';
        }).catch(() => {
            window.location.href = 'login.php';
        });
    }
}
