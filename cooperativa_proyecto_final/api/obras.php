<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session_helpers.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';
$input  = file_get_contents('php://input');
$data   = json_decode($input, true) ?? [];

function json_error_obras($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg]);
    exit;
}

function json_ok_obras($payload = []) {
    echo json_encode($payload);
    exit;
}

function newObraId(mysqli $db): int {
    $r = $db->query("SELECT MAX(IDobr) AS maxid FROM OBRA");
    $max = $r && ($row = $r->fetch_assoc()) && $row['maxid'] ? (int)$row['maxid'] : 0;
    return $max + 1;
}

switch ($action) {
    case 'list':
        $res = $mysqli->query("SELECT IDobr, Estado, TipoObr, FechInicio, FechFin FROM OBRA ORDER BY IDobr");
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        json_ok_obras($rows);
        break;

    case 'create':
        $tipo       = trim($data['tipo'] ?? '');
        $estado     = trim($data['estado'] ?? '');
        $fechainicio= $data['fechainicio'] ?? '';
        $fechafin   = $data['fechafin'] ?? '';
        if (!$tipo || !$estado || !$fechainicio || !$fechafin) {
            json_error_obras('Faltan datos');
        }
        $IDobr = newObraId($mysqli);
        $stmt = $mysqli->prepare("INSERT INTO OBRA (IDobr, Estado, TipoObr, FechInicio, FechFin) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $IDobr, $estado, $tipo, $fechainicio, $fechafin);
        $stmt->execute();
        json_ok_obras(['success' => true, 'IDobr' => $IDobr]);
        break;

    case 'update':
        $IDobr      = isset($data['id']) ? (int)$data['id'] : 0;
        $tipo       = trim($data['tipo'] ?? '');
        $estado     = trim($data['estado'] ?? '');
        $fechainicio= $data['fechainicio'] ?? '';
        $fechafin   = $data['fechafin'] ?? '';
        if (!$IDobr) json_error_obras('ID inválido');
        $stmt = $mysqli->prepare("UPDATE OBRA SET Estado = ?, TipoObr = ?, FechInicio = ?, FechFin = ? WHERE IDobr = ?");
        $stmt->bind_param("ssssi", $estado, $tipo, $fechainicio, $fechafin, $IDobr);
        $stmt->execute();
        json_ok_obras(['success' => true]);
        break;

    case 'delete':
        $IDobr = isset($data['id']) ? (int)$data['id'] : 0;
        if (!$IDobr) json_error_obras('ID inválido');
        $stmt = $mysqli->prepare("DELETE FROM OBRA WHERE IDobr = ?");
        $stmt->bind_param("i", $IDobr);
        $stmt->execute();
        json_ok_obras(['success' => true]);
        break;

    default:
        json_error_obras('Acción no soportada', 404);
}
