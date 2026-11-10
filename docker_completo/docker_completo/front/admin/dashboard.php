<?php
header('Location: /front/vistas/admin/dashboard.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? ('?' . $_SERVER['QUERY_STRING']) : ''), true, 301);
exit;
