// assets/js/common.js
document.addEventListener('DOMContentLoaded', async () => {
    // Si estamos en login o password reset, no validar sesión
    const path = window.location.pathname.toLowerCase();
    if (path.includes('login') || path.includes('forgot_password') || path.includes('reset_password')) {
        return;
    }

    try {
        const res = await fetch('../backend/api.php?route=check_session');
        const data = await res.json();

        if (!data.logged_in) {
            console.log('No session found, redirecting to login...');
            window.location.href = 'login.php';
            return;
        }

        // Actualizar datos del usuario en la barra superior si existe el elemento
        const userNameEl = document.querySelector('.top-navbar .user-name-display');
        if (userNameEl && data.user) {
            userNameEl.textContent = data.user.nombre;
        }
    } catch (e) {
        console.error('Error checking session:', e);
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
