<?php
namespace App\Models;

class CategoriaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listar() {
        try {
            $stmt = $this->db->query('SELECT id_categoria, nombre, slug FROM categorias ORDER BY nombre');
            $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(function ($fila) {
                return [
                    'id' => (int) $fila['id_categoria'],
                    'nombre' => $fila['nombre'],
                    'slug' => $fila['slug'],
                ];
            }, $filas);
        } catch (\Exception $e) {
            return false;
        }
    }
}
