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
$user_role = checkUserRole($user_id, 'stake_lider');
?>

<!DOCTYPE html>
<html lang="pt-BR">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1" />
    <title>Caravana</title>

    <?php
    require_once ROOT_PATH . '/resources/head_tailwind.php';
    require_once ROOT_PATH . '/resources/head_flowbite.php';
    require_once ROOT_PATH . '/resources/head_fontawesome.php';
    ?>

  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Estaca</h1>
          <p class="text-gray-500">Aqui você pode ajustar configurações que influenciam o uso do app e seus acessos.</p>
        </div>
        <!-- <button type="button"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full md:w-fit">Criar</button> -->
      </div>

      <!-- tabela -->

      <div class="flex flex-col gap-4">
        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y">

          <a href="stake_lider_wards.php"
             class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
            <span><i class="fa fa-tents text-lg text-gray-500 fa-fw me-2"></i>
              Alas</span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </a>
          <a href="travels.php"
             class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
            <span><i class="fa fa-place-of-worship text-lg text-gray-500 fa-fw me-2"></i>
              Caravanas</span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </a>
          <a href="vehicles.php"
             class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
            <span><i class="fa fa-car-side text-lg text-gray-500 fa-fw me-2"></i>
              Veiculos</span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </a>
          <a href="stake_users.php"
             class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
            <span><i class="fa fa-user-gear text-lg text-gray-500 fa-fw me-2"></i>
              Lideres</span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </a>

        </div>

      </div>

    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>

  </body>

</html>