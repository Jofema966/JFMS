
<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function verify_csrf(string $t): bool { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }

function require_admin(): void {
  if (empty($_SESSION['admin_idadmin'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
}
function require_socio(): void {
  if (empty($_SESSION['socio_ci']) || empty($_SESSION['socio_idsocio'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
}
