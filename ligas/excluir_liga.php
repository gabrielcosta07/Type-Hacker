<?php

require '../includes/cors.php';
session_start();
require '../database/conection.php';

header('Content-Type: application/json');


$usuario_id = $_SESSION['user_id'] ?? null;
if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);
$liga_id = $data['liga_id'] ?? null;

if (empty($liga_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID da liga não fornecido.']);
    exit;
}

$conexao->begin_transaction();

try {

    $stmt_check = $conexao->prepare("SELECT criador_id FROM ligas WHERE id = ?");
    $stmt_check->bind_param("i", $liga_id);
    $stmt_check->execute();
    $liga = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$liga || $liga['criador_id'] != $usuario_id) {

        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Você não tem permissão para excluir esta liga.']);
        $conexao->rollback();
        exit;
    }

    $stmt_delete_membros = $conexao->prepare("DELETE FROM membros_liga WHERE liga_id = ?");
    $stmt_delete_membros->bind_param("i", $liga_id);
    $stmt_delete_membros->execute();
    $stmt_delete_membros->close();

    $stmt_delete_liga = $conexao->prepare("DELETE FROM ligas WHERE id = ?");
    $stmt_delete_liga->bind_param("i", $liga_id);
    $stmt_delete_liga->execute();
    $stmt_delete_liga->close();

    $conexao->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conexao->rollback();
    http_response_code(500);
    error_log("Erro ao excluir liga: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao excluir a liga.']);
}

$conexao->close();
?>