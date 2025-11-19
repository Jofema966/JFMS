<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro socio</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('regForm');
        const msg  = document.getElementById('msg');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msg.textContent = '';
            const data = {
                ci: form.ci.value,
                pnom: form.pnom.value,
                pape: form.pape.value,
                fechnac: form.fechnac.value,
                email: form.email.value,
                password: form.password.value
            };
            try {
                const res = await apiRequest('../api/auth.php?action=registro', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                msg.style.color = '#4ade80';
                msg.textContent = res.message || 'Solicitud enviada';
                form.reset();
            } catch (err) {
                msg.style.color = '#f87171';
                msg.textContent = err.message || err.error || 'Error al registrar';
            }
        });
    });
    </script>
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h2>Solicitud de membresía</h2>
            <p>Completa tus datos para que el administrador revise tu solicitud.</p>
        </div>
        <form id="regForm" class="login-form">
            <div class="form-group">
                <label>CI</label>
                <input type="number" name="ci" required>
            </div>
            <div class="form-group">
                <label>Primer nombre</label>
                <input type="text" name="pnom" required>
            </div>
            <div class="form-group">
                <label>Primer apellido</label>
                <input type="text" name="pape" required>
            </div>
            <div class="form-group">
                <label>Fecha de nacimiento</label>
                <input type="date" name="fechnac" required>
            </div>
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña numérica</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Enviar solicitud</button>
        </form>
        <p id="msg" style="margin-top:1rem;"></p>
        <div class="login-footer">
            <p>¿Ya eres socio? <a href="login.php">Iniciar sesión</a></p>
        </div>
    </div>
</div>
</body>
</html>
