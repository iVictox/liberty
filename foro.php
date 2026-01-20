<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /liberty/login.php');
    exit;
}
include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/db/connect.php');

// Función de tiempo
function tiempoChat($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Ahora';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) {
        return date('h:i A', $time); // Hora si es hoy
    }
    return date('d/m h:i A', $time); // Fecha y hora si es antiguo
}

// Cargar más mensajes (100) para que se sienta como un historial
$sql = "SELECT f.*, u.nombre, u.apellido 
        FROM foro_mensajes f 
        JOIN usuario u ON f.usuario_id = u.id 
        ORDER BY f.fecha ASC LIMIT 100"; // Orden ASC para que los nuevos queden abajo (estilo chat)
$mensajes = $conn->query($sql)->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat de Equipo - Liberty Express</title>
    <link rel="stylesheet" href="/liberty/app/assets/css/sidebar.css">
    <link rel="stylesheet" href="/liberty/app/assets/css/forum.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="height: 100vh; overflow: hidden;"> <div class="app-wrap">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/liberty/app/includes/menu.php'); ?>
        
        <main class="main-content" style="height: 100vh; padding: 20px; box-sizing: border-box; display: flex; flex-direction: column;">
            
            <div class="forum-container">
                <div class="chat-header">
                    <div>
                        <h2 class="chat-title"><i class="fas fa-hashtag"></i> Novedades Operativas</h2>
                        <span class="chat-subtitle"><?php echo count($mensajes); ?> mensajes recientes</span>
                    </div>
                </div>

                <div class="chat-feed" id="chatFeed">
                    <?php if (empty($mensajes)): ?>
                        <div style="text-align: center; padding: 40px; color: #94a3b8;">
                            <i class="far fa-comments fa-2x"></i><br>
                            El chat está vacío. ¡Escribe el primer mensaje!
                        </div>
                    <?php else: ?>
                        <?php foreach ($mensajes as $msg): ?>
                            <div class="chat-message">
                                <div class="chat-avatar">
                                    <?php echo strtoupper(substr($msg->nombre, 0, 1) . substr($msg->apellido, 0, 1)); ?>
                                </div>
                                <div class="chat-content">
                                    <div class="chat-meta">
                                        <span class="chat-user"><?php echo htmlspecialchars($msg->nombre . ' ' . $msg->apellido); ?></span>
                                        <span class="chat-time"><?php echo tiempoChat($msg->fecha); ?></span>
                                    </div>
                                    <div class="chat-text"><?php echo nl2br(htmlspecialchars($msg->mensaje)); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input-area">
                    <form action="/liberty/app/db/functions/foro/publicar.php" method="POST">
                        <input type="hidden" name="origen" value="foro">
                        <div class="input-wrapper">
                            <textarea name="mensaje" class="chat-textarea" placeholder="Enviar mensaje a todos..." required></textarea>
                            <button type="submit" class="btn-send-chat">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script src="/liberty/app/assets/js/sidebar.js"></script>
    <script>
        // Auto-scroll al fondo al cargar
        window.onload = function() {
            var feed = document.getElementById("chatFeed");
            feed.scrollTop = feed.scrollHeight;
        };
    </script>
</body>
</html>