<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class DocumentoModel extends connectionDB
{
    /**
     * Inserta un nuevo archivo usando el procedimiento almacenado 'subir_archivo'.
     */
    public function createArchivo(string $nombre, string $ruta, string $tipo, float $tamano, int $idCarpeta, int $idUsuario): bool
    {
        try {
            $query = "CALL subir_archivo(:p_nombre, :p_ruta, :p_mime, :p_tamano, :p_idCarpeta, :p_idUsuario)";
            
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':p_nombre', $nombre);
            $statement->bindParam(':p_ruta', $ruta);
            $statement->bindParam(':p_mime', $tipo);
            $statement->bindParam(':p_tamano', $tamano);
            $statement->bindParam(':p_idCarpeta', $idCarpeta, PDO::PARAM_INT);
            $statement->bindParam(':p_idUsuario', $idUsuario, PDO::PARAM_INT);
            
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DocumentoModel::createArchivo -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los archivos para mostrarlos en la tabla.
     */
    public function getAllArchivos()
    {
        try {
            $query = "
                SELECT 
                    a.idArchivo, a.nombre, a.tipoMIME, a.tamanoKB, a.fechaSubida, 
                    u.nombre as nombreUsuarioSube, c.nombre as nombreCarpeta
                FROM Archivo a
                JOIN Usuario u ON a.idUsuarioSube = u.idUsuario
                JOIN Carpeta c ON a.idCarpeta = c.idCarpeta
                WHERE a.estado = 1
                ORDER BY a.fechaSubida DESC
            ";
            $statement = $this->connection->query($query);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("DocumentoModel::getAllArchivos -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los datos de un solo archivo por su ID.
     */
    public function getArchivoById(int $id)
    {
        try {
            $query = "SELECT nombre, rutaAlmacenamiento, tipoMIME FROM Archivo WHERE idArchivo = :id AND estado = 1";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("DocumentoModel::getArchivoById -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un archivo existente en la base de datos.
     * @return bool
     */
    public function updateArchivo(int $idArchivo, string $nuevoNombre, string $nuevaRuta, string $nuevoTipoMIME, float $nuevoTamano, int $idUsuarioSube): bool
    {
        try {
            $query = "UPDATE Archivo 
                      SET nombre = :nombre, 
                          rutaAlmacenamiento = :ruta, 
                          tipoMIME = :tipo, 
                          tamanoKB = :tamano,
                          idUsuarioSube = :idUsuario,
                          fechaSubida = CURRENT_TIMESTAMP
                      WHERE idArchivo = :id";
            
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $idArchivo, PDO::PARAM_INT);
            $statement->bindParam(':nombre', $nuevoNombre);
            $statement->bindParam(':ruta', $nuevaRuta);
            $statement->bindParam(':tipo', $nuevoTipoMIME);
            $statement->bindParam(':tamano', $nuevoTamano);
            $statement->bindParam(':idUsuario', $idUsuarioSube, PDO::PARAM_INT);

            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DocumentoModel::updateArchivo -> " . $e->getMessage());
            return false;
        }
    }
/**
     * Actualiza un documento existente, creando una versión del anterior.
     */
    public function update(int $id)
    {
        $tokenData = $this->validateToken();
        if (!$tokenData) { return responseHTTP::responseError(responseHTTP::$HTTP_401_UNAUTHORIZED); }

        // Como los formularios HTML no soportan PUT con archivos, usamos POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return responseHTTP::responseError(responseHTTP::$HTTP_400_BAD_REQUEST);
        }

        // 1. Obtener los datos del archivo actual antes de modificarlo
        $documentoModel = new DocumentoModel();
        $versionModel = new VersionDocumentoModel();
        $archivoActual = $documentoModel->getArchivoById($id);

        if (!$archivoActual) {
            return responseHTTP::responseError(responseHTTP::$HTTP_404_NOT_FOUND, "El documento que intentas actualizar no existe.");
        }

        // 2. Guardar el archivo actual como una versión
        $comentarioVersion = "Versión anterior a la actualización del " . date('Y-m-d H:i:s');
        $versionModel->createVersion($id, $tokenData->data->idUsuario, $archivoActual['rutaAlmacenamiento'], $comentarioVersion);

        // 3. Procesar el nuevo archivo subido (si existe)
        $nuevoNombre = $_POST['nombre'] ?? $archivoActual['nombre']; // Usar nuevo nombre o mantener el antiguo
        $rutaFinal = $archivoActual['rutaAlmacenamiento'];
        $tipoFinal = $archivoActual['tipoMIME'];
        $tamanoFinal = $archivoActual['tamanoKB'];

        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['documento'];
            
            // Generar nuevo nombre único y mover el archivo
            $uniqueName = uniqid('doc_') . '-' . basename($file['name']);
            $destinationPath = $this->storagePath . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                // Si se sube un nuevo archivo, actualizamos todos los datos
                $rutaFinal = $destinationPath;
                $tipoFinal = $file['type'];
                $tamanoFinal = round($file['size'] / 1024, 2);
                $nuevoNombre = $file['name']; // El nuevo nombre es el del archivo subido
            } else {
                return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo mover el nuevo archivo.");
            }
        }
        
        // 4. Actualizar el registro principal en la tabla Archivo
        $idUsuario = (int)$tokenData->data->idUsuario;
        if ($documentoModel->updateArchivo($id, $nuevoNombre, $rutaFinal, $tipoFinal, $tamanoFinal, $idUsuario)) {
            return responseHTTP::response(responseHTTP::$HTTP_200_OK, "Documento actualizado exitosamente.");
        } else {
            return responseHTTP::responseError(responseHTTP::$HTTP_500_INTERNAL_SERVER_ERROR, "No se pudo actualizar el registro en la base de datos.");
        }
    }

}