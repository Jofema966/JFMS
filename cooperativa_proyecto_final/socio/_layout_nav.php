<?php
// socio/_layout_nav.php
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Cooperativa</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span> Dashboard
        </a>
        <a href="perfil.php" class="nav-item <?php echo $currentPage === 'perfil.php' ? 'active' : ''; ?>">
            <span class="icon">👤</span> Mi perfil
        </a>
        <a href="horas.php" class="nav-item <?php echo $currentPage === 'horas.php' ? 'active' : ''; ?>">
            <span class="icon">⏱️</span> Horas trabajadas
        </a>
        <a href="pagos.php" class="nav-item <?php echo $currentPage === 'pagos.php' ? 'active' : ''; ?>">
            <span class="icon">💰</span> Pagos
        </a>
        <a href="vivienda.php" class="nav-item <?php echo $currentPage === 'vivienda.php' ? 'active' : ''; ?>">
            <span class="icon">🏠</span> Vivienda y obras
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="#" id="logout-link" class="nav-item logout">
            <span class="icon">🚪</span> Cerrar sesión
        </a>
    </div>
</aside>
