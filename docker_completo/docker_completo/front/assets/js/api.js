// Consultas a la API de Practicos3MD (/api).
const Api = {
    base: '/api',

    async get(ruta) {
        const respuesta = await fetch(this.base + ruta, {
            headers: { Accept: 'application/json' }
        });
        const datos = await respuesta.json().catch(function () {
            return null;
        });
        if (!respuesta.ok) {
            const mensaje = datos && datos.message ? datos.message : 'No se pudo consultar la API';
            throw new Error(mensaje);
        }
        return datos;
    },

    async post(ruta, cuerpo) {
        const respuesta = await fetch(this.base + ruta, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(cuerpo || {})
        });
        const datos = await respuesta.json().catch(function () {
            return null;
        });
        if (!respuesta.ok) {
            const mensaje = datos && datos.message ? datos.message : 'No se pudo consultar la API';
            throw new Error(mensaje);
        }
        return datos;
    }
};
