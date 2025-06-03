<?php
include('../includes/cors.php');
include('../includes/funcoes.php');

$arquivoLigas = 'ligas.json';

$dados = json_decode(file_get_contents("php://input"), true);
$nome = $dados['nome'] ?? ''; // nome dentro dos [] vai mudar dependendo dos nomes das variaveis no front
$palavra_chave = $dados['palavra_chave'] ?? '';
$criador_id = $dados['criador_id'] ?? null;

if (!$nome || !$palavra_chave || !$criador_id) {
    echo json_encode(['erro' => 'Campos obrigatórios faltando']);
    exit;
}

$ligas = lerJson($arquivoLigas);

$novaLiga = [
    'id' => count($ligas) + 1,
    'nome' => $nome,
    'palavra_chave' => $palavra_chave,
    'criador_id' => $criador_id,
    'data_criacao' => date('Y-m-d H:i:s')
];

$ligas[] = $novaLiga;
salvarJson($arquivoLigas, $ligas);

if ($novaLiga) {
    echo json_encode(['success' => true, 'mensagem' => 'Liga criada com sucesso']);
} else {
    echo json_encode(['erro' => 'Erro ao criar a liga']);
}
exit;
