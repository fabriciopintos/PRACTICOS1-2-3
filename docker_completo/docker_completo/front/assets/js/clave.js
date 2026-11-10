(function () {
    function icono(visible) {
        if (visible) {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4l18 16M10.5 6.3A11 11 0 0 1 12 6c6.2 0 10 6 10 6a18 18 0 0 1-3.5 3.8M6.1 8.2C3.7 9.8 2 12 2 12s3.8 6 10 6c1.2 0 2.4-.2 3.5-.7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.8-6 10-6 10 6 10 6-3.8 6-10 6S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.7" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
    }

    document.querySelectorAll('[data-clave]').forEach(function (grupo) {
        var input = grupo.querySelector('input');
        var boton = grupo.querySelector('.ojo-clave');
        var aviso = grupo.querySelector('.aviso-mayus');
        if (!input || !boton || !aviso) return;

        function pintarOjo() {
            var visible = input.type === 'text';
            boton.innerHTML = icono(visible);
            boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
            boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        }

        function marcarMayusculas(evento) {
            if (!evento.getModifierState) return;
            aviso.hidden = !evento.getModifierState('CapsLock');
        }

        pintarOjo();
        boton.addEventListener('click', function () {
            input.type = input.type === 'password' ? 'text' : 'password';
            pintarOjo();
            input.focus();
        });
        input.addEventListener('keydown', marcarMayusculas);
        input.addEventListener('keyup', marcarMayusculas);
        input.addEventListener('blur', function () {
            aviso.hidden = true;
        });
    });
})();
