<?php
// Inicia o buffer para evitar qualquer output antes do JSON
ob_start();

include "../conexao_bd/conexao.php";
include "../includes/cors.php";


//Le o json
$data = json_decode(file_get_contents('php://input'), true);

$nome = $data['nome'] ?? '';
$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';

if (!$nome || !$email || !$senha) {
    //Limpa o buffer antes de responder 
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Preencha todos os campos."]);
    exit;
}

//Prepara consulta para verificar email 
$consulta = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
if (!$consulta) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Erro na preparação da consulta: " . $conexao->error]);
    exit;
}

$consulta->bind_param("s", $email);
$consulta->execute();
$consulta->store_result();

if ($consulta->num_rows > 0) {
    $consulta->close();
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Email já cadastrado."]);
    exit;
}

$consulta->close();


$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

//Prepata parar inserir usuários
$consulta = $conexao->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
if (!$consulta) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Erro na preparação da inserção: " . $conexao->error]);
    exit;
}

$consulta->bind_param("sss", $nome, $email, $senha_hash);

if ($consulta->execute()) {
    ob_end_clean();
    echo json_encode(["success" => true, "message" => "Usuário cadastrado com sucesso!", "id" => $consulta->insert_id]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Erro ao cadastrar usuário: " . $consulta->error]);
}

$consulta->close();
$conexao->close();
?>
