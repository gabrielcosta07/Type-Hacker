<?php
include "../includes/cors.php";

session_start();

include "../database/conection.php";

header('Content-Type: application/json');

function fetch_query($conexao, $sql, $params = [], $types = "") {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

$sql_geral = "
    SELECT u.id, u.nome, MAX(p.pontos) AS pontuacao
    FROM partidas p
    JOIN usuarios u ON p.usuario_id = u.id
    GROUP BY u.id, u.nome
    ORDER BY pontuacao DESC
    LIMIT 10
";
$ranking_geral = fetch_query($conexao, $sql_geral);

$sql_semanal = "
    SELECT u.id, u.nome, MAX(p.pontos) AS pontuacao
    FROM partidas p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.data_partida >= CURDATE() - INTERVAL 7 DAY
    GROUP BY u.id, u.nome
    ORDER BY pontuacao DESC
    LIMIT 10
";
$ranking_semanal = fetch_query($conexao, $sql_semanal);

$sql_liga = "
    WITH UserMaxScores AS (
        SELECT
            ml.liga_id,
            p.usuario_id,
            MAX(p.pontos) AS max_pontos
        FROM partidas p
        JOIN membros_liga ml ON p.membro_liga_id = ml.id
        GROUP BY ml.liga_id, p.usuario_id
    ),
    RankedScores AS (
        SELECT
            liga_id,
            max_pontos,
            ROW_NUMBER() OVER(PARTITION BY liga_id ORDER BY max_pontos DESC) as rn
        FROM UserMaxScores
    )
    SELECT
        l.id,
        l.nome,
        SUM(rs.max_pontos) AS pontuacao
    FROM RankedScores rs
    JOIN ligas l ON rs.liga_id = l.id
    WHERE rs.rn <= 5
    GROUP BY l.id, l.nome
    ORDER BY pontuacao DESC
    LIMIT 10
";
$ranking_liga = fetch_query($conexao, $sql_liga);

$resposta = [
    'geral' => $ranking_geral,
    'semanal' => $ranking_semanal,
    'liga' => $ranking_liga
];

echo json_encode($resposta);

$conexao->close();
?>
