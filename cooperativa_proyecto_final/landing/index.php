<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cooperativa - Construyamos juntos</title>
    <link rel="stylesheet" href="../estilos.css">
    <script defer src="../scripts.js"></script>
</head>
<body>
<header class="header">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
        <div class="logo">Cooperativa</div>
        <nav class="nav">
            <a href="#sobre" class="nav-link">Sobre nosotros</a>
            <a href="#membresia" class="nav-link">Membresía</a>
            <!-- RUTAS RELATIVAS -->
            <button class="btn-secondary" onclick="location.href='../socio/login.php'">Iniciar sesión</button>
            <button class="btn-primary" onclick="location.href='../socio/registro.php'">Solicitar membresía</button>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Construyamos juntos una cooperativa fuerte</h1>
            <p class="hero-subtitle">
                Una cooperativa es una organización autónoma de personas unidas voluntariamente para satisfacer 
                sus necesidades y aspiraciones económicas, sociales y culturales en común a través de una empresa 
                de propiedad conjunta y de gestión democrática.
            </p>
            <button class="btn-cta" onclick="location.href='../socio/registro.php'">
                Quiero hacerme miembro
            </button>
        </div>
    </section>

    <section id="sobre" class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <h2>¿Qué es nuestra cooperativa?</h2>
                    <p>
                        Organización inclusiva que mejora productos y servicios para beneficio de sus socios,
                        con gestión democrática y participación real.
                    </p>
                </div>

                <div class="info-card">
                    <h3>Principios clave</h3>
                    <ul class="benefits-list">
                        <li>✓ Gestión democrática - 1 socio, 1 voto</li>
                        <li>✓ Participación económica y crecimiento</li>
                        <li>✓ Educación y formación continua</li>
                        <li>✓ Compromiso con la comunidad</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h3>Beneficios</h3>
                    <ul class="benefits-list">
                        <li>📊 Mejor acceso a productos y servicios</li>
                        <li>🏛️ Participación en decisiones</li>
                        <li>💰 Distribución justa de excedentes</li>
                        <li>🔒 Transparencia en las operaciones</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="membresia" class="membership-section">
        <div class="container">
            <h2 style="text-align:center;">Cómo convertirse en miembro</h2>
            <p class="subtitle">Unidos somos mejores: la fuerza está en los socios.</p>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Completa tu solicitud</h3>
                    <p>Llena el formulario de membresía con tu información personal.</p>
                    <button class="btn-primary" onclick="location.href='../socio/registro.php'">Iniciar solicitud</button>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Evaluación y aprobación</h3>
                    <p>El equipo de administración revisa tu solicitud pendiente.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Aportes mensuales</h3>
                    <p>Realizas los pagos y accedes a todos los beneficios.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container footer-content">
        <div class="footer-col">
            <h4>Cooperativa</h4>
            <p>Construyendo juntos un futuro mejor.</p>
        </div>
        <div class="footer-col">
            <h4>Enlaces</h4>
            <a href="#sobre">Sobre nosotros</a>
            <a href="#membresia">Membresía</a>
        </div>
        <div class="footer-col">
            <h4>Acceso</h4>
            <!-- TAMBIÉN RELATIVAS -->
            <a href="../socio/login.php">Panel socio</a>
            <a href="../admin/login.php">Panel admin</a>
        </div>
    </div>
</footer>
</body>
</html>
