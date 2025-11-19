<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Obras</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        await cargarObras();
        const form = document.getElementById('form-obra');
        const msg  = document.getElementById('msg-obra');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msg.textContent = '';
            const data = {
                estado: form.estado.value,
                tipo: form.tipo.value,
                fechInicio: form.inicio.value,
                fechFin: form.fin.value
            };
            try {
                const res = await apiRequest('../api/admin.php?action=crear_obra', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                if (res.success) {
                    msg.textContent = 'Obra creada correctamente';
                    await cargarObras();
                    form.reset();
                } else {
                    msg.textContent = res.error || 'Error al crear obra';
                }
            } catch (err) {
                msg.textContent = err.message || 'Error de servidor';
            }
        });
    });

    async function cargarObras() {
        const tbody = document.querySelector('#tabla-obras tbody');
        tbody.innerHTML = '';
        try {
            const obras = await apiRequest('../api/admin.php?action=list_obras');
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
            <a href="obras.php" class="nav-item active">
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
                <h1>Obras</h1>
                <p>Gestión de viviendas en construcción</p>
            </div>
        </header>

        <section class="card full-width">
            <div class="card-header">
                <h2>Obras registradas</h2>
            </div>
            <div class="table-container">
                <table class="data-table" id="tabla-obras">
                    <thead>
                        <tr>
                            <th>ID</th>
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

        <section class="card full-width">
            <div class="card-header">
                <h2>Nueva obra</h2>
            </div>
            <form id="form-obra" class="login-form">
                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" name="estado" required placeholder="En curso / Finalizada / etc">
                </div>
                <div class="form-group">
                    <label>Tipo de obra</label>
                    <input type="text" name="tipo" required placeholder="Vivienda, Bloque, etc">
                </div>
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" name="inicio" required>
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="fin" required>
                </div>
                <button type="submit" class="btn-primary">Crear obra</button>
                <p id="msg-obra" style="margin-top:1rem;color:#facc15;"></p>
            </form>
        </section>
    </main>
</body>
</html>
