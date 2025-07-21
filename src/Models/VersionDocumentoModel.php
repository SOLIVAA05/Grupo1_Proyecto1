<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class VersionDocumentoModel extends connectionDB
{
    /**
     * Crea un nuevo registro de versión en la base de datos.
     * @return bool
     */
    public function createVersion(int $idArchivoOriginal, int $idUsuario, string $rutaVersion, string $comentario): bool
    {
        try {
            // Usamos una consulta directa ya que no hay un procedimiento almacenado para esto.
            $query = "INSERT INTO VersionArchivo (idArchivoOriginal, idUsuario, rutaVersion, comentarioVersion) 
                      VALUES (:idArchivo, :idUsuario, :ruta, :comentario)";
            
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':idArchivo', $idArchivoOriginal, PDO::PARAM_INT);
            $statement->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $statement->bindParam(':ruta', $rutaVersion);
            $statement->bindParam(':comentario', $comentario);
            
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("VersionDocumentoModel::createVersion -> " . $e->getMessage());
            return false;
        }
    }
}
