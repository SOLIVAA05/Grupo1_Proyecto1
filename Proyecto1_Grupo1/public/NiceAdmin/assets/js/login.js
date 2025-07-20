document.addEventListener('DOMContentLoaded', function() {
    // Apuntamos al formulario por su ID
    const loginForm = document.querySelector('#login-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Evita que la página se recargue

            const usernameInput = document.querySelector('#yourUsername');
            const passwordInput = document.querySelector('#yourPassword');
            const feedbackDiv = document.querySelector('#login-feedback');

            const data = {
                user_name: usernameInput.value,
                password: passwordInput.value
            };

            // La URL completa de nuestro endpoint de login
            const url = 'http://localhost/Proyecto1_Grupo1/public/auth/login';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 200 && result.data.token) {
                    // ¡Login exitoso!
                    localStorage.setItem('jwt_token', result.data.token); // Guardamos el token
                    
                    // CORRECCIÓN: Construimos la URL completa para redirigir
                    const dashboardUrl = window.location.origin + '/Proyecto1_Grupo1/public/index.html';
                    window.location.href = dashboardUrl;

                } else {
                    // Error en el login
                    feedbackDiv.textContent = 'Usuario o contraseña incorrectos.';
                    usernameInput.classList.add('is-invalid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                feedbackDiv.textContent = 'Ocurrió un error de conexión con la API.';
                usernameInput.classList.add('is-invalid');
            });
        });
    }
});