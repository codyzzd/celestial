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

// Função para listar reservas
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
  users.role = roles.id
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

  // 4. Monta a query com ou sem a condição dependendo do slug
  if ($slug_role === 'stake_lider' || $slug_role === 'stake_aux') {
    $sql = "
  SELECT seats.id, passengers.name, seats.seat, seats.is_approved, seats.is_payed
  FROM seats
  JOIN passengers ON seats.id_passenger = passengers.id
  WHERE seats.id_caravan = ?
  ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $caravan_id);
  } else {
    $sql = "
  SELECT seats.id, passengers.name, seats.seat, seats.is_approved, seats.is_payed
  FROM seats
  JOIN passengers ON seats.id_passenger = passengers.id
  WHERE seats.id_caravan = ? AND passengers.id_ward = ?
  ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $caravan_id, $ward_id);
  }

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

// Função para alternar aprovação de reserva
if ($indicador == 'reserv_toggle') {
  $seat_id = $_POST['seat_id'];

  if ($seat_id) {
    $sql = "UPDATE seats SET is_approved = NOT is_approved WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $seat_id);

    if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'msg' => 'Status alterado com sucesso.']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao atualizar o status.']);
    }

    $stmt->close();
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'ID do assento não fornecido.']);
  }
}

// Função para alternar pagamento de reserva
if ($indicador == 'pay_toggle') {
  $seat_id = $_POST['seat_id'];

  if ($seat_id) {
    $sql = "UPDATE seats SET is_payed = NOT is_payed WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $seat_id);

    if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'msg' => 'Status alterado com sucesso.']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao atualizar o status.']);
    }

    $stmt->close();
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'ID do assento não fornecido.']);
  }
}

// Função para deletar reserva
if ($indicador == 'reserv_delete') {
  $seat_id = $_POST['id'];

  $sql = "DELETE FROM seats WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $seat_id);

  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'msg' => 'Reserva deletada com sucesso.']);
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao deletar reserva.']);
  }

  $stmt->close();
}

// Função para trocar assentos
if ($indicador == 'reserv_switch') {
  $new_seat_number = $_POST['id_new_seat'];
  $old_seat_id = $_POST['id_old_seat'];
  $caravan_id = $_POST['caravan_id'];

  $query1 = "SELECT seat FROM seats WHERE id = ?";
  $stmt1 = $conn->prepare($query1);
  $stmt1->bind_param("s", $old_seat_id);
  $stmt1->execute();
  $stmt1->bind_result($old_seat_number);
  $stmt1->fetch();
  $stmt1->close();

  $query2 = "SELECT id FROM seats WHERE seat = ? AND id_caravan = ?";
  $stmt2 = $conn->prepare($query2);
  $stmt2->bind_param("ss", $new_seat_number, $caravan_id);
  $stmt2->execute();
  $stmt2->bind_result($new_seat_id);
  $stmt2->fetch();
  $stmt2->close();

  $conn->begin_transaction();

  try {
    $update1 = "UPDATE seats SET seat = ? WHERE id = ?";
    $stmt3 = $conn->prepare($update1);
    $stmt3->bind_param("ss", $new_seat_number, $old_seat_id);
    $stmt3->execute();
    $stmt3->close();

    $update2 = "UPDATE seats SET seat = ? WHERE id = ?";
    $stmt4 = $conn->prepare($update2);
    $stmt4->bind_param("ss", $old_seat_number, $new_seat_id);
    $stmt4->execute();
    $stmt4->close();

    $conn->commit();

    echo json_encode(['status' => 'success', 'msg' => 'Assentos trocados com sucesso.']);
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao trocar assentos.']);
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


if ($indicador == 'seat_add_novehicle') {
  $id_user = $_POST['user_id'] ?? null;
  $id_caravan = $_POST['id_caravan'] ?? null;
  $id_passenger = $_POST['passenger_id'] ?? null;
  $no_seat = isset($_POST['no_seat']) && $_POST['no_seat'] == '1' ? 1 : 0;

  try {
    // Busca o total de assentos da caravana
    $queryTotal = "SELECT total_seats FROM caravans WHERE id = ?";
    $stmt = $conn->prepare($queryTotal);
    $stmt->bind_param("s", $id_caravan);
    $stmt->execute();
    $result = $stmt->get_result();
    $caravan = $result->fetch_assoc();
    $stmt->close();

    if (!$caravan) {
      throw new Exception('Caravana não encontrada.');
    }

    $totalSeats = (int) $caravan['total_seats'];

    // Só checa assentos se for reserva com assento
    if (!$no_seat) {
      $queryCount = "SELECT COUNT(*) AS total FROM seats WHERE id_caravan = ? AND no_seat = 0 ";
      $stmt = $conn->prepare($queryCount);
      $stmt->bind_param("s", $id_caravan);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $usedSeats = (int) $row['total'];
      $stmt->close();

      if ($usedSeats >= $totalSeats) {
        echo json_encode([
          'status' => 'error',
          'msg' => 'A caravana já está com todos os assentos ocupados.'
        ]);
        exit;
      }
    }

    // Insere a nova reserva
    $queryInsert = "INSERT INTO seats (id, id_caravan, id_passenger, no_seat, created_by) VALUES (UUID(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($queryInsert);
    $stmt->bind_param("ssis", $id_caravan, $id_passenger, $no_seat, $id_user);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
      'status' => 'success',
      'msg' => 'Reserva registrada com sucesso.'
    ]);
  } catch (Exception $e) {
    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }
}


if ($indicador == 'download_list') {
  $vehicle = $_POST['vehicleId']; //id cv do veiculo
  $novehicle = $_POST['novehicles'];
  // $filetype = $_POST['fileType']; //xls ou pdf
  $reportType = $_POST['reportType']; //simples ou completo

  if ($novehicle === 'false') {
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
  } else { //pegar dados sem veiculos
    $sql_completo = "SELECT
 ROW_NUMBER() OVER (ORDER BY p.name ASC) AS N,
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
    wards w ON w.id = p.id_ward -- Adiciona o join com a tabela wards
  WHERE
    s.id_caravan = ? -- Alterado para usar s.id_caravan_vehicle
  ORDER BY p.name asc";

    $sql_simples = "SELECT

 ROW_NUMBER() OVER (ORDER BY p.name ASC) AS N,
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
  wards w ON w.id = p.id_ward -- Adiciona o join com a tabela wards
  WHERE
  s.id_caravan = ? -- Alterado para usar s.id_caravan_vehicle
  ORDER BY p.name asc";
  }

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


if ($indicador == 'caravan_edit') {
  if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
      ${$key} = $value;
    }
  }

  $conn->begin_transaction();

  try {
    $start_date = $start_date ? formatDateOrTime($start_date, 'date_BR_EN') : null;
    $return_date = $return_date ? formatDateOrTime($return_date, 'date_BR_EN') : null;

    // Se vier total_seats, valida antes de atualizar
    if (isset($_POST['total_seats'])) {
      // Consulta para contar assentos já reservados
      $stmt_check = $conn->prepare("SELECT COUNT(*) FROM seats WHERE id_caravan = ?");
      $stmt_check->bind_param("s", $id);
      $stmt_check->execute();
      $stmt_check->bind_result($current_seats);
      $stmt_check->fetch();
      $stmt_check->close();

      // Verifica se o novo total é suficiente
      if ((int) $total_seats <= (int) $current_seats || (int) $total_seats <= 0) {
        $conn->rollback();
        echo json_encode([
          'status' => 'error',
          'msg' => 'O número total de vagas não pode ser menor que o total de reservas já realizadas.'
        ]);
        exit;
      }

      // UPDATE com total_seats
      $stmt = $conn->prepare("
        UPDATE caravans
        SET name = ?, start_date = ?, start_time = ?, return_date = ?, return_time = ?, obs = ?, total_seats = ?
        WHERE id = ?
      ");
      $stmt->bind_param("ssssssss", $name, $start_date, $start_time, $return_date, $return_time, $obs, $total_seats, $id);
    } else {
      // UPDATE normal sem total_seats
      $stmt = $conn->prepare("
        UPDATE caravans
        SET name = ?, start_date = ?, start_time = ?, return_date = ?, return_time = ?, obs = ?
        WHERE id = ?
      ");
      $stmt->bind_param("sssssss", $name, $start_date, $start_time, $return_date, $return_time, $obs, $id);
    }

    if ($stmt->execute()) {
      if (empty($total_seats) && !empty($_POST['vehicle_ids'])) {
        $vehicleIds = json_decode($_POST['vehicle_ids'], true);

        if (!empty($vehicleIds)) {
          $stmt = $conn->prepare("INSERT INTO caravan_vehicles (id, id_caravan, id_vehicle) VALUES (UUID(), ?, ?)");

          foreach ($vehicleIds as $vehicle) {
            $stmt->bind_param("ss", $id, $vehicle['id']);
            $stmt->execute();
          }
        }
      }

      $conn->commit();

      echo json_encode([
        'status' => 'success',
        'msg' => 'Caravana atualizada com sucesso!'
      ]);
    } else {
      throw new Exception('Erro ao atualizar a caravana: ' . $stmt->error);
    }
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }

  $stmt->close();
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

    if (!empty($total_seats)) {
      // Cadastro sem veículos, com total_seats
      $stmt = $conn->prepare("
        INSERT INTO caravans (id, id_stake, name, start_date, start_time, return_date, return_time, obs, destination, total_seats)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->bind_param("ssssssssss", $uuid, $stake_id, $name, $start_date, $start_time, $return_date, $return_time, $obs, $destination, $total_seats);

      if ($stmt->execute()) {
        $conn->commit();
        echo json_encode([
          'status' => 'success',
          'msg' => 'Caravana adicionada com sucesso!'
        ]);
      } else {
        throw new Exception('Erro ao adicionar a caravana: ' . $stmt->error);
      }
    } else {
      // Cadastro com veículos
      $stmt = $conn->prepare("
        INSERT INTO caravans (id, id_stake, name, start_date, start_time, return_date, return_time, obs, destination)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->bind_param("sssssssss", $uuid, $stake_id, $name, $start_date, $start_time, $return_date, $return_time, $obs, $destination);

      if ($stmt->execute()) {
        if (!empty($_POST['vehicle_ids'])) {
          $vehicleIds = json_decode($_POST['vehicle_ids'], true);

          if (!empty($vehicleIds)) {
            $stmt = $conn->prepare("INSERT INTO caravan_vehicles (id, id_caravan, id_vehicle) VALUES (UUID(), ?, ?)");

            foreach ($vehicleIds as $vehicleId) {
              $stmt->bind_param("ss", $uuid, $vehicleId);
              $stmt->execute();
            }

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
    }
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
      'status' => 'error',
      'msg' => $e->getMessage()
    ]);
  }

  if (isset($stmt)) {
    $stmt->close();
  }
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

if ($indicador == 'vehicle_add') {
  // user_id
  // name
  // obs
  // capacity
  // stake_id
  // seat_map

  // Verifica se o array $_POST não está vazio
  if (!empty($_POST)) {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $obs = $_POST['obs'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $stake_id = $_POST['stake_id'] ?? '';
    $seat_map = $_POST['seat_map'] ?? '';

    // Preparar a query de inserção com UUID() diretamente
    $stmt = $conn->prepare("INSERT INTO vehicles (id, name, capacity, obs, id_stake, seat_map) VALUES (UUID(), ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $name, $capacity, $obs, $stake_id, $seat_map);

    // Executar a query
    if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'msg' => 'Veículo adicionado com sucesso.']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao adicionar veículo.']);
    }

    $stmt->close(); // Fechar a declaração
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Dados insuficientes fornecidos.']);
  }
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
    echo json_encode(['status' => 'error', 'msg' => 'ID do veículo não fornecido.']);
    exit;
  }

  // Prepara a consulta SQL
  $sql = "SELECT * FROM vehicles WHERE id = ?";

  // Prepara a declaração
  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $vehicle = $result->fetch_assoc();
      echo json_encode(['status' => 'success', 'data' => $vehicle]);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Veículo não encontrado.']);
    }

    $stmt->close();
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao preparar a consulta.']);
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
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $obs = $_POST['obs'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $seat_map = $_POST['seat_map'] ?? '';

    // Preparar a query de atualização
    $stmt = $conn->prepare("UPDATE vehicles SET name = ?, obs = ?, capacity = ?, seat_map = ? WHERE id = ?");
    $stmt->bind_param("ssiss", $name, $obs, $capacity, $seat_map, $id);

    // Executar a query
    if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'msg' => 'Veículo atualizado com sucesso.']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => 'Erro ao atualizar veículo.']);
    }

    $stmt->close(); // Fechar a declaração
  } else {
    echo json_encode(['status' => 'error', 'msg' => 'Dados insuficientes fornecidos.']);
  }
}

$conn->close();