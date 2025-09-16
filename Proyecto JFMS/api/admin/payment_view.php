<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();
$c = $_GET['c'] ?? '';
$i = $_GET['i'] ?? '';
if ($c==='' || $i===''){ http_response_code(400); exit('Parámetros faltantes'); }
$stmt = $pdo->prepare('SELECT filename, mime_type, data FROM PAGO_ARCHIVO WHERE Comprobante=? AND IDpago=? LIMIT 1');
$stmt->execute([$c,$i]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Archivo no encontrado'); }
header('Content-Type: '.$row['mime_type']);
header('Content-Disposition: inline; filename="'.preg_replace('/[^a-zA-Z0-9_.-]/','_', $row['filename']).'"');
echo $row['data'];