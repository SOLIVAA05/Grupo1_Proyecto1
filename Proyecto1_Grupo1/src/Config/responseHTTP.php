<?php
namespace App\Config;

class responseHTTP
{
    // Códigos de estado HTTP comunes
    public static $HTTP_200_OK = ["code" => 200, "message" => "OK"];
    public static $HTTP_201_CREATED = ["code" => 201, "message" => "Creado"];
    public static $HTTP_400_BAD_REQUEST = ["code" => 400, "message" => "Solicitud incorrecta"];
    public static $HTTP_401_UNAUTHORIZED = ["code" => 401, "message" => "No autorizado"];
    public static $HTTP_404_NOT_FOUND = ["code" => 404, "message" => "No encontrado"];
    public static $HTTP_500_INTERNAL_SERVER_ERROR = ["code" => 500, "message" => "Error interno del servidor"];

    /**
     * Envía una respuesta exitosa (200 o 201)
     * @param array $status El código y mensaje de estado
     * @param mixed $data Los datos a enviar
     */
    final public static function response(array $status, $data)
    {
        header('Content-Type: application/json');
        http_response_code($status['code']);
        $response = [
            "status" => $status['code'],
            "message" => $status['message'],
            "data" => $data
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Envía una respuesta de error (400, 401, 404, 500)
     * @param array $status El código y mensaje de estado
     */
    final public static function responseError(array $status)
    {
        header('Content-Type: application/json');
        http_response_code($status['code']);
        $response = [
            "status" => $status['code'],
            "message" => $status['message']
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}