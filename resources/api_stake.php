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

if ($indicador == 'stake_add') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $name = $_POST['name'] ?? '';
  $cod = $_POST['cod'] ?? '';

  // Verificar se o código já existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM stakes WHERE cod = ?");
  $stmt->bind_param("s", $cod);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Código já existe.']);
  } else {
    $stmt->close();

    // Preparar a query de inserção
    $stmt = $conn->prepare("INSERT INTO stakes (id, name, cod, created_by) VALUES (UUID(), ?, ?, ?)");
    $stmt->bind_param("sss", $name, $cod, $user_id);

    if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'msg' => 'Estaca adicionada com sucesso.']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao adicionar estaca.']);
    }

    $stmt->close();
  }
}

if ($indicador == 'stake_edit') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $stake_id = $_POST['stake_id'] ?? '';

  // Verificar se o user_id e stake_id foram fornecidos
  if (empty($user_id) || empty($stake_id)) {
    echo json_encode(['status' => 'error', 'msg' => 'Dados incompletos fornecidos.']);
    exit;
  }

  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE users SET id_stake = ? WHERE id = ?");
  if (!$stmt) {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao preparar a consulta.']);
    exit;
  }

  $stmt->bind_param("ss", $stake_id, $user_id);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'msg' => 'Estaca atualizada com sucesso.']);
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao atualizar estaca.']);
  }

  $stmt->close();
}


if ($indicador == 'ward_edit') {
  // Pegar dados do form
  $id = $_POST['id'] ?? ''; // ID da ala que será atualizada
  $name = $_POST['name'] ?? '';
  $cod = $_POST['cod'] ?? '';

  // Verificar se o código já existe no banco de dados, mas não para o mesmo registro
  $stmt = $conn->prepare("SELECT id FROM wards WHERE cod = ? AND id != ?");
  $stmt->bind_param("ss", $cod, $id);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(
      [
        'status' => 'error',
        'msg' => 'Este código já está em uso por outra ala!'
      ]
    );
  } else {
    // Código não existe, prosseguir com a atualização
    $stmt->close();

    // Preparar a query de atualização
    $stmt = $conn->prepare("UPDATE wards SET name = ?, cod = ? WHERE id = ?");
    $stmt->bind_param("sss", $name, $cod, $id);

    // Executar a query
    if ($stmt->execute()) {
      echo json_encode([
        'status' => 'success',
        'msg' => 'Ala atualizada com sucesso!'
      ]);
    } else {
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro ao atualizar ala: ' . $stmt->error
      ]);
    }

    $stmt->close(); // Fechar a declaração
  }
}


if ($indicador == "ward_list") {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';

  // Verificar se o user_id foi fornecido
  if ($user_id) {
    // Obter as wards associadas ao user_id
    $wards = getWardsByUserId($user_id);

    // Retornar os dados em formato JSON
    echo json_encode($wards);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'User ID não fornecido.'
    ]);
  }
}

$conn->close();