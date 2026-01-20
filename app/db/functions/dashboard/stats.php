<?php
// /liberty/app/db/functions/dashboard/stats.php

/* ---------------------------------------------------------
   SECCIÓN 1: MÉTRICAS DE VOLUMEN (PAQUETES REGISTRADOS)
   --------------------------------------------------------- */

function getIngresosHoy(PDO $conn): int {
    $sql = "SELECT COUNT(*) FROM Paquete WHERE DATE(Fecha_Registro) = CURDATE()";
    return (int) $conn->query($sql)->fetchColumn();
}

function getIngresosAyer(PDO $conn): int {
    // SUBDATE(CURDATE(), 1) devuelve la fecha de ayer
    $sql = "SELECT COUNT(*) FROM Paquete WHERE DATE(Fecha_Registro) = SUBDATE(CURDATE(), 1)";
    return (int) $conn->query($sql)->fetchColumn();
}

/* ---------------------------------------------------------
   SECCIÓN 2: MÉTRICAS DE INVENTARIO (EN SEDE)
   --------------------------------------------------------- */

function getPaquetesEnSede(PDO $conn): int {
    $sql = "SELECT COUNT(*) FROM Paquete WHERE Status = 'En Sede' AND Estado = 1";
    return (int) $conn->query($sql)->fetchColumn();
}

// Obtener el total activo para calcular qué % del inventario está en sede
function getTotalPaquetesActivos(PDO $conn): int {
    $sql = "SELECT COUNT(*) FROM Paquete WHERE Estado = 1";
    return (int) $conn->query($sql)->fetchColumn();
}

/* ---------------------------------------------------------
   SECCIÓN 3: MÉTRICAS DE TRÁNSITO (EN RUTA)
   --------------------------------------------------------- */

function getPaquetesEnRuta(PDO $conn): int {
    // Filtramos también por la modalidad del destino si es necesario, 
    // pero por ahora 'En Ruta' es un status global.
    $sql = "SELECT COUNT(*) FROM Paquete WHERE Status = 'En Ruta' AND Estado = 1";
    return (int) $conn->query($sql)->fetchColumn();
}

/* ---------------------------------------------------------
   SECCIÓN 4: MÉTRICAS DE ÉXITO (ENTREGADOS)
   --------------------------------------------------------- */

function getEntregadosEsteMes(PDO $conn): int {
    // Contamos paquetes con status Entregado registrados este mes
    // Nota: Lo ideal sería usar una tabla de historial de cambios de status, 
    // pero usaremos Fecha_Registro como aproximación para este MVP.
    $sql = "SELECT COUNT(*) FROM Paquete 
            WHERE Status = 'Entregado' 
            AND MONTH(Fecha_Registro) = MONTH(CURRENT_DATE())
            AND YEAR(Fecha_Registro) = YEAR(CURRENT_DATE())";
    return (int) $conn->query($sql)->fetchColumn();
}

function getEntregadosMesAnterior(PDO $conn): int {
    $sql = "SELECT COUNT(*) FROM Paquete 
            WHERE Status = 'Entregado' 
            AND MONTH(Fecha_Registro) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
            AND YEAR(Fecha_Registro) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
    return (int) $conn->query($sql)->fetchColumn();
}

/* ---------------------------------------------------------
   HELPER: CALCULO DE PORCENTAJE DE CAMBIO
   --------------------------------------------------------- */
function calcularTendencia($actual, $anterior) {
    if ($anterior == 0) {
        // Si antes era 0 y ahora tengo algo, es un aumento del 100%
        return $actual > 0 ? 100 : 0; 
    }
    
    $diferencia = $actual - $anterior;
    $porcentaje = ($diferencia / $anterior) * 100;
    
    return round($porcentaje, 1); // Retorna con 1 decimal (ej: 15.5)
}
?>