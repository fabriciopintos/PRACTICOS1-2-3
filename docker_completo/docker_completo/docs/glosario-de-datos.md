# Glosario de datos — Practicos3MD

Descripción de cada campo que usa la biblioteca.

## roles


| Campo  | Significado                                                    |
| ------ | -------------------------------------------------------------- |
| id_rol | Identificador del rol.                                         |
| nombre | Nombre del rol. En este práctico: `administrador` o `usuario`. |


## usuarios


| Campo         | Significado                                                             |
| ------------- | ----------------------------------------------------------------------- |
| id_usuario    | Identificador de la cuenta.                                             |
| nombre        | Nombre que se muestra en el panel y en la página del usuario.           |
| email         | Correo con el que se inicia sesión. No se repite.                       |
| password_hash | Contraseña guardada con bcrypt. La clave en texto plano no se almacena. |
| id_rol        | Rol de la cuenta. Apunta a `roles`.                                     |
| activo        | 1 si la cuenta puede entrar. 0 si está deshabilitada.                   |
| creado_en     | Fecha y hora en que se creó la cuenta.                                  |


Cuentas de prueba para verificar el práctico:


| Rol           | Correo                                                  | Contraseña     |
| ------------- | ------------------------------------------------------- | -------------- |
| Administrador | [admin@biblioteca.test](mailto:admin@biblioteca.test)   | Biblioteca2026 |
| Usuario       | [lector@biblioteca.test](mailto:lector@biblioteca.test) | Lectura2026    |




## categorias


| Campo        | Significado                                                            |
| ------------ | ---------------------------------------------------------------------- |
| id_categoria | Identificador de la categoría.                                         |
| nombre       | Nombre visible, por ejemplo Fantasía.                                  |
| slug         | Versión corta usada por los filtros del sitio, por ejemplo `fantasia`. |




## autores


| Campo        | Significado                                                |
| ------------ | ---------------------------------------------------------- |
| id_autor     | Identificador del autor.                                   |
| nombre       | Nombre completo que se muestra en las fichas.              |
| es_destacado | 1 si el autor tiene estante propio en la página de inicio. |




## editoriales


| Campo        | Significado              |
| ------------ | ------------------------ |
| id_editorial | Identificador del sello. |
| nombre       | Nombre de la editorial.  |




## productos


| Campo          | Significado                                                               |
| -------------- | ------------------------------------------------------------------------- |
| id_producto    | Identificador del título.                                                 |
| tipo           | `libro`, `revista` o `audiolibro`.                                        |
| titulo         | Nombre del título.                                                        |
| id_autor       | Autor, si corresponde. Vacío en las revistas.                             |
| id_categoria   | Categoría del título.                                                     |
| id_editorial   | Editorial del título.                                                     |
| precio         | Precio de lista.                                                          |
| precio_oferta  | Precio rebajado. Vacío cuando no hay oferta.                              |
| anio           | Año de la edición o de la obra.                                           |
| descripcion    | Texto breve de la ficha.                                                  |
| estilo_portada | Código de la tapa, por ejemplo `got1` o `revista2`.                       |
| imagen         | Ruta de la tapa dentro del sitio, por ejemplo `assets/portadas/got1.jpg`. |
| disponible     | 1 si el título se muestra en el catálogo público; 0 si está oculto.       |
| en_autor       | 1 si el título aparece en el estante del autor destacado.                 |
| en_aventura    | 1 si el título aparece en el estante de aventuras.                        |
| editorial      | Nombre de la editorial, repetido para la búsqueda del catálogo.           |


