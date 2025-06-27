<?php
// O include do CORS DEVE ser a primeira coisa a ser executada.
include "../includes/cors.php";

session_start();

// Inclui a conexão com o banco
include "../database/conection.php";

// Garante que a resposta será sempre JSON a partir daqui
header('Content-Type: application/json');

// --- Verificação de Segurança: Utilizador Logado ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Código de "Não Autorizado"
    echo json_encode(["success" => false, "message" => "Utilizador não autenticado."]);
    exit;
}

// Obtém os dados enviados pelo front-end (React)
$data = json_decode(file_get_contents('php://input'), true);

// Extrai os dados da requisição
$pontos = $data['pontos'] ?? 0;
$erros = $data['erros'] ?? 0;
$tempo_jogado = $data['tempo_jogado'] ?? 0; // Em segundos
$user_id = $_SESSION['user_id']; // ID do utilizador da sessão

// --- Lógica para Encontrar o 'membro_liga_id' (AGORA É OPCIONAL) ---
$membro_liga_id = null; // Inicia a variável como nula por defeito.

$sql_membro = "SELECT id FROM membros_liga WHERE usuario_id = ? LIMIT 1";
$stmt_membro = $conexao->prepare($sql_membro);

// Verifica se a preparação da query foi bem-sucedida
if ($stmt_membro) {
    $stmt_membro->bind_param("i", $user_id);
    $stmt_membro->execute();
    $result_membro = $stmt_membro->get_result();

    // Se encontrar um membro de liga, guarda o ID
    if ($result_membro->num_rows > 0) {
        $membro = $result_membro->fetch_assoc();
        $membro_liga_id = $membro['id'];
    }
    $stmt_membro->close();
}
// O script continua mesmo que não encontre uma liga, com $membro_liga_id a nulo.


// --- Inserção da Partida no Banco de Dados ---
// A query foi atualizada para incluir o usuario_id e o membro_liga_id (que pode ser nulo).
$sql_partida = "INSERT INTO partidas (usuario_id, membro_liga_id, pontos, erros, tempo_jogado) VALUES (?, ?, ?, ?, ?)";
$stmt_partida = $conexao->prepare($sql_partida);

if (!$stmt_partida) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao preparar a inserção da partida: " . $conexao->error]);
    exit;
}

// Associa os parâmetros à query. 'i' para inteiro. O MySQLi trata o 'null' corretamente.
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
