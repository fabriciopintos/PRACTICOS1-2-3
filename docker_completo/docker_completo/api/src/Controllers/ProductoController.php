<?php
namespace App\Controllers;

use App\Models\ProductoModel;

class ProductoController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new ProductoModel($db);
    }

    public function listar() {
        $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : null;
        $buscar = isset($_GET['q']) ? trim($_GET['q']) : null;
        $soloOferta = isset($_GET['oferta']) && $_GET['oferta'] === '1';
        $permitidos = ['libro', 'revista', 'audiolibro'];

        if ($tipo === '') {
            $tipo = null;
        }
        if ($tipo !== null && !in_array($tipo, $permitidos, true)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'El tipo debe ser libro, revista o audiolibro',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($buscar === '') {
            $buscar = null;
        } elseif ($buscar !== null && strlen($buscar) > 80) {
            $buscar = substr($buscar, 0, 80);
        }

        $productos = $this->modelo->listar($tipo, $buscar, $soloOferta);
        if ($productos === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo leer el catálogo',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status' => 'ok',
            'cantidad' => count($productos),
            'productos' => $productos,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function ver($id) {
        $producto = $this->modelo->obtener((int) $id);
        if ($producto === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo leer el producto',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($producto === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Producto no encontrado',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status' => 'ok',
            'producto' => $producto,
        ], JSON_UNESCAPED_UNICODE);
    }
}
