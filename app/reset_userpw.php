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

// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();
// echo $user_id;
// Guarda a role do usuário
$user_role = checkUserRole($user_id, ['stake_lider', 'ward_lider']);

// Pega profile
// $profile = getProfile($user_id);
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
    <title>Resetar Senha de outro Usuário - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <style>
      .relative-container {
        position: relative;
      }

      #email {
        width: 100%;
        /* Garante que o input ocupa toda a largura do contêiner */
      }

      #dropdown {
        position: absolute;
        /*top: 100%;*/
        /* Posiciona o dropdown logo abaixo do input */
        left: 0;
        width: 100%;
        /* Garante que o dropdown tenha a mesma largura do input */
        box-sizing: border-box;
        /* Inclui padding e bordas na largura total */
      }
    </style>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <!-- header -->
      <div class="flex flex-col mb-4 gap-4">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Resetar Senha de outro Usuário</h1>
          <p class="text-gray-500">Defina uma nova senha para o usuário, garantindo acesso seguro conforme solicitado.</p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow  flex flex-col  gap-2 w-full  relative-container">
          <form class="grid gap-4 grid-cols-2"
                id="newpw">
            <div class="relative col-span-2">
              <label for="email"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
              <input type="text"
                     id="email"
                     name="email"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                     placeholder="ex: bruno@email.com"
                     required />
              <input type="hidden"
                     id="user_id"
                     name="user_id">
              <div id="dropdown"
                   class="absolute left-0 z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-full dark:bg-gray-700">
                <ul id="dropdown-menu"
                    class="py-2 text-sm text-gray-700 dark:text-gray-200"></ul>
              </div>
            </div>
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
                    class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Trocar Senha</button>
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

        // Caminho da API passado pelo PHP
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";
        var roleSlug = "<?php echo $user_role; ?>";

        // Dropdown e campos relacionados
        const $input = $('#email');
        const $dropdown = $('#dropdown');
        const $dropdownMenu = $('#dropdown-menu');
        const $userId = $('#user_id'); // Campo oculto para armazenar o data-id
        let timeout;

        $input.on('input', function () {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            const query = $input.val();
            if (query.length < 2) {
              $dropdown.addClass('hidden');
              return;
            }

            $.ajax({
              url: '../resources/fetch_users.php',
              data: { term: query, role_slug: roleSlug, },
              dataType: 'json',
              success: function (data) {
                $dropdownMenu.empty();
                data.forEach(item => {
                  const $li = $('<li>');
                  $li.html(`<a href="#" data-id="${item.id}" class="block px-4 py-2 hover:bg-gray-100">${item.email} - ${item.name}</a>`);
                  $dropdownMenu.append($li);
                });
                $dropdown.toggleClass('hidden', data.length === 0);
              },
              error: function (xhr, status, error) {
                console.error('Error fetching data:', error);
              }
            });
          }, 300); // Timeout para evitar chamadas excessivas
        });

        $dropdownMenu.on('click', 'a', function (event) {
          event.preventDefault();
          $input.val($(this).text());
          $userId.val($(this).data('id')); // Atualizar o campo oculto com o data-id
          $dropdown.addClass('hidden');
        });

        $(document).on('click', function (event) {
          if (!$input.is(event.target) && !$dropdown.is(event.target) && !$dropdown.has(event.target).length) {
            $dropdown.addClass('hidden');
          }
        });

        // Trocar a senha do usuário
        $("#newpw").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&indicador=user_newpw";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#newpw")[0].reset(); // Reseta o formulário
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

      });

    </script>
  </body>

</html>