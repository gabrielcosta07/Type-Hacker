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

$sql_geral = "SELECT u.id, u.nome, MAX(p.pontos) AS pontuacao FROM partidas p JOIN usuarios u ON p.usuario_id = u.id GROUP BY u.id, u.nome ORDER BY pontuacao DESC LIMIT 10";
$ranking_geral = fetch_query($conexao, $sql_geral);

$sql_semanal_global = "SELECT u.id, u.nome, MAX(p.pontos) AS pontuacao FROM partidas p JOIN usuarios u ON p.usuario_id = u.id WHERE p.data_partida >= CURDATE() - INTERVAL 7 DAY GROUP BY u.id, u.nome ORDER BY pontuacao DESC LIMIT 10";
$ranking_semanal = fetch_query($conexao, $sql_semanal_global);

$user_leagues = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_user_leagues = "
        SELECT l.id, l.nome
        FROM ligas l
        JOIN membros_liga ml ON l.id = ml.liga_id
        WHERE ml.usuario_id = ?
        ORDER BY l.nome
    ";
    $user_leagues = fetch_query($conexao, $sql_user_leagues, [$user_id], "i");
}

$resposta = [
    'geral' => $ranking_geral,
    'semanal' => $ranking_semanal,
    'liga' => $user_leagues 
];

echo json_encode($resposta);
$conexao->close();
?>
