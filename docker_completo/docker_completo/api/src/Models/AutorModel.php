<?php
namespace App\Models;

class AutorModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function destacado() {
        try {
            $stmt = $this->db->query(
                'SELECT id_autor, nombre FROM autores WHERE es_destacado = 1 ORDER BY id_autor LIMIT 1'
            );
            $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$fila) {
                return null;
            }
            return [
                'id' => (int) $fila['id_autor'],
                'nombre' => $fila['nombre'],
            ];
        } catch (\Exception $e) {
            return false;
        }
    }
}
