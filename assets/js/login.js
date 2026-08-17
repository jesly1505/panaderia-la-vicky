document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (!loginForm) {
        return;
    }

    const handleLogin = async (e) => {
        if (e) e.preventDefault();
        
        if (errorMessage) errorMessage.style.display = 'none';

        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('../backend/api.php?route=login', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.location.replace('index.php');
            } else {
                if (errorMessage) {
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error procesando login:', error);
            if (errorMessage) {
                errorMessage.textContent = 'Error de conexión con el servidor.';
                errorMessage.style.display = 'block';
            }
        }
    };

    loginForm.addEventListener('submit', handleLogin);
});
