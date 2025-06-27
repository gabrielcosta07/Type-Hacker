<?php
include 'conection.php';

$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS ligas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    palavra_chave VARCHAR(50) NOT NULL,
    criador_id INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criador_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS membros_liga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    liga_id INT NOT NULL,
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (liga_id) REFERENCES ligas(id),
    UNIQUE(usuario_id, liga_id)
);

CREATE TABLE partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    membro_liga_id INT NULL,
    pontos INT NOT NULL,
    erros INT NOT NULL,
    tempo_jogado INT NOT NULL,
    data_partida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (membro_liga_id) REFERENCES membros_liga(id)
);
";

if ($conexao->multi_query($sql)) {
    echo "Tabela criada com sucesso!";
} else {
    echo "Erro ao criar tabela!" . $conexao->error;
}

$conexao->close();
?>