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
    // Redirigir de vuelta al formulario
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
        // 3. La contraseña es correcta. Ahora verifica el estado y rol.
        if ($usuario->estado == 1) {
            unset($_SESSION['login_error']);
            
            // Guarda los datos del usuario en la sesión para usarlos en otras páginas
            $_SESSION['user_id'] = $usuario->id;
            $_SESSION['user_nombre'] = $usuario->nombre; 
            $_SESSION['user_apellido'] = $usuario->apellido; 
            $_SESSION['user_rol'] = $usuario->rol_id;
            $_SESSION['logged_in'] = true;

            // Regenera el ID de sesión para prevenir "Session Fixation"
            session_regenerate_id(true);

            // Redirige al panel principal
            header("Location: /liberty/"); 
            exit;

        } else {
            // El usuario existe y la contraseña es correcta, pero está inactivo o no es admin
            $_SESSION['login_error'] = 'Tu cuenta está inactiva o no tienes permisos para ingresar.';
            header("Location: /liberty/login.php");
            exit;
        }
    } else {
        // El usuario no existe O la contraseña es incorrecta
        // Damos un mensaje genérico por seguridad
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