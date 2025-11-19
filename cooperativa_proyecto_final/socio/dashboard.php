<?php
require_once __DIR__ . '/../config.php';
requireSocioPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de socio</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>Cooperativa</h1>
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="perfil.php" class="nav-link">Mi perfil</a>
        <a href="horas.php" class="nav-link">Horas trabajadas</a>
        <a href="pagos.php" class="nav-link">Pagos</a>
        <a href="vivienda.php" class="nav-link">Vivienda y obras</a>
        <a href="#" class="nav-link" onclick="logoutSocio(event)">Cerrar sesión</a>
    </aside>

    <main class="main">
        <h2 class="page-title">Panel de socio</h2>

        <p style="margin-bottom: 16px;">Resumen general de tu situación en la cooperativa.</p>

        <div class="grid grid-cols-3">
            <div class="card">
                <h2>Nombre</h2>
                <div id="card-nombre" class="stat-value">Cargando...</div>
            </div>
            <div class="card">
                <h2>Horas esta semana</h2>
                <div id="card-horas" class="stat-value">-</div>
            </div>
            <div class="card">
                <h2>Pagos registrados</h2>
                <div id="card-pagos" class="stat-value">-</div>
            </div>
        </div>

        <div class="card">
            <h2>Detalle</h2>
            <p id="dashboard-msg"></p>
        </div>
    </main>
</div>

<script>
async function cargarDashboard() {
    try {
        const res = await fetch('../api/socio.php?action=dashboard');
        const data = await res.json();

        if (data.status !== 'ok') {
            document.getElementById('card-nombre').textContent = 'Error cargando perfil';
            document.getElementById('card-horas').textContent  = '-';
            document.getElementById('card-pagos').textContent  = 'Error';
            document.getElementById('dashboard-msg').textContent = data.message || 'Error cargando información del panel.';
            return;
        }

        document.getElementById('card-nombre').textContent = data.data.nombre;
        document.getElementById('card-horas').textContent  = data.data.horas_semana;
        document.getElementById('card-pagos').textContent  = data.data.pagos_registrados;

        let msg = '';
        if (data.data.horas_semana < 21) {
            msg = 'Atención: tienes menos de 21 horas registradas esta semana.';
        } else {
            msg = 'Horas semanales dentro de lo esperado.';
        }
        document.getElementById('dashboard-msg').textContent = msg;

    } catch (e) {
        document.getElementById('card-nombre').textContent = 'Error cargando perfil';
        document.getElementById('card-horas').textContent  = '-';
        document.getElementById('card-pagos').textContent  = 'Error';
        document.getElementById('dashboard-msg').textContent = 'Error cargando información del panel.';
    }
}

cargarDashboard();
</script>

<script>
async function logoutSocio(event) {
    event.preventDefault();
    try {
        // Llama a TU API existente
        const res = await fetch('../api/auth.php?action=logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        // No necesitamos ni leer la respuesta, con que llegue basta
    } catch (e) {
        // Aunque falle la llamada, igual forzamos la salida
        console.error('Error llamando a logout:', e);
    }

    // Redirigimos al login de socio
    window.location.href = 'login.php';
}
</script>
</body>
</html>
