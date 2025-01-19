<?php
// Pega o caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Conectar no banco de dados
$conn = getDatabaseConnection();

// Executar a query para deletar registros com expire_at anterior à data de hoje
$query = "DELETE FROM role_alt WHERE expire_at < CURDATE()";

// Preparar e executar a query
$stmt = $conn->prepare($query);
$success = $stmt->execute();

// Checar quantas linhas foram afetadas (deletadas)
$affected_rows = $stmt->affected_rows;

// Exibir a quantidade de registros excluídos
echo "Registros excluídos: " . $affected_rows;

// Caso queira ver os dados excluídos antes de deletar, pode fazer um SELECT:
$select_query = "SELECT * FROM role_alt WHERE expire_at < CURDATE()";
$select_stmt = $conn->prepare($select_query);
$select_stmt->execute();
$deleted_rows = $select_stmt->get_result();

// Exibir os registros que seriam excluídos (se necessário)
$deleted_data = $deleted_rows->fetch_all(MYSQLI_ASSOC);

// Usando var_dump para mostrar os dados que seriam excluídos
echo "<pre>";
var_dump($deleted_data);
echo "</pre>";

// Fechar as conexões
$stmt->close();
$select_stmt->close();
$conn->close();
?>