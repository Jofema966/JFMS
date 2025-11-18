<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de socios</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    async function cargarSocios() {
        const tbody = document.getElementById('tbody-socios');
        tbody.innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';
        try {
            const data = await apiRequest('../api/admin.php?action=list_socios');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="6">No hay socios</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(s => `
                <tr>
                    <td>${s.CiS}</td>
                    <td>${s.IDsocio}</td>
                    <td>${s.Pnom} ${s.Pape}</td>
                    <td>${s.Email}</td>
                    <td>${s.HorTrab}</td>
                    <td>
                        <button class="btn-secondary" onclick="verPagos(${s.CiS}, ${s.IDsocio})">Pagos</button>
                        <button class="btn-primary" onclick="banear(${s.CiS}, ${s.IDsocio})">Banear</button>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="6">Error cargando socios</td></tr>';
        }
    }

    async function banear(ci, idsocio) {
        if (!confirm('¿Banear este socio? Dejará de poder ingresar.')) return;
        try {
            await apiRequest('../api/admin.php?action=banear_socio', {
                method: 'POST',
                body: JSON.stringify({ ci: ci, idsocio: idsocio })
            });
            cargarSocios();
        } catch (err) {
            alert(err.message || err.error || 'Error al banear');
        }
    }

    async function verPagos(ci, idsocio) {
        const modal = document.getElementById('modal-pagos');
        const tbody = document.getElementById('tbody-pagos');
        modal.style.display = 'block';
        tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';
        try {
            const pagos = await apiRequest(`../api/admin.php?action=pagos_socio&ci=${ci}&idsocio=${idsocio}`);
            if (!pagos.length) {
                tbody.innerHTML = '<tr><td colspan="5">Sin pagos registrados</td></tr>';
            } else {
                tbody.innerHTML = pagos.map(p => `
                    <tr>
                        <td>${p.Comprobante}</td>
                        <td>${p.Monto}</td>
                        <td>${p.TipoPago}</td>
                        <td>${p.FechaPago}</td>
                        <td>${p.EstadoPago}</td>
                    </tr>
                `).join('');
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5">Error cargando pagos</td></tr>';
        }
    }

    function cerrarModal() {
        document.getElementById('modal-pagos').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', cargarSocios);
    </script>
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="icon">📊</span> Dashboard</a>
            <a href="solicitudes.php" class="nav-item"><span class="icon">✅</span> Solicitudes</a>
            <a href="socios.php" class="nav-item active"><span class="icon">👥</span> Socios</a>
            <a href="obras.php" class="nav-item"><span class="icon">🏗️</span> Obras</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Gestión de socios</h1>
            </div>
        </header>

        <section class="card">
            <div class="card-header">
                <h2>Socios activos</h2>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>CI</th>
                            <th>ID socio</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Horas trab.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-socios"></tbody>
                </table>
            </div>
        </section>

        <!-- Modal pagos -->
        <div id="modal-pagos" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.7);padding:2rem;">
            <div style="max-width:800px;margin:0 auto;background:#020617;border:1px solid #1f2937;border-radius:0.75rem;padding:1.5rem;">
                <div class="card-header">
                    <h2>Pagos del socio</h2>
                    <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Comprobante</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-pagos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
