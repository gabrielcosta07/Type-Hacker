<?php

//funções pra ler e salvar nos arquivos json, só pra não precisar ficar criando a mesma função em todo código
function lerJson($arquivo)
{
    if (!file_exists($arquivo))
        file_put_contents($arquivo, json_encode([]));
    return json_decode(file_get_contents($arquivo), true);
}

function salvarJson($arquivo, $dados)
{
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
}
