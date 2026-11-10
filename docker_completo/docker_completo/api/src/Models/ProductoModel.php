<?php
namespace App\Models;

class ProductoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function consultaBase() {
        return 'SELECT p.id_producto, p.tipo, p.titulo, p.precio, p.precio_oferta, p.anio,
                       p.descripcion, p.estilo_portada, p.en_autor, p.en_aventura, p.imagen,
                       p.disponible, p.id_autor, p.id_categoria,
                       COALESCE(ed.nombre, p.editorial) AS editorial,
                       a.nombre AS autor, c.nombre AS categoria, c.slug AS categoria_slug
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                LEFT JOIN autores a ON a.id_autor = p.id_autor
                LEFT JOIN editoriales ed ON ed.id_editorial = p.id_editorial';
    }

    public function listar($tipo = null, $buscar = null, $soloOferta = false, $soloDisponibles = true) {
        try {
            $sql = $this->consultaBase() . ' WHERE 1 = 1';
            $params = [];

            if ($soloDisponibles) {
                $sql .= ' AND p.disponible = 1';
            }

            if ($tipo !== null) {
                $sql .= ' AND p.tipo = :tipo';
                $params[':tipo'] = $tipo;
            }

            if ($soloOferta) {
                $sql .= ' AND p.precio_oferta IS NOT NULL';
            }

            if ($buscar !== null) {
                $sql .= ' AND (p.titulo LIKE :q1 OR a.nombre LIKE :q2 OR c.nombre LIKE :q3 OR p.editorial LIKE :q4)';
                $like = '%' . $buscar . '%';
                $params[':q1'] = $like;
                $params[':q2'] = $like;
                $params[':q3'] = $like;
                $params[':q4'] = $like;
            }

            $sql .= ' ORDER BY p.id_producto';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map([$this, 'mapear'], $filas);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->consultaBase() . ' WHERE p.id_producto = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$fila) {
                return null;
            }
            return $this->mapear($fila);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delAutor($idAutor) {
        try {
            $sql = $this->consultaBase() . ' WHERE p.id_autor = :id ORDER BY p.id_producto';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idAutor]);
            return array_map([$this, 'mapear'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function contar($soloDisponibles = false) {
        try {
            $sql = 'SELECT COUNT(*) FROM productos';
            if ($soloDisponibles) {
                $sql .= ' WHERE disponible = 1';
            }
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function asegurarAutor($nombre) {
        $nombre = trim($nombre);
        $busca = $this->db->prepare('SELECT id_autor FROM autores WHERE nombre = :nombre LIMIT 1');
        $busca->execute([':nombre' => $nombre]);
        $existente = $busca->fetchColumn();
        if ($existente) {
            return (int) $existente;
        }
        $alta = $this->db->prepare('INSERT INTO autores (nombre, es_destacado) VALUES (:nombre, 0)');
        $alta->execute([':nombre' => $nombre]);
        return (int) $this->db->lastInsertId();
    }

    public function guardar($datos, $id = null) {
        $params = [
            ':tipo' => $datos['tipo'],
            ':titulo' => $datos['titulo'],
            ':id_autor' => $datos['id_autor'],
            ':id_categoria' => $datos['id_categoria'],
            ':precio' => $datos['precio'],
            ':precio_oferta' => $datos['precio_oferta'],
            ':anio' => $datos['anio'],
            ':descripcion' => $datos['descripcion'],
            ':en_autor' => $datos['en_autor'],
            ':en_aventura' => $datos['en_aventura'],
            ':editorial' => $datos['editorial'],
            ':imagen' => $datos['imagen'],
            ':disponible' => $datos['disponible'],
        ];

        if ($id === null) {
            $sql = 'INSERT INTO productos
                    (tipo, titulo, id_autor, id_categoria, precio, precio_oferta, anio, descripcion,
                     estilo_portada, en_autor, en_aventura, editorial, imagen, disponible)
                    VALUES
                    (:tipo, :titulo, :id_autor, :id_categoria, :precio, :precio_oferta, :anio, :descripcion,
                     :estilo, :en_autor, :en_aventura, :editorial, :imagen, :disponible)';
            $params[':estilo'] = 'propia';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $this->db->lastInsertId();
        }

        $sql = 'UPDATE productos SET
                    tipo = :tipo,
                    titulo = :titulo,
                    id_autor = :id_autor,
                    id_categoria = :id_categoria,
                    precio = :precio,
                    precio_oferta = :precio_oferta,
                    anio = :anio,
                    descripcion = :descripcion,
                    en_autor = :en_autor,
                    en_aventura = :en_aventura,
                    editorial = :editorial,
                    imagen = :imagen,
                    disponible = :disponible
                WHERE id_producto = :id';
        $params[':id'] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $id;
    }

    public function eliminar($id) {
        $stmt = $this->db->prepare('DELETE FROM productos WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapear($fila) {
        return [
            'id' => (int) $fila['id_producto'],
            'tipo' => $fila['tipo'],
            'titulo' => $fila['titulo'],
            'autor' => $fila['autor'],
            'editorial' => $fila['editorial'],
            'firma' => $fila['autor'] ?: $fila['editorial'],
            'categoria' => $fila['categoria'],
            'categoria_slug' => $fila['categoria_slug'],
            'precio' => (float) $fila['precio'],
            'precio_oferta' => $fila['precio_oferta'] !== null ? (float) $fila['precio_oferta'] : null,
            'anio' => $fila['anio'] !== null ? (int) $fila['anio'] : null,
            'descripcion' => $fila['descripcion'],
            'estilo_portada' => $fila['estilo_portada'],
            'imagen' => $fila['imagen'],
            'en_autor' => (int) $fila['en_autor'],
            'en_aventura' => (int) $fila['en_aventura'],
            'disponible' => (int) $fila['disponible'],
            'id_autor' => $fila['id_autor'] !== null ? (int) $fila['id_autor'] : null,
            'id_categoria' => (int) $fila['id_categoria'],
        ];
    }
}
