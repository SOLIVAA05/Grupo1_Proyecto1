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

    /**
     * Sube un nuevo documento (Tarea de Alison).
     */
    public function upload()
    {
        $tokenData = $this->validateToken();
        if (!$tokenData) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        // Validar que se haya subido un archivo y no haya errores
        if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        // Validar que tengamos el id de la carpeta
        if (!isset($_POST['idCarpeta']) || !is_numeric($_POST['idCarpeta'])) {
             return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $file = $_FILES['documento'];
        $idCarpeta = (int)$_POST['idCarpeta'];
        $idUsuario = (int)$tokenData->data->idUsuario;

        // Generar un nombre de archivo único para evitar sobreescrituras
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeOriginalName = preg_replace("/[^a-zA-Z0-9-_\.]/", "", $originalName);
        $uniqueName = uniqid() . '-' . $safeOriginalName . '.' . $extension;
        
        $destination = $this->storagePath . $uniqueName;

        // Mover el archivo a nuestra carpeta de almacenamiento
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Si se movió correctamente, guardar en la BD
            $nombre = $file['name'];
            $ruta = $destination;
            $tipo = $file['type'];
            $tamanoKB = round($file['size'] / 1024, 2);

            if ($this->documentoModel->createArchivo($nombre, $ruta, $tipo, $tamanoKB, $idCarpeta, $idUsuario)) {
                return responseHTTP::response(responseHTTP::$HTTP_201_CREATED, "Archivo subido exitosamente.");
            } else {
                // Si falla el guardado en BD, borrar el archivo para no dejar basura
                unlink($destination);
                return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
            }
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Consulta todos los documentos (Tarea de Kevin).
     */
    public function getAll()
    {
        if (!$this->validateToken()) {
            return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $documentos = $this->documentoModel->getAllArchivos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $documentos);
    }
}