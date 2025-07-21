<?php
use App\Controllers\UserController;

// Instanciamos el controlador que usaremos
$userController = new UserController();

// Obtenemos la ruta de la URL
$route = $_SERVER['REQUEST_URI'];

// Definimos el endpoint para el login
$loginEndpoint = "/Proyecto1_Grupo1/public/auth/login";

// Comparamos la ruta solicitada con nuestro endpoint de login
if ($route === $loginEndpoint) {
    $userController->getLogin($loginEndpoint);
} else {
    // Si no es la ruta de login, podrías manejar un error 404 aquí
    // Por ahora, lo dejaremos para que el index.php lo maneje.
}