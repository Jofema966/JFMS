<?php
// conexion.php
$host    = 'localhost';
$db      = 'cooperativa_bd';   // <- nombre de tu base
$user    = 'root';             // XAMPP por defecto
$pass    = '';                 // XAMPP por defecto (vacío)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lanza excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Si se llama desde las APIs, devolvemos JSON de error
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión BD: ' . $e->getMessage()]);
    } else {
        // Si se llama desde una página normal
        die('Error de conexión a la base de datos.');
    }
    exit;
}
