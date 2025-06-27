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

$sql_membros = "SELECT id FROM membros_liga WHERE usuario_id = ?";
$stmt_membros = $conexao->prepare($sql_membros);

if (!$stmt_membros) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao preparar busca de ligas."]);
    exit;
}

$stmt_membros->bind_param("i", $user_id);
$stmt_membros->execute();
$result_membros = $stmt_membros->get_result();
$ligas_do_usuario = $result_membros->fetch_all(MYSQLI_ASSOC);
$stmt_membros->close();

$partidas_inseridas = 0;
$erros_insercao = [];

if (count($ligas_do_usuario) > 0) {
    $sql_partida = "INSERT INTO partidas (usuario_id, membro_liga_id, pontos, erros, tempo_jogado) VALUES (?, ?, ?, ?, ?)";
    $stmt_partida = $conexao->prepare($sql_partida);

    if ($stmt_partida) {
        foreach ($ligas_do_usuario as $membro) {
            $membro_liga_id = $membro['id'];
            
            $stmt_partida->bind_param("iiiii", $user_id, $membro_liga_id, $pontos, $erros, $tempo_jogado);
            if ($stmt_partida->execute()) {
                $partidas_inseridas++;
            } else {
                $erros_insercao[] = $stmt_partida->error;
            }
        }
        $stmt_partida->close();
    } else {
        $erros_insercao[] = "Erro ao preparar a inserção da partida.";
    }
} else {
    $sql_partida_sem_liga = "INSERT INTO partidas (usuario_id, membro_liga_id, pontos, erros, tempo_jogado) VALUES (?, NULL, ?, ?, ?)";
    $stmt_partida_sem_liga = $conexao->prepare($sql_partida_sem_liga);

    if ($stmt_partida_sem_liga) {
        $stmt_partida_sem_liga->bind_param("iiii", $user_id, $pontos, $erros, $tempo_jogado);
        if ($stmt_partida_sem_liga->execute()) {
            $partidas_inseridas = 1;
        } else {
            $erros_insercao[] = $stmt_partida_sem_liga->error;
        }
        $stmt_partida_sem_liga->close();
    } else {
         $erros_insercao[] = "Erro ao preparar a inserção da partida (sem liga).";
    }
}

$conexao->close();

if ($partidas_inseridas > 0 && empty($erros_insercao)) {
    echo json_encode(["success" => true, "message" => "$partidas_inseridas registo(s) de partida guardado(s) com sucesso!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Ocorreram erros ao guardar a pontuação.", "details" => $erros_insercao]);
}
?>
