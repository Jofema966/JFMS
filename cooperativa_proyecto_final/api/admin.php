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

function json_error_admin($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg]);
    exit;
}

function json_ok_admin($payload = []) {
    echo json_encode($payload);
    exit;
}

function newSocioId(mysqli $db): int {
    $r = $db->query("SELECT MAX(IDsocio) AS maxid FROM SOCIO");
    $max = $r && ($row = $r->fetch_assoc()) && $row['maxid'] ? (int)$row['maxid'] : 100000000;
    return $max + 1;
}

function newAdminId(mysqli $db): int {
    $r = $db->query("SELECT MAX(IDadmin) AS maxid FROM ADMINISTRADOR");
    if ($r && ($row = $r->fetch_assoc()) && $row['maxid']) {
        $max = (int)$row['maxid'];
        if ($max < 120000000) $max = 120000000;
    } else {
        $max = 120000000;
    }
    return $max + 1;
}

switch ($action) {
    case 'stats':
        $stats = [
            'total_socios'      => 0,
            'total_pendientes'  => 0,
            'total_obras'       => 0,
            'total_pagos'       => 0
        ];
        if ($r = $mysqli->query("SELECT COUNT(*) c FROM SOCIO")) {
            $row = $r->fetch_assoc(); $stats['total_socios'] = (int)$row['c'];
        }
        if ($r = $mysqli->query("SELECT COUNT(*) c FROM USUARIO")) {
            $row = $r->fetch_assoc(); $stats['total_pendientes'] = (int)$row['c'];
        }
        if ($r = $mysqli->query("SELECT COUNT(*) c FROM OBRA")) {
            $row = $r->fetch_assoc(); $stats['total_obras'] = (int)$row['c'];
        }
        if ($r = $mysqli->query("SELECT COUNT(*) c FROM PAGO")) {
            $row = $r->fetch_assoc(); $stats['total_pagos'] = (int)$row['c'];
        }
        json_ok_admin($stats);
        break;

    case 'list_solicitudes':
        $sql = "
            SELECT u.CiU, u.Pnom, u.Pape, u.FechNac, p.Email
            FROM USUARIO u
            JOIN PERSONA p ON u.CiU = p.Ci
            ORDER BY u.CiU
        ";
        $res = $mysqli->query($sql);
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        json_ok_admin($rows);
        break;

    case 'aprobar_socio':
        $ci = isset($data['ci']) ? (int)$data['ci'] : 0;
        if (!$ci) json_error_admin('CI inválido');
        $mysqli->begin_transaction();
        try {
            // Persona
            $stmt = $mysqli->prepare("SELECT Ci, Pnom, Pape, FechNac FROM PERSONA WHERE Ci = ?");
            $stmt->bind_param("i", $ci);
            $stmt->execute();
            $res = $stmt->get_result();
            if (!$row = $res->fetch_assoc()) {
                throw new Exception('No existe persona con ese CI');
            }
            $IDsocio = newSocioId($mysqli);
            $horTrab = 0;

            $stmt = $mysqli->prepare("
                INSERT INTO SOCIO (CiS, IDsocio, HorTrab, Pnom, Pape, FechNac)
                VALUES (?,?,?,?,?,?)
            ");
            $stmt->bind_param(
                "iiisss",
                $row['Ci'],
                $IDsocio,
                $horTrab,
                $row['Pnom'],
                $row['Pape'],
                $row['FechNac']
            );
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM USUARIO WHERE CiU = ?");
            $stmt->bind_param("i", $ci);
            $stmt->execute();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            json_error_admin('Error al aprobar: ' . $e->getMessage(), 500);
        }
        json_ok_admin(['success' => true, 'message' => 'Socio aprobado']);
        break;

    case 'rechazar_socio':
        $ci = isset($data['ci']) ? (int)$data['ci'] : 0;
        if (!$ci) json_error_admin('CI inválido');
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("DELETE FROM USUARIO WHERE CiU = ?");
            $stmt->bind_param("i", $ci);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM PERSONA WHERE Ci = ?");
            $stmt->bind_param("i", $ci);
            $stmt->execute();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            json_error_admin('Error al rechazar: ' . $e->getMessage(), 500);
        }
        json_ok_admin(['success' => true, 'message' => 'Solicitud rechazada y datos eliminados']);
        break;

    case 'list_socios':
        $sql = "
            SELECT s.CiS, s.IDsocio, s.HorTrab, s.Pnom, s.Pape, p.Email
            FROM SOCIO s
            JOIN PERSONA p ON s.CiS = p.Ci
            ORDER BY s.CiS
        ";
        $res = $mysqli->query($sql);
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        json_ok_admin($rows);
        break;

    case 'banear_socio':
        $ci      = isset($data['ci']) ? (int)$data['ci'] : 0;
        $idsocio = isset($data['idsocio']) ? (int)$data['idsocio'] : 0;
        if (!$ci || !$idsocio) json_error_admin('Datos inválidos');

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("DELETE FROM SOCIO WHERE CiS = ? AND IDsocio = ?");
            $stmt->bind_param("ii", $ci, $idsocio);
            $stmt->execute();
            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            json_error_admin('Error al banear: ' . $e->getMessage(), 500);
        }
        json_ok_admin(['success' => true, 'message' => 'Socio baneado (ya no puede iniciar sesión)']);
        break;

    case 'pagos_socio':
        $ci      = isset($_GET['ci']) ? (int)$_GET['ci'] : 0;
        $idsocio = isset($_GET['idsocio']) ? (int)$_GET['idsocio'] : 0;
        if (!$ci || !$idsocio) json_error_admin('Parámetros inválidos');

        $stmt = $mysqli->prepare("
            SELECT P.Comprobante, P.Monto, P.TipoPago, P.FechaPago, P.PagoCompen
            FROM PAGO P
            JOIN MENSUALIDAD M
              ON P.Comprobante = M.Comprobante AND P.IDpago = M.IDpago
            WHERE M.CiS = ? AND M.IDsocio = ?
            ORDER BY P.FechaPago DESC
        ");
        $stmt->bind_param("ii", $ci, $idsocio);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        $today = date('Y-m-d');
        while ($r = $res->fetch_assoc()) {
            $estado = 'En fecha';
            if ((int)$r['PagoCompen'] === 1) {
                $estado = 'Aceptado';
            } else {
                if ($r['FechaPago'] < $today) {
                    $estado = 'Retrasado';
                }
            }
            $rows[] = [
                'Comprobante' => $r['Comprobante'],
                'Monto'       => $r['Monto'],
                'TipoPago'    => $r['TipoPago'],
                'FechaPago'   => $r['FechaPago'],
                'EstadoPago'  => $estado
            ];
        }
        json_ok_admin($rows);
        break;

    case 'crear_admin':
        $ci       = isset($data['ci']) ? (int)$data['ci'] : 0;
        $pnom     = trim($data['pnom'] ?? '');
        $pape     = trim($data['pape'] ?? '');
        $fechnac  = $data['fechnac'] ?? '';
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        if (!$ci || !$pnom || !$pape || !$fechnac || !$email || !$password) {
            json_error_admin('Faltan datos');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error_admin('Email inválido');
        }
        if (!ctype_digit($password)) {
            json_error_admin('La contraseña debe ser numérica');
        }
        $passInt = (int)$password;

        $mysqli->begin_transaction();
        try {
            // Persona
            $stmt = $mysqli->prepare("SELECT Ci FROM PERSONA WHERE Ci = ? OR Email = ?");
            $stmt->bind_param("is", $ci, $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                throw new Exception('Ya existe persona con ese CI o email');
            }

            $stmt = $mysqli->prepare("INSERT INTO PERSONA (Ci, Pnom, Pape, FechNac, Contraseña, Email) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssis", $ci, $pnom, $pape, $fechnac, $passInt, $email);
            $stmt->execute();

            $idadmin = newAdminId($mysqli);
            $stmt = $mysqli->prepare("
                INSERT INTO ADMINISTRADOR (CiA, IDadmin, Pnom, Pape, FechNac)
                VALUES (?,?,?,?,?)
            ");
            $stmt->bind_param("iisss", $ci, $idadmin, $pnom, $pape, $fechnac);
            $stmt->execute();

            $mysqli->commit();
        } catch (Throwable $e) {
            $mysqli->rollback();
            json_error_admin('Error al crear admin: ' . $e->getMessage(), 500);
        }

        json_ok_admin(['success' => true, 'message' => 'Administrador creado', 'IDadmin' => $idadmin]);
        break;

    default:
        json_error_admin('Acción no soportada', 404);
}
