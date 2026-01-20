<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/functions/auditoria/registrar.php'); // Incluir auditoría

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Acceso no autorizado.'];
    header('Location: /paquetes/gestion.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['paquetes_json']) && !empty($_POST['paquetes_json'])) {
        
        $paquetes = json_decode($_POST['paquetes_json'], true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($paquetes)) {
            
            $usuario_id = $_SESSION['user_id'];
            $status_inicial = 'En Sede';
            $count_exito = 0;
            $count_error = 0;
            $errores_detalles = [];
            $codigos_registrados = [];

            $sql = "INSERT INTO Paquete (Codigo, Origen_id, Fecha_Registro, Tipo_Destino_ID, Destino_id, Usuario_id, Status) 
                    VALUES (?, ?, NOW(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            $conn->beginTransaction();

            foreach ($paquetes as $p) {
                $codigo = $p['codigo'];
                $origen_id = $p['origen_id'];
                $tipo_destino = $p['tipo_destino_varchar'];
                $destino_id = $p['destino_id'];

                try {
                    $stmt->execute([$codigo, $origen_id, $tipo_destino, $destino_id, $usuario_id, $status_inicial]);
                    $count_exito++;
                    $codigos_registrados[] = $codigo;
                } catch (PDOException $e) {
                    $count_error++;
                    if ($e->errorInfo[1] == 1062) {
                        $errores_detalles[] = "Código $codigo ya existe.";
                    } else {
                        $errores_detalles[] = "Error en $codigo.";
                    }
                }
            }

            $conn->commit();

            if ($count_error == 0) {
                // --- AUDITORÍA ---
                $detalles = "Lote de $count_exito paquetes. Códigos: " . implode(", ", $codigos_registrados);
                registrarAuditoria($conn, $usuario_id, "Registro Lote", substr($detalles, 0, 500)); // Cortamos si es muy largo

                $_SESSION['mensaje'] = ['tipo' => 'exito', 'texto' => "Lote registrado: $count_exito paquetes creados exitosamente."];
            } else {
                // Auditoría parcial
                if($count_exito > 0) {
                    $detalles = "Registro parcial ($count_exito éxitos). Fallos: " . implode(", ", $errores_detalles);
                    registrarAuditoria($conn, $usuario_id, "Registro Lote (Parcial)", substr($detalles, 0, 500));
                }
                
                $msg = "Se registraron $count_exito paquetes. Fallaron $count_error. (" . implode(", ", $errores_detalles) . ")";
                $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => $msg];
            }

        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al procesar los datos del lote.'];
        }
    } else {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se recibieron paquetes para registrar.'];
    }
} else {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Método no permitido.'];
}

header('Location: /liberty/paquetes/gestion.php');
exit;
?>