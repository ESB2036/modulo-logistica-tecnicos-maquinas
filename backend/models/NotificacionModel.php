<?php
require_once __DIR__ . '/../config/database.php';

class NotificacionModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Crea una nueva notificación en el sistema.
     * @param int $idRemitente ID del usuario remitente.
     * @param int $idDestinatario ID del usuario destinatario.
     * @param int $idMaquina ID de la máquina relacionada.
     * @param string $tipo Tipo de notificación.
     * @param string $mensaje Contenido de la notificación.
     * @return bool Resultado de la operación.
     */
    public function crearNotificacion($idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje) {
        $conn = $this->db->getConnection();
        // Escapar todos los parámetros:
        $idRemitente = $conn->real_escape_string($idRemitente);
        $idDestinatario = $conn->real_escape_string($idDestinatario);
        $idMaquina = $conn->real_escape_string($idMaquina);
        $tipo = $conn->real_escape_string($tipo);
        $mensaje = $conn->real_escape_string($mensaje);
        
        $sql = "INSERT INTO NotificacionMaquinaRecreativa (ID_Remitente, ID_Destinatario, ID_Maquina, Tipo, Mensaje) 
                VALUES ($idRemitente, $idDestinatario, $idMaquina, '$tipo', '$mensaje')";
        
        return $conn->query($sql);
    }

    /**
     * Obtiene notificaciones para un usuario específico.
     * @param int $idDestinatario ID del usuario destinatario.
     * @return array Lista de notificaciones con datos extendidos.
     */
    public function obtenerNotificacionesPorDestinatario($idDestinatario) {
        $conn = $this->db->getConnection();
        $idDestinatario = $conn->real_escape_string($idDestinatario);
        
        // Join con tablas Usuario y MaquinaRecreativa para datos adicionales:
        $sql = "SELECT n.*, u.nombre as NombreRemitente, m.Nombre_Maquina, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
            FROM NotificacionMaquinaRecreativa n 
            JOIN usuario u ON n.ID_Remitente = u.ID_Usuario 
            JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
            JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE n.ID_Destinatario = $idDestinatario 
            ORDER BY n.Fecha DESC"; // Ordenadas por fecha descendente.
        
        $result = $conn->query($sql);
        $notificaciones = [];
        
        while ($row = $result->fetch_assoc()) {
            $notificaciones[] = $row;
        }
        
        return $notificaciones;
    }

    public function marcarComoLeida($idNotificacion) {
        $conn = $this->db->getConnection();
        $idNotificacion = $conn->real_escape_string($idNotificacion);
        
        $sql = "UPDATE NotificacionMaquinaRecreativa SET Estado = 'Leido' WHERE ID_Notificacion = $idNotificacion";
        return $conn->query($sql);
    }
    
    public function obtenerNoLeidas($idUsuario) {
        $conn = $this->db->getConnection();
        $idUsuario = $conn->real_escape_string($idUsuario);
        
        $sql = "SELECT COUNT(*) as total FROM NotificacionMaquinaRecreativa 
                WHERE ID_Destinatario = $idUsuario AND Estado = 'No leido'";
        
        $result = $conn->query($sql);
        return $result->fetch_assoc()['total'];
    }
}
?>