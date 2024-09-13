<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//$apiPath = ROOT_PATH . '/resources/api.php';
$apiPath = "../resources/api.php";
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
    <title>Como resetar senha? - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="flex flex-col h-dvh p-4 items-center justify-center max-w-lg container mx-auto p-4 pb-20">
      <!-- caixa -->
      <div class="p-4 rounded-lg flex flex-col w-full border-[2px] border-gray-300 border-dashed"
           id="empty_state">
        <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Precisa redefinir sua senha?</h5>
        <p class="text-gray-600 dark:text-gray-300 text-base mb-2">
          Para redefinir sua senha, entre em contato com os líderes responsáveis da sua ala ou estaca. Eles podem ajudá-lo a configurar uma nova senha com segurança.
        </p>
        <div class="flex">
          <a href="login.php"
             class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2 text-center">
            Voltar para o login
          </a>
        </div>
      </div>
    </section>
    <?php //require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>