<?php
namespace App\Controllers;

use App\Models\AutorModel;
use App\Models\ProductoModel;

class AutorController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function destacado() {
        $autor = (new AutorModel($this->db))->destacado();
        if ($autor === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo leer el autor destacado',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($autor === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'No hay un autor destacado',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $libros = (new ProductoModel($this->db))->delAutor($autor['id']);
        if ($libros === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudieron leer los libros del autor',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $autor['libros'] = array_values(array_filter($libros, function ($libro) {
            return $libro['en_autor'] === 1;
        }));

        echo json_encode([
            'status' => 'ok',
            'autor' => $autor,
        ], JSON_UNESCAPED_UNICODE);
    }
}
