<?php
$fixes = [
    ['hobbit', 'El Hobbit', 'Tolkien'],
    ['carrie', 'Carrie', 'Stephen King'],
    ['notebook', 'The Notebook', 'Nicholas Sparks'],
    ['silencio-corderos', 'Silence of the Lambs', 'Thomas Harris'],
    ['comunidad-anillo', 'Fellowship of the Ring', 'Tolkien'],
    ['ensayo-ceguera', 'Blindness', 'Saramago'],
    ['jekyll-hyde', 'Strange Case of Dr Jekyll and Mr Hyde', 'Stevenson'],
];

$dir = dirname(__DIR__, 2) . '/front/assets/portadas/reales';
$ctx = stream_context_create([
    'http' => ['timeout' => 30, 'header' => "User-Agent: Practicos3MD/1.0\r\n"],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);

foreach ($fixes as [$clave, $titulo, $autor]) {
    $url = 'https://openlibrary.org/search.json?title=' . rawurlencode($titulo)
        . '&author=' . rawurlencode($autor) . '&limit=10';
    $json = @file_get_contents($url, false, $ctx);
    $data = json_decode((string) $json, true);
    $ok = false;
    foreach (($data['docs'] ?? []) as $doc) {
        if (empty($doc['cover_i'])) {
            continue;
        }
        $bin = @file_get_contents(
            'https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-L.jpg',
            false,
            $ctx
        );
        if ($bin && strlen($bin) > 10000 && @getimagesizefromstring($bin)) {
            file_put_contents("$dir/$clave.jpg", $bin);
            echo "OK $clave cover={$doc['cover_i']} bytes=" . strlen($bin) . " title=" . ($doc['title'] ?? '') . "\n";
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        echo "FAIL $clave\n";
    }
}
