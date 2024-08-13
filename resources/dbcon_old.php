<?php
// Configurações do banco de dados
// $host = 'sql201.infinityfree.com'; // Nome do servidor MySQL
// $database = 'if0_37046785_caravana'; // Nome do banco de dados
// $username = 'if0_37046785'; // Nome de usuário do MySQL
// $password = 'beGW2hENOAL'; // Senha do MySQL

$host = 'localhost'; // Nome do servidor MySQL
$database = 'caravana'; // Nome do banco de dados
$username = 'root'; // Nome de usuário do MySQL
$password = 'root'; // Senha do MySQL
$port = 8889;

// Conexão com o banco de dados
// $conn = new mysqli($host, $username, $password, $database, $port);

// //Verificar se a conexão teve sucesso
// if ($conn->connect_error) {
//   echo '<script>console.error("Erro na conexão: ' . $conn->connect_error . '");</script>';
// } else {
//   echo '<script>console.log("Conexão com o banco de dados estabelecida com sucesso.");</script>';
// }

// Definir o conjunto de caracteres para UTF-8
// $conn->set_charset('utf8');


// Cria a conexão
$conn = new mysqli($host, $username, $password, $database, $port);

// Verifica a conexão
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}