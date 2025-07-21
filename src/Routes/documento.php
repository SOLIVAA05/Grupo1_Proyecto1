<?php
use App\Controllers\DocumentController;

$documentController = new DocumentController();
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$url_parts = parse_url($request_uri);
$path = $url_parts['path'];
$base_path = '/Proyecto1_Grupo1/public/documento';
$uri = str_replace($base_path, '', $path);
$uri = trim($uri, '/');
$id = is_numeric($uri) && $uri != '' ? (int)$uri : null;

if ($request_method === 'GET') {
    if ($id !== null) {
        $documentController->download($id);
    } else {
        $documentController->getAll();
    }
} elseif ($request_method === 'POST') {
    if ($id !== null) {
        // Si la URL es /documento/{id} y el método es POST, es una ACTUALIZACIÓN
        $documentController->update($id);
    } else {
        // Si la URL es solo /documento, es una SUBIDA NUEVA
        $documentController->upload();
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
}