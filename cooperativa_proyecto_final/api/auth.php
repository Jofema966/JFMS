<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php'; // $pdo

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function get_json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        /* ================== REGISTRO DE INTERESADO ================== */
        case 'registro': {
            $data = get_json_input();

            $ci       = (int)($data['ci'] ?? 0);
            $pnom     = trim($data['pnom'] ?? '');
            $pape     = trim($data['pape'] ?? '');
            $fechnac  = $data['fechnac'] ?? null;
            $email    = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');

            if (!$ci || !$pnom || !$pape || !$fechnac || !$email || !$password) {
                json_response(['error' => 'Faltan datos obligatorios'], 400);
            }

            // ¿Ya existe PERSONA con ese CI o email?
            $stmt = $pdo->prepare("SELECT Ci FROM PERSONA WHERE Ci = ? OR Email = ?");
            $stmt->execute([$ci, $email]);
            if ($stmt->fetch()) {
                json_response(['error' => 'Ya existe una persona con ese CI o email'], 400);
            }

            // Insertar PERSONA (contraseña numérica, la guardamos tal cual como int)
            $stmt = $pdo->prepare("
                INSERT INTO PERSONA (Ci, Pnom, Pape, FechNac, Contraseña, Email)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ci, $pnom, $pape, $fechnac, (int)$password, $email]);

            // Insertar en INTERESADO (pendiente de aprobación)
            $stmt = $pdo->prepare("
                INSERT INTO INTERESADO (CiU, Pnom, Pape, FechNac)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$ci, $pnom, $pape, $fechnac]);

            json_response(['success' => true, 'message' => 'Solicitud enviada. Un administrador debe aprobarla.']);
        }

        /* ================== LOGIN SOCIO ================== */
        case 'login_socio': {
            $data = get_json_input();
            $email    = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');

            if (!$email || !$password) {
                json_response(['error' => 'Email y contraseña son obligatorios'], 400);
            }

            // Buscar en PERSONA
            $stmt = $pdo->prepare("SELECT Ci, Contraseña FROM PERSONA WHERE Email = ?");
            $stmt->execute([$email]);
            $persona = $stmt->fetch();

            if (!$persona || (int)$persona['Contraseña'] !== (int)$password) {
                json_response(['error' => 'Credenciales incorrectas'], 401);
            }

            // Verificar que sea socio
            $stmt = $pdo->prepare("
                SELECT s.CiS, s.IDsocio, s.HorTrab, s.Pnom, s.Pape, s.FechNac, p.Email
                FROM SOCIO s
                JOIN PERSONA p ON p.Ci = s.CiS
                WHERE s.CiS = ?
            ");
            $stmt->execute([$persona['Ci']]);
            $socio = $stmt->fetch();

            if (!$socio) {
                json_response(['error' => 'Tu solicitud aún no fue aprobada o fuiste dado de baja'], 403);
            }

            $_SESSION['role']     = 'socio';
            $_SESSION['ci']       = $socio['CiS'];
            $_SESSION['idsocio']  = $socio['IDsocio'];
            $_SESSION['nombre']   = $socio['Pnom'];
            $_SESSION['apellido'] = $socio['Pape'];

            json_response(['success' => true]);
        }

        /* ================== LOGIN ADMIN ================== */
        case 'login_admin': {
            $data = get_json_input();
            $email    = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');

            if (!$email || !$password) {
                json_response(['error' => 'Email y contraseña son obligatorios'], 400);
            }

            // Buscar persona por email
            $stmt = $pdo->prepare("SELECT Ci, Contraseña, Pnom, Pape FROM PERSONA WHERE Email = ?");
            $stmt->execute([$email]);
            $persona = $stmt->fetch();

            if (!$persona || (int)$persona['Contraseña'] !== (int)$password) {
                json_response(['error' => 'Credenciales incorrectas'], 401);
            }

            // Verificar que sea administrador
            $stmt = $pdo->prepare("
                SELECT CiA, IDadmin
                FROM ADMINISTRADOR
                WHERE CiA = ?
            ");
            $stmt->execute([$persona['Ci']]);
            $admin = $stmt->fetch();

            if (!$admin) {
                json_response(['error' => 'No tienes permisos de administrador'], 403);
            }

            $_SESSION['role']     = 'admin';
            $_SESSION['ci']       = $admin['CiA'];
            $_SESSION['idadmin']  = $admin['IDadmin'];
            $_SESSION['nombre']   = $persona['Pnom'];
            $_SESSION['apellido'] = $persona['Pape'];

            json_response(['success' => true]);
        }

        /* ================== CREAR OTRO ADMIN (SOLO ADMIN) ================== */
        case 'crear_admin': {
            // Solo un admin logueado puede crear admins
            if (($_SESSION['role'] ?? '') !== 'admin') {
                json_response(['error' => 'No autorizado'], 403);
            }

            $data = get_json_input();

            $ci       = (int)($data['ci'] ?? 0);
            $pnom     = trim($data['pnom'] ?? '');
            $pape     = trim($data['pape'] ?? '');
            $fechnac  = $data['fechnac'] ?? null;
            $email    = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');

            if (!$ci || !$pnom || !$pape || !$fechnac || !$email || !$password) {
                json_response(['error' => 'Faltan datos obligatorios'], 400);
            }

            // Verificar que no exista PERSONA con ese CI o email
            $stmt = $pdo->prepare("SELECT Ci FROM PERSONA WHERE Ci = ? OR Email = ?");
            $stmt->execute([$ci, $email]);
            if ($stmt->fetch()) {
                json_response(['error' => 'Ya existe una persona con ese CI o email'], 400);
            }

            // Generar un IDadmin nuevo, comenzando por 120000000 si no hay ninguno
            $stmt = $pdo->query("SELECT MAX(IDadmin) AS maxid FROM ADMINISTRADOR");
            $row  = $stmt->fetch();
            $nextId = $row && $row['maxid'] ? ((int)$row['maxid'] + 1) : 120000000;

            $pdo->beginTransaction();

            // Insertar en PERSONA
            $stmt = $pdo->prepare("
                INSERT INTO PERSONA (Ci, Pnom, Pape, FechNac, Contraseña, Email)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ci, $pnom, $pape, $fechnac, (int)$password, $email]);

            // Insertar en ADMINISTRADOR
            $stmt = $pdo->prepare("
                INSERT INTO ADMINISTRADOR (CiA, IDadmin, Pnom, Pape, FechNac)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ci, $nextId, $pnom, $pape, $fechnac]);

            $pdo->commit();

            json_response([
                'success'  => true,
                'message'  => 'Administrador creado correctamente',
                'idadmin'  => $nextId,
                'ci'       => $ci,
            ]);
        }

        /* ================== LOGOUT ================== */
        case 'logout': {
            session_unset();
            session_destroy();
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
