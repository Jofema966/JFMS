<?php
require_once __DIR__ . '/../config.php';
requireSocioPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos y comprobantes</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>Cooperativa</h1>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="perfil.php" class="nav-link">Mi perfil</a>
        <a href="horas.php" class="nav-link">Horas trabajadas</a>
        <a href="pagos.php" class="nav-link active">Pagos</a>
        <a href="vivienda.php" class="nav-link">Vivienda y obras</a>
        <a href="#" class="nav-link" onclick="logoutSocio(event)">Cerrar sesión</a>
    </aside>

    <main class="main">
        <h2 class="page-title">Pagos y comprobantes</h2>

        <div class="card">
            <h2>Subir comprobante de pago</h2>
            <p id="pagos-msg" style="margin-bottom:8px;"></p>
            <form id="form-pago" enctype="multipart/form-data">
                <div style="margin-bottom:8px;">
                    <label>Monto</label><br>
                    <input type="number" name="monto" id="pago-monto" class="input" min="1" required style="width:200px;">
                </div>
                <div style="margin-bottom:8px;">
                    <label>Tipo de pago</label><br>
                    <select name="tipo_pago" id="pago-tipo" class="select" style="width:200px;">
                        <option value="Transferencia">Transferencia</option>
                        <option value="Depósito">Depósito</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                </div>
                <div style="margin-bottom:8px;">
                    <label>Comprobante (PDF)</label><br>
                    <input type="file" name="comprobante" id="pago-archivo" accept="application/pdf" required>
                </div>
                <button type="submit" class="button">Enviar comprobante</button>
            </form>
        </div>

        <div class="card">
            <h2>Historial de pagos</h2>
            <p id="pagos-error" style="color:#f87171; margin-bottom:8px;"></p>
            <table class="table">
                <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Monto</th>
                    <th>Tipo pago</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Archivo</th>
                </tr>
                </thead>
                <tbody id="pagos-tbody">
                <tr><td colspan="6">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
async function cargarPagos() {
    try {
        const res = await fetch('../api/socio.php?action=pagos_list');
        const data = await res.json();

        const tbody = document.getElementById('pagos-tbody');
        tbody.innerHTML = '';

        if (data.status !== 'ok') {
            document.getElementById('pagos-error').textContent = data.message || 'Error cargando pagos.';
            tbody.innerHTML = '<tr><td colspan="6">Sin datos</td></tr>';
            return;
        }

        if (!data.data.length) {
            tbody.innerHTML = '<tr><td colspan="6">Sin pagos registrados.</td></tr>';
            return;
        }

        for (const p of data.data) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.Comprobante}</td>
                <td>${p.Monto}</td>
                <td>${p.TipoPago}</td>
                <td>${p.FechaPago}</td>
                <td>${p.Estado}</td>
                <td>${p.Archivo ? `<a href="../${p.Archivo}" target="_blank">Ver PDF</a>` : '-'}</td>
            `;
            tbody.appendChild(tr);
        }

    } catch (e) {
        document.getElementById('pagos-error').textContent = 'Error cargando pagos.';
        document.getElementById('pagos-tbody').innerHTML = '<tr><td colspan="6">Error</td></tr>';
    }
}

document.getElementById('form-pago').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const res = await fetch('../api/socio.php?action=pagos_subir', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        document.getElementById('pagos-msg').textContent = data.message || '';

        if (data.status === 'ok') {
            form.reset();
            cargarPagos();
        }

    } catch (e) {
        document.getElementById('pagos-msg').textContent = 'Error al enviar comprobante.';
    }
});

cargarPagos();
</script>

<script>
async function logoutSocio(event) {
    event.preventDefault();
    try {
        // Llama a TU API existente
        const res = await fetch('../api/auth.php?action=logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        // No necesitamos ni leer la respuesta, con que llegue basta
    } catch (e) {
        // Aunque falle la llamada, igual forzamos la salida
        console.error('Error llamando a logout:', e);
    }

    // Redirigimos al login de socio
    window.location.href = 'login.php';
}
</script>
</body>
</html>
