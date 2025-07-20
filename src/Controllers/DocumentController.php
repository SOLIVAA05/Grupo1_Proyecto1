<?php
namespace App\Controllers;

use App\Config\responseHTTP;
use App\Models\DocumentoModel;
use App\Config\Security;

class DocumentController
{
    private $documentoModel;
    private $storagePath = __DIR__ . '/../../storage/docs/';

    public function __construct()
    {
        $this->documentoModel = new DocumentoModel();
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

    public function upload()
    {
        $tokenData = $this->validateToken();
        if (!$tokenData) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST); }
        if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) { return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST, "No se recibió el archivo o hubo un error."); }
        if (!isset($_POST['idCarpeta']) || !is_numeric($_POST['idCarpeta'])) { return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST, "Falta el ID de la carpeta."); }

        $file = $_FILES['documento'];
        $idCarpeta = (int)$_POST['idCarpeta'];
        $idUsuario = (int)$tokenData->data->idUsuario;
        $uniqueName = uniqid('doc_') . '-' . basename($file['name']);
        $destinationPath = $this->storagePath . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
            if ($this->documentoModel->createArchivo($file['name'], $destinationPath, $file['type'], round($file['size'] / 1024, 2), $idCarpeta, $idUsuario)) {
                return responseHTTP::response(responseHTTP::$HTTP_201_CREATED, "Archivo subido exitosamente.");
            } else {
                unlink($destinationPath);
                return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "Error al registrar el archivo en la BD.");
            }
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo mover el archivo. Verifique permisos de la carpeta 'storage/docs'.");
        }
    }

    public function getAll()
    {
        if (!$this->validateToken()) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $documentos = $this->documentoModel->getAllArchivos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $documentos);
    }
}
