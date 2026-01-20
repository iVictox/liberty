<?php
session_start();

// Conexión
include($_SERVER['DOCUMENT_ROOT'].'/liberty/app/db/connect.php');

// Redirigir si no es un método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location:  /liberty/login.php");
    exit;
}

// Obtener valores
$correo = $_POST['correo'];
$contraseña_ingresada = trim($_POST['contraseña']); 

// Validar que no estén vacíos
if (empty($correo) || empty($contraseña_ingresada)) {
    $_SESSION['login_error'] = 'Por favor, ingresa correo y contraseña.';
    header("Location:  /liberty/login.php");
    exit;
}

try {    
    // 1. Busca AL USUARIO por su correo
    $sentencia = $conn->prepare('SELECT * FROM usuario WHERE correo = ? LIMIT 1;');
    $sentencia->execute([$correo]);
    $usuario = $sentencia->fetch(PDO::FETCH_OBJ);

    // 2. Verifica si el usuario existe Y SI LA CONTRASEÑA ES CORRECTA
    if ($usuario && password_verify($contraseña_ingresada, $usuario->contraseña)) {
        
        // 3. Verificar estado activo
        if ($usuario->estado == 1) {
            unset($_SESSION['login_error']);
            
            // Guardar datos en sesión
            $_SESSION['user_id'] = $usuario->id;
            $_SESSION['user_nombre'] = $usuario->nombre; 
            $_SESSION['user_apellido'] = $usuario->apellido; 
            $_SESSION['user_rol'] = $usuario->rol_id;
            $_SESSION['logged_in'] = true;

            // Regenerar ID por seguridad
            session_regenerate_id(true);

            // --- NUEVA LÓGICA DE SEGURIDAD ---
            // Si requiere cambio, lo enviamos a la pantalla de cambio
            if ($usuario->requiere_cambio == 1) {
                header("Location: /liberty/cambiar_clave.php");
                exit;
            }

            // Si todo está normal, al Dashboard
            header("Location: /liberty/"); 
            exit;

        } else {
            $_SESSION['login_error'] = 'Tu cuenta está inactiva o no tienes permisos.';
            header("Location: /liberty/login.php");
            exit;
        }
    } else {
        $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
        header("Location: /liberty/login.php");
        exit;
    }

} catch (PDOException $e) {    
    $_SESSION['login_error'] = 'Error del sistema. Inténtalo más tarde.';
    header("Location: /liberty/login.php");
    exit;
}
?>