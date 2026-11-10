<?php
namespace App\Controllers;

use App\Models\CategoriaModel;

class CategoriaController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new CategoriaModel($db);
    }

    public function listar() {
        $categorias = $this->modelo->listar();
        if ($categorias === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudieron leer las categorías',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status' => 'ok',
            'categorias' => $categorias,
        ], JSON_UNESCAPED_UNICODE);
    }
}
