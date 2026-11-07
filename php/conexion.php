<?php

$dbHost = 'localhost';
$dbUsuario = 'usuario_admin';
$dbPassword = 'admin';
$db = 'cursolandia_db';

$conexion = mysqli_connect($dbHost, $dbUsuario, $dbPassword, $db);

// Verificar si hubo algún error
if (!$conexion) {
    die("Error crítico: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8 para evitar problemas con acentos y eñes
mysqli_set_charset($conexion, "utf8mb4");

?>
