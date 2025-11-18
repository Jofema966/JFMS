<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../session_helpers.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'socio') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$ci      = (int)($_SESSION['ci'] ?? 0);
$IDsocio = (int)($_SESSION['IDsocio'] ?? 0);
$action  = $_GET['action'] ?? '';

function json_error_socio($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg]);
    exit;
}

function json_ok_socio($payload = []) {
    echo json_encode($payload);
    exit;
}

switch ($action) {
    case 'perfil':
        $stmt = $mysqli->prepare("
            SELECT p.Ci, p.Pnom, p.Pape, p.Email, p.FechNac,
                   s.IDsocio, s.HorTrab
            FROM PERSONA p
            JOIN SOCIO s ON p.Ci = s.CiS
            WHERE p.Ci = ?
        ");
        $stmt->bind_param("i", $ci);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$row = $res->fetch_assoc()) {
            json_error_socio('No se encontró el perfil', 404);
        }
        json_ok_socio($row);
        break;

    case 'mensualidades':
        $stmt = $mysqli->prepare("
            SELECT P.Comprobante, P.Monto, P.TipoPago, P.FechaPago, P.PagoCompen
            FROM PAGO P
            JOIN MENSUALIDAD M
              ON P.Comprobante = M.Comprobante AND P.IDpago = M.IDpago
            WHERE M.CiS = ? AND M.IDsocio = ?
            ORDER BY P.FechaPago DESC
        ");
        $stmt->bind_param("ii", $ci, $IDsocio);
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
        json_ok_socio($rows);
        break;

    default:
        json_error_socio('Acción no soportada', 404);
}
