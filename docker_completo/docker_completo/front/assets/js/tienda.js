/* Practicos3MD — carrito y biblioteca comprada (localStorage) */
const Tienda = {
    CLAVE_CARRITO: 'practicos3md_carrito',
    CLAVE_LIBROS: 'practicos3md_mis_libros',

    leer(clave) {
        try {
            const raw = localStorage.getItem(clave);
            const lista = raw ? JSON.parse(raw) : [];
            return Array.isArray(lista) ? lista : [];
        } catch (error) {
            return [];
        }
    },

    guardar(clave, lista) {
        localStorage.setItem(clave, JSON.stringify(lista));
    },

    carrito() {
        return this.leer(this.CLAVE_CARRITO);
    },

    misLibros() {
        return this.leer(this.CLAVE_LIBROS);
    },

    parsePrecio(valor) {
        if (typeof valor === 'number' && !isNaN(valor)) return valor;
        if (valor == null || valor === '') return 0;
        const limpio = String(valor).replace(/[^\d.,-]/g, '').replace(/\./g, '').replace(',', '.');
        const n = Number(limpio);
        return isNaN(n) ? 0 : n;
    },

    precioDe(producto) {
        if (!producto) return 0;
        if (producto.precio_num != null && producto.precio_num !== '') {
            return this.parsePrecio(producto.precio_num);
        }
        if (producto.precio_oferta != null && producto.precio_oferta !== '') {
            return this.parsePrecio(producto.precio_oferta);
        }
        return this.parsePrecio(producto.precio);
    },

    ficha(producto) {
        let imagen = producto.imagen || '';
        if (imagen.indexOf('/front/') === 0) imagen = imagen.replace(/^\/front\//, '');
        return {
            id: String(producto.id || producto.id_producto || ''),
            titulo: producto.titulo || '',
            autor: producto.firma || producto.autor || producto.editorial || '',
            imagen: imagen,
            tipo: producto.tipo || 'libro',
            precio: this.precioDe(producto),
            categoria: producto.categoria || '',
            categoria_slug: producto.categoria_slug || ''
        };
    },

    tieneLibro(id) {
        const clave = String(id);
        return this.misLibros().some(function (item) { return String(item.id) === clave; });
    },

    enCarrito(id) {
        const clave = String(id);
        return this.carrito().some(function (item) { return String(item.id) === clave; });
    },

    cantidadTotal() {
        return this.carrito().reduce(function (suma, item) {
            return suma + (Number(item.cantidad) || 1);
        }, 0);
    },

    totalCarrito() {
        return this.carrito().reduce(function (suma, item) {
            return suma + (Number(item.precio) || 0) * (Number(item.cantidad) || 1);
        }, 0);
    },

    agregar(producto) {
        const ficha = this.ficha(producto);
        if (!ficha.id) return { ok: false, motivo: 'sin-id' };
        if (this.tieneLibro(ficha.id)) return { ok: false, motivo: 'ya-comprado' };

        const lista = this.carrito();
        const existente = lista.find(function (item) { return String(item.id) === ficha.id; });
        if (existente) {
            existente.cantidad = (Number(existente.cantidad) || 1) + 1;
        } else {
            ficha.cantidad = 1;
            lista.push(ficha);
        }
        this.guardar(this.CLAVE_CARRITO, lista);
        return { ok: true, motivo: 'agregado' };
    },

    setCantidad(id, cantidad) {
        const clave = String(id);
        let lista = this.carrito();
        lista = lista.map(function (item) {
            if (String(item.id) !== clave) return item;
            return Object.assign({}, item, { cantidad: Math.max(1, Number(cantidad) || 1) });
        });
        this.guardar(this.CLAVE_CARRITO, lista);
    },

    quitar(id) {
        const clave = String(id);
        const lista = this.carrito().filter(function (item) { return String(item.id) !== clave; });
        this.guardar(this.CLAVE_CARRITO, lista);
    },

    vaciar() {
        this.guardar(this.CLAVE_CARRITO, []);
    },

    comprar() {
        const carrito = this.carrito();
        if (!carrito.length) return { ok: false, cantidad: 0 };

        const biblioteca = this.misLibros();
        const ahora = new Date().toISOString();
        const total = this.totalCarrito();
        carrito.forEach(function (item) {
            const id = String(item.id);
            if (!biblioteca.some(function (libro) { return String(libro.id) === id; })) {
                biblioteca.push({
                    id: id,
                    titulo: item.titulo,
                    autor: item.autor,
                    imagen: item.imagen,
                    tipo: item.tipo,
                    precio: item.precio,
                    categoria: item.categoria || '',
                    categoria_slug: item.categoria_slug || '',
                    comprado_en: ahora
                });
            }
        });
        this.guardar(this.CLAVE_LIBROS, biblioteca);
        this.vaciar();
        return { ok: true, cantidad: carrito.length, total: total };
    },

    totalInvertido() {
        return this.misLibros().reduce(function (suma, item) {
            return suma + (Number(item.precio) || 0);
        }, 0);
    },

    ultimaCompra() {
        const fechas = this.misLibros()
            .map(function (item) { return item.comprado_en; })
            .filter(Boolean)
            .sort();
        return fechas.length ? fechas[fechas.length - 1] : null;
    }
};

function rutaImagenTienda(ruta) {
    if (!ruta) return '';
    if (ruta.indexOf('http') === 0 || ruta.indexOf('/') === 0) return ruta;
    return '/front/' + ruta;
}

function plataTienda(numero) {
    const valor = Math.round(Number(numero) || 0);
    return '$ ' + String(valor).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
