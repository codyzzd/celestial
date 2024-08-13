<?php
// Configurações e inicialização
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
require_once ROOT_PATH . '/resources/functions.php'; // Funções auxiliares

// Verificar se o usuário está logado
// $user_id = checkUserLogin();

// Conexão com o banco de dados
$conn = getDatabaseConnection();

// Responder às solicitações AJAX
if (isset($_GET['term'])) {
  $term = $conn->real_escape_string($_GET['term']);

  // Buscar estacas que correspondem ao termo
  $sql = "SELECT id, name,cod FROM stakes WHERE name LIKE '%$term%'";
  $result = $conn->query($sql);

  $stakes = [];
  while ($row = $result->fetch_assoc()) {
    $stakes[] = $row;
  }

  echo json_encode($stakes);
  $conn->close();
  exit;
}
