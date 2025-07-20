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

    /**
     * Valida el token JWT, buscándolo primero en la cabecera de autorización
     * y, si no lo encuentra, en un parámetro 'token' de la URL.
     */
    private function validateToken()
    {
        $token = null;
        // 1. Buscar en la cabecera (método estándar para peticiones AJAX)
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        } 
        // 2. Si no está, buscar en la URL (para descargas y enlaces directos)
        else if (isset($_GET['token'])) {
            $token = $_GET['token'];
        } 
        // Si no está en ningún lado, no hay permiso.
        else {
            return false;
        }

        if (!Security::validateTokenJWT($token)) { return false; }
        return Security::getDataJwt($token);
    }

    public function upload()
    {
        // Esta función se mantiene exactamente igual que antes
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
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo mover el archivo. Verifique permisos.");
        }
    }

    public function getAll()
    {
        // Esta función se mantiene exactamente igual que antes
        if (!$this->validateToken()) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $documentos = $this->documentoModel->getAllArchivos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $documentos);
    }

    public function download(int $id)
    {
        // Esta función se mantiene exactamente igual que antes
        if (!$this->validateToken()) {
            http_response_code(401);
            die("No autorizado");
        }
        $fileData = $this->documentoModel->getArchivoById($id);
        if (!$fileData || !file_exists($fileData['rutaAlmacenamiento'])) {
            http_response_code(404);
            die("Archivo no encontrado en el servidor o en la base de datos.");
        }
        $filePath = $fileData['rutaAlmacenamiento'];
        $fileName = $fileData['nombre'];
        $fileMime = $fileData['tipoMIME'];
        header("Content-Type: " . $fileMime);
        header("Content-Disposition: inline; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . filesize($filePath));
        header('Cache-Control: private');
        header('Pragma: private');
        ob_clean();
        flush();
        readfile($filePath);
        exit;
    }
}