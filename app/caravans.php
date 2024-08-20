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
// Guarda o id da estaca
$user_stake = checkStake($user_id);
// Pega as caravanas
$caravans = getCaravans($user_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">

  <head>
    <meta charset="utf-8" />

    <?php
    require_once ROOT_PATH . '/resources/head_favicon.php';
    require_once ROOT_PATH . '/resources/functions.php';
    require_once ROOT_PATH . '/resources/head_tailwind.php';
    require_once ROOT_PATH . '/resources/head_flowbite.php';
    require_once ROOT_PATH . '/resources/head_fontawesome.php';
    require_once ROOT_PATH . '/resources/head_jquery.php';
    ?>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0<?php if (isMobile())
            echo ', user-scalable=no'; ?>">
    <title>Caravanas - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Caravanas</h1>
          <p class="text-gray-500">Confira as caravanas disponíveis na sua estaca. Reserve seu lugar!</p>
        </div>
        <!-- <button type="button"
                data-modal-target="criar-caravana"
                data-modal-toggle="criar-caravana"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full md:w-fit">Criar Caravana</button> -->
      </div>

      <!-- tabela -->
      <div class="bg-white rounded-lg overflow-hidden border-gray-200 border-[1px]">
        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600  divide-y">

          <?php foreach ($caravans as $caravan): ?>
            <a href="caravan.php?id=<?= htmlspecialchars($caravan['id']) ?>"
               class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between items-center">
              <div class="text-left w-full">
                <p class="truncate text-sm"><?= htmlspecialchars($caravan['name']) ?></p>
                <div class="flex justify-between items-center">
                  <div class="flex-1 text-left">
                    <p class="text-xl"><?= htmlspecialchars(formatDateOrTime($caravan['start_time'], 'time_Hi')) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars(formatDateOrTime($caravan['start_date'], 'date_EN_dMY')) ?></p>
                  </div>
                  <div class="mx-2 flex-shrink-0">
                    <i class="fa fa-circle-right text-xl text-gray-500"></i>
                  </div>
                  <div class="flex-1 text-right">
                    <p class="text-xl"><?= htmlspecialchars(formatDateOrTime($caravan['return_time'], 'time_Hi')) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars(formatDateOrTime($caravan['return_date'], 'date_EN_dMY')) ?></p>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col"
                  class="px-6 py-3">
                Product name
              </th>
              <th scope="col"
                  class="px-6 py-3">
                Color
              </th>
              <th scope="col"
                  class="px-6 py-3">
                Category
              </th>
              <th scope="col"
                  class="px-6 py-3">
                Price
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-white  dark:bg-gray-800 dark:border-gray-700">
              <th scope="row"
                  class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                Apple MacBook Pro 17"
              </th>
              <td class="px-6 py-4">
                Silver
              </td>
              <td class="px-6 py-4">
                Laptop
              </td>
              <td class="px-6 py-4">
                $2999
              </td>
            </tr>
            <tr class="bg-white  dark:bg-gray-800 dark:border-gray-700">
              <th scope="row"
                  class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                Microsoft Surface Pro
              </th>
              <td class="px-6 py-4">
                White
              </td>
              <td class="px-6 py-4">
                Laptop PC
              </td>
              <td class="px-6 py-4">
                $1999
              </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
              <th scope="row"
                  class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                Magic Mouse 2
              </th>
              <td class="px-6 py-4">
                Black
              </td>
              <td class="px-6 py-4">
                Accessories
              </td>
              <td class="px-6 py-4">
                $99
              </td>
            </tr>
          </tbody>
        </table>
      </div> -->
    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>