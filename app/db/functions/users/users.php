<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/auditoria/registrar.php'); // Incluir auditoría


if (!function_exists('traducirRol')) {
    function traducirRol($rol_id) {
        global $conn; 
        if (!$conn) return ($rol_id == 1) ? 'Administrador' : 'Usuario';
        try {
            $stmt = $conn->prepare("SELECT Nombre FROM rol WHERE id = ?");
            $stmt->execute([$rol_id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rol ? $rol['Nombre'] : 'Desconocido';
        } catch (PDOException $e) { return 'Error DB'; }
    }
}
if (!function_exists('obtenerRoles')) {
    function obtenerRoles() {
        global $conn;
        if (!$conn) return [];
        try {
            $stmt = $conn->prepare("SELECT id, Nombre FROM rol ORDER BY Nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
}
if (!function_exists('traducirEstado')) {
    function traducirEstado($estado) {
        return ($estado == 1) ? 'Activo' : 'Inactivo';
    }
}
if (!function_exists('traducirTurno')) {
    function traducirTurno($turno) {
        return ($turno == 0) ? 'Mañana' : (($turno == 1) ? 'Tarde' : 'N/A');
    }
}

// -------------------------------------------------
// MANEJADOR DE ACCIONES (POST)
// -------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // Validar Rol Admin (3)
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_rol'] != 3) {
        $_SESSION['user_error'] = 'Acceso denegado. No tienes permisos de administrador.';
        header('Location: /liberty/usuario.php');
        exit;
    }

    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id']; // Quien hace la acción

    try {
        switch ($action) {

            // --- ACCIÓN: CREAR USUARIO ---
            case 'create':
                $nombre = trim($_POST['nombre']);
                $apellido = trim($_POST['apellido']);
                $correo = trim($_POST['correo']);
                $contraseña_plana = trim($_POST['contraseña']);
                $turno = (int) $_POST['turno'];
                $rol_id = (int) $_POST['rol_id'];
                $estado = trim($_POST['estado']);

                if (empty($nombre) || empty($apellido) || empty($correo) || empty($contraseña_plana)) {
                    $_SESSION['user_error'] = 'Error: Campos básicos vacíos.';
                } else {
                    $hash_contraseña = password_hash($contraseña_plana, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, correo, contraseña, turno, rol_id, estado, requiere_cambio) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$nombre, $apellido, $correo, $hash_contraseña, $turno, $rol_id, $estado]);
                    
                    // AUDITORÍA
                    registrarAuditoria($conn, $admin_id, "Crear Usuario", "Creó al usuario: $correo (Rol ID: $rol_id)");

                    $_SESSION['user_message'] = '¡Usuario creado! Deberá cambiar su contraseña al ingresar.';
                }
                break;

            // --- ACCIÓN: EDITAR USUARIO ---
            case 'edit':
                $id = (int) $_POST['id'];
                $nombre = trim($_POST['nombre']);
                $apellido = trim($_POST['apellido']);
                $correo = trim($_POST['correo']);
                $contraseña_plana = trim($_POST['contraseña']); 
                $turno = (int) $_POST['turno'];
                $rol_id = (int) $_POST['rol_id'];
                $estado = trim($_POST['estado']);

                if (empty($nombre) || empty($apellido) || empty($correo) || empty($id)) {
                    $_SESSION['user_error'] = 'Error: Campos básicos vacíos.';
                } else {
                    if (!empty($contraseña_plana)) {
                        $hash_contraseña = password_hash($contraseña_plana, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, contraseña = ?, turno = ?, rol_id = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$nombre, $apellido, $correo, $hash_contraseña, $turno, $rol_id, $estado, $id]);
                        $cambioPass = " (Clave cambiada)";
                    } else {
                        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, turno = ?, rol_id = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$nombre, $apellido, $correo, $turno, $rol_id, $estado, $id]);
                        $cambioPass = "";
                    }
                    
                    // AUDITORÍA
                    registrarAuditoria($conn, $admin_id, "Editar Usuario", "Editó datos de ID: $id ($correo)$cambioPass");

                    $_SESSION['user_message'] = '¡Usuario actualizado exitosamente!';
                }
                break;

            // --- ACCIÓN: ELIMINAR USUARIO ---
            case 'delete':
                $id = (int) $_POST['id'];
                if (empty($id)) {
                    $_SESSION['user_error'] = 'Error: ID no proporcionado.';
                } else if ($id == $_SESSION['user_id']) {
                    $_SESSION['user_error'] = 'No puedes eliminar tu propia cuenta.';
                } else {
                    $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    // AUDITORÍA
                    registrarAuditoria($conn, $admin_id, "Eliminar Usuario", "Eliminó al usuario ID: $id");

                    $_SESSION['user_message'] = '¡Usuario eliminado exitosamente!';
                }
                break;

            default:
                $_SESSION['user_error'] = 'Acción desconocida.';
                break;
        }

    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { 
            $_SESSION['user_error'] = 'Error: El correo electrónico ya existe.';
        } else {
            $_SESSION['user_error'] = 'Error de base de datos: ' . $e->getMessage();
        }
    }
    header('Location: /liberty/usuario.php');
    exit;
}
?>