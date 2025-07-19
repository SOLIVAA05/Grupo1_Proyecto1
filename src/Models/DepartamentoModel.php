<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class DepartamentoModel extends connectionDB
{
    /**
     * Obtiene todos los departamentos activos.
     * @return array|false
     */
    public function getAllDepartamentos()
    {
        try {
            $query = "SELECT idDepartamento, nombre FROM Departamento WHERE estado = 1";
            $statement = $this->connection->prepare($query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::getAllDepartamentos -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo departamento.
     * @param string $nombre
     * @return bool
     */
    public function createDepartamento(string $nombre): bool
    {
        try {
            $query = "INSERT INTO Departamento (nombre) VALUES (:nombre)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':nombre', $nombre);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::createDepartamento -> " . $e->getMessage());
            return false;
        }
    }
}
