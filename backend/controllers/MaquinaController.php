<?php
require_once __DIR__ . '/../services/MaquinaService.php';

class MaquinaController {
    private $service;

    public function __construct() {
        $this->service = new MaquinaService();
    }
    
    // Registro de nueva máquina:
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $this->service->registrarMaquina($data);
        $this->sendResponse($response);
    }

    /**
     * Transición de estado: Enviar a comprobación.
     * Valida que existan los campos requeridos antes de procesar.
     */
    public function mandarAComprobacion() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina']) || !isset($data['idRemitente']) || !isset($data['mensaje'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $response = $this->service->mandarAComprobacion($data['idMaquina'], $data['idRemitente'], $data['mensaje']);
        $this->sendResponse($response);
    }

    /**
     * Transición de estado: Enviar a reensamblar.
     * Valida que existan los campos requeridos antes de procesar.
     */
    public function mandarAReensamblar() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina']) || !isset($data['idRemitente']) || !isset($data['mensaje'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $response = $this->service->mandarAReensamblar($data['idMaquina'], $data['idRemitente'], $data['mensaje']);
        $this->sendResponse($response);
    }

    /**
     * Transición de etapa y estado: Enviar a distribución.
     * Valida que existan los campos requeridos antes de procesar.
     */
    public function mandarADistribucion() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina']) || !isset($data['idRemitente']) || !isset($data['mensaje'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        $response = $this->service->mandarADistribucion($data['idMaquina'], $data['idRemitente'], $data['mensaje']);
        $this->sendResponse($response);
    }

    // Marcar máquina como operativa:
    public function ponerOperativa() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina'])) {
            $this->sendResponse(['success' => false, 'message' => 'ID de maquina requerido']);
            return;
        }
        
        $response = $this->service->ponerOperativa($data['idMaquina']);
        $this->sendResponse($response);
    }

    public function obtenerPorTecnicoEnsamblador($idTecnico) {
        $response = $this->service->obtenerMaquinasPorTecnicoEnsamblador($idTecnico);
        $this->sendResponse($response);
    }
    
    public function obtenerPorTecnicoComprobador($idTecnico) {
        $response = $this->service->obtenerMaquinasPorTecnicoComprobador($idTecnico);
        $this->sendResponse($response);
    }

    // Obtener máquinas asignadas a un técnico de mantenimiento:
    public function obtenerPorTecnicoMantenimiento($idTecnico) {
        $response = $this->service->obtenerMaquinasPorTecnicoMantenimiento($idTecnico);
        $this->sendResponse($response);
    }

    // Iniciar proceso de mantenimiento:
    public function darMantenimiento() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina']) || !isset($data['mensaje']) || !isset($data['idLogistica'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $response = $this->service->darMantenimiento($data['idMaquina'], $data['mensaje'], $data['idLogistica']);
        $this->sendResponse($response);
    }

    // Finalizar proceso de mantenimiento:
    public function finalizarMantenimiento() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idMaquina']) || !isset($data['idRemitente']) || 
            !isset($data['exito']) || !isset($data['mensaje'])) {
            $this->sendResponse(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $response = $this->service->finalizarMantenimiento(
            $data['idMaquina'],
            $data['idRemitente'],
            $data['exito'],
            $data['mensaje']
        );
        $this->sendResponse($response);
    }

    // Filtros por estado:
    public function obtenerPorEstado($estado) {
        $response = $this->service->obtenerMaquinasPorEstado($estado);
        $this->sendResponse($response);
    }

    // Filtros por etapa:
    public function obtenerPorEtapa($etapa) {
        $response = $this->service->obtenerMaquinasPorEtapa($etapa);
        $this->sendResponse($response);
    }

    // Método común para enviar respuestas:
    private function sendResponse($response) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
?>