<?php
// config.php
// Configuración común + helpers de sesión / BD

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANTE: que esto coincida con el nombre REAL de tu BD
// Si tu BD se llama cooperativa_db, cambia 'cooperativa_bd' por 'cooperativa_db'.
const DB_HOST = 'localhost';
const DB_NAME = 'cooperativa_bd';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

/**
 * Devuelve [ci, idsocio] del socio logueado o corta con 401 JSON.
 * Aquí centralizamos los nombres de variables de sesión.
 */
function requireSocioSessionForApi(): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // AJUSTA SOLO si usas otros nombres en tu login
    $ci      = $_SESSION['socio_ci'] ?? $_SESSION['ci']      ?? null;
    $idsocio = $_SESSION['socio_id'] ?? $_SESSION['idsocio'] ?? null;

    if (!$ci || !$idsocio) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => 'error',
            'message' => 'No autorizado. Inicie sesión como socio.',
        ]);
        exit;
    }

    return [(int)$ci, (int)$idsocio];
}

/**
 * Protección simple para páginas HTML de socio.
 */
function requireSocioPage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $ci      = $_SESSION['socio_ci'] ?? $_SESSION['ci']      ?? null;
    $idsocio = $_SESSION['socio_id'] ?? $_SESSION['idsocio'] ?? null;

    if (!$ci || !$idsocio) {
        header('Location: login.php');
        exit;
    }
}
