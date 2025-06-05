<?php 

$servidor = "localhost";
$usuario = "Jogo_Web";
$senha = "admin123#";
$banco = "jogo_web";

$conexao = new mysqli($servidor, $usuario, $senha);

if($conexao->connect_error){
    die("Falha na conexão" .$conexao->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS jogo_web";
$conexao->query($sql);
$conexao->close();

$conexao = new mysqli($servidor,$usuario,$senha,$banco);
if($conexao->connect_error){
    die("Falha na conexão com o banco: " . $conexao->connect_error);
} 

//echo "conectado e pronto para usar o banco!";
?>