<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// header('Content-Type: application/json');

//session_start(); // iniciar sessao

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("HTTP/1.1 200 OK");
  exit;
}

// Configurações de exibição e log de erros
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// ini_set('log_errors', 1);
// //ini_set('error_log', '/caminho/para/seu/arquivo_de_log.log');
// error_reporting(E_ALL);

// Conexão com o banco de dados
// require_once 'dbcon.php';

// Chamando as funções
require_once 'functions.php';
$conn = getDatabaseConnection();

// Pegar o valor do indicador
$indicador = getIndicador();

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
      // Email existe, vamos verificar a senha
      $stmt->bind_result($id, $stored_password, $stored_salt);
      $stmt->fetch();

      // Verificar a senha fornecida usando a função verifyPassword
      if (verifyPassword($password, $stored_password, $stored_salt)) {
        session_start(); // Iniciar a sessão
        session_regenerate_id(true); // Regenerar o ID da sessão por segurança
        $_SESSION['user_id'] = $id; // Definir a variável de sessão

        // Verifique se o usuário quer permanecer logado
        if (isset($_POST['remember_token']) && $_POST['remember_token'] === 'remember_token') {
          // Crie um token seguro
          $token = bin2hex(random_bytes(16));

          // Salve o token no banco de dados associado ao usuário
          $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
          $stmt->bind_param("si", $token, $id);
          $stmt->execute();

          // Crie um cookie que expira em, por exemplo, 30 dias
          // setcookie('caravana_remember_token', $token, time() + (86400 * 30), "/");
          setcookie('caravana_remember_token', $token, time() + (86400 * 365 * 50), "/");
        }

        echo json_encode([
          'status' => 'loading',
          'msg' => 'Login realizado com sucesso! Entrando...',
          'user_id' => $id
        ]);
      } else {
        // Senha incorreta
        echo json_encode([
          'status' => 'error',
          'msg' => 'Email ou senha incorretos!'
        ]);
      }
    } else {
      // Email não encontrado ou senha incorreta
      echo json_encode([
        'status' => 'error',
        'msg' => 'Email ou senha incorretos!'
      ]);
    }

    $stmt->close(); // Fechar a declaração
  } else {
    // Falha ao preparar a declaração
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro no servidor. Por favor, tente novamente mais tarde.'
    ]);
  }
}

if ($indicador == 'user_resetpw') {
  // Pegar o e-mail do formulário
  $email = $_POST['email'] ?? '';

  // Sanitizar e validar o e-mail
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'E-mail inválido!'
    ]);
    exit();
  }

  // Verificar se o e-mail existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // E-mail encontrado, gerar um token de redefinição
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expira em 1 hora

    // Atualizar o token e a expiração no banco de dados
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();

    // Enviar e-mail com link de redefinição
    $resetLink = "https://caravanacelestial.com.br/app/reset_password.php?token=$token";
    $subject = "Redefinição de Senha";
    $message = "Clique no link abaixo para redefinir sua senha:\n\n$resetLink";
    $headers = "From: noreply@caravanacelestial.com.br";

    mail($email, $subject, $message, $headers);

    echo json_encode([
      'status' => 'success',
      'msg' => 'Um link para redefinir sua senha foi enviado para o seu e-mail.'
    ]);
  } else {
    // E-mail não encontrado
    echo json_encode([
      'status' => 'error',
      'msg' => 'E-mail não encontrado!'
    ]);
  }

  $stmt->close();
  $conn->close();
}

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
    echo json_encode(
      [
        'status' => 'error',
        'msg' => 'Esta estaca já existe!'
      ]
    );
  } else {
    // Código não existe, prosseguir com o cadastro
    $stmt->close();

    // Gerar um novo UUID no banco de dados
    $uuid_stmt = $conn->query("SELECT UUID() AS new_id");
    $uuid_row = $uuid_stmt->fetch_assoc();
    $new_stake_id = $uuid_row['new_id'];

    // Preparar a query de inserção
    $stmt = $conn->prepare("INSERT INTO stakes (id, name, cod) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $new_stake_id, $name, $cod);

    // Executar a query
    if ($stmt->execute()) {
      // Fechar a declaração de inserção
      $stmt->close();

      // Buscar o ID da role "Líder da estaca"
      $role_stmt = $conn->prepare("SELECT id FROM roles WHERE slug = ?");
      $role_name = 'stake_lider'; // Nome da role
      $role_stmt->bind_param("s", $role_name);
      $role_stmt->execute();
      $role_result = $role_stmt->get_result();
      $role_row = $role_result->fetch_assoc();
      $role_id = $role_row['id'];
      $role_stmt->close();

      // Atualizar o campo id_stake e role do usuário
      $stmt = $conn->prepare("UPDATE users SET id_stake = ?, role = ? WHERE id = ?");
      $stmt->bind_param("sss", $new_stake_id, $role_id, $user_id);

      if ($stmt->execute()) {
        echo json_encode([
          'status' => 'loading',
          'msg' => 'Estaca adicionada com sucesso! Seu usuário está sendo ativado como líder...'
        ]);
      } else {
        echo json_encode([
          'status' => 'error',
          'msg' => 'Erro ao atualizar o usuário: ' . $stmt->error
        ]);
      }

      $stmt->close(); // Fechar a declaração
    } else {
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro ao adicionar a stake: ' . $stmt->error
      ]);
    }
  }
}

if ($indicador == 'stake_edit') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $stake_id = $_POST['stake_id'] ?? '';

  // Verificar se o user_id e stake_id foram fornecidos
  if (empty($user_id) || empty($stake_id)) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'user_id e stake_id são necessários!'
    ]);
    exit;
  }

  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE users SET id_stake = ? WHERE id = ?");
  if (!$stmt) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao preparar a query: ' . $conn->error
    ]);
    exit;
  }

  $stmt->bind_param("ss", $stake_id, $user_id);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'loading',
      'msg' => 'Sua nova estaca esta sendo ativada...'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao atualizar o banco de dados: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'ward_add') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $name = $_POST['name'] ?? '';
  $cod = $_POST['cod'] ?? '';

  // Verificar se o código já existe no banco de dados
  $stmt = $conn->prepare("SELECT id FROM wards WHERE cod = ?");
  $stmt->bind_param("s", $cod);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(
      [
        'status' => 'error',
        'msg' => 'Esta ala já existe!'
      ]
    );
  } else {
    // Código não existe, prosseguir com o cadastro
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

      // Executar a query
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

      $stmt->close(); // Fechar a declaração
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

if ($indicador == 'passenger_add') {
  // Pegar dados do form
  $name = $_POST['name'] ?? '';
  $nasc_date = $_POST['nasc_date'] ?? '';
  $sex = $_POST['sex'] ?? '';
  $id_ward = $_POST['id_ward'] ?? '';
  // $fever_date = $_POST['fever_date'] ?? null; // Campo opcional
  $id_document = $_POST['id_document'] ?? '';
  $document = $_POST['document'] ?? '';
  $obs = $_POST['obs'] ?? null; // Campo opcional
  $id_user = $_POST['user_id'] ?? ''; // Coleta o ID do usuário
  $id_relationship = $_POST['id_relationship'] ?? ''; // Coleta o ID do usuário
  $id_church = $_POST['id_church'] ?? '';

  // Converter as datas
  // $nasc_date = convertDateFormat($nasc_date);
  $nasc_date = formatDateOrTime($nasc_date, 'date_BR_EN');
  // $fever_date = $fever_date ? convertDateFormat($fever_date) : null;

  // Preparar a query de inserção com UUID() diretamente
  $stmt = $conn->prepare("INSERT INTO passengers (id, name, nasc_date, sex, id_ward, id_document, document, obs, created_by, id_relationship, id_church) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssisssisss", $name, $nasc_date, $sex, $id_ward, $id_document, $document, $obs, $id_user, $id_relationship, $id_church);


  // Executar a query
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'success',
      'msg' => 'Pessoa adicionado com sucesso!'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao adicionar o pessoa: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'passenger_edit') {
  // Pegar dados do form
  $id = $_POST['id'] ?? ''; // ID do passageiro a ser atualizado
  $name = $_POST['name'] ?? '';
  $nasc_date = $_POST['nasc_date'] ?? '';
  $sex = $_POST['sex'] ?? '';
  $id_ward = $_POST['id_ward'] ?? '';
  // $fever_date = $_POST['fever_date'] ?? null; // Campo opcional
  $id_document = $_POST['id_document'] ?? '';
  $document = $_POST['document'] ?? '';
  $obs = $_POST['obs'] ?? null; // Campo opcional
  $id_user = $_POST['user_id'] ?? ''; // Coleta o ID do usuário
  $id_relationship = $_POST['id_relationship'] ?? ''; // Coleta o ID do relacionamento
  $id_church = $_POST['id_church'] ?? ''; // Coleta o ID do usuário

  // Converter as datas
  // $nasc_date = convertDateFormat($nasc_date);
  $nasc_date = formatDateOrTime($nasc_date, 'date_BR_EN');
  // $fever_date = $fever_date ? convertDateFormat($fever_date) : null;

  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE passengers SET name = ?, nasc_date = ?, sex = ?, id_ward = ?, id_document = ?, document = ?, obs = ?, id_relationship = ?, id_church = ? WHERE id = ?");
  $stmt->bind_param("ssississss", $name, $nasc_date, $sex, $id_ward, $id_document, $document, $obs, $id_relationship, $id_church, $id);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'success',
      'msg' => 'Pessoa atualizada com sucesso!'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao atualizar a pessoa: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'passenger_list') {
  // Pegar dados do form
  $user_id = $_POST['user_id'] ?? '';
  $relation_slug = $_POST['relation'] ?? '';
  $status = $_POST['status'] ?? '';

  // Verificar se o ID do usuário foi fornecido
  if (empty($user_id)) {
    echo json_encode([
      'status' => 'error',
      'msg' => 'ID do usuário é obrigatório.'
    ]);
    exit;
  }

  // Inicializar a variável $relation_id como nula
  $relation_id = null;

  // Se o slug de relação for fornecido, buscar o ID correspondente
  if (!empty($relation_slug)) {
    // Buscar o ID da relação a partir do slug
    $stmt = $conn->prepare("SELECT id FROM relationship WHERE slug = ?");
    $stmt->bind_param("s", $relation_slug);

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $relation_id = $row['id']; // Atribuir o ID correspondente ao slug
      } else {
        echo json_encode([
          'status' => 'error',
          'msg' => 'Relação não encontrada.'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro ao buscar o ID da relação: ' . $stmt->error
      ]);
      exit;
    }

    $stmt->close(); // Fechar a declaração
  } else {
    $relation_id = null; // Nenhuma relação específica
  }

  // Construir a query para buscar passageiros com junção na tabela relationship
  $query = "SELECT p.*,
                 r.slug AS relation_slug,
                 s.slug AS sex_slug
          FROM passengers p
          LEFT JOIN relationship r ON p.id_relationship = r.id
          LEFT JOIN sexs s ON p.sex = s.id
          WHERE p.created_by = ?";

  // Adicionar a condição de relação, se $relation_id não for nulo
  if ($relation_id !== null) {
    $query .= " AND p.id_relationship = ?";
  }

  // Adicionar a condição baseada no status
  if ($status === 'deleted') {
    $query .= " AND p.deleted_at IS NOT NULL";
  } elseif ($status === 'not_deleted') {
    $query .= " AND p.deleted_at IS NULL";
  }

  // Preparar a query para buscar passageiros
  $stmt = $conn->prepare($query);

  // Bind dos parâmetros, incluindo $relation_id se não for nulo
  if ($relation_id !== null) {
    $stmt->bind_param("ss", $user_id, $relation_id);
  } else {
    $stmt->bind_param("s", $user_id);
  }

  // Executar a query para buscar os passageiros
  if ($stmt->execute()) {
    $result = $stmt->get_result();
    $passengers = [];

    // Buscar os resultados e armazená-los no array $passengers
    while ($row = $result->fetch_assoc()) {
      $passengers[] = $row;
    }

    // Retornar os passageiros como JSON
    echo json_encode($passengers);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao buscar os passageiros: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'passenger_get') {
  // Pegar dados do form
  $passenger_id = $_POST['passenger_id'] ?? '';

  // Verifica se o user_id é válido
  if ($passenger_id <= 0) {
    echo json_encode([
      'error' => 'ID de passageiro inválido.'
    ]);
    exit;
  }

  // Prepara a consulta SQL
  $sql = "SELECT * FROM passengers WHERE id = ?";

  // Prepara a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Liga os parâmetros
    $stmt->bind_param("i", $passenger_id);

    // Executa a declaração
    $stmt->execute();

    // Obtém o resultado
    $result = $stmt->get_result();

    // Verifica se há resultados
    if ($result->num_rows > 0) {
      // Cria um array para armazenar os dados
      $passengers = [];

      // Itera sobre os resultados e armazena no array
      while ($row = $result->fetch_assoc()) {
        $passengers[] = $row;
      }

      // Codifica o array em JSON e envia como resposta
      echo json_encode($passengers);
    } else {
      // Se nenhum resultado, retorna uma mensagem de erro
      echo json_encode([
        'error' => 'Nenhum passageiro encontrado para o ID fornecido.'
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

if ($indicador == 'vehicle_add') {
  // user_id
  // name
  // obs
  // capacity
  // stake_id
  // seat_map

  // Verifica se o array $_POST não está vazio
  if (!empty($_POST)) {
    // Itera sobre cada item no array $_POST
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = $value;
    }
  }

  // Preparar a query de inserção com UUID() diretamente
  $stmt = $conn->prepare("INSERT INTO vehicles (id, name, capacity,obs,id_stake,seat_map) VALUES (UUID(), ?, ?, ?,?,?)");
  $stmt->bind_param("sisss", $name, $capacity, $obs, $stake_id, $seat_map);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'success',
      'msg' => 'Veículo adicionado com sucesso!'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao adicionar o veículo: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'vehicle_list') {
  // Inicializar variáveis
  $id_stake = null;
  $status = 'all'; // Valor padrão

  // Verifica se o array $_POST não está vazio
  if (!empty($_POST)) {
    // Itera sobre cada item no array $_POST
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = $value;
    }
  }


  try {
    // Verificar se id_stake foi recebido
    if (isset($id_stake)) {
      // Construir a consulta SQL base
      $sql = "SELECT * FROM vehicles WHERE id_stake = ?";

      // Adicionar condição para registros não excluídos se necessário
      if ($status === 'not_deleted') {
        $sql .= " AND deleted_at IS NULL";
      }

      // Preparar a consulta SQL
      $stmt = $conn->prepare($sql);

      // Verificar se a preparação foi bem-sucedida
      if ($stmt === false) {
        throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
      }

      // Vincular o parâmetro e executar a consulta
      $stmt->bind_param("s", $id_stake);
      $stmt->execute();

      // Obter os resultados
      $result = $stmt->get_result();
      $vehicles = [];

      // Iterar sobre os resultados e armazenar em um array
      while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
      }

      // Fechar a declaração
      $stmt->close();

      // Retornar os resultados como JSON
      echo json_encode($vehicles);

    } else {
      // Retornar uma mensagem de erro se id_stake não estiver definido
      echo json_encode(['status' => 'error', 'msg' => 'ID Stake não fornecido.']);
    }

  } catch (Exception $e) {
    // Tratar erros e exibir uma mensagem adequada
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao executar a consulta: ' . $e->getMessage()]);

  }
  // O fechamento da conexão com o banco de dados deve ser feito no final do arquivo.
}

if ($indicador == 'vehicle_get') {
  // Pegar dados do form
  $vehicle_id = $_POST['vehicle_id'] ?? '';

  // Verifica se o vehicle_id é válido
  if (empty($vehicle_id)) {
    echo json_encode([
      'error' => 'ID de veículo inválido.'
    ]);
    exit;
  }

  // Prepara a consulta SQL
  $sql = "SELECT * FROM vehicles WHERE id = ?";

  // Prepara a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Liga os parâmetros (assumindo que vehicle_id é um UUID, que é uma string)
    $stmt->bind_param("s", $vehicle_id);

    // Executa a declaração
    $stmt->execute();

    // Obtém o resultado
    $result = $stmt->get_result();

    // Verifica se há resultados
    if ($result->num_rows > 0) {
      // Pega os dados do veículo
      $vehicle = $result->fetch_assoc();

      // Codifica o array em JSON e envia como resposta
      echo json_encode($vehicle);
    } else {
      // Se nenhum resultado, retorna uma mensagem de erro
      echo json_encode([
        'error' => 'Nenhum veículo encontrado para o ID fornecido.'
      ]);
    }

    // Fecha a declaração
    $stmt->close();
  } else {
    // Erro ao preparar a declaração
    echo json_encode(['error' => 'Erro ao preparar a declaração SQL.']);
  }
}

if ($indicador == 'vehicle_edit') {
  // user_id
  // name
  // obs
  // capacity
  // stake_id
  // id do veiculo

  // Verifica se o array $_POST não está vazio
  if (!empty($_POST)) {
    // Itera sobre cada item no array $_POST
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = $value;
    }
  }

  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE vehicles SET name = ?, obs = ?, capacity = ? WHERE id = ?");
  $stmt->bind_param("ssis", $name, $obs, $capacity, $id);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode([
      'status' => 'success',
      'msg' => 'Veículo atualizado com sucesso!'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao atualizar a pessoa: ' . $stmt->error
    ]);
  }

  $stmt->close(); // Fechar a declaração
}

if ($indicador == 'caravan_add') {
  // Pega valores do POST
  if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = $value;
    }
  }

  // Gerar um novo UUID no banco de dados
  $uuid_stmt = $conn->query("SELECT UUID() AS new_id");
  $uuid_row = $uuid_stmt->fetch_assoc();
  $uuid = $uuid_row['new_id'];

  // Inicia a transação
  $conn->begin_transaction();

  try {
    // Converte as datas (caso seja necessário)
    $start_date = $start_date ? formatDateOrTime($start_date, 'date_BR_EN') : null;
    $return_date = $return_date ? formatDateOrTime($return_date, 'date_BR_EN') : null;

    // Prepara a consulta para inserção na tabela caravans com UUID()
    $stmt = $conn->prepare("
      INSERT INTO caravans (id, id_stake, name, start_date, start_time, return_date, return_time, obs)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Associa os parâmetros
    $stmt->bind_param("ssssssss", $uuid, $stake_id, $name, $start_date, $start_time, $return_date, $return_time, $obs);

    // Executa a consulta para inserir a caravana
    if ($stmt->execute()) {
      // Verifica se há IDs de veículos e se não está vazio
      if (!empty($_POST['vehicle_ids'])) {
        $vehicleIds = json_decode($_POST['vehicle_ids'], true);

        if (!empty($vehicleIds)) {
          // Prepara a inserção na tabela caravan_vehicle
          $stmt = $conn->prepare("INSERT INTO caravan_vehicles (id, id_caravan, id_vehicle) VALUES (UUID(), ?, ?)");

          foreach ($vehicleIds as $vehicleId) {
            $stmt->bind_param("ss", $uuid, $vehicleId);
            $stmt->execute();
          }

          // Confirma a transação se tudo deu certo
          $conn->commit();

          echo json_encode([
            'status' => 'success',
            'msg' => 'Caravana e veículos adicionados com sucesso!'
          ]);
        } else {
          throw new Exception('Nenhum veículo selecionado.');
        }
      } else {
        throw new Exception('Nenhum veículo selecionado.');
      }
    } else {
      throw new Exception('Erro ao adicionar a caravana: ' . $stmt->error);
    }
  } catch (Exception $e) {
    // Algo deu errado, desfaz a transação
    $conn->rollback();

    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }

  // Fecha a declaração e a conexão com o banco de dados
  $stmt->close();
}

if ($indicador == 'caravan_list') {
  // Inicializar variável
  $stake_id = null;

  // Verifica se o array $_POST não está vazio
  if (!empty($_POST)) {
    // Itera sobre cada item no array $_POST
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = trim($value);
    }
  }

  try {
    // Verificar se stake_id foi recebido
    if (isset($stake_id) && !empty($stake_id)) {
      // Construir a consulta SQL base para a tabela caravans
      $sql = "SELECT * FROM caravans WHERE id_stake = ? order by start_date desc";

      // Adicionar condição para registros não excluídos (soft delete)
      // if ($status === 'not_deleted') {
      //   $sql .= " AND deleted_at IS NULL";
      // }

      // Preparar a consulta SQL
      $stmt = $conn->prepare($sql);

      // Verificar se a preparação foi bem-sucedida
      if ($stmt === false) {
        throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
      }

      // Vincular o parâmetro e executar a consulta
      $stmt->bind_param("s", $stake_id);
      $stmt->execute();

      // Obter os resultados
      $result = $stmt->get_result();
      $caravans = [];

      // Iterar sobre os resultados e armazenar em um array
      while ($row = $result->fetch_assoc()) {
        $caravans[] = $row;
      }

      // Fechar a declaração
      $stmt->close();

      // Retornar os resultados como JSON
      echo json_encode($caravans);

    } else {
      // Retornar uma mensagem de erro se stake_id não estiver definido
      echo json_encode(['status' => 'error', 'msg' => 'ID Stake não fornecido.']);
    }

  } catch (Exception $e) {
    // Tratar erros e exibir uma mensagem adequada
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao executar a consulta: ' . $e->getMessage()]);

  }
}




if ($indicador == 'archive_something') {
  // Pegar dados do formulário
  $bd = $_POST['bd'] ?? '';
  $id = $_POST['id'] ?? '';

  // Definir as tabelas permitidas e as mensagens de sucesso
  $tables_config = [
    'vehicles' => 'Veículo arquivado com sucesso!',
    'passengers' => 'Pessoa arquivada com sucesso!',
    'wards' => 'Ala arquivada com sucesso!',
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

// Fechar a conexão
$conn->close();