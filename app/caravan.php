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
$caravan = getCaravan($_GET['id']);
//pega os veiculos da caravana
// $vehicles = getCaravanVehicles($caravan['id']);
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
    <title><?= $caravan['name']; ?> - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <section class="max-w-lg container mx-auto pb-2 md:p-4 mb-20">
      <div class="flex flex-col gap-1 md:gap-2">
        <div class="bg-white  dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  overflow-auto "
             id="header">
          <div class="destinationphoto aspect-video"></div>
          <div class="p-4">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Caravana</p>
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white"><?= $caravan['name']; ?></h5>
          </div>
        </div>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-row justify-between gap-4 md:rounded-lg md:shadow  "
             id="start_finish">
          <!-- <i class="fa fa-fw fa-circle-right text-4xl text-gray-500"></i> -->
          <div class="flex-1 text-left">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Partida</p>
            <p class="text-2xl"><?= $caravan['start_time']; ?></p>
            <p class="text-sm text-gray-500"><?= $caravan['start_date']; ?></p>
          </div>
          <div class="flex-1 text-left">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Retorno</p>
            <p class="text-2xl"><?= $caravan['return_time']; ?></p>
            <p class="text-sm text-gray-500"><?= $caravan['return_time']; ?></p>
          </div>
        </div>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-col gap-1 md:rounded-lg md:shadow"
             id="capacity">
          <!-- <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Capacidade</p> -->
          <div class="flex flex-row gap-1 items-end">
            <p class="text-2xl">52</p>
            <p class="text-sm text-gray-500 mb-[2px]">Assentos disponíveis</p>
          </div>
          <div class="w-full bg-gray-200 h-2 rounded-full overflow-auto flex flex-row justify-end">
            <div class="bg-purple-600 h-2 "
                 style="width: 15%;"></div>
          </div>
        </div>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  "
             id="obs">
          <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Observação</p>
          <p class="text-sm text-gray-500"><?= $caravan['obs']; ?></p>
        </div>
        <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Reservar vaga</h2>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  ">
          <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">{nome do veiculo}</p>
        </div>
      </div>
      </div>
      </div>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>