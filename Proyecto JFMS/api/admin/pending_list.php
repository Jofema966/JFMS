
<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();
$stmt = $pdo->query("SELECT temp_id, Ci as ci, Pnom as pnom, Pape as pape, Email as email, DATE_FORMAT(FechNac,'%Y-%m-%d') as fechaNac, DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') as creado FROM PENDING_SOCIO ORDER BY created_at DESC");
echo json_encode(['ok'=>true,'data'=>$stmt->fetchAll()]);
