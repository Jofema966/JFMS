<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'socio') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login socio</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('loginForm');
        const msg  = document.getElementById('msg');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msg.textContent = '';
            const email = form.email.value;
            const password = form.password.value;
            try {
                const res = await apiRequest('../api/auth.php?action=login_socio', {
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
    <div class="login-box">
        <div class="login-header">
            <h2>Ingreso de socio</h2>
            <p>Accede a tu panel personal</p>
        </div>
        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña (numérica)</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        <p id="msg" style="margin-top:1rem;color:#f87171;"></p>
        <div class="login-footer">
            <p>¿No tienes una cuenta? <a href="registro.php">Solicitar membresía</a></p>
        </div>
    </div>
    <div class="login-info">
        <h3>Panel de miembro</h3>
        <ul>
            <li>✓ Ver tus datos personales</li>
            <li>✓ Consultar tus mensualidades</li>
            <li>✓ Ver estado de tus pagos</li>
        </ul>
    </div>
</div>
</body>
</html>
