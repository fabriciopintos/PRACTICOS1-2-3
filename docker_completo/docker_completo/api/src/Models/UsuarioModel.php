<?php
namespace App\Models;

class UsuarioModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarPorEmail($email) {
        try {
            $sql = 'SELECT u.id_usuario, u.nombre, u.email, u.password_hash, u.activo,
                           u.foto, u.bio, u.tema, u.color_acento, r.nombre AS rol
                    FROM usuarios u
                    INNER JOIN roles r ON r.id_rol = u.id_rol
                    WHERE u.email = :email
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function obtener($id) {
        try {
            $sql = 'SELECT u.id_usuario, u.nombre, u.email, u.activo,
                           u.foto, u.bio, u.tema, u.color_acento, r.nombre AS rol
                    FROM usuarios u
                    INNER JOIN roles r ON r.id_rol = u.id_rol
                    WHERE u.id_usuario = :id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function actualizarPerfil($id, array $datos) {
        $sql = 'UPDATE usuarios
                SET nombre = :nombre,
                    bio = :bio,
                    tema = :tema,
                    color_acento = :color';
        $params = [
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':bio' => $datos['bio'],
            ':tema' => $datos['tema'],
            ':color' => $datos['color_acento'],
        ];
        if (array_key_exists('foto', $datos)) {
            $sql .= ', foto = :foto';
            $params[':foto'] = $datos['foto'];
        }
        $sql .= ' WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function actualizarClave($id, $hash) {
        $stmt = $this->db->prepare('UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id');
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public function mapearSesion(array $usuario) {
        return [
            'id' => (int) $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'rol' => $usuario['rol'],
            'foto' => $usuario['foto'] ?? null,
            'bio' => $usuario['bio'] ?? null,
            'tema' => $usuario['tema'] ?? 'papel',
            'color_acento' => $usuario['color_acento'] ?? '#7c2f2f',
        ];
    }
}
