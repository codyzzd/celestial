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

if ($indicador == 'role_alt') {
  // Obter variáveis de POST
  $role = $_POST['permission-radio'];
  $ward_id = $_POST['id_ward'];
  $user_id = $_POST['user_id'];
  $stake_id = $_POST['stake_id'];

  // Definir o período de expiração (7 dias por padrão)
  $daysToExpire = 7; // você pode alterar esse valor conforme necessário
  $expireDate = (new DateTime())->modify("+$daysToExpire days")->format('Y-m-d H:i:s');

  try {
    // Buscar o role_id no banco usando o slug
    $stmt = $conn->prepare("SELECT id FROM roles WHERE slug = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();

    if ($role_id === null) {
      // Tratar caso em que o role_slug não é encontrado
      echo json_encode([
        'status' => 'error',
        'msg' => 'Role não encontrado.'
      ]);
      exit;
    }

    // Gerar um novo UUID no banco de dados
    $uuid_stmt = $conn->query("SELECT UUID() AS new_id");
    $uuid_row = $uuid_stmt->fetch_assoc();
    $new_uuid = $uuid_row['new_id'];

    // Inserir no banco de dados
    $sql = "INSERT INTO role_alt (id, role, stake_id, ward_id,expire_at)
            VALUES (?, ?, ?, ?,?)";

    // Preparar e executar a consulta
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      throw new Exception("Erro ao preparar a consulta: " . $conn->error);
    }

    // Associar os parâmetros
    $stmt->bind_param('sssss', $new_uuid, $role_id, $stake_id, $ward_id, $expireDate);

    // Executar a consulta
    if ($stmt->execute()) {
      echo json_encode([
        'status' => 'success',
        'msg' => 'Token gerado e inserido com sucesso.',
        'uuid' => $new_uuid
      ]);
    } else {
      throw new Exception("Erro ao inserir no banco de dados: " . $stmt->error);
    }

    // Fechar a declaração
    $stmt->close();
  } catch (Exception $e) {
    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }
}

if ($indicador === 'role_alt_edit') {
  $permissionRadio = $_POST['permission-radio'] ?? '';
  $userId = $_POST['id'] ?? '';

  // Resposta padrão
  $response = ['status' => 'error', 'msg' => 'Dados insuficientes fornecidos.'];

  // Verifica se os dados necessários estão presentes
  if ($permissionRadio && $userId) {
    // Se o permissionRadio for "member", atualiza diretamente
    if ($permissionRadio === 'member') {
      $sql = "UPDATE users SET role = NULL WHERE id = ?";
      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $response = [
          'status' => $stmt->affected_rows > 0 ? 'success' : 'error',
          'msg' => $stmt->affected_rows > 0 ? 'Role atualizada com sucesso.' : 'Nenhuma alteração foi feita ou usuário não encontrado.'
        ];
        $stmt->close();
      } else {
        $response['msg'] = 'Erro na preparação da consulta de atualização.';
      }
    } else {
      // Consulta para obter o ID da role
      $sql = "SELECT id FROM roles WHERE slug = ?";
      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $permissionRadio);
        $stmt->execute();
        $stmt->bind_result($roleId);
        $stmt->fetch();
        $stmt->close();

        // Atualiza o usuário se o roleId foi encontrado
        if ($roleId) {
          $sql = "UPDATE users SET role = ? WHERE id = ?";
          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('is', $roleId, $userId);
            $stmt->execute();
            $response = [
              'status' => $stmt->affected_rows > 0 ? 'success' : 'error',
              'msg' => $stmt->affected_rows > 0 ? 'Role atualizada com sucesso.' : 'Nenhuma alteração foi feita ou usuário não encontrado.'
            ];
            $stmt->close();
          } else {
            $response['msg'] = 'Erro na preparação da consulta de atualização.';
          }
        } else {
          $response['msg'] = 'Nenhuma role encontrada para o slug fornecido.';
        }
      } else {
        $response['msg'] = 'Erro na preparação da consulta.';
      }
    }
  }

  // Retorna a resposta em formato JSON
  echo json_encode($response);
}

if ($indicador === 'role_alt_edit_exist') {
  // Variável de controle de transação
  $transactionStarted = false;

  try {
    // Pega e sanitiza valores do POST
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $perm = $_POST['perm'] ?? '';

    // Verifica se os dados são válidos
    if (!$email || !$password || !$perm) {
      throw new Exception('Dados inválidos fornecidos.');
    }

    // Verifica login
    $sql = "SELECT id, password, salt, id_stake, id_ward FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
      throw new Exception('Erro ao preparar a declaração SQL.');
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0)
      throw new Exception('Email ou senha incorretos!');
    $stmt->bind_result($user_id, $stored_password, $stored_salt, $user_stake_id, $user_ward_id);
    $stmt->fetch();
    $stmt->close();

    // Verifica a senha
    if (!verifyPassword($password, $stored_password, $stored_salt))
      throw new Exception('Email ou senha incorretos!');

    // Inicia a transação
    $conn->begin_transaction();
    $transactionStarted = true;

    // Verifica permissão
    $sql_role = "SELECT role, stake_id, ward_id, expire_at FROM role_alt WHERE id = ?";
    $stmt_role = $conn->prepare($sql_role);
    if (!$stmt_role)
      throw new Exception('Erro ao preparar a declaração SQL para permissões.');
    $stmt_role->bind_param("s", $perm);
    $stmt_role->execute();
    $stmt_role->store_result();
    if ($stmt_role->num_rows === 0)
      throw new Exception('Permissão não encontrada.');
    $stmt_role->bind_result($role, $role_stake_id, $role_ward_id, $expire_at);
    $stmt_role->fetch();
    $stmt_role->close();

    // Verifica se a permissão expirou e se a estaca corresponde
    if (strtotime($expire_at) < time())
      throw new Exception('Permissão expirou.');
    if ($role_stake_id !== $user_stake_id)
      throw new Exception('Permissão não é válida para este usuário.');

    // Atualiza usuário com a nova permissão e ward_id
    $sql_update = "UPDATE users SET role = ?, id_ward = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update)
      throw new Exception('Erro ao preparar a declaração de atualização.');
    $stmt_update->bind_param("sss", $role, $role_ward_id, $user_id);
    if (!$stmt_update->execute())
      throw new Exception('Erro ao atualizar a permissão.');
    $stmt_update->close();

    // Remove a permissão da tabela role_alt
    $sql_delete = "DELETE FROM role_alt WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if (!$stmt_delete)
      throw new Exception('Erro ao preparar a declaração de exclusão.');
    $stmt_delete->bind_param("s", $perm);
    if (!$stmt_delete->execute())
      throw new Exception('Erro ao excluir a permissão da tabela role_alt.');
    $stmt_delete->close();

    // Confirma a transação
    $conn->commit();
    echo json_encode(['status' => 'loading', 'msg' => 'Permissão atualizada! Enviando para login...']);

  } catch (Exception $e) {
    // Reverte a transação se ela foi iniciada
    if ($transactionStarted) {
      $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
  }
}

if ($indicador === 'role_alt_edit_new') {
  $name = $_POST['name'] ?? '';
  $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
  $password = $_POST['password'] ?? '';
  $perm = $_POST['perm'] ?? '';

  // Converter o e-mail para minúsculas
  $email = strtolower($email);

  // Variável de controle de transação
  $transactionStarted = false;

  try {
    // Verifica se todos os dados foram fornecidos
    if (!$name || !$email || !$password || !$perm) {
      throw new Exception('Dados inválidos fornecidos.');
    }

    // Inicia a transação
    $conn->begin_transaction();
    $transactionStarted = true;

    // Verifica se o email já existe
    $sql_check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    if (!$stmt_check_email)
      throw new Exception('Erro ao preparar a declaração SQL para verificar email.');
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    if ($stmt_check_email->num_rows > 0)
      throw new Exception('Este email já foi cadastrado!');
    $stmt_check_email->close();

    // Verifica permissão
    $sql_role = "SELECT role, stake_id, ward_id, expire_at FROM role_alt WHERE id = ?";
    $stmt_role = $conn->prepare($sql_role);
    if (!$stmt_role)
      throw new Exception('Erro ao preparar a declaração SQL para permissões.');
    $stmt_role->bind_param("s", $perm);
    $stmt_role->execute();
    $stmt_role->store_result();
    if ($stmt_role->num_rows === 0)
      throw new Exception('Permissão não encontrada.');
    $stmt_role->bind_result($role, $role_stake_id, $role_ward_id, $expire_at);
    $stmt_role->fetch();
    $stmt_role->close();

    // Verifica se a permissão expirou
    if (strtotime($expire_at) < time())
      throw new Exception('Permissão expirou.');

    // Gerar um SALT aleatório
    $salt = generateSalt();

    // Hash a senha com o SALT
    $hashed_password = hashPassword($password, $salt);

    // Inserir o novo usuário
    $sql_insert = "INSERT INTO users (id, email, password, salt, name, role, id_stake, id_ward) VALUES (uuid(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert)
      throw new Exception('Erro ao preparar a declaração de inserção.');
    $stmt_insert->bind_param("sssssss", $email, $hashed_password, $salt, $name, $role, $role_stake_id, $role_ward_id);
    if (!$stmt_insert->execute())
      throw new Exception('Erro ao criar a conta: ' . $stmt_insert->error);
    $stmt_insert->close();

    // Remove a permissão da tabela role_alt
    $sql_delete = "DELETE FROM role_alt WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if (!$stmt_delete)
      throw new Exception('Erro ao preparar a declaração de exclusão.');
    $stmt_delete->bind_param("s", $perm);
    if (!$stmt_delete->execute())
      throw new Exception('Erro ao excluir a permissão da tabela role_alt: ' . $stmt_delete->error);
    $stmt_delete->close();

    // Confirma a transação
    $conn->commit();
    echo json_encode(['status' => 'loading', 'msg' => 'Conta criada com Permissão atualizada! Enviando para login...']);

  } catch (Exception $e) {
    // Reverte a transação se ela foi iniciada
    if ($transactionStarted) {
      $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
  }
}

if ($indicador == 'user_add') {
  // Pegar dados do form
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $name = $_POST['name'] ?? '';

  // Converter o e-mail para minúsculas antes de qualquer operação
  $email = toLowerCase($email);

  // Verificar se o email já existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(
      [
        'status' => 'error',
        'msg' => 'Este email já foi cadastrado!'
      ]
    );
  } else {
    // Email não existe, prosseguir com o cadastro
    $stmt->close();

    // Gerar um SALT aleatório
    $salt = generateSalt();

    // Hash a senha com o SALT
    $hashed_password = hashPassword($password, $salt);

    // Preparar a query de inserção
    $stmt = $conn->prepare("INSERT INTO users (id, email, password, salt,name) VALUES (uuid(), ?, ?, ?,?)");
    $stmt->bind_param("ssss", $email, $hashed_password, $salt, $name);

    // Executar a query
    if ($stmt->execute()) {
      echo json_encode([
        'status' => 'success',
        'msg' => 'Conta criada com sucesso!'
      ]);
    } else {
      echo json_encode([
        'status' => 'error',
        'msg' => $stmt->error
      ]);
    }

    $stmt->close(); // Fechar a declaração
  }
}

if ($indicador == 'user_login') {
  // Pegar dados do form
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Converter o e-mail para minúsculas antes de qualquer operação
  $email = strtolower($email);

  // Verificar se o email é válido e sanitizá-lo
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);

  // Verificar se o email existe no banco de dados
  if ($stmt = $conn->prepare("SELECT id, password, salt FROM users WHERE email = ?")) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      // Email existe, verificar a senha
      $stmt->bind_result($id, $stored_password, $stored_salt);
      $stmt->fetch();

      if (verifyPassword($password, $stored_password, $stored_salt)) {
        session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;

        // Verificar se o usuário quer permanecer logado
        if (!empty($_POST['remember_token']) && $_POST['remember_token'] === 'remember_token') {
          // Criar e salvar um novo token seguro
          $token = hash('sha256', uniqid(bin2hex(random_bytes(16)), true) . time());

          $updateStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
          $updateStmt->bind_param("ss", $token, $id);
          $updateStmt->execute();
          $updateStmt->close();

          // Detectar se é ambiente local ou produção
          $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
          $domain = $_SERVER['HTTP_HOST'];

          // Para ambiente local, não definir domínio específico
          if (strpos($domain, 'localhost') !== false || strpos($domain, '127.0.0.1') !== false) {
            $domain = '';
          }

          // Criar um cookie seguro que expira em 30 dias (mais realista que 50 anos)
          $cookieOptions = [
            'expires' => time() + (86400 * 365 * 50), // 50 anos
            'path' => '/',
            'httponly' => true, // Impede acesso via JavaScript
            'samesite' => 'Lax' // Protege contra CSRF
          ];

          // Só adicionar 'secure' e 'domain' se não for ambiente local
          if ($isSecure) {
            $cookieOptions['secure'] = true;
          }
          if (!empty($domain)) {
            $cookieOptions['domain'] = $domain;
          }

          setcookie('caravana_remember_token', $token, $cookieOptions);
        }

        echo json_encode([
          'status' => 'loading',
          'msg' => 'Login realizado com sucesso! Entrando...',
          'user_id' => $id
        ]);
      } else {
        echo json_encode(['status' => 'error', 'msg' => 'Email ou senha incorretos!']);
      }
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Email ou senha incorretos!']);
    }

    $stmt->close();
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro no servidor. Por favor, tente novamente mais tarde.']);
  }
}

if ($indicador == 'user_newpw') { // para ele mesmo resetar a senha
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $password = $_POST['password'] ?? '';

  // Gerar um SALT aleatório
  $salt = generateSalt();

  // Hash a senha com o SALT
  $hashed_password = hashPassword($password, $salt);

  // Preparar a consulta SQL
  $sql = "UPDATE users SET password = ?, salt = ? WHERE id = ?";

  // Preparar a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Vincular os parâmetros
    $stmt->bind_param('sss', $hashed_password, $salt, $user_id);

    // Executar a consulta
    if ($stmt->execute()) {
      // Retornar sucesso em JSON
      echo json_encode(
        [
          'status' => 'success',
          'msg' => 'Senha atualizada com sucesso!'
        ]
      );
    } else {
      // Retornar erro em JSON
      echo json_encode(
        [
          'status' => 'error',
          'msg' => 'Erro ao atualizar a senha: ' . $stmt->error
        ]
      );
    }

    // Fechar a declaração
    $stmt->close();
  } else {
    // Retornar erro em JSON
    echo json_encode(
      [
        'status' => 'error',
        'msg' => 'Erro ao preparar a consulta: ' . $conn->error
      ]
    );
  }
}

if ($indicador == 'user_get') {
  // Armazenar o valor de $_POST['user_id'] em uma variável
  $user_id = $_POST['user_id'];
  // Preparar a consulta SQL com JOIN
  $sql = "SELECT users.id, users.role, users.name, roles.slug
FROM users
JOIN roles ON users.role = roles.id
WHERE users.id = ?";
  // Preparar a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Associar a variável $user_id ao placeholder (?) na consulta
    $stmt->bind_param("s", $user_id); // "s" indica que o parâmetro é uma string
// Executar a declaração preparada
    $stmt->execute();
    // Obter o resultado
    $result = $stmt->get_result();
    // Verificar se houve resultados
    if ($result->num_rows > 0) {
      // Retornar os dados como um array associativo
      $user_data = $result->fetch_assoc();
      // Exibir ou manipular os dados retornados
      echo json_encode($user_data);
    } else {
      // Caso não haja resultados, pode retornar uma mensagem de erro
      echo json_encode(['status' => 'error', 'msg' => 'Usuário não encontrado.']);
    }
    // Fechar a declaração
    $stmt->close();
  } else {
    // Caso a preparação da declaração falhe
    echo json_encode(['status' => 'error', 'msg' => 'Erro na preparação da consulta.']);
  }
}

if ($indicador == 'user_list_stake') {
  // Obter variáveis de POST
  $stake_id = $_POST['stake_id'] ?? '';
  $ward_id = $_POST['ward_id'] ?? '';
  $role = $_POST['role'] ?? '';

  try {
    // Inicializa as variáveis para a consulta
    $sql = "";
    $params = [];
    $types = ""; // Para os tipos dos parâmetros no bind_param

    // Verifica o valor de role para alterar a SQL conforme necessário
    if ($role === 'stake_lider') {
      // Consulta SQL para stake_lider
      $sql = "
          SELECT u.id,
                 u.name AS user_name,u.email AS user_email,
                 r.name AS role_name,
                 w.name AS ward_name
          FROM users u
          LEFT JOIN roles r ON u.role = r.id
          LEFT JOIN wards w ON u.id_ward = w.id
          WHERE u.id_stake = ?
            AND u.role IS NOT NULL
            AND u.role != 3
            ORDER BY w.name
          ";
      $params = [$stake_id];
      $types = "s"; // Um parâmetro do tipo string
    } elseif ($role === 'ward_lider') {
      // Consulta SQL para ward_lider
      $sql = "
          SELECT u.id,
                 u.name AS user_name,u.email AS user_email,
                 r.name AS role_name,
                 w.name AS ward_name
          FROM users u
          LEFT JOIN roles r ON u.role = r.id
          LEFT JOIN wards w ON u.id_ward = w.id
          WHERE u.id_stake = ?
            AND u.id_ward = ?
            AND u.role IS NOT NULL
            AND u.role != 3
            AND u.role != 1
            AND u.role != 4
            ORDER BY u.name
          ";
      $params = [$stake_id, $ward_id];
      $types = "ss"; // Dois parâmetros do tipo string
    } else {
      throw new Exception('Tipo de role inválido.');
    }

    // Preparar a consulta SQL
    $stmt = $conn->prepare($sql);

    // Verificar se existem parâmetros a serem vinculados
    if (!empty($params)) {
      // Usa a função spread para passar os parâmetros ao bind_param
      $stmt->bind_param($types, ...$params);
    }

    // Executar a consulta
    $stmt->execute();
    // Obter os resultados
    $result = $stmt->get_result();
    $users = [];
    // Loop pelos resultados e adicionar ao array
    while ($row = $result->fetch_assoc()) {
      $users[] = [
        'id' => $row['id'],
        'user_name' => $row['user_name'],
        'role_name' => $row['role_name'],
        'ward_name' => $row['ward_name'],
        'user_email' => $row['user_email']
      ];
    }
    // Fechar a declaração e a conexão
    $stmt->close();
    // Retornar o array de usuários como JSON
    echo json_encode([
      'status' => 'success',
      'data' => $users
    ]);
  } catch (Exception $e) {
    // Em caso de erro, lançar uma exceção ou tratar o erro conforme necessário
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao buscar os usuários: ' . $e->getMessage()
    ]);
  }
}


if ($indicador == 'recomendation_get') {
  $id_member = $_POST['memberId'];

  // Prepara a consulta SQL
  $sql = "SELECT
  passengers.name,
  passengers.id_church,
  passengers.id_ward,
  passengers.sex,
  sexs.name AS sex_name,
  passengers.barcode,
  passengers.expiration_date,
  wards.name AS ward_name,
  stakes.name AS stake_name
FROM passengers
JOIN wards ON passengers.id_ward = wards.id
JOIN sexs ON passengers.sex = sexs.id
JOIN stakes ON wards.id_stake = stakes.id
WHERE passengers.id = ?";

  // Prepara a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Liga os parâmetros
    $stmt->bind_param("s", $id_member);

    // Executa a declaração
    $stmt->execute();

    // Obtém o resultado
    $result = $stmt->get_result();

    // Verifica se há resultados
    if ($result->num_rows > 0) {
      // Obtém o único resultado
      $member = $result->fetch_assoc();

      // Codifica o resultado em JSON e envia como resposta
      echo json_encode($member);
    } else {
      // Se nenhum resultado, retorna uma mensagem de erro
      echo json_encode([
        'error' => 'Nenhum membro encontrado para o ID fornecido.'
      ]);
    }

    // Fecha a declaração
    $stmt->close();
  } else {
    // Erro ao preparar a declaração
    echo json_encode(['error' => 'Erro ao preparar a declaração SQL.']);
  }
}


if ($indicador == 'ward_edit_user') {

  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $id_ward = $_POST['id_ward'] ?? '';

  // Validar os dados
  if (empty($user_id) || empty($id_ward)) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Dados inválidos.'
    ]);
    exit();
  }

  // Preparar a consulta
  $stmt = $conn->prepare("UPDATE users SET id_ward = ? WHERE id = ?");
  if (!$stmt) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao preparar a consulta: ' . $conn->error
    ]);
    exit();
  }

  // Bind dos parâmetros
  $stmt->bind_param("ss", $id_ward, $user_id);

  // Executar a consulta
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'loading',
      'msg' => 'Ativando Ala...'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao atualizar o banco de dados: ' . $stmt->error
    ]);
  }

  // Fechar a declaração e a conexão
  $stmt->close();

}

$conn->close();