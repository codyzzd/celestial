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

// if ($indicador == 'user_newpw_reset') { // para lider trocar senha de usuário
//   //pegar variaveis
//   $user_id = $_POST['user_id'];
//   $pw = $_POST['password'];
// }


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

if ($indicador == 'user_resetpw') { //NOT IN USE
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

  $barcode = $_POST['barcode'] ?? null;
  $mes = $_POST['mes'] ?? null;
  $ano = $_POST['ano'] ?? null;

  // Verifica se o mês e o ano estão preenchidos
  if (!empty($mes) && !empty($ano)) {
    // Criar uma data com o primeiro dia do mês
    $expiration_date = DateTime::createFromFormat('Y-m-d', "$ano-$mes-01");

    // Ajustar para o último dia do mês
    $expiration_date->modify('last day of this month');

    // Formatar a data para YYYY-MM-DD
    $expiration_date = $expiration_date->format('Y-m-d');
  } else {
    // Se mês ou ano estiver vazio, define expiration_date como NULL ou uma data padrão
    $expiration_date = null; // ou qualquer outra lógica que você queira implementar
  }

  // Converter as datas
  // $nasc_date = convertDateFormat($nasc_date);
  $nasc_date = formatDateOrTime($nasc_date, 'date_BR_EN');
  // $fever_date = $fever_date ? convertDateFormat($fever_date) : null;

  // Preparar a query de inserção com UUID() diretamente
  $stmt = $conn->prepare("INSERT INTO passengers (id, name, nasc_date, sex, id_ward, id_document, document, obs, created_by, id_relationship, id_church,barcode,expiration_date) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)");
  $stmt->bind_param("ssisssisssss", $name, $nasc_date, $sex, $id_ward, $id_document, $document, $obs, $id_user, $id_relationship, $id_church, $barcode, $expiration_date);


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
  $id_church = $_POST['id_church'] ?? ''; // Coleta o ID da igreja

  $barcode = $_POST['barcode'] ?? '';
  $mes = $_POST['mes'] ?? '';
  $ano = $_POST['ano'] ?? '';

  // Verifica se o mês e o ano estão preenchidos
  if (!empty($mes) && !empty($ano)) {
    // Criar uma data com o primeiro dia do mês
    $expiration_date = DateTime::createFromFormat('Y-m-d', "$ano-$mes-01");

    // Ajustar para o último dia do mês
    $expiration_date->modify('last day of this month');

    // Formatar a data para YYYY-MM-DD
    $expiration_date = $expiration_date->format('Y-m-d');
  } else {
    // Se mês ou ano estiver vazio, define expiration_date como NULL ou uma data padrão
    $expiration_date = null; // ou qualquer outra lógica que você queira implementar
  }

  // Converter as datas
  // $nasc_date = convertDateFormat($nasc_date);
  $nasc_date = formatDateOrTime($nasc_date, 'date_BR_EN');
  // $fever_date = $fever_date ? convertDateFormat($fever_date) : null;

  // Preparar a query de atualização
  $stmt = $conn->prepare("UPDATE passengers SET name = ?, nasc_date = ?, sex = ?, id_ward = ?, id_document = ?, document = ?, obs = ?, id_relationship = ?, id_church = ?, barcode = ?, expiration_date = ? WHERE id = ?");

  // A ordem dos parâmetros e tipos deve ser corrigida
  $stmt->bind_param("ssississssss", $name, $nasc_date, $sex, $id_ward, $id_document, $document, $obs, $id_relationship, $id_church, $barcode, $expiration_date, $id);

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
  // Pega dados do formulário
  $passenger_id = $_POST['passenger_id'] ?? '';

  // Verifica se o passenger_id é válido
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
    $stmt->bind_param("s", $passenger_id);

    // Executa a declaração
    $stmt->execute();

    // Obtém o resultado
    $result = $stmt->get_result();

    // Verifica se há resultados
    if ($result->num_rows > 0) {
      // Obtém o único resultado
      $passenger = $result->fetch_assoc();

      // Codifica o resultado em JSON e envia como resposta
      echo json_encode($passenger);
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
      // Construir a consulta SQL base com JOIN
      $sql = "
        select * from vehicles
        WHERE id_stake = ?
      ";

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
  $stmt = $conn->prepare("UPDATE vehicles SET name = ?, obs = ?, capacity = ? ,seat_map = ? WHERE id = ?");
  $stmt->bind_param("ssiss", $name, $obs, $capacity, $seat_map, $id);

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
      INSERT INTO caravans (id, id_stake, name, start_date, start_time, return_date, return_time, obs,destination)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)
    ");

    // Associa os parâmetros
    $stmt->bind_param("sssssssss", $uuid, $stake_id, $name, $start_date, $start_time, $return_date, $return_time, $obs, $destination);

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

if ($indicador == 'caravan_edit') {
  // Pega valores do POST
  if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
      // Remove possíveis tags HTML e espaços em branco
      ${$key} = $value;
    }
  }

  // Inicia a transação
  $conn->begin_transaction();

  try {
    // Converte as datas (caso seja necessário)
    $start_date = $start_date ? formatDateOrTime($start_date, 'date_BR_EN') : null;
    $return_date = $return_date ? formatDateOrTime($return_date, 'date_BR_EN') : null;

    // Prepara a consulta para atualizar a tabela caravans
    $stmt = $conn->prepare("
      UPDATE caravans
      SET  name = ?, start_date = ?, start_time = ?, return_date = ?, return_time = ?, obs = ?
      WHERE id = ?
    ");

    // Associa os parâmetros
    $stmt->bind_param("sssssss", $name, $start_date, $start_time, $return_date, $return_time, $obs, $id);

    // Executa a consulta para atualizar a caravana
    if ($stmt->execute()) {
      // Verifica se há IDs de veículos e se não está vazio
      if (!empty($_POST['vehicle_ids'])) {
        $vehicleIds = json_decode($_POST['vehicle_ids'], true);

        if (!empty($vehicleIds)) {
          // Prepara a inserção na tabela caravan_vehicles
          $stmt = $conn->prepare("INSERT INTO caravan_vehicles (id, id_caravan, id_vehicle) VALUES (UUID(), ?, ?)");

          foreach ($vehicleIds as $vehicle) {
            $stmt->bind_param("ss", $id, $vehicle['id']);
            $stmt->execute();
          }
        }
      }

      // Confirma a transação se tudo deu certo
      $conn->commit();

      echo json_encode([
        'status' => 'success',
        'msg' => 'Caravana atualizada com sucesso!'
      ]);
    } else {
      throw new Exception('Erro ao atualizar a caravana: ' . $stmt->error);
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
  $status = null;

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
      $sql = "SELECT * FROM caravans WHERE id_stake = ? order by start_date asc";

      // Adicionar condição para registros não excluídos (soft delete)
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

if ($indicador == 'seat_add') {
  // user_id, reserva, id_caravan
  $user_id = $_POST['user_id'] ?? '';
  $id_caravan = $_POST['id_caravan'] ?? '';
  $reservaData = $_POST['reserva'] ?? '';

  // Decodificar os dados JSON
  $decodedReservaData1 = urldecode($reservaData);
  $decodedReservaData2 = trim($decodedReservaData1, '"');
  $reserva = json_decode($decodedReservaData2, true);

  // Iniciar transação
  $conn->begin_transaction();

  try {
    // Preparar a consulta para verificação
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM seats WHERE id_caravan_vehicle = ? AND seat = ?");
    if (!$checkStmt) {
      throw new Exception("Erro ao preparar a consulta de verificação: " . $conn->error);
    }

    // Preparar a consulta para inserção
    $stmt = $conn->prepare("INSERT INTO seats (id, id_caravan_vehicle, id_caravan, id_passenger, seat, created_by) VALUES (UUID(), ?, ?, ?, ?, ?)");
    if (!$stmt) {
      throw new Exception("Erro ao preparar a consulta de inserção: " . $conn->error);
    }

    // Inserir cada reserva
    foreach ($reserva as $reservaItem) {
      $vehicleId = $reservaItem['vehicleId'];
      $seatNumber = $reservaItem['seatNumber'];
      $passengerId = $reservaItem['passengerId'];

      // Ajustar $seatNumber para NULL se for "no-seat"
      if ($seatNumber === "no-seat") {
        $seatNumber = null;  // Definir seat como NULL no banco de dados
      } else {
        // Verificar se o banco já está ocupado
        $checkStmt->bind_param("ss", $vehicleId, $seatNumber);
        $checkStmt->execute();
        $checkStmt->bind_result($seatCount);
        $checkStmt->fetch();

        if ($seatCount > 0) {
          // Retornar erro específico para banco já ocupado
          echo json_encode([
            'status' => 'error',
            'msg' => "Banco $seatNumber já está ocupado."
          ]);
          $conn->rollback();
          $checkStmt->close();
          $stmt->close();
          exit;
        }

        // Liberar o resultado antes de continuar
        $checkStmt->free_result();
      }

      // Bind parameters (id_caravan_vehicle, id_caravan, id_passenger, seat)
      $stmt->bind_param("sssss", $vehicleId, $id_caravan, $passengerId, $seatNumber, $user_id);

      // Executar a declaração
      if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir dados: " . $stmt->error);
      }
    }

    // Confirmar a transação se tudo deu certo
    $conn->commit();

    // Fechar as declarações e a conexão
    $checkStmt->close();
    $stmt->close();
    // $conn->close();

    // Retornar sucesso em formato JSON
    echo json_encode([
      'status' => 'loading',
      'msg' => 'Fazendo reservas...'
    ]);
  } catch (Exception $e) {
    // Desfazer a transação em caso de erro
    $conn->rollback();

    // Fechar as declarações e a conexão
    if (isset($checkStmt))
      $checkStmt->close();
    if (isset($stmt))
      $stmt->close();
    // $conn->close();

    // Retornar erro em formato JSON
    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }
}

if ($indicador == 'destination_add') {
  // Debug: Verifique se o arquivo está sendo enviado
  // error_log(print_r($_FILES['file_upload'], true));

  $name = $_POST['name'] ?? '';

  // Inicialize a variável para o caminho da foto
  $photoPath = null;

  // Verifique se o arquivo foi enviado
  if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file_upload']['tmp_name'];
    $fileName = $_FILES['file_upload']['name'];
    $fileSize = $_FILES['file_upload']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Verifique o tamanho do arquivo (máximo 6 MB)
    if ($fileSize > 6 * 1024 * 1024) {
      die(json_encode(['status' => 'error', 'msg' => 'O arquivo é maior que 6 MB.']));
    }

    // Defina o caminho do arquivo de upload
    $uploadFileDir = 'i/destinations/';

    // Gera um nome de arquivo único baseado no nome fornecido
    // Remove caracteres não alfanuméricos e substitui espaços por underscores
    $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    // Adiciona um identificador único para garantir a unicidade
    $uniqueFileName = strtolower($safeName) . '_' . uniqid() . '.' . $fileExtension;
    $destFilePath = $uploadFileDir . $uniqueFileName;

    // Verifique se o diretório de upload existe e, se não, crie-o
    if (!is_dir($uploadFileDir)) {
      mkdir($uploadFileDir, 0755, true);
    }

    // Redimensionar a imagem se necessário
    if (!resizeAndConvertToPng($fileTmpPath, $destFilePath)) {
      die(json_encode(['status' => 'error', 'msg' => 'Erro ao processar o arquivo de imagem.']));
    }

    // Defina o caminho final da foto
    // $photoPath = $destFilePath;
  }

  // Prepare e execute a consulta para inserir os dados na tabela
  $stmt = $conn->prepare("INSERT INTO destinations (id, name, photo) VALUES (uuid(), ?, ?)");
  $stmt->bind_param("ss", $name, $uniqueFileName);

  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'msg' => 'Destino adicionado com sucesso.']);
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao adicionar destino.']);
  }

  // Fechar a declaração
  $stmt->close();
}

if ($indicador == 'destination_list') {
  // Consulta para selecionar todos os destinos
  $sql = "SELECT id, name, photo FROM destinations";

  // Executar a consulta
  $result = $conn->query($sql);

  // Verificar se a consulta foi executada com sucesso
  if ($result) {
    // Verificar se houve resultados
    if ($result->num_rows > 0) {
      $destinations = [];

      // Iterar sobre os resultados e armazená-los em um array
      while ($row = $result->fetch_assoc()) {
        $destinations[] = [
          'id' => $row['id'],
          'name' => $row['name'],
          'photo' => $row['photo'],
        ];
      }

      // Retornar os destinos como JSON
      echo json_encode($destinations);
    } else {
      // Caso não haja resultados
      echo json_encode([
        'status' => 'error',
        'msg' => 'Nenhum destino encontrado.'
      ]);
    }
  } else {
    // Caso a consulta falhe
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao executar a consulta no banco de dados: ' . $conn->error
    ]);
  }
}

if ($indicador == 'destination_get') {
  // Recupera o ID do POST
  $id = $_POST['id'];

  // Consulta para selecionar o destino com o ID fornecido
  $sql = "SELECT * FROM destinations WHERE id = ?";

  // Preparar a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Fazer o bind do parâmetro
    $stmt->bind_param("i", $id); // "i" indica que o parâmetro é um inteiro

    // Executar a consulta
    if ($stmt->execute()) {
      // Obter o resultado
      $result = $stmt->get_result();

      // Verificar se há um resultado
      if ($result->num_rows > 0) {
        // Obter o registro como um array associativo
        $destination = $result->fetch_assoc();

        // Retornar o destino como JSON
        echo json_encode($destination);
      } else {
        // Caso não haja resultados
        echo json_encode([
          'status' => 'error',
          'msg' => 'Nenhum destino encontrado.'
        ]);
      }
    } else {
      // Caso a execução da consulta falhe
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro ao executar a consulta no banco de dados: ' . $stmt->error
      ]);
    }

    // Fechar a declaração
    $stmt->close();
  } else {
    // Caso a preparação da consulta falhe
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao preparar a consulta no banco de dados: ' . $conn->error
    ]);
  }
}

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

if ($indicador == 'user_list_stake') {
  // Obter variáveis de POST
  $stake_id = $_POST['stake_id'];
  $role = $_POST['role'];

  try {
    // Verifica o valor de role para alterar a SQL conforme necessário
    if ($role === 'stake_lider') {
      // Consulta SQL para stake_lider
      $sql = "
          SELECT u.id,
                 u.name AS user_name,
                 r.name AS role_name,
                 w.name AS ward_name
          FROM users u
          LEFT JOIN roles r ON u.role = r.id
          LEFT JOIN wards w ON u.id_ward = w.id
          WHERE u.id_stake = ?
            AND u.role IS NOT NULL
            AND u.role != 3
          ";
    } elseif ($role === 'ward_lider') {
      // Consulta SQL para ward_lider
      $sql = "
          SELECT u.id,
                 u.name AS user_name,
                 r.name AS role_name,
                 w.name AS ward_name
          FROM users u
          LEFT JOIN roles r ON u.role = r.id
          LEFT JOIN wards w ON u.id_ward = w.id
          WHERE u.id_stake = ?
            AND u.role IS NOT NULL
            AND u.role != 3
            AND u.role != 1
            AND u.role != 4
          ";
    } else {
      throw new Exception('Tipo de role inválido.');
    }

    // Preparar a consulta SQL
    $stmt = $conn->prepare($sql);
    // Associar os parâmetros
    $stmt->bind_param("s", $stake_id);
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
        'ward_name' => $row['ward_name']
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

if ($indicador == 'reserv_list') {

  // 1. Captura as variáveis vindas via POST
  $user_id = $_POST['user_id'] ?? null;
  $caravan_id = $_POST['caravan_id'] ?? null;

  // 2. Verifica se os dados foram passados corretamente
  if (!$user_id || !$caravan_id) {
    echo json_encode(['status' => 'error', 'msg' => 'Dados incompletos.']);
    exit;
  }

  // 3. Consulta para obter o ward_id do usuário
  $sql_ward = "
  SELECT
      users.id_ward,
      roles.slug
  FROM
      users
  INNER JOIN
      roles
  ON
      users.role_id = roles.id
  WHERE
      users.id = ?
  ";

  $stmt_ward = $conn->prepare($sql_ward);
  $stmt_ward->bind_param("s", $user_id);
  $stmt_ward->execute();
  $result_ward = $stmt_ward->get_result();
  $user_data = $result_ward->fetch_assoc();
  $stmt_ward->close();

  // Se não encontrar o usuário
  if (!$user_data) {
    echo json_encode(['status' => 'error', 'msg' => 'Usuário não encontrado.']);
    exit;
  }

  $ward_id = $user_data['id_ward'];
  $slug_role = $user_data['slug'];

  // 4. Consulta para buscar os passageiros da caravana
  $sql = "
  SELECT seats.id, passengers.name, seats.seat, seats.is_approved
  FROM seats
  JOIN passengers ON seats.id_passenger = passengers.id
  WHERE seats.id_caravan = ? AND passengers.id_ward = ?
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $caravan_id, $ward_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();

  // 5. Formata a resposta
  $passengers = $result->fetch_all(MYSQLI_ASSOC);

  if (!$passengers) {
    echo json_encode(['status' => 'error', 'msg' => 'Nenhum passageiro encontrado para essa caravana.']);
  } else {
    echo json_encode($passengers);
  }
}

if ($indicador == 'reserv_toggle') {
  $seat_id = $_POST['seat_id'];

  // Verifica se o seat_id foi passado
  if ($seat_id) {
    // Query para alternar o valor de is_approved diretamente
    $sql = "UPDATE seats SET is_approved = NOT is_approved WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $seat_id);

    if ($stmt->execute()) {
      // Sucesso ao atualizar
      echo json_encode(['status' => 'success', 'msg' => 'Status alterado com sucesso.']);
    } else {
      // Erro ao atualizar
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao atualizar o status.']);
    }

    // Fecha a declaração
    $stmt->close();
  } else {
    // Caso o seat_id não seja passado corretamente
    echo json_encode(['status' => 'error', 'msg' => 'ID do assento não fornecido.']);
  }
}

if ($indicador == 'reserv_delete') {
  $seat_id = $_POST['id'];

  // Prepara a query para deletar o assento com um parâmetro
  $sql = "DELETE FROM seats WHERE id = ?";

  // Prepara a statement
  if ($stmt = mysqli_prepare($conn, $sql)) {
    // Liga o parâmetro ao statement
    mysqli_stmt_bind_param($stmt, "s", $seat_id);

    // Executa o statement
    if (mysqli_stmt_execute($stmt)) {
      // Caso a deleção tenha sido bem-sucedida, retorna sucesso
      echo json_encode([
        'status' => 'success',
        'msg' => 'Reserva deletada com sucesso.'
      ]);
    } else {
      // Caso contrário, retorna um erro
      echo json_encode([
        'status' => 'error',
        'msg' => 'Erro ao deletar reserva.'
      ]);
    }

    // Fecha o statement
    mysqli_stmt_close($stmt);
  } else {
    // Caso a preparação do statement falhe, retorna um erro
    echo json_encode([
      'status' => 'error',
      'msg' => 'Erro ao preparar a consulta.'
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


if ($indicador == 'download_list') {
  $vehicle = $_POST['vehicleId']; //id cv do veiculo
  // $filetype = $_POST['fileType']; //xls ou pdf
  $reportType = $_POST['reportType']; //simples ou completo

  // Preparar a query SQL
  $sql_completo = "SELECT
  IFNULL(s.seat, '#') AS Banco, -- Substitui NULL por '#'
  p.name AS Nome,
  TIMESTAMPDIFF(YEAR, p.nasc_date, CURDATE()) AS Idade,
  d.name AS `Tipo Doc.`,
  p.document AS `Doc.`,
  p.obs AS Obs
FROM
  seats s
JOIN
  passengers p ON s.id_passenger = p.id
JOIN
  documents d ON d.id = p.id_document
JOIN
  caravan_vehicles v ON v.id = s.id_caravan_vehicle
JOIN
  vehicles ve ON ve.id = v.id_vehicle
JOIN
  wards w ON w.id = p.id_ward -- Adiciona o join com a tabela wards
WHERE
  s.id_caravan_vehicle = ? -- Alterado para usar s.id_caravan_vehicle
ORDER BY
  v.id, CAST(s.seat AS UNSIGNED) ASC;";

  $sql_simples = "SELECT

IFNULL(s.seat, '#') AS Banco, -- Substitui NULL por '#'
p.name AS Nome,
TIMESTAMPDIFF(YEAR, p.nasc_date, CURDATE()) AS Idade,

p.obs as Obs,

w.name AS Ala -- Adiciona o nome do ward
FROM
seats s
JOIN
passengers p ON s.id_passenger = p.id
JOIN
documents d ON d.id = p.id_document
JOIN
caravan_vehicles v ON v.id = s.id_caravan_vehicle
JOIN
vehicles ve ON ve.id = v.id_vehicle
JOIN
wards w ON w.id = p.id_ward -- Adiciona o join com a tabela wards
WHERE
s.id_caravan_vehicle = ? -- Alterado para usar s.id_caravan_vehicle
ORDER BY
v.id, CAST(s.seat AS UNSIGNED) ASC;";

  // Seleciona a query SQL com base no tipo de relatório
  if ($reportType == 'completo') {
    $sql = $sql_completo;
  } else if ($reportType == 'simples') {
    $sql = $sql_simples;
  } else {
    // Caso o valor de $reportType não seja reconhecido, retorna um erro
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Tipo de relatório não reconhecido']);
    exit;
  }

  // Preparar e executar a query SQL
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $vehicle);
  $stmt->execute();
  $result = $stmt->get_result();

  // Coletar os dados
  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  // Enviar dados como JSON
  header('Content-Type: application/json');
  echo json_encode($data);

  $stmt->close();
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
// Fechar a conexão
$conn->close();