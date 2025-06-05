<?php 
include 'conection.php';

$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
)";

if($conexao->query($sql)){
    echo "Tabela criada com sucesso!";
} else {
    echo "Erro ao criar tabela!". $conexao->error;
}

$conexao->close();
?>