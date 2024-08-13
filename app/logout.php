<?php
session_start(); // Inicia a sessão

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se você estiver usando cookies para gerenciar a sessão, exclua o cookie de sessão
if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), '', time() - 42000, '/');
}

// Destroi a sessão
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit();