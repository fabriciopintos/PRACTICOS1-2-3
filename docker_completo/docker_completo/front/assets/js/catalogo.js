/* Practicos3MD — catálogo dinámico, favoritos y helpers */
const etiquetasTipo = {
    libro: 'Libro',
    revista: 'Revista',
    audiolibro: 'Audiolibro'
};

const CLAVE_FAVORITOS = 'practicos3md_favoritos';
const PAGINA_TAMANIO = 12;

let catalogoProductos = [];
let paginaVisible = 1;

function plata(numero) {
    const valor = Math.round(Number(numero) || 0);
    return '$ ' + String(valor).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function rutaImagen(ruta) {
    if (!ruta) return '';
    if (ruta.indexOf('http') === 0 || ruta.indexOf('/') === 0) return ruta;
    return '/front/' + ruta;
}

function leerFavoritos() {
    try {
        const raw = localStorage.getItem(CLAVE_FAVORITOS);
        const lista = raw ? JSON.parse(raw) : [];
        return Array.isArray(lista) ? lista.map(String) : [];
    } catch (error) {
        return [];
    }
}

function guardarFavoritos(lista) {
    localStorage.setItem(CLAVE_FAVORITOS, JSON.stringify(lista));
}

function esFavorito(id) {
    return leerFavoritos().indexOf(String(id)) !== -1;
}

function alternarFavorito(id) {
    const clave = String(id);
    let lista = leerFavoritos();
    const estaba = lista.indexOf(clave) !== -1;
    if (estaba) {
        lista = lista.filter(function (item) { return item !== clave; });
    } else {
        lista.push(clave);
    }
    guardarFavoritos(lista);
    return !estaba;
}

function porcentajeDescuento(producto) {
    const normal = Number(producto.precio) || 0;
    const oferta = Number(producto.precio_oferta) || 0;
    if (!oferta || !normal || oferta >= normal) return 0;
    return Math.round(((normal - oferta) / normal) * 100);
}

function datosFicha(producto) {
    const oferta = producto.precio_oferta;
    const precioVisible = oferta ? plata(oferta) : plata(producto.precio);
    return {
        id: String(producto.id || producto.id_producto || ''),
        estilo: 'cover',
        tipo: producto.tipo,
        titulo: producto.titulo,
        autor: producto.firma || producto.autor || producto.editorial || '',
        categoria: producto.categoria || '',
        categoriaSlug: producto.categoria_slug || '',
        anio: producto.anio ? String(producto.anio) : '',
        precio: precioVisible,
        precioNum: String(oferta || producto.precio || 0),
        antes: oferta ? plata(producto.precio) : '',
        descripcion: producto.descripcion || '',
        imagen: rutaImagen(producto.imagen)
    };
}

function aplicarDatos(nodo, producto) {
    const datos = datosFicha(producto);
    Object.keys(datos).forEach(function (clave) {
        nodo.dataset[clave] = datos[clave];
    });
}

function portadaDe(producto) {
    const imagen = rutaImagen(producto.imagen);
    const foto = document.createElement('img');
    foto.loading = 'lazy';
    foto.decoding = 'async';
    foto.alt = producto.titulo || '';
    foto.src = imagen || ('data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect fill="#d9cfc0" width="100%" height="100%"/></svg>'
    ));
    foto.onerror = function () {
        this.onerror = null;
        this.src = 'data:image/svg+xml,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect fill="#d9cfc0" width="100%" height="100%"/><text x="50%" y="50%" text-anchor="middle" fill="#5d675f" font-family="Georgia" font-size="18">Sin tapa</text></svg>'
        );
    };
    return foto;
}

function tarjeta(producto, oferta) {
    const id = producto.id || producto.id_producto;
    const columna = document.createElement('div');
    columna.className = 'col-12 col-sm-6 col-lg-4 col-xl-3 col-producto';
    columna.dataset.id = String(id);
    columna.dataset.tipo = producto.tipo;
    columna.dataset.categoria = producto.categoria_slug || '';
    columna.dataset.precio = String(producto.precio_oferta || producto.precio || 0);
    columna.dataset.anio = String(producto.anio || 0);
    columna.dataset.oferta = producto.precio_oferta ? '1' : '0';
    columna.dataset.titulo = producto.titulo || '';
    columna.dataset.busqueda = [
        producto.titulo,
        producto.autor,
        producto.editorial,
        producto.categoria,
        etiquetasTipo[producto.tipo]
    ].join(' ');

    const article = document.createElement('article');
    article.className = 'card h-100 catalogo-card';

    if (oferta || producto.precio_oferta) {
        const cinta = document.createElement('span');
        cinta.className = 'cinta-oferta';
        cinta.textContent = 'Oferta';
        article.appendChild(cinta);
    }

    const fav = document.createElement('button');
    fav.type = 'button';
    fav.className = 'btn-fav' + (esFavorito(id) ? ' activo' : '');
    fav.setAttribute('aria-label', esFavorito(id) ? 'Quitar de favoritos' : 'Agregar a favoritos');
    fav.dataset.id = String(id);
    fav.innerHTML = '<i class="bi bi-heart' + (esFavorito(id) ? '-fill' : '') + '" aria-hidden="true"></i>';
    article.appendChild(fav);

    const marco = document.createElement('div');
    marco.className = 'card-portada';
    marco.appendChild(portadaDe(producto));
    const dto = porcentajeDescuento(producto);
    if (dto > 0) {
        const desc = document.createElement('span');
        desc.className = 'cinta-descuento';
        desc.textContent = '-' + dto + '%';
        marco.appendChild(desc);
    }

    const cuerpo = document.createElement('div');
    cuerpo.className = 'card-body d-flex flex-column';

    const tipo = document.createElement('span');
    tipo.className = 'badge rounded-pill badge-tipo badge-' + producto.tipo;
    tipo.textContent = etiquetasTipo[producto.tipo] || producto.tipo;

    const titulo = document.createElement('h3');
    titulo.className = 'card-title h6';
    titulo.textContent = producto.titulo;

    const autor = document.createElement('p');
    autor.className = 'card-autor small';
    autor.textContent = producto.firma || '';

    const texto = document.createElement('p');
    texto.className = 'card-text';
    texto.textContent = producto.descripcion || '';

    const pie = document.createElement('div');
    pie.className = 'mt-auto d-flex justify-content-between align-items-center gap-2 flex-wrap';

    const precio = document.createElement('p');
    precio.className = 'precio mb-0';
    if (producto.precio_oferta) {
        const viejo = document.createElement('span');
        viejo.className = 'precio-viejo';
        viejo.textContent = plata(producto.precio);
        precio.appendChild(viejo);
        precio.appendChild(document.createTextNode(' ' + plata(producto.precio_oferta)));
    } else {
        precio.textContent = plata(producto.precio);
    }

    const acciones = document.createElement('div');
    acciones.className = 'd-flex flex-wrap gap-2';

    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'btn btn-oscuro btn-sm btn-ficha';
    boton.setAttribute('data-bs-toggle', 'modal');
    boton.setAttribute('data-bs-target', '#modalDetalle');
    boton.textContent = 'Ver ficha';
    boton.setAttribute('aria-label', 'Ver ficha de ' + producto.titulo);
    aplicarDatos(boton, producto);

    const carritoBtn = document.createElement('button');
    carritoBtn.type = 'button';
    carritoBtn.className = 'btn btn-linea btn-sm btn-carrito';
    carritoBtn.dataset.id = String(id);
    aplicarDatos(carritoBtn, producto);
    const comprado = typeof Tienda !== 'undefined' && Tienda.tieneLibro(id);
    if (comprado) {
        carritoBtn.textContent = 'En mi biblioteca';
        carritoBtn.disabled = true;
        carritoBtn.classList.add('comprado');
        article.classList.add('ya-comprado');
    } else {
        carritoBtn.textContent = 'Al carrito';
    }

    acciones.append(boton, carritoBtn);
    pie.append(precio, acciones);
    cuerpo.append(tipo, titulo, autor, texto, pie);
    article.append(marco, cuerpo);
    columna.appendChild(article);
    return columna;
}

function tapaEstante(producto, clasesColumna) {
    const columna = document.createElement('div');
    columna.className = clasesColumna || 'col-6 col-md-4 col-lg-3';

    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'cover';
    boton.setAttribute('data-bs-toggle', 'modal');
    boton.setAttribute('data-bs-target', '#modalDetalle');
    boton.setAttribute('aria-label', producto.titulo + ' ' + (producto.firma || ''));
    aplicarDatos(boton, producto);
    boton.appendChild(portadaDe(producto));

    const titulo = document.createElement('span');
    titulo.className = 'cover__titulo';
    titulo.textContent = producto.titulo;
    const autor = document.createElement('span');
    autor.className = 'cover__autor';
    autor.textContent = producto.firma || '';
    boton.append(titulo, autor);

    columna.appendChild(boton);
    return columna;
}

function ordenarProductos(lista, criterio) {
    const copia = lista.slice();
    copia.sort(function (a, b) {
        const ta = (a.titulo || '').toLocaleLowerCase('es');
        const tb = (b.titulo || '').toLocaleLowerCase('es');
        const pa = Number(a.precio_oferta || a.precio || 0);
        const pb = Number(b.precio_oferta || b.precio || 0);
        const aa = Number(a.anio || 0);
        const ab = Number(b.anio || 0);
        if (criterio === 'titulo-desc') return tb.localeCompare(ta, 'es');
        if (criterio === 'precio') return pa - pb;
        if (criterio === 'precio-desc') return pb - pa;
        if (criterio === 'anio-desc') return ab - aa;
        if (criterio === 'oferta') {
            const oa = a.precio_oferta ? 0 : 1;
            const ob = b.precio_oferta ? 0 : 1;
            if (oa !== ob) return oa - ob;
            return ta.localeCompare(tb, 'es');
        }
        return ta.localeCompare(tb, 'es');
    });
    return copia;
}

function filtrarProductos(lista, opciones) {
    const consulta = opciones.consulta || '';
    const tipo = opciones.tipo || 'todos';
    const categoria = opciones.categoria || 'todas';
    const precioMax = opciones.precioMax == null ? Infinity : Number(opciones.precioMax);
    const favoritos = leerFavoritos();

    return lista.filter(function (producto) {
        const id = String(producto.id || producto.id_producto || '');
        let coincideTipo = true;
        if (tipo === 'favoritos') coincideTipo = favoritos.indexOf(id) !== -1;
        else if (tipo === 'oferta') coincideTipo = !!producto.precio_oferta;
        else if (tipo !== 'todos') coincideTipo = producto.tipo === tipo;
        const coincideCategoria = categoria === 'todas' || producto.categoria_slug === categoria;
        const precioActual = Number(producto.precio_oferta || producto.precio || 0);
        const coincidePrecio = !isFinite(precioMax) || precioActual <= precioMax;
        const texto = [
            producto.titulo,
            producto.autor,
            producto.editorial,
            producto.categoria,
            etiquetasTipo[producto.tipo]
        ].join(' ');
        const coincideTexto = !consulta || texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(consulta);
        return coincideTipo && coincideCategoria && coincideTexto && coincidePrecio;
    });
}

async function pintarCatalogo() {
    const grilla = document.getElementById('grilla-catalogo');
    const ofertas = document.getElementById('grilla-ofertas');
    const autor = document.getElementById('estante-autor');
    const aventura = document.getElementById('estante-aventura');
    const nombreAutor = document.getElementById('nombre-autor');
    if (!grilla) return;

    grilla.innerHTML = '';
    for (let i = 0; i < 8; i += 1) {
        const sk = document.createElement('div');
        sk.className = 'col-12 col-sm-6 col-lg-4 col-xl-3';
        sk.innerHTML = '<div class="skeleton-card" aria-hidden="true"></div>';
        grilla.appendChild(sk);
    }

    try {
        const datos = await Api.get('/productos');
        catalogoProductos = (datos.productos || []).map(function (producto) {
            if (!producto.id && producto.id_producto) producto.id = producto.id_producto;
            return producto;
        });
    } catch (error) {
        grilla.innerHTML = '<div class="col-12 text-center text-danger py-4">No se pudo cargar el catálogo. <button type="button" class="btn btn-oscuro btn-sm ms-2" id="reintentar-catalogo">Reintentar</button></div>';
        const reintentar = document.getElementById('reintentar-catalogo');
        if (reintentar) reintentar.addEventListener('click', pintarCatalogo);
        throw error;
    }

    if (ofertas) {
        ofertas.replaceChildren.apply(ofertas, catalogoProductos.filter(function (producto) {
            return producto.precio_oferta;
        }).map(function (producto) {
            return tarjeta(producto, true);
        }));
    }

    if (autor) {
        const destacados = catalogoProductos.filter(function (producto) { return producto.en_autor; });
        autor.replaceChildren.apply(autor, destacados.map(function (producto) {
            return tapaEstante(producto, 'col-4 col-md-4 col-lg-4');
        }));
    }

    if (aventura) {
        aventura.replaceChildren.apply(aventura, catalogoProductos.filter(function (producto) {
            return producto.en_aventura;
        }).map(function (producto) {
            return tapaEstante(producto, 'col-6 col-md-4 col-lg-3');
        }));
    }

    if (nombreAutor) {
        try {
            const destacado = await Api.get('/autor-destacado');
            if (destacado.autor && destacado.autor.nombre) {
                nombreAutor.textContent = destacado.autor.nombre;
            }
        } catch (error) {
            /* el estante igual muestra los títulos */
        }
    }

    llenarSugerencias();
    actualizarEstadisticas();
    pintarRecomendados();
}

function pintarMisLibros() {
    const grilla = document.getElementById('grilla-mis-libros');
    const vacio = document.getElementById('mis-libros-vacio');
    const contador = document.getElementById('mis-libros-contador');
    const resumen = document.getElementById('resumen-biblioteca');
    const totalInv = document.getElementById('total-invertido');
    const ultima = document.getElementById('ultima-compra');
    if (!grilla || typeof Tienda === 'undefined') return;

    const libros = Tienda.misLibros();
    if (contador) contador.textContent = String(libros.length);

    if (resumen) {
        resumen.classList.toggle('d-none', !libros.length);
        if (libros.length) {
            if (totalInv) totalInv.textContent = plata(Tienda.totalInvertido());
            if (ultima) {
                const fecha = Tienda.ultimaCompra();
                ultima.textContent = fecha ? new Date(fecha).toLocaleDateString('es-AR') : '—';
            }
        }
    }

    if (!libros.length) {
        grilla.replaceChildren();
        if (vacio) vacio.classList.remove('d-none');
        return;
    }
    if (vacio) vacio.classList.add('d-none');

    const nodos = libros.map(function (libro) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4 col-lg-3 col-xl-2';

        const card = document.createElement('article');
        card.className = 'card h-100 catalogo-card libro-mio';

        const marco = document.createElement('button');
        marco.type = 'button';
        marco.className = 'card-portada border-0 p-0 w-100';
        marco.setAttribute('data-bs-toggle', 'modal');
        marco.setAttribute('data-bs-target', '#modalDetalle');
        marco.setAttribute('aria-label', 'Ver ficha de ' + (libro.titulo || ''));
        const original = catalogoProductos.find(function (p) {
            return String(p.id || p.id_producto) === String(libro.id);
        });
        if (original) {
            aplicarDatos(marco, original);
        } else {
            Object.assign(marco.dataset, {
                id: String(libro.id),
                titulo: libro.titulo || '',
                autor: libro.autor || '',
                tipo: libro.tipo || 'libro',
                categoria: libro.categoria || '',
                precio: plata(libro.precio),
                precioNum: String(libro.precio || 0),
                imagen: rutaImagen(libro.imagen),
                descripcion: 'Título en tu biblioteca.'
            });
        }
        const img = document.createElement('img');
        img.loading = 'lazy';
        img.alt = libro.titulo || '';
        img.src = rutaImagen(libro.imagen);
        img.onerror = function () {
            this.onerror = null;
            this.src = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect fill="#d9cfc0" width="100%" height="100%"/></svg>');
        };
        marco.appendChild(img);

        const cuerpo = document.createElement('div');
        cuerpo.className = 'card-body p-2';
        const titulo = document.createElement('h3');
        titulo.className = 'card-title h6 mb-1';
        titulo.textContent = libro.titulo;
        const autor = document.createElement('p');
        autor.className = 'card-autor small mb-0';
        autor.textContent = libro.autor || '';
        const meta = document.createElement('p');
        meta.className = 'small text-secondary mb-0 mt-1';
        if (libro.comprado_en) {
            const fecha = new Date(libro.comprado_en);
            meta.textContent = 'Comprado ' + fecha.toLocaleDateString('es-AR');
        }
        cuerpo.append(titulo, autor, meta);
        card.append(marco, cuerpo);
        col.appendChild(card);
        return col;
    });
    grilla.replaceChildren.apply(grilla, nodos);
}

function pintarRecomendados() {
    const bloque = document.getElementById('bloque-recomendados');
    const rail = document.getElementById('rail-recomendados');
    if (!bloque || !rail || !catalogoProductos.length) return;

    const favoritos = leerFavoritos();
    if (!favoritos.length) {
        bloque.classList.add('d-none');
        return;
    }

    const cats = {};
    catalogoProductos.forEach(function (p) {
        const id = String(p.id || p.id_producto);
        if (favoritos.indexOf(id) === -1) return;
        const slug = p.categoria_slug || '';
        if (slug) cats[slug] = (cats[slug] || 0) + 1;
    });
    const topCats = Object.keys(cats).sort(function (a, b) { return cats[b] - cats[a]; }).slice(0, 3);
    if (!topCats.length) {
        bloque.classList.add('d-none');
        return;
    }

    const sugeridos = catalogoProductos.filter(function (p) {
        const id = String(p.id || p.id_producto);
        if (favoritos.indexOf(id) !== -1) return false;
        if (typeof Tienda !== 'undefined' && Tienda.tieneLibro(id)) return false;
        return topCats.indexOf(p.categoria_slug) !== -1;
    }).slice(0, 10);

    if (!sugeridos.length) {
        bloque.classList.add('d-none');
        return;
    }

    bloque.classList.remove('d-none');
    rail.replaceChildren.apply(rail, sugeridos.map(function (producto) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'recomendado-chip';
        btn.setAttribute('data-bs-toggle', 'modal');
        btn.setAttribute('data-bs-target', '#modalDetalle');
        aplicarDatos(btn, producto);
        const img = portadaDe(producto);
        const strong = document.createElement('strong');
        strong.textContent = producto.titulo;
        const span = document.createElement('span');
        span.textContent = producto.firma || producto.categoria || '';
        btn.append(img, strong, span);
        return btn;
    }));
}

function llenarSugerencias() {
    const lista = document.getElementById('sugerencias-busqueda');
    if (!lista) return;
    const unicos = {};
    catalogoProductos.forEach(function (p) {
        if (p.titulo) unicos[p.titulo] = true;
        if (p.autor) unicos[p.autor] = true;
        if (p.firma) unicos[p.firma] = true;
        if (p.categoria) unicos[p.categoria] = true;
    });
    lista.replaceChildren.apply(lista, Object.keys(unicos).slice(0, 80).map(function (texto) {
        const opt = document.createElement('option');
        opt.value = texto;
        return opt;
    }));
}

function actualizarEstadisticas() {
    const total = document.getElementById('stat-total');
    const ofertas = document.getElementById('stat-ofertas');
    const favs = document.getElementById('stat-favs');
    const mios = document.getElementById('stat-mios');
    if (total) total.textContent = String(catalogoProductos.length || 0);
    if (ofertas) {
        ofertas.textContent = String(catalogoProductos.filter(function (p) { return !!p.precio_oferta; }).length);
    }
    if (favs) favs.textContent = String(leerFavoritos().length);
    if (mios && typeof Tienda !== 'undefined') mios.textContent = String(Tienda.misLibros().length);
}

function renderGrillaFiltrada(opciones) {
    const grilla = document.getElementById('grilla-catalogo');
    const vacio = document.getElementById('catalogo-vacio');
    const resumen = document.getElementById('resultado-busqueda');
    const mas = document.getElementById('btn-mostrar-mas');
    if (!grilla) return 0;

    const filtrados = ordenarProductos(
        filtrarProductos(catalogoProductos, opciones),
        opciones.orden || 'titulo'
    );

    const limite = Math.max(1, opciones.pagina || 1) * PAGINA_TAMANIO;
    const visibles = filtrados.slice(0, limite);

    grilla.replaceChildren.apply(grilla, visibles.map(function (producto) {
        return tarjeta(producto, false);
    }));

    if (vacio) vacio.classList.toggle('d-none', filtrados.length !== 0);
    if (mas) {
        const hayMas = filtrados.length > visibles.length;
        mas.classList.toggle('d-none', !hayMas);
        mas.textContent = 'Mostrar más (' + (filtrados.length - visibles.length) + ')';
    }
    if (resumen) {
        resumen.textContent = filtrados.length === 1
            ? '1 título'
            : filtrados.length + ' títulos' + (visibles.length < filtrados.length ? ' · mostrando ' + visibles.length : '');
    }
    return filtrados.length;
}
