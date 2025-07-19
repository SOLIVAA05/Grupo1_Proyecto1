<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /Proyecto/Public/login'); // Redirigir al login si no está autenticado
    exit;
}

$username = $_SESSION['username'];
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; background-color: #e9ecef; }
        .dashboard-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: center; }
        .dashboard-container h2 { margin-bottom: 15px; color: #333; }
        .dashboard-container p { margin-bottom: 10px; color: #555; }
        .dashboard-container .logout-button { padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; }
        .dashboard-container .logout-button:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>¡Bienvenido, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Tu rol es: <strong><?php echo htmlspecialchars($rol); ?></strong></p>
        <p>Esta es la interfaz principal de tu aplicación.</p>
        <a href="/Proyecto/Public/logout" class="logout-button">Cerrar Sesión</a>
    </div>
</body>
</html>
