<?php
// listar_ligas.php

require '../includes/cors.php';
session_start();
require '../database/conection.php'; // Usa sua conexão MySQLi

header('Content-Type: application/json');

$usuario_id = $_SESSION['user_id'] ?? null;
if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Não autenticado"]);
    exit;
}

try {
    // Query para buscar todas as ligas e informações
    $stmt_ligas = $conexao->prepare("
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
       GROUP BY l.id, l.nome, l.criador_id, u.nome
       ORDER BY l.id DESC
    ");
    $stmt_ligas->execute();
    $result_ligas = $stmt_ligas->get_result();
    $ligas = $result_ligas->fetch_all(MYSQLI_ASSOC);
    $stmt_ligas->close();

    $stmt_minhas_ligas = $conexao->prepare("SELECT liga_id FROM membros_liga WHERE usuario_id = ?");
    $stmt_minhas_ligas->bind_param("i", $usuario_id);
    $stmt_minhas_ligas->execute();
    $result_minhas_ligas = $stmt_minhas_ligas->get_result();

    $minhas_ligas_ids = [];
    while ($row = $result_minhas_ligas->fetch_assoc()) {
        $minhas_ligas_ids[] = (int) $row['liga_id'];
    }
    $stmt_minhas_ligas->close();

    echo json_encode([
        'success' => true,
        'ligas' => $ligas,
        'minhas_ligas_ids' => $minhas_ligas_ids
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Erro ao buscar ligas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao buscar ligas.']);
}

$conexao->close();
?>