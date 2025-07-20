<?php
use App\Controllers\DocumentController;

$documentController = new DocumentController();
$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        $documentController->getAll();
        break;
    case 'POST':
        $documentController->upload();
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        break;
}