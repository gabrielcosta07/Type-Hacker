<?php
require '../includes/cors.php';
session_start();
require '../database/conection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Não autenticado"]);
    exit;
}

$usuario_id = $_SESSION['user_id'];

error_log("Sessão atual: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não autenticado.");
    echo json_encode(["success" => false, "error" => "Não autenticado"]);
    exit;
}


try {
    $stmt = $conexao->prepare("
       SELECT 
  l.id, 
  l.nome, 
  l.criador_id,
  u.nome AS nome_criador,
  COUNT(m.usuario_id) AS qtd_jogadores,
  10 AS max_jogadores
FROM ligas l
LEFT JOIN membros_liga m ON l.id = m.liga_id
LEFT JOIN usuarios u ON l.criador_id = u.id
GROUP BY l.id
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $ligas = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($ligas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar ligas: ' . $e->getMessage()]);
}
?>