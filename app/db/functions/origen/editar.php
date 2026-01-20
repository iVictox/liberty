<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// 1. Obtener el ID de la URL (que envía el JavaScript)
$id = $_GET['id'] ?? null; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && $id) {
    // 2. Obtener el nombre del formulario
    $nombre = $_POST['nombre'] ?? '';

    if (empty($nombre)) {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: El nombre es obligatorio.'];
    } else {
        try {
            // 3. Esta es la consulta SQL correcta: UPDATE
            $stmt = $conn->prepare("UPDATE Origen SET Nombre = ? WHERE Origen_id = ?");
            
            // 4. Ejecutar con ambos parámetros: el nombre nuevo y el ID
            $stmt->execute([$nombre, $id]);
            
            $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => 'Origen actualizado con éxito.'];
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al actualizar el origen: ' . $e->getMessage()];
        }
    }
} else if (!$id) {
    // Manejo de error si no se proporciona un ID
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error: No se proporcionó ID para editar.'];
}

// Redirige de vuelta a la página de gestión
header('Location: /liberty/gestion_origenes.php'); 
exit;
?>
