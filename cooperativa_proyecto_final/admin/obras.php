<?php
require_once __DIR__ . '/../session_helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de obras</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
    <script>
    async function cargarObras() {
        const tbody = document.getElementById('tbody-obras');
        tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';
        try {
            const data = await apiRequest('../api/obras.php?action=list');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="5">No hay obras registradas</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(o => `
                <tr>
                    <td>${o.IDobr}</td>
                    <td>${o.TipoObr}</td>
                    <td>${o.Estado}</td>
                    <td>${o.FechInicio}</td>
                    <td>${o.FechFin}</td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5">Error cargando obras</td></tr>';
        }
    }

    async function guardarObra(e) {
        e.preventDefault();
        const form = document.getElementById('form-obra');
        const data = {
            tipo: form.tipo.value,
            estado: form.estado.value,
            fechainicio: form.fechainicio.value,
            fechafin: form.fechafin.value
        };
        try {
            await apiRequest('../api/obras.php?action=create', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            form.reset();
            cargarObras();
        } catch (err) {
            alert(err.message || err.error || 'Error al crear obra');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarObras();
        document.getElementById('form-obra').addEventListener('submit', guardarObra);
    });
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
            <a href="socios.php" class="nav-item"><span class="icon">👥</span> Socios</a>
            <a href="obras.php" class="nav-item active"><span class="icon">🏗️</span> Obras</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Gestión de obras</h1>
            </div>
        </header>

        <section class="card">
            <div class="card-header">
                <h2>Registrar nueva obra</h2>
            </div>
            <form id="form-obra" class="login-form">
                <div class="form-group">
                    <label>Tipo de obra</label>
                    <input type="text" name="tipo" required>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" name="estado" placeholder="en construcción, finalizada..." required>
                </div>
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" name="fechainicio" required>
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="fechafin" required>
                </div>
                <button type="submit" class="btn-login">Guardar obra</button>
            </form>
        </section>

        <section class="card">
            <div class="card-header">
                <h2>Obras registradas</h2>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-obras"></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
