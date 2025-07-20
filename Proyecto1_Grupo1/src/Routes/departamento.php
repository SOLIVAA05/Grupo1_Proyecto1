<?php
use App\Controllers\DepartamentoController;

$departamentoController = new DepartamentoController();
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Proyecto1_Grupo1/public/departamento';
$uri = str_replace($base_path, '', $request_uri);
$uri = trim($uri, '/');
$id = is_numeric($uri) ? (int)$uri : null;

switch ($request_method) {
    case 'GET': $departamentoController->getAll(); break;
    case 'POST': $departamentoController->create(); break;
    case 'PUT':
        if ($id) { $departamentoController->update($id); } 
        else { responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST, "ID de departamento no proporcionado."); }
        break;
    case 'DELETE':
        if ($id) { $departamentoController->delete($id); }
        else { responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST, "ID de departamento no proporcionado."); }
        break;
    default: header("HTTP/1.1 405 Method Not Allowed"); break;
}