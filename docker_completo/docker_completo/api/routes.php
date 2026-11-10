<?php
// api/routes.php
// Cuando pidan esta URL, se ejecuta este controlador y este método.

$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');
$router->get('/sesion', 'AuthController@sesion');
$router->get('/estado', 'EstadoController@ver');
$router->get('/productos', 'ProductoController@listar');
$router->get('/productos/:id', 'ProductoController@ver');
$router->get('/categorias', 'CategoriaController@listar');
$router->get('/autor-destacado', 'AutorController@destacado');
