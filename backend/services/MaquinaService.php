<?php
require_once __DIR__ . '/../models/MaquinaModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/NotificacionModel.php';

class MaquinaService {
    private $maquinaModel;
    private $usuarioModel;
    private $notificacionModel;

    /**
     * Registra una nueva máquina con asignación automática de técnicos.
     * @param array $data Datos de la máquina.
     * @return array Resultado de la operación.
     */
    public function __construct() {
        $this->maquinaModel = new MaquinaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->notificacionModel = new NotificacionModel();
    }

    public function registrarMaquina($data) {
        // Validación de campos:
        $required = ['nombre', 'tipo', 'idComercio', 'idUsuarioLogistica'];        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['success' => false, 'message' => "El campo $field es requerido"];
            }
        }
        
        // Obtener técnicos con menos actividades hechas para asignación balanceada de técnicos:
        $ensambladores = $this->usuarioModel->obtenerTecnicosPorEspecialidad('Ensamblador');
        $comprobadores = $this->usuarioModel->obtenerTecnicosPorEspecialidad('Comprobador');
        
        if (empty($ensambladores) || empty($comprobadores)) {
            return ['success' => false, 'message' => 'No hay tecnicos disponibles para asignar'];
        }
        
        $idEnsamblador = $ensambladores[0]['ID_Usuario']; // Técnico ensamblador con menos actividades.
        $idComprobador = $comprobadores[0]['ID_Usuario']; // Técnico comprobador con menos actividades.
        
        // Registrar máquina:
        $idMaquina = $this->maquinaModel->registrarMaquina(
            $data['nombre'],
            $data['tipo'],
            $idEnsamblador,
            $idComprobador,
            $data['idComercio']
        );
        
        if ($idMaquina) {
            // Crear notificación automática para el técnico ensamblador:
            $this->notificacionModel->crearNotificacion(
                $data['idUsuarioLogistica'],
                $idEnsamblador,
                $idMaquina,
                'Nuevo montaje',
                ''
            );

            // Incrementar actividades de técnicos asignados:
            $usuarioModel = new UsuarioModel();
            $usuarioModel->incrementarActividadesTecnico($idEnsamblador);
            $usuarioModel->incrementarActividadesTecnico($idComprobador);
            
            return ['success' => true, 'idMaquina' => $idMaquina];
        } else {
            return ['success' => false, 'message' => 'Error al registrar maquina'];
        }
    }

    public function mandarAComprobacion($idMaquina, $idRemitente, $mensaje) {
        $maquina = $this->maquinaModel->obtenerMaquinaPorId($idMaquina);
        
        if (!$maquina) {
            return ['success' => false, 'message' => 'Maquina no encontrada'];
        }
        
        // Actualizar estado:
        $this->maquinaModel->actualizarEstadoMaquina($idMaquina, 'Comprobandose');
        
        // Crear notificacion para el técnico comprobador:
        $this->notificacionModel->crearNotificacion(
            $idRemitente,
            $maquina['ID_Tecnico_Comprobador'],
            $idMaquina,
            'Comprobar maquina recreativa',
            $mensaje
        );
        
        return ['success' => true];
    }

    public function mandarAReensamblar($idMaquina, $idRemitente, $mensaje) {
        $maquina = $this->maquinaModel->obtenerMaquinaPorId($idMaquina);
        
        if (!$maquina) {
            return ['success' => false, 'message' => 'Maquina no encontrada'];
        }
        
        // Actualizar estado:
        $this->maquinaModel->actualizarEstadoMaquina($idMaquina, 'Reensamblandose');
        
        // Crear notificación para el técnico ensamblador:
        $this->notificacionModel->crearNotificacion(
            $idRemitente,
            $maquina['ID_Tecnico_Ensamblador'],
            $idMaquina,
            'Reensamblar maquina recreativa',
            $mensaje
        );
        
        return ['success' => true];
    }

    public function mandarADistribucion($idMaquina, $idRemitente, $mensaje) {
        $maquina = $this->maquinaModel->obtenerMaquinaPorId($idMaquina);
        
        if (!$maquina) {
            return ['success' => false, 'message' => 'Maquina no encontrada'];
        }
        
        // Actualizar estado y etapa:
        $this->maquinaModel->actualizarEstadoMaquina($idMaquina, 'Distribuyendose', 'Distribucion');
        
        // Obtener todos los usuarios de logística:
        $logisticas = $this->usuarioModel->obtenerUsuariosPorTipo('Logistica');
        
        if (!empty($logisticas)) {
            // Crear notificación para cada usuario de logística:
            foreach ($logisticas as $logistica) {
                $this->notificacionModel->crearNotificacion(
                    $idRemitente,
                    $logistica['ID_Usuario'],
                    $idMaquina,
                    'Distribuir maquina recreativa',
                    $mensaje
                );
            }
        }
        
        return ['success' => true];
    }

    public function ponerOperativa($idMaquina) {
        // Actualizar estado y etapa:
        $result = $this->maquinaModel->actualizarEstadoMaquina($idMaquina, 'Operativa', 'Recaudacion');
        
        if ($result) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar maquina'];
        }
    }

    public function obtenerMaquinasPorTecnicoEnsamblador($idTecnico) {
        $maquinas = $this->maquinaModel->obtenerMaquinasPorTecnicoEnsamblador($idTecnico);
        return ['success' => true, 'maquinas' => $maquinas];
    }
    
    public function obtenerMaquinasPorTecnicoComprobador($idTecnico) {
        $maquinas = $this->maquinaModel->obtenerMaquinasPorTecnicoComprobador($idTecnico);
        return ['success' => true, 'maquinas' => $maquinas];
    }

    public function obtenerMaquinasPorTecnicoMantenimiento($idTecnico) {
        $maquinas = $this->maquinaModel->obtenerMaquinasPorTecnicoMantenimiento($idTecnico);
        return ['success' => true, 'maquinas' => $maquinas];
    }
    
    public function darMantenimiento($idMaquina, $mensaje, $idLogistica) {
        // Obtener técnico con menos actividades:
        $tecnicos = $this->usuarioModel->obtenerTecnicosPorEspecialidad('Mantenimiento');
        
        if (empty($tecnicos)) {
            return ['success' => false, 'message' => 'No hay técnicos de mantenimiento disponibles'];
        }
        
        $idTecnico = $tecnicos[0]['ID_Usuario'];
        
        // Actualizar máquina:
        $this->maquinaModel->actualizarEstadoMaquina($idMaquina, 'No operativa');
        $this->maquinaModel->asignarTecnicoMantenimiento($idMaquina, $idTecnico);
        
        // Crear notificación:
        $this->notificacionModel->crearNotificacion(
            $idLogistica,
            $idTecnico,
            $idMaquina,
            'Dar mantenimiento a máquina recreativa',
            $mensaje
        );
        
        return ['success' => true];
    }

    public function finalizarMantenimiento($idMaquina, $idRemitente, $exito, $mensaje) {
        $maquina = $this->maquinaModel->obtenerMaquinaPorId($idMaquina);
        
        if (!$maquina) {
            return ['success' => false, 'message' => 'Maquina no encontrada'];
        }
        
        // Determinar nuevo estado:
        $nuevoEstado = $exito ? 'Operativa' : 'Retirada';
        
        // Actualizar estado:
        $this->maquinaModel->actualizarEstadoMaquina($idMaquina, $nuevoEstado);
        
        // Obtener todos los usuarios de logística:
        $logisticas = $this->usuarioModel->obtenerUsuariosPorTipo('Logistica');
        
        if (!empty($logisticas)) {
            $tipoNotificacion = $exito ? 'Maquina recreativa reparada' : 'Maquina recreativa retirada';
            
            // Crear notificación para cada usuario de logística:
            foreach ($logisticas as $logistica) {
                $this->notificacionModel->crearNotificacion(
                    $idRemitente,
                    $logistica['ID_Usuario'],
                    $idMaquina,
                    $tipoNotificacion,
                    $mensaje
                );
            }
        }
        
        return ['success' => true];
    }

    public function obtenerMaquinasPorEstado($estado) {
        $maquinas = $this->maquinaModel->obtenerMaquinasPorEstado($estado);
        return ['success' => true, 'maquinas' => $maquinas];
    }

    public function obtenerMaquinasPorEtapa($etapa) {
        $maquinas = $this->maquinaModel->obtenerMaquinasPorEtapa($etapa);
        return ['success' => true, 'maquinas' => $maquinas];
    }
}
?>