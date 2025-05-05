<?php
require_once __DIR__ . '/../services/UsuarioService.php';

class UsuarioController {
    private $service;

    public function __construct() {
        $this->service = new UsuarioService();
    }

    // Registro de nuevo usuario:
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $this->service->registrarUsuario($data);
        $this->sendResponse($response);
    }
    
    /**
     * Autenticación de usuario.
     * Valida presencia de usuario_asignado y contraseña.
     */
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['usuario_asignado']) || !isset($data['contrasena'])) {
            $this->sendResponse(['success' => false, 'message' => 'Usuario asignado y contraseña son requeridos']);
            return;
        }
        
        $response = $this->service->login($data['usuario_asignado'], $data['contrasena']);
        $this->sendResponse($response);
    }

    // Obtener perfil de usuario por ID:
    public function obtenerUsuarioPorId($id) {
        $response = $this->service->obtenerUsuarioPorId($id);
        $this->sendResponse($response);
    }

    /**
     * Obtiene técnicos filtrados por especialidad.
     * @param string $especialidad Tipo de técnico (Ensamblador, Comprobador o Mantenimiento).
     */
    public function obtenerTecnicos($especialidad) {
        $response = $this->service->obtenerTecnicosPorEspecialidad($especialidad);
        $this->sendResponse($response);
    }

    private function sendResponse($response) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
?>