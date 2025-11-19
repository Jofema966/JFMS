<?php
// api/admin_admins.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexion.php'; // aquí ya tienes $pdo

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ================== LISTAR ADMINISTRADORES ==================
        case 'listar_admins': {
            // Solo admin puede ver esto
            if (($_SESSION['role'] ?? '') !== 'admin') {
                json_response(['error' => 'No autorizado'], 403);
            }

            $stmt = $pdo->query("
                SELECT 
                    a.CiA         AS ci,
                    a.IDadmin     AS idadmin,
                    a.Pnom        AS pnom,
                    a.Pape        AS pape,
                    a.FechNac     AS fechnac,
                    p.Email       AS email
                FROM ADMINISTRADOR a
                JOIN PERSONA p ON p.Ci = a.CiA
                ORDER BY a.IDadmin ASC
            ");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response([
                'success' => true,
                'admins'  => $admins
            ]);
        }

        default:
            json_response(['error' => 'Acción no válida'], 400);
    }

} catch (PDOException $e) {
    json_response(['error' => 'Error SQL: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    json_response(['error' => 'Error: ' . $e->getMessage()], 500);
}
