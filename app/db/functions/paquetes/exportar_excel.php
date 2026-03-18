<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("Acceso denegado");
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$origen_id = $_GET['origen_id'] ?? '';
$tipo_destino = $_GET['tipo_destino'] ?? '';

// Headers para descarga
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_liberty_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "
    SELECT 
        p.Codigo, 
        p.Fecha_Registro, 
        p.Status, 
        p.Motivo_Devolucion,
        o.Nombre AS Origen,
        d.Nombre AS Destino,
        d.Modalidad AS Tipo,
        CONCAT(u.nombre, ' ', u.apellido) AS Responsable
    FROM Paquete p
    JOIN Origen o ON p.Origen_id = o.Origen_id
    JOIN Destino d ON p.Destino_id = d.Destino_id
    JOIN usuario u ON p.Usuario_id = u.id
    WHERE DATE(p.Fecha_Registro) BETWEEN ? AND ?
";

$params = [$fecha_inicio, $fecha_fin];

if (!empty($status)) {
    $sql .= " AND p.Status = ?";
    $params[] = $status;
}
if (!empty($origen_id)) {
    $sql .= " AND p.Origen_id = ?";
    $params[] = $origen_id;
}
if (!empty($tipo_destino)) {
    $sql .= " AND d.Modalidad = ?";
    $params[] = $tipo_destino;
}

$sql .= " ORDER BY p.Fecha_Registro DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estructura de tabla mejorada para Excel
echo "<table border='1' cellpadding='5' cellspacing='0' style='font-family: Arial, sans-serif; border-collapse: collapse; width: 100%;'>";
echo "<thead>";

// Título principal del reporte
echo "<tr>";
echo "<th colspan='8' style='background-color: #f1f5f9; color: #333; text-align: center; font-size: 16px; height: 40px;'>";
echo "<strong>REPORTE OPERATIVO - LIBERTY EXPRESS <br> GENERADO EL: " . date('d/m/Y h:i A') . "</strong>";
echo "</th>";
echo "</tr>";

// Cabeceras de columnas
echo "<tr>";
$estiloTh = "background-color: #500101; color: white; font-weight: bold; text-align: center; height: 30px;";
echo "<th style='$estiloTh'>Codigo</th>";
echo "<th style='$estiloTh'>Fecha Registro</th>";
echo "<th style='$estiloTh'>Origen</th>";
echo "<th style='$estiloTh'>Destino</th>";
echo "<th style='$estiloTh'>Tipo</th>";
echo "<th style='$estiloTh'>Responsable</th>";
echo "<th style='$estiloTh'>Status Logistico</th>";
echo "<th style='$estiloTh'>Observaciones / Devolucion</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

// Filas de datos
foreach ($resultados as $row) {
    echo "<tr>";
    echo "<td style='text-align: center; vertical-align: middle;'>" . $row['Codigo'] . "</td>";
    echo "<td style='text-align: center; vertical-align: middle;'>" . $row['Fecha_Registro'] . "</td>";
    echo "<td style='vertical-align: middle; padding-left: 5px;'>" . mb_convert_encoding($row['Origen'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "<td style='vertical-align: middle; padding-left: 5px;'>" . mb_convert_encoding($row['Destino'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "<td style='text-align: center; vertical-align: middle;'>" . $row['Tipo'] . "</td>";
    echo "<td style='vertical-align: middle; padding-left: 5px;'>" . mb_convert_encoding($row['Responsable'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    
    // Colores dinámicos para el status
    $colorStatus = ($row['Status'] == 'Entregado') ? '#0f5132' : '#333';
    $bgStatus = ($row['Status'] == 'Entregado') ? '#d1e7dd' : 'transparent';
    echo "<td style='text-align: center; vertical-align: middle; font-weight: bold; color: $colorStatus; background-color: $bgStatus;'>" . $row['Status'] . "</td>";
    
    echo "<td style='vertical-align: middle;'>" . mb_convert_encoding($row['Motivo_Devolucion'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
exit;
?>