<?php

session_start();
ob_start();

include "../database/conection.php";
include "../includes/cors.php";

$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';

if (!$email || !$senha) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Preencha todos os campos."]);
    exit;
}

$email = mysqli_real_escape_string($conexao, $email);
$senha = mysqli_real_escape_string($conexao, $senha);
$sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = '$email'";
$result = $conexao->query($sql);

if (!$result || $result->num_rows === 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Usuário não encontrado!"]);
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($senha, $user['senha'])) {
    $_SESSION["user_id"] = $user['id'];
    $_SESSION["user_name"] = $user['nome'];
    $_SESSION["user_email"] = $user['email'];

    ob_end_clean();
    echo json_encode([
        "success" => true,
        "message" => "Login realizado com sucesso!",
        "user" => [
            "id" => $user['id'],
            "nome" => $user['nome'],
            "email" => $user['email']
        ]
    ]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Senha incorreta!"]);
}

$conexao->close();
?>