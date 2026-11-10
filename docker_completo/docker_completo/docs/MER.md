# Modelo Entidad-Relación — Practicos3MD

Biblioteca virtual del práctico 1. El modelo cubre el catálogo público (libros, revistas y audiolibros) y el ingreso con roles del práctico 2.

## Entidades

- **Rol.** Tipo de cuenta. En este práctico hay dos: administrador y usuario.
- **Usuario.** Persona que inicia sesión. Tiene un único rol.
- **Categoría.** Género o sección del catálogo (fantasía, terror, amor, aventura, clásicos, cultura, ciencia, drama).
- **Autor.** Persona asociada a un libro o a un audiolibro. Una revista puede no tener autor.
- **Editorial.** Sello que publica el título.
- **Producto.** Cada título del catálogo. El atributo `tipo` distingue libro, revista y audiolibro.

## Relaciones y cardinalidades

```mermaid
erDiagram
    ROL ||--o{ USUARIO : "tiene"
    CATEGORIA ||--o{ PRODUCTO : "clasifica"
    AUTOR |o--o{ PRODUCTO : "escribe"
    EDITORIAL |o--o{ PRODUCTO : "publica"

    ROL {
        int id_rol PK
        string nombre
    }
    USUARIO {
        int id_usuario PK
        string nombre
        string email
        string password_hash
        int id_rol FK
        int activo
        datetime creado_en
    }
    CATEGORIA {
        int id_categoria PK
        string nombre
        string slug
    }
    AUTOR {
        int id_autor PK
        string nombre
        int es_destacado
    }
    EDITORIAL {
        int id_editorial PK
        string nombre
    }
    PRODUCTO {
        int id_producto PK
        string tipo
        string titulo
        int id_autor FK
        int id_categoria FK
        int id_editorial FK
        decimal precio
        decimal precio_oferta
        int anio
        string descripcion
        string estilo_portada
        string imagen
        int en_autor
        int en_aventura
        string editorial
    }
```

| Relación | Cardinalidad | Lectura |
| --- | --- | --- |
| Rol — Usuario | 1 a N | Un rol agrupa muchos usuarios. Cada usuario tiene exactamente un rol. |
| Categoría — Producto | 1 a N | Una categoría reúne muchos títulos. Cada título pertenece a una categoría. |
| Autor — Producto | 0..1 a N | Un autor puede firmar muchos títulos. Un título tiene un autor o ninguno (las revistas). |
| Editorial — Producto | 0..1 a N | Una editorial publica muchos títulos. Un título puede quedar sin editorial. |

## Claves primarias

Cada entidad tiene un identificador propio: `id_rol`, `id_usuario`, `id_categoria`, `id_autor`, `id_editorial` e `id_producto`.
