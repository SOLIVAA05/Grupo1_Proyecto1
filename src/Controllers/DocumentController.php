<?php
namespace App\Controllers;

use App\Config\responseHTTP;
use App\Models\DocumentoModel;
use App\Models\VersionDocumentoModel; // Importamos el nuevo modelo
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
        $token = null;
        $headers = getallheaders();
        if (isset($headers['Authorization'])) { $token = str_replace('Bearer ', '', $headers['Authorization']); } 
        else if (isset($_GET['token'])) { $token = $_GET['token']; } 
        else { return false; }
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
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo mover el archivo. Verifique permisos.");
        }
    }

    public function getAll()
    {
        if (!$this->validateToken()) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }
        $documentos = $this->documentoModel->getAllArchivos();
        return responseHTTP::response(responseHTTP::$HTTP_200_OK, $documentos);
    }

    public function download(int $id)
    {
        if (!$this->validateToken()) { http_response_code(401); die("No autorizado"); }
        $fileData = $this->documentoModel->getArchivoById($id);
        if (!$fileData || !file_exists($fileData['rutaAlmacenamiento'])) { http_response_code(404); die("Archivo no encontrado."); }
        
        $filePath = $fileData['rutaAlmacenamiento'];
        $fileName = $fileData['nombre'];
        $fileMime = $fileData['tipoMIME'];
        header("Content-Type: " . $fileMime);
        header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . filesize($filePath));
        ob_clean();
        flush();
        readfile($filePath);
        exit;
    }

    /**
     * Actualiza un documento existente, creando una versión del anterior.
     */
    public function update(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        $versionModel = new VersionDocumentoModel();
        $archivoActual = $this->documentoModel->getArchivoById($id);

        if (!$archivoActual || !file_exists($archivoActual['rutaAlmacenamiento'])) {
            return responseHTTP::responseError(responseHTTP::$HTTP_404_NOT_FOUND, "El documento a actualizar no existe.");
        }

        $comentarioVersion = "Versión anterior a la actualización del " . date('Y-m-d H:i:s');
        $versionModel->createVersion($id, (int)$tokenData->data->idUsuario, $archivoActual['rutaAlmacenamiento'], $comentarioVersion);

        $rutaFinal = $archivoActual['rutaAlmacenamiento'];
        $tipoFinal = $archivoActual['tipoMIME'];
        $nombreFinal = $archivoActual['nombre'];
        $tamanoFinal = round(filesize($archivoActual['rutaAlmacenamiento']) / 1024, 2);

        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['documento'];
            $uniqueName = uniqid('doc_') . '-' . basename($file['name']);
            $destinationPath = $this->storagePath . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                $rutaFinal = $destinationPath;
                $tipoFinal = $file['type'];
                $tamanoFinal = round($file['size'] / 1024, 2);
                $nombreFinal = $file['name'];
            } else {
                return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo mover el nuevo archivo.");
            }
        }
        
        $idUsuario = (int)$tokenData->data->idUsuario;
        if ($this->documentoModel->updateArchivo($id, $nombreFinal, $rutaFinal, $tipoFinal, $tamanoFinal, $idUsuario)) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Documento actualizado exitosamente.");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo actualizar el registro en la BD.");
        }
    }
}