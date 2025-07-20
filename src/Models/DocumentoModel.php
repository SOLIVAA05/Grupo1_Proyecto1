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
}
