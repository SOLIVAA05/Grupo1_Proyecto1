<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/errorlogs.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Proyecto1_Grupo1/public';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}
$uri_segments = explode('/', trim($request_uri, '/'));
$resource = $uri_segments[0] ?? '';

switch ($resource) {
    case 'auth': require_once __DIR__ . '/../src/Routes/auth.php'; break;
    case 'user': require_once __DIR__ . '/../src/Routes/user.php'; break;
    case 'departamento': require_once __DIR__ . '/../src/Routes/departamento.php'; break;
    case 'documento': require_once __DIR__ . '/../src/Routes/documento.php'; break; // RUTA AÑADIDA
    default:
        if ($request_uri === '/' || $request_uri === '') {
            echo json_encode(["message" => "La API está funcionando correctamente..."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ruta no encontrada"]);
        }
        break;
}
