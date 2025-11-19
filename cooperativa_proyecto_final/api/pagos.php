<?php
// api/pagos.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ==== CONEXIÓN A BD (ajusta user/pass si hace falta) ====
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=cooperativa_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión BD: ' . $e->getMessage()]);
    exit;
}

// ==== HELPERS DE SESIÓN ====
function requireSocioCi(PDO $pdo): array {
    if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado (socio)']);
        exit;
    }
    if (empty($_SESSION['ci'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No se encontró CI en la sesión']);
        exit;
    }

    $ci = (int)$_SESSION['ci'];

    // Obtener IDsocio desde la tabla SOCIO
    $st = $pdo->prepare("SELECT IDsocio FROM socio WHERE CiS = :ci LIMIT 1");
    $st->execute([':ci' => $ci]);
    $row = $st->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró el socio para los pagos']);
        exit;
    }

    return ['ci' => $ci, 'idSocio' => (int)$row['IDsocio']];
}

function requireAdmin(): void {
    if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado (admin)']);
        exit;
    }
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        /* --------------------------------------------------
         * SOCIO: LISTAR SUS PAGOS / MENSUALIDADES
         * -------------------------------------------------- */
        case 'list_mis_pagos': {
            $socio = requireSocioCi($pdo);
            $ci = $socio['ci'];
            $idSocio = $socio['idSocio'];

            $sql = "
                SELECT 
                    p.Comprobante,
                    p.Monto,
                    p.TipoPago,
                    p.FechaPago,
                    p.Estado          AS estado,
                    p.MotivoRechazo   AS motivo_rechazo,
                    p.Archivo         AS archivo
                FROM mensualidad m
                INNER JOIN pago p 
                    ON p.Comprobante = m.Comprobante 
                   AND p.IDpago      = m.IDpago
                WHERE m.CiS = :ci
                  AND m.IDsocio = :idSocio
                ORDER BY p.FechaPago DESC
            ";
            $st = $pdo->prepare($sql);
            $st->execute([
                ':ci'      => $ci,
                ':idSocio' => $idSocio,
            ]);
            $pagos = $st->fetchAll();

            // Armar URLs del archivo
            foreach ($pagos as &$p) {
                if (!empty($p['archivo'])) {
                    $p['archivo_url'] = '../uploads/comprobantes/' . $p['archivo'];
                } else {
                    $p['archivo_url'] = null;
                }
            }

            echo json_encode(['pagos' => $pagos]);
            break;
        }

        /* --------------------------------------------------
         * SOCIO: SUBIR COMPROBANTE DE PAGO (PDF)
         * -------------------------------------------------- */
        case 'subir': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                exit;
            }

            $socio = requireSocioCi($pdo);
            $ci = $socio['ci'];
            $idSocio = $socio['idSocio'];

            // Validar campos básicos
            $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
            $tipo  = $_POST['tipo_pago'] ?? '';
            if ($monto <= 0 || $tipo === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Monto o tipo de pago inválidos']);
                exit;
            }

            if (empty($_FILES['comprobante_pdf']) || $_FILES['comprobante_pdf']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'Archivo de comprobante requerido']);
                exit;
            }

            $file = $_FILES['comprobante_pdf'];
            if ($file['type'] !== 'application/pdf') {
                http_response_code(400);
                echo json_encode(['error' => 'Solo se permiten archivos PDF']);
                exit;
            }

            // Carpeta de uploads
            $uploadDir = realpath(__DIR__ . '/../uploads');
            if ($uploadDir === false) {
                $uploadDir = __DIR__ . '/../uploads';
            }
            $subDir = $uploadDir . '/comprobantes';
            if (!is_dir($subDir)) {
                mkdir($subDir, 0777, true);
            }

            // Nombre único
            $baseName = 'cp_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $destPath = $subDir . '/' . $baseName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                http_response_code(500);
                echo json_encode(['error' => 'No se pudo guardar el archivo']);
                exit;
            }

            // Generar IDs de pago
            $idPago      = (int)time();
            $comprobante = 'CP-' . $idPago;

            // Insert en PAGO
            $sqlPago = "
                INSERT INTO pago 
                    (Comprobante, IDpago, PagoCompen, Monto, TipoPago, FechaPago, Estado, MotivoRechazo, Archivo)
                VALUES 
                    (:comp, :idpago, NULL, :monto, :tipo, CURDATE(), 'pendiente', NULL, :archivo)
            ";
            $stPago = $pdo->prepare($sqlPago);
            $stPago->execute([
                ':comp'    => $comprobante,
                ':idpago'  => $idPago,
                ':monto'   => $monto,
                ':tipo'    => $tipo,
                ':archivo' => $baseName,
            ]);

            // Insert en MENSUALIDAD
            $sqlMen = "
                INSERT INTO mensualidad (CiS, IDsocio, Comprobante, IDpago)
                VALUES (:ci, :idSocio, :comp, :idpago)
            ";
            $stMen = $pdo->prepare($sqlMen);
            $stMen->execute([
                ':ci'      => $ci,
                ':idSocio' => $idSocio,
                ':comp'    => $comprobante,
                ':idpago'  => $idPago,
            ]);

            echo json_encode([
                'ok'          => true,
                'comprobante' => $comprobante,
                'idPago'      => $idPago,
                'archivo'     => $baseName,
            ]);
            break;
        }

        /* --------------------------------------------------
         * ADMIN: LISTAR TODOS LOS PAGOS (para backoffice)
         * -------------------------------------------------- */
        case 'list_all': {
            requireAdmin();

            $sql = "
                SELECT 
                    p.Comprobante,
                    p.IDpago,
                    p.Monto,
                    p.TipoPago,
                    p.FechaPago,
                    p.Estado        AS estado,
                    p.MotivoRechazo AS motivo_rechazo,
                    p.Archivo       AS archivo,
                    m.CiS,
                    m.IDsocio
                FROM pago p
                LEFT JOIN mensualidad m 
                    ON m.Comprobante = p.Comprobante 
                   AND m.IDpago      = p.IDpago
                ORDER BY p.FechaPago DESC
            ";
            $pagos = $pdo->query($sql)->fetchAll();

            foreach ($pagos as &$p) {
                if (!empty($p['archivo'])) {
                    $p['archivo_url'] = '../uploads/comprobantes/' . $p['archivo'];
                } else {
                    $p['archivo_url'] = null;
                }
            }

            echo json_encode(['pagos' => $pagos]);
            break;
        }

        /* --------------------------------------------------
         * ADMIN: CAMBIAR ESTADO DE UN PAGO (aceptar/rechazar)
         * -------------------------------------------------- */
        case 'cambiar_estado': {
            requireAdmin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                exit;
            }

            $body   = json_decode(file_get_contents('php://input'), true);
            $comp   = $body['comprobante']   ?? null;
            $idPago = $body['idpago']        ?? null;
            $estado = $body['estado']        ?? null;
            $motivo = $body['motivo']        ?? null;

            if (!$comp || !$idPago || !in_array($estado, ['aceptado', 'rechazado', 'pendiente'], true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }

            $sql = "
                UPDATE pago
                SET Estado = :estado,
                    MotivoRechazo = :motivo
                WHERE Comprobante = :comp
                  AND IDpago      = :idpago
            ";
            $st = $pdo->prepare($sql);
            $st->execute([
                ':estado' => $estado,
                ':motivo' => $motivo,
                ':comp'   => $comp,
                ':idpago' => $idPago,
            ]);

            echo json_encode(['ok' => true]);
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida en api/pagos.php']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
}
