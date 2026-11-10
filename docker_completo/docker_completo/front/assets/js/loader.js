/* Practicos3MD — loader al entrar y al cambiar de vista */
(function () {
    const TEXTO_DEFAULT = 'Cargando…';

    function nodo() {
        return document.getElementById('loader-pagina');
    }

    function markupInterno(texto) {
        return (
            '<div class="loader-caja">' +
            '<p class="loader-marca">EXTERNOLAB</p>' +
            '<div class="loader-libro" aria-hidden="true">' +
            '<span class="loader-libro-lomo"></span>' +
            '<span class="loader-libro-pagina"></span>' +
            '<span class="loader-libro-pagina"></span>' +
            '<span class="loader-libro-pagina"></span>' +
            '<span class="loader-libro-tapa"></span>' +
            '</div>' +
            '<div class="loader-barra" aria-hidden="true"><span></span></div>' +
            '<p class="loader-texto">' + (texto || TEXTO_DEFAULT) + '</p>' +
            '</div>'
        );
    }

    function asegurarMarkup(texto) {
        let capa = nodo();
        if (capa) {
            if (!capa.querySelector('.loader-libro')) {
                capa.innerHTML = markupInterno(texto || TEXTO_DEFAULT);
            } else {
                const p = capa.querySelector('.loader-texto');
                if (p && texto) p.textContent = texto;
            }
            return capa;
        }
        capa = document.createElement('div');
        capa.id = 'loader-pagina';
        capa.className = 'loader-pagina';
        capa.setAttribute('role', 'status');
        capa.setAttribute('aria-live', 'polite');
        capa.setAttribute('aria-busy', 'true');
        capa.innerHTML = markupInterno(texto || TEXTO_DEFAULT);
        document.documentElement.appendChild(capa);
        return capa;
    }

    const Loader = {
        show: function (texto) {
            const capa = asegurarMarkup(texto || TEXTO_DEFAULT);
            capa.classList.remove('oculto', 'd-none');
            capa.setAttribute('aria-busy', 'true');
            document.documentElement.classList.add('cargando-vista');
        },

        hide: function () {
            const capa = nodo();
            if (!capa) return;
            capa.classList.add('oculto');
            capa.setAttribute('aria-busy', 'false');
            document.documentElement.classList.remove('cargando-vista');
            window.setTimeout(function () {
                capa.classList.add('d-none');
            }, 300);
        },

        ir: function (url, texto) {
            this.show(texto || 'Cambiando de vista…');
            window.setTimeout(function () {
                window.location.href = url;
            }, 80);
        }
    };

    function esNavegacionInterna(enlace) {
        if (!enlace || !enlace.getAttribute) return false;
        if (enlace.hasAttribute('download')) return false;
        if (enlace.target && enlace.target !== '_self') return false;
        if (enlace.dataset.loader === 'off') return false;
        const href = enlace.getAttribute('href');
        if (!href || href.charAt(0) === '#') return false;
        if (href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0 || href.indexOf('javascript:') === 0) return false;
        try {
            const url = new URL(enlace.href, window.location.href);
            if (url.origin !== window.location.origin) return false;
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
                return false;
            }
            return true;
        } catch (error) {
            return false;
        }
    }

    function textoPara(url) {
        const ruta = String(url || '');
        if (ruta.indexOf('login') !== -1) return 'Abriendo ingreso…';
        if (ruta.indexOf('dashboard') !== -1) return 'Abriendo panel…';
        if (ruta.indexOf('usuario') !== -1) return 'Abriendo tu cuenta…';
        if (ruta.indexOf('index') !== -1 || ruta.indexOf('/front') !== -1) return 'Abriendo EXTERNOLAB…';
        return 'Cambiando de vista…';
    }

    function bindNavegacion() {
        document.addEventListener('click', function (evento) {
            if (evento.defaultPrevented) return;
            if (evento.button !== 0) return;
            if (evento.metaKey || evento.ctrlKey || evento.shiftKey || evento.altKey) return;
            const enlace = evento.target.closest('a[href]');
            if (!esNavegacionInterna(enlace)) return;
            evento.preventDefault();
            Loader.ir(enlace.href, textoPara(enlace.href));
        }, true);

        document.addEventListener('submit', function (evento) {
            const form = evento.target;
            if (!form || form.tagName !== 'FORM') return;
            if (evento.defaultPrevented) return;
            if (form.dataset.loader === 'off') return;
            if (form.getAttribute('target') && form.getAttribute('target') !== '_self') return;
            Loader.show('Guardando…');
        }, true);
    }

    window.Loader = Loader;

    function textoInicial() {
        if (document.body && document.body.dataset && document.body.dataset.loaderTexto) {
            return document.body.dataset.loaderTexto;
        }
        return TEXTO_DEFAULT;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            asegurarMarkup(textoInicial());
        });
    }

    function listo() {
        // Las páginas con carga async (catálogo) pueden llamar Loader.hide() después.
        if (document.body && document.body.dataset.loaderManual === '1') return;
        Loader.hide();
    }

    if (document.readyState === 'complete') {
        window.setTimeout(listo, 120);
    } else {
        window.addEventListener('load', function () {
            window.setTimeout(listo, 120);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindNavegacion);
    } else {
        bindNavegacion();
    }
})();
