<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
?>
<?php
session_start(); // Inicia ou continua a sessão atual

// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();

// Guarda a role do usuário
$user_role = checkUserRole($user_id);

//iniciar consulta para obter dados para pagina
$conn = getDatabaseConnection();

// Prepare a SQL statement com placeholders
$sql = "
    SELECT
        u.id,
        u.name AS user_name,
        s.name AS stake_name,
        w.name AS ward_name,
        r.name AS role_name
    FROM
        users u
    LEFT JOIN
        stakes s ON u.id_stake = s.id
    LEFT JOIN
        wards w ON u.id_ward = w.id
    LEFT JOIN
        roles r ON u.role = r.id
";

// Preparar a declaração SQL
$stmt = $conn->prepare($sql);

// Execute a consulta
$stmt->execute();

// Obtenha o resultado
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  // Atribuir os valores às variáveis, ou manter null se não houver resultado
  $user_name_profile = $row["user_name"] ?? null;
  $user_stake_profile = $row["stake_name"] ?? 'não selecionada';
  $user_ward_profile = $row["ward_name"] ?? 'não selecionada';
  $user_role_profile = $row["role_name"] ?? 'Membro';
}

// Criar o array associativo
$profile = [
  'name' => $user_name_profile,
  'stake' => $user_stake_profile,
  'ward' => $user_ward_profile,
  'role' => $user_role_profile,
];

// Fechar a declaração e a conexão
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

  <head>
    <meta charset="utf-8" />


    <?php
    require_once ROOT_PATH . '/resources/functions.php';
    require_once ROOT_PATH . '/resources/head_tailwind.php';
    require_once ROOT_PATH . '/resources/head_flowbite.php';
    require_once ROOT_PATH . '/resources/head_fontawesome.php';
    require_once ROOT_PATH . '/resources/head_jquery.php';
    ?>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0<?php if (isMobile())
            echo ', user-scalable=no'; ?>">
    <title>Perfil - Caravana Celestial</title>
    <style>
      .relative-container {
        position: relative;
      }

      #stake {
        width: 100%;
        /* Garante que o input ocupa toda a largura do contêiner */
      }

      #dropdown {
        position: absolute;
        /*top: 100%;*/
        /* Posiciona o dropdown logo abaixo do input */
        left: 0;
        width: 100%;
        /* Garante que o dropdown tenha a mesma largura do input */
        box-sizing: border-box;
        /* Inclui padding e bordas na largura total */
      }
    </style>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Perfil</h1>
          <!-- <p class="text-gray-500">Aqui você pode ajustar configurações que influenciam o uso do app e seus acessos.</p> -->
        </div>
        <!-- <button type="button"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full md:w-fit">Criar</button> -->
      </div>

      <!-- tabela -->

      <div class="flex flex-col gap-4">

        <div id="profile_info"
             class="flex flex-row gap-4">
          <div class="relative inline-flex items-center justify-center w-16 h-16 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-600">
            <span class="font-medium text-gray-600 dark:text-gray-300">BG</span>
          </div>
          <div>
            <p class="text-[10px] font-medium tracking-wider text-gray-600 uppercase">
              <?= $profile['role'] ?>
            </p>
            <h2 class="font-semibold truncate text-xl">
              <?= $profile['name'] ?>
            </h2>

            <!-- <p class="text-sm text-gray-500 truncate">0 Caravanas</p> -->
            <p class="text-sm text-gray-500 truncate">
              Estaca <?= $profile['stake'] ?>
            </p>
            <p class="text-sm text-gray-500 truncate">
              Ala <?= $profile['ward'] ?>
            </p>

          </div>
        </div>

        <div class="flex flex-col gap-2">
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white">Configurações</h2>
          <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y">
            <!-- <a href="profile_edit.php"
               class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between  ">
              <span><i class="fa fa-user-pen text-lg text-gray-500 fa-fw me-2"></i>
                Editar Perfil</span>
              <i class="fa fa-chevron-right text-lg text-gray-500"></i>
            </a> -->
            <a href="stake_select.php"
               class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
              <span><i class="fa fa-people-group text-lg text-gray-500 fa-fw me-2"></i>
                Selecionar Estaca</span>
              <i class="fa fa-chevron-right text-lg text-gray-500"></i>
            </a>
            <a href="ward_select.php"
               class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
              <span><i class="fa fa-tents text-lg text-gray-500 fa-fw me-2"></i>
                Selecionar Ala</span>
              <i class="fa fa-chevron-right text-lg text-gray-500"></i>
            </a>

            <a href="recomendation_edit.php"
               class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between cursor-not-allowed text-gray-400 bg-gray-200 pointer-events-none">
              <span><i class="fa fa-id-card-clip text-lg text-gray-500 fa-fw me-2"></i>
                Editar Recomendação</span>
              <i class="fa fa-chevron-right text-lg text-gray-500"></i>
            </a>

          </div>
        </div>

        <a href="logout.php"
           class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 text-center">
          <i class="fa fa-right-from-bracket me-2"></i>Sair do Celestial
        </a>
      </div>

    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>

  </body>

</html>