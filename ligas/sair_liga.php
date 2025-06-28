<?php
require '../includes/cors.php';
session_start();
require '../database/conection.php';

header('Content-Type: application/json');

$usuario_id = $_SESSION['user_id'] ?? null;
if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
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

    if ($liga && $liga['criador_id'] == $usuario_id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'O criador não pode sair da própria liga.']);
        $conexao->rollback();
        exit;
    }

    $stmt_get_member_id = $conexao->prepare("SELECT id FROM membros_liga WHERE usuario_id = ? AND liga_id = ?");
    $stmt_get_member_id->bind_param("ii", $usuario_id, $liga_id);
    $stmt_get_member_id->execute();
    $membro = $stmt_get_member_id->get_result()->fetch_assoc();
    $stmt_get_member_id->close();

    if ($membro) {
        $membro_liga_id = $membro['id'];

        $stmt_delete_partidas = $conexao->prepare("DELETE FROM partidas WHERE membro_liga_id = ?");
        $stmt_delete_partidas->bind_param("i", $membro_liga_id);
        $stmt_delete_partidas->execute();
        $stmt_delete_partidas->close();
    }

    $stmt_delete_membro = $conexao->prepare("DELETE FROM membros_liga WHERE liga_id = ? AND usuario_id = ?");
    $stmt_delete_membro->bind_param("ii", $liga_id, $usuario_id);
    $stmt_delete_membro->execute();

    if ($stmt_delete_membro->affected_rows > 0) {
        $conexao->commit();
        echo json_encode(['success' => true]);
    } else {
        $conexao->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Usuário não é membro desta liga.']);
    }
    $stmt_delete_membro->close();

} catch (Exception $e) {
    $conexao->rollback();
    http_response_code(500);
    error_log("Erro ao sair da liga: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao sair da liga.']);
}

$conexao->close();
?>