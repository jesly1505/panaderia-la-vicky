document.addEventListener('DOMContentLoaded', async () => {
    // Check session except on login page
    if (window.location.href.includes('login.html')) return;

    try {
        const res = await fetch('../backend/api.php?route=check_session');
        const data = await res.json();

        if (!data.logged_in) {
            console.log('No session found, redirecting to login...');
            window.location.href = 'login.html';
            return;
        }

        // Update user info in navbar (el sidebar ya lo muestra en servidor; se mantiene por consistencia)
        const userNameEl = document.querySelector('.top-navbar span');
        if (userNameEl) {
            userNameEl.innerHTML = `<i class="fas fa-user-circle"></i> ${data.user.nombre} <small class="text-muted">(${data.user.rol})</small>`;
        }
    } catch (e) {
        console.error('Error checking session:', e);
    }
});

function logout() {
    if (confirm('¿Cerrar sesión?')) {
        fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html');
    }
}
