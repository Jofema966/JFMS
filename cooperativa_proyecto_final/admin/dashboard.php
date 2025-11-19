<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard admin</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const totSocios = document.getElementById('total-socios');
        const totPend   = document.getElementById('total-pendientes');
        const totObras  = document.getElementById('total-obras');
        const totPagos  = document.getElementById('total-pagos');
        try {
            const stats = await apiRequest('../api/admin.php?action=stats');
            totSocios.textContent = stats.total_socios ?? 0;
            totPend.textContent   = stats.total_pendientes ?? 0;
            totObras.textContent  = stats.total_obras ?? 0;
            totPagos.textContent  = stats.total_pagos ?? 0;
        } catch (err) {
            console.error(err);
        }
    });

    async function logoutAdmin() {
        try { await apiRequest('../api/auth.php?action=logout', { method:'POST' }); } catch(e){}
        location.href = '../landing/index.php';
    }
    </script>
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <span class="admin-badge-small">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
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
                <h1>Panel de administración</h1>
                <p>Control general de la cooperativa</p>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <div class="stat-label">Socios activos</div>
                    <div class="stat-value" id="total-socios">0</div>
                </div>
            </div>
            <div class="stat-card highlight">
                <div class="stat-icon">✅</div>
                <div>
                    <div class="stat-label">Solicitudes pendientes</div>
                    <div class="stat-value" id="total-pendientes">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏗️</div>
                <div>
                    <div class="stat-label">Obras registradas</div>
                    <div class="stat-value" id="total-obras">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div>
                    <div class="stat-label">Pagos registrados</div>
                    <div class="stat-value" id="total-pagos">0</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
