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

$data = json_decode(file_get_contents('php://input'), true);

$pontos = $data['pontos'] ?? 0;
$erros = $data['erros'] ?? 0;
$tempo_jogado = $data['tempo_jogado'] ?? 0;
$user_id = $_SESSION['user_id'];

$membro_liga_id = null;

$sql_membro = "SELECT id FROM membros_liga WHERE usuario_id = ? LIMIT 1";
$stmt_membro = $conexao->prepare($sql_membro);

if ($stmt_membro) {
    $stmt_membro->bind_param("i", $user_id);
    $stmt_membro->execute();
    $result_membro = $stmt_membro->get_result();

    if ($result_membro->num_rows > 0) {
        $membro = $result_membro->fetch_assoc();
        $membro_liga_id = $membro['id'];
    }
    $stmt_membro->close();
}

$sql_partida = "INSERT INTO partidas (usuario_id, membro_liga_id, pontos, erros, tempo_jogado) VALUES (?, ?, ?, ?, ?)";
$stmt_partida = $conexao->prepare($sql_partida);

if (!$stmt_partida) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao preparar a inserção da partida: " . $conexao->error]);
    exit;
}

$stmt_partida->bind_param("iiiii", $user_id, $membro_liga_id, $pontos, $erros, $tempo_jogado);

if ($stmt_partida->execute()) {
    echo json_encode(["success" => true, "message" => "Pontuação guardada com sucesso!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao guardar pontuação: " . $stmt_partida->error]);
}

$stmt_partida->close();
$conexao->close();
?>
