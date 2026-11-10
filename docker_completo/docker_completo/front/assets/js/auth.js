const Auth = {
    destino(rol) {
        return rol === 'administrador'
            ? '/front/vistas/admin/dashboard.php'
            : '/front/vistas/usuario/inicio.php';
    },

    async sesion() {
        return Api.get('/sesion');
    },

    async entrar(email, password) {
        return Api.post('/login', { email: email, password: password });
    },

    async salir() {
        return Api.post('/logout', {});
    }
};
