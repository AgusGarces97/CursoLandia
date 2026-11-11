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
    
    // 3. Capturamos y limpiamos los datos de datos personales
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $edad = (int)$_POST['edad'];
    $antecedentes = mysqli_real_escape_string($conexion, trim($_POST['antecedentes']));

    // 4. Ejecutamos el UPDATE de datos personales del usuario
    $sql_update = "
        UPDATE usuarios 
        SET nombre = '$nombre', 
            apellido = '$apellido', 
            edad = $edad, 
            antecedentes = '$antecedentes' 
        WHERE id_usuario = $idUsuario
    ";

    if (mysqli_query($conexion, $sql_update)) {
        
        // Actualizamos las variables de sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;

        // 5. PROCESAMIENTO DINÁMICO DE INTERESES / ETIQUETAS
        // A. Limpiamos las relaciones anteriores del usuario en usuario_intereses
        mysqli_query($conexion, "DELETE FROM usuario_intereses WHERE id_usuario = $idUsuario");

        // B. Si se enviaron etiquetas seleccionadas, procesamos cada una
        if (isset($_POST['etiquetas']) && is_array($_POST['etiquetas'])) {
            foreach ($_POST['etiquetas'] as $nombreEtiqueta) {
                $nombreLimpio = mysqli_real_escape_string($conexion, trim(str_replace('#', '', $nombreEtiqueta)));
                
                if (!empty($nombreLimpio)) {
                    // Verificamos si la etiqueta ya existe en el catálogo general 'etiquetas'
                    $sql_check_tag = "SELECT id_etiqueta FROM etiquetas WHERE LOWER(nombre) = LOWER('$nombreLimpio')";
                    $res_check_tag = mysqli_query($conexion, $sql_check_tag);

                    if (mysqli_num_rows($res_check_tag) > 0) {
                        $row_tag = mysqli_fetch_assoc($res_check_tag);
                        $id_etiqueta = $row_tag['id_etiqueta'];
                    } else {
                        // Si no existe, la creamos dinámicamente en el catálogo
                        mysqli_query($conexion, "INSERT INTO etiquetas (nombre) VALUES ('$nombreLimpio')");
                        $id_etiqueta = mysqli_insert_id($conexion);
                    }

                    // Insertamos la relación usuario <-> etiqueta
                    mysqli_query($conexion, "INSERT IGNORE INTO usuario_intereses (id_usuario, id_etiqueta) VALUES ($idUsuario, $id_etiqueta)");
                }
            }
        }
        
        header("Location: perfil.php?editado=exito");
        exit();
        
    } else {
        header("Location: perfil.php?editado=error");
        exit();
    }

} else {
    header("Location: perfil.php");
    exit();
}