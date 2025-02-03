<?php
session_start();
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);

// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Conectar ao banco de dados
$conn = getDatabaseConnection();

// Destrói todas as variáveis de sessão
$_SESSION = [];

// Remover cookie de sessão
if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), '', time() - 42000, '/');
}

// Verifica se há um usuário logado e reseta o token no banco
if (!empty($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
}

// Destroi a sessão completamente
session_destroy();
session_write_close();
session_regenerate_id(true);

// Remover o cookie de remember_token corretamente
if (isset($_COOKIE['caravana_remember_token'])) {
  setcookie('caravana_remember_token', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
}

// Fecha a conexão com o banco de dados
$conn->close();

// Forçar o navegador a não armazenar cache da sessão
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

// Redireciona para a página de login
header("Location: login.php");
exit();