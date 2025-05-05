<?php
require_once __DIR__ . '/../config/database.php';

class MaquinaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Registra una nueva máquina recreativa.
     * @param string $nombre Nombre de la máquina.
     * @param string $tipo Tipo de máquina.
     * @param int $idEnsamblador ID del técnico ensamblador.
     * @param int $idComprobador ID del técnico comprobador.
     * @param int $idComercio ID del comercio destino.
     * @return int|false ID de la nueva máquina o false en error
     */
    public function registrarMaquina($nombre, $tipo, $idEnsamblador, $idComprobador, $idComercio) {
        $conn = $this->db->getConnection();
        // Escapar todos los parámetros.
        $nombre = $conn->real_escape_string($nombre);
        $tipo = $conn->real_escape_string($tipo);
        $idEnsamblador = $conn->real_escape_string($idEnsamblador);
        $idComprobador = $conn->real_escape_string($idComprobador);
        $idComercio = $conn->real_escape_string($idComercio);
        $fecha = date('Y-m-d');
        
        // Registrar nueva máquina con valores iniciales por defecto para etapa y estado:
        $sql = "INSERT INTO MaquinaRecreativa (
                    Nombre_Maquina, Tipo, Fecha_Registro, 
                    ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Comercio,
                    Etapa, Estado
                ) VALUES (
                    '$nombre', '$tipo', '$fecha',
                    $idEnsamblador, $idComprobador, $idComercio,
                    'Montaje', 'Ensamblandose'
                )";
        
        if ($conn->query($sql) === TRUE) {
            $idMaquina = $conn->insert_id;
            
            // Incrementar máquinas en comercio destino:
            $comercioModel = new ComercioModel();
            $comercioModel->incrementarMaquinasComercio($idComercio);
            
            return $idMaquina;
        } else {
            return false;
        }
    }

    /**
     * Actualiza el estado y opcionalmente la etapa de una máquina.
     * @param int $idMaquina ID de la máquina.
     * @param string $estado Nuevo estado.
     * @param string|null $etapa Nueva etapa (opcional).
     * @return bool Resultado de la operación.
     */
    public function actualizarEstadoMaquina($idMaquina, $estado, $etapa = null) {
        $conn = $this->db->getConnection();
        $idMaquina = $conn->real_escape_string($idMaquina);
        $estado = $conn->real_escape_string($estado);
        
        $sql = "UPDATE MaquinaRecreativa SET Estado = '$estado'";
        
        if ($etapa) {
            $etapa = $conn->real_escape_string($etapa);
            $sql .= ", Etapa = '$etapa'";
        }
        
        $sql .= " WHERE ID_Maquina = $idMaquina";
        
        return $conn->query($sql);
    }

    /**
     * Asigna un técnico de mantenimiento a una máquina.
     * @param int $idMaquina ID de la máquina.
     * @param int $idTecnico ID del técnico.
     * @return bool Resultado de la operación.
     */
    public function asignarTecnicoMantenimiento($idMaquina, $idTecnico) {
        $conn = $this->db->getConnection();
        $idMaquina = $conn->real_escape_string($idMaquina);
        $idTecnico = $conn->real_escape_string($idTecnico);
        
        $sql = "UPDATE MaquinaRecreativa 
                SET ID_Tecnico_Mantenimiento = $idTecnico 
                WHERE ID_Maquina = $idMaquina";
        
        if ($conn->query($sql)) {
            $usuarioModel = new UsuarioModel();
            return $usuarioModel->incrementarActividadesTecnico($idTecnico);
        }
        
        return false;
    }

    public function obtenerMaquinasPorTecnicoEnsamblador($idTecnico) {
        $conn = $this->db->getConnection();
        $idTecnico = $conn->real_escape_string($idTecnico);
        
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE (m.Estado = 'Ensamblandose' OR m.Estado = 'Reensamblandose')
                AND m.ID_Tecnico_Ensamblador = $idTecnico";
        
        $result = $conn->query($sql);
        $maquinas = [];
        
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }

    public function obtenerMaquinasPorTecnicoComprobador($idTecnico) {
        $conn = $this->db->getConnection();
        $idTecnico = $conn->real_escape_string($idTecnico);
        
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE m.Estado = 'Comprobandose'
                AND m.ID_Tecnico_Comprobador = $idTecnico";
        
        $result = $conn->query($sql);
        $maquinas = [];
        
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }

    /**
     * Obtiene máquinas asignadas a un técnico de mantenimiento.
     * @param int $idTecnico ID del técnico de mantenimiento.
     * @return array Lista de máquinas con información de comercio.
     */
    public function obtenerMaquinasPorTecnicoMantenimiento($idTecnico) {
        $conn = $this->db->getConnection();
        $idTecnico = $conn->real_escape_string($idTecnico);
        
        // Join con tabla Comercio para obtener nombre y dirección:
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE m.Estado = 'No operativa' 
                AND m.ID_Tecnico_Mantenimiento = $idTecnico";
        
        $result = $conn->query($sql);
        
        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }

    // Métodos similares para obtener por estado y etapa...

    public function obtenerMaquinasPorEstado($estado) {
        $conn = $this->db->getConnection();
        $estado = $conn->real_escape_string($estado);
        
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE m.Estado = '$estado'";
        
        $result = $conn->query($sql);
        $maquinas = [];
        
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }

    public function obtenerMaquinasPorEtapa($etapa) {
        $conn = $this->db->getConnection();
        $etapa = $conn->real_escape_string($etapa);
        
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE m.Etapa = '$etapa'";
        
        $result = $conn->query($sql);
        $maquinas = [];
        
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }

    public function obtenerMaquinaPorId($id) {
        $conn = $this->db->getConnection();
        $id = $conn->real_escape_string($id);
        
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m 
                JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio 
                WHERE m.ID_Maquina = $id";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }
}
?>