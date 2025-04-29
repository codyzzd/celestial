<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once 'functions.php';
$conn = getDatabaseConnection();

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("HTTP/1.1 200 OK");
  exit;
}

// Pegar o valor do indicador
$indicador = getIndicador();

if ($indicador == 'archive_something') {
  // Pegar dados do formulário
  $bd = $_POST['bd'] ?? '';
  $id = $_POST['id'] ?? '';
  // Definir as tabelas permitidas e as mensagens de sucesso
  $tables_config = [
    'vehicles' => 'Veículo arquivado com sucesso!',
    'passengers' => 'Pessoa arquivada com sucesso!',
    'wards' => 'Ala arquivada com sucesso!',
    'caravans' => 'Caravana arquivada com sucesso!',
    // Adicionar mais tabelas conforme necessário
  ];
  // Verificar se a tabela é permitida
  if (!array_key_exists($bd, $tables_config)) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Tabela inválida.'
    ]);
    exit;
  }
  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE $bd SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
  if (!$stmt) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao preparar a query: ' . $conn->error
    ]);
    exit;
  }
  $stmt->bind_param("s", $id);
  // Obter a mensagem de sucesso
  $success_msg = $tables_config[$bd];
  // Executar a query e retornar o resultado
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'success',
      'msg' => $success_msg
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao atualizar o banco de dados: ' . $stmt->error
    ]);
  }
  $stmt->close(); // Fechar a declaração
}

$conn->close();