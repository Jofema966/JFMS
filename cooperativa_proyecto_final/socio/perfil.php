<?php
require_once __DIR__ . '/../config.php';
requireSocioPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>Cooperativa</h1>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="perfil.php" class="nav-link active">Mi perfil</a>
        <a href="horas.php" class="nav-link">Horas trabajadas</a>
        <a href="pagos.php" class="nav-link">Pagos</a>
        <a href="vivienda.php" class="nav-link">Vivienda y obras</a>
        <a href="#" class="nav-link" onclick="logoutSocio(event)">Cerrar sesión</a>
    </aside>

    <main class="main">
        <h2 class="page-title">Mi perfil</h2>

        <div class="card">
            <h2>Datos personales</h2>
            <p id="perfil-error" style="color:#f87171; display:none;"></p>
            <p><strong>CI:</strong> <span id="perfil-ci"></span></p>
            <p><strong>Nombre:</strong> <span id="perfil-nombre"></span></p>
            <p><strong>Email:</strong> <span id="perfil-email"></span></p>
            <p><strong>Fecha de nacimiento:</strong> <span id="perfil-fecha"></span></p>
            <p><strong>ID socio:</strong> <span id="perfil-idsocio"></span></p>
            <p><strong>Horas trabajadas (última semana registrada):</strong> <span id="perfil-horas"></span></p>
        </div>
    </main>
</div>

<script>
async function cargarPerfil() {
    try {
        const res = await fetch('../api/socio.php?action=perfil');
        const data = await res.json();

        if (data.status !== 'ok') {
            document.getElementById('perfil-error').style.display = 'block';
            document.getElementById('perfil-error').textContent = data.message || 'Error cargando datos.';
            return;
        }

        const p = data.data;
        document.getElementById('perfil-ci').textContent      = p.Ci;
        document.getElementById('perfil-nombre').textContent  = p.Pnom + ' ' + p.Pape;
        document.getElementById('perfil-email').textContent   = p.Email;
        document.getElementById('perfil-fecha').textContent   = p.FechNac;
        document.getElementById('perfil-idsocio').textContent = p.IDsocio;
        document.getElementById('perfil-horas').textContent   = p.HorTrab;

    } catch (e) {
        document.getElementById('perfil-error').style.display = 'block';
        document.getElementById('perfil-error').textContent = 'Error cargando datos.';
    }
}

cargarPerfil();
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
