<?php
namespace App\Models;

use App\DB\connectionDB;
use PDO;

class UserModel extends connectionDB
{
    // ... (los métodos getUserByUsername, getAllUsers, y createUser se quedan como están) ...

    public function getUserByUsername(string $username)
    {
        try {
            $query = "CALL obtener_usuario_por_username(:username)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':username', $username);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            return $user;
        } catch (\PDOException $e) {
            error_log("UserModel::getUserByUsername -> " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers()
    {
        try {
            $query = "CALL obtener_usuarios()";
            $statement = $this->connection->query($query);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("UserModel::getAllUsers -> " . $e->getMessage());
            return false;
        }
    }

    public function createUser(string $nombre, string $userName, string $password, int $idRol, int $idDepartamento): bool
    {
        try {
            $query = "CALL crear_usuario(:nombre, :user_name, :password, :idRol, :idDepartamento)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':nombre', $nombre);
            $statement->bindParam(':user_name', $userName);
            $statement->bindParam(':password', $password);
            $statement->bindParam(':idRol', $idRol, PDO::PARAM_INT);
            $statement->bindParam(':idDepartamento', $idDepartamento, PDO::PARAM_INT);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("UserModel::createUser -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un usuario existente.
     * @return bool
     */
    public function updateUser(int $id, string $nombre, string $userName, int $idRol, int $idDepartamento): bool
    {
        try {
            $query = "CALL actualizar_usuario(:id, :nombre, :user_name, :idRol, :idDepartamento)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':nombre', $nombre);
            $statement->bindParam(':user_name', $userName);
            $statement->bindParam(':idRol', $idRol, PDO::PARAM_INT);
            $statement->bindParam(':idDepartamento', $idDepartamento, PDO::PARAM_INT);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("UserModel::updateUser -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desactiva (elimina lógicamente) un usuario.
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        try {
            $query = "CALL desactivar_usuario(:id)";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            return $statement->execute();
        } catch (\PDOException $e) {
            error_log("UserModel::deleteUser -> " . $e->getMessage());
            return false;
        }
    }
}