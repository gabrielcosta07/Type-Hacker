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

$sql = "
    SELECT 
        id, 
        pontos, 
        erros, 
        tempo_jogado, 
        DATE_FORMAT(data_partida, '%d/%m/%Y às %H:%i') AS data_formatada 
    FROM partidas 
    WHERE usuario_id = ? 
    ORDER BY data_partida DESC
";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao preparar a busca de histórico."]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$historico = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "historico" => $historico]);

$conexao->close();
?>
