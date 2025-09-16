
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
  $pdo->beginTransaction();
  $sel = $pdo->prepare("SELECT * FROM PENDING_SOCIO WHERE temp_id=? FOR UPDATE");
  $sel->execute([$temp_id]);
  $p = $sel->fetch();
  if (!$p){ $pdo->rollBack(); echo json_encode(['ok'=>false,'error'=>'Solicitud no encontrada']); exit; }

  // persona existente?
  $ex = $pdo->prepare("SELECT Email FROM PERSONA WHERE Ci=?");
  $ex->execute([$p['Ci']]);
  if ($row = $ex->fetch()) {
    if ($row['Email'] !== $p['Email']) { $pdo->rollBack(); echo json_encode(['ok'=>false,'error'=>'CI ya existe con otro email']); exit; }
  } else {
    $insPer = $pdo->prepare("INSERT INTO PERSONA (Ci,Pnom,Pape,FechNac,`Contraseña`,Email) VALUES (?,?,?,?,?,?)");
    $insPer->execute([$p['Ci'],$p['Pnom'],$p['Pape'],$p['FechNac'],$p['Contraseña'],$p['Email']]);
  }

  // generar IDsocio único
  do {
    $ids = random_int(100000000, 999999999);
    $q = $pdo->prepare("SELECT 1 FROM SOCIO WHERE CiS=? AND IDsocio=?");
    $q->execute([$p['Ci'], $ids]);
  } while ($q->fetch());

  $insSoc = $pdo->prepare("INSERT INTO SOCIO (CiS,IDsocio,HorTrab,Pnom,Pape,FechNac) VALUES (?,?,?,?,?,?)");
  $insSoc->execute([$p['Ci'],$ids,0,$p['Pnom'],$p['Pape'],$p['FechNac']]);

  $del = $pdo->prepare("DELETE FROM PENDING_SOCIO WHERE temp_id=?");
  $del->execute([$temp_id]);

  $pdo->commit();
  echo json_encode(['ok'=>true,'socio_id'=>$ids]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Error de servidor']);
}
