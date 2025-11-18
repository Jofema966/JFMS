<?php
require_once __DIR__ . '/../session_helpers.php';
requireRole('socio');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard socio</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const perfilDiv = document.getElementById('perfil');
        const pagosTbody = document.getElementById('pagos');
        try {
            const perfil = await apiRequest('../api/socio.php?action=perfil');
            perfilDiv.innerHTML = `
                <p><strong>CI:</strong> ${perfil.Ci}</p>
                <p><strong>Nombre:</strong> ${perfil.Pnom} ${perfil.Pape}</p>
                <p><strong>Email:</strong> ${perfil.Email}</p>
                <p><strong>Fecha de nacimiento:</strong> ${perfil.FechNac}</p>
                <p><strong>Horas trabajadas:</strong> ${perfil.HorTrab}</p>
                <p><strong>ID socio:</strong> ${perfil.IDsocio}</p>
            `;
            const pagos = await apiRequest('../api/socio.php?action=mensualidades');
            if (!pagos.length) {
                pagosTbody.innerHTML = '<tr><td colspan="5">Sin pagos registrados</td></tr>';
            } else {
                pagosTbody.innerHTML = pagos.map(p => `
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
            perfilDiv.innerHTML = '<p>Error cargando datos.</p>';
            pagosTbody.innerHTML = '<tr><td colspan="5">Error cargando pagos</td></tr>';
        }
    });

    async function logout() {
        try { await apiRequest('../api/auth.php?action=logout', { method:'POST' }); } catch(e){}
        location.href = '../landing/index.php';
    }
    </script>
</head>
<body class="dashboard-page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Cooperativa</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active"><span class="icon">📊</span> Dashboard</a>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="nav-item logout" onclick="logout();return false;">
                <span class="icon">🚪</span> Cerrar sesión
            </a>
        </div>
    </aside>
    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Panel de socio</h1>
                <p>Bienvenido/a</p>
            </div>
        </header>

        <section class="card">
            <div class="card-header"><h2>Mi perfil</h2></div>
            <div id="perfil"></div>
        </section>

        <section class="card">
            <div class="card-header"><h2>Mensualidades</h2></div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Monto</th>
                            <th>Tipo pago</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="pagos"></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
