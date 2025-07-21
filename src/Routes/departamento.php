<?php
use App\Controllers\DepartamentoController;

$departamentoController = new DepartamentoController();

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        $departamentoController->getAll();
        break;
    case 'POST':
        $departamentoController->create();
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        break;
}
