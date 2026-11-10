<?php
require __DIR__ . '/../../includes/sesion.php';
exigirAdministrador();

require_once __DIR__ . '/../../../api/autoload.php';
require_once __DIR__ . '/../../../api/config/database.php';
\App\Utils\EnvLoader::load(__DIR__ . '/../../../api/.env');

use App\Models\CategoriaModel;
use App\Models\ProductoModel;

$usuario = usuarioActual();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$avisos = [
    'creado' => 'El título se agregó y ya está en el catálogo público.',
    'editado' => 'Los cambios quedaron guardados y se ven en el catálogo público.',
    'eliminado' => 'El título se eliminó y ya no aparece en el catálogo público.',
];
$mensaje = $avisos[$_GET['aviso'] ?? ''] ?? '';
$error = '';
$errorCatalogo = '';
$productos = [];
$categorias = [];
$autores = [];
$editando = null;

function plata($numero) {
    return '$ ' . number_format((float) $numero, 0, ',', '.');
}

function campo($origen, $clave, $defecto = '') {
    return isset($origen[$clave]) ? trim((string) $origen[$clave]) : $defecto;
}

function guardarTapa($campo) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen.');
    }
    if ($_FILES[$campo]['size'] > 2500000) {
        throw new RuntimeException('La imagen supera los 2,5 MB.');
    }
    $info = @getimagesize($_FILES[$campo]['tmp_name']);
    $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($tipos[$info[2]])) {
        throw new RuntimeException('La imagen tiene que ser JPG, PNG o WebP.');
    }
    $dir = __DIR__ . '/../../assets/portadas/cargas';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('No se pudo crear la carpeta de tapas.');
    }
    $nombre = 'tapa-' . bin2hex(random_bytes(8)) . '.' . $tipos[$info[2]];
    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $dir . '/' . $nombre)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    return 'assets/portadas/cargas/' . $nombre;
}

try {
    $db = (new Database())->getConnection();
    $modelo = new ProductoModel($db);
    $categorias = (new CategoriaModel($db))->listar();
    if ($categorias === false) {
        $categorias = [];
        $errorCatalogo = 'No se pudieron leer las categorías.';
    }
    $stmtAutores = $db->query('SELECT id_autor, nombre FROM autores ORDER BY nombre');
    $autores = $stmtAutores->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || !hash_equals($csrf, $token)) {
            $error = 'El formulario venció. Volvé a enviarlo.';
        } else {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'eliminar') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($modelo->eliminar($id)) {
                    header('Location: ' . RUTA_ADMIN . '?aviso=eliminado');
                    exit;
                }
                $error = 'No se encontró el título para eliminar.';
            } elseif ($accion === 'guardar') {
                $id = (int) ($_POST['id'] ?? 0);
                $titulo = campo($_POST, 'titulo');
                $descripcion = campo($_POST, 'descripcion');
                $tipo = campo($_POST, 'tipo');
                $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
                $autorNuevo = campo($_POST, 'autor_nuevo');
                $idAutor = (int) ($_POST['id_autor'] ?? 0);
                $editorial = campo($_POST, 'editorial');
                $precioTexto = str_replace(',', '.', campo($_POST, 'precio'));
                $ofertaTexto = str_replace(',', '.', campo($_POST, 'precio_oferta'));
                $anioTexto = campo($_POST, 'anio');
                $tipos = ['libro', 'revista', 'audiolibro'];
                $idsCategoria = array_column($categorias, 'id');

                if ($titulo === '' || strlen($titulo) > 160) {
                    $error = 'El título es obligatorio y puede tener hasta 160 caracteres.';
                } elseif ($descripcion === '' || strlen($descripcion) > 400) {
                    $error = 'La descripción es obligatoria y puede tener hasta 400 caracteres.';
                } elseif (!in_array($tipo, $tipos, true)) {
                    $error = 'Elegí si es libro, revista o audiolibro.';
                } elseif (!in_array($idCategoria, $idsCategoria, true)) {
                    $error = 'Elegí una categoría.';
                } elseif (!is_numeric($precioTexto) || (float) $precioTexto < 0) {
                    $error = 'El precio tiene que ser un número mayor o igual a cero.';
                } elseif ($ofertaTexto !== '' && (!is_numeric($ofertaTexto) || (float) $ofertaTexto < 0)) {
                    $error = 'El precio de oferta tiene que ser un número.';
                } elseif ($anioTexto !== '' && !preg_match('/^\d{1,4}$/', $anioTexto)) {
                    $error = 'El año tiene que ser un número.';
                } else {
                    $actual = $id > 0 ? $modelo->obtener($id) : null;
                    if ($id > 0 && !$actual) {
                        $error = 'No se encontró el título para modificar.';
                    } else {
                        $imagen = guardarTapa('imagen');
                        if ($imagen === null) {
                            $imagen = $actual['imagen'] ?? null;
                        }
                        if ($id === 0 && !$imagen) {
                            $error = 'Subí una imagen de la tapa.';
                        } else {
                            if ($autorNuevo !== '') {
                                $idAutor = $modelo->asegurarAutor($autorNuevo);
                            }
                            $modelo->guardar([
                                'tipo' => $tipo,
                                'titulo' => $titulo,
                                'id_autor' => $idAutor > 0 ? $idAutor : null,
                                'id_categoria' => $idCategoria,
                                'precio' => $precioTexto,
                                'precio_oferta' => $ofertaTexto === '' ? null : $ofertaTexto,
                                'anio' => $anioTexto === '' ? null : (int) $anioTexto,
                                'descripcion' => $descripcion,
                                'en_autor' => isset($_POST['en_autor']) ? 1 : 0,
                                'en_aventura' => isset($_POST['en_aventura']) ? 1 : 0,
                                'editorial' => $editorial === '' ? null : $editorial,
                                'imagen' => $imagen,
                                'disponible' => isset($_POST['disponible']) ? 1 : 0,
                            ], $id > 0 ? $id : null);
                            header('Location: ' . RUTA_ADMIN . '?aviso=' . ($id > 0 ? 'editado' : 'creado'));
                            exit;
                        }
                    }
                }
            }
        }
    }

    $lista = $modelo->listar(null, null, false, false);
    $productos = $lista === false ? [] : $lista;
    if ($lista === false) {
        $errorCatalogo = 'No se pudo leer el catálogo.';
    }

    $idEditar = (int) ($_GET['editar'] ?? 0);
    if ($idEditar > 0) {
        foreach ($productos as $producto) {
            if ($producto['id'] === $idEditar) {
                $editando = $producto;
                break;
            }
        }
    }
} catch (Throwable $e) {
    if ($error === '') {
        $error = $e instanceof RuntimeException ? $e->getMessage() : 'No se pudo guardar el título.';
    }
    if (!$productos && $errorCatalogo === '') {
        $errorCatalogo = 'No se pudo conectar con la base.';
    }
}

$conteo = ['libro' => 0, 'revista' => 0, 'audiolibro' => 0];
foreach ($productos as $producto) {
    if (isset($conteo[$producto['tipo']])) {
        $conteo[$producto['tipo']]++;
    }
}

$formulario = [
    'id' => $editando['id'] ?? 0,
    'titulo' => $editando['titulo'] ?? '',
    'descripcion' => $editando['descripcion'] ?? '',
    'tipo' => $editando['tipo'] ?? 'libro',
    'id_categoria' => $editando['id_categoria'] ?? 0,
    'id_autor' => $editando['id_autor'] ?? 0,
    'editorial' => $editando['editorial'] ?? '',
    'precio' => isset($editando['precio']) ? $editando['precio'] : '',
    'precio_oferta' => $editando['precio_oferta'] ?? '',
    'anio' => $editando['anio'] ?? '',
    'disponible' => $editando ? (int) $editando['disponible'] === 1 : true,
    'en_autor' => $editando ? (int) $editando['en_autor'] === 1 : false,
    'en_aventura' => $editando ? (int) $editando['en_aventura'] === 1 : false,
    'imagen' => $editando['imagen'] ?? '',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar' && $error !== '') {
    $formulario['id'] = (int) ($_POST['id'] ?? 0);
    $formulario['titulo'] = campo($_POST, 'titulo');
    $formulario['descripcion'] = campo($_POST, 'descripcion');
    $formulario['tipo'] = campo($_POST, 'tipo', 'libro');
    $formulario['id_categoria'] = (int) ($_POST['id_categoria'] ?? 0);
    $formulario['id_autor'] = (int) ($_POST['id_autor'] ?? 0);
    $formulario['editorial'] = campo($_POST, 'editorial');
    $formulario['precio'] = campo($_POST, 'precio');
    $formulario['precio_oferta'] = campo($_POST, 'precio_oferta');
    $formulario['anio'] = campo($_POST, 'anio');
    $formulario['disponible'] = isset($_POST['disponible']);
    $formulario['en_autor'] = isset($_POST['en_autor']);
    $formulario['en_aventura'] = isset($_POST['en_aventura']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración · EXTERNOLAB</title>
    <link rel="icon" href="../../assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,560;0,9..144,700;1,9..144,560&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/panel.css?v=13">
    <link rel="stylesheet" href="../../assets/css/mobile.css?v=12">
    <link rel="stylesheet" href="../../assets/css/loader.css?v=16">
</head>
<body class="panel" data-loader-texto="Abriendo panel…">
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
            <p class="loader-texto">Abriendo panel…</p>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg techo sticky-top">
        <div class="container">
            <a class="marca navbar-brand" href="<?= RUTA_LANDING ?>">EXTERNOLAB · Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin" aria-controls="menuAdmin" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuAdmin">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-2">
                    <li class="nav-item"><a class="nav-link activo" href="<?= RUTA_ADMIN ?>">Títulos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= RUTA_LANDING ?>">Sitio público</a></li>
                    <li class="nav-item"><a class="nav-link" href="#lista-titulos">Listado</a></li>
                </ul>
                <div class="d-flex flex-wrap align-items-center gap-2 quien">
                    <span class="fw-bold"><?= e($usuario['nombre']) ?></span>
                    <button class="btn btn-linea btn-sm" id="cerrar-sesion" type="button">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4 hoja">
        <p class="kicker">Administración</p>
        <h1 class="h2"><?= $formulario['id'] ? 'Modificar título' : 'Agregar título' ?></h1>
        <div class="stats-admin" aria-label="Resumen del catálogo">
            <div class="stat"><strong><?= count($productos) ?></strong><span>Total</span></div>
            <div class="stat"><strong><?= (int) $conteo['libro'] ?></strong><span>Libros</span></div>
            <div class="stat"><strong><?= (int) $conteo['revista'] ?></strong><span>Revistas</span></div>
            <div class="stat"><strong><?= (int) $conteo['audiolibro'] ?></strong><span>Audiolibros</span></div>
        </div>

        <?php if ($mensaje !== ''): ?><div class="alert aviso-ok" role="status"><?= e($mensaje) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert aviso-error" role="alert"><?= e($error) ?></div><?php endif; ?>

        <form class="row g-3 form-admin mb-4 pb-4 border-bottom" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id" value="<?= (int) $formulario['id'] ?>">

            <div class="col-md-8">
                <label class="form-label" for="titulo">Nombre o título</label>
                <input class="form-control" id="titulo" name="titulo" maxlength="160" required value="<?= e($formulario['titulo']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="tipo">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <?php foreach (['libro' => 'Libro', 'revista' => 'Revista', 'audiolibro' => 'Audiolibro'] as $valor => $etiqueta): ?>
                        <option value="<?= $valor ?>" <?= $formulario['tipo'] === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" maxlength="400" rows="3" required><?= e($formulario['descripcion']) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="id_categoria">Categoría</label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">Elegir</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>" <?= (int) $formulario['id_categoria'] === (int) $categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="id_autor">Autor</label>
                <select class="form-select" id="id_autor" name="id_autor">
                    <option value="0">Sin autor</option>
                    <?php foreach ($autores as $autor): ?>
                        <option value="<?= (int) $autor['id_autor'] ?>" <?= (int) $formulario['id_autor'] === (int) $autor['id_autor'] ? 'selected' : '' ?>><?= e($autor['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="autor_nuevo">Autor nuevo</label>
                <input class="form-control" id="autor_nuevo" name="autor_nuevo" maxlength="120" placeholder="Si no está en la lista">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="editorial">Editorial</label>
                <input class="form-control" id="editorial" name="editorial" maxlength="120" value="<?= e($formulario['editorial']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="precio">Precio</label>
                <input class="form-control" id="precio" name="precio" inputmode="decimal" required value="<?= e($formulario['precio']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="precio_oferta">Precio de oferta</label>
                <input class="form-control" id="precio_oferta" name="precio_oferta" inputmode="decimal" value="<?= e($formulario['precio_oferta']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="anio">Año</label>
                <input class="form-control" id="anio" name="anio" inputmode="numeric" value="<?= e($formulario['anio']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="imagen">Imagen de la tapa</label>
                <input class="form-control" id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
                <?php if ($formulario['imagen']): ?>
                    <img class="mini-tapa previa mt-2" src="/front/<?= e($formulario['imagen']) ?>" alt="">
                <?php endif; ?>
            </div>
            <div class="col-12">
                <div class="d-flex flex-wrap gap-3 checks">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="disponible" id="disponible" <?= $formulario['disponible'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="disponible">Disponible</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="en_autor" id="en_autor" <?= $formulario['en_autor'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="en_autor">Estante del autor destacado</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="en_aventura" id="en_aventura" <?= $formulario['en_aventura'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="en_aventura">Estante de aventuras</label>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                <button class="btn btn-oscuro" type="submit"><?= $formulario['id'] ? 'Guardar cambios' : 'Agregar título' ?></button>
                <?php if ($formulario['id']): ?><a class="link-cancelar" href="<?= RUTA_ADMIN ?>">Cancelar</a><?php endif; ?>
            </div>
        </form>

        <section class="bloque-admin" id="lista-titulos">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
                <div>
                    <h2 class="h4 mb-1">Títulos de la sala</h2>
                    <p class="nota mb-0">Al agregar, modificar o eliminar, el cambio queda en la base y se refleja en la página pública.</p>
                </div>
                <div class="filtro-admin w-100 w-md-auto">
                    <label class="form-label small mb-1" for="buscar-admin">Filtrar listado</label>
                    <input class="form-control" id="buscar-admin" type="search" placeholder="Título, autor, categoría…" autocomplete="off">
                </div>
            </div>
            <?php if ($errorCatalogo !== ''): ?>
                <div class="alert aviso-error"><?= e($errorCatalogo) ?></div>
            <?php else: ?>
                <p class="nota" id="admin-resultados"><?= count($productos) ?> títulos visibles</p>
                <div class="table-responsive">
                    <table class="table align-middle tabla-titulos" id="tabla-titulos">
                        <thead>
                            <tr>
                                <th>Tapa</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Autor o editorial</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <?php
                                $textoFila = strtolower(trim(
                                    ($producto['titulo'] ?? '') . ' ' .
                                    ($producto['tipo'] ?? '') . ' ' .
                                    ($producto['autor'] ?? '') . ' ' .
                                    ($producto['editorial'] ?? '') . ' ' .
                                    ($producto['categoria'] ?? '')
                                ));
                                ?>
                                <tr data-busca="<?= e($textoFila) ?>">
                                    <td>
                                        <?php if (!empty($producto['imagen'])): ?>
                                            <img class="mini-tapa" src="/front/<?= e($producto['imagen']) ?>" alt="" loading="lazy">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($producto['titulo']) ?></td>
                                    <td><span class="tipo tipo-<?= e($producto['tipo']) ?>"><?= e($producto['tipo']) ?></span></td>
                                    <td><?= e($producto['autor'] ?: $producto['editorial']) ?></td>
                                    <td><?= e($producto['categoria']) ?></td>
                                    <td>
                                        <?php if ($producto['precio_oferta']): ?>
                                            <s><?= e(plata($producto['precio'])) ?></s>
                                            <?= e(plata($producto['precio_oferta'])) ?>
                                        <?php else: ?>
                                            <?= e(plata($producto['precio'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($producto['disponible']): ?>
                                            <span class="badge-estado ok">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge-estado no">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="acciones text-nowrap">
                                        <a href="<?= RUTA_ADMIN ?>?editar=<?= (int) $producto['id'] ?>#titulo"><i class="bi bi-pencil" aria-hidden="true"></i> Modificar</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este título del catálogo?');">
                                            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                            <button type="submit"><i class="bi bi-trash" aria-hidden="true"></i> Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../../assets/js/loader.js?v=16"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/auth.js"></script>
    <script>
        document.getElementById('cerrar-sesion').addEventListener('click', async function () {
            try {
                await Auth.salir();
            } catch (error) {}
            if (window.Loader) Loader.ir('<?= RUTA_LOGIN ?>', 'Cerrando sesión…');
            else window.location.href = '<?= RUTA_LOGIN ?>';
        });

        const buscar = document.getElementById('buscar-admin');
        const filas = document.querySelectorAll('#tabla-titulos tbody tr');
        const resultados = document.getElementById('admin-resultados');
        if (buscar) {
            buscar.addEventListener('input', function () {
                const q = buscar.value.trim().toLowerCase();
                let visibles = 0;
                filas.forEach(function (fila) {
                    const ok = !q || (fila.dataset.busca || '').indexOf(q) !== -1;
                    fila.classList.toggle('oculto-filtro', !ok);
                    if (ok) visibles += 1;
                });
                if (resultados) resultados.textContent = visibles + ' títulos visibles';
            });
        }

        <?php if ($formulario['id']): ?>
        const campoTitulo = document.getElementById('titulo');
        if (campoTitulo) campoTitulo.scrollIntoView({ behavior: 'smooth', block: 'center' });
        <?php endif; ?>
    </script>
</body>
</html>
