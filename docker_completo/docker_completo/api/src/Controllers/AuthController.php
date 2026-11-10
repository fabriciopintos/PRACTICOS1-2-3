<?php
namespace App\Controllers;

use App\Models\UsuarioModel;

class AuthController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new UsuarioModel($db);
    }

    public function login() {
        $cuerpo = json_decode(file_get_contents('php://input'), true);
        if (!is_array($cuerpo)) {
            $cuerpo = [];
        }

        $email = isset($cuerpo['email']) ? trim($cuerpo['email']) : '';
        $password = isset($cuerpo['password']) ? (string) $cuerpo['password'] : '';

        if ($email === '' || $password === '') {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Ingresá el correo y la contraseña',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = $this->modelo->buscarPorEmail($email);
        if ($usuario === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo verificar la cuenta',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $claveOk = $usuario && (int) $usuario['activo'] === 1
            && password_verify($password, $usuario['password_hash']);

        if (!$claveOk) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Correo o contraseña incorrectos',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_SESSION['usuario'] = $this->modelo->mapearSesion($usuario);

        echo json_encode([
            'status' => 'ok',
            'usuario' => $_SESSION['usuario'],
            'destino' => $usuario['rol'] === 'administrador'
                ? '/front/vistas/admin/dashboard.php'
                : '/front/vistas/usuario/inicio.php',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
    }

    public function sesion() {
        if (empty($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Sin sesión',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Refrescar datos de perfil desde la BD
        $fresco = $this->modelo->obtener((int) $_SESSION['usuario']['id']);
        if (is_array($fresco)) {
            $_SESSION['usuario'] = $this->modelo->mapearSesion($fresco);
        }

        echo json_encode([
            'status' => 'ok',
            'usuario' => $_SESSION['usuario'],
        ], JSON_UNESCAPED_UNICODE);
    }
}
