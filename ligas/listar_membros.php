<?php


require '../includes/cors.php';
session_start();
require '../database/conection.php';

header('Content-Type: application/json');

$usuario_id_logado = $_SESSION['user_id'] ?? null;
if (!$usuario_id_logado) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Não autenticado"]);
    exit;
}

$liga_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($liga_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "ID da liga inválido"]);
    exit;
}

try {
    $stmt_check = $conexao->prepare("SELECT id FROM membros_liga WHERE liga_id = ? AND usuario_id = ?");
    $stmt_check->bind_param("ii", $liga_id, $usuario_id_logado);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Você não tem permissão para ver os membros desta liga."]);
        exit;
    }
    $stmt_check->close();

    $stmt = $conexao->prepare("
        SELECT u.id, u.nome 
        FROM membros_liga m 
        JOIN usuarios u ON m.usuario_id = u.id 
        WHERE m.liga_id = ?
        ORDER BY u.nome ASC
    ");
    $stmt->bind_param("i", $liga_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $membros = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(["success" => true, "membros" => $membros]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro no servidor: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Erro no servidor ao listar membros."]);
}

$conexao->close();
?>