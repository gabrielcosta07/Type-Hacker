<?php
include('../includes/cors.php');
include('../includes/funcoes.php');

$arquivoLigas = 'ligas.json';
$arquivoMembros = 'membros_liga.json';

$dados = json_decode(file_get_contents("php://input"), true);
$usuario_id = $dados['usuario_id'] ?? null;
$palavra_chave = $dados['palavra_chave'] ?? '';

if (!$usuario_id || !$palavra_chave) {
    echo json_encode(['erro' => 'Campos obrigatórios faltando']);
    exit;
}

$ligas = lerJson($arquivoLigas);
$membros = lerJson($arquivoMembros);

$liga = array_values(array_filter($ligas, fn($l) => $l['palavra_chave'] === $palavra_chave))[0] ?? null;

if (!$liga) {
    echo json_encode(['erro' => 'Liga não encontrada']);
    exit;
}

foreach ($membros as $m) {
    if ($m['usuario_id'] == $usuario_id && $m['liga_id'] == $liga['id']) {
        echo json_encode(['erro' => 'Usuário já está na liga']);
        exit;
    }
}

$novoMembro = [
    'id' => count($membros) + 1,
    'usuario_id' => $usuario_id,
    'liga_id' => $liga['id'],
    'data_entrada' => date('Y-m-d H:i:s')
];

$membros[] = $novoMembro;
salvarJson($arquivoMembros, $membros);

if ($novoMembro) {
    echo json_encode(['success' => true, 'mensagem' => 'Usuário adicionado à liga com sucesso']);
} else {
    echo json_encode(['erro' => 'Erro ao criar a liga']);
}
exit;

