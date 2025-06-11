<?php
session_start();
require '../includes/cors.php';
require '../database/conection.php';

header('Content-Type: application/json');


$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);
$ligaId = $data['id'] ?? null;
$palavra = trim($data['palavra_chave'] ?? '');

if (!$ligaId || !$palavra) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios']);
    exit;
}

try {

    $stmt = $conexao->prepare("SELECT palavra_chave FROM ligas WHERE id = ?");
    $stmt->bind_param("i", $ligaId);
    $stmt->execute();
    $result = $stmt->get_result();
    $liga = $result->fetch_assoc();

    if (!$liga || $liga['palavra_chave'] !== $palavra) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Palavra-chave incorreta']);
        exit;
    }


    $stmt = $conexao->prepare("SELECT id FROM membros_liga WHERE usuario_id = ? AND liga_id = ?");
    $stmt->bind_param("ii", $userId, $ligaId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        echo json_encode(['success' => true, 'message' => 'Você já está na liga']);
        exit;
    }

    $stmt = $conexao->prepare("INSERT INTO membros_liga (usuario_id, liga_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $ligaId);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao entrar na liga: ' . $e->getMessage()]);
}
?>