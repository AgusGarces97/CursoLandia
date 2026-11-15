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

// 2. PROCESAMIENTO DE EDICIÓN DE PERFIL (DATOS E INTERESES)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botonEditarPerfil'])) {
    
    $idUsuario = $_SESSION['id_usuario'];
    
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $edad = (int)$_POST['edad'];
    $antecedentes = mysqli_real_escape_string($conexion, trim($_POST['antecedentes']));

    // --- LÓGICA DE SUBIDA DE IMAGEN DE PERFIL ---
    $sql_img = "";
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagen_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($ext, $permitidas)) {
            $dir = '../uploads/perfiles/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $nombre_img = 'perfil_' . $idUsuario . '_' . time() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $dir . $nombre_img)) {
                $sql_img = ", imagen_perfil = '$nombre_img'";
            }
        }
    }
    // --------------------------------------------

    $sql_update = "
        UPDATE usuarios 
        SET nombre = '$nombre', 
            apellido = '$apellido', 
            edad = $edad, 
            antecedentes = '$antecedentes'
            $sql_img
        WHERE id_usuario = $idUsuario
    ";

    if (mysqli_query($conexion, $sql_update)) {
        
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;

        mysqli_query($conexion, "DELETE FROM usuario_intereses WHERE id_usuario = $idUsuario");

        if (isset($_POST['etiquetas']) && is_array($_POST['etiquetas'])) {
            foreach ($_POST['etiquetas'] as $nombreEtiqueta) {
                $nombreLimpio = mysqli_real_escape_string($conexion, trim(str_replace('#', '', $nombreEtiqueta)));
                
                if (!empty($nombreLimpio)) {
                    $sql_check_tag = "SELECT id_etiqueta FROM etiquetas WHERE LOWER(nombre) = LOWER('$nombreLimpio')";
                    $res_check_tag = mysqli_query($conexion, $sql_check_tag);

                    if (mysqli_num_rows($res_check_tag) > 0) {
                        $row_tag = mysqli_fetch_assoc($res_check_tag);
                        $id_etiqueta = $row_tag['id_etiqueta'];
                    } else {
                        mysqli_query($conexion, "INSERT INTO etiquetas (nombre) VALUES ('$nombreLimpio')");
                        $id_etiqueta = mysqli_insert_id($conexion);
                    }

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

// 3. PROCESAMIENTO DE CAMBIO DE CONTRASEÑA
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botonCambiarPassword'])) {
    
    $idUsuario = $_SESSION['id_usuario'];
    $pass_actual = $_POST['password_actual'];
    $pass_nueva = $_POST['password_nueva'];
    $pass_confirmar = $_POST['password_confirmar'];

    // Validar coincidencia de la nueva contraseña
    if ($pass_nueva !== $pass_confirmar) {
        header("Location: perfil.php?pass=no_coinciden");
        exit();
    }

    // Consultar clave actual del usuario en la BD
    $sql_user = "SELECT password FROM usuarios WHERE id_usuario = $idUsuario";
    $res_user = mysqli_query($conexion, $sql_user);

    if ($res_user && mysqli_num_rows($res_user) > 0) {
        $user_data = mysqli_fetch_assoc($res_user);
        
        // Verificamos si coincide con la clave de la base de datos
        if (password_verify($pass_actual, $user_data['password']) || $pass_actual === $user_data['password']) {
            
            // Guardamos la nueva clave como texto plano para mantener compatibilidad con el login
            $pass_nueva_esc = mysqli_real_escape_string($conexion, $pass_nueva);

            $sql_pass = "UPDATE usuarios SET password = '$pass_nueva_esc' WHERE id_usuario = $idUsuario";
            
            if (mysqli_query($conexion, $sql_pass)) {
                header("Location: perfil.php?pass=exito");
                exit();
            } else {
                header("Location: perfil.php?editado=error");
                exit();
            }
        } else {
            header("Location: perfil.php?pass=actual_incorrecta");
            exit();
        }
    } else {
        header("Location: perfil.php?editado=error");
        exit();
    }

} else {
    header("Location: perfil.php");
    exit();
}