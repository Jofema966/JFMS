<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_socio();
$periodo = $_POST['periodo'] ?? '';
$monto   = $_POST['monto'] ?? '';
$csrf    = $_POST['csrf'] ?? '';
if (!verify_csrf($csrf)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'CSRF inválido']); exit; }
if (!preg_match('/^\d{4}-\d{2}$/',$periodo)) { echo json_encode(['ok'=>false,'error'=>'Período inválido']); exit; }
if (!is_numeric($monto) || $monto < 0) { echo json_encode(['ok'=>false,'error'=>'Monto inválido']); exit; }
if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'Archivo requerido']); exit; }
$f = $_FILES['comprobante'];
if ($f['size'] > 8*1024*1024) { echo json_encode(['ok'=>false,'error'=>'Archivo demasiado grande']); exit; }
$mime = mime_content_type($f['tmp_name']) ?: 'application/octet-stream';
if (!in_array($mime, ['application/pdf','image/jpeg','image/png'], true)) { echo json_encode(['ok'=>false,'error'=>'Formato no permitido']); exit; }
$data = file_get_contents($f['tmp_name']);
$filename = basename($f['name']);
try {
  $pdo->beginTransaction();
  do {
    $comprobante = 'C' . substr((string)time(), -9);
    $idpago = str_pad((string)random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
    $q = $pdo->prepare('SELECT 1 FROM PAGO WHERE Comprobante=? AND IDpago=?');
    $q->execute([$comprobante, $idpago]);
  } while ($q->fetch());
  $fechaPago = $periodo.'-01';
  $pdo->prepare('INSERT INTO PAGO (Comprobante, IDpago, PagoCompen, Monto, TipoPago, FechaPago) VALUES (?, ?, NULL, ?, \'MENSUALIDAD\', ?)')->execute([$comprobante, $idpago, (int)$monto, $fechaPago]);
  $pdo->prepare('INSERT INTO MENSUALIDAD (CiS, IDsocio, Comprobante, IDpago, Estado) VALUES (?, ?, ?, ?, \'PENDIENTE\') ON DUPLICATE KEY UPDATE Estado=\'PENDIENTE\'')->execute([$_SESSION['socio_ci'], $_SESSION['socio_idsocio'], $comprobante, $idpago]);
  $stmt = $pdo->prepare('INSERT INTO PAGO_ARCHIVO (Comprobante, IDpago, filename, mime_type, data) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE filename=VALUES(filename), mime_type=VALUES(mime_type), data=VALUES(data), uploaded_at=CURRENT_TIMESTAMP');
  $stmt->bindParam(1, $comprobante);
  $stmt->bindParam(2, $idpago);
  $stmt->bindParam(3, $filename);
  $stmt->bindParam(4, $mime);
  $stmt->bindParam(5, $data, PDO::PARAM_LOB);
  $stmt->execute();
  $pdo->commit();
  echo json_encode(['ok'=>true, 'Comprobante'=>$comprobante, 'IDpago'=>$idpago]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error de servidor']);
}