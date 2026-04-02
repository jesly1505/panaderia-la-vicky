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

        // Update user info in navbar
        const userNameEl = document.querySelector('.top-navbar span');
        if (userNameEl) {
            userNameEl.innerHTML = `<i class="fas fa-user-circle"></i> ${data.user.nombre} <small class="text-muted">(${data.user.rol})</small>`;
        }

        // Role-based restrictions
        if (data.user.rol !== 'Administrador') {
            // Hide configuration link
            const configLink = document.querySelector('a[href="configuracion.html"]');
            if (configLink) configLink.remove();

            // Redirect if on forbidden page
            if (window.location.pathname.includes('configuracion.html')) {
                window.location.href = 'index.html';
            }

            // Hide delete/admin-only actions after a short delay to account for dynamic rendering
            const observer = new MutationObserver(() => {
                const restrictedActions = document.querySelectorAll('.btn-outline-danger, .btn-danger, .delete-btn');
                restrictedActions.forEach(btn => {
                    // Check if it's a delete button (trash icon or specific text)
                    if (btn.innerText.toLowerCase().includes('eliminar') ||
                        btn.querySelector('.fa-trash') ||
                        btn.classList.contains('btn-delete')) {
                        btn.remove();
                    }
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
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
