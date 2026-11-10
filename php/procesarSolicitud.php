<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botonEnviarSolicitud'])) {
    
    $idUsuario = $_SESSION['id_usuario'];
    $mensaje = mysqli_real_escape_string($conexion, $_POST['mensaje']);
    $nombreArchivoGuardado = null;

    // Procesamiento de subida del archivo comprobante
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        
        $directorioDestino = '../uploads/comprobantes/';
        
        // Si la carpeta no existe, la crea automáticamente
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $nombreArchivoGuardado = 'comprobante_' . $idUsuario . '_' . time() . '.' . strtolower($extension);
        $rutaCompleta = $directorioDestino . $nombreArchivoGuardado;

        move_uploaded_file($_FILES['comprobante']['tmp_name'], $rutaCompleta);
    }

    // Insertar solicitud en la base de datos
    $sql_insert = "
        INSERT INTO solicitudes_pro (id_usuario, mensaje, comprobante, estado) 
        VALUES ($idUsuario, '$mensaje', '$nombreArchivoGuardado', 'pendiente')
    ";

    if (mysqli_query($conexion, $sql_insert)) {
        header("Location: perfil.php?solicitud=enviada");
        exit();
    } else {
        header("Location: perfil.php?solicitud=error");
        exit();
    }

} else {
    header("Location: perfil.php");
    exit();
}