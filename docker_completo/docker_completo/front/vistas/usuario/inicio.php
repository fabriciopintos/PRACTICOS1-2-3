<?php
require __DIR__ . '/../../includes/sesion.php';
exigirSesion();

require_once __DIR__ . '/../../../api/autoload.php';
require_once __DIR__ . '/../../../api/config/database.php';
\App\Utils\EnvLoader::load(__DIR__ . '/../../../api/.env');

use App\Models\UsuarioModel;

$usuario = usuarioActual();
if ($usuario['rol'] === 'administrador') {
    header('Location: ' . RUTA_ADMIN);
    exit;
}

$mensaje = '';
$error = '';
$temas = [
    'papel' => 'Papel (claro)',
    'sepia' => 'Sepia de lectura',
    'bosque' => 'Bosque',
    'noche' => 'Noche suave',
];
$colores = ['#7c2f2f', '#3d5c4a', '#2f4a6e', '#6a3b1f', '#5b3d6b'];

try {
    $db = (new Database())->getConnection();
    $modelo = new UsuarioModel($db);
    $perfil = $modelo->obtener((int) $usuario['id']);
    if (!$perfil) {
        throw new RuntimeException('No se encontró el perfil.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? 'perfil';

        if ($accion === 'perfil') {
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $bio = trim((string) ($_POST['bio'] ?? ''));
            $tema = (string) ($_POST['tema'] ?? 'papel');
            $color = (string) ($_POST['color_acento'] ?? '#7c2f2f');

            if ($nombre === '' || strlen($nombre) > 120) {
                throw new RuntimeException('El nombre tiene que tener entre 1 y 120 caracteres.');
            }
            if (strlen($bio) > 280) {
                throw new RuntimeException('La bio no puede pasar los 280 caracteres.');
            }
            if (!isset($temas[$tema])) {
                $tema = 'papel';
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color) || !in_array(strtolower($color), array_map('strtolower', $colores), true)) {
                $color = '#7c2f2f';
            }

            $datos = [
                'nombre' => $nombre,
                'bio' => $bio === '' ? null : $bio,
                'tema' => $tema,
                'color_acento' => strtolower($color),
            ];

            if (!empty($_POST['quitar_foto'])) {
                $datos['foto'] = null;
            }

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('No se pudo subir la foto.');
                }
                if ($_FILES['foto']['size'] > 2500000) {
                    throw new RuntimeException('La foto supera los 2,5 MB.');
                }
                $info = @getimagesize($_FILES['foto']['tmp_name']);
                $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
                if (!$info || !isset($tipos[$info[2]])) {
                    throw new RuntimeException('La foto tiene que ser JPG, PNG o WebP.');
                }
                $dir = __DIR__ . '/../../assets/perfiles';
                if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                    throw new RuntimeException('No se pudo crear la carpeta de perfiles.');
                }
                $nombreArchivo = 'perfil-' . (int) $usuario['id'] . '-' . bin2hex(random_bytes(4)) . '.' . $tipos[$info[2]];
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dir . '/' . $nombreArchivo)) {
                    throw new RuntimeException('No se pudo guardar la foto.');
                }
                $datos['foto'] = 'assets/perfiles/' . $nombreArchivo;
            }

            if (!$modelo->actualizarPerfil((int) $usuario['id'], $datos)) {
                throw new RuntimeException('No se pudieron guardar los cambios.');
            }
            $mensaje = 'Perfil actualizado.';
        }

        if ($accion === 'clave') {
            $actual = (string) ($_POST['clave_actual'] ?? '');
            $nueva = (string) ($_POST['clave_nueva'] ?? '');
            $repetir = (string) ($_POST['clave_repetir'] ?? '');
            $cuenta = $modelo->buscarPorEmail($perfil['email']);
            if (!$cuenta || !password_verify($actual, $cuenta['password_hash'])) {
                throw new RuntimeException('La contraseña actual no es correcta.');
            }
            if (strlen($nueva) < 8) {
                throw new RuntimeException('La nueva contraseña necesita al menos 8 caracteres.');
            }
            if ($nueva !== $repetir) {
                throw new RuntimeException('La confirmación no coincide.');
            }
            if (!$modelo->actualizarClave((int) $usuario['id'], password_hash($nueva, PASSWORD_DEFAULT))) {
                throw new RuntimeException('No se pudo cambiar la contraseña.');
            }
            $mensaje = 'Contraseña actualizada.';
        }

        $perfil = $modelo->obtener((int) $usuario['id']);
        $_SESSION['usuario'] = $modelo->mapearSesion($perfil);
        $usuario = $_SESSION['usuario'];
    } else {
        $_SESSION['usuario'] = $modelo->mapearSesion($perfil);
        $usuario = $_SESSION['usuario'];
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
    if (!isset($perfil) || !is_array($perfil)) {
        $perfil = [
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'foto' => $usuario['foto'] ?? null,
            'bio' => $usuario['bio'] ?? '',
            'tema' => $usuario['tema'] ?? 'papel',
            'color_acento' => $usuario['color_acento'] ?? '#7c2f2f',
        ];
    }
}

$temaActual = $perfil['tema'] ?? 'papel';
$colorActual = $perfil['color_acento'] ?? '#7c2f2f';
$fotoUrl = !empty($perfil['foto']) ? '/front/' . ltrim($perfil['foto'], '/') : '';
?>
<!DOCTYPE html>
<html lang="es" data-tema="<?= e($temaActual) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil · EXTERNOLAB</title>
    <link rel="icon" href="../../assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,560;0,9..144,700;1,9..144,560&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=14">
    <link rel="stylesheet" href="../../assets/css/panel.css?v=13">
    <link rel="stylesheet" href="../../assets/css/mobile.css?v=12">
    <link rel="stylesheet" href="../../assets/css/loader.css?v=16">
    <link rel="stylesheet" href="../../assets/css/clave.css">
    <style>:root { --acento-lector: <?= e($colorActual) ?>; }</style>
</head>
<body class="lector lector-full tema-<?= e($temaActual) ?>" data-loader-texto="Abriendo tu cuenta…">
    <div id="loader-pagina" class="loader-pagina" role="status" aria-live="polite" aria-busy="true">
        <div class="loader-caja">
            <p class="loader-marca">EXTERNOLAB</p>
            <div class="loader-libro" aria-hidden="true">
                <span class="loader-libro-lomo"></span>
                <span class="loader-libro-pagina"></span>
                <span class="loader-libro-pagina"></span>
                <span class="loader-libro-pagina"></span>
                <span class="loader-libro-tapa"></span>
            </div>
            <div class="loader-barra" aria-hidden="true"><span></span></div>
            <p class="loader-texto">Abriendo tu cuenta…</p>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light biblioteca-nav sticky-top">
        <div class="container">
            <a class="marca-nav" href="<?= RUTA_LANDING ?>">EXTERNOLAB</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuUsuario" aria-controls="menuUsuario" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuUsuario">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="<?= RUTA_LANDING ?>">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#mis-libros">Mis libros</a></li>
                    <li class="nav-item"><a class="nav-link activo" href="#perfil">Mi perfil</a></li>
                    <li class="nav-item"><button class="nav-link btn btn-link" type="button" id="cerrar-sesion">Cerrar sesión</button></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <?php if ($mensaje !== ''): ?><div class="alert aviso-ok" role="status"><?= e($mensaje) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert aviso-error" role="alert"><?= e($error) ?></div><?php endif; ?>

        <section class="perfil-hero mb-4" id="perfil">
            <div class="perfil-avatar-wrap">
                <?php if ($fotoUrl): ?>
                    <img class="perfil-avatar" src="<?= e($fotoUrl) ?>" alt="Foto de perfil de <?= e($perfil['nombre']) ?>">
                <?php else: ?>
                    <div class="perfil-avatar perfil-avatar-vacio" aria-hidden="true"><?= e(strtoupper(substr($perfil['nombre'], 0, 1))) ?></div>
                <?php endif; ?>
            </div>
            <div class="perfil-hero-texto">
                <p class="kicker">Espacio del lector</p>
                <h1 class="cuenta-nombre" id="nombre-visible"><?= e($perfil['nombre']) ?></h1>
                <p class="mb-1"><?= e($perfil['email']) ?></p>
                <p class="perfil-bio mb-0"><?= e($perfil['bio'] ?: 'Todavía no escribiste una bio. Contá qué te gusta leer.') ?></p>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-7">
                <section class="panel-perfil">
                    <h2 class="h4 seccion-titulo">Personalizar perfil</h2>
                    <p class="text-secondary">Cambiá tu nombre, foto, bio y cómo se ve tu espacio.</p>
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="accion" value="perfil">
                        <div class="col-md-8">
                            <label class="form-label" for="nombre">Nombre visible</label>
                            <input class="form-control" id="nombre" name="nombre" maxlength="120" required value="<?= e($perfil['nombre']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="foto">Foto de perfil</label>
                            <input class="form-control" id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <?php if (!empty($perfil['foto'])): ?>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="quitar_foto" id="quitar_foto" value="1">
                                <label class="form-check-label" for="quitar_foto">Quitar foto actual</label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <label class="form-label" for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" maxlength="280" placeholder="Qué géneros te gustan, autores favoritos…"><?= e($perfil['bio'] ?? '') ?></textarea>
                            <div class="form-text"><span id="bio-contador">0</span>/280</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="tema">Tema de la interfaz</label>
                            <select class="form-select" id="tema" name="tema">
                                <?php foreach ($temas as $valor => $etiqueta): ?>
                                    <option value="<?= e($valor) ?>" <?= $temaActual === $valor ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <span class="form-label d-block">Color de acento</span>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <?php foreach ($colores as $color): ?>
                                    <label class="color-swatch" title="<?= e($color) ?>">
                                        <input type="radio" name="color_acento" value="<?= e($color) ?>" <?= strtolower($colorActual) === strtolower($color) ? 'checked' : '' ?>>
                                        <span style="background: <?= e($color) ?>"></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="preview-tema" id="preview-tema">
                                Vista previa · tema <strong id="preview-tema-nombre"><?= e($temas[$temaActual] ?? 'Papel') ?></strong>
                                con acento <span id="preview-acento" style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= e($colorActual) ?>;vertical-align:middle"></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-oscuro" type="submit">Guardar perfil</button>
                        </div>
                    </form>
                </section>

                <section class="panel-perfil mt-4">
                    <h2 class="h5">Cambiar contraseña</h2>
                    <form method="post" class="row g-3" id="form-clave">
                        <input type="hidden" name="accion" value="clave">
                        <div class="col-md-4">
                            <label class="form-label" for="clave_actual">Actual</label>
                            <div class="grupo-clave" data-clave>
                                <div class="campo-clave">
                                    <input class="form-control" id="clave_actual" name="clave_actual" type="password" required autocomplete="current-password">
                                    <button type="button" class="ojo-clave" aria-label="Mostrar contraseña" aria-pressed="false"></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="clave_nueva">Nueva</label>
                            <div class="grupo-clave" data-clave>
                                <div class="campo-clave">
                                    <input class="form-control" id="clave_nueva" name="clave_nueva" type="password" required minlength="8" autocomplete="new-password">
                                    <button type="button" class="ojo-clave" aria-label="Mostrar contraseña" aria-pressed="false"></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="clave_repetir">Repetir</label>
                            <div class="grupo-clave" data-clave>
                                <div class="campo-clave">
                                    <input class="form-control" id="clave_repetir" name="clave_repetir" type="password" required minlength="8" autocomplete="new-password">
                                    <button type="button" class="ojo-clave" aria-label="Mostrar contraseña" aria-pressed="false"></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-linea" type="submit">Actualizar contraseña</button>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="panel-perfil" id="mis-libros">
                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                        <div>
                            <h2 class="h4 seccion-titulo mb-1">Mis libros</h2>
                            <p class="bajada mb-0"><span id="mis-libros-contador">0</span> títulos comprados</p>
                        </div>
                        <a class="btn btn-oscuro btn-sm" href="<?= RUTA_LANDING ?>#catalogo">Catálogo</a>
                    </div>
                    <div class="resumen-biblioteca d-none" id="resumen-biblioteca">
                        <div>Invertido: <strong id="total-invertido">$ 0</strong></div>
                        <div>Última compra: <strong id="ultima-compra">—</strong></div>
                    </div>
                    <div class="row g-3" id="grilla-mis-libros"></div>
                    <div id="mis-libros-vacio" class="vacio-accion">
                        <i class="bi bi-bag-plus" aria-hidden="true"></i>
                        <p class="mb-0">Todavía no hay compras.</p>
                        <a class="btn btn-oscuro btn-sm" href="<?= RUTA_LANDING ?>#catalogo">Ir al catálogo</a>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../../assets/js/loader.js?v=16"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/auth.js"></script>
    <script src="../../assets/js/clave.js"></script>
    <script src="../../assets/js/tienda.js?v=12"></script>
    <script src="../../assets/js/catalogo.js?v=12"></script>
    <script>
        pintarMisLibros();
        const bio = document.getElementById('bio');
        const contador = document.getElementById('bio-contador');
        function contarBio() { if (contador && bio) contador.textContent = String(bio.value.length); }
        if (bio) { bio.addEventListener('input', contarBio); contarBio(); }

        const temas = <?= json_encode($temas, JSON_UNESCAPED_UNICODE) ?>;
        const temaSelect = document.getElementById('tema');
        const previewNombre = document.getElementById('preview-tema-nombre');
        const previewAcento = document.getElementById('preview-acento');

        function aplicarPreview() {
            const tema = temaSelect ? temaSelect.value : 'papel';
            document.body.className = document.body.className.replace(/tema-\w+/g, '').trim() + ' tema-' + tema;
            if (previewNombre) previewNombre.textContent = temas[tema] || tema;
            const color = document.querySelector('input[name="color_acento"]:checked');
            if (color) {
                document.documentElement.style.setProperty('--acento-lector', color.value);
                if (previewAcento) previewAcento.style.background = color.value;
            }
        }
        if (temaSelect) temaSelect.addEventListener('change', aplicarPreview);
        document.querySelectorAll('input[name="color_acento"]').forEach(function (input) {
            input.addEventListener('change', aplicarPreview);
        });

        document.getElementById('cerrar-sesion').addEventListener('click', async function () {
            try { await Auth.salir(); } catch (error) {}
            if (window.Loader) Loader.ir('<?= RUTA_LOGIN ?>', 'Cerrando sesión…');
            else window.location.href = '<?= RUTA_LOGIN ?>';
        });
    </script>
</body>
</html>
