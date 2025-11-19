<?php
// admin/socios.php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Socios - Admin</title>
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
    cargarSocios();

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-aviso')) {
            const nombre = e.target.dataset.nombre;
            const horas  = e.target.dataset.horas;
            const email  = e.target.dataset.email;

            const msg = `
Aviso de horas trabajadas

Estimado/a ${nombre},
según los registros de la cooperativa, esta semana llevas registradas ${horas} horas de trabajo.
Recordá que el mínimo esperado es de 21 horas semanales.

Saludos,
Administración de la cooperativa
`.trim();

            alert(
                'Mensaje sugerido para enviar al socio (por mail, WhatsApp, etc.):\n\n' +
                msg +
                (email ? '\n\nCorreo del socio: ' + email : '')
            );
        }
    });
});

async function cargarSocios() {
    const tbody = document.querySelector('#tabla-socios tbody');
    tbody.innerHTML = '<tr><td colspan="9">Cargando...</td></tr>';
    try {
        const socios = await apiRequest('../api/admin_viviendas.php?action=list_socios');
        tbody.innerHTML = '';
        if (!socios.length) {
            tbody.innerHTML = '<tr><td colspan="9">No hay socios registrados.</td></tr>';
            return;
        }

        socios.forEach(s => {
            const tr = document.createElement('tr');

            const horas   = parseInt(s.HorTrab ?? 0, 10);
            const bajo21  = horas < 21;
            const badgeClass = bajo21 ? 'status-badge pending' : 'status-badge active';
            const badgeText  = bajo21 ? '<21 hs' : 'OK';

            const vivienda = (s.Bloque && s.NumPuerta)
                ? `Bloque ${s.Bloque} - Puerta ${s.NumPuerta}`
                : 'Sin asignar';

            tr.innerHTML = `
                <td>${s.CiS}</td>
                <td>${s.IDsocio}</td>
                <td>${s.Pape} ${s.Pnom}</td>
                <td>${s.Email ?? '-'}</td>
                <td>${s.FechNac}</td>
                <td>${horas}</td>
                <td><span class="${badgeClass}">${badgeText}</span></td>
                <td>${vivienda}</td>
                <td>
                    <button
                        class="btn-primary-small btn-aviso"
                        data-nombre="${s.Pnom} ${s.Pape}"
                        data-horas="${horas}"
                        data-email="${s.Email ?? ''}"
                    >
                        Aviso horas
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="9">Error al cargar socios.</td></tr>';
    }
}
    </script>
</head>
<body class="dashboard-page admin-dashboard">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <span class="admin-badge-small">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="icon">📊</span> Dashboard
            </a>
            <a href="solicitudes.php" class="nav-item">
                <span class="icon">✅</span> Solicitudes
            </a>
            <a href="socios.php" class="nav-item active">
                <span class="icon">👥</span> Socios
            </a>
            <a href="obras.php" class="nav-item">
                <span class="icon">🏗️</span> Obras
            </a>
            <a href="pagos.php" class="nav-item">
                <span class="icon">💰</span> Pagos
            </a>
            <a href="viviendas.php" class="nav-item">
                <span class="icon">🏠</span> Viviendas
            </a>
            <a href="admins.php" class="nav-item">
                <span class="icon">🛡️</span> Gestión de admins
            </a>
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
                <h1>Socios</h1>
                <p>Control de horas trabajadas y vivienda asignada.</p>
            </div>
        </header>

        <section class="card full-width">
            <div class="card-header">
                <h2>Listado de socios</h2>
                <p class="text-muted">Los que tienen menos de 21 horas aparecen marcados.</p>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-socios">
                    <thead>
                        <tr>
                            <th>CI</th>
                            <th>ID socio</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Nacimiento</th>
                            <th>Horas trabajadas</th>
                            <th>Estado horas</th>
                            <th>Vivienda</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
