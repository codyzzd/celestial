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
    <meta name="viewport"
          content="width=device-width, initial-scale=1" />
    <title>Caravana</title>

    <?php
    require_once ROOT_PATH . '/resources/head_tailwind.php';
    require_once ROOT_PATH . '/resources/head_flowbite.php';
    require_once ROOT_PATH . '/resources/head_fontawesome.php';
    require_once ROOT_PATH . '/resources/head_jquery.php';
    ?>

  </head>

  <body class="bg-gray-100">

    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="flex flex-col h-dvh p-4 items-center justify-end md:justify-center">

      <!-- caixa -->
      <div class="p-4 bg-white rounded-lg shadow  flex flex-col gap-4 w-full sm:max-w-md">
        <form class="flex flex-col gap-4"
              id="signup"
              autocomplete="on">

          <div>
            <label for="name"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
            <input type="text"
                   id="name"
                   name="name"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   placeholder="ex: Bruno"
                   autocomplete="given-name"
                   required />
          </div>
          <!-- <div>
            <label for="church"
                   data-tooltip-target="tooltip-animation"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Número de Membro</label>
            <input type="text"
                   id="id_church"
                   name="id_church"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   placeholder="ex: 123-1234-1234"
                   autocomplete="nrm"
                   required />
          </div> -->

          <div>
            <label for="email"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   placeholder="ex: bruno@email.com"
                   autocomplete="off"
                   required />
          </div>

          <div>
            <label for="password"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Senha</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   placeholder="•••••••••"
                   autocomplete="new-password"
                   required />
          </div>

          <button type="submit"
                  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5  dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full ">Criar Conta</button>

          <p class="text-sm font-light text-gray-500 dark:text-gray-400">
            Já tem conta? <a href="login.php"
               class="font-medium text-blue-600 hover:underline dark:text-primary-500">Fazer Login</a>
          </p>

        </form>
      </div>

    </section>

    <?php //require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>

    <script>
      $(document).ready(function () {
        // Caminho da API passado pelo PHP
        var apiPath = "<?php echo $apiPath; ?>";

        // Mascarar campo 'cod' dentro do formulário com id 'ward_add'
        // $('#id_church').mask('000-0000-0000');

        // Criar participante
        $("#signup").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente
          var formData = $(this).serialize(); // Serializar os campos do formulário

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData + "&indicador=user_add", // Incluir os campos serializados e o indicador
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#signup")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                } else if (jsonResponse.status === "error") {
                  toast(jsonResponse.status, jsonResponse.msg);
                }
              } catch (e) {
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        });
      });
    </script>
  </body>

</html>