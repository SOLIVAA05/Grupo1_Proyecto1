<?php
// --- AUTOLOADER DE COMPOSER Y CONFIGURACIÓN DE ERRORES ---
// Carga todas las librerías y nuestras clases
require_once __DIR__ . '/../vendor/autoload.php';
// Carga nuestro manejador de errores personalizado
require_once __DIR__ . '/../src/Config/errorlogs.php';

// --- CABECERAS CORS ---
// Permite que cualquier dominio (origen) pueda hacer peticiones a nuestra API
header("Access-Control-Allow-Origin: *");
// Permite los siguientes métodos HTTP
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE");
// Permite las siguientes cabeceras en la petición (importante para enviar el token JWT)
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- RESPUESTA INICIAL PARA PROBAR ---
echo "La API está funcionando correctamente...";
?>