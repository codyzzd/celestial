<?php
session_start(); // Inicia a sessão
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);

// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Conectar ao banco de dados
$conn = getDatabaseConnection();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se você estiver usando cookies para gerenciar a sessão, exclua o cookie de sessão
if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), '', time() - 42000, '/');
}

// Verifica se a variável de sessão 'user_id' está definida
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  // Atualiza o token no banco de dados para NULL ou uma string vazia
  $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
}

// Destroi a sessão
session_destroy();

// Fecha a conexão com o banco de dados
$conn->close();

// Se houver um cookie de token, removê-lo
if (isset($_COOKIE['caravana_remember_token'])) {
  setcookie('caravana_remember_token', '', time() - 3600, '/'); // Define a data de expiração no passado para deletar o cookie
}

// Redireciona para a página de login
header("Location: login.php");
exit();
