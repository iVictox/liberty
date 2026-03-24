<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/users/users.php');

$id = $_SESSION['user_id'];
$mensaje = '';
$tipo_mensaje = '';

// --- LÓGICA DE SUBIDA DE FOTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    $nombre_archivo = $archivo['name'];
    $tipo = $archivo['type'];
    $tmp_name = $archivo['tmp_name'];
    $error = $archivo['error'];
    $size = $archivo['size'];

    // Validaciones básicas
    $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($error === 0) {
        if (in_array($tipo, $permitidos) && $size <= $max_size) {
            // Crear nombre único: id_timestamp.ext
            $ext = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
            $nuevo_nombre = $id . '_' . time() . '.' . $ext;
            $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/liberty/app/assets/uploads/perfiles/' . $nuevo_nombre;

            // Mover archivo
            if (move_uploaded_file($tmp_name, $ruta_destino)) {
                // Actualizar BD
                $stmt = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE id = ?");
                if ($stmt->execute([$nuevo_nombre, $id])) {
                    // Actualizar variable de sesión para que el menú cambie al instante
                    $_SESSION['user_foto'] = $nuevo_nombre;
                    $mensaje = "Foto de perfil actualizada correctamente.";
                    $tipo_mensaje = 'exito';
                } else {
                    $mensaje = "Error al guardar en base de datos.";
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = "Error al subir el archivo al servidor.";
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "Formato no válido (solo JPG/PNG) o archivo muy pesado (max 2MB).";
            $tipo_mensaje = 'error';
        }
    }
}

// --- LÓGICA DE CAMBIO DE CONTRASEÑA ---
// CORRECCIÓN: Se cambió "action" por "perfil_action" para que users.php no lo intercepte
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['perfil_action']) && $_POST['perfil_action'] == 'update_password') {
    $actual = $_POST['pass_actual'];
    $nueva = $_POST['pass_nueva'];
    $confirmar = $_POST['pass_confirmar'];

    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $mensaje = "Todos los campos son obligatorios."; $tipo_mensaje = 'error';
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas nuevas no coinciden."; $tipo_mensaje = 'error';
    } elseif (strlen($nueva) < 6) {
        $mensaje = "La contraseña nueva debe tener al menos 6 caracteres."; $tipo_mensaje = 'error';
    } else {
        $stmt = $conn->prepare("SELECT contraseña FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user && password_verify($actual, $user->contraseña)) {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
            if ($update->execute([$hash, $id])) {
                $mensaje = "Contraseña actualizada correctamente."; $tipo_mensaje = 'exito';
            } else {
                $mensaje = "Error al actualizar en BD."; $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "La contraseña actual es incorrecta."; $tipo_mensaje = 'error';
        }
    }
}

// Obtener datos frescos
$stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/forms.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 700px; margin: 0 auto; }
        .profile-header { text-align: center; margin-bottom: 2rem; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .profile-avatar-container { position: relative; width: 120px; height: 120px; margin: 0 auto 1rem; }
        .profile-avatar { width: 100%; height: 100%; background: #500101; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; overflow: hidden; object-fit: cover; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .btn-upload-label { position: absolute; bottom: 0; right: 0; background: #333; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s; }
        .btn-upload-label:hover { background: #500101; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.exito { background: #dcfce7; color: #166534; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            <div class="profile-card">
                <?php if ($mensaje): ?>
                    <div class="msg <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                <?php endif; ?>

                <div class="profile-header">
                    <form action="" method="POST" enctype="multipart/form-data" id="formFoto">
                        <div class="profile-avatar-container">
                            <div class="profile-avatar">
                                <?php if (!empty($usuario->foto_perfil) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/assets/uploads/perfiles/' . $usuario->foto_perfil)): ?>
                                    <img src="/liberty/app/assets/uploads/perfiles/<?php echo $usuario->foto_perfil; ?>" alt="Foto Perfil">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($usuario->nombre, 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <label for="uploadFoto" class="btn-upload-label" title="Cambiar Foto">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="foto_perfil" id="uploadFoto" style="display: none;" onchange="document.getElementById('formFoto').submit();">
                        </div>
                    </form>

                    <h2><?php echo htmlspecialchars($usuario->nombre . ' ' . $usuario->apellido); ?></h2>
                    <div>
                        <span class="badge badge-info"><?php echo traducirRol($usuario->rol_id); ?></span>
                        <span class="badge <?php echo $usuario->estado ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $usuario->estado ? 'Cuenta Activa' : 'Cuenta Inactiva'; ?>
                        </span>
                    </div>
                </div>

                <form method="POST" class="form-container">
                    <h3 style="margin-bottom: 15px; color: #500101;"><i class="fas fa-id-card"></i> Información Personal</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario->correo); ?>" disabled style="background-color: #f8fafc;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Turno de Trabajo</label>
                            <input type="text" class="form-control" value="<?php echo traducirTurno($usuario->turno); ?>" disabled style="background-color: #f8fafc;">
                        </div>
                    </div>
                    
                    <h3 style="margin-bottom: 15px; margin-top: 30px; color: #500101;"><i class="fas fa-lock"></i> Seguridad</h3>
                    <input type="hidden" name="perfil_action" value="update_password">
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="pass_actual" class="form-control" placeholder="Ingresa tu contraseña actual para confirmar">
                    </div>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar Nueva</label>
                            <input type="password" name="pass_confirmar" class="form-control">
                        </div>
                    </div>

                    <div class="form-actions" style="justify-content: center; margin-top: 20px;">
                        <button type="submit" class="btn-submit">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>