<?php 

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "type_hacker";

$conexao = new mysqli($servidor, $usuario, $senha);

if($conexao->connect_error){
    die("Falha na conexão" .$conexao->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS type_hacker";
$conexao->query($sql);
$conexao->close();

$conexao = new mysqli($servidor,$usuario,$senha,$banco);
if($conexao->connect_error){
    die("Falha na conexão com o banco: " . $conexao->connect_error);
} 

//echo "conectado e pronto para usar o banco!";
?>