<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
    $idUsuario = (int)$_POST['id_usuario'];

    // Verificamos si ya tiene una solicitud pendiente
    $sql_check = "SELECT * FROM solicitudes_pro WHERE id_usuario = $idUsuario AND estado = 'pendiente'";
    $res_check = mysqli_query($conexion, $sql_check);

    if (mysqli_num_rows($res_check) == 0) {
        $sql_insert = "INSERT INTO solicitudes_pro (id_usuario) VALUES ($idUsuario)";
        mysqli_query($conexion, $sql_insert);
        header("Location: perfil.php?solicitud=enviada");
        exit();
    } else {
        header("Location: perfil.php?solicitud=pendiente");
        exit();
    }
} else {
    header("Location: perfil.php");
    exit();
}