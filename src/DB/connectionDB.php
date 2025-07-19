<?php
namespace App\DB;

use PDO;
use PDOException;
use Dotenv\Dotenv;

// Cargar las variables de entorno del archivo .env
// createImmutable necesita la ruta a la CARPETA que contiene el .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); 
$dotenv->load();

class connectionDB
{
    private string $ip;
    private string $db;
    private string $user;
    private string $password;
    private string $port;
    protected $connection;

    public function __construct()
    {
        $this->ip = $_ENV['IP'];
        $this->db = $_ENV['DB'];
        $this->user = $_ENV['USER'];
        $this->password = $_ENV['PASSWORD'];
        $this->port = $_ENV['PORT'];

        try {
            // Crear la conexión PDO
            $this->connection = new PDO(
                "mysql:host={$this->ip};port={$this->port};dbname={$this->db}",
                $this->user,
                $this->password
            );
            // Configurar PDO para que lance excepciones en caso de error
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Asegurar que la conexión use el conjunto de caracteres utf8mb4
            $this->connection->exec("SET NAMES 'utf8mb4'");

        } catch (PDOException $e) {
            // Si la conexión falla, se registrará en nuestro archivo de log
            error_log("Fallo en la conexión: " . $e->getMessage());
            die("Falló la conexión a la base de datos. Revise los logs para más detalles.");
        }
    }

    /**
     * Cierra la conexión a la base de datos
     */
    public function closeConnection()
    {
        $this->connection = null;
    }
}