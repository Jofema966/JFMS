<?php
// socio/obras.php
require_once __DIR__ . '/../session_helpers.php';
requireSocio();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Obras y vivienda - Socio</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer>
async function apiRequest(url, options = {}) {
    const opts = {
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        ...options
    };
    const res = await fetch(url, opts);
    const data = await res.json();
    if (!res.ok || data.error) throw data;
    return data;
}

document.addEventListener('DOMContentLoaded', () => {
    cargarObras();
    cargarVivienda();
});

async function cargarObras() {
    const tbody = document.querySelector('#tabla-obras tbody');
    tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';
    try {
        const obras = await apiRequest('../api/socio.php?action=obras');
        tbody.innerHTML = '';
        if (!obras.length) {
            tbody.innerHTML = '<tr><td colspan="5">No hay obras registradas.</td></tr>';
            return;
        }
        obras.forEach(o => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${o.IDobr}</td>
                <td>${o.TipoObr}</td>
                <td>${o.Estado}</td>
                <td>${o.FechInicio}</td>
                <td>${o.FechFin}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="5">Error al cargar obras.</td></tr>';
    }
}

async function cargarVivienda() {
    const cont = document.getElementById('mi-vivienda');
    cont.innerHTML = 'Cargando...';
    try {
        const viv = await apiRequest('../api/socio.php?action=mi_vivienda');
        if (!viv.length) {
            cont.innerHTML = 'Todavía no tienes una vivienda asignada.';
            return;
        }
        const v = viv[0]; // si hubiera varias, mostramos la primera
        cont.innerHTML = `
            <p><strong>Bloque:</strong> ${v.Bloque}</p>
            <p><strong>Nº puerta:</strong> ${v.NumPuerta}</p>
            <p><strong>Administrador:</strong> ${v.AdminNombre} ${v.AdminApellido} (CI ${v.CiA})</p>
        `;
    } catch (err) {
        console.error(err);
        cont.innerHTML = 'Error al cargar tu vivienda.';
    }
}
    </script>
</head>
<body class="dashboard-page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Cooperativa</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="icon">📊</span> Dashboard</a>
            <a href="pagos.php" class="nav-item"><span class="icon">💰</span> Pagos</a>
            <a href="obras.php" class="nav-item active"><span class="icon">🏗️</span> Obras y vivienda</a>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="nav-item logout"
               onclick="apiRequest('../api/auth.php?action=logout',{method:'POST'})
                        .then(()=>location.href='../landing/index.php');return false;">
                <span class="icon">🚪</span> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Obras y vivienda</h1>
                <p>Consulta las obras en curso y la vivienda que te fue asignada.</p>
            </div>
        </header>

        <section class="card">
            <div class="card-header">
                <h2>Mi vivienda</h2>
            </div>
            <div id="mi-vivienda" class="p-2">
                <!-- se rellena por JS -->
            </div>
        </section>

        <section class="card full-width">
            <div class="card-header">
                <h2>Obras registradas</h2>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-obras">
                    <thead>
                        <tr>
                            <th>ID Obra</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
