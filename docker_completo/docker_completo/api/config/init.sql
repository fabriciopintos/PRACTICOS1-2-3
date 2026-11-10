-- Biblioteca: libros, revistas y audiolibros
-- Si el volumen de MySQL ya existía, hay que recrearlo para que este script corra:
-- docker compose down -v && docker compose up --build

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS editoriales;
DROP TABLE IF EXISTS autores;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE editoriales (
    id_editorial INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE autores (
    id_autor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    es_destacado TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('libro', 'revista', 'audiolibro') NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    id_autor INT NULL,
    id_categoria INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    precio_oferta DECIMAL(10, 2) NULL,
    anio INT NULL,
    descripcion VARCHAR(400) NOT NULL,
    estilo_portada VARCHAR(40) NOT NULL,
    en_autor TINYINT(1) NOT NULL DEFAULT 0,
    en_aventura TINYINT(1) NOT NULL DEFAULT 0,
    editorial VARCHAR(120) NULL,
    FOREIGN KEY (id_autor) REFERENCES autores(id_autor),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nombre, slug) VALUES
('Drama', 'drama'),
('Terror', 'terror'),
('Amor', 'amor'),
('Aventura', 'aventura'),
('Fantasía', 'fantasia'),
('Ciencia', 'ciencia'),
('Cultura', 'cultura'),
('Clásicos', 'clasicos');

INSERT INTO autores (nombre, es_destacado) VALUES
('George R.R. Martin', 1),
('J. K. Rowling', 0),
('Rick Riordan', 0),
('Suzanne Collins', 0),
('Homero', 0),
('Jane Austen', 0),
('Gabriel García Márquez', 0),
('Stephen King', 0),
('Isabel Allende', 0),
('Antoine de Saint-Exupéry', 0),
('Bram Stoker', 0),
('Miguel de Cervantes', 0);

INSERT INTO productos
    (tipo, titulo, id_autor, id_categoria, precio, precio_oferta, anio, descripcion, estilo_portada, en_autor, en_aventura, editorial)
VALUES
('libro', 'Juego de tronos', 1, 5, 2490.00, NULL, 1996,
 'El comienzo de la lucha por el Trono de Hierro, entre familias, inviernos y secretos.', 'got1', 1, 0, NULL),
('libro', 'Choque de reyes', 1, 5, 2490.00, NULL, 1998,
 'Los reinos se parten y las alianzas cambian mientras la guerra se extiende.', 'got2', 1, 0, NULL),
('libro', 'Tormenta de espadas', 1, 5, 2690.00, NULL, 2000,
 'Batallas, traiciones y juramentos marcan el punto más crudo de la saga.', 'got3', 1, 0, NULL),
('libro', 'Festín de cuervos', 1, 5, 2590.00, NULL, 2005,
 'Intrigas de corte y viajes largos mientras el reino intenta recomponerse.', 'got4', 1, 0, NULL),
('libro', 'Danza de dragones', 1, 5, 2890.00, NULL, 2011,
 'Dos historias lejanas avanzan hacia el mismo invierno.', 'got5', 1, 0, NULL),
('libro', 'Fuego y sangre', 1, 5, 3290.00, 2790.00, 2018,
 'La historia de los Targaryen y la conquista de los siete reinos.', 'got6', 1, 0, NULL),
('libro', 'Harry Potter y la Orden del Fénix', 2, 4, 1890.00, NULL, 2003,
 'Harry vuelve a Hogwarts cuando una sociedad secreta se reúne otra vez.', 'hp', 0, 1, NULL),
('libro', 'Percy Jackson y el ladrón del rayo', 3, 4, 1590.00, NULL, 2005,
 'Percy descubre un origen divino y cruza el país para evitar una guerra.', 'percy', 0, 1, NULL),
('libro', 'Los juegos del hambre', 4, 4, 1690.00, 1290.00, 2008,
 'Katniss vuelve a la arena y todo el distrito mira el juego.', 'hambre', 0, 1, NULL),
('libro', 'La Ilíada', 5, 4, 990.00, NULL, NULL,
 'El poema de la guerra de Troya, los héroes y la cólera de Aquiles.', 'iliada', 0, 1, NULL),
('libro', 'Orgullo y prejuicio', 6, 3, 890.00, 690.00, 1813,
 'Elizabeth Bennet y el señor Darcy chocan entre el orgullo y el afecto.', 'austen', 0, 0, NULL),
('libro', 'Cien años de soledad', 7, 1, 1490.00, NULL, 1967,
 'La saga de la familia Buendía en Macondo, entre la memoria y la soledad.', 'cien', 0, 0, NULL),
('libro', 'It', 8, 2, 1790.00, NULL, 1986,
 'En Derry, un grupo de amigos enfrenta al mal que vuelve cada generación.', 'it', 0, 0, NULL),
('libro', 'La casa de los espíritus', 9, 1, 1390.00, NULL, 1982,
 'La historia de los Trueba entre el amor, la política y los espíritus.', 'espiritus', 0, 0, NULL),
('revista', 'Letras del Sur', NULL, 7, 450.00, NULL, 2026,
 'Ensayos y cuentos de autores contemporáneos. Edición de otoño.', 'revista1', 0, 0, 'Tinta Viva'),
('revista', 'Bitácora Científica', NULL, 6, 520.00, 390.00, 2026,
 'Hallazgos y experimentos contados para leer en un rato.', 'revista2', 0, 0, 'Laboratorio Norte'),
('revista', 'Rutas y Mapas', NULL, 4, 480.00, NULL, 2026,
 'Mapas, caminatas y ciudades para preparar la próxima salida.', 'revista3', 0, 0, 'Editorial Brújula'),
('revista', 'Corazón de Papel', NULL, 3, 350.00, NULL, 2026,
 'Relatos breves y cartas sobre vínculos y la vida en pareja.', 'revista4', 0, 0, 'Editorial Nido'),
('audiolibro', 'El Principito', 10, 8, 690.00, NULL, 1943,
 'El relato de Saint-Exupéry, narrado de principio a fin.', 'audio1', 0, 0, NULL),
('audiolibro', 'Drácula', 11, 2, 590.00, NULL, 1897,
 'El conde llega a Inglaterra en esta versión narrada.', 'audio2', 0, 0, NULL),
('audiolibro', 'Don Quijote de la Mancha', 12, 4, 750.00, 590.00, 1605,
 'Una selección de las aventuras del hidalgo y de Sancho.', 'audio3', 0, 0, NULL),
('audiolibro', 'Orgullo y prejuicio', 6, 3, 640.00, NULL, 1813,
 'La novela de Austen narrada con voces distintas.', 'audio4', 0, 0, NULL);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    foto VARCHAR(180) NULL,
    bio VARCHAR(280) NULL,
    tema VARCHAR(30) NOT NULL DEFAULT 'papel',
    color_acento VARCHAR(20) NOT NULL DEFAULT '#7c2f2f',
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE productos
    ADD COLUMN id_editorial INT NULL,
    ADD COLUMN imagen VARCHAR(180) NULL,
    ADD COLUMN disponible TINYINT(1) NOT NULL DEFAULT 1,
    ADD CONSTRAINT fk_producto_editorial FOREIGN KEY (id_editorial) REFERENCES editoriales(id_editorial);

INSERT INTO roles (nombre) VALUES
('administrador'),
('usuario');

INSERT INTO editoriales (nombre) VALUES
('Gigamesh'),
('Salamandra'),
('Montena'),
('RBA'),
('Gredos'),
('Alba'),
('Sudamericana'),
('Plaza & Janés'),
('Tinta Viva'),
('Laboratorio Norte'),
('Editorial Brújula'),
('Editorial Nido'),
('Voces en Papel');

INSERT INTO usuarios (nombre, email, password_hash, id_rol) VALUES
('Admin Biblioteca', 'admin@biblioteca.test', '$2y$10$6GzbmrGscTIPqvVoalfrLeHtIQwgblKqzWqellIrs.KsSiiXggRPa', 1),
('Lector Invitado', 'lector@biblioteca.test', '$2y$10$75cy3EqRmEUCcvCqXINvnOPnP./AWqe7ttfrGIDO30msEot/h.7jW', 2);

UPDATE productos SET imagen = CONCAT('assets/portadas/', estilo_portada, '.jpg');
UPDATE productos SET id_editorial = 1 WHERE estilo_portada LIKE 'got%';
UPDATE productos SET id_editorial = 2 WHERE estilo_portada = 'hp';
UPDATE productos SET id_editorial = 3 WHERE estilo_portada = 'percy';
UPDATE productos SET id_editorial = 4 WHERE estilo_portada = 'hambre';
UPDATE productos SET id_editorial = 5 WHERE estilo_portada = 'iliada';
UPDATE productos SET id_editorial = 6 WHERE estilo_portada = 'austen';
UPDATE productos SET id_editorial = 7 WHERE estilo_portada IN ('cien', 'espiritus');
UPDATE productos SET id_editorial = 8 WHERE estilo_portada = 'it';
UPDATE productos SET id_editorial = 9 WHERE estilo_portada = 'revista1';
UPDATE productos SET id_editorial = 10 WHERE estilo_portada = 'revista2';
UPDATE productos SET id_editorial = 11 WHERE estilo_portada = 'revista3';
UPDATE productos SET id_editorial = 12 WHERE estilo_portada = 'revista4';
UPDATE productos SET id_editorial = 13 WHERE estilo_portada LIKE 'audio%';

UPDATE productos p
INNER JOIN editoriales e ON e.id_editorial = p.id_editorial
SET p.editorial = e.nombre;
