<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session_helpers.php';

$action = $_GET['action'] ?? '';

$input = file_get_contents('php://input');
$data  = json_decode($input, true) ?? [];

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg]);
    exit;
}

function json_ok($payload = []) {
    echo json_encode(['success' => true] + $payload);
    exit;
}

switch ($action) {
    case 'registro':
        $ci       = isset($data['ci']) ? (int)$data['ci'] : 0;
        $pnom     = trim($data['pnom'] ?? '');
        $pape     = trim($data['pape'] ?? '');
        $fechnac  = $data['fechnac'] ?? '';
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$ci || !$pnom || !$pape || !$fechnac || !$email || !$password) {
            json_error('Faltan datos');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error('Email inválido');
        }
        if (!ctype_digit($password)) {
            json_error('La contraseña debe ser numérica');
        }
        $passInt = (int)$password;

        // ¿Ya existe persona con ese CI o email?
        $stmt = $mysqli->prepare("SELECT Ci FROM PERSONA WHERE Ci = ? OR Email = ?");
        $stmt->bind_param("is", $ci, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            json_error('Ya existe una persona con ese CI o email');
        }

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("INSERT INTO PERSONA (Ci, Pnom, Pape, FechNac, Contraseña, Email) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssis", $ci, $pnom, $pape, $fechnac, $passInt, $email);
            $stmt->execute();

            $stmt = $mysqli->prepare("INSERT INTO USUARIO (CiU, Pnom, Pape, FechNac) VALUES (?,?,?,?)");
            $stmt->bind_param("isss", $ci, $pnom, $pape, $fechnac);
            $stmt->execute();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            json_error('Error al registrar: ' . $e->getMessage(), 500);
        }

        json_ok(['message' => 'Solicitud registrada. Un administrador debe aprobarla.']);
        break;

    case 'login_socio':
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        if (!$email || !$password) {
            json_error('Faltan datos');
        }
        if (!ctype_digit($password)) {
            json_error('La contraseña debe ser numérica');
        }
        $passInt = (int)$password;

        $stmt = $mysqli->prepare("SELECT Ci, Contraseña FROM PERSONA WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$row = $res->fetch_assoc()) {
            json_error('Credenciales incorrectas', 401);
        }
        if ((int)$row['Contraseña'] !== $passInt) {
            json_error('Credenciales incorrectas', 401);
        }
        $ci = (int)$row['Ci'];

        // ¿Es socio?
        $stmt = $mysqli->prepare("SELECT IDsocio FROM SOCIO WHERE CiS = ?");
        $stmt->bind_param("i", $ci);
        $stmt->execute();
        $res2 = $stmt->get_result();
        if ($soc = $res2->fetch_assoc()) {
            $_SESSION['role']    = 'socio';
            $_SESSION['ci']      = $ci;
            $_SESSION['IDsocio'] = (int)$soc['IDsocio'];
            json_ok(['message' => 'Login correcto']);
        }

        // ¿está como pendiente?
        $stmt = $mysqli->prepare("SELECT CiU FROM USUARIO WHERE CiU = ?");
        $stmt->bind_param("i", $ci);
        $stmt->execute();
        $res3 = $stmt->get_result();
        if ($res3->num_rows > 0) {
            json_error('Tu solicitud aún está pendiente de aprobación', 403);
        }

        json_error('No eres socio de la cooperativa', 403);
        break;

    case 'login_admin':
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        if (!$email || !$password) {
            json_error('Faltan datos');
        }
        if (!ctype_digit($password)) {
            json_error('La contraseña debe ser numérica');
        }
        $passInt = (int)$password;

        // Busca persona por email
        $stmt = $mysqli->prepare("SELECT Ci, Contraseña FROM PERSONA WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$row = $res->fetch_assoc()) {
            json_error('Credenciales incorrectas', 401);
        }
        if ((int)$row['Contraseña'] !== $passInt) {
            json_error('Credenciales incorrectas', 401);
        }
        $ci = (int)$row['Ci'];

        // ¿Es admin?
        $stmt = $mysqli->prepare("SELECT IDadmin FROM ADMINISTRADOR WHERE CiA = ?");
        $stmt->bind_param("i", $ci);
        $stmt->execute();
        $res2 = $stmt->get_result();
        if (!$adm = $res2->fetch_assoc()) {
            json_error('No tienes rol de administrador', 403);
        }

        $_SESSION['role']    = 'admin';
        $_SESSION['ci']      = $ci;
        $_SESSION['IDadmin'] = (int)$adm['IDadmin'];

        json_ok(['message' => 'Login admin correcto']);
        break;

    case 'logout':
        session_destroy();
        json_ok(['message' => 'Sesión cerrada']);
        break;

    default:
        json_error('Acción no soportada', 404);
}
