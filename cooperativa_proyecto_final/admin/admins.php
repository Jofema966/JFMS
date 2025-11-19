<?php
// admin/admins.php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo admins
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login-admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de administradores - Backoffice</title>
    <!-- Ajusta la ruta del CSS si tu backoffice usa otro -->
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
    <!-- Sidebar (ajusta para que coincida con tu layout actual) -->
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
            <a href="pagos.php" class="nav-item">
                <span class="icon">💰</span> Pagos
            </a>
            <a href="viviendas.php" class="nav-item">
                <span class="icon">🏠</span> Viviendas
            </a>
            <a href="admins.php" class="nav-item active">
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

    <!-- Contenido principal -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Gestión de administradores</h1>
                <p>Crear y gestionar cuentas de administrador</p>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">🛡️</div>
                <div class="stat-content">
                    <p class="stat-label">Total de administradores</p>
                    <h3 class="stat-value" id="total-admins">0</h3>
                </div>
            </div>
        </section>

        <div class="dashboard-grid admin-grid">
            <!-- Lista de admins -->
            <section class="card full-width">
                <div class="card-header">
                    <h2>Administradores actuales</h2>
                    <button class="btn-text" type="button" onclick="cargarAdmins()">Actualizar</button>
                </div>
                <div class="table-container">
                    <table class="data-table" id="tabla-admins">
                        <thead>
                            <tr>
                                <th>CI</th>
                                <th>ID Admin</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Fecha nac.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llena por JS -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Formulario crear admin -->
            <section class="card">
                <div class="card-header">
                    <h2>Crear nuevo administrador</h2>
                </div>
                <div id="crear-admin-msg" class="mt-1 mb-1" style="min-height:1.2rem;"></div>

                <form id="form-crear-admin" class="login-form">
                    <div class="form-group">
                        <label for="ca-ci">CI</label>
                        <input type="number" id="ca-ci" name="ci" required>
                    </div>

                    <div class="form-group">
                        <label for="ca-pnom">Nombre</label>
                        <input type="text" id="ca-pnom" name="pnom" required>
                    </div>

                    <div class="form-group">
                        <label for="ca-pape">Apellido</label>
                        <input type="text" id="ca-pape" name="pape" required>
                    </div>

                    <div class="form-group">
                        <label for="ca-fechnac">Fecha de nacimiento</label>
                        <input type="date" id="ca-fechnac" name="fechnac" required>
                    </div>

                    <div class="form-group">
                        <label for="ca-email">Email</label>
                        <input type="email" id="ca-email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="ca-password">Contraseña (número, como usas en PERSONA.Contraseña)</label>
                        <input type="number" id="ca-password" name="password" required>
                    </div>

                    <button type="submit" class="btn-login">Crear administrador</button>
                </form>
            </section>
        </div>
    </main>

    <script>
    // Logout usando tu API existente

    async function cargarAdmins() {
        const tbody = document.querySelector('#tabla-admins tbody');
        const totalSpan = document.getElementById('total-admins');

        tbody.innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';

        try {
            const res = await fetch('../api/admin_admins.php?action=listar_admins');
            const data = await res.json();

            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="6">' + (data.error || 'Error cargando administradores') + '</td></tr>';
                totalSpan.textContent = '0';
                return;
            }

            const admins = data.admins || [];
            totalSpan.textContent = admins.length;

            if (admins.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No hay administradores registrados.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            admins.forEach(a => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${a.ci}</td>
                    <td>${a.idadmin}</td>
                    <td>${a.pnom}</td>
                    <td>${a.pape}</td>
                    <td>${a.email || ''}</td>
                    <td>${a.fechnac || ''}</td>
                `;
                tbody.appendChild(tr);
            });

        } catch (err) {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="6">Error de comunicación con el servidor.</td></tr>';
            totalSpan.textContent = '0';
        }
    }

    // Crear admin usando api/auth.php?action=crear_admin
    document.getElementById('form-crear-admin').addEventListener('submit', async function (e) {
        e.preventDefault();
        const msg = document.getElementById('crear-admin-msg');
        msg.textContent = '';
        msg.style.color = '';

        const payload = {
            ci:       document.getElementById('ca-ci').value,
            pnom:     document.getElementById('ca-pnom').value,
            pape:     document.getElementById('ca-pape').value,
            fechnac:  document.getElementById('ca-fechnac').value,
            email:    document.getElementById('ca-email').value,
            password: document.getElementById('ca-password').value
        };

        try {
            const res = await fetch('../api/auth.php?action=crear_admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                msg.style.color = '#10b981';
                msg.textContent = data.message + ' (IDadmin: ' + (data.idadmin || '') + ')';
                this.reset();
                // refrescar listado
                cargarAdmins();
            } else {
                msg.style.color = '#ef4444';
                msg.textContent = data.error || 'Error creando administrador';
            }
        } catch (err) {
            console.error(err);
            msg.style.color = '#ef4444';
            msg.textContent = 'Error de comunicación con el servidor';
        }
    });

    // Cargar admins al entrar
    cargarAdmins();
    </script>
</body>
</html>
