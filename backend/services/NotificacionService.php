<?php
require_once __DIR__ . '/../models/NotificacionModel.php';

class NotificacionService {
    private $model;

    public function __construct() {
        $this->model = new NotificacionModel();
    }

    /**
     * Obtiene notificaciones para un usuario.
     * @param int $idUsuario ID del usuario.
     * @return array Lista de notificaciones.
     */
    public function obtenerNotificaciones($idUsuario) {
        $notificaciones = $this->model->obtenerNotificacionesPorDestinatario($idUsuario);
        return ['success' => true, 'notificaciones' => $notificaciones];
    }

    /**
     * Crea una nueva notificación.
     * @param int $idRemitente ID del remitente.
     * @param int $idDestinatario ID del destinatario.
     * @param int $idMaquina ID de la máquina relacionada.
     * @param string $tipo Tipo de notificación.
     * @param string $mensaje Contenido de la notificación.
     * @return array Resultado de la operación.
     */
    public function crearNotificacion($idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje) {
        $result = $this->model->crearNotificacion($idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje);
        
        if ($result) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al crear notificacion'];
        }
    }

    public function marcarComoLeida($idNotificacion) {
        $result = $this->model->marcarComoLeida($idNotificacion);
        return $result 
            ? ['success' => true] 
            : ['success' => false, 'message' => 'Error al actualizar notificación'];
    }
    
    public function obtenerNoLeidas($idUsuario) {
        $total = $this->model->obtenerNoLeidas($idUsuario);
        return ['success' => true, 'total' => $total];
    }
}
?>