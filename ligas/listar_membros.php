<?php

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}


session_start();

include '../database/conection.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "error" => "Não autenticado"]);
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "error" => "ID da liga não fornecido"]);
    exit;
}

$liga_id = intval($_GET["id"]);
$usuario_id_logado = $_SESSION["user_id"];

try {

    $stmt_check = $conexao->prepare("
        SELECT 
            (SELECT COUNT(*) FROM ligas WHERE id = ? AND criador_id = ?) as is_creator,
            (SELECT COUNT(*) FROM membros_liga WHERE liga_id = ? AND usuario_id = ?) as is_member
    ");
    $stmt_check->bind_param("iiii", $liga_id, $usuario_id_logado, $liga_id, $usuario_id_logado);
    $stmt_check->execute();
    $permission = $stmt_check->get_result()->fetch_assoc();

    if ($permission['is_creator'] == 0 && $permission['is_member'] == 0) {
        http_response_code(403); // Forbidden
        echo json_encode(["success" => false, "error" => "Você não tem permissão para ver os membros desta liga."]);
        exit;
    }

    $stmt = $conexao->prepare("
        SELECT u.id, u.nome 
        FROM membros_liga m 
        JOIN usuarios u ON m.usuario_id = u.id 
        WHERE m.liga_id = ?
    ");
    $stmt->bind_param("i", $liga_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $membros = [];
    while ($row = $result->fetch_assoc()) {
        $membros[] = $row;
    }

    $stmt_creator = $conexao->prepare("
        SELECT u.id, u.nome 
        FROM ligas l 
        JOIN usuarios u ON l.criador_id = u.id 
        WHERE l.id = ?
    ");
    $stmt_creator->bind_param("i", $liga_id);
    $stmt_creator->execute();
    $creator_info = $stmt_creator->get_result()->fetch_assoc();

    if ($creator_info) {
        $creator_in_list = false;
        foreach ($membros as $membro) {
            if ($membro['id'] == $creator_info['id']) {
                $creator_in_list = true;
                break;
            }
        }
        if (!$creator_in_list) {

            array_unshift($membros, $creator_info);
        }
    }

    echo json_encode([
        "success" => true,
        "membros" => $membros
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["success" => false, "error" => "Erro no servidor: " . $e->getMessage()]);
}

$conexao->close();

?>