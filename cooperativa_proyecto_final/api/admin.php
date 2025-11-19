<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php'; // $pdo

function json_response($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'No autorizado'], 401);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        /* ================== STATS ================== */
        case 'stats': {
            $stats = [];
            $stats['total_socios']     = (int)$pdo->query("SELECT COUNT(*) FROM SOCIO")->fetchColumn();
            $stats['total_pendientes'] = (int)$pdo->query("SELECT COUNT(*) FROM INTERESADO")->fetchColumn();
            $stats['total_obras']      = (int)$pdo->query("SELECT COUNT(*) FROM OBRA")->fetchColumn();
            $stats['total_pagos']      = (int)$pdo->query("SELECT COUNT(*) FROM PAGO")->fetchColumn();
            json_response($stats);
        }

        /* ================== SOLICITUDES (INTERESADO) ================== */
        case 'list_solicitudes': {
            $sql = "
                SELECT i.CiU,
                       p.Pnom,
                       p.Pape,
                       p.Email,
                       p.FechNac
                FROM INTERESADO i
                JOIN PERSONA p ON p.Ci = i.CiU
                ORDER BY p.FechNac DESC
            ";
            $rows = $pdo->query($sql)->fetchAll();
            json_response($rows);
        }

        case 'aprobar_socio': {
            $data = json_decode(file_get_contents('php://input'), true);
            $ci = (int)($data['ci'] ?? 0);
            if (!$ci) json_response(['error' => 'CI inválido'], 400);

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT CiU FROM INTERESADO WHERE CiU = ?");
            $stmt->execute([$ci]);
            if (!$stmt->fetch()) {
                $pdo->rollBack();
                json_response(['error' => 'El interesado no existe'], 404);
            }

            $stmt = $pdo->prepare("SELECT Pnom, Pape, FechNac FROM PERSONA WHERE Ci = ?");
            $stmt->execute([$ci]);
            $persona = $stmt->fetch();
            if (!$persona) {
                $pdo->rollBack();
                json_response(['error' => 'La persona no existe'], 404);
            }

            $nextId = (int)$pdo->query("SELECT IFNULL(MAX(IDsocio),0)+1 FROM SOCIO")->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO SOCIO (CiS, IDsocio, HorTrab, Pnom, Pape, FechNac)
                VALUES (?, ?, 0, ?, ?, ?)
            ");
            $stmt->execute([
                $ci,
                $nextId,
                $persona['Pnom'],
                $persona['Pape'],
                $persona['FechNac']
            ]);

            $stmt = $pdo->prepare("DELETE FROM INTERESADO WHERE CiU = ?");
            $stmt->execute([$ci]);

            $pdo->commit();
            json_response(['success' => true, 'idsocio' => $nextId]);
        }

        case 'rechazar_socio': {
            $data = json_decode(file_get_contents('php://input'), true);
            $ci = (int)($data['ci'] ?? 0);
            if (!$ci) json_response(['error' => 'CI inválido'], 400);

            $stmt = $pdo->prepare("DELETE FROM INTERESADO WHERE CiU = ?");
            $stmt->execute([$ci]);

            json_response(['success' => true]);
        }

        /* ================== SOCIOS ================== */
        case 'list_socios': {
            $sql = "
                SELECT s.CiS,
                       s.IDsocio,
                       s.HorTrab,
                       s.Pnom,
                       s.Pape,
                       p.Email,
                       s.FechNac
                FROM SOCIO s
                JOIN PERSONA p ON p.Ci = s.CiS
                ORDER BY s.IDsocio
            ";
            $rows = $pdo->query($sql)->fetchAll();
            json_response($rows);
        }

        case 'banear_socio': {
            $data = json_decode(file_get_contents('php://input'), true);
            $ci      = (int)($data['ci'] ?? 0);
            $idsocio = (int)($data['idsocio'] ?? 0);
            if (!$ci || !$idsocio) json_response(['error' => 'Datos inválidos'], 400);

            $stmt = $pdo->prepare("DELETE FROM SOCIO WHERE CiS = ? AND IDsocio = ?");
            $stmt->execute([$ci, $idsocio]);

            json_response(['success' => true]);
        }

        case 'pagos_socio': {
            $ci      = (int)($_GET['ci'] ?? 0);
            $idsocio = (int)($_GET['idsocio'] ?? 0);
            if (!$ci || !$idsocio) json_response(['error' => 'Datos inválidos'], 400);

            $sql = "
                SELECT p.Comprobante,
                       p.IDpago,
                       p.Monto,
                       p.TipoPago,
                       p.FechaPago,
                       p.Estado,
                       p.MotivoRechazo,
                       p.ArchivoPDF
                FROM MENSUALIDAD m
                JOIN PAGO p
                  ON p.Comprobante = m.Comprobante
                 AND p.IDpago = m.IDpago
                WHERE m.CiS = ? AND m.IDsocio = ?
                ORDER BY p.FechaPago DESC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ci, $idsocio]);
            $rows = $stmt->fetchAll();
            json_response($rows);
        }

        /* ================== OBRAS ================== */
        case 'list_obras': {
            $sql = "
                SELECT IDobr, Estado, TipoObr, FechInicio, FechFin
                FROM OBRA
                ORDER BY FechInicio DESC
            ";
            $rows = $pdo->query($sql)->fetchAll();
            json_response($rows);
        }

        case 'crear_obra': {
            $data = json_decode(file_get_contents('php://input'), true);
            $estado    = trim($data['estado'] ?? '');
            $tipo      = trim($data['tipo'] ?? '');
            $inicio    = $data['fechInicio'] ?? null;
            $fin       = $data['fechFin'] ?? null;

            if (!$estado || !$tipo || !$inicio || !$fin) {
                json_response(['error' => 'Datos incompletos'], 400);
            }

            $nextId = (int)$pdo->query("SELECT IFNULL(MAX(IDobr),0)+1 FROM OBRA")->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO OBRA (IDobr, Estado, TipoObr, FechInicio, FechFin)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nextId, $estado, $tipo, $inicio, $fin]);

            json_response(['success' => true, 'id' => $nextId]);
        }

        /* ================== PAGOS (GLOBAL) ================== */
        case 'list_pagos': {
            $sql = "
                SELECT p.Comprobante,
                       p.IDpago,
                       p.Monto,
                       p.TipoPago,
                       p.FechaPago,
                       p.Estado,
                       p.MotivoRechazo,
                       p.ArchivoPDF,
                       m.CiS,
                       m.IDsocio
                FROM PAGO p
                LEFT JOIN MENSUALIDAD m
                  ON p.Comprobante = m.Comprobante
                 AND p.IDpago = m.IDpago
                ORDER BY p.FechaPago DESC
            ";
            $rows = $pdo->query($sql)->fetchAll();
            json_response($rows);
        }

        case 'aprobar_pago': {
            $data = json_decode(file_get_contents('php://input'), true);
            $comp  = $data['comprobante'] ?? '';
            $idpag = $data['idpago'] ?? '';
            if (!$comp || !$idpag) json_response(['error' => 'Datos inválidos'], 400);

            $stmt = $pdo->prepare("
                UPDATE PAGO
                   SET Estado = 'aceptado',
                       MotivoRechazo = NULL
                 WHERE Comprobante = ? AND IDpago = ?
            ");
            $stmt->execute([$comp, $idpag]);

            json_response(['success' => true]);
        }

        case 'rechazar_pago': {
            $data   = json_decode(file_get_contents('php://input'), true);
            $comp   = $data['comprobante'] ?? '';
            $idpag  = $data['idpago'] ?? '';
            $motivo = trim($data['motivo'] ?? '');
            if (!$comp || !$idpag || !$motivo) json_response(['error' => 'Datos inválidos'], 400);

            $stmt = $pdo->prepare("
                UPDATE PAGO
                   SET Estado = 'rechazado',
                       MotivoRechazo = ?
                 WHERE Comprobante = ? AND IDpago = ?
            ");
            $stmt->execute([$motivo, $comp, $idpag]);

            json_response(['success' => true]);
        }

        /* ================== CREAR OTRO ADMIN ================== */
        case 'crear_admin': {
            $data = json_decode(file_get_contents('php://input'), true);

            $ci        = (int)($data['ci'] ?? 0);
            $pnom      = trim($data['pnom'] ?? '');
            $pape      = trim($data['pape'] ?? '');
            $fechnac   = $data['fechnac'] ?? null;
            $email     = trim($data['email'] ?? '');
            $password  = trim($data['password'] ?? '');

            if (!$ci || !$pnom || !$pape || !$fechnac || !$email || !$password) {
                json_response(['error' => 'Datos incompletos'], 400);
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT Ci FROM PERSONA WHERE Ci = ? OR Email = ?");
            $stmt->execute([$ci, $email]);
            $existe = $stmt->fetch();

            if (!$existe) {
                $stmt = $pdo->prepare("
                    INSERT INTO PERSONA (Ci, Pnom, Pape, FechNac, Contraseña, Email)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$ci, $pnom, $pape, $fechnac, (int)$password, $email]);
            }

            $nextId = (int)$pdo->query("SELECT IFNULL(MAX(IDadmin),119999999)+1 FROM ADMINISTRADOR")->fetchColumn();
            if ($nextId < 120000000) {
                $nextId = 120000001;
            }

            $stmt = $pdo->prepare("
                INSERT INTO ADMINISTRADOR (CiA, IDadmin, Pnom, Pape, FechNac)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ci, $nextId, $pnom, $pape, $fechnac]);

            $pdo->commit();
            json_response(['success' => true, 'idadmin' => $nextId]);
        }

        default:
            json_response(['error' => 'Acción no válida'], 400);
    }

} catch (PDOException $e) {
    json_response(['error' => 'Error SQL: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    json_response(['error' => 'Error: ' . $e->getMessage()], 500);
}
