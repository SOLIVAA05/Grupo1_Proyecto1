<?php
use App\Controllers\DocumentController;

$documentController = new DocumentController();
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];


$url_parts = parse_url($request_uri);
$path = $url_parts['path'];

// 2. Limpiamos la ruta base para quedarnos solo con lo que viene después de '/documento'
$base_path = '/Proyecto1_Grupo1/public/documento';
$uri = str_replace($base_path, '', $path);
$uri = trim($uri, '/');

// 3. El ID del documento, si existe, será el único segmento numérico que queda
$id = is_numeric($uri) && $uri != '' ? (int)$uri : null;

// 4. Ahora sí, decidimos qué método del controlador llamar
if ($request_method === 'GET') {
    if ($id !== null) {
        // Si hay un ID en la URL (ej. /documento/3), llamamos a la función de descarga
        $documentController->download($id);
    } else {
        // Si NO hay ID (ej. /documento), llamamos a la función de obtener la lista
        $documentController->getAll();
    }
} elseif ($request_method === 'POST') {
    // Si el método es POST, es para subir un archivo nuevo
    $documentController->upload();
} else {
    // Si es cualquier otro método (PUT, DELETE, etc.) que no hemos definido
    header("HTTP/1.1 405 Method Not Allowed");
}