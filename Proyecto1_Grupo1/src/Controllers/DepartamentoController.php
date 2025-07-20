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
        if (!isset($headers['Authorization'])) { return false; }
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (!Security::validateTokenJWT($token)) { return false; }
        return Security::getDataJwt($token);
    }

    public function getAll()
    {
        if (!$this->validateToken()) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $departamentos = $this->departamentoModel->getAllDepartamentos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $departamentos);
    }

    public function create()
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) { return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST); }
        if ($this->departamentoModel->createDepartamento($data['nombre'])) {
            return responseHTTP::response(responseHTTP::$HTTP_201_CREATED, "Departamento creado");
        } else { return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR); }
    }

    public function update(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) { return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST); }
        if ($this->departamentoModel->updateDepartamento($id, $data['nombre'])) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Departamento actualizado");
        } else { return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR); }
    }

    public function delete(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        if ($this->departamentoModel->deleteDepartamento($id)) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Departamento eliminado");
        } else { return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR); }
    }
}