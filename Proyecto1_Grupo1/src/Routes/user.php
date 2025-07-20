<?php
use App\Controllers\UserController;

$userController = new UserController();

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Proyecto1_Grupo1/public/user';

// Limpiar la URI base
$uri = str_replace($base_path, '', $request_uri);
$uri = trim($uri, '/');
$uri_segments = explode('/', $uri);

// El ID del usuario, si existe, será el primer segmento después de /user/
$userId = $uri_segments[0] ?? null;

switch ($request_method) {
    case 'GET':
        $userController->getAllUsers();
        break;
    case 'POST':
        $userController->createUser();
        break;
    case 'PUT':
        if ($userId && is_numeric($userId)) {
            $userController->updateUser((int)$userId);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de usuario no válido"]);
        }
        break;
    case 'DELETE':
        if ($userId && is_numeric($userId)) {
            $userController->deleteUser((int)$userId);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de usuario no válido"]);
        }
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        break;
}