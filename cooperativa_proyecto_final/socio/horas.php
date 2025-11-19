<?php
require_once __DIR__ . '/../config.php';
requireSocioPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horas trabajadas</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>Cooperativa</h1>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="perfil.php" class="nav-link">Mi perfil</a>
        <a href="horas.php" class="nav-link active">Horas trabajadas</a>
        <a href="pagos.php" class="nav-link">Pagos</a>
        <a href="vivienda.php" class="nav-link">Vivienda y obras</a>
        <a href="#" class="nav-link" onclick="logoutSocio(event)">Cerrar sesión</a>
    </aside>

    <main class="main">
        <h2 class="page-title">Horas trabajadas</h2>

        <div class="card">
            <h2>Estado actual</h2>
            <p>Horas registradas esta semana: <strong id="horas-actual">0</strong></p>
        </div>

        <div class="card">
            <h2>Actualizar horas</h2>
            <p id="horas-msg" style="margin-bottom: 8px;"></p>
            <form id="form-horas">
                <label for="input-horas">Horas de esta semana:</label><br>
                <input id="input-horas" name="horas" type="number" min="0" class="input" style="margin:8px 0; width:200px;">
                <br>
                <button type="submit" class="button">Guardar</button>
            </form>
        </div>
    </main>
</div>

<script>
async function cargarHoras() {
    try {
        const res = await fetch('../api/socio.php?action=get_horas');
        const data = await res.json();
        if (data.status === 'ok') {
            document.getElementById('horas-actual').textContent = data.data.horas;
        } else {
            document.getElementById('horas-msg').textContent = 'Error cargando horas.';
        }
    } catch (e) {
        document.getElementById('horas-msg').textContent = 'Error cargando horas.';
    }
}

document.getElementById('form-horas').addEventListener('submit', async (e) => {
    e.preventDefault();
    const horas = document.getElementById('input-horas').value;

    const formData = new FormData();
    formData.append('horas', horas);

    try {
        const res = await fetch('../api/socio.php?action=set_horas', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'ok') {
            document.getElementById('horas-msg').textContent = 'Horas actualizadas.';
            cargarHoras();
        } else {
            document.getElementById('horas-msg').textContent = data.message || 'Error al guardar horas.';
        }
    } catch (e) {
        document.getElementById('horas-msg').textContent = 'Error al guardar horas.';
    }
});

cargarHoras();
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
