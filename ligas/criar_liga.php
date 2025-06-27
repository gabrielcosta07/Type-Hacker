<?php


require '../includes/cors.php';
session_start();
require '../database/conection.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$nome = trim($data['nome'] ?? '');
$palavra_chave = trim($data['palavra_chave'] ?? '');

if (empty($nome) || empty($palavra_chave)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nome e palavra-chave são obrigatórios.']);
    exit;
}


$conexao->begin_transaction();

try {

    $palavra_chave_hash = password_hash($palavra_chave, PASSWORD_DEFAULT);


    $stmt = $conexao->prepare("INSERT INTO ligas (nome, palavra_chave_hash, criador_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nome, $palavra_chave_hash, $userId);
    $stmt->execute();

    $ligaId = $conexao->insert_id;

    $stmt_membro = $conexao->prepare("INSERT INTO membros_liga (usuario_id, liga_id) VALUES (?, ?)");
    $stmt_membro->bind_param("ii", $userId, $ligaId);
    $stmt_membro->execute();

    $conexao->commit();

    echo json_encode(['success' => true, 'liga_id' => $ligaId]);

} catch (Exception $e) {
    $conexao->rollback();
    http_response_code(500);
    error_log("Erro ao criar liga: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao criar a liga.']);
}

$stmt->close();
$conexao->close();
?>