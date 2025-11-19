<?php
// admin/viviendas.php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Viviendas - Admin</title>
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
    cargarViviendas();
    cargarAdmins();
    cargarSocios();
    cargarViviendasAdmin();

    document.getElementById('form-vivienda').addEventListener('submit', async (e) => {
        e.preventDefault();
        const bloque = parseInt(document.getElementById('bloque').value, 10);
        const numPuerta = parseInt(document.getElementById('numPuerta').value, 10);
        const msg = document.getElementById('msg-vivienda');
        msg.textContent = '';

        try {
            await apiRequest('../api/admin_viviendas.php?action=crear_vivienda', {
                method: 'POST',
                body: JSON.stringify({ bloque, num_puerta: numPuerta })
            });
            msg.style.color = '#22c55e';
            msg.textContent = 'Vivienda creada correctamente.';
            e.target.reset();
            cargarViviendas();
        } catch (err) {
            msg.style.color = '#f97373';
            msg.textContent = err.error || 'Error al crear vivienda';
        }
    });

    document.getElementById('form-asignar-admin').addEventListener('submit', async (e) => {
        e.preventDefault();
        const selAdmin    = document.getElementById('sel-admin');
        const selVivienda = document.getElementById('sel-vivienda');
        const msg = document.getElementById('msg-asignar-admin');
        msg.textContent = '';

        if (!selAdmin.value || !selVivienda.value) return;

        const [cia, idadmin] = selAdmin.value.split('|').map(Number);
        const numPuerta      = parseInt(selVivienda.value, 10);

        try {
            await apiRequest('../api/admin_viviendas.php?action=asignar_vivienda_admin', {
                method: 'POST',
                body: JSON.stringify({ cia, idadmin, num_puerta: numPuerta })
            });
            msg.style.color = '#22c55e';
            msg.textContent = 'Vivienda asignada al admin.';
            cargarViviendasAdmin();
        } catch (err) {
            msg.style.color = '#f97373';
            msg.textContent = err.error || 'Error al asignar vivienda';
        }
    });

    document.getElementById('form-asignar-socio').addEventListener('submit', async (e) => {
        e.preventDefault();
        const selSocio    = document.getElementById('sel-socio');
        const selAdminViv = document.getElementById('sel-admin-vivienda');
        const msg = document.getElementById('msg-asignar-socio');
        msg.textContent = '';

        if (!selSocio.value || !selAdminViv.value) return;

        const [cis, idsocio] = selSocio.value.split('|').map(Number);
        const [cia, idadmin, numPuerta] = selAdminViv.value.split('|').map(Number);

        try {
            await apiRequest('../api/admin_viviendas.php?action=asignar_vivienda_socio', {
                method: 'POST',
                body: JSON.stringify({ cis, idsocio, cia, idadmin, num_puerta: numPuerta })
            });
            msg.style.color = '#22c55e';
            msg.textContent = 'Vivienda asignada al socio.';
        } catch (err) {
            msg.style.color = '#f97373';
            msg.textContent = err.error || 'Error al asignar vivienda al socio';
        }
    });
});

async function cargarViviendas() {
    const tbody = document.querySelector('#tabla-viviendas tbody');
    tbody.innerHTML = '<tr><td colspan="2">Cargando...</td></tr>';
    try {
        const viv = await apiRequest('../api/admin_viviendas.php?action=list_viviendas');
        tbody.innerHTML = '';
        if (!viv.length) {
            tbody.innerHTML = '<tr><td colspan="2">No hay viviendas registradas.</td></tr>';
            return;
        }
        const selVivienda = document.getElementById('sel-vivienda');
        selVivienda.innerHTML = '<option value="">Seleccionar vivienda...</option>';

        viv.forEach(v => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${v.Bloque}</td>
                <td>${v.NumPuerta}</td>
            `;
            tbody.appendChild(tr);

            const opt = document.createElement('option');
            opt.value = v.NumPuerta;
            opt.textContent = `Bloque ${v.Bloque} - Puerta ${v.NumPuerta}`;
            selVivienda.appendChild(opt);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="2">Error al cargar viviendas.</td></tr>';
    }
}

async function cargarAdmins() {
    const sel = document.getElementById('sel-admin');
    sel.innerHTML = '<option value="">Seleccionar admin...</option>';
    try {
        const admins = await apiRequest('../api/admin_viviendas.php?action=list_admins');
        admins.forEach(a => {
            const opt = document.createElement('option');
            opt.value = `${a.CiA}|${a.IDadmin}`;
            opt.textContent = `${a.Pape} ${a.Pnom} (CI ${a.CiA}, ID ${a.IDadmin})`;
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error(err);
    }
}

async function cargarSocios() {
    const sel = document.getElementById('sel-socio');
    sel.innerHTML = '<option value="">Seleccionar socio...</option>';
    try {
        const socios = await apiRequest('../api/admin_viviendas.php?action=list_socios_simple');
        socios.forEach(s => {
            const opt = document.createElement('option');
            opt.value = `${s.CiS}|${s.IDsocio}`;
            opt.textContent = `${s.Pape} ${s.Pnom} (CI ${s.CiS}, ID ${s.IDsocio})`;
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error(err);
    }
}

async function cargarViviendasAdmin() {
    const tbody = document.querySelector('#tabla-admin-viv tbody');
    tbody.innerHTML = '<tr><td colspan="3">Cargando...</td></tr>';
    const sel = document.getElementById('sel-admin-vivienda');
    sel.innerHTML = '<option value="">Seleccionar admin + vivienda...</option>';

    try {
        const rows = await apiRequest('../api/admin_viviendas.php?action=list_viviendas_admin');
        tbody.innerHTML = '';
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="3">No hay viviendas administradas.</td></tr>';
            return;
        }

        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.Pape} ${r.Pnom} (CI ${r.CiA}, ID ${r.IDadmin})</td>
                <td>${r.Bloque}</td>
                <td>${r.ViviendaNum}</td>
            `;
            tbody.appendChild(tr);

            const opt = document.createElement('option');
            opt.value = `${r.CiA}|${r.IDadmin}|${r.ViviendaNum}`;
            opt.textContent = `${r.Pape} ${r.Pnom} - Bloque ${r.Bloque} Puerta ${r.ViviendaNum}`;
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="3">Error al cargar asignaciones.</td></tr>';
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
            <a href="pagos.php" class="nav-item">
                <span class="icon">💰</span> Pagos
            </a>
            <a href="viviendas.php" class="nav-item active">
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
                <h1>Viviendas y asignaciones</h1>
                <p>Gestiona viviendas, qué admin las administra y a qué socio se asignan.</p>
            </div>
        </header>

        <!-- Alta de viviendas -->
        <section class="card">
            <div class="card-header">
                <h2>Crear vivienda</h2>
            </div>
            <form id="form-vivienda" class="login-form">
                <div class="form-group">
                    <label for="bloque">Bloque</label>
                    <input type="number" id="bloque" min="1" required>
                </div>
                <div class="form-group">
                    <label for="numPuerta">Número de puerta</label>
                    <input type="number" id="numPuerta" min="1" required>
                </div>
                <button type="submit" class="btn-primary">Guardar vivienda</button>
                <p id="msg-vivienda" class="mt-1"></p>
            </form>
        </section>

        <!-- Listado de viviendas -->
        <section class="card">
            <div class="card-header">
                <h2>Viviendas registradas</h2>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-viviendas">
                    <thead>
                        <tr>
                            <th>Bloque</th>
                            <th>Puerta</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- Asignar admin a vivienda -->
        <section class="card">
            <div class="card-header">
                <h2>Asignar vivienda a administrador</h2>
            </div>
            <form id="form-asignar-admin" class="login-form">
                <div class="form-group">
                    <label for="sel-admin">Administrador</label>
                    <select id="sel-admin" required></select>
                </div>
                <div class="form-group">
                    <label for="sel-vivienda">Vivienda</label>
                    <select id="sel-vivienda" required></select>
                </div>
                <button type="submit" class="btn-primary">Asignar</button>
                <p id="msg-asignar-admin" class="mt-1"></p>
            </form>
        </section>

        <!-- Asignar socio a admin+vivienda -->
        <section class="card full-width">
            <div class="card-header">
                <h2>Asignar vivienda a socio</h2>
            </div>
            <form id="form-asignar-socio" class="login-form">
                <div class="form-group">
                    <label for="sel-socio">Socio</label>
                    <select id="sel-socio" required></select>
                </div>
                <div class="form-group">
                    <label for="sel-admin-vivienda">Admin + vivienda</label>
                    <select id="sel-admin-vivienda" required></select>
                </div>
                <button type="submit" class="btn-primary">Asignar</button>
                <p id="msg-asignar-socio" class="mt-1"></p>
            </form>

            <div class="card-header" style="margin-top:2rem;">
                <h2>Viviendas administradas</h2>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-admin-viv">
                    <thead>
                        <tr>
                            <th>Administrador</th>
                            <th>Bloque</th>
                            <th>Puerta</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
