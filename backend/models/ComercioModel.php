<?php
require_once __DIR__ . '/../config/database.php';

class ComercioModel {
    private $db; // Instancia de la conexión a la base de datos.

    public function __construct() {
        $this->db = new Database(); // Inicializa la conexión al crear el modelo.
    }

    /**
     * Registra un nuevo comercio en la base de datos
     * @param string $nombre Nombre del comercio.
     * @param string $tipo Tipo (Minorista/Mayorista).
     * @param string $direccion Dirección del comercio.
     * @param string $telefono Número de teléfono.
     * @return int|false ID del nuevo comercio o false en caso de error.
     */
    public function registrarComercio($nombre, $tipo, $direccion, $telefono) {
        $conn = $this->db->getConnection();
        // Escapar valores para prevenir inyección SQL.
        $nombre = $conn->real_escape_string($nombre);
        $tipo = $conn->real_escape_string($tipo);
        $direccion = $conn->real_escape_string($direccion);
        $telefono = $conn->real_escape_string($telefono);
        $fecha = date('Y-m-d'); // Fecha actual automática.
        
        $sql = "INSERT INTO Comercio (Nombre, Tipo, Direccion, Telefono, Fecha_Registro) 
                VALUES ('$nombre', '$tipo', '$direccion', '$telefono', '$fecha')";
        
        if ($conn->query($sql) === TRUE) {
            return $conn->insert_id; // Devuelve el ID generado.
        } else {
            return false; // Error en la inserción.
        }
    }

    /**
     * Obtiene todos los comercios registrados.
     * @return array Lista de comercios.
     */
    public function obtenerComercios() {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Comercio";
        $result = $conn->query($sql);
        $comercios = [];
        
        // Recorrer resultados y guardar en array:
        while ($row = $result->fetch_assoc()) {
            $comercios[] = $row;
        }
        
        return $comercios;
    }
    
    /**
     * Obtiene un comercio específico por su ID.
     * @param int $id ID del comercio.
     * @return array|false Datos del comercio o false si no existe.
     */
    public function obtenerComercioPorId($id) {
        $conn = $this->db->getConnection();
        $id = $conn->real_escape_string($id);
        
        $sql = "SELECT * FROM Comercio WHERE ID_Comercio = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Devuelve la primera fila.
        } else {
            return false; // No encontrado.
        }
    }

    /**
     * Incrementa el contador de máquinas de un comercio.
     * @param int $idComercio ID del comercio.
     * @return bool Resultado de la operación.
     */
    public function incrementarMaquinasComercio($idComercio) {
        $conn = $this->db->getConnection();
        $idComercio = $conn->real_escape_string($idComercio);
        
        $sql = "UPDATE Comercio SET Cantidad_Maquinas = Cantidad_Maquinas + 1 WHERE ID_Comercio = $idComercio";
        return $conn->query($sql); // Devuelve true/false.
    }
}
?>