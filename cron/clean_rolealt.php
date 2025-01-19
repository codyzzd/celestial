<?php
// Pega o caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Conectar no banco de dados
$conn = getDatabaseConnection();

// Caminho para o arquivo de log
$log_file = ROOT_PATH . '/cron/clean_rolealt.txt';

// Função para registrar no log
function log_message($message)
{
  global $log_file;
  $date = date('Y-m-d H:i:s');
  $log_entry = "[" . $date . "] " . $message . PHP_EOL;
  file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Executar o SELECT para verificar os registros que seriam excluídos
$select_query = "SELECT * FROM role_alt WHERE expire_at < CURDATE()";
$select_stmt = $conn->prepare($select_query);
$select_stmt->execute();
$deleted_rows = $select_stmt->get_result();

// Exibir os registros que seriam excluídos (se necessário)
$deleted_data = $deleted_rows->fetch_all(MYSQLI_ASSOC);

// Verifica se existem dados para excluir
if (empty($deleted_data)) {
  log_message("Nenhum registro encontrado para exclusão.");
} else {
  // Usando var_dump para mostrar os dados que seriam excluídos
  log_message("Registros encontrados para exclusão:");
  foreach ($deleted_data as $data) {
    log_message("ID: " . $data['id'] . " | Expire At: " . $data['expire_at']);
  }
}

// Agora, executar a query para deletar registros com expire_at anterior à data de hoje
$query = "DELETE FROM role_alt WHERE expire_at < CURDATE()";

// Preparar e executar a query de DELETE
$stmt = $conn->prepare($query);
$success = $stmt->execute();

// Checar quantas linhas foram afetadas (deletadas)
$affected_rows = $stmt->affected_rows;

// Exibir a quantidade de registros excluídos no log
if ($affected_rows > 0) {
  log_message("Registros excluídos: " . $affected_rows);
} else {
  log_message("Nenhum registro foi excluído.");
}

// Fechar as conexões
$stmt->close();
$select_stmt->close();
$conn->close();
?>