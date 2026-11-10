<?php
/**
 * Reemplaza portadas por tapas reales de Open Library (tarea académica).
 * docker exec Practicos3MD_app php /var/www/html/api/config/fix_todas_portadas.php
 */
declare(strict_types=1);

$pdo = new PDO(
    'mysql:host=db;dbname=mi_base_de_datos;charset=utf8mb4',
    'root',
    'root_password',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$dir = dirname(__DIR__, 2) . '/front/assets/portadas/reales';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$ctx = stream_context_create([
    'http' => [
        'timeout' => 35,
        'follow_location' => 1,
        'header' => "User-Agent: Practicos3MD-tarea/1.0\r\n",
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);

function slugify(string $texto): string
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?? $texto;
    return trim($texto, '-') ?: 'tapa';
}

function httpGet(string $url, $ctx)
{
    return @file_get_contents($url, false, $ctx);
}

function esImagenValida(string $bin): bool
{
    if (strlen($bin) < 8000) {
        return false;
    }
    $info = @getimagesizefromstring($bin);
    return $info !== false && ($info[0] ?? 0) >= 100 && ($info[1] ?? 0) >= 120;
}

function bajarTapa(string $titulo, string $autor, $ctx): string|false
{
    $consultas = [
        'https://openlibrary.org/search.json?title=' . rawurlencode($titulo) . '&author=' . rawurlencode($autor) . '&limit=12',
        'https://openlibrary.org/search.json?q=' . rawurlencode(trim($titulo . ' ' . $autor)) . '&limit=12',
        'https://openlibrary.org/search.json?title=' . rawurlencode($titulo) . '&limit=12',
    ];

    foreach ($consultas as $url) {
        $json = httpGet($url, $ctx);
        if ($json === false) {
            continue;
        }
        $data = json_decode($json, true);
        foreach ($data['docs'] ?? [] as $doc) {
            if (empty($doc['cover_i'])) {
                continue;
            }
            $t = (string) ($doc['title'] ?? '');
            // Evitar adaptaciones de cine/merchandising obvias
            if (preg_match('/movie|film|soundtrack|calendar|diary|journal|coloring|colouring|kit|lego/i', $t)) {
                continue;
            }
            foreach (['L', 'M'] as $tam) {
                $bin = httpGet('https://covers.openlibrary.org/b/id/' . (int) $doc['cover_i'] . '-' . $tam . '.jpg', $ctx);
                if ($bin !== false && esImagenValida($bin)) {
                    return $bin;
                }
            }
        }
    }
    return false;
}

$rows = $pdo->query(
    "SELECT p.id_producto, p.titulo, p.tipo, p.imagen, COALESCE(a.nombre, '') AS autor
     FROM productos p
     LEFT JOIN autores a ON a.id_autor = p.id_autor
     WHERE p.tipo = 'libro'
     ORDER BY p.id_producto"
)->fetchAll(PDO::FETCH_ASSOC);

$update = $pdo->prepare('UPDATE productos SET imagen = :img, estilo_portada = \'propia\' WHERE id_producto = :id');
$ok = 0;
$fail = 0;

foreach ($rows as $row) {
    $id = (int) $row['id_producto'];
    $titulo = $row['titulo'];
    $autor = $row['autor'] ?: '';
    $clave = slugify($titulo) . '-' . $id;
    $archivo = $dir . '/' . $clave . '.jpg';
    $rel = 'assets/portadas/reales/' . $clave . '.jpg';

    echo "Tapa: {$titulo}… ";
    $bin = bajarTapa($titulo, $autor, $ctx);
    if ($bin === false) {
        echo "FAIL\n";
        $fail++;
        continue;
    }
    file_put_contents($archivo, $bin);
    $update->execute([':img' => $rel, ':id' => $id]);
    echo 'OK ' . strlen($bin) . " bytes → {$rel}\n";
    $ok++;
    usleep(120000);
}

echo "\nListo. Actualizadas: {$ok}. Fallidas: {$fail}.\n";
