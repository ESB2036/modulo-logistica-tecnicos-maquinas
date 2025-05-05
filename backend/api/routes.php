<?php
/**
 * API propia (es decir, una API REST personalizada).
 * Es el enrutador principal que maneja todas las
 * solicitudes HTTP entrantes y las dirige a los
 * controladores adecuados.
 */

// =============================================
// CONFIGURACIÓN CORS (Cross-Origin Resource Sharing)
// =============================================
// Permite solicitudes desde cualquier origen (*) - (en producción real debería restringirse)
header("Access-Control-Allow-Origin: *");
// Métodos HTTP permitidos:
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
// Cabeceras permitidas en las solicitudes:
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Indicar que todas las respuestas serán en formato JSON:
header("Content-Type: application/json");

// =============================================
// INCLUIR CONTROLADORES
// =============================================
// Cargar los archivos de los controladores que manejarán las solicitudes:
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../controllers/ComercioController.php';
require_once __DIR__ . '/../controllers/MaquinaController.php';
require_once __DIR__ . '/../controllers/NotificacionController.php';

// =============================================
// FUNCIÓN PRINCIPAL DE ENRUTAMIENTO
// =============================================
/**
 * Función que enruta las solicitudes a los controladores adecuados:
 * 
 * @param string $apiRoute La ruta solicitada (ej. '/api/usuario/login')
 * @param string $requestMethod El método HTTP usado (GET, POST, etc.)
 */
function routeRequest($apiRoute, $requestMethod) {
    // Usar switch para manejar diferentes rutas:
    switch ($apiRoute) {
        // --------------------------------
        // ENDPOINTS DE USUARIO
        // --------------------------------
        case '/api/usuario/register':
            // Solo aceptar método POST para registro:
            if ($requestMethod === 'POST') {
                // Verificar si los datos vienen como JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Si no es JSON válido, usar datos POST tradicionales
                    $input = $_POST;
                }
                
                // Crear instancia del controlador y llamar al método register
                $controller = new UsuarioController();
                $controller->register();
            }
            break;

        case '/api/usuario/login':
            if ($requestMethod === 'POST') {
                $controller = new UsuarioController();
                $controller->login();
            } else {
                // Método no permitido (405):
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // Ruta dinámica para perfiles de usuario (ej. /api/usuario/profile/123)
        case (preg_match('/\/api\/usuario\/profile\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                // Pasar el ID capturado en la URL al método:
                $controller->obtenerUsuarioPorId($matches[1]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // Ruta dinámica para obtener técnicos por especialidad:
        case (preg_match('/\/api\/usuario\/tecnicos\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new UsuarioController();
                $controller->obtenerTecnicos($matches[1]); // Pasar especialidad.
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE COMERCIO
        // --------------------------------
        case '/api/comercio/register':
            if ($requestMethod === 'POST') {
                $controller = new ComercioController();
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        case '/api/comercio/all':
            if ($requestMethod === 'GET') {
                $controller = new ComercioController();
                $controller->obtenerComercios(); // Obtener todos los comercios
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE MÁQUINAS RECREATIVAS
        // --------------------------------
        case '/api/maquina/register':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;

        // Endpoints para el flujo de trabajo de las máquinas:
        case '/api/maquina/mandar-comprobacion':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarAComprobacion();
            }
            break;

        case '/api/maquina/mandar-reensamblar':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarAReensamblar();
            }
            break;

        case '/api/maquina/mandar-distribucion':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->mandarADistribucion();
            }
            break;

        case '/api/maquina/poner-operativa':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->ponerOperativa();
            }
            break;

        // Obtener máquinas por técnico de ensamblaje/reensamblaje:
        case (preg_match('/\/api\/maquina\/ensamblador\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoEnsamblador($matches[1]);
            }
            break;

        // Obtener máquinas por técnico de comprobación:
        case (preg_match('/\/api\/maquina\/comprobador\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoComprobador($matches[1]);
            }
            break;

        // Obtener máquinas por técnico de mantenimiento:
        case (preg_match('/\/api\/maquina\/mantenimiento\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorTecnicoMantenimiento($matches[1]);
            }
            break;

        // Endpoints para mantenimiento:
        case '/api/maquina/dar-mantenimiento':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->darMantenimiento();
            }
            break;

        case '/api/maquina/finalizar-mantenimiento':
            if ($requestMethod === 'POST') {
                $controller = new MaquinaController();
                $controller->finalizarMantenimiento();
            }
            break;

        // Obtener máquinas por estado:
        case (preg_match('/\/api\/maquina\/estado\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorEstado($matches[1]);
            }
            break;
        
        // Obtener máquinas por etapa:
        case (preg_match('/\/api\/maquina\/etapa\/(\w+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new MaquinaController();
                $controller->obtenerPorEtapa($matches[1]);
            }
            break;

        // --------------------------------
        // ENDPOINTS DE NOTIFICACIONES
        // --------------------------------
        case (preg_match('/\/api\/notificaciones\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new NotificacionController();
                $controller->obtenerPorUsuario($matches[1]); // Obtener notificaciones por usuario.
            }
            break;

        case '/api/notificaciones/create':
            if ($requestMethod === 'POST') {
                $controller = new NotificacionController();
                $controller->create();
            }
            break;

        // Añadir estas rutas:
        case '/api/notificaciones/marcar-leida':
            if ($requestMethod === 'POST') {
                $controller = new NotificacionController();
                $controller->marcarComoLeida();
            }
            break;

        case (preg_match('/\/api\/notificaciones\/no-leidas\/(\d+)/', $apiRoute, $matches) ? true : false):
            if ($requestMethod === 'GET') {
                $controller = new NotificacionController();
                $controller->obtenerNoLeidas($matches[1]);
            }
            break;

        // --------------------------------
        // ENDPOINT POR DEFECTO (404)
        // --------------------------------
        default:
            // Si no coincide con ninguna ruta, devolver 404:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado']);
            break;
    }
}
?>