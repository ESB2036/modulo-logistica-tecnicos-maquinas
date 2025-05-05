<?php
require_once __DIR__ . '/../config/database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Registra un nuevo usuario en el sistema.
     * @param string $ci Cédula de identidad.
     * @param string $nombre Nombre del usuario.
     * @param string $apellido Apellido del usuario.
     * @param string $email Correo electrónico.
     * @param string $tipo Tipo de usuario.
     * @param string $usuario_asignado Nombre de usuario dentro del sistema.
     * @param string $contrasena Contraseña (debería estar hasheada en producción real).
     * @param string|null $especialidad Solo para técnicos.
     * @return int|false ID del nuevo usuario o false en error.
     */
    public function registrarUsuario($ci, $nombre, $apellido, $email, $tipo, $usuario_asignado, $contrasena, $especialidad = null) {
        $conn = $this->db->getConnection();

        // Escapar todos los parámetros:
        $ci = $conn->real_escape_string($ci);
        $nombre = $conn->real_escape_string($nombre);
        $apellido = $conn->real_escape_string($apellido);
        $email = $conn->real_escape_string($email);
        $tipo = $conn->real_escape_string($tipo);
        $usuario_asignado = $conn->real_escape_string($usuario_asignado);
        $contrasena = $conn->real_escape_string($contrasena);
        
        $sql = "INSERT INTO usuario (ci, nombre, apellido, email, tipo, usuario_asignado, contrasena) 
                VALUES ('$ci', '$nombre', '$apellido', '$email', '$tipo', '$usuario_asignado', '$contrasena')";
        
        if ($conn->query($sql) === TRUE) {
            $idUsuario = $conn->insert_id;
            
            // Registrar en tabla específica según tipo de usuario:
            if ($tipo === 'Tecnico' && $especialidad) {
                $especialidad = $conn->real_escape_string($especialidad);
                $sqlTecnico = "INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES ($idUsuario, '$especialidad')";
                $conn->query($sqlTecnico);
            } elseif ($tipo === 'Logistica') {
                $sqlLogistica = "INSERT INTO Logistica (ID_Logistica) VALUES ($idUsuario)";
                $conn->query($sqlLogistica);
            }
            
            return $idUsuario;
        } else {
            return false;
        }
    }

    /**
     * Autentica un usuario.
     * @param string $usuario_asignado Nombre de usuario dentro del sistema.
     * @param string $contrasena Contraseña (debería comparar hash, pero no están hasheadas).
     * @return array|false Datos del usuario o false si no coincide.
     */
    public function login($usuario_asignado, $contrasena) {
        $conn = $this->db->getConnection();
        $usuario_asignado = $conn->real_escape_string($usuario_asignado);
        $contrasena = $conn->real_escape_string($contrasena);
        
        // Join con tabla Tecnico para obtener especialidad si es técnico.
        $sql = "SELECT u.*, t.Especialidad 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.usuario_asignado = '$usuario_asignado' AND u.Contrasena = '$contrasena'";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function obtenerUsuarioPorId($id) {
        $conn = $this->db->getConnection();
        $id = $conn->real_escape_string($id);
        
        $sql = "SELECT u.*, t.Especialidad 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.ID_Usuario = $id";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function obtenerTecnicosPorEspecialidad($especialidad) {
        $conn = $this->db->getConnection();
        $especialidad = $conn->real_escape_string($especialidad);
        
        $sql = "SELECT u.ID_Usuario, u.nombre, u.apellido, t.Cantidad_Actividades 
                FROM usuario u 
                JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE t.Especialidad = '$especialidad' 
                ORDER BY t.Cantidad_Actividades ASC";
        
        $result = $conn->query($sql);
        $tecnicos = [];
        
        while ($row = $result->fetch_assoc()) {
            $tecnicos[] = $row;
        }
        
        return $tecnicos;
    }

    public function incrementarActividadesTecnico($idTecnico) {
        $conn = $this->db->getConnection();
        $idTecnico = $conn->real_escape_string($idTecnico);
        
        $sql = "UPDATE Tecnico SET Cantidad_Actividades = Cantidad_Actividades + 1 WHERE ID_Tecnico = $idTecnico";
        return $conn->query($sql);
    }

    public function obtenerUsuariosPorTipo($tipo) {
        $conn = $this->db->getConnection();
        $tipo = $conn->real_escape_string($tipo);
        
        $sql = "SELECT * FROM usuario WHERE tipo = '$tipo'";
        $result = $conn->query($sql);
        $usuarios = [];
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        
        return $usuarios;
    }
}
?>