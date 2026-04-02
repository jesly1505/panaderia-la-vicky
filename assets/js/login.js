document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (!loginForm) {
        console.error('No se encontró el formulario de login (id="loginForm")');
        return;
    }

    const handleLogin = async (e) => {
        if (e) e.preventDefault();
        
        console.log('Iniciando proceso de login...');
        errorMessage.style.display = 'none';

        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('../backend/api.php?route=login', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('Respuesta del servidor:', data);

            if (data.success) {
                console.log('Login OK - Redirigiendo a Dashboard (Timestamp: ' + Date.now() + ')');
                window.location.replace('index.php');
            } else {
                errorMessage.textContent = data.message;
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error procesando login:', error);
            errorMessage.textContent = 'Error de conexión con el servidor.';
            errorMessage.style.display = 'block';
        }
    };

    // Escuchar el evento submit del formulario
    loginForm.addEventListener('submit', handleLogin);

    // Asegurar que Enter en los campos también dispare el login
    const inputs = loginForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleLogin(e);
            }
        });
    });
});
