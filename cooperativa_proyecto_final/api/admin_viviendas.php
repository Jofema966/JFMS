<?php
// api/admin_viviendas.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php'; // Debe definir $pdo (PDO a cooperativa_bd)

function json_response($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Solo admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'No autorizado'], 401);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        /* ============================================================
         * LISTA DE SOCIOS CON HORAS Y VIVIENDA
         * GET api/admin_viviendas.php?action=list_socios
         * ============================================================ */
        case 'list_socios': {
            $sql = "
                SELECT 
                    s.CiS,
                    s.IDsocio,
                    s.Pnom,
                    s.Pape,
                    s.HorTrab,
                    s.FechNac,
                    p.Email,
                    v.Bloque,
                    v.NumPuerta
                FROM SOCIO s
                JOIN PERSONA p   ON p.Ci = s.CiS
                LEFT JOIN ASIGNA asg
                       ON asg.CiS = s.CiS
                LEFT JOIN ADMINISTRA adm
                       ON adm.CiA = asg.CiA
                      AND adm.IDadmin = asg.IDadmin
                      AND adm.NumPuerta = asg.NumPuerta
                LEFT JOIN VIVIENDA v
                       ON v.NumPuerta = adm.NumPuerta
                ORDER BY s.Pape, s.Pnom
            ";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_response($rows);
        }

        /* ============================================================
         * LISTAR TODOS LOS ADMINS (para selects)
         * GET api/admin_viviendas.php?action=list_admins
         * ============================================================ */
        case 'list_admins': {
            $sql = "
                SELECT 
                    a.CiA,
                    a.IDadmin,
                    a.Pnom,
                    a.Pape
                FROM ADMINISTRADOR a
                ORDER BY a.Pape, a.Pnom
            ";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_response($rows);
        }

        /* ============================================================
         * LISTAR TODOS LOS SOCIOS (para selects sencillos)
         * GET api/admin_viviendas.php?action=list_socios_simple
         * ============================================================ */
        case 'list_socios_simple': {
            $sql = "
                SELECT 
                    s.CiS,
                    s.IDsocio,
                    s.Pnom,
                    s.Pape
                FROM SOCIO s
                ORDER BY s.Pape, s.Pnom
            ";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_response($rows);
        }

        /* ============================================================
         * CREAR VIVIENDA
         * POST JSON { bloque, num_puerta }
         * ============================================================ */
        case 'crear_vivienda': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['error' => 'Método no permitido'], 405);
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $bloque    = (int)($data['bloque'] ?? 0);
            $numPuerta = (int)($data['num_puerta'] ?? 0);

            if ($bloque <= 0 || $numPuerta <= 0) {
                json_response(['error' => 'Datos de vivienda inválidos'], 400);
            }

            // Comprobar si ya existe
            $stmt = $pdo->prepare("SELECT 1 FROM VIVIENDA WHERE NumPuerta = ?");
            $stmt->execute([$numPuerta]);
            if ($stmt->fetch()) {
                json_response(['error' => 'Ya existe una vivienda con ese número de puerta'], 409);
            }

            $stmt = $pdo->prepare("INSERT INTO VIVIENDA (Bloque, NumPuerta) VALUES (?, ?)");
            $stmt->execute([$bloque, $numPuerta]);

            json_response(['success' => true]);
        }

        /* ============================================================
         * LISTAR VIVIENDAS
         * GET api/admin_viviendas.php?action=list_viviendas
         * ============================================================ */
        case 'list_viviendas': {
            $sql = "
                SELECT Bloque, NumPuerta
                FROM VIVIENDA
                ORDER BY Bloque, NumPuerta
            ";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_response($rows);
        }

        /* ============================================================
         * ASIGNAR VIVIENDA A UN ADMIN (ADMINISTRA)
         * POST JSON { cia, idadmin, num_puerta }
         * ============================================================ */
        case 'asignar_vivienda_admin': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['error' => 'Método no permitido'], 405);
            }

            $data      = json_decode(file_get_contents('php://input'), true) ?? [];
            $ciA       = (int)($data['cia'] ?? 0);
            $idAdmin   = (int)($data['idadmin'] ?? 0);
            $numPuerta = (int)($data['num_puerta'] ?? 0);

            if (!$ciA || !$idAdmin || !$numPuerta) {
                json_response(['error' => 'Datos incompletos'], 400);
            }

            // Verificar admin
            $stmt = $pdo->prepare("SELECT 1 FROM ADMINISTRADOR WHERE CiA = ? AND IDadmin = ?");
            $stmt->execute([$ciA, $idAdmin]);
            if (!$stmt->fetch()) {
                json_response(['error' => 'Administrador no encontrado'], 404);
            }

            // Verificar vivienda
            $stmt = $pdo->prepare("SELECT 1 FROM VIVIENDA WHERE NumPuerta = ?");
            $stmt->execute([$numPuerta]);
            if (!$stmt->fetch()) {
                json_response(['error' => 'Vivienda no encontrada'], 404);
            }

            // Evitar duplicados
            $stmt = $pdo->prepare("
                SELECT 1 FROM ADMINISTRA
                WHERE CiA = ? AND IDadmin = ? AND NumPuerta = ?
            ");
            $stmt->execute([$ciA, $idAdmin, $numPuerta]);
            if ($stmt->fetch()) {
                json_response(['success' => true, 'message' => 'Ya estaba asignado']);
            }

            $stmt = $pdo->prepare("
                INSERT INTO ADMINISTRA (CiA, IDadmin, NumPuerta)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$ciA, $idAdmin, $numPuerta]);

            json_response(['success' => true]);
        }

        /* ============================================================
         * LISTAR VIVIENDAS ADMINISTRADAS (ADMINISTRA)
         * GET api/admin_viviendas.php?action=list_viviendas_admin
         * ============================================================ */
        case 'list_viviendas_admin': {
            $sql = "
                SELECT 
                    adm.CiA,
                    adm.IDadmin,
                    adm.NumPuerta,
                    v.Bloque,
                    v.NumPuerta AS ViviendaNum,
                    a.Pnom,
                    a.Pape
                FROM ADMINISTRA adm
                JOIN VIVIENDA v
                  ON v.NumPuerta = adm.NumPuerta
                JOIN ADMINISTRADOR a
                  ON a.CiA = adm.CiA AND a.IDadmin = adm.IDadmin
                ORDER BY v.Bloque, v.NumPuerta
            ";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_response($rows);
        }

        /* ============================================================
         * ASIGNAR VIVIENDA + ADMIN A UN SOCIO (ASIGNA)
         * POST JSON { cis, idsocio, cia, idadmin, num_puerta }
         * ============================================================ */
        case 'asignar_vivienda_socio': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_response(['error' => 'Método no permitido'], 405);
            }

            $data      = json_decode(file_get_contents('php://input'), true) ?? [];
            $ciS       = (int)($data['cis'] ?? 0);
            $idSocio   = (int)($data['idsocio'] ?? 0);
            $ciA       = (int)($data['cia'] ?? 0);
            $idAdmin   = (int)($data['idadmin'] ?? 0);
            $numPuerta = (int)($data['num_puerta'] ?? 0);

            if (!$ciS || !$idSocio || !$ciA || !$idAdmin || !$numPuerta) {
                json_response(['error' => 'Datos incompletos'], 400);
            }

            // Verificar socio
            $stmt = $pdo->prepare("SELECT 1 FROM SOCIO WHERE CiS = ? AND IDsocio = ?");
            $stmt->execute([$ciS, $idSocio]);
            if (!$stmt->fetch()) {
                json_response(['error' => 'Socio no encontrado'], 404);
            }

            // Verificar relación admin-vivienda
            $stmt = $pdo->prepare("
                SELECT 1 FROM ADMINISTRA
                WHERE CiA = ? AND IDadmin = ? AND NumPuerta = ?
            ");
            $stmt->execute([$ciA, $idAdmin, $numPuerta]);
            if (!$stmt->fetch()) {
                json_response(['error' => 'El admin no administra esa vivienda'], 400);
            }

            // Evitar duplicado
            $stmt = $pdo->prepare("
                SELECT 1 FROM ASIGNA
                WHERE CiS = ? AND CiA = ? AND IDadmin = ? AND NumPuerta = ?
            ");
            $stmt->execute([$ciS, $ciA, $idAdmin, $numPuerta]);
            if ($stmt->fetch()) {
                json_response(['success' => true, 'message' => 'Ya estaba asignado']);
            }

            $stmt = $pdo->prepare("
                INSERT INTO ASIGNA (CiS, CiA, IDadmin, NumPuerta)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$ciS, $ciA, $idAdmin, $numPuerta]);

            json_response(['success' => true]);
        }

        default:
            json_response(['error' => 'Acción no válida'], 400);
    }

} catch (PDOException $e) {
    json_response(['error' => 'Error SQL: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    json_response(['error' => 'Error: ' . $e->getMessage()], 500);
}
