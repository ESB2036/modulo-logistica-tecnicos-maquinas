<?php
require_once __DIR__ . '/../services/NotificacionService.php';

class NotificacionController {
    private $service;

    public function __construct() {
        $this->service = new NotificacionService();
    }
    
    /**
     * Obtiene notificaciones por usuario.
     * @param int $idUsuario ID del usuario destinatario.
     */
    public function obtenerPorUsuario($idUsuario) {
        $response = $this->service->obtenerNotificaciones($idUsuario);
        $this->sendResponse($response);
    }

    /**
     * Crea una nueva notificación.
     * Valida todos los campos requeridos para una notificación.
     */
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validación exhaustiva de campos requeridos:
        if (!isset($data['idRemitente']) || !isset($data['idDestinatario']) || 
            !isset($data['idMaquina']) || !isset($data['tipo']) || !isset($data['mensaje'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $response = $this->service->crearNotificacion(
            $data['idRemitente'],
            $data['idDestinatario'],
            $data['idMaquina'],
            $data['tipo'],
            $data['mensaje']
        );
        $this->sendResponse($response);
    }

    public function marcarComoLeida() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idNotificacion'])) {
            $this->sendResponse(['success' => false, 'message' => 'ID de notificación requerido']);
            return;
        }
        
        $response = $this->service->marcarComoLeida($data['idNotificacion']);
        $this->sendResponse($response);
    }
    
    public function obtenerNoLeidas($idUsuario) {
        $response = $this->service->obtenerNoLeidas($idUsuario);
        $this->sendResponse($response);
    }

    private function sendResponse($response) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
?>