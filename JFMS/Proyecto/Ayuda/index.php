<?php
session_start();

// Si no hay sesión activa, redirigir a la página intermedia
if (!isset($_SESSION['Pnom'])) {
    header('Location: redirigir.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <div class="welcome-container">
        <h3><?php echo "Bienvenido, " . htmlspecialchars($_SESSION['Pnom']); ?></h3>
        <a href="login/logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
</body>
</html>
