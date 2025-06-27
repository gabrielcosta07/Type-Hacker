<?php

include "../includes/cors.php";
session_start();
include "../database/conection.php";

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Utilizador não autenticado."]);
    exit;
}
$user_id = $_SESSION['user_id'];

try {

    $sql = "
        SELECT
            MAX(id) as id, 
            pontos,
            erros,
            tempo_jogado,
            DATE_FORMAT(data_partida, '%d/%m/%Y %H:%i') AS data_formatada
        FROM
            partidas
        WHERE
            usuario_id = ?
        GROUP BY
            data_partida, pontos, erros, tempo_jogado
        ORDER BY
            data_partida DESC -- Mostra as partidas mais recentes primeiro
    ";

    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $historico = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(["success" => true, "historico" => $historico]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro ao buscar histórico: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Erro no servidor ao buscar o histórico."]);
}

$conexao->close();
?>