<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    if (empty($nombre)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: El nombre es obligatorio.'];
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO Origen (Nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Origen registrado con éxito.'];
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al registrar el origen: ' . $e->getMessage()];
        }
    }
}
header('Location: /liberty/gestion_origenes.php'); // Redirige al UI de Orígenes
exit;
?>
