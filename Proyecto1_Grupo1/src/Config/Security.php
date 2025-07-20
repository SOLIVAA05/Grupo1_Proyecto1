<?php
namespace App\Config;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Security
{
    private static $secret_key;
    private static $encrypt = ['HS256']; // Algoritmo de encriptación

    public function __construct()
    {
        // Carga la clave secreta desde el archivo .env
        self::$secret_key = $_ENV['SECRET_KEY'];
    }

    /**
     * Encripta una contraseña usando el algoritmo BCRYPT.
     *
     * @param string $password La contraseña en texto plano.
     * @return string La contraseña encriptada (hash).
     */
    public static function createPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Valida si una contraseña en texto plano coincide con su hash.
     *
     * @param string $password La contraseña en texto plano.
     * @param string $hash La contraseña encriptada desde la BD.
     * @return bool True si la contraseña es válida, false si no.
     */
    public static function validatePassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Crea un nuevo token JWT.
     *
     * @param array $data Los datos que se almacenarán dentro del token.
     * @return string El token JWT generado.
     */
    public static function createTokenJWT(array $data): string
    {
        $time = time();
        $payload = [
            'iat' => $time, // Tiempo en que se creó el token
            'exp' => $time + (60 * 60 * 24), // El token expira en 24 horas
            'data' => $data
        ];

        return JWT::encode($payload, self::$secret_key, self::$encrypt[0]);
    }

    /**
     * Valida un token JWT.
     *
     * @param string $token El token a validar.
     * @return bool True si el token es válido, false si no.
     */
    public static function validateTokenJWT(string $token): bool
    {
        try {
            self::getDataJwt($token);
            return true;
        } catch (\Exception $e) {
            error_log("Security::validateTokenJWT -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los datos almacenados dentro de un token JWT.
     *
     * @param string $token El token del cual extraer los datos.
     * @return object Los datos decodificados del token.
     */
    public static function getDataJwt(string $token)
    {
        return JWT::decode($token, new Key(self::$secret_key, self::$encrypt[0]));
    }
}

