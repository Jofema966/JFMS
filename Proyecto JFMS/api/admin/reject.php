
<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$temp_id = (int)($in['temp_id'] ?? 0);
if ($temp_id<=0){ echo json_encode(['ok'=>false,'error'=>'temp_id inválido']); exit; }

try {
  $stmt = $pdo->prepare("DELETE FROM PENDING_SOCIO WHERE temp_id=?");
  $stmt->execute([$temp_id]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error de servidor']);
}
