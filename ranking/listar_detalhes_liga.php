<?php
include "../includes/cors.php";
session_start();
include "../database/conection.php";

header('Content-Type: application/json');

function fetch_query($conexao, $sql, $params = [], $types = "") {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) { return []; }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID da liga inválido ou não fornecido."]);
    exit;
}

$liga_id = intval($_GET['id']);

$sql_nome_liga = "SELECT nome FROM ligas WHERE id = ?";
$nome_liga_result = fetch_query($conexao, $sql_nome_liga, [$liga_id], "i");
$nome_liga = $nome_liga_result[0]['nome'] ?? 'Liga Desconhecida';

$sql_membros = "
    SELECT
        u.id,
        u.nome,
        COALESCE(MAX(p.pontos), 0) AS pontuacao_geral,
        COALESCE(MAX(CASE WHEN p.data_partida >= CURDATE() - INTERVAL 7 DAY THEN p.pontos ELSE 0 END), 0) AS pontuacao_semanal
    FROM usuarios u
    JOIN membros_liga ml ON u.id = ml.usuario_id
    LEFT JOIN partidas p ON u.id = p.usuario_id AND ml.id = p.membro_liga_id
    WHERE ml.liga_id = ?
    GROUP BY u.id, u.nome
    ORDER BY pontuacao_geral DESC
";
$membros_da_liga = fetch_query($conexao, $sql_membros, [$liga_id], "i");

$ranking_liga_detalhado = [
    'nome_liga' => $nome_liga,
    'membros' => $membros_da_liga
];

echo json_encode(["success" => true, "data" => $ranking_liga_detalhado]);
$conexao->close();
?>
