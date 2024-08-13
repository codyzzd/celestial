<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
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

    <section class="max-w-screen-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Estacas</h1>
          <p class="text-gray-500">Administre o cadastro de estacas.</p>
        </div>
        <button type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full md:w-fit">Criar</button>
      </div>

      <!-- tabela -->

      <div class="bg-white rounded-lg overflow-hidden border-gray-200 border-[1px]">
        <div class="divide-y divide-gray-200 [&_h2]:font-bold [&_p]:text-gray-600 [&>_div]:p-4 [&>_div]:flex [&>_div]:flex-row [&>_div]:justify-between [&>_div]:items-center ">

          <div class="">
            <div>
              <p><strong>Foz do Iguaçu</strong> - (454564)</p>
            </div>
            <i class="fa-solid fa-chevron-right"></i>
          </div>

          <div class="">
            <div>
              <p><strong>Cascavel</strong> - (454564)</p>
            </div>
            <i class="fa-solid fa-chevron-right"></i>
          </div>

        </div>
      </div>

    </section>

    <?php require_once ROOT_PATH . '/section/advanced_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>