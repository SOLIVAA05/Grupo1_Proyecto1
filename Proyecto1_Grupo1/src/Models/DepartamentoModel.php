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
            $query = "CALL crear_departamento(:nombre)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':nombre', $nombre);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::createDepartamento -> " . $e->getMessage());
            return false;
        }
    }

    // --- NUEVOS MÉTODOS AÑADIDOS ---
    public function updateDepartamento(int $id, string $nombre): bool
    {
        try {
            $query = "CALL actualizar_departamento(:id, :nombre)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':nombre', $nombre);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::updateDepartamento -> " . $e->getMessage());
            return false;
        }
    }

    public function deleteDepartamento(int $id): bool
    {
        try {
            $query = "CALL eliminar_departamento(:id)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("DepartamentoModel::deleteDepartamento -> " . $e->getMessage());
            return false;
        }
    }
}
