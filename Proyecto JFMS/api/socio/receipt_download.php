<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_socio();
$Comprobante = $_GET['c'] ?? '';
$IDpago = $_GET['i'] ?? '';
if ($Comprobante==='' || $IDpago==='') { http_response_code(400); exit('Parámetros faltantes'); }
$stmt = $pdo->prepare('
  SELECT pa.filename, pa.mime_type, pa.data
  FROM PAGO_ARCHIVO pa
  JOIN MENSUALIDAD m ON m.Comprobante=pa.Comprobante AND m.IDpago=pa.IDpago
  WHERE pa.Comprobante=? AND pa.IDpago=? AND m.CiS=? AND m.IDsocio=?
  LIMIT 1
');
$stmt->execute([$Comprobante, $IDpago, $_SESSION['socio_ci'], $_SESSION['socio_idsocio']]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Archivo no encontrado'); }
header('Content-Type: '.$row['mime_type']);
header('Content-Disposition: inline; filename="'.preg_replace('/[^a-zA-Z0-9_.-]/','_', $row['filename']).'"');
echo $row['data'];