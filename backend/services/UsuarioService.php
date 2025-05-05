<?php
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioService {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    /**
     * Registra un nuevo usuario con validación.
     * @param array $data Datos del usuario.
     * @return array Resultado de la operación.
     */
    public function registrarUsuario($data) {
        $required = ['ci', 'nombre', 'apellido', 'email', 'tipo', 'usuario_asignado', 'contrasena'];
        
        // Validación básica:
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['success' => false, 'message' => "El campo $field es requerido"];
            }
        }
        
        // Validación especial para técnicos:
        if ($data['tipo'] === 'Tecnico' && empty($data['especialidad'])) {
            return ['success' => false, 'message' => 'La especialidad es requerida para técnicos'];
        }
        
        // Registrar usuario:
        $idUsuario = $this->model->registrarUsuario(
            $data['ci'],
            $data['nombre'],
            $data['apellido'],
            $data['email'],
            $data['tipo'],
            $data['usuario_asignado'],
            $data['contrasena'],
            $data['especialidad'] ?? null
        );
        
        // Formatear respuesta:
        if ($idUsuario) {
            return ['success' => true, 'idUsuario' => $idUsuario];
        } else {
            return ['success' => false, 'message' => 'Error al registrar usuario'];
        }
    }

    public function login($usuario_asignado, $contrasena) {
        $usuarioData = $this->model->login($usuario_asignado, $contrasena);
        
        if ($usuarioData) {
            return ['success' => true, 'usuario' => $usuarioData];
        } else {
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }
    }

    public function obtenerUsuarioPorId($id) {
        $usuario = $this->model->obtenerUsuarioPorId($id);
        
        if ($usuario) {
            return ['success' => true, 'usuario' => $usuario];
        } else {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
    }

    public function obtenerTecnicosPorEspecialidad($especialidad) {
        $tecnicos = $this->model->obtenerTecnicosPorEspecialidad($especialidad);
        return ['success' => true, 'tecnicos' => $tecnicos];
    }
}
?>