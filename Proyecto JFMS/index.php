
<?php ?><!doctype html><html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cooperativa de Viviendas | Inicio</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/landing.css">
  <script type="module" src="assets/js/landing.js"></script>
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <div class="logo">Cooperativa</div>
    <nav class="nav" id="nav">
      <a href="#que-es">Qué es</a>
      <a href="#exteriores">Exteriores</a>
      <a href="#beneficios">Beneficios</a>
      <a href="#como-funciona">Cómo funciona</a>
      <a href="#mas-info">Más información</a>
      <a href="#faq">FAQ</a>
      <a href="#contacto">Contacto</a>
      <a class="btn btn-acc" href="register.php">Quiero asociarme</a>
    </nav>
    <button class="nav-toggle" id="navToggle" aria-label="Abrir menú">☰</button>
  </div>
</header>

<main>
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy">
        <h1>Tu vivienda, en comunidad y con reglas claras</h1>
        <p>Unite a la cooperativa, gestioná tu perfil de socio y seguí el proceso en línea. Los administradores validan altas y mantienen todo en orden.</p>
        <div class="cta">
          <a class="btn btn-acc" href="register.php">Asociarme ahora</a>
          <a class="btn" href="login_socio.php">Ingresar como socio</a>
          <a class="btn" href="login_admin.php" title="Backoffice">Backoffice</a>
        </div>
      </div>
      <div class="hero-card card">
        <h2>Estado de solicitudes</h2>
        <ul class="dash">
          <li><span class="dot ok"></span> Aprobadas por admin</li>
          <li><span class="dot"></span> Pendientes de revisión</li>
          <li><span class="dot danger"></span> Rechazadas</li>
        </ul>
        <p class="muted">El alta de socios se confirma sólo tras aprobación administrativa.</p>
      </div>
    </div>
  </section>

  <section id="que-es" class="section">
    <div class="wrap">
      <h2>Qué es la cooperativa</h2>
      <p>Una organización de ayuda mutua enfocada en acceso a vivienda mediante gestión transparente, cuotas claras y participación de sus miembros.</p>
    </div>
  </section>

  <section id="exteriores" class="section alt">
    <div class="wrap">
      <h2>Exteriores</h2>
      <div class="grid-3">
        <div class="card">
          <h3>Plaza central</h3>
          <p>Espacio de encuentro con arbolado, bancos y juegos infantiles. Punto de reunión para actividades comunitarias.</p>
        </div>
        <div class="card">
          <h3>Salón de eventos</h3>
          <p>Multiuso para asambleas y celebraciones. Se utiliza como gimnasio cuando no hay eventos programados.</p>
        </div>
        <div class="card">
          <h3>Áreas verdes</h3>
          <p>Zonas de descanso y picnic con senderos peatonales y sector de juegos. Mantenimiento periódico.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="beneficios" class="section">
    <div class="wrap">
      <h2>Beneficios</h2>
      <div class="grid-3">
        <div class="card">
          <h3>Transparencia</h3>
          <p>Solicitudes y estados auditables. Aprobaciones sólo por administradores.</p>
        </div>
        <div class="card">
          <h3>Acompañamiento</h3>
          <p>Proceso guiado desde el registro hasta la adjudicación.</p>
        </div>
        <div class="card">
          <h3>Autogestión</h3>
          <p>Área de socios para ver datos personales y comunicaciones.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="como-funciona" class="section">
    <div class="wrap">
      <h2>Cómo funciona</h2>
      <ol class="steps">
        <li><strong>Registrate</strong> en línea con tus datos personales.</li>
        <li><strong>Validación</strong> por el backoffice de administración.</li>
        <li><strong>Ingreso</strong> al área de socio para seguir tus trámites.</li>
      </ol>
      <div class="cta">
        <a class="btn btn-acc" href="register.php">Comenzar registro</a>
      </div>
    </div>
  </section>

  <section id="mas-info" class="section alt">
    <div class="wrap">
      <h2>Más información</h2>
      <div class="grid-2">
        <div class="card">
          <h3>Acerca de la cooperativa</h3>
          <p>COVICOOP promueve el acceso a vivienda digna bajo principios de solidaridad, ayuda mutua y esfuerzo propio. Participación activa de las familias en decisiones y control de gestión.</p>
          <p>Proyectos con estándares de calidad y eficiencia energética. Lazos comunitarios y sentido de pertenencia.</p>
        </div>
        <div class="card">
          <h3>Proceso y requisitos</h3>
          <ul class="list">
            <li>Completar registro con datos personales verificados.</li>
            <li>Esperar validación de administración.</li>
            <li>Acceder al panel de socio para seguimiento.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section id="faq" class="section">
    <div class="wrap">
      <h2>Preguntas frecuentes</h2>
      <details class="card">
        <summary>¿Cuánto demora la aprobación?</summary>
        <p>Depende del volumen de solicitudes. Te notificaremos al aprobar o rechazar.</p>
      </details>
      <details class="card">
        <summary>¿Puedo editar mis datos?</summary>
        <p>Una vez aprobado tu alta, podrás solicitar correcciones desde tu área de socio.</p>
      </details>
      <details class="card">
        <summary>¿Quién ve mis datos?</summary>
        <p>Sólo los administradores autorizados y vos desde tu sesión.</p>
      </details>
    </div>
  </section>

  <section id="contacto" class="section alt">
    <div class="wrap">
      <h2>Contacto</h2>
      <div class="grid-2">
        <div class="card">
          <h3>Escribinos</h3>
          <p><a href="mailto:contacto@cooperativa.example">contacto@cooperativa.example</a></p>
          <p class="muted">Atención de lunes a viernes.</p>
        </div>
        <div class="card">
          <h3>¿Listo para sumarte?</h3>
          <p>Completá el formulario y esperá la validación.</p>
          <a class="btn btn-acc" href="register.php">Ir al registro</a>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="wrap">
    <span>© Cooperativa de Viviendas</span>
    <span class="sep">|</span>
    <a href="login_admin.php">Backoffice</a>
  </div>
</footer>
</body>
</html>
