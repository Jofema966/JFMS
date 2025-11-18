<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login admin</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('adminLoginForm');
        const msg  = document.getElementById('msg');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msg.textContent = '';
            const email = form.email.value;
            const password = form.password.value;
            try {
                const res = await apiRequest('../api/auth.php?action=login_admin', {
                    method: 'POST',
                    body: JSON.stringify({ email, password })
                });
                if (res.success) {
                    location.href = 'dashboard.php';
                } else {
                    msg.textContent = res.message || 'Error de login';
                }
            } catch (err) {
                msg.textContent = err.message || err.error || 'Error de servidor';
            }
        });
    });
    </script>
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-box admin-box">
        <div class="login-header">
            <h2>Panel de Administración</h2>
            <p>Acceso exclusivo para administradores</p>
        </div>
        <form id="adminLoginForm" class="login-form">
            <div class="form-group">
                <label>Correo / Usuario</label>
                <input type="text" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña (numérica)</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Acceder al panel</button>
        </form>
        <p id="msg" style="margin-top:1rem;color:#f87171;"></p>
        <div class="login-footer">
            <p><a href="../landing/index.php">← Volver a inicio</a></p>
        </div>
    </div>
    <div class="login-info">
        <h3>Panel de Administración</h3>
        <ul>
            <li>🔐 Gestión de socios</li>
            <li>📊 Control de pagos</li>
            <li>🏗️ Obras en construcción</li>
        </ul>
        <div class="security-notice">
            <strong>Área restringida</strong>
            <p>Solo personal autorizado. Todos los accesos quedan registrados.</p>
        </div>
    </div>
</div>
</body>
</html>
