<?php
// Configurações e inicialização
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
require_once ROOT_PATH . '/resources/functions.php'; // Funções auxiliares

// Conexão com o banco de dados
$conn = getDatabaseConnection();

$ward_id = $_GET['ward_id'];
$stake_id = $_GET['stake_id'];
$role_slug = $_GET['role_slug'];

// Responder às solicitações AJAX
if (isset($_GET['term'])) {
  $term = $conn->real_escape_string($_GET['term']);

  // Iniciar a query base
  $sql = "SELECT id, name, email FROM users WHERE email LIKE '%$term%'";

  // Ajustar a query com base no valor de $role_slug
  if ($role_slug === 'stake_lider') {
    // Para stake_lider, adicionar condição id_stake
    $sql .= " AND id_stake = '$stake_id'";
  } elseif ($role_slug === 'ward_lider') {
    // Para ward_lider, adicionar condição id_ward
    $sql .= " AND id_ward = '$ward_id'";
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