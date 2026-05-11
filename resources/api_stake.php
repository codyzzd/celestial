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

  if (empty($user_id) || empty($name) || empty($cod)) {
    echo json_encode(['status' => 'error', 'msg' => 'Dados incompletos fornecidos.']);
    exit;
  }

  // Verificar se o código já existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM stakes WHERE cod = ?");
  $stmt->bind_param("s", $cod);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Esta estaca já existe!']);
    $stmt->close();
  } else {
    $stmt->close();

    try {
      $conn->begin_transaction();

      // Gerar o UUID antes do insert para vincular o usuário à nova estaca.
      $uuid_stmt = $conn->query("SELECT UUID() AS new_id");
      $uuid_row = $uuid_stmt->fetch_assoc();
      $new_stake_id = $uuid_row['new_id'];

      // Preparar a query de inserção
      $stmt = $conn->prepare("INSERT INTO stakes (id, name, cod) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $new_stake_id, $name, $cod);

      if (!$stmt->execute()) {
        throw new Exception('Erro ao adicionar a stake: ' . $stmt->error);
      }

      $stmt->close();

      // Buscar o ID da role "Líder da estaca"
      $role_stmt = $conn->prepare("SELECT id FROM roles WHERE slug = ?");
      $role_name = 'stake_lider';
      $role_stmt->bind_param("s", $role_name);
      $role_stmt->execute();
      $role_result = $role_stmt->get_result();
      $role_row = $role_result->fetch_assoc();
      $role_stmt->close();

      if (!$role_row) {
        throw new Exception('Role de líder da estaca não encontrada.');
      }

      $role_id = $role_row['id'];

      // Atualizar o campo id_stake e role do usuário
      $stmt = $conn->prepare("UPDATE users SET id_stake = ?, role = ? WHERE id = ?");
      $stmt->bind_param("sss", $new_stake_id, $role_id, $user_id);

      if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar o usuário: ' . $stmt->error);
      }

      $stmt->close();
      $conn->commit();

      echo json_encode([
        'status' => 'loading',
        'msg' => 'Estaca adicionada com sucesso! Seu usuário está sendo ativado como líder...'
      ]);
    } catch (Exception $e) {
      $conn->rollback();
      echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
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

if ($indicador == 'ward_add') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $name = $_POST['name'] ?? '';
  $cod = $_POST['cod'] ?? '';

  if (empty($user_id) || empty($name) || empty($cod)) {
    echo json_encode(['status' => 'error', 'msg' => 'Dados incompletos fornecidos.']);
    exit;
  }

  // Verificar se o código já existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM wards WHERE cod = ?");
  $stmt->bind_param("s", $cod);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Esta ala já existe!'
    ]);
    $stmt->close();
  } else {
    $stmt->close();

    // Buscar o id_stake do usuário
    $stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($id_stake);
    $stmt->fetch();
    $stmt->close();

    if ($id_stake) {
      // Preparar a query de inserção com UUID gerado diretamente no MySQL e id_stake
      $stmt = $conn->prepare("INSERT INTO wards (id, name, cod, id_stake) VALUES (UUID(), ?, ?, ?)");
      $stmt->bind_param("sss", $name, $cod, $id_stake);

      if ($stmt->execute()) {
        echo json_encode([
          'status' => 'success',
          'msg' => 'Ala adicionada com sucesso!'
        ]);
      } else {
        echo json_encode([
          'status' => 'error',
          'msg' => 'Erro ao adicionar ala: ' . $stmt->error
        ]);
      }

      $stmt->close();
    } else {
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro: id_stake não encontrado para o usuário.'
      ]);
    }
  }
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
