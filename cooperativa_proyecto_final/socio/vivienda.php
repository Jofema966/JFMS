<?php
require_once __DIR__ . '/../config.php';
requireSocioPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vivienda y obras</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>Cooperativa</h1>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="perfil.php" class="nav-link">Mi perfil</a>
        <a href="horas.php" class="nav-link">Horas trabajadas</a>
        <a href="pagos.php" class="nav-link">Pagos</a>
        <a href="vivienda.php" class="nav-link active">Vivienda y obras</a>
        <a href="#" class="nav-link" onclick="logoutSocio(event)">Cerrar sesión</a>
    </aside>

    <main class="main">
        <h2 class="page-title">Vivienda y obras asignadas</h2>

        <div class="card">
            <h2>Vivienda asignada</h2>
            <p id="vivienda-texto">Cargando...</p>
        </div>

        <div class="card">
            <h2>Obras actuales</h2>
            <p id="obras-error" style="color:#f87171; margin-bottom:8px;"></p>
            <table class="table">
                <thead>
                <tr>
                    <th>ID obra</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Fechas</th>
                </tr>
                </thead>
                <tbody id="obras-tbody">
                <tr><td colspan="4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
async function cargarViviendaYObras() {
    try {
        const res = await fetch('../api/socio.php?action=vivienda_obras');
        const data = await res.json();

        if (data.status !== 'ok') {
            document.getElementById('vivienda-texto').textContent = 'Error cargando vivienda.';
            document.getElementById('obras-error').textContent = data.message || 'Error cargando obras.';
            return;
        }

        const v = data.data.vivienda;
        if (v) {
            document.getElementById('vivienda-texto').textContent =
                `Bloque ${v.Bloque}, puerta ${v.NumPuerta}`;
        } else {
            document.getElementById('vivienda-texto').textContent = 'Aún no tienes vivienda asignada.';
        }

        const tbody = document.getElementById('obras-tbody');
        tbody.innerHTML = '';

        const obras = data.data.obras || [];
        if (!obras.length) {
            tbody.innerHTML = '<tr><td colspan="4">No hay obras registradas.</td></tr>';
            return;
        }

        for (const o of obras) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${o.IDobr}</td>
                <td>${o.TipoObr}</td>
                <td>${o.Estado}</td>
                <td>${o.FechInicio} - ${o.FechFin}</td>
            `;
            tbody.appendChild(tr);
        }

    } catch (e) {
        document.getElementById('vivienda-texto').textContent = 'Error cargando vivienda.';
        document.getElementById('obras-error').textContent = 'Error cargando obras.';
        document.getElementById('obras-tbody').innerHTML = '<tr><td colspan="4">Error</td></tr>';
    }
}

cargarViviendaYObras();
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
