<?php
namespace App\DB;

class sql extends connectionDB
{
    /**
     * Valida si un registro existe en la base de datos.
     *
     * @param string $table Nombre de la tabla.
     * @param string $field Nombre del campo a verificar.
     * @param mixed $data El valor a buscar en el campo.
     * @return bool Devuelve true si el registro existe, false en caso contrario.
     */
    public function validateRecord(string $table, string $field, $data): bool
    {
        try {
            // Prepara la consulta SQL para contar los registros que coinciden
            $query = "SELECT COUNT(*) FROM {$table} WHERE {$field} = :data";
            $statement = $this->connection->prepare($query);
            $statement->bindParam(':data', $data);
            $statement->execute();

            // Si el conteo es mayor a 0, el registro existe
            return $statement->fetchColumn() > 0;
            
        } catch (\PDOException $e) {
            // En caso de error, lo registramos para futura revisión
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }
}
