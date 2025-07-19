<?php
use App\Config\ResponseHTTP;
use App\Config\Security;
use App\DB\connectionDB;

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /Proyecto/Public/dashboard'); // Ajusta la ruta según tu configuración
    exit;
}

// Si la solicitud es POST (envío del formulario de login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(ResponseHTTP::status400('Todos los campos son obligatorios.'));
        exit;
    }

    try {
        $conn = connectionDB::getConnection();
        // Buscar el usuario y su rol
        $stmt = $conn->prepare("SELECT id, user_name, password, rol FROM Usuario WHERE user_name = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (Security::validatePassword($password, $user['password'])) {
                // Contraseña correcta, iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['user_name'];
                $_SESSION['rol'] = $user['rol']; // Guardar el rol del usuario

                // Redirigir al dashboard
                header('Location: /Proyecto/Public/dashboard'); // Ajusta la ruta según tu configuración
                exit;
            } else {
                echo json_encode(ResponseHTTP::status401('Credenciales incorrectas.'));
            }
        } else {
            echo json_encode(ResponseHTTP::status401('Usuario no encontrado.'));
        }
    } catch (PDOException $e) {
        error_log('Error de base de datos en login: ' . $e->getMessage());
        echo json_encode(ResponseHTTP::status500('Error en el servidor.'));
    }
    exit;
} else {
    // Si la solicitud es GET, mostrar el formulario de login
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <style>
            body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
            .login-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
            .login-container h2 { text-align: center; margin-bottom: 20px; }
            .login-container label { display: block; margin-bottom: 8px; }
            .login-container input[type="text"],
            .login-container input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .login-container button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            .login-container button:hover { background-color: #0056b3; }
            .error-message { color: red; text-align: center; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Iniciar Sesión</h2>
            <form action="/Proyecto/Public/login" method="POST">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Entrar</button>
                <?php
                // Mostrar mensajes de error si existen (ej. desde una redirección con error)
                if (isset($_SESSION['login_error'])) {
                    echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
                    unset($_SESSION['login_error']); // Limpiar el error después de mostrarlo
                }
                ?>
            </form>
        </div>
    </body>
    </html>
    <?php
}
