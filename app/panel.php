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
?>

<!DOCTYPE html>
<html lang="pt-BR">

  <head>
    <meta charset="utf-8" />

    <?php
    require_once ROOT_PATH . '/resources/favicon.png';
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

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Painel</h1>
          <p class="text-gray-500">Veja suas estatísticas, confira o ranking e planeje suas próximas caravanas facilmente.</p>

        </div>
        <!-- <button type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full md:w-fit">Criar</button> -->
      </div>

      <div class="flex flex-col gap-4">

        <div class="bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700"
             id="shareapp">
          <div class="appshare rounded-t-lg"></div>
          <!--
          <img class="rounded-t-lg"
               src="/docs/images/blog/image-1.jpg"
               alt="" /> -->

          <div class="p-5">
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Leve o céu no seu bolso?”</h5>
            <p class="text-gray-600 dark:text-gray-300 text-base">Salve o "App da Caravana" como atalho <strong>a partir desta tela</strong> e tenha as bênçãos do Senhor sempre à mão!</p>
            <!-- <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Quer ter o céu ao alcance de um toque?</h5>

            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Salve o "App da Caravana" como atalho e tenha as bênçãos do Senhor sempre à mão!</p> -->

          </div>
        </div>

        <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed">
          <i class="fa fa-message text-3xl text-gray-500"></i>

          <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Tem alguma ideia ou sugestão?</h5>
          <p class="text-gray-600 dark:text-gray-300 text-base">Envie suas ideias e ajude-nos a fortalecer nosso trabalho no reino!</p>
          <a href="mailto:codyzzd@gmail.com?subject=CaravanaCelestial"
             class="inline-flex font-medium items-center text-blue-600 hover:underline">
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
    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>

  </body>

</html>