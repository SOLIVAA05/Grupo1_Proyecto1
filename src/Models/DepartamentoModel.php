<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class DepartamentoModel extends connectionDB
{
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

    public function createDepartamento(string $nombre): bool
    {
        try {
            // Usamos el procedimiento que ya existe en tu nueva BD
            $query = "CALL crear_departamento(:nombre)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':nombre', $nombre);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::createDepartamento -> " . $e->getMessage());
            return false;
        }
    }
}
