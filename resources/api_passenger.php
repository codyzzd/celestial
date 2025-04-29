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

if ($indicador == 'passenger_add') {
  // Pegar dados do form
  $name = $_POST['name'] ?? '';
  $nasc_date = $_POST['nasc_date'] ?? '';
  $sex = $_POST['sex'] ?? '';
  $id_ward = $_POST['id_ward'] ?? '';
  $id_document = $_POST['id_document'] ?? '';
  $document = $_POST['document'] ?? '';
  $obs = $_POST['obs'] ?? null; // Campo opcional
  $id_user = $_POST['user_id'] ?? ''; // Coleta o ID do usuário
  $id_relationship = $_POST['id_relationship'] ?? ''; // Coleta o ID do relacionamento
  $id_church = $_POST['id_church'] ?? '';

  $barcode = $_POST['barcode'] ?? null;
  $mes = $_POST['mes'] ?? null;
  $ano = $_POST['ano'] ?? null;

  // Verifica se o mês e o ano estão preenchidos
  if (!empty($mes) && !empty($ano)) {
    $expiration_date = "$ano-$mes-01";
  } else {
    $expiration_date = null;
  }

  // Converter as datas
  $nasc_date = formatDateOrTime($nasc_date, 'date_BR_EN');

  // Preparar a query de inserção com UUID() diretamente
  $stmt = $conn->prepare("INSERT INTO passengers (id, name, nasc_date, sex, id_ward, id_document, document, obs, created_by, id_relationship, id_church, barcode, expiration_date) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssisssisssss", $name, $nasc_date, $sex, $id_ward, $id_document, $document, $obs, $id_user, $id_relationship, $id_church, $barcode, $expiration_date);

  // Executar a query
  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'msg' => 'Passageiro adicionado com sucesso.']);
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao adicionar passageiro.']);
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

$conn->close();