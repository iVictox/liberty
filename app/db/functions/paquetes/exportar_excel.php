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

echo "<table>";
echo "<thead>";

// --- CORRECCIÓN: FECHA Y HORA AL INICIO ---
echo "<tr>";
echo "<th colspan='8' style='background-color: #f1f5f9; color: #333; text-align: center; border: 1px solid #ddd; height: 30px;'>";
echo "<strong>REPORTE OPERATIVO - GENERADO EL: " . date('d/m/Y h:i A') . "</strong>";
echo "</th>";
echo "</tr>";
// ------------------------------------------

echo "<tr style='background-color: #500101; color: white;'>";
echo "<th>Codigo</th>";
echo "<th>Fecha Registro</th>";
echo "<th>Origen</th>";
echo "<th>Destino</th>";
echo "<th>Tipo</th>";
echo "<th>Responsable</th>";
echo "<th>Status Logistico</th>";
echo "<th>Observaciones / Devolucion</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($resultados as $row) {
    echo "<tr>";
    echo "<td>" . $row['Codigo'] . "</td>";
    echo "<td>" . $row['Fecha_Registro'] . "</td>";
    echo "<td>" . mb_convert_encoding($row['Origen'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "<td>" . mb_convert_encoding($row['Destino'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "<td>" . $row['Tipo'] . "</td>";
    echo "<td>" . mb_convert_encoding($row['Responsable'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "<td>" . $row['Status'] . "</td>";
    echo "<td>" . mb_convert_encoding($row['Motivo_Devolucion'], 'HTML-ENTITIES', 'UTF-8') . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
exit;
?>