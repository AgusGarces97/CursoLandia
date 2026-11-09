<?php
// 1. Iniciamos sesión y validamos seguridad
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

require_once 'conexion.php';

// 2. Verificamos que los datos vengan por el formulario correcto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botonEditarPerfil'])) {
    
    $idUsuario = $_SESSION['id_usuario'];
    
    // 3. Capturamos y limpiamos los datos para evitar inyección SQL
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $edad = (int)$_POST['edad'];
    $antecedentes = mysqli_real_escape_string($conexion, $_POST['antecedentes']);

    // NOTA: DNI y Email no se actualizan porque son de solo lectura (RN02)

    // 4. Ejecutamos el UPDATE en la base de datos
    $sql_update = "
        UPDATE usuarios 
        SET nombre = '$nombre', 
            apellido = '$apellido', 
            edad = $edad, 
            antecedentes = '$antecedentes' 
        WHERE id_usuario = $idUsuario
    ";

    if (mysqli_query($conexion, $sql_update)) {
        
        // 5. ¡Clave! Actualizamos la sesión para que el menú cambie instantáneamente
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        
        // Redirigimos de vuelta con una variable de éxito en la URL
        header("Location: perfil.php?editado=exito");
        exit();
        
    } else {
        // Redirigimos con error si falla la BD
        header("Location: perfil.php?editado=error");
        exit();
    }

} else {
    // Si entran por URL directa, los pateamos al perfil
    header("Location: perfil.php");
    exit();
}