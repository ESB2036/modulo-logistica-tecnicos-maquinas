<?php
// Definición de constantes para la configuración de la base de datos.
// Estas constantes son accesibles desde cualquier parte de la aplicación.
define('DB_HOST', 'localhost'); // Servidor donde está alojada la BD.
define('DB_USER', 'root');      // Usuario de la base de datos.
define('DB_PASS', '');          // Contraseña del usuario.
define('DB_NAME', 'bd_recrea_sys'); // Nombre de la base de datos.

class Database {
    private $connection; // Propiedad privada para almacenar la conexión MySQLi.

    // Constructor: se ejecuta automáticamente al crear una instancia.
    public function __construct() {
        // Crear nueva conexión MySQLi con los parámetros definidos:
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Verificar si hubo error en la conexión:
        if ($this->connection->connect_error) {
            // Terminar ejecución y mostrar error (solo en desarrollo)
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Establecer el conjunto de caracteres a utf8mb4 para soportar caracteres especiales:
        $this->connection->set_charset("utf8mb4");
    }

    // Método para obtener la conexión (útil para casos especiales):
    public function getConnection() {
        return $this->connection;
    }

    // Método para cerrar la conexión explícitamente:
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Método para ejecutar consultas SQL:
    public function query($sql) {
        // Ejecutar la consulta
        $result = $this->connection->query($sql);
        
        // Si hay error, registrarlo en el log de errores
        if (!$result) {
            error_log("Error en consulta SQL: " . $this->connection->error);
            error_log("Consulta: " . $sql);
        }
        return $result;
    }

    // Método para escapar strings y prevenir inyección SQL:
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    // Método para obtener el ID del último registro insertado:
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
}
?>