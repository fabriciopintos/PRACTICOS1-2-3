const nombresCategoria = {
    drama: 'Drama',
    terror: 'Terror',
    amor: 'Amor',
    aventura: 'Aventura',
    fantasia: 'Fantasía',
    ciencia: 'Ciencia',
    cultura: 'Cultura',
    clasicos: 'Clásicos'
};

let tipoActivo = 'todos';
let categoriaActiva = 'todas';
let ordenActivo = 'titulo';
let paginaActiva = 1;
let fichaActualId = '';
let precioMaxActivo = 50000;
const CLAVE_BUSQUEDAS = 'practicos3md_busquedas';
const CLAVE_VISTA = 'practicos3md_vista';

function normalizar(texto) {
    return (texto || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function leerBusquedas() {
    try {
        const raw = localStorage.getItem(CLAVE_BUSQUEDAS);
        const lista = raw ? JSON.parse(raw) : [];
        return Array.isArray(lista) ? lista : [];
    } catch (error) {
        return [];
    }
}

function guardarBusqueda(texto) {
    const limpio = (texto || '').trim();
    if (limpio.length < 2) return;
    const lista = leerBusquedas().filter(function (item) {
        return item.toLowerCase() !== limpio.toLowerCase();
    });
    lista.unshift(limpio);
    localStorage.setItem(CLAVE_BUSQUEDAS, JSON.stringify(lista.slice(0, 6)));
    pintarBusquedasRecientes();
}

function pintarBusquedasRecientes() {
    const caja = document.getElementById('busquedas-recientes');
    if (!caja) return;
    const lista = leerBusquedas();
    if (!lista.length) {
        caja.hidden = true;
        caja.replaceChildren();
        return;
    }
    caja.hidden = false;
    caja.replaceChildren.apply(caja, lista.map(function (texto) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'chip-busqueda';
        btn.textContent = texto;
        btn.addEventListener('click', function () {
            const buscador = document.getElementById('buscador');
            if (buscador) buscador.value = texto;
            aplicarFiltros();
        });
        return btn;
    }));
}

function formatearPrecioRango(valor) {
    const n = Math.round(Number(valor) || 0);
    return 'Hasta $ ' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function avisar(texto) {
    const toast = document.getElementById('aviso');
    if (!toast || typeof bootstrap === 'undefined') return;
    toast.querySelector('.toast-body').textContent = texto;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 2400 }).show();
}

function ocultarLoader() {
    if (window.Loader) Loader.hide();
}

function actualizarBadgeFavoritos() {
    const badge = document.getElementById('badge-favoritos');
    if (!badge || typeof leerFavoritos !== 'function') return;
    const n = leerFavoritos().length;
    badge.textContent = String(n);
    badge.classList.toggle('vacio', n === 0);
    if (typeof actualizarEstadisticas === 'function') actualizarEstadisticas();
    if (typeof pintarRecomendados === 'function') pintarRecomendados();
}

function actualizarBadgesTienda() {
    if (typeof Tienda === 'undefined') return;
    const badgeCarrito = document.getElementById('badge-carrito');
    const badgeLibros = document.getElementById('badge-mis-libros');
    const cant = Tienda.cantidadTotal();
    const mios = Tienda.misLibros().length;
    if (badgeCarrito) {
        badgeCarrito.textContent = String(cant);
        badgeCarrito.classList.toggle('vacio', cant === 0);
    }
    if (badgeLibros) {
        badgeLibros.textContent = String(mios);
        badgeLibros.classList.toggle('vacio', mios === 0);
    }
    if (typeof actualizarEstadisticas === 'function') actualizarEstadisticas();
}

function animarBadgeCarrito() {
    const badge = document.getElementById('badge-carrito');
    if (!badge) return;
    badge.classList.remove('rebote');
    void badge.offsetWidth;
    badge.classList.add('rebote');
}

function pintarCarrito() {
    if (typeof Tienda === 'undefined') return;
    const lista = document.getElementById('lista-carrito');
    const vacio = document.getElementById('carrito-vacio');
    const total = document.getElementById('total-carrito');
    const comprar = document.getElementById('btn-comprar');
    const vaciar = document.getElementById('btn-vaciar-carrito');
    if (!lista) return;

    const items = Tienda.carrito();
    actualizarBadgesTienda();
    if (total) total.textContent = typeof plataTienda === 'function' ? plataTienda(Tienda.totalCarrito()) : ('$ ' + Tienda.totalCarrito());
    if (comprar) comprar.disabled = items.length === 0;
    if (vaciar) vaciar.disabled = items.length === 0;

    if (!items.length) {
        lista.replaceChildren();
        if (vacio) vacio.classList.remove('d-none');
        return;
    }
    if (vacio) vacio.classList.add('d-none');

    lista.replaceChildren.apply(lista, items.map(function (item) {
        const fila = document.createElement('article');
        fila.className = 'item-carrito';

        const img = document.createElement('img');
        img.src = typeof rutaImagenTienda === 'function' ? rutaImagenTienda(item.imagen) : item.imagen;
        img.alt = '';
        img.className = 'item-carrito-tapa';

        const info = document.createElement('div');
        info.className = 'item-carrito-info';
        const titulo = document.createElement('h3');
        titulo.className = 'h6 mb-1';
        titulo.textContent = item.titulo;
        const meta = document.createElement('p');
        meta.className = 'small text-secondary mb-2';
        meta.textContent = (item.autor || '') + ' · ' + (typeof plataTienda === 'function' ? plataTienda(item.precio) : item.precio);

        const controles = document.createElement('div');
        controles.className = 'd-flex align-items-center gap-2';
        const menos = document.createElement('button');
        menos.type = 'button';
        menos.className = 'btn btn-sm btn-linea btn-qty';
        menos.dataset.id = item.id;
        menos.dataset.accion = 'menos';
        menos.textContent = '−';
        const cant = document.createElement('span');
        cant.className = 'fw-bold';
        cant.textContent = String(item.cantidad || 1);
        const mas = document.createElement('button');
        mas.type = 'button';
        mas.className = 'btn btn-sm btn-linea btn-qty';
        mas.dataset.id = item.id;
        mas.dataset.accion = 'mas';
        mas.textContent = '+';
        const quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'btn btn-sm btn-link text-danger ms-auto';
        quitar.dataset.id = item.id;
        quitar.dataset.accion = 'quitar';
        quitar.textContent = 'Quitar';

        controles.append(menos, cant, mas, quitar);
        info.append(titulo, meta, controles);
        fila.append(img, info);
        return fila;
    }));
}

function refrescarTiendaUI() {
    pintarCarrito();
    if (typeof pintarMisLibros === 'function') pintarMisLibros();
    actualizarBadgesTienda();
    if (typeof renderGrillaFiltrada === 'function') {
        renderGrillaFiltrada(opcionesFiltro());
    }
}

function agregarAlCarritoDesdeNodo(nodo) {
    if (!nodo || typeof Tienda === 'undefined') return;
    const id = nodo.dataset.id;
    const original = (typeof catalogoProductos !== 'undefined' ? catalogoProductos : []).find(function (p) {
        return String(p.id || p.id_producto) === String(id);
    });
    const producto = original || {
        id: id,
        titulo: nodo.dataset.titulo,
        autor: nodo.dataset.autor,
        firma: nodo.dataset.autor,
        imagen: (nodo.dataset.imagen || '').replace(/^\/front\//, ''),
        tipo: nodo.dataset.tipo,
        categoria: nodo.dataset.categoria,
        categoria_slug: nodo.dataset.categoriaSlug || '',
        precio_num: nodo.dataset.precioNum,
        precio: nodo.dataset.precioNum || 0
    };
    const resultado = Tienda.agregar(producto);
    if (!resultado.ok && resultado.motivo === 'ya-comprado') {
        avisar('Ese título ya está en Mis libros.');
        return;
    }
    refrescarTiendaUI();
    animarBadgeCarrito();
    avisar('Agregado al carrito.');
}

function opcionesFiltro() {
    const buscador = document.getElementById('buscador');
    return {
        consulta: normalizar(buscador ? buscador.value.trim() : ''),
        tipo: tipoActivo,
        categoria: categoriaActiva,
        orden: ordenActivo,
        pagina: paginaActiva,
        precioMax: precioMaxActivo
    };
}

function hayFiltrosActivos() {
    const buscador = document.getElementById('buscador');
    const texto = buscador ? buscador.value.trim() : '';
    return texto !== '' || tipoActivo !== 'todos' || categoriaActiva !== 'todas' || ordenActivo !== 'titulo' || precioMaxActivo < 50000;
}

function sincronizarFiltros() {
    document.querySelectorAll('.filtro').forEach(function (boton) {
        const activo = boton.dataset.filtro === tipoActivo;
        boton.classList.toggle('activo', activo);
        boton.setAttribute('aria-pressed', activo ? 'true' : 'false');
    });

    document.querySelectorAll('[data-ir-categoria]').forEach(function (enlace) {
        enlace.classList.toggle('activa', enlace.dataset.irCategoria === categoriaActiva);
    });

    const selectCategoria = document.getElementById('filtro-categoria');
    if (selectCategoria && selectCategoria.value !== categoriaActiva) {
        selectCategoria.value = categoriaActiva;
    }

    const banner = document.getElementById('banner-categoria');
    const nombre = document.getElementById('nombre-categoria');
    if (banner && nombre) {
        if (categoriaActiva === 'todas') {
            banner.classList.add('d-none');
            banner.classList.remove('d-flex');
        } else {
            nombre.textContent = nombresCategoria[categoriaActiva] || categoriaActiva;
            banner.classList.remove('d-none');
            banner.classList.add('d-flex');
        }
    }

    const limpiar = document.getElementById('btn-limpiar-filtros');
    if (limpiar) limpiar.classList.toggle('d-none', !hayFiltrosActivos());
}

function aplicarFiltros(resetPagina) {
    if (resetPagina !== false) paginaActiva = 1;
    sincronizarFiltros();
    if (typeof renderGrillaFiltrada === 'function') {
        renderGrillaFiltrada(opcionesFiltro());
    }
    actualizarBadgeFavoritos();
}

function limpiarFiltros() {
    const buscador = document.getElementById('buscador');
    const orden = document.getElementById('orden-catalogo');
    const rango = document.getElementById('filtro-precio');
    const rangoVal = document.getElementById('filtro-precio-val');
    if (buscador) buscador.value = '';
    if (orden) orden.value = 'titulo';
    if (rango) rango.value = '50000';
    if (rangoVal) rangoVal.textContent = formatearPrecioRango(50000);
    tipoActivo = 'todos';
    categoriaActiva = 'todas';
    ordenActivo = 'titulo';
    precioMaxActivo = 50000;
    paginaActiva = 1;
    aplicarFiltros();
    avisar('Filtros reiniciados.');
}

function aplicarVista(modo) {
    const catalogo = document.getElementById('catalogo');
    const btnGrilla = document.getElementById('vista-grilla');
    const btnLista = document.getElementById('vista-lista');
    if (!catalogo) return;
    const lista = modo === 'lista';
    catalogo.classList.toggle('vista-lista', lista);
    if (btnGrilla) {
        btnGrilla.classList.toggle('activo', !lista);
        btnGrilla.setAttribute('aria-pressed', lista ? 'false' : 'true');
    }
    if (btnLista) {
        btnLista.classList.toggle('activo', lista);
        btnLista.setAttribute('aria-pressed', lista ? 'true' : 'false');
    }
    try { localStorage.setItem(CLAVE_VISTA, lista ? 'lista' : 'grilla'); } catch (error) {}
}

function actualizarProgresoScroll() {
    const barra = document.getElementById('progreso-scroll');
    if (!barra) return;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    const pct = max > 0 ? (window.scrollY / max) * 100 : 0;
    barra.style.width = pct + '%';
}

function marcarSeccionActiva() {
    const ids = ['inicio', 'categorias', 'ofertas', 'catalogo', 'mis-libros'];
    let actual = 'inicio';
    ids.forEach(function (id) {
        const nodo = document.getElementById(id);
        if (!nodo) return;
        const top = nodo.getBoundingClientRect().top;
        if (top <= 120) actual = id;
    });
    document.querySelectorAll('#menuPrincipal a.nav-link[href^="#"]').forEach(function (enlace) {
        const href = enlace.getAttribute('href') || '';
        enlace.classList.toggle('seccion-activa', href === '#' + actual);
    });
}

function actualizarBotonesFavorito(id) {
    const activo = typeof esFavorito === 'function' && esFavorito(id);
    document.querySelectorAll('.btn-fav[data-id="' + id + '"]').forEach(function (boton) {
        boton.classList.toggle('activo', activo);
        boton.setAttribute('aria-label', activo ? 'Quitar de favoritos' : 'Agregar a favoritos');
        boton.innerHTML = '<i class="bi bi-heart' + (activo ? '-fill' : '') + '" aria-hidden="true"></i>';
    });
    const detalle = document.getElementById('btn-fav-detalle');
    if (detalle && String(fichaActualId) === String(id)) {
        detalle.textContent = activo ? 'Quitar de favoritos' : 'Agregar a favoritos';
        detalle.classList.toggle('activo', activo);
    }
    actualizarBadgeFavoritos();
}

async function refrescarSesion() {
    const boton = document.getElementById('btn-login');
    const texto = document.getElementById('texto-sesion');
    const salir = document.getElementById('btn-salir');
    if (!boton) return;

    let usuario = null;
    try {
        const datos = await Auth.sesion();
        usuario = datos.usuario;
    } catch (error) {
        usuario = null;
    }

    const etiqueta = document.getElementById('texto-btn-login');
    const avatar = document.getElementById('nav-avatar');

    if (usuario) {
        const label = usuario.rol === 'administrador' ? 'Panel' : 'Mi cuenta';
        if (etiqueta) etiqueta.textContent = label;
        else boton.textContent = label;
        boton.removeAttribute('data-bs-toggle');
        boton.removeAttribute('data-bs-target');
        boton.dataset.cuenta = usuario.rol;
        boton.dataset.href = Auth.destino(usuario.rol);
        if (texto) {
            texto.textContent = usuario.nombre + ' · ' + (usuario.rol === 'administrador' ? 'Administrador' : 'Usuario');
        }
        if (avatar) {
            if (usuario.foto) {
                avatar.src = usuario.foto.indexOf('/') === 0 ? usuario.foto : ('/front/' + usuario.foto);
                avatar.alt = usuario.nombre || '';
                avatar.classList.remove('d-none');
            } else {
                avatar.classList.add('d-none');
                avatar.removeAttribute('src');
            }
        }
        if (usuario.rol !== 'administrador') {
            if (usuario.tema) document.body.dataset.temaLector = usuario.tema;
            if (usuario.color_acento) document.documentElement.style.setProperty('--acento-lector', usuario.color_acento);
        }
        if (salir) salir.classList.remove('d-none');
    } else {
        if (etiqueta) etiqueta.textContent = 'Login';
        else boton.textContent = 'Login';
        boton.setAttribute('data-bs-toggle', 'modal');
        boton.setAttribute('data-bs-target', '#modalLogin');
        delete boton.dataset.cuenta;
        delete boton.dataset.href;
        if (avatar) {
            avatar.classList.add('d-none');
            avatar.removeAttribute('src');
        }
        document.body.removeAttribute('data-tema-lector');
        document.documentElement.style.removeProperty('--acento-lector');
        if (texto) texto.textContent = 'Todavía no iniciaste sesión.';
        if (salir) salir.classList.add('d-none');
    }
}

async function enviarLogin(formulario) {
    const error = formulario.querySelector('.error-login') || document.getElementById('error-login');
    const correo = formulario.querySelector('[name="email"]').value.trim();
    const clave = formulario.querySelector('[name="password"]').value;
    if (error) error.textContent = '';

    try {
        const datos = await Auth.entrar(correo, clave);
        const destino = datos.destino || Auth.destino(datos.usuario.rol);
        if (window.Loader) Loader.ir(destino, 'Entrando…');
        else window.location.href = destino;
    } catch (fallo) {
        if (window.Loader) Loader.hide();
        if (error) error.textContent = fallo.message;
    }
}

function completarDetalle(origen) {
    const titulo = origen.dataset.titulo || 'Ficha';
    fichaActualId = origen.dataset.id || '';
    document.getElementById('detalleTitulo').textContent = titulo;
    document.getElementById('detalle-autor').textContent = origen.dataset.autor || '';
    document.getElementById('detalle-categoria').textContent = origen.dataset.categoria || '';
    document.getElementById('detalle-anio').textContent = origen.dataset.anio ? 'Año: ' + origen.dataset.anio : '';
    document.getElementById('detalle-descripcion').textContent = origen.dataset.descripcion || '';

    const tipo = origen.dataset.tipo || '';
    const badge = document.getElementById('detalle-tipo');
    badge.className = 'badge rounded-pill badge-tipo badge-' + tipo;
    badge.textContent = (typeof etiquetasTipo !== 'undefined' && etiquetasTipo[tipo]) || tipo;

    const precio = document.getElementById('detalle-precio');
    precio.replaceChildren();
    if (origen.dataset.antes) {
        const anterior = document.createElement('span');
        anterior.className = 'precio-viejo';
        anterior.textContent = origen.dataset.antes;
        precio.append(anterior, document.createTextNode(' ' + (origen.dataset.precio || '')));
    } else {
        precio.textContent = origen.dataset.precio || '';
    }

    const portada = document.getElementById('detalle-portada');
    portada.className = 'cover cover-modal';
    portada.style.backgroundImage = '';
    portada.replaceChildren();
    if (origen.dataset.imagen) {
        const foto = document.createElement('img');
        foto.src = origen.dataset.imagen;
        foto.alt = titulo;
        portada.appendChild(foto);
    }

    const fav = document.getElementById('btn-fav-detalle');
    if (fav) {
        const activo = fichaActualId && typeof esFavorito === 'function' && esFavorito(fichaActualId);
        fav.textContent = activo ? 'Quitar de favoritos' : 'Agregar a favoritos';
        fav.classList.toggle('activo', !!activo);
        fav.disabled = !fichaActualId;
    }

    const carritoDetalle = document.getElementById('btn-carrito-detalle');
    if (carritoDetalle) {
        const comprado = fichaActualId && typeof Tienda !== 'undefined' && Tienda.tieneLibro(fichaActualId);
        carritoDetalle.disabled = !fichaActualId || comprado;
        carritoDetalle.textContent = comprado ? 'Ya está en Mis libros' : 'Agregar al carrito';
        carritoDetalle.dataset.id = fichaActualId;
        Object.keys(origen.dataset).forEach(function (clave) {
            carritoDetalle.dataset[clave] = origen.dataset[clave];
        });
    }
}

function cerrarMenu() {
    const menu = document.getElementById('menuPrincipal');
    if (!menu || !menu.classList.contains('show') || typeof bootstrap === 'undefined') return;
    bootstrap.Collapse.getOrCreateInstance(menu).hide();
}

async function marcarEstado() {
    const nodo = document.getElementById('estado-api');
    if (!nodo || typeof Api === 'undefined') return;
    try {
        const datos = await Api.get('/estado');
        if (datos && datos.status === 'ok' && typeof datos.cantidad_productos === 'number') {
            nodo.textContent = 'Base de datos en línea · ' + datos.cantidad_productos + ' títulos';
        }
    } catch (error) {
        nodo.textContent = 'Catálogo EXTERNOLAB.';
    }
}

function manejarClicCatalogo(evento) {
    const fav = evento.target.closest('.btn-fav');
    if (fav && typeof alternarFavorito === 'function') {
        evento.preventDefault();
        evento.stopPropagation();
        const id = fav.dataset.id;
        const agregado = alternarFavorito(id);
        actualizarBotonesFavorito(id);
        avisar(agregado ? 'Guardado en favoritos.' : 'Sacado de favoritos.');
        if (tipoActivo === 'favoritos') aplicarFiltros(false);
        return;
    }

    const carrito = evento.target.closest('.btn-carrito');
    if (carrito) {
        evento.preventDefault();
        evento.stopPropagation();
        agregarAlCarritoDesdeNodo(carrito);
    }
}

async function iniciarPagina() {
    actualizarBadgeFavoritos();
    actualizarBadgesTienda();
    pintarBusquedasRecientes();
    refrescarSesion();

    try {
        const vistaGuardada = localStorage.getItem(CLAVE_VISTA) || 'grilla';
        aplicarVista(vistaGuardada);
    } catch (error) {
        aplicarVista('grilla');
    }

    try {
        if (typeof pintarCatalogo === 'function') {
            await pintarCatalogo();
        }
        aplicarFiltros();
        refrescarTiendaUI();
        await marcarEstado();
    } catch (error) {
        avisar('No se pudo cargar el catálogo.');
    } finally {
        ocultarLoader();
    }

    const buscador = document.getElementById('buscador');
    const formBusqueda = document.getElementById('form-busqueda');
    if (formBusqueda) {
        formBusqueda.addEventListener('submit', function (evento) {
            evento.preventDefault();
            if (buscador) guardarBusqueda(buscador.value);
            aplicarFiltros();
            document.getElementById('catalogo')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    if (buscador) {
        let espera = null;
        buscador.addEventListener('input', function () {
            window.clearTimeout(espera);
            espera = window.setTimeout(aplicarFiltros, 180);
        });
        buscador.addEventListener('change', function () {
            guardarBusqueda(buscador.value);
        });
        buscador.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                buscador.value = '';
                aplicarFiltros();
            }
        });
    }

    const rangoPrecio = document.getElementById('filtro-precio');
    const rangoVal = document.getElementById('filtro-precio-val');
    if (rangoPrecio) {
        precioMaxActivo = Number(rangoPrecio.value) || 50000;
        if (rangoVal) rangoVal.textContent = formatearPrecioRango(precioMaxActivo);
        rangoPrecio.addEventListener('input', function () {
            precioMaxActivo = Number(rangoPrecio.value) || 50000;
            if (rangoVal) rangoVal.textContent = formatearPrecioRango(precioMaxActivo);
            aplicarFiltros();
        });
    }

    const vistaGrilla = document.getElementById('vista-grilla');
    const vistaLista = document.getElementById('vista-lista');
    if (vistaGrilla) vistaGrilla.addEventListener('click', function () { aplicarVista('grilla'); });
    if (vistaLista) vistaLista.addEventListener('click', function () { aplicarVista('lista'); });

    const vacioLimpiar = document.getElementById('btn-vacio-limpiar');
    if (vacioLimpiar) vacioLimpiar.addEventListener('click', limpiarFiltros);

    document.addEventListener('keydown', function (evento) {
        if (evento.key !== '/' || evento.ctrlKey || evento.metaKey || evento.altKey) return;
        const tag = (evento.target && evento.target.tagName) || '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || evento.target.isContentEditable) return;
        if (!buscador) return;
        evento.preventDefault();
        document.getElementById('catalogo')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(function () { buscador.focus(); }, 200);
    });

    window.addEventListener('scroll', function () {
        actualizarProgresoScroll();
        marcarSeccionActiva();
    }, { passive: true });
    actualizarProgresoScroll();
    marcarSeccionActiva();

    const limpiarBusqueda = document.getElementById('limpiar-busqueda');
    if (limpiarBusqueda) {
        limpiarBusqueda.addEventListener('click', function () {
            if (buscador) buscador.value = '';
            aplicarFiltros();
            buscador?.focus();
        });
    }

    const filtroCategoria = document.getElementById('filtro-categoria');
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', function () {
            categoriaActiva = filtroCategoria.value || 'todas';
            aplicarFiltros();
        });
    }

    const orden = document.getElementById('orden-catalogo');
    if (orden) {
        orden.addEventListener('change', function () {
            ordenActivo = orden.value || 'titulo';
            aplicarFiltros();
        });
    }

    document.querySelectorAll('.filtro').forEach(function (boton) {
        boton.addEventListener('click', function () {
            tipoActivo = boton.dataset.filtro;
            aplicarFiltros();
        });
    });

    document.querySelectorAll('[data-ir-categoria]').forEach(function (enlace) {
        enlace.addEventListener('click', function () {
            categoriaActiva = enlace.dataset.irCategoria;
            tipoActivo = 'todos';
            aplicarFiltros();
            cerrarMenu();
            document.getElementById('catalogo')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const quitar = document.getElementById('quitar-categoria');
    if (quitar) {
        quitar.addEventListener('click', function () {
            categoriaActiva = 'todas';
            aplicarFiltros();
        });
    }

    const limpiarFiltrosBtn = document.getElementById('btn-limpiar-filtros');
    if (limpiarFiltrosBtn) limpiarFiltrosBtn.addEventListener('click', limpiarFiltros);

    const mostrarMas = document.getElementById('btn-mostrar-mas');
    if (mostrarMas) {
        mostrarMas.addEventListener('click', function () {
            paginaActiva += 1;
            if (typeof renderGrillaFiltrada === 'function') {
                renderGrillaFiltrada(opcionesFiltro());
            }
        });
    }

    const irBusqueda = document.getElementById('ir-busqueda');
    if (irBusqueda && buscador) {
        irBusqueda.addEventListener('click', function () {
            window.setTimeout(function () { buscador.focus(); }, 280);
            cerrarMenu();
        });
    }

    const irFavoritos = document.getElementById('ir-favoritos');
    if (irFavoritos) {
        irFavoritos.addEventListener('click', function (evento) {
            evento.preventDefault();
            tipoActivo = 'favoritos';
            aplicarFiltros();
            cerrarMenu();
            document.getElementById('catalogo')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    document.querySelectorAll('#menuPrincipal .nav-link').forEach(function (enlace) {
        enlace.addEventListener('click', cerrarMenu);
    });

    const grilla = document.getElementById('grilla-catalogo');
    if (grilla) grilla.addEventListener('click', manejarClicCatalogo);
    const ofertas = document.getElementById('grilla-ofertas');
    if (ofertas) ofertas.addEventListener('click', manejarClicCatalogo);

    const modalDetalle = document.getElementById('modalDetalle');
    if (modalDetalle) {
        modalDetalle.addEventListener('show.bs.modal', function (evento) {
            if (evento.relatedTarget) completarDetalle(evento.relatedTarget);
        });
    }

    const favDetalle = document.getElementById('btn-fav-detalle');
    if (favDetalle) {
        favDetalle.addEventListener('click', function () {
            if (!fichaActualId || typeof alternarFavorito !== 'function') return;
            const agregado = alternarFavorito(fichaActualId);
            actualizarBotonesFavorito(fichaActualId);
            avisar(agregado ? 'Guardado en favoritos.' : 'Sacado de favoritos.');
            if (tipoActivo === 'favoritos') aplicarFiltros(false);
        });
    }

    const carritoDetalle = document.getElementById('btn-carrito-detalle');
    if (carritoDetalle) {
        carritoDetalle.addEventListener('click', function () {
            agregarAlCarritoDesdeNodo(carritoDetalle);
        });
    }

    const listaCarrito = document.getElementById('lista-carrito');
    if (listaCarrito) {
        listaCarrito.addEventListener('click', function (evento) {
            const boton = evento.target.closest('[data-accion]');
            if (!boton || typeof Tienda === 'undefined') return;
            const id = boton.dataset.id;
            const accion = boton.dataset.accion;
            const item = Tienda.carrito().find(function (x) { return String(x.id) === String(id); });
            if (accion === 'quitar') Tienda.quitar(id);
            if (accion === 'mas' && item) Tienda.setCantidad(id, (Number(item.cantidad) || 1) + 1);
            if (accion === 'menos' && item) {
                const nueva = (Number(item.cantidad) || 1) - 1;
                if (nueva < 1) Tienda.quitar(id);
                else Tienda.setCantidad(id, nueva);
            }
            pintarCarrito();
        });
    }

    const vaciarCarrito = document.getElementById('btn-vaciar-carrito');
    if (vaciarCarrito) {
        vaciarCarrito.addEventListener('click', function () {
            if (typeof Tienda === 'undefined') return;
            Tienda.vaciar();
            pintarCarrito();
            avisar('Carrito vacío.');
        });
    }

    const comprar = document.getElementById('btn-comprar');
    if (comprar) {
        comprar.addEventListener('click', function () {
            if (typeof Tienda === 'undefined') return;
            const items = Tienda.carrito();
            if (!items.length) {
                avisar('El carrito está vacío.');
                return;
            }
            const cant = document.getElementById('confirmar-cantidad');
            const total = document.getElementById('confirmar-total');
            if (cant) cant.textContent = String(items.length);
            if (total) total.textContent = typeof plataTienda === 'function' ? plataTienda(Tienda.totalCarrito()) : ('$ ' + Tienda.totalCarrito());
            const modal = document.getElementById('modalConfirmar');
            if (modal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    }

    const confirmarCompra = document.getElementById('btn-confirmar-compra');
    if (confirmarCompra) {
        confirmarCompra.addEventListener('click', function () {
            if (typeof Tienda === 'undefined') return;
            const resultado = Tienda.comprar();
            if (!resultado.ok) {
                avisar('El carrito está vacío.');
                return;
            }
            refrescarTiendaUI();
            avisar('Compra lista · ' + resultado.cantidad + (resultado.cantidad === 1 ? ' título' : ' títulos') + ' en Mis libros.');
            const modal = document.getElementById('modalConfirmar');
            if (modal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modal).hide();
            }
            const panel = document.getElementById('panelCarrito');
            if (panel && typeof bootstrap !== 'undefined') {
                bootstrap.Offcanvas.getOrCreateInstance(panel).hide();
            }
            document.getElementById('mis-libros')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    const copiar = document.getElementById('btn-copiar-titulo');
    if (copiar) {
        copiar.addEventListener('click', async function () {
            const titulo = document.getElementById('detalleTitulo')?.textContent || '';
            try {
                await navigator.clipboard.writeText(titulo);
                avisar('Título copiado.');
            } catch (error) {
                avisar('No se pudo copiar el título.');
            }
        });
    }

    const compartir = document.getElementById('btn-compartir');
    if (compartir) {
        compartir.addEventListener('click', async function () {
            const titulo = document.getElementById('detalleTitulo')?.textContent || 'Título';
            const autor = document.getElementById('detalle-autor')?.textContent || '';
            const texto = autor ? (titulo + ' — ' + autor) : titulo;
            const url = window.location.origin + window.location.pathname + '#catalogo';
            try {
                if (navigator.share) {
                    await navigator.share({ title: titulo, text: texto, url: url });
                    avisar('Compartido.');
                } else {
                    await navigator.clipboard.writeText(texto + '\n' + url);
                    avisar('Enlace copiado para compartir.');
                }
            } catch (error) {
                if (error && error.name === 'AbortError') return;
                avisar('No se pudo compartir.');
            }
        });
    }

    const botonLogin = document.getElementById('btn-login');
    if (botonLogin) {
        botonLogin.addEventListener('click', function (evento) {
            if (botonLogin.dataset.href) {
                evento.preventDefault();
                if (window.Loader) Loader.ir(botonLogin.dataset.href, 'Abriendo tu espacio…');
                else window.location.href = botonLogin.dataset.href;
            }
        });
    }

    const formLogin = document.getElementById('form-login');
    if (formLogin) {
        formLogin.addEventListener('submit', function (evento) {
            evento.preventDefault();
            enviarLogin(formLogin);
        });
    }

    const demos = {
        admin: { email: 'admin@biblioteca.test', password: 'Biblioteca2026' },
        lector: { email: 'lector@biblioteca.test', password: 'Lectura2026' }
    };
    document.querySelectorAll('[data-demo]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const datos = demos[boton.dataset.demo];
            if (!datos || !formLogin) return;
            const correo = formLogin.querySelector('[name="email"]');
            const clave = formLogin.querySelector('[name="password"]');
            if (correo) correo.value = datos.email;
            if (clave) clave.value = datos.password;
            if (correo) correo.focus();
        });
    });

    const salir = document.getElementById('btn-salir');
    if (salir) {
        salir.addEventListener('click', async function () {
            try {
                await Auth.salir();
            } catch (error) {
                /* la sesión local se limpia igual */
            }
            if (window.Loader) Loader.ir('/front/vistas/publicas/login.php', 'Cerrando sesión…');
            else window.location.href = '/front/vistas/publicas/login.php';
        });
    }

    const subir = document.getElementById('btn-subir');
    if (subir) {
        window.addEventListener('scroll', function () {
            subir.classList.toggle('d-none', window.scrollY < 480);
        }, { passive: true });
        subir.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarPagina);
} else {
    iniciarPagina();
}
