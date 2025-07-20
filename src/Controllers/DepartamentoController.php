<?php
namespace App\Controllers;

use App\Config\responseHTTP;
use App\Models\DepartamentoModel;
use App\Config\Security;

class DepartamentoController
{
    private $departamentoModel;

    public function __construct()
    {
        $this->departamentoModel = new DepartamentoModel();
        new Security();
    }

    private function validateToken()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }
        
        $authorizationHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authorizationHeader);

        if (!Security::validateTokenJWT($token)) {
            return false;
        }
        return Security::getDataJwt($token);
    }

    /**
     * Obtiene todos los departamentos (ruta protegida).
     */
    public function getAll()
    {
        if (!$this->validateToken()) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $departamentos = $this->departamentoModel->getAllDepartamentos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $departamentos);
    }

    /**
     * Crea un nuevo departamento (ruta protegida, solo para admin).
     */
    public function create()
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) { // Solo rol de Administrador (ID 1)
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        if ($this->departamentoModel->createDepartamento($data['nombre'])) {
            return responseHTTP::response(responseHTTP::$HTTP_201_CREATED, "Departamento creado exitosamente");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }
}