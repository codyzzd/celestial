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
// Descobrir quais caravanas tem reserva
$caravan = getMyCaravans($user_id);
// echo json_encode($caravan);
// $caravan = null;
$hasCaravans = !empty($caravan);

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
    <title>Painel - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between hidden">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Painel</h1>
          <p class="text-gray-500">Veja suas estatísticas, confira o ranking e planeje suas próximas caravanas facilmente.</p>
        </div>
      </div>
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2"
             id="caravan_list">
          <!-- <h2 class=" text-lg font-semibold text-gray-900 dark:text-white">Caravanas com reserva</h2> -->
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Caravanas Reservadas</h1>
          <?php if ($hasCaravans): ?>
            <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y overflow-auto"
                 id="caravans_reserved">
              <?php foreach ($caravan as $item): ?>
                <a href="<?= isset($item['total_seats']) && $item['total_seats'] !== null ? 'caravan_total_seats.php' : 'caravan.php' ?>?id=<?= htmlspecialchars($item['id']) ?>"
                   class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between items-center">
                  <div class="text-left w-full">
                    <p class="truncate text-sm"><?= htmlspecialchars($item['name']) ?></p>
                    <div class="flex justify-between items-center">
                      <div class="flex-1 text-left">
                        <p class="text-xl"><?= formatDateOrTime($item['start_time'], 'time_Hi') ?></p>
                        <p class="text-sm text-gray-500"><?= formatDateOrTime($item['start_date'], 'date_EN_dMY') ?></p>
                      </div>
                      <div class="mx-2 flex-shrink-0">
                        <i class="fa fa-circle-right text-xl text-gray-500"></i>
                      </div>
                      <div class="flex-1 text-right">
                        <p class="text-xl"><?= formatDateOrTime($item['return_time'], 'time_Hi') ?></p>
                        <p class="text-sm text-gray-500"><?= formatDateOrTime($item['return_date'], 'date_EN_dMY') ?></p>
                      </div>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if (!$hasCaravans): ?>
            <div class="p-4 rounded-lg flex flex-col w-full border-[2px] border-gray-300 border-dashed"
                 id="empty_state">
              <!-- <i class="fa fa-signs-post text-3xl text-gray-500 mb-2"></i> -->
              <h5 class="text-xl font-semibold text-gray-900 dark:text-white">O tempo ta passando...</h5>
              <p class="text-gray-600 dark:text-gray-300 text-base mb-2">Que tal seguir o exemplo de Paulo e começar sua jornada agora?</p>
              <div class="flex">
                <a href="caravans.php"
                   class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-green-300 dark:text-green-800 dark:hover:bg-green-400 dark:focus:ring-green-800">
                  Quero ir na próxima
                </a>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">
        <div class="bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-row overflow-auto"
             id="shareapp">
          <div class="appshare min-w-[100px] min-h-full"></div>
          <div class="p-4 flex flex-col">
            <!-- <h5 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Leve o céu no seu bolso?”</h5> -->
            <p class="text-gray-600 dark:text-gray-300 text-sm">Salve o "App da Caravana" como atalho <strong>a partir desta tela</strong> e tenha as bênçãos do Senhor sempre à mão!</p>
          </div>
        </div>
        <div class="flex items-start p-4 mb-4 text-sm text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800"
             role="alert">
          <svg class="flex-shrink-0 inline w-4 h-4 me-3"
               aria-hidden="true"
               xmlns="http://www.w3.org/2000/svg"
               fill="currentColor"
               viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
          </svg>
          <span class="sr-only">Info</span>
          <div>
            <p><span class="font-medium  flex flex-col">Tem alguma ideia ou sugestão?</span> Envie suas ideias e ajude-nos a fortalecer nosso trabalho no reino!</p>
            <a href="mailto:codyzzd@gmail.com?subject=CaravanaCelestial"
               class="inline-flex font-medium items-center text-blue-700 hover:underline mt-2">
              Mandar e-mail
              <svg class="w-3 h-3 ms-2.5 rtl:rotate-[270deg]"
                   aria-hidden="true"
                   xmlns="http://www.w3.org/2000/svg"
                   fill="none"
                   viewBox="0 0 18 18">
                <path stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 11v4.833A1.166 1.166 0 0 1 13.833 17H2.167A1.167 1.167 0 0 1 1 15.833V4.167A1.166 1.166 0 0 1 2.167 3h4.618m4.447-2H17v5.768M9.111 8.889l7.778-7.778" />
              </svg>
            </a>
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