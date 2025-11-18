<?php
// Ajusta estos datos a tu entorno
$host = "localhost";
$user = "root";
$pass = "";
$db   = "cooperativa_db";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Error de conexión a la base de datos";
    exit;
}

$mysqli->set_charset("utf8mb4");
