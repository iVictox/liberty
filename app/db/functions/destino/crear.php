<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $modalidad = $_POST['modalidad'] ?? '';
    $estado = $_POST['estado'] ?? 'Activo';

    if (empty($nombre) || empty($modalidad)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: Nombre y Modalidad son obligatorios.'];
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO Destino (Nombre, Modalidad, Estado) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $modalidad, $estado]);
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Destino registrado con éxito.'];
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al registrar el destino: ' . $e->getMessage()];
        }
    }
}
// Redirige de vuelta a la página de gestión
header('Location: /liberty/gestion_destinos.php');
exit;
?>
