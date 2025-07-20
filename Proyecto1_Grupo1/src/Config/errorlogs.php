<?php
namespace App\Config;

// Establecer la zona horaria para Honduras
date_default_timezone_set("America/Tegucigalpa");

// --- MANEJO DE ERRORES Y EXCEPCIONES ---

// Esta función se ejecutará cada vez que ocurra un error en PHP
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Este código de error no está incluido en error_reporting
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// Esta función se ejecutará para excepciones no capturadas
set_exception_handler(function($exception) {
    // Desactivar el reporte de errores estándar en pantalla
    error_reporting(0);
    ini_set('display_errors', '0');

    // Habilitar el registro de errores en un archivo
    ini_set('log_errors', '1');
    // Definir la ruta del archivo de log
    ini_set('error_log', __DIR__ . '/../Logs/php-error.log');

    $errorMessage = sprintf(
        "Excepción no capturada: '%s' en %s:%s\nStack trace:\n%s\n",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    // Escribir el mensaje en el archivo de log
    error_log($errorMessage);
});