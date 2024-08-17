<?php
function checkUserLogin()
{
  // Verifique se o usuário está logado
  if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redirecionar para a página de login
    header("Location: login.php");
    exit();
  }

  // Retorna o ID do usuário se estiver logado
  return $_SESSION['user_id'];
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

// Função para converter data do formato dd/mm/yyyy para YYYY-MM-DD
function convertDateFormat($date)
{
  $dateArray = explode('/', $date);
  if (count($dateArray) == 3) {
    return sprintf('%04d-%02d-%02d', $dateArray[2], $dateArray[1], $dateArray[0]);
  }
  return null;
}