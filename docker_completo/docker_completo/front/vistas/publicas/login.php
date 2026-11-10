<?php
require __DIR__ . '/../../includes/sesion.php';

$actual = usuarioActual();
if ($actual) {
    header('Location: ' . destinoPorRol($actual['rol']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar · EXTERNOLAB</title>
    <link rel="icon" href="../../assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,560;0,9..144,700;1,9..144,560&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/panel.css?v=13">
    <link rel="stylesheet" href="../../assets/css/mobile.css?v=12">
    <link rel="stylesheet" href="../../assets/css/loader.css?v=16">
    <link rel="stylesheet" href="../../assets/css/clave.css">
</head>
<body class="acceso" data-loader-texto="Abriendo ingreso…">
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
            <p class="loader-texto">Abriendo ingreso…</p>
        </div>
    </div>
    <a class="volver" href="./index.html">← Volver al catálogo</a>
    <main class="ficha-acceso">
        <section class="carnet">
            <p class="carnet-kicker">EXTERNOLAB</p>
            <h1>Ingresar</h1>
            <p class="text-secondary">Entrá para ver tu cuenta. El carrito y Mis libros también funcionan desde el catálogo.</p>
            <form id="form-login" novalidate data-loader="off">
                <div class="mb-3">
                    <label class="form-label" for="correo">Correo</label>
                    <input class="form-control form-control-lg" id="correo" name="email" type="email" autocomplete="username" required placeholder="admin@biblioteca.test">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="clave">Contraseña</label>
                    <div class="grupo-clave" data-clave>
                        <div class="campo-clave">
                            <input class="form-control form-control-lg" id="clave" name="password" type="password" autocomplete="current-password" required>
                            <button type="button" class="ojo-clave" aria-label="Mostrar contraseña" aria-pressed="false"></button>
                        </div>
                        <p class="aviso-mayus" hidden><span class="caps-marca" aria-hidden="true">A</span> Bloq Mayús activado</p>
                    </div>
                </div>
                <p id="error-login" class="error-login text-danger small" role="alert"></p>
                <button class="btn btn-oscuro btn-lg w-100" type="submit">Entrar</button>
            </form>
            <div class="cuentas">
                <p class="fw-bold mb-2">Cuentas de prueba</p>
                <div class="cuenta-demo mb-2">
                    <button type="button" class="btn btn-linea btn-sm" data-demo="admin">Usar admin</button>
                    <button type="button" class="btn btn-linea btn-sm" data-demo="lector">Usar lector</button>
                </div>
                <p class="mb-1"><i class="bi bi-shield-lock" aria-hidden="true"></i> Admin: <code>admin@biblioteca.test</code> · <code>Biblioteca2026</code></p>
                <p class="mb-0"><i class="bi bi-person" aria-hidden="true"></i> Usuario: <code>lector@biblioteca.test</code> · <code>Lectura2026</code></p>
            </div>
        </section>
    </main>
    <script src="../../assets/js/loader.js?v=16"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/auth.js"></script>
    <script src="../../assets/js/clave.js"></script>
    <script>
        const demos = {
            admin: { email: 'admin@biblioteca.test', password: 'Biblioteca2026' },
            lector: { email: 'lector@biblioteca.test', password: 'Lectura2026' }
        };
        document.querySelectorAll('[data-demo]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                const datos = demos[boton.dataset.demo];
                if (!datos) return;
                document.getElementById('correo').value = datos.email;
                document.getElementById('clave').value = datos.password;
                document.getElementById('correo').focus();
            });
        });

        document.getElementById('form-login').addEventListener('submit', async function (evento) {
            evento.preventDefault();
            const formulario = evento.currentTarget;
            const error = document.getElementById('error-login');
            const correo = formulario.email.value.trim();
            const clave = formulario.password.value;
            error.textContent = '';
            if (window.Loader) Loader.show('Entrando…');
            try {
                const datos = await Auth.entrar(correo, clave);
                const destino = datos.destino || Auth.destino(datos.usuario.rol);
                if (window.Loader) Loader.ir(destino, 'Entrando…');
                else window.location.href = destino;
            } catch (fallo) {
                if (window.Loader) Loader.hide();
                error.textContent = fallo.message;
            }
        });
    </script>
</body>
</html>
