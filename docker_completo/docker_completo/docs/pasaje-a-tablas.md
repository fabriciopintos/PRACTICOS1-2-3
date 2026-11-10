# Pasaje a tablas — Practicos3MD

Cada entidad del MER queda en una tabla. Las relaciones 1 a N se resuelven con una clave foránea en el lado de muchos. No hace falta una tabla intermedia: ninguna relación es de muchos a muchos.

## roles

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_rol | INT, autoincremental | PK |
| nombre | VARCHAR(40), único | |

Valores cargados: `administrador`, `usuario`.

## usuarios

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_usuario | INT, autoincremental | PK |
| nombre | VARCHAR(120) | |
| email | VARCHAR(120), único | |
| password_hash | VARCHAR(255) | |
| id_rol | INT, obligatorio | FK → roles.id_rol |
| activo | TINYINT(1), por defecto 1 | |
| creado_en | TIMESTAMP | |

## categorias

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_categoria | INT, autoincremental | PK |
| nombre | VARCHAR(50) | |
| slug | VARCHAR(50), único | |

## autores

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_autor | INT, autoincremental | PK |
| nombre | VARCHAR(120) | |
| es_destacado | TINYINT(1) | |

## editoriales

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_editorial | INT, autoincremental | PK |
| nombre | VARCHAR(120), único | |

## productos

| Campo | Tipo | Clave |
| --- | --- | --- |
| id_producto | INT, autoincremental | PK |
| tipo | ENUM libro, revista, audiolibro | |
| titulo | VARCHAR(160) | |
| id_autor | INT, nulo | FK → autores.id_autor |
| id_categoria | INT, obligatorio | FK → categorias.id_categoria |
| id_editorial | INT, nulo | FK → editoriales.id_editorial |
| precio | DECIMAL(10,2) | |
| precio_oferta | DECIMAL(10,2), nulo | |
| anio | INT, nulo | |
| descripcion | VARCHAR(400) | |
| estilo_portada | VARCHAR(40) | |
| imagen | VARCHAR(180), nulo | |
| en_autor | TINYINT(1) | |
| en_aventura | TINYINT(1) | |
| editorial | VARCHAR(120), nulo | |

`editorial` guarda el nombre del sello para poder buscarlo junto con el título. `id_editorial` es la relación real con la tabla `editoriales`. El script de carga deja ambos alineados.

## Dónde está implementado

El script completo está en `api/config/init.sql`. MySQL lo ejecuta al crear el volumen y también se puede volver a correr sobre la base `mi_base_de_datos`.
