<?php
/**
 * Carga libros reales por categoría (≥10 libros cada una) con portadas de Open Library.
 * Uso: docker exec Practicos3MD_app php /var/www/html/api/config/seed_libros.php
 */

declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'db';
$name = getenv('DB_NAME') ?: 'mi_base_de_datos';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'root_password';

$pdo = new PDO(
    "mysql:host={$host};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$raiz = dirname(__DIR__, 2);
$dirPortadas = $raiz . '/front/assets/portadas/reales';
if (!is_dir($dirPortadas)) {
    mkdir($dirPortadas, 0775, true);
}

/**
 * Categorías: 1 drama, 2 terror, 3 amor, 4 aventura, 5 fantasia, 6 ciencia, 7 cultura, 8 clasicos
 * isbn → portada Open Library; clave → nombre de archivo local
 */
$libros = [
    // Drama (faltan para llegar a 10)
    ['clave' => 'crimen-castigo', 'isbn' => '9788420674209', 'titulo' => 'Crimen y castigo', 'autor' => 'Fiódor Dostoyevski', 'cat' => 1, 'anio' => 1866, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Alianza', 'desc' => 'Raskólnikov comete un asesinato y enfrenta la culpa en San Petersburgo.'],
    ['clave' => 'anna-karenina', 'isbn' => '9788420674216', 'titulo' => 'Anna Karenina', 'autor' => 'León Tolstói', 'cat' => 1, 'anio' => 1877, 'precio' => 1390, 'oferta' => null, 'editorial' => 'Alianza', 'desc' => 'El adulterio y la sociedad rusa envuelven a Anna en una tragedia íntima.'],
    ['clave' => 'matar-ruiseñor', 'isbn' => '9788497592581', 'titulo' => 'Matar a un ruiseñor', 'autor' => 'Harper Lee', 'cat' => 1, 'anio' => 1960, 'precio' => 1190, 'oferta' => 990, 'editorial' => 'Debolsillo', 'desc' => 'Scout Finch observa el juicio de un hombre negro en el Sur de Estados Unidos.'],
    ['clave' => 'gran-gatsby', 'isbn' => '9788491050391', 'titulo' => 'El gran Gatsby', 'autor' => 'F. Scott Fitzgerald', 'cat' => 1, 'anio' => 1925, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'La opulencia y el deseo de Gatsby en la Costa Este de los años veinte.'],
    ['clave' => 'extranjero', 'isbn' => '9788466338141', 'titulo' => 'El extranjero', 'autor' => 'Albert Camus', 'cat' => 1, 'anio' => 1942, 'precio' => 890, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Meursault narra con frialdad un crimen y el juicio que lo sigue.'],
    ['clave' => 'metamorfosis', 'isbn' => '9788491050483', 'titulo' => 'La metamorfosis', 'autor' => 'Franz Kafka', 'cat' => 1, 'anio' => 1915, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Gregor Samsa despierta convertido en un insecto y su familia se desmorona.'],
    ['clave' => 'uvas-ira', 'isbn' => '9788497592956', 'titulo' => 'Las uvas de la ira', 'autor' => 'John Steinbeck', 'cat' => 1, 'anio' => 1939, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'La familia Joad migra a California en busca de trabajo durante la Gran Depresión.'],
    ['clave' => 'madame-bovary', 'isbn' => '9788491050278', 'titulo' => 'Madame Bovary', 'autor' => 'Gustave Flaubert', 'cat' => 1, 'anio' => 1857, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Emma Bovary busca una vida de novela y choca con la realidad provinciana.'],

    // Terror
    ['clave' => 'resplandor', 'isbn' => '9788497593786', 'titulo' => 'El resplandor', 'autor' => 'Stephen King', 'cat' => 2, 'anio' => 1977, 'precio' => 1590, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Jack Torrance cuida el Overlook y el hotel despierta una amenaza antigua.'],
    ['clave' => 'carrie', 'isbn' => '9788497593779', 'titulo' => 'Carrie', 'autor' => 'Stephen King', 'cat' => 2, 'anio' => 1974, 'precio' => 1090, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Una adolescente con poderes telequinéticos es humillada en el baile de graduación.'],
    ['clave' => 'cementerio-animales', 'isbn' => '9788497593816', 'titulo' => 'Cementerio de animales', 'autor' => 'Stephen King', 'cat' => 2, 'anio' => 1983, 'precio' => 1490, 'oferta' => 1190, 'editorial' => 'Debolsillo', 'desc' => 'Un cementerio indio devuelve a los muertos, pero nunca iguales.'],
    ['clave' => 'frankenstein', 'isbn' => '9788491050155', 'titulo' => 'Frankenstein', 'autor' => 'Mary Shelley', 'cat' => 2, 'anio' => 1818, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Victor Frankenstein crea una criatura y enfrenta las consecuencias de su ambición.'],
    ['clave' => 'jekyll-hyde', 'isbn' => '9788491050534', 'titulo' => 'El extraño caso del Dr. Jekyll y Mr. Hyde', 'autor' => 'Robert Louis Stevenson', 'cat' => 2, 'anio' => 1886, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Un científico divide su naturaleza en dos identidades enfrentadas.'],
    ['clave' => 'dracula-libro', 'isbn' => '9788491050292', 'titulo' => 'Drácula', 'autor' => 'Bram Stoker', 'cat' => 2, 'anio' => 1897, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'El conde llega a Inglaterra y un grupo intenta detener su amenaza.'],
    ['clave' => 'llamada-cthulhu', 'isbn' => '9788418002694', 'titulo' => 'La llamada de Cthulhu', 'autor' => 'H. P. Lovecraft', 'cat' => 2, 'anio' => 1928, 'precio' => 890, 'oferta' => null, 'editorial' => 'Alma', 'desc' => 'Relatos cósmicos donde lo humano es insignificante frente a lo antiguo.'],
    ['clave' => 'silencio-corderos', 'isbn' => '9788497592444', 'titulo' => 'El silencio de los corderos', 'autor' => 'Thomas Harris', 'cat' => 2, 'anio' => 1988, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Clarice Starling consulta a Hannibal Lecter para atrapar a un asesino.'],
    ['clave' => 'exorcista', 'isbn' => '9788497593120', 'titulo' => 'El exorcista', 'autor' => 'William Peter Blatty', 'cat' => 2, 'anio' => 1971, 'precio' => 1190, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Una niña es poseída y dos sacerdotes enfrentan un mal antiguo.'],

    // Amor
    ['clave' => 'sentido-sensibilidad', 'isbn' => '9788491050414', 'titulo' => 'Sentido y sensibilidad', 'autor' => 'Jane Austen', 'cat' => 3, 'anio' => 1811, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Las hermanas Dashwood equilibran razón y sentimiento en el amor.'],
    ['clave' => 'emma', 'isbn' => '9788491050421', 'titulo' => 'Emma', 'autor' => 'Jane Austen', 'cat' => 3, 'anio' => 1815, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Emma Woodhouse cree arreglar los matrimonios ajenos y tropieza con el suyo.'],
    ['clave' => 'jane-eyre', 'isbn' => '9788491050469', 'titulo' => 'Jane Eyre', 'autor' => 'Charlotte Brontë', 'cat' => 3, 'anio' => 1847, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Una institutriz independiente se enamora del misterioso señor Rochester.'],
    ['clave' => 'cumbres-borrascosas', 'isbn' => '9788491050476', 'titulo' => 'Cumbres Borrascosas', 'autor' => 'Emily Brontë', 'cat' => 3, 'anio' => 1847, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Heathcliff y Catherine viven un amor destructivo en los páramos de Yorkshire.'],
    ['clave' => 'agua-chocolate', 'isbn' => '9788466331913', 'titulo' => 'Como agua para chocolate', 'autor' => 'Laura Esquivel', 'cat' => 3, 'anio' => 1989, 'precio' => 1090, 'oferta' => 890, 'editorial' => 'Debolsillo', 'desc' => 'Tita cocina emociones que alteran a todos en su familia mexicana.'],
    ['clave' => 'bajo-misma-estrella', 'isbn' => '9788499892498', 'titulo' => 'Bajo la misma estrella', 'autor' => 'John Green', 'cat' => 3, 'anio' => 2012, 'precio' => 990, 'oferta' => null, 'editorial' => 'Nube de Tinta', 'desc' => 'Hazel y Augustus se conocen en un grupo de apoyo y viven un amor intenso.'],
    ['clave' => 'normal-people', 'isbn' => '9788439736196', 'titulo' => 'Gente normal', 'autor' => 'Sally Rooney', 'cat' => 3, 'anio' => 2018, 'precio' => 1190, 'oferta' => null, 'editorial' => 'Literatura Random House', 'desc' => 'Connell y Marianne cruzan amistad y deseo desde el instituto hasta Dublín.'],
    ['clave' => 'notebook', 'isbn' => '9788497592178', 'titulo' => 'El cuaderno de Noah', 'autor' => 'Nicholas Sparks', 'cat' => 3, 'anio' => 1996, 'precio' => 990, 'oferta' => null, 'editorial' => 'Roca', 'desc' => 'Noah lee a Allie la historia de su amor de juventud.'],
    ['clave' => 'persuasión', 'isbn' => '9788491050438', 'titulo' => 'Persuasión', 'autor' => 'Jane Austen', 'cat' => 3, 'anio' => 1817, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Anne Elliot reencuentra al capitán Wentworth años después de rechazarlo.'],
    ['clave' => 'orlandos-love', 'isbn' => '9788466334181', 'titulo' => 'Lo que el viento se llevó', 'autor' => 'Margaret Mitchell', 'cat' => 3, 'anio' => 1936, 'precio' => 1490, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Scarlett O\'Hara sobrevive a la guerra civil estadounidense entre amores y pérdidas.'],

    // Aventura
    ['clave' => 'odisea', 'isbn' => '9788424938857', 'titulo' => 'La Odisea', 'autor' => 'Homero', 'cat' => 4, 'anio' => null, 'precio' => 990, 'oferta' => null, 'editorial' => 'Gredos', 'desc' => 'Ulises regresa a Ítaca tras la guerra de Troya enfrentando dioses y monstruos.'],
    ['clave' => 'isla-tesoro', 'isbn' => '9788491050544', 'titulo' => 'La isla del tesoro', 'autor' => 'Robert Louis Stevenson', 'cat' => 4, 'anio' => 1883, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Jim Hawkins busca un tesoro pirata con el mapa del capitán Flint.'],
    ['clave' => 'montecristo', 'isbn' => '9788491050612', 'titulo' => 'El conde de Montecristo', 'autor' => 'Alexandre Dumas', 'cat' => 4, 'anio' => 1844, 'precio' => 1590, 'oferta' => 1290, 'editorial' => 'Penguin Clásicos', 'desc' => 'Edmond Dantès escapa de prisión y trama una venganza perfecta.'],
    ['clave' => '20000-leguas', 'isbn' => '9788491050704', 'titulo' => 'Veinte mil leguas de viaje submarino', 'autor' => 'Julio Verne', 'cat' => 4, 'anio' => 1870, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'El capitán Nemo recorre los océanos a bordo del Nautilus.'],
    ['clave' => 'vuelta-mundo', 'isbn' => '9788491050711', 'titulo' => 'La vuelta al mundo en ochenta días', 'autor' => 'Julio Verne', 'cat' => 4, 'anio' => 1873, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Phileas Fogg apuesta a dar la vuelta al planeta en ochenta días.'],
    ['clave' => 'tom-sawyer', 'isbn' => '9788491050728', 'titulo' => 'Las aventuras de Tom Sawyer', 'autor' => 'Mark Twain', 'cat' => 4, 'anio' => 1876, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Tom y Huck viven travesuras y peligros a orillas del Mississippi.'],

    // Fantasía
    ['clave' => 'hobbit', 'isbn' => '9788445071403', 'titulo' => 'El Hobbit', 'autor' => 'J. R. R. Tolkien', 'cat' => 5, 'anio' => 1937, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Minotauro', 'desc' => 'Bilbo Bolsón parte con enanos a recuperar el tesoro del dragón Smaug.'],
    ['clave' => 'comunidad-anillo', 'isbn' => '9788445071489', 'titulo' => 'La comunidad del anillo', 'autor' => 'J. R. R. Tolkien', 'cat' => 5, 'anio' => 1954, 'precio' => 1490, 'oferta' => null, 'editorial' => 'Minotauro', 'desc' => 'Frodo inicia el viaje para destruir el Anillo Único.'],
    ['clave' => 'nombre-viento', 'isbn' => '9788401352836', 'titulo' => 'El nombre del viento', 'autor' => 'Patrick Rothfuss', 'cat' => 5, 'anio' => 2007, 'precio' => 1690, 'oferta' => 1390, 'editorial' => 'Plaza & Janés', 'desc' => 'Kvothe cuenta cómo se convirtió en leyenda entre magia y música.'],
    ['clave' => 'ultimo-deseo', 'isbn' => '9788498890685', 'titulo' => 'El último deseo', 'autor' => 'Andrzej Sapkowski', 'cat' => 5, 'anio' => 1993, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Bibliópolis', 'desc' => 'Geralt de Rivia caza monstruos en relatos que abren la saga de The Witcher.'],

    // Ciencia
    ['clave' => 'breve-historia-tiempo', 'isbn' => '9788497933050', 'titulo' => 'Historia del tiempo', 'autor' => 'Stephen Hawking', 'cat' => 6, 'anio' => 1988, 'precio' => 1190, 'oferta' => null, 'editorial' => 'Crítica', 'desc' => 'Hawking explica el Big Bang, los agujeros negros y el tiempo para todos.'],
    ['clave' => 'cosmos', 'isbn' => '9788408163381', 'titulo' => 'Cosmos', 'autor' => 'Carl Sagan', 'cat' => 6, 'anio' => 1980, 'precio' => 1390, 'oferta' => null, 'editorial' => 'Planeta', 'desc' => 'Un recorrido por el universo, la vida y el lugar de la humanidad.'],
    ['clave' => 'sapiens', 'isbn' => '9788499926223', 'titulo' => 'Sapiens', 'autor' => 'Yuval Noah Harari', 'cat' => 6, 'anio' => 2011, 'precio' => 1490, 'oferta' => 1190, 'editorial' => 'Debate', 'desc' => 'Cómo el Homo sapiens pasó de tribus a sociedades globales.'],
    ['clave' => 'homo-deus', 'isbn' => '9788499926988', 'titulo' => 'Homo Deus', 'autor' => 'Yuval Noah Harari', 'cat' => 6, 'anio' => 2015, 'precio' => 1490, 'oferta' => null, 'editorial' => 'Debate', 'desc' => 'Harari imagina el futuro de la humanidad tras el dominio tecnológico.'],
    ['clave' => 'origen-especies', 'isbn' => '9788491050735', 'titulo' => 'El origen de las especies', 'autor' => 'Charles Darwin', 'cat' => 6, 'anio' => 1859, 'precio' => 1090, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'La selección natural como explicación de la diversidad de la vida.'],
    ['clave' => 'astrofisica-prisa', 'isbn' => '9786077474517', 'titulo' => 'Astrofísica para gente con prisa', 'autor' => 'Neil deGrasse Tyson', 'cat' => 6, 'anio' => 2017, 'precio' => 990, 'oferta' => null, 'editorial' => 'Paidós', 'desc' => 'Capítulos breves sobre el cosmos para leer en cualquier momento.'],
    ['clave' => 'gen-egoista', 'isbn' => '9788497599131', 'titulo' => 'El gen egoísta', 'autor' => 'Richard Dawkins', 'cat' => 6, 'anio' => 1976, 'precio' => 1190, 'oferta' => null, 'editorial' => 'Salvat', 'desc' => 'La evolución vista desde la perspectiva de los genes.'],
    ['clave' => 'fisica-imposible', 'isbn' => '9788499087085', 'titulo' => 'La física de lo imposible', 'autor' => 'Michio Kaku', 'cat' => 6, 'anio' => 2008, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Qué tecnologías de ciencia ficción podrían existir algún día.'],
    ['clave' => 'breve-historia-casi', 'isbn' => '9788498920475', 'titulo' => 'Una breve historia de casi todo', 'autor' => 'Bill Bryson', 'cat' => 6, 'anio' => 2003, 'precio' => 1390, 'oferta' => null, 'editorial' => 'RBA', 'desc' => 'La ciencia contada con humor: desde el átomo hasta los dinosaurios.'],
    ['clave' => 'hombre-que-confundio', 'isbn' => '9788433920478', 'titulo' => 'El hombre que confundió a su mujer con un sombrero', 'autor' => 'Oliver Sacks', 'cat' => 6, 'anio' => 1985, 'precio' => 1090, 'oferta' => null, 'editorial' => 'Anagrama', 'desc' => 'Casos clínicos que revelan cómo funciona —y falla— el cerebro.'],

    // Cultura
    ['clave' => '1984', 'isbn' => '9788499890944', 'titulo' => '1984', 'autor' => 'George Orwell', 'cat' => 7, 'anio' => 1949, 'precio' => 990, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Winston Smith vive bajo la vigilancia del Gran Hermano.'],
    ['clave' => 'fahrenheit', 'isbn' => '9788497594257', 'titulo' => 'Fahrenheit 451', 'autor' => 'Ray Bradbury', 'cat' => 7, 'anio' => 1953, 'precio' => 990, 'oferta' => 790, 'editorial' => 'Minotauro', 'desc' => 'En un futuro donde quemar libros es el trabajo de los bomberos.'],
    ['clave' => 'arte-guerra', 'isbn' => '9788491050742', 'titulo' => 'El arte de la guerra', 'autor' => 'Sun Tzu', 'cat' => 7, 'anio' => null, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Estrategia militar china aplicada al conflicto y a la decisión.'],
    ['clave' => 'principe', 'isbn' => '9788491050759', 'titulo' => 'El príncipe', 'autor' => 'Nicolás Maquiavelo', 'cat' => 7, 'anio' => 1532, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Consejos de poder y realismo político para gobernar un Estado.'],
    ['clave' => 'meditaciones', 'isbn' => '9788491050766', 'titulo' => 'Meditaciones', 'autor' => 'Marco Aurelio', 'cat' => 7, 'anio' => null, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Notas estoicas del emperador romano sobre virtud y equilibrio.'],
    ['clave' => 'pensar-rapido', 'isbn' => '9788499087207', 'titulo' => 'Pensar rápido, pensar despacio', 'autor' => 'Daniel Kahneman', 'cat' => 7, 'anio' => 2011, 'precio' => 1590, 'oferta' => null, 'editorial' => 'Debate', 'desc' => 'Los dos sistemas de pensamiento que guían nuestras decisiones.'],
    ['clave' => 'armas-germenes', 'isbn' => '9788499081571', 'titulo' => 'Armas, gérmenes y acero', 'autor' => 'Jared Diamond', 'cat' => 7, 'anio' => 1997, 'precio' => 1490, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Por qué algunas sociedades dominaron a otras a lo largo de la historia.'],
    ['clave' => 'una-educacion', 'isbn' => '9788416709331', 'titulo' => 'Una educación', 'autor' => 'Tara Westover', 'cat' => 7, 'anio' => 2018, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Lumen', 'desc' => 'Memorias de una mujer que pasó de una infancia aislada a Cambridge.'],
    ['clave' => 'ensayo-ceguera', 'isbn' => '9788466331920', 'titulo' => 'Ensayo sobre la ceguera', 'autor' => 'José Saramago', 'cat' => 7, 'anio' => 1995, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Una epidemia de ceguera blanca pone a prueba la civilización.'],
    ['clave' => 'rebelion-granja', 'isbn' => '9788499890951', 'titulo' => 'Rebelión en la granja', 'autor' => 'George Orwell', 'cat' => 7, 'anio' => 1945, 'precio' => 790, 'oferta' => null, 'editorial' => 'Debolsillo', 'desc' => 'Los animales se levantan contra los humanos y descubren un nuevo poder.'],

    // Clásicos
    ['clave' => 'quijote-libro', 'isbn' => '9788491050773', 'titulo' => 'Don Quijote de la Mancha', 'autor' => 'Miguel de Cervantes', 'cat' => 8, 'anio' => 1605, 'precio' => 1290, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'El hidalgo y Sancho recorren Castilla entre molinos y aventuras.'],
    ['clave' => 'divina-comedia', 'isbn' => '9788491050780', 'titulo' => 'La divina comedia', 'autor' => 'Dante Alighieri', 'cat' => 8, 'anio' => 1320, 'precio' => 1190, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Dante atraviesa Infierno, Purgatorio y Paraíso guiado por Virgilio.'],
    ['clave' => 'hamlet', 'isbn' => '9788491050797', 'titulo' => 'Hamlet', 'autor' => 'William Shakespeare', 'cat' => 8, 'anio' => 1603, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'El príncipe de Dinamarca duda entre la venganza y la razón.'],
    ['clave' => 'macbeth', 'isbn' => '9788491050803', 'titulo' => 'Macbeth', 'autor' => 'William Shakespeare', 'cat' => 8, 'anio' => 1606, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'La ambición y la culpa consumen a Macbeth tras el asesinato del rey.'],
    ['clave' => 'eneida', 'isbn' => '9788424938864', 'titulo' => 'La Eneida', 'autor' => 'Virgilio', 'cat' => 8, 'anio' => null, 'precio' => 990, 'oferta' => null, 'editorial' => 'Gredos', 'desc' => 'Eneas huye de Troya y funda el linaje que originará Roma.'],
    ['clave' => 'edipo-rey', 'isbn' => '9788491050810', 'titulo' => 'Edipo rey', 'autor' => 'Sófocles', 'cat' => 8, 'anio' => null, 'precio' => 690, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Edipo investiga una maldición y descubre la verdad sobre su origen.'],
    ['clave' => 'lazarillo', 'isbn' => '9788491050827', 'titulo' => 'La vida de Lazarillo de Tormes', 'autor' => 'Anónimo', 'cat' => 8, 'anio' => 1554, 'precio' => 690, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Lázaro sirve a varios amos y narra la picaresca del Siglo de Oro.'],
    ['clave' => 'mil-noches', 'isbn' => '9788491050834', 'titulo' => 'Las mil y una noches', 'autor' => 'Anónimo', 'cat' => 8, 'anio' => null, 'precio' => 1390, 'oferta' => 1090, 'editorial' => 'Penguin Clásicos', 'desc' => 'Sherezade cuenta historias para sobrevivir noche tras noche.'],
    ['clave' => 'fausto', 'isbn' => '9788491050841', 'titulo' => 'Fausto', 'autor' => 'Johann Wolfgang von Goethe', 'cat' => 8, 'anio' => 1808, 'precio' => 990, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Fausto pacta con Mefistófeles en busca de conocimiento absoluto.'],
    ['clave' => 'principito-libro', 'isbn' => '9788498381498', 'titulo' => 'El Principito', 'autor' => 'Antoine de Saint-Exupéry', 'cat' => 8, 'anio' => 1943, 'precio' => 790, 'oferta' => null, 'editorial' => 'Salamandra', 'desc' => 'Un aviador encuentra a un pequeño príncipe venido de otro asteroide.'],
    ['clave' => 'romeo-julieta', 'isbn' => '9788491050851', 'titulo' => 'Romeo y Julieta', 'autor' => 'William Shakespeare', 'cat' => 8, 'anio' => 1597, 'precio' => 790, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Dos amantes de Verona desafían la rivalidad de sus familias.'],
    ['clave' => 'celestina', 'isbn' => '9788491050868', 'titulo' => 'La Celestina', 'autor' => 'Fernando de Rojas', 'cat' => 8, 'anio' => 1499, 'precio' => 890, 'oferta' => null, 'editorial' => 'Penguin Clásicos', 'desc' => 'Calisto y Melibea, mediando la alcahueta Celestina, viven un amor trágico.'],
];

function asegurarAutor(PDO $pdo, string $nombre): int
{
    $buscar = $pdo->prepare('SELECT id_autor FROM autores WHERE nombre = :n LIMIT 1');
    $buscar->execute([':n' => $nombre]);
    $id = $buscar->fetchColumn();
    if ($id) {
        return (int) $id;
    }
    $alta = $pdo->prepare('INSERT INTO autores (nombre, es_destacado) VALUES (:n, 0)');
    $alta->execute([':n' => $nombre]);
    return (int) $pdo->lastInsertId();
}

function asegurarEditorial(PDO $pdo, string $nombre): int
{
    $buscar = $pdo->prepare('SELECT id_editorial FROM editoriales WHERE nombre = :n LIMIT 1');
    $buscar->execute([':n' => $nombre]);
    $id = $buscar->fetchColumn();
    if ($id) {
        return (int) $id;
    }
    $alta = $pdo->prepare('INSERT INTO editoriales (nombre) VALUES (:n)');
    $alta->execute([':n' => $nombre]);
    return (int) $pdo->lastInsertId();
}

function httpGet(string $url): string|false
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 30,
            'follow_location' => 1,
            'header' => "User-Agent: Practicos3MD-biblioteca/1.0\r\nAccept: */*\r\n",
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    return @file_get_contents($url, false, $ctx);
}

function guardarImagen(string $bin, string $destino): bool
{
    if ($bin === false || strlen($bin) < 2500) {
        return false;
    }
    $info = @getimagesizefromstring($bin);
    if ($info === false) {
        return false;
    }
    return file_put_contents($destino, $bin) !== false;
}

function coverIdUrl(int $coverId, string $tam = 'L'): string
{
    return "https://covers.openlibrary.org/b/id/{$coverId}-{$tam}.jpg";
}

function bajarPortada(string $isbn, string $titulo, string $autor, string $destino): bool
{
    $intentos = [
        "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg",
        "https://covers.openlibrary.org/b/isbn/{$isbn}-M.jpg",
    ];
    foreach ($intentos as $url) {
        if (guardarImagen(httpGet($url), $destino)) {
            return true;
        }
    }

    $q = rawurlencode($titulo . ' ' . $autor);
    $json = httpGet("https://openlibrary.org/search.json?q={$q}&limit=5");
    if ($json === false) {
        return false;
    }
    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['docs'])) {
        return false;
    }
    foreach ($data['docs'] as $doc) {
        if (empty($doc['cover_i'])) {
            continue;
        }
        $coverId = (int) $doc['cover_i'];
        if (guardarImagen(httpGet(coverIdUrl($coverId, 'L')), $destino)) {
            return true;
        }
        if (guardarImagen(httpGet(coverIdUrl($coverId, 'M')), $destino)) {
            return true;
        }
    }
    return false;
}

$existeTitulo = $pdo->prepare('SELECT id_producto FROM productos WHERE titulo = :t AND tipo = \'libro\' LIMIT 1');
$insertar = $pdo->prepare(
    'INSERT INTO productos
    (tipo, titulo, id_autor, id_categoria, precio, precio_oferta, anio, descripcion,
     estilo_portada, en_autor, en_aventura, editorial, id_editorial, imagen, disponible)
     VALUES
    (\'libro\', :titulo, :autor, :cat, :precio, :oferta, :anio, :desc,
     \'propia\', 0, :aventura, :editorial, :id_ed, :imagen, 1)'
);

$ok = 0;
$saltados = 0;
$sinPortada = 0;
$errores = [];

foreach ($libros as $libro) {
    $existeTitulo->execute([':t' => $libro['titulo']]);
    if ($existeTitulo->fetchColumn()) {
        $saltados++;
        echo "Ya existe: {$libro['titulo']}\n";
        continue;
    }

    $archivo = $dirPortadas . '/' . $libro['clave'] . '.jpg';
    $relativa = 'assets/portadas/reales/' . $libro['clave'] . '.jpg';

    if (!is_file($archivo) || filesize($archivo) < 2500) {
        echo "Bajando portada: {$libro['titulo']} ({$libro['isbn']})...\n";
        if (!bajarPortada($libro['isbn'], $libro['titulo'], $libro['autor'], $archivo)) {
            $sinPortada++;
            $errores[] = "Sin portada: {$libro['titulo']} ISBN {$libro['isbn']}";
            continue;
        }
    }

    $idAutor = asegurarAutor($pdo, $libro['autor']);
    $idEd = asegurarEditorial($pdo, $libro['editorial']);
    $enAventura = ((int) $libro['cat'] === 4) ? 1 : 0;

    $insertar->execute([
        ':titulo' => $libro['titulo'],
        ':autor' => $idAutor,
        ':cat' => $libro['cat'],
        ':precio' => $libro['precio'],
        ':oferta' => $libro['oferta'],
        ':anio' => $libro['anio'],
        ':desc' => $libro['desc'],
        ':aventura' => $enAventura,
        ':editorial' => $libro['editorial'],
        ':id_ed' => $idEd,
        ':imagen' => $relativa,
    ]);
    $ok++;
    echo "OK: {$libro['titulo']}\n";
}

echo "\n--- Resumen ---\n";
echo "Insertados: {$ok}\n";
echo "Ya existían: {$saltados}\n";
echo "Sin portada (omitidos): {$sinPortada}\n";

$conteo = $pdo->query(
    "SELECT c.nombre, SUM(CASE WHEN p.tipo='libro' THEN 1 ELSE 0 END) AS libros
     FROM categorias c
     LEFT JOIN productos p ON p.id_categoria = c.id_categoria AND p.disponible = 1
     GROUP BY c.id_categoria
     ORDER BY c.id_categoria"
)->fetchAll(PDO::FETCH_ASSOC);

foreach ($conteo as $fila) {
    echo "{$fila['nombre']}: {$fila['libros']} libros\n";
}

if ($errores) {
    echo "\nAvisos:\n";
    foreach ($errores as $e) {
        echo "- {$e}\n";
    }
}
