<?php 
//Faz conversar com o front
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, PTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

//gerência e responde requisições
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

//Defini o conteúdo da resposta como Json
header("Content-Type: application/json; charset=UTF-8");

//Guarda os usuários registrados
$arquivo = 'usuarios.json';

//Função pra ler os usuários
function lerUsuarios($arquivo){
    if(!file_exists($arquivo)){
        file_put_contents($arquivo, json_encode([]));
    } 
    return json_decode(file_get_contents($arquivo), true);
}

//Função para salvar usuários
function salvarUsuarios($arquivo, $usuarios){
    file_put_contents($arquivo, json_encode($usuarios, JSON_PRETTY_PRINT));
}

$metodo = $_SERVER['REQUEST_METHOD'];

//Recebe a ordem do React
$dados = json_decode(file_get_contents('php://input'), true);

if ($metodo == 'POST'){
    $acao = $dados['acao'];

    $usuarios = lerUsuarios($arquivo);

    //Se for registrar recebe os dados e faz a verificação deles
    if($acao == 'registrar'){

        $nome  = $dados['nome'];
        $email = $dados['email'];
        $senha = $dados['senha'];


        //Verifica se tem algum campo vazio
        if (empty($nome) || empty($email) || empty($senha)) {
            echo json_encode(['erro' => 'Nome, email e senha são obrigatórios.']);
            exit;
        }

        //Verifica o formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['erro' => 'Formato de email inválido.']);
            exit;
        }

        //Verifica se o usuario ja existe 
        foreach($usuarios as $usuario){
            if($usuario['email'] == $email){
                echo json_encode(['erro' => 'Usuário ja existe']);
                exit;
            }
        }

        //Cria um novo usário com ID
        $novoUsuario = [
            'id' => count($usuarios) +1,
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT)
        ];

        $usuarios[] = $novoUsuario;
        salvarUsuarios($arquivo, $usuarios);

        echo json_encode(['mensagem' => 'Usuário registrado com sucesso']);

    } else if ($acao == 'login'){

        $email = $dados['email'];
        $senha = $dados['senha'];

        $usuarioEncontrado = false;

        foreach($usuarios as $usuario){
            if($usuario['email'] == $email && password_verify($senha, $usuario['senha'])){
                $usuarioEncontrado = true; 
                break;
            }
        }

        if($usuarioEncontrado){
            echo json_encode(['success' => true, 'mensagem' => 'Login bem-sucedido']);
        } else {
            echo json_encode(['erro' => 'Email ou senha incorretos']);
        }
    } else {
        echo json_encode(['success' => false, 'erro' => 'Ação inválida']);
    }
} else {
    //Se o metodo for diferente de POST, não permite, da erro
    echo json_encode(['erro' => 'Método não permitido']);
}
?>
