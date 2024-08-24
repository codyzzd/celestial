<?php

function checkUserLogin()
{
  // Inicie a sessão se ainda não estiver iniciada
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  // Verifique se o usuário está logado com base na variável de sessão
  if (isset($_SESSION['user_id'])) {
    return $_SESSION['user_id'];
  }

  // Verifique o token de lembrete se a sessão não estiver configurada
  if (isset($_COOKIE['caravana_remember_token'])) {
    $token = $_COOKIE['caravana_remember_token'];

    // Conectar ao banco de dados
    $conn = getDatabaseConnection(); // Assuma que você tem uma função para obter a conexão

    // Prepare a consulta para obter o ID do usuário com base no token
    $sql = "SELECT id FROM users WHERE remember_token = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
      die('Erro na preparação da consulta SQL: ' . $conn->error);
    }

    // Vincular o token e executar a consulta
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      // Se o token for encontrado, obtenha o ID do usuário
      $stmt->bind_result($user_id);
      $stmt->fetch();

      // Defina a variável de sessão
      $_SESSION['user_id'] = $user_id;

      // Opcional: Regenerar o token para segurança
      $new_token = bin2hex(random_bytes(16));
      $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
      $update_stmt->bind_param("si", $new_token, $user_id);
      $update_stmt->execute();
      setcookie('caravana_remember_token', $new_token, time() + (86400 * 30), "/");

      // Fechar declarações
      $update_stmt->close();
    } else {
      // Se o token não for válido, redirecionar para a página de login
      header("Location: login.php");
      exit();
    }

    $stmt->close();
    $conn->close();
  } else {
    // Se não houver token de lembrete e não estiver na sessão, redirecionar para a página de login
    header("Location: login.php");
    exit();
  }
}

function getIndicador(): string
{
  return $_POST['indicador'] ?? $_GET['indicador'] ?? '';
}

function generateSalt()
{
  return bin2hex(random_bytes(16)); // 16 bytes = 32 caracteres hexadecimais
}

function hashPassword($password, $salt)
{
  return hash('sha256', $password . $salt);
}

function verifyPassword($provided_password, $stored_hash, $stored_salt)
{
  $login_hash = hashPassword($provided_password, $stored_salt);
  return $login_hash === $stored_hash;
}

function toLowerCase($input)
{
  return strtolower($input);
}

// Função para obter a role do usuário com base no ID
function checkUserRole($user_id, $role = null)
{
  $conn = getDatabaseConnection();

  // Prepare a consulta para buscar a role do usuário
  $stmt = $conn->prepare("SELECT r.slug FROM users u JOIN roles r ON u.role = r.id WHERE u.id = ?");
  $stmt->bind_param("s", $user_id);

  // Executa a consulta
  $stmt->execute();
  $result = $stmt->get_result();

  // Verifica se há resultados e retorna o slug da role
  if ($row = $result->fetch_assoc()) {
    $role_slug = $row['slug'];

    // Se um role for fornecido e não corresponder ao slug encontrado, redireciona para profile.php
    if ($role !== null && $role !== $role_slug) {
      header("Location: panel.php");
      exit(); // Para garantir que o script pare após o redirecionamento
    }
  } else {
    $role_slug = null; // Role não encontrada
  }

  // Fecha a declaração
  $stmt->close();

  // Retorna o slug da role
  return $role_slug;
}

function checkStake($user_id)
{
  // Obtém a conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Prepara a consulta para buscar o id_stake do usuário
  $stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);

  // Executa a consulta
  $stmt->execute();

  // Armazena o resultado
  $stmt->bind_result($id_stake);
  $stmt->fetch();

  // Fecha a declaração
  $stmt->close();

  // Fecha a conexão com o banco de dados
  $conn->close();

  // Verifica se o id_stake é NULL
  if ($id_stake === null) {
    // Redireciona para stake_select.php se id_stake for NULL
    header("Location: stake_select.php");
    exit();
  }

  // Retorna o valor do id_stake
  return $id_stake;
}

function getDatabaseConnection()
{
  $config = require 'dbcon.php';

  // Usando as configurações do array $config
  $host = $config['host'];
  $username = $config['username'];
  $password = $config['password'];
  $database = $config['database'];
  $port = $config['port'];

  // Criando a conexão com o banco de dados
  $conn = new mysqli($host, $username, $password, $database, $port);

  if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
  }

  return $conn; //volta a conexao
}

function getWardsByUserId($user_id)
{
  $conn = getDatabaseConnection();
  // Inicializar as variáveis
  $id_stake = null;
  $name = '';
  $cod = '';
  $id = '';
  $deleted_at = '';

  // Buscar o id_stake do usuário
  $stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");
  $stmt->bind_param("s", $user_id);
  $stmt->execute();
  $stmt->bind_result($id_stake);
  $stmt->fetch();
  $stmt->close();

  // Verificar se id_stake foi encontrado
  if (!$id_stake) {
    return []; // Retornar array vazio se id_stake não encontrado
  }

  // Buscar as wards com o id_stake correspondente
  $stmt = $conn->prepare("SELECT id, name, cod, deleted_at FROM wards WHERE id_stake = ? ORDER BY name");
  $stmt->bind_param("s", $id_stake);
  $stmt->execute();
  $stmt->bind_result($id, $name, $cod, $deleted_at);

  $wards = [];
  // Armazenar os resultados em um array
  while ($stmt->fetch()) {
    $wards[] = [
      'id' => $id,
      'name' => $name,
      'cod' => $cod,
      'deleted_at' => $deleted_at
    ];
  }

  $stmt->close();

  return $wards;
}

function getDocuments()
{
  // Estabelecer conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Preparar a consulta SQL para selecionar e ordenar os documentos por nome
  $stmt = $conn->prepare("SELECT id, name FROM documents ORDER BY name");

  // Executar a consulta
  $stmt->execute();

  // Vincular os resultados às variáveis
  $stmt->bind_result($id, $name);

  // Inicializar um array para armazenar os documentos
  $documents = [];

  // Armazenar os resultados em um array
  while ($stmt->fetch()) {
    $documents[] = [
      'id' => $id,
      'name' => $name
    ];
  }

  // Fechar a consulta
  $stmt->close();

  // Fechar a conexão com o banco de dados
  $conn->close();

  // Retornar o array de documentos
  return $documents;
}

function getSexs()
{
  // Estabelecer conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Preparar a consulta SQL para selecionar e ordenar os documentos por nome
  $stmt = $conn->prepare("SELECT id, name, slug FROM sexs");

  // Executar a consulta
  $stmt->execute();

  // Vincular os resultados às variáveis
  $stmt->bind_result($id, $name, $slug);

  // Inicializar um array para armazenar os documentos
  $sexs = [];

  // Armazenar os resultados em um array
  while ($stmt->fetch()) {
    $sexs[] = [
      'id' => $id,
      'name' => $name,
      'slug' => $slug
    ];
  }

  // Fechar a consulta
  $stmt->close();

  // Fechar a conexão com o banco de dados
  $conn->close();

  // Retornar o array de documentos
  return $sexs;
}

function getRelations()
{
  // Estabelecer conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Preparar a consulta SQL para selecionar e ordenar os documentos por nome
  $stmt = $conn->prepare("SELECT id, name, slug FROM relationship");

  // Executar a consulta
  $stmt->execute();

  // Vincular os resultados às variáveis
  $stmt->bind_result($id, $name, $slug);

  // Inicializar um array para armazenar os documentos
  $relations = [];

  // Armazenar os resultados em um array
  while ($stmt->fetch()) {
    $relations[] = [
      'id' => $id,
      'name' => $name,
      'slug' => $slug
    ];
  }

  // Fechar a consulta
  $stmt->close();

  // Fechar a conexão com o banco de dados
  $conn->close();

  // Retornar o array de documentos
  return $relations;
}

function getStake($user_id)
{
  // Inicializar a variável para armazenar o id_stake
  $id_stake = null;

  // Estabelecer conexão com o banco de dados
  $conn = getDatabaseConnection();

  try {
    // Preparar a consulta SQL
    $stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");

    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
      throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
    }

    // Vincular o parâmetro e executar a consulta
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    // Buscar o resultado da consulta
    $stmt->bind_result($id_stake);
    if (!$stmt->fetch()) {
      // Se não encontrar o id_stake para o user_id fornecido
      $id_stake = null;
    }

    // Fechar a declaração
    $stmt->close();

  } catch (Exception $e) {
    // Tratar erros e exibir uma mensagem adequada (opcional)
    error_log('Erro: ' . $e->getMessage());
    // Garantir que $id_stake permaneça null em caso de erro
    $id_stake = null;
  } finally {
    // Fechar a conexão com o banco de dados
    if (isset($conn) && $conn instanceof mysqli) {
      $conn->close();
    }
  }

  // Retornar o id_stake
  return $id_stake;
}

function formatDateOrTime($value, $type)
{
  switch ($type) {
    case 'date_BR_EN':
      // Função para converter data do formato dd/mm/yyyy para YYYY-MM-DD
      $dateArray = explode('/', $value);
      if (count($dateArray) == 3) {
        return sprintf('%04d-%02d-%02d', $dateArray[2], $dateArray[1], $dateArray[0]);
      }
      return null;

    case 'date_EN_dMY':
      // Define o padrão de formatação da data
      $formatter = new IntlDateFormatter(
        'pt_BR',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        null,
        null,
        'dd MMM yyyy'
      );

      // Converte a string de data para um timestamp
      $timestamp = strtotime($value);

      // Formata a data
      $formattedDate = $formatter->format($timestamp);

      // Converte o mês para minúsculas
      return strtolower($formattedDate);

    case 'time_Hi':
      // Formata a hora como "HH:mm" (ex: "10:00")
      $timestamp = strtotime($value);
      return date('H:i', $timestamp);

    case 'date_EN_BR':
      // Converte a data do formato YYYY-MM-DD para dd/mm/yyyy
      $dateArray = explode('-', $value);
      if (count($dateArray) == 3) {
        return sprintf('%02d/%02d/%04d', $dateArray[2], $dateArray[1], $dateArray[0]);
      }
      return null;

    default:
      return null;
  }
}

function isMobile()
{
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  return preg_match('/iPhone|iPad|iPod|Android|webOS|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
}

function getVehicles($user_stake)
{
  // Estabelecer conexão com o banco de dados
  $conn = getDatabaseConnection(); // Supondo que getDatabaseConnection() retorne um objeto mysqli

  // Preparar a consulta SQL
  $stmt = $conn->prepare("SELECT id, capacity, name FROM vehicles WHERE id_stake = ?");

  // Verifica se a preparação da consulta foi bem-sucedida
  if ($stmt === false) {
    die('Erro ao preparar a consulta: ' . $conn->error);
  }

  // Vincular o parâmetro
  $stmt->bind_param("s", $user_stake);

  // Executar a consulta
  $stmt->execute();

  // Obtém o resultado
  $result = $stmt->get_result();

  // Verifica se encontrou algum resultado
  $vehicles = [];
  if ($result->num_rows > 0) {
    // Loop para pegar cada veículo
    while ($row = $result->fetch_assoc()) {
      $vehicles[] = $row; // Adiciona cada veículo ao array
    }
  }

  // Fecha a declaração
  $stmt->close();

  // Fecha a conexão com o banco de dados
  $conn->close();

  // Retornar o array de veículos
  return $vehicles;
}

function getCaravans($user_id)
{
  // Conectar ao banco de dados
  $conn = getDatabaseConnection();

  // Passo 1: Obter o id_stake do usuário
  $stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");
  $stmt->bind_param("s", $user_id);
  $stmt->execute();
  $stmt->bind_result($id_stake);
  $stmt->fetch();
  $stmt->close();

  // Verificar se encontrou o id_stake
  if (!$id_stake) {
    return []; // Retorna array vazio se não encontrar id_stake
  }

  // Passo 2: Buscar as caravanas onde id_stake corresponde e a data de partida é no futuro ou hoje
  $today = date('Y-m-d');
  $stmt = $conn->prepare("SELECT * FROM caravans WHERE id_stake = ? AND start_date >= ?");
  $stmt->bind_param("ss", $id_stake, $today);
  $stmt->execute();
  $result = $stmt->get_result();

  // Passo 3: Armazenar os resultados em um array
  $caravans = [];
  while ($row = $result->fetch_assoc()) {
    $caravans[] = $row;
  }

  // Fechar a conexão
  $stmt->close();
  $conn->close();

  return $caravans;
}

function getCaravan($caravan_id)
{
  $conn = getDatabaseConnection();

  // Prepare a consulta SQL para selecionar todos os dados da tabela caravans baseado no caravan_id
  $sql = "SELECT * FROM caravans WHERE id = ?";

  // Preparar a declaração SQL
  if ($stmt = $conn->prepare($sql)) {
    // Bind do parâmetro $caravan_id
    $stmt->bind_param("s", $caravan_id); // "s" significa que o parâmetro é uma string

    // Executar a declaração
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();

    // Verificar se algum dado foi encontrado
    if ($result->num_rows > 0) {
      // Retornar os dados como um array associativo
      $caravanData = $result->fetch_assoc();
    } else {
      $caravanData = null; // Nenhum dado encontrado
    }

    // Fechar a declaração
    $stmt->close();
  } else {
    // Em caso de erro na preparação da consulta
    $caravanData = null;
  }

  // Fechar a conexão
  $conn->close();

  return $caravanData;
}

function getVehicle($vehicle_id)
{
  $conn = getDatabaseConnection();

  // Construir a consulta SQL com LEFT JOIN e CASE
  $sql = "
        SELECT v.*,
               CASE
                 WHEN cv.id_vehicle IS NOT NULL THEN 'yes'
                 ELSE 'no'
               END AS used
        FROM vehicles v
        LEFT JOIN caravan_vehicles cv ON v.id = cv.id_vehicle
        WHERE v.id = ?
    ";

  try {
    // Preparar a consulta SQL
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
    }

    // Vincular o parâmetro e executar a consulta
    $stmt->bind_param("s", $vehicle_id);
    $stmt->execute();

    // Obter os resultados
    $result = $stmt->get_result();

    // Verificar se algum veículo foi encontrado
    if ($result->num_rows > 0) {
      // Buscar o veículo e armazenar em um array
      $vehicle = $result->fetch_assoc();

      // Fechar a declaração
      $stmt->close();

      // Retornar os dados do veículo
      return $vehicle;
    } else {
      // Se nenhum veículo for encontrado, redirecionar para vehicles.php
      $_SESSION['error_message'] = 'Nenhum veículo encontrado com o ID fornecido.';
      header('Location: vehicles.php');
      exit();
    }
  } catch (Exception $e) {
    // Tratar erros e redirecionar para vehicles.php com mensagem de erro
    $_SESSION['error_message'] = 'Erro ao executar a consulta: ' . $e->getMessage();
    header('Location: vehicles.php');
    exit();
  }
}

function getVehiclesUsed($caravan_id)
{
  // Obtém a conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Prepara a consulta SQL para buscar os veículos associados à caravana
  $sql = "
  SELECT v.id as id, v.name, v.capacity, cv.id as id_caravan_vehicle
  FROM caravan_vehicles cv
  JOIN vehicles v ON cv.id_vehicle = v.id
  WHERE cv.id_caravan = ?
  ";

  // Prepara a declaração SQL
  $stmt = $conn->prepare($sql);

  // Verifica se a preparação foi bem-sucedida
  if ($stmt === false) {
    die('Erro ao preparar a consulta: ' . $conn->error);
  }

  // Liga o parâmetro caravan_id à consulta
  $stmt->bind_param('s', $caravan_id);

  // Executa a consulta
  $stmt->execute();

  // Armazena o resultado
  $stmt->store_result();

  // Verifica se houve resultados
  $vehicles = array();
  if ($stmt->num_rows > 0) {
    // Liga as variáveis de saída aos campos da consulta
    $stmt->bind_result($id, $name, $capacity, $id_caravan_vehicle);

    // Busca os resultados
    while ($stmt->fetch()) {
      $vehicles[] = [
        'id' => $id,
        'id_cv' => $id_caravan_vehicle,
        'name' => $name,
        'capacity' => $capacity,
      ];
    }
  }

  // Fecha a declaração e a conexão
  $stmt->close();
  $conn->close();

  // Retorna o array de veículos (pode estar vazio)
  return $vehicles;
}

function getCaravanVehicles($caravan_id)
{
  // Conecta no banco
  $conn = getDatabaseConnection();

  // Prepara a consulta SQL
  $sql = "
        SELECT vehicles.*
        FROM caravan_vehicles
        JOIN vehicles ON caravan_vehicles.id_vehicle = vehicles.id
        WHERE caravan_vehicles.id_caravan = ?
    ";

  // Prepara a declaração
  $stmt = $conn->prepare($sql);

  // Vincula o parâmetro e executa
  $stmt->bind_param('i', $caravan_id); // 'i' indica que o parâmetro é um inteiro
  $stmt->execute();

  // Obtém o resultado
  $result = $stmt->get_result();

  // Busca todos os dados
  $vehicles = $result->fetch_all(MYSQLI_ASSOC);

  // Fecha a declaração e a conexão
  $stmt->close();
  $conn->close();

  return $vehicles;
}

function getPassengers($user_id)
{
  // Obter a conexão com o banco de dados
  $conn = getDatabaseConnection();

  // Preparar a consulta SQL
  $sql = "SELECT * FROM passengers WHERE created_by = ? AND deleted_at IS NULL";

  // Preparar a declaração
  if ($stmt = $conn->prepare($sql)) {
    // Bind the user_id parameter to the query
    $stmt->bind_param("i", $user_id); // "i" para integer

    // Executar a declaração
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();

    // Armazenar todos os dados em um array
    $passengers = $result->fetch_all(MYSQLI_ASSOC);

    // Fechar a declaração
    $stmt->close();

    // Fechar a conexão
    $conn->close();

    // Retornar os dados dos passageiros
    return $passengers;
  } else {
    // Se houver um erro ao preparar a consulta
    throw new Exception("Erro ao preparar a consulta: " . $conn->error);
  }
}