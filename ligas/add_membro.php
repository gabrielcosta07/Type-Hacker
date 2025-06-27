<?php


session_start();
require '../includes/cors.php';
require '../database/conection.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ligaId = $data['liga_id'] ?? null;
$palavra_chave_digitada = trim($data['palavra_chave'] ?? '');

if (empty($ligaId) || !isset($palavra_chave_digitada)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID da liga e palavra-chave são obrigatórios.']);
    exit;
}

try {

    $stmt = $conexao->prepare("SELECT palavra_chave_hash FROM ligas WHERE id = ?");
    $stmt->bind_param("i", $ligaId);
    $stmt->execute();
    $result = $stmt->get_result();
    $liga = $result->fetch_assoc();


    if (!$liga || !password_verify($palavra_chave_digitada, $liga['palavra_chave_hash'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Palavra-chave incorreta ou liga inválida']);
        exit;
    }


    $stmt_check = $conexao->prepare("SELECT id FROM membros_liga WHERE usuario_id = ? AND liga_id = ?");
    $stmt_check->bind_param("ii", $userId, $ligaId);
    $stmt_check->execute();
    if ($stmt_check->get_result()->fetch_assoc()) {
        echo json_encode(['success' => true, 'message' => 'Você já está na liga']);
        exit;
    }

    $stmt_insert = $conexao->prepare("INSERT INTO membros_liga (usuario_id, liga_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $userId, $ligaId);
    $stmt_insert->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro ao entrar na liga: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro ao entrar na liga.']);
}

$conexao->close();
?>