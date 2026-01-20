<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Lógica para OBTENER ESTADÍSTICAS CON NUEVOS STATUS
try {
    $total = $conn->query("SELECT COUNT(*) FROM Paquete")->fetchColumn();
    
    // Status para Ruta y Tienda
    $en_sede = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'En Sede'")->fetchColumn();
    $entregados = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'Entregado'")->fetchColumn();
    $devolucion = $conn->query("SELECT COUNT(*) FROM Paquete WHERE Status = 'Devolución'")->fetchColumn();

    // Status específico de Ruta
    $en_ruta = $conn->query("
        SELECT COUNT(*) 
        FROM Paquete p
        JOIN Destino d ON p.Destino_id = d.Destino_id
        WHERE p.Status = 'En Ruta' AND d.Modalidad = 'Ruta'
    ")->fetchColumn();

    // Status específico de Tienda
    $transferido = $conn->query("
        SELECT COUNT(*) 
        FROM Paquete p
        JOIN Destino d ON p.Destino_id = d.Destino_id
        WHERE p.Status = 'Transferido' AND d.Modalidad = 'Tienda'
    ")->fetchColumn();


} catch (PDOException $e) {
    die("Error al consultar estadísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Paquetes - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/dashboard.css"> 
</head>
<body>
    <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content">
            
            <section class="welcome-box">
                <h1 id='welcome-title'>Informes y Estadísticas</h1>
                <p>Resumen del estado actual de todos los paquetes.</p>
            </section>

            <div class="container-fluid py-2">
                <!-- Ajustamos el layout para 6 tarjetas -->
                <div class="row dashboard-stack" style="justify-content: center;">

                    <!-- 1. Total Paquetes -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Total Paquetes</p>
                                        <h4 class="mb-0"><?php echo $total; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark">
                                        <i class="fas fa-archive"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. En Sede (Nuevo) -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">En Sede</p>
                                        <h4 class="mb-0"><?php echo $en_sede; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark" style="background: #007bff;">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. En Ruta (Nuevo) -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">En Ruta</p>
                                        <h4 class="mb-0"><?php echo $en_ruta; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark" style="background: #ffc107;">
                                        <i class="fas fa-truck-moving"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 4. Transferido (Nuevo) -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Transferido (a Tienda)</p>
                                        <h4 class="mb-0"><?php echo $transferido; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark" style="background: #fd7e14;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 5. Entregados (Mantenido) -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Entregados</p>
                                        <h4 class="mb-0"><?php echo $entregados; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark" style="background: #28a745;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Devolución (Nuevo) -->
                    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-2 ps-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-sm mb-0 text-capitalize">Devolución</p>
                                        <h4 class="mb-0"><?php echo $devolucion; ?></h4>
                                    </div>
                                    <div class="icon icon-md icon-shape bg-gradient-dark" style="background: #dc3545;">
                                        <i class="fas fa-undo-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main> 
    </div> 
    <script src="/liberty/app/assets/js/sidebar.js"></script>
</body>
</html>
