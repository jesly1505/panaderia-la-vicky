/**
 * Helpers compartidos para el frontend de "La Vicky".
 * - api()        -> fetch hacia backend/api.php devolviendo JSON.
 * - formatMoney  -> formatea un número como moneda.
 * - escapeHtml   -> escapa texto antes de insertarlo con innerHTML.
 */

const API_BASE = '../backend/api.php';

/**
 * @param {string} route    Nombre de la ruta (ej. 'get_ventas').
 * @param {string} method   'GET' | 'POST'.
 * @param {object|FormData|null} body  Cuerpo para POST (JSON o FormData).
 * @param {object} params   Parámetros de query adicionales (ej. {id: 3}).
 * @returns {Promise<object>} Respuesta JSON de la API.
 */
async function api(route, method = 'GET', body = null, params = {}) {
    const qs = new URLSearchParams(Object.assign({ route: route }, params)).toString();
    const opts = { method: method, headers: {} };
    if (method === 'POST' && body) {
        if (body instanceof FormData) {
            opts.body = body;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
    }
    const res = await fetch(API_BASE + '?' + qs, opts);
    return res.json();
}

function formatMoney(value) {
    return '$ ' + Number(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
