<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes de membresía</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    async function cargarSolicitudes() {
        const tbody = document.getElementById('tbody-solicitudes');
        tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';
        try {
            const data = await apiRequest('../api/admin.php?action=list_solicitudes');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="5">No hay solicitudes pendientes</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(s => `
                <tr>
                    <td>${s.CiU}</td>
                    <td>${s.Pnom} ${s.Pape}</td>
                    <td>${s.Email}</td>
                    <td>${s.FechNac}</td>
                    <td>
                        <button class="btn-primary" onclick="aprobar(${s.CiU})">Aprobar</button>
                        <button class="btn-secondary" onclick="rechazar(${s.CiU})">Rechazar</button>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5">Error cargando solicitudes</td></tr>';
        }
    }

    async function aprobar(ci) {
        if (!confirm('¿Aprobar a este usuario como socio?')) return;
        try {
            await apiRequest('../api/admin.php?action=aprobar_socio', {
                method: 'POST',
                body: JSON.stringify({ ci: ci })
            });
            cargarSolicitudes();
        } catch (err) {
            alert(err.message || err.error || 'Error al aprobar');
        }
    }

    async function rechazar(ci) {
        if (!confirm('¿Rechazar esta solicitud? Se eliminará el registro.')) return;
        try {
            await apiRequest('../api/admin.php?action=rechazar_socio', {
                method: 'POST',
                body: JSON.stringify({ ci: ci })
            });
            cargarSolicitudes();
        } catch (err) {
            alert(err.message || err.error || 'Error al rechazar');
        }
    }

    document.addEventListener('DOMContentLoaded', cargarSolicitudes);
    </script>
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="icon">📊</span> Dashboard</a>
            <a href="solicitudes.php" class="nav-item active"><span class="icon">✅</span> Solicitudes</a>
            <a href="socios.php" class="nav-item"><span class="icon">👥</span> Socios</a>
            <a href="obras.php" class="nav-item"><span class="icon">🏗️</span> Obras</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Solicitudes de membresía</h1>
            </div>
        </header>

        <section class="card">
            <div class="card-header">
                <h2>Pendientes</h2>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>CI</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha nac.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-solicitudes"></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
