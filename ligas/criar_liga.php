<?php
require '../includes/cors.php';
session_start();
require '../database/conection.php';
header('Content-Type: application/json');


$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);
$nome = trim($data['nome'] ?? '');
$palavra = trim($data['palavra_chave'] ?? '');


if (!$nome || !$palavra) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios']);
    exit;
}

try {

    $stmt = $conexao->prepare("
        SELECT ligas.id 
        FROM ligas 
        INNER JOIN membros_liga ON membros_liga.liga_id = ligas.id 
        WHERE membros_liga.usuario_id = ? AND ligas.nome = ?
    ");
    $stmt->bind_param("is", $userId, $nome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->fetch_assoc()) {
        http_response_code(409);
        echo json_encode(['error' => 'Você já participa de uma liga com esse nome.']);
        exit;
    }

    $stmt = $conexao->prepare("INSERT INTO ligas (nome, palavra_chave, criador_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nome, $palavra, $userId);
    $stmt->execute();

    $ligaId = $conexao->insert_id;

    $stmt = $conexao->prepare("INSERT INTO membros_liga (usuario_id, liga_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $ligaId);
    $stmt->execute();

    echo json_encode(['success' => true, 'liga_id' => $ligaId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao criar liga: ' . $e->getMessage()]);
}
?>