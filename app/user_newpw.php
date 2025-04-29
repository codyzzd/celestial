<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
$apiPath = "../resources/api_user.php";
?>
<?php
session_start(); // Inicia ou continua a sessão atual

// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();
// echo $user_id;
// Guarda a role do usuário
$user_role = checkUserRole($user_id);

// Pega profile
$profile = getProfile($user_id);
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
    <title>Resetar Senha - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <!-- header -->
      <div class="flex flex-col mb-4 gap-4">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Resetar Senha</h1>
          <p class="text-gray-500">Estabeleça uma nova senha para garantir o acesso seguro e personalizado.</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow  flex flex-col  gap-2 w-full  relative-container">
          <form class="grid gap-4 grid-cols-2"
                id="newpw">
            <div class="col-span-2">
              <label for="password"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white col-span-2">Nova Senha</label>
              <div class="relative col-span-2">
                <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 cursor-pointer"
                     id="eye">
                  <i class="fa fa-eye-slash text-base text-gray-500 fa-fw"
                     id="togglePassword"></i>
                </div>
                <input type="password"
                       id="password"
                       name="password"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pe-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                       placeholder="•••••••••"
                       autocomplete="current-password"
                       required>
              </div>
            </div>
            <button type="submit"
                    class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Ataulizar Senha</button>
          </form>
        </div>
      </div>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
    <script>
      $(document).ready(function () {

        // função de trocar input
        function togglePasswordVisibility(triggerSelector, inputSelector) {
          $(triggerSelector).on('click', function () {
            const passwordInput = $(inputSelector);
            const icon = $(this).find('i');
            const isPassword = passwordInput.attr('type') === 'password';

            // Toggle input type
            passwordInput.attr('type', isPassword ? 'text' : 'password');

            // Toggle icon class
            icon.toggleClass('fa-eye fa-eye-slash');
          });
        }

        // Call the function with appropriate selectors
        togglePasswordVisibility('#eye', '#password');


        // Caminho da API passado pelo PHP
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // Determinar nova senha no banco do usuário
        $("#newpw").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=user_newpw";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Incluir os campos serializados e o indicador
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#newpw")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // Atrasar o redirecionamento para a página de login por 2 segundos (2000 milissegundos)
                  setTimeout(function () {
                    window.location.href = "profile.php";
                  }, 3000);
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