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
$hasCaravans = empty($caravans);
// echo json_encode($caravans);
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
    <section class="max-w-lg container mx-auto p-4 mb-20">
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
      <?php if (!$hasCaravans): ?>
        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y overflow-auto">
          <?php foreach ($caravans as $caravan): ?>
            <?php if (empty($caravan['deleted_at'])): // Verifica se o campo deleted_at está vazio ?>
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
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($hasCaravans): ?>
        <div class="p-4  rounded-lg   flex flex-col   w-full  border-[2px] border-gray-300 border-dashed"
             id="empty_state">
          <i class="fa fa-signs-post text-3xl text-gray-500 mb-2"></i>
          <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Ainda não há caravanas disponíveis.</h5>
          <p class="text-gray-600 dark:text-gray-300 text-base">Para realizar sua reserva, entre em contato com os líderes e solicite a criação de novas caravanas.</p>
        </div>
      <?php endif; ?>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>