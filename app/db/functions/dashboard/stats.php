<?php
// /liberty/app/db/functions/dashboard/stats.php

/**
 * Obtiene el número total de paquetes registrados en la fecha actual (usando PDO).
 *
 * @param PDO $conn La conexión PDO a la base de datos.
 * @return int El número de paquetes registrados hoy.
 */
function getPaquetesRegistradosHoy(PDO $conn): int {
    // Obtiene la fecha actual en el formato Y-m-d
    $fechaHoy = date('Y-m-d');

    // Prepara la consulta
    $sql = "SELECT COUNT(*) FROM Paquete WHERE DATE(Fecha_Registro) = ?";
    
    try {
        // Prepara la sentencia
        $stmt = $conn->prepare($sql);
        
        // Ejecuta la sentencia pasando los parámetros en un array
        // '?' se reemplaza por $fechaHoy
        $stmt->execute([$fechaHoy]);
        
        // fetchColumn() es ideal para obtener un solo valor (como un COUNT)
        $total = $stmt->fetchColumn(); 
        
        // Devuelve el total (o 0 si no hay resultados)
        return $total !== false ? (int)$total : 0;

    } catch (PDOException $e) {
        // En caso de error, regístralo y devuelve 0
        error_log("Error en getPaquetesRegistradosHoy (PDO): " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtiene el número total de paquetes que tienen el estado 'En Sede' (usando PDO).
 *
 * @param PDO $conn La conexión PDO a la base de datos.
 * @return int El número de paquetes 'En Sede'.
 */
function getPaquetesEnSede(PDO $conn): int {
    // Define el estado que queremos contar
    $status = 'En Sede';

    // Prepara la consulta
    $sql = "SELECT COUNT(*) FROM Paquete WHERE Status = ?";
    
    try {
        // Prepara la sentencia
        $stmt = $conn->prepare($sql);
        
        // Ejecuta la sentencia
        $stmt->execute([$status]);
        
        // Obtiene el resultado con fetchColumn()
        $total = $stmt->fetchColumn();
        
        // Devuelve el total (o 0 si no hay resultados)
        return $total !== false ? (int)$total : 0;

    } catch (PDOException $e) {
        // En caso de error, regístralo y devuelve 0
        error_log("Error en getPaquetesEnSede (PDO): " . $e->getMessage());
        return 0;
    }
}
?>