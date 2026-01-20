<?php
// Detectar la página actual por su nombre de archivo
$current = basename($_SERVER['PHP_SELF'] ?? '');

function is_active($file, $current){ 
    return $file === $current ? 'active-link' : ''; 
}

function is_active_parent($files = [], $current){
    foreach ($files as $file) {
        if ($file === $current) return 'active-link';
    }
    return '';
}

$paquetes_pages = ['gestion.php', 'informe.php'];
$ubicaciones_pages = ['gestion_origenes.php', 'gestion_destinos.php'];

// --- VERIFICACIÓN DE ROL ---
// Asumimos que Rol 3 es 'Administrador'
$esAdmin = (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 3);
?>

<link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<button class="toggle-btn" aria-label="Toggle menu" aria-expanded="false">☰</button>

<nav class="sidebar" aria-label="Sidebar navigation">
    
    <a href="/liberty/perfil.php" class="brand brand-link <?php echo is_active('perfil.php', $current); ?>">
        <img src="/liberty/app/assets/img/logo-le.png" alt="Liberty Express - Logo" class="logo">
        <div>
            <div style="font-weight:600">Liberty Express</div>
            <div style="font-size:12px;opacity:0.8"><?php echo $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido']; ?></div>
            <div style="font-size:11px;opacity:0.6; margin-top:2px;">
                <?php echo ($esAdmin) ? 'Administrador' : 'Empleado'; ?>
            </div>
        </div>
    </a>

    <div class="nav">
        
        <a href="/liberty/" class="<?php echo is_active('index.php', $current); ?>">
            <span class="icon"><i class="fas fa-home"></i></span>
            <span>Inicio</span>
        </a>

        <div class="nav-item">
            <a href="/liberty/#" class="nav-toggle <?php echo is_active_parent($paquetes_pages, $current); ?>">
                <span class="icon"><i class="fas fa-boxes-stacked"></i></span>
                <span>Paquetes</span>
                <span class="arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <div class="sub-nav">
                <a href="/liberty/paquetes/gestion.php" class="<?php echo is_active('gestion.php', $current); ?>">
                    <span class="icon"><i class="fas fa-folder-open"></i></span>
                    <span>Gestionar</span>
                </a>
                <a href="/liberty/paquetes/informe.php" class="<?php echo is_active('informe.php', $current); ?>">
                    <span class="icon"><i class="fas fa-chart-line"></i></span>
                    <span>Informe</span>
                </a>
            </div>
        </div>

        <?php if($esAdmin): ?>
        <div class="nav-item">
            <a href="/liberty/#" class="nav-toggle <?php echo is_active_parent($ubicaciones_pages, $current); ?>">
                <span class="icon"><i class="fas fa-map-signs"></i></span>
                <span>Ubicaciones</span>
                <span class="arrow"><i class="fas fa-chevron-down"></i></span>
            </a>
            <div class="sub-nav">
                <a href="/liberty/gestion_origenes.php" class="<?php echo is_active('gestion_origenes.php', $current); ?>">
                    <span class="icon"><i class="fas fa-map-pin"></i></span>
                    <span>Orígenes</span>
                </a>
                <a href="/liberty/gestion_destinos.php" class="<?php echo is_active('gestion_destinos.php', $current); ?>">
                    <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                    <span>Destinos</span>
                </a>
            </div>
        </div>
        
        <a href="/liberty/usuario.php" class="<?php echo is_active('usuario.php', $current); ?>">
            <span class="icon"><i class="fas fa-users-cog"></i></span>
            <span>Gestión de Usuarios</span>
        </a>

        <a href="/liberty/auditoria.php" class="<?php echo is_active('auditoria.php', $current); ?>">
            <span class="icon"><i class="fas fa-history"></i></span>
            <span>Auditoría</span>
        </a>
        <?php endif; ?>

        <a href="/liberty/foro.php" class="<?php echo is_active('foro.php', $current); ?>">
            <span class="icon"><i class="fas fa-comments"></i></span>
            <span>Foro Interno</span>
        </a>
        
        <a href="/liberty/perfil.php" class="<?php echo is_active('perfil.php', $current); ?>">
            <span class="icon"><i class="fas fa-user-circle"></i></span>
            <span>Mi Perfil</span>
        </a>
    </div>

    <div class="spacer"></div>

    <div class="nav-footer">
        <a href="/liberty/ayuda.php" class="<?php echo is_active('ayuda.php', $current); ?>">
            <span class="icon"><i class="fas fa-question-circle"></i></span>
            <span>Ayuda</span>
        </a>
        <a href="/liberty/cerrar.php" class="logout-link">
            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
            <span>Cerrar sesión</span>
        </a>
    </div>
</nav>