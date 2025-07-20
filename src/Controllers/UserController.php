<?php
namespace App\Controllers;

use App\Config\responseHTTP;
use App\Models\UserModel;
use App\Config\Security;

class UserController
{
    // ... (propiedades y métodos __construct, validateToken, getLogin, getAllUsers, createUser se quedan como están) ...
    private static $validar_rol = '/^[1-9]\d*$/';
    private static $validar_numero = '/^[0-9]+$/';
    private static $validar_texto = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/';
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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

    public function getLogin(string $endPoint)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }
        try {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);
            if (!isset($data['user_name']) || !isset($data['password'])) {
                return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
            }
            $username = $data['user_name'];
            $password = $data['password'];
            $user = $this->userModel->getUserByUsername($username);
            if (!$user || !Security::validatePassword($password, $user['password'])) {
                return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
            }
            $tokenData = [ 'idUsuario' => $user['idUsuario'], 'nombre' => $user['nombre'], 'idRol' => $user['idRol'] ];
            $token = Security::createTokenJWT($tokenData);
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, ["token" => $token]);
        } catch (\Exception $e) {
            error_log("UserController::getLogin -> " . $e->getMessage());
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllUsers()
    {
        if (!$this->validateToken()) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $users = $this->userModel->getAllUsers();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $users);
    }

    public function createUser()
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['nombre'], $data['user_name'], $data['password'], $data['idRol'], $data['idDepartamento'])) {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $nombre = $data['nombre'];
        $userName = $data['user_name'];
        $password = Security::createPassword($data['password']);
        $idRol = $data['idRol'];
        $idDepartamento = $data['idDepartamento'];

        if ($this->userModel->createUser($nombre, $userName, $password, $idRol, $idDepartamento)) {
            return responseHTTP::response(responseHTTP::$HTTP_201_CREATED, "Usuario creado exitosamente");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Actualiza un usuario (ruta protegida, solo para admin).
     */
    public function updateUser(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['nombre'], $data['user_name'], $data['idRol'], $data['idDepartamento'])) {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        if ($this->userModel->updateUser($id, $data['nombre'], $data['user_name'], $data['idRol'], $data['idDepartamento'])) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Usuario actualizado exitosamente");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Elimina (desactiva) un usuario (ruta protegida, solo para admin).
     */
    public function deleteUser(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData || $tokenData->data->idRol != 1) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        if ($this->userModel->deleteUser($id)) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Usuario desactivado exitosamente");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }
}