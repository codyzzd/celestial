<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
$apiPath = "../resources/api.php";
?>
<?php
session_start(); // Inicia ou continua a sessão atual

// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

//pega o id da url
$id_caravan = $_GET['id'];
// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();
// Guarda a role do usuário
$user_role = checkUserRole($user_id);
// Guarda o id da estaca
$user_stake = checkStake($user_id);
// Pega as caravanas
$caravan = getCaravan($id_caravan);
//pega os veiculos da caravana
$vehicles = getCaravanVehicles($id_caravan);
//pega os passageiros disponiveis do usuario
$passengers = getPassengers($user_id);
//pega os passageiros que tem até 6 anos
// $kids = getPassengers($user_id, $caravan['start_date']);
//descobrir qual banco esta ocupado por quem
$reserveds = getSeatsReserved($id_caravan);

$myseats = getMySeats($user_id, $id_caravan);
// echo json_encode($myseats);

//calcular capacity
$totalCapacity = 0;
foreach ($vehicles as $vehicle) {
  $totalCapacity += $vehicle['capacity'];
}
// Contar o número de assentos reservados
$totalReservedSeats = count($reserveds);

// Calcular a capacidade disponível
$availableSeats = $totalCapacity - $totalReservedSeats;

// Calcular a porcentagem de ocupação
$occupiedPercentage = ($totalCapacity - $availableSeats) / $totalCapacity * 100;

// Formatar a porcentagem com duas casas decimais
$formattedPercentage = number_format($occupiedPercentage, 2);
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
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto pb-2 md:p-4 mb-20">
      <div class="flex flex-col gap-1 md:gap-2">
        <div class="bg-white  dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  overflow-auto "
             id="header">
          <div class="bg-cover bg-center bg-no-repeat  aspect-video md:aspect-auto md:h-[170px]"
               id="foto"
               style="background-image: url('../resources/i/destinations/<?= $caravan['photo'] ?>');"></div>
          <div class="p-4">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Caravana</p>
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white"><?= $caravan['name']; ?></h5>
          </div>
        </div>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-row justify-between gap-4 md:rounded-lg md:shadow  "
             id="start_finish">
          <div class="flex-1 text-left">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Partida</p>
            <p class="text-2xl"><?= formatDateOrTime($caravan['start_time'], 'time_Hi'); ?></p>
            <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
          </div>
          <div class="flex-1 text-left">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Retorno</p>
            <p class="text-2xl"><?= formatDateOrTime($caravan['return_time'], 'time_Hi'); ?></p>
            <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
          </div>
        </div>
        <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-col gap-1 md:rounded-lg md:shadow"
             id="capacity">
          <div class="flex flex-row gap-1 items-end">
            <p class="text-2xl"><?= $availableSeats ?></p>
            <p class="text-sm text-gray-500 mb-[2px]">Assentos disponíveis</p>
          </div>
          <div class="w-full bg-gray-200 h-2 rounded-full overflow-auto flex flex-row ">
            <div class="h-2 <?= $formattedPercentage >= 80 ? 'bg-red-600' : 'bg-purple-600' ?>"
                 style="width:<?= $formattedPercentage ?>%;"></div>
          </div>
        </div>
        <?php if (!empty($caravan['obs'])): ?>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  "
               id="obs">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Observação</p>
            <p class="text-sm text-gray-500"><?= $caravan['obs']; ?></p>
          </div>
        <?php endif; ?>
        <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4 mb-2">Suas Reservas</h2>
      </div>
      <div class="md:w-full md:mx-0 text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y overflow-auto mx-4">
        <?php foreach ($myseats as $seat): ?>
          <button class="block w-full px-4 py-2 border-gray-200 cursor-pointer flex justify-between items-center">
            <div class="flex flex-row text-start truncate items-center">
              <i class="text-lg text-gray-500 me-2 fa min-w-[20px] text-center <?= $seat['is_approved'] ? 'fa-circle-check text-green-600' : 'fa-hourglass-start text-yellow-600' ?> "></i>
              <div class="flex flex-col truncate">
                <p class="truncate"><?= htmlspecialchars($seat['name']) ?></p>
                <p class="text-sm text-gray-500 truncate"><?= $seat['is_approved'] ? 'Aprovado' : 'Pendente' ?></p>
              </div>
            </div>
            <div class="flex flex-col text-end">
              <p class="text-sm text-gray-500"><?= htmlspecialchars($seat['vehicle_name']) ?></p>
              <p class="text-sm text-gray-500">
                <?= $seat['seat'] !== null ? "Banco " . htmlspecialchars($seat['seat']) : "Sem Banco" ?>
              </p>
            </div>
          </button>
        <?php endforeach; ?>
      </div>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>