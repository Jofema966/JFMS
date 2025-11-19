<?php
// api/socio.php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

[$ci, $idsocio] = requireSocioSessionForApi();
$pdo = db();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {

    switch ($action) {

        // ===================== DASHBOARD =====================
        case 'dashboard':
            $stmt = $pdo->prepare("
                SELECT p.Pnom, p.Pape, s.HorTrab
                FROM SOCIO s
                JOIN PERSONA p ON p.Ci = s.CiS
                WHERE s.CiS = :ci AND s.IDsocio = :idsocio
                LIMIT 1
            ");
            $stmt->execute([':ci' => $ci, ':idsocio' => $idsocio]);
            $row = $stmt->fetch();

            if (!$row) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Socio no encontrado',
                ]);
                exit;
            }

            $stmt2 = $pdo->prepare("
                SELECT COUNT(*) AS total
                FROM MENSUALIDAD m
                WHERE m.CiS = :ci AND m.IDsocio = :idsocio
            ");
            $stmt2->execute([':ci' => $ci, ':idsocio' => $idsocio]);
            $totalPagos = (int)$stmt2->fetch()['total'];

            echo json_encode([
                'status' => 'ok',
                'data'   => [
                    'nombre'            => $row['Pnom'] . ' ' . $row['Pape'],
                    'horas_semana'      => (int)$row['HorTrab'],
                    'pagos_registrados' => $totalPagos,
                ],
            ]);
            break;

        // ===================== PERFIL =====================
        case 'perfil':
            $stmt = $pdo->prepare("
                SELECT p.Ci, p.Pnom, p.Pape, p.Email, p.FechNac,
                       s.IDsocio, s.HorTrab
                FROM SOCIO s
                JOIN PERSONA p ON p.Ci = s.CiS
                WHERE s.CiS = :ci AND s.IDsocio = :idsocio
                LIMIT 1
            ");
            $stmt->execute([':ci' => $ci, ':idsocio' => $idsocio]);
            $row = $stmt->fetch();

            if (!$row) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'No se encontraron datos del socio',
                ]);
                break;
            }

            echo json_encode([
                'status' => 'ok',
                'data'   => $row,
            ]);
            break;

        // ===================== HORAS =====================
        case 'get_horas':
            $stmt = $pdo->prepare("
                SELECT HorTrab
                FROM SOCIO
                WHERE CiS = :ci AND IDsocio = :idsocio
                LIMIT 1
            ");
            $stmt->execute([':ci' => $ci, ':idsocio' => $idsocio]);
            $row = $stmt->fetch();

            echo json_encode([
                'status' => 'ok',
                'data'   => [
                    'horas' => $row ? (int)$row['HorTrab'] : 0,
                ],
            ]);
            break;

        case 'set_horas':
            $horas = isset($_POST['horas']) ? (int)$_POST['horas'] : 0;

            $stmt = $pdo->prepare("
                UPDATE SOCIO
                SET HorTrab = :horas
                WHERE CiS = :ci AND IDsocio = :idsocio
            ");
            $stmt->execute([
                ':horas'   => $horas,
                ':ci'      => $ci,
                ':idsocio' => $idsocio,
            ]);

            echo json_encode([
                'status'  => 'ok',
                'message' => 'Horas actualizadas',
            ]);
            break;

        // ===================== PAGOS =====================
        case 'pagos_list':
            // Tu tabla PAGO original no tiene Estado ni Archivo:
            // los devolvemos como literales para no romper el frontend.
            $stmt = $pdo->prepare("
                SELECT 
                    p.Comprobante,
                    p.Monto,
                    p.TipoPago,
                    p.FechaPago,
                    'pendiente' AS Estado,
                    '' AS Archivo
                FROM MENSUALIDAD m
                JOIN PAGO p ON p.Comprobante = m.Comprobante
                           AND p.IDpago      = m.IDpago
                WHERE m.CiS = :ci AND m.IDsocio = :idsocio
                ORDER BY p.FechaPago DESC
            ");
            $stmt->execute([':ci' => $ci, ':idsocio' => $idsocio]);
            $rows = $stmt->fetchAll();

            echo json_encode([
                'status' => 'ok',
                'data'   => $rows,
            ]);
            break;

        case 'pagos_subir':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Método no permitido',
                ]);
                break;
            }

            $monto    = isset($_POST['monto']) ? (int)$_POST['monto'] : 0;
            $tipoPago = $_POST['tipo_pago'] ?? 'Transferencia';

            if ($monto <= 0) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Monto inválido',
                ]);
                break;
            }

            if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Error al subir el archivo',
                ]);
                break;
            }

            $file = $_FILES['comprobante'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'El comprobante debe ser un PDF',
                ]);
                break;
            }

            // Guardamos el archivo físicamente (aunque la ruta no quede en la BD original)
            $uploadDir = __DIR__ . '/../uploads/pagos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $idPago          = substr(uniqid('', true), -9);
            $comprobanteCode = 'COMP-' . $idPago;
            $fileName        = $comprobanteCode . '.pdf';
            $destPath        = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'No se pudo guardar el archivo',
                ]);
                break;
            }

            $pdo->beginTransaction();

            // PAGO según tu definición original
            $stmt = $pdo->prepare("
                INSERT INTO PAGO (Comprobante, IDpago, PagoCompen, Monto, TipoPago, FechaPago)
                VALUES (:comp, :id, NULL, :monto, :tipo, CURDATE())
            ");
            $stmt->execute([
                ':comp'  => $comprobanteCode,
                ':id'    => $idPago,
                ':monto' => $monto,
                ':tipo'  => $tipoPago,
            ]);

            // Relación con el socio
            $stmt = $pdo->prepare("
                INSERT INTO MENSUALIDAD (CiS, IDsocio, Comprobante, IDpago)
                VALUES (:ci, :idsocio, :comp, :id)
            ");
            $stmt->execute([
                ':ci'      => $ci,
                ':idsocio' => $idsocio,
                ':comp'    => $comprobanteCode,
                ':id'      => $idPago,
            ]);

            $pdo->commit();

            echo json_encode([
                'status'  => 'ok',
                'message' => 'Comprobante enviado correctamente',
            ]);
            break;

        // ===================== VIVIENDA / OBRAS =====================
        case 'vivienda_obras':
            // Vivienda asignada
            $stmt = $pdo->prepare("
                SELECT v.Bloque, v.NumPuerta
                FROM ASIGNA a
                JOIN VIVIENDA v ON v.NumPuerta = a.NumPuerta
                WHERE a.CiS = :ci
                LIMIT 1
            ");
            $stmt->execute([':ci' => $ci]);
            $vivienda = $stmt->fetch() ?: null;

            // Obras (todas las OBRA)
            $stmt2 = $pdo->query("
                SELECT IDobr, TipoObr, Estado, FechInicio, FechFin
                FROM OBRA
                ORDER BY FechInicio DESC
            ");
            $obras = $stmt2->fetchAll();

            echo json_encode([
                'status' => 'ok',
                'data'   => [
                    'vivienda' => $vivienda,
                    'obras'    => $obras,
                ],
            ]);
            break;

        // ===================== DEFAULT =====================
        default:
            echo json_encode([
                'status'  => 'error',
                'message' => 'Acción no válida',
            ]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error en el servidor',
        // Deja este detail activado mientras depuras. Si quieres, luego lo quitas.
        'detail'  => $e->getMessage(),
    ]);
}
