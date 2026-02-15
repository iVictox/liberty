<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/lib/phpmailer/src/SMTP.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_rol'] != 3) {
    $_SESSION['user_error'] = 'Acceso no autorizado.';
    header('Location: /liberty/usuario.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['user_error'] = 'ID de usuario no proporcionado.';
    header('Location: /liberty/usuario.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT correo, nombre FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$usuario) {
        throw new Exception("Usuario no encontrado.");
    }

    $token = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // --- CORRECCIÓN: ESTADO = 0 (DESHABILITA CUENTA) ---
    $update = $conn->prepare("UPDATE usuario SET token_password = ?, token_expiracion = ?, estado = 0 WHERE id = ?");
    $update->execute([$token, $expiracion, $id]);
    // ---------------------------------------------------

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'libertyexpress.system@gmail.com';
    $mail->Password   = 'loyq pexn oyif mtku'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('no-reply@libertyexpress.com', 'Soporte Liberty Express');
    $mail->addAddress($usuario->correo, $usuario->nombre);

    $link = "http://localhost/liberty/restablecer.php?token=" . $token;
    
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Restablecer Contraseña - Liberty Express';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #500101;'>Solicitud de Cambio de Contraseña</h2>
            <p>Hola <strong>{$usuario->nombre}</strong>,</p>
            <p>El administrador ha solicitado un restablecimiento de contraseña para tu cuenta.</p>
            <p>Por seguridad, <strong>tu cuenta ha sido inhabilitada temporalmente</strong>.</p>
            <p>Haz clic en el siguiente botón para crear una nueva contraseña y reactivar tu cuenta:</p>
            <p><a href='$link'>Restablecer y Reactivar</a></p>
        </div>
    ";

    $mail->send();
    $_SESSION['user_message'] = 'Correo enviado. La cuenta ha sido inhabilitada temporalmente.';

} catch (Exception $e) {
    $_SESSION['user_error'] = 'Error al enviar correo: ' . $mail->ErrorInfo;
} catch (PDOException $e) {
    $_SESSION['user_error'] = 'Error de BD: ' . $e->getMessage();
}

header('Location: /liberty/usuario.php');
exit;
?>