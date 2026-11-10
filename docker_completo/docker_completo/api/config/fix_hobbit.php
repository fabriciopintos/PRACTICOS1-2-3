<?php
$ctx = stream_context_create([
    'http' => ['timeout' => 30, 'header' => "User-Agent: Practicos3MD/1.0\r\n"],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);
$json = file_get_contents('https://openlibrary.org/search.json?q=The%20Hobbit%20Tolkien&limit=20', false, $ctx);
$data = json_decode((string) $json, true);
foreach ($data['docs'] ?? [] as $doc) {
    $t = $doc['title'] ?? '';
    if (empty($doc['cover_i'])) {
        continue;
    }
    if (preg_match('/desol|smaug|inesperado|unexpected|battle|batalha/i', $t)) {
        continue;
    }
    if (!preg_match('/^(The Hobbit|El Hobbit)$/i', trim($t))) {
        continue;
    }
    $bin = file_get_contents('https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-L.jpg', false, $ctx);
    if ($bin && strlen($bin) > 12000 && @getimagesizefromstring($bin)) {
        file_put_contents(dirname(__DIR__, 2) . '/front/assets/portadas/reales/hobbit.jpg', $bin);
        echo "OK $t cover={$doc['cover_i']} bytes=" . strlen($bin) . "\n";
        exit(0);
    }
}
echo "FAIL\n";
exit(1);
