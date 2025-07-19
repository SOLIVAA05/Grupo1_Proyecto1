<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class DocumentoModel extends connectionDB
{
    /**
     * Inserta el registro de un nuevo archivo en la base de datos.
     */
    public function createArchivo(string $nombre, string $ruta, string $tipo, float $tamano, int $idCarpeta, int $idUsuario)
    {
        try {
            $query = "INSERT INTO Archivo (nombre, rutaAlmacenamiento, tipoMIME, tamanoKB, idCarpeta, idUsuarioSube) VALUES (:nombre, :ruta, :tipo, :tamano, :idCarpeta, :idUsuario)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':nombre', $nombre);
            $statement->bindParam(':ruta', $ruta);
            $statement->bindParam(':tipo', $tipo);
            $statement->bindParam(':tamano', $tamano);
            $statement->bindParam(':idCarpeta', $idCarpeta, PDO::PARAM_INT);
            $statement->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DocumentoModel::createArchivo -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los archivos.
     */
    public function getAllArchivos()
    {
        try {
            $query = "SELECT * FROM Archivo WHERE estado = 1";
            $statement = $this->connection->query($query);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("DocumentoModel::getAllArchivos -> " . $e->getMessage());
            return false;
        }
    }
}
