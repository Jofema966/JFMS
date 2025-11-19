<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos - Admin</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
// Helper por si apiRequest no existe global
async function apiRequest(url, options = {}) {
    const opts = {
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        ...options
    };
    const res = await fetch(url, opts);
    const data = await res.json();
    if (!res.ok) {
        throw data;
    }
    return data;
}

document.addEventListener('DOMContentLoaded', () => {
    cargarPagos();

    const tbody = document.querySelector('#tabla-pagos tbody');
    tbody.addEventListener('click', async (e) => {
        const btn = e.target;
        const tr  = btn.closest('tr');
        if (!tr) return;

        const comp  = tr.dataset.comprobante;
        const idpag = tr.dataset.idpago;

        if (btn.classList.contains('btn-approve')) {
            if (!confirm(`¿Aprobar el pago ${comp}/${idpag}?`)) return;
            try {
                await apiRequest('../api/admin.php?action=aprobar_pago', {
                    method: 'POST',
                    body: JSON.stringify({ comprobante: comp, idpago: idpag })
                });
                await cargarPagos();
                showNotification('Pago aprobado', 'success');
            } catch (err) {
                showNotification(err.error || err.message || 'Error al aprobar pago', 'error');
            }
        }

        if (btn.classList.contains('btn-reject')) {
            const motivo = prompt('Motivo del rechazo:');
            if (!motivo) return;
            try {
                await apiRequest('../api/admin.php?action=rechazar_pago', {
                    method: 'POST',
                    body: JSON.stringify({ comprobante: comp, idpago: idpag, motivo })
                });
                await cargarPagos();
                showNotification('Pago rechazado', 'info');
            } catch (err) {
                showNotification(err.error || err.message || 'Error al rechazar pago', 'error');
            }
        }
    });
});

async function cargarPagos() {
    const tbody = document.querySelector('#tabla-pagos tbody');
    tbody.innerHTML = '<tr><td colspan="9">Cargando...</td></tr>';
    try {
        const pagos = await apiRequest('../api/admin.php?action=list_pagos');
        tbody.innerHTML = '';
        if (!pagos.length) {
            tbody.innerHTML = '<tr><td colspan="9">No hay pagos registrados.</td></tr>';
            return;
        }

        pagos.forEach(p => {
            const tr = document.createElement('tr');
            tr.dataset.comprobante = p.Comprobante;
            tr.dataset.idpago      = p.IDpago;

            const estadoLabel = p.Estado || 'pendiente';
            let badgeClass = 'status-badge pending';
            if (estadoLabel === 'aceptado')  badgeClass = 'status-badge active';
            if (estadoLabel === 'rechazado') badgeClass = 'status-badge pending';

            tr.innerHTML = `
                <td>${p.Comprobante}</td>
                <td>${p.IDpago}</td>
                <td>$${p.Monto}</td>
                <td>${p.TipoPago}</td>
                <td>${p.FechaPago}</td>
                <td><span class="${badgeClass}">${estadoLabel}</span></td>
                <td>${p.CiS ? (p.CiS + ' / ' + p.IDsocio) : '-'}</td>
                <td>${p.MotivoRechazo ? p.MotivoRechazo : '-'}</td>
                <td class="table-actions">
                    <button type="button" class="btn-approve">✓ Aprobar</button>
                    <button type="button" class="btn-reject">✕ Rechazar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="9">Error al cargar pagos.</td></tr>';
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
            <a href="socios.php" class="nav-item">
                <span class="icon">👥</span> Socios
            </a>
            <a href="obras.php" class="nav-item">
                <span class="icon">🏗️</span> Obras
            </a>
            <a href="pagos.php" class="nav-item active">
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
                <h1>Pagos</h1>
                <p>Revisión y gestión de pagos de socios</p>
            </div>
        </header>

        <section class="card full-width">
            <div class="card-header">
                <h2>Pagos registrados</h2>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-pagos">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>ID Pago</th>
                            <th>Monto</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Socio (Ci / ID)</th>
                            <th>Motivo rechazo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- filas desde JS -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
