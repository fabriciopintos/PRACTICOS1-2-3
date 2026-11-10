<?php
namespace App\Controllers;

use App\Models\ProductoModel;

class EstadoController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function ver() {
        $respuesta = [
            'status' => 'ok',
            'message' => 'La API de Practicos3MD responde',
            'base_datos' => 'conectada',
            'tabla_productos' => 'no existe todavía',
            'cantidad_productos' => 0,
        ];

        $cantidad = (new ProductoModel($this->db))->contar(true);
        if ($cantidad !== false) {
            $respuesta['tabla_productos'] = 'ok';
            $respuesta['cantidad_productos'] = $cantidad;
        } else {
            $respuesta['tabla_productos'] = 'Falta crear la tabla productos (init.sql)';
        }

        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
}
