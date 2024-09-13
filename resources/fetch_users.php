<?php
// Configurações e inicialização
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
require_once ROOT_PATH . '/resources/functions.php'; // Funções auxiliares

// Verificar se o usuário está logado
// $user_id = checkUserLogin();

// Conexão com o banco de dados
$conn = getDatabaseConnection();

// $ward_id = $_GET['ward_id'];
// $stake_id = $_GET['stake_id'];
$role_slug = $_GET['role_slug'];

// Responder às solicitações AJAX
if (isset($_GET['term'])) {
  $term = $conn->real_escape_string($_GET['term']);

  // Iniciar a query base
  $sql = "SELECT id, name, email FROM users WHERE email LIKE '%$term%'";

  // Ajustar a query com base no valor de $role_slug
  if ($role_slug === 'stake_lider') {
    // Para stake_lider, o role pode ser qualquer coisa menos o número 3
    $sql .= " AND role != 3";
  } elseif ($role_slug === 'ward_lider') {
    // Para ward_lider, o role deve ser 2 ou 5
    $sql .= " AND role IN (2, 5)";
  }

  // Executar a query
  $result = $conn->query($sql);

  $users = [];
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }

  // Retornar o resultado como JSON
  echo json_encode($users);
  $conn->close();
  exit;
}
