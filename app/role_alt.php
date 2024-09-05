<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
$apiPath = "../resources/api.php";
?>
<?php
// Chamando as funções
require_once ROOT_PATH . '/resources/functions.php';

//checa o id
$id = $_GET['id'] ?? null;
$status = checkRoleAltValidity($id);
// echo $status;
// Redireciona para login.php se o status for 'noid'
if ($status === 'noid') {
  header('Location: login.php');
  exit;
}
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
    <title>Link de Permissão - Caravana Celestial</title>
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
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Link de Permissão</h1>
          <p class="text-gray-500">Receba uma nova permissão no app e amplie seus acessos e funções.</p>
        </div>
      </div>
      <!-- tabela -->
      <div class="flex flex-col gap-4">
        <?php if ($status === 'valid'): ?>
          <div class="p-4 bg-white rounded-lg shadow  flex flex-col  gap-2 w-full relative-container">
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
              <ul class="flex flex-wrap -mb-px text-sm font-medium text-center"
                  id="default-tab"
                  data-tabs-toggle="#default-tab-content"
                  role="tablist"
                  data-tabs-active-classes="text-purple-600 hover:text-purple-600 dark:text-purple-500 dark:hover:text-purple-500 border-purple-600 dark:border-purple-500"
                  data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300">
                <li class="me-2"
                    role="presentation">
                  <button class="inline-block p-4 border-b-2 rounded-t-lg"
                          id="exist-tab"
                          data-tabs-target="#exist"
                          type="button"
                          role="tab"
                          aria-controls="exist"
                          aria-selected="false">Entrar</button>
                </li>
                <li class="me-2"
                    role="presentation">
                  <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                          id="new-tab"
                          data-tabs-target="#new"
                          type="button"
                          role="tab"
                          aria-controls="new"
                          aria-selected="false">Criar Conta</button>
                </li>
              </ul>
            </div>
            <div id="default-tab-content">
              <div class="hidden "
                   id="exist"
                   role="tabpanel"
                   aria-labelledby="exist-tab">
                <form class="grid gap-4 grid-cols-2"
                      id="role_alt_exist">
                  <div class="col-span-2">
                    <label for="email"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                           placeholder="ex: bruno@email.com"
                           autocomplete="off"
                           required />
                  </div>
                  <div class="col-span-2">
                    <label for="password"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Senha</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                           placeholder="•••••••••"
                           autocomplete="current-password"
                           required />
                  </div>
                  <button type="submit"
                          class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Aceitar Nova Permissão</button>
                </form>
              </div>
              <div class="hidden "
                   id="new"
                   role="tabpanel"
                   aria-labelledby="new-tab">
                <form class="grid gap-4 grid-cols-2"
                      id="role_alt_new">
                  <div class="col-span-2">
                    <label for="name"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                           placeholder="ex: Bruno"
                           autocomplete="given-name"
                           required />
                  </div>
                  <div class="col-span-2">
                    <label for="email"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                           placeholder="ex: bruno@email.com"
                           autocomplete="off"
                           required />
                  </div>
                  <div class="col-span-2">
                    <label for="password_new"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white col-span-2">Senha</label>
                    <div class="relative col-span-2">
                      <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 cursor-pointer"
                           id="eye">
                        <i class="fa fa-eye-slash text-base text-gray-500 fa-fw"
                           id="togglePassword"></i>
                      </div>
                      <input type="password"
                             id="password_new"
                             name="password"
                             class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pe-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                             placeholder="•••••••••"
                             autocomplete="current-password"
                             required>
                    </div>
                  </div>
                  <button type="submit"
                          class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Criar conta e Aceitar Nova Permissão</button>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($status === 'expired'): ?>
          <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed "
               id="expired">
            <i class="fa fa-ban text-3xl text-gray-500 mb-2"></i>
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Link Expirado!</h5>
            <p class="text-gray-600 dark:text-gray-300 text-base">Este link já expirou. Por favor, solicite um novo link para continuar.</p>
          </div>
        <?php endif; ?>
        <?php if ($status === 'wrongid'): ?>
          <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed "
               id="wrongid">
            <i class="fa fa-ban text-3xl text-gray-500 mb-2"></i>
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Link Inválido!</h5>
            <p class="text-gray-600 dark:text-gray-300 text-base">O link fornecido não é válido ou não existe. Verifique o endereço e tente novamente.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
    <script>
      $(document).ready(function () {
        // Caminho da API para passenger_add
        var apiPath = "<?php echo $apiPath; ?>";

        var perm = "<?php echo $id; ?>";

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
        togglePasswordVisibility('#eye', '#password_new');

        // checar e pegar a permissao para o usuario
        $("#role_alt_exist").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&indicador=role_alt_edit_exist" + "&perm=" + encodeURIComponent(perm);

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "loading") {
                  toast(jsonResponse.status, jsonResponse.msg);
                  // Configura o timeout para redirecionar o usuário
                  setTimeout(function () {
                    window.location.href = 'login.php';
                  }, 2000); // 2000 milissegundos = 2 segundos
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

        // criar conta e pegar permissao para o usuario
        $("#role_alt_new").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&indicador=role_alt_edit_new" + "&perm=" + encodeURIComponent(perm);

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "loading") {
                  toast(jsonResponse.status, jsonResponse.msg);
                  // Configura o timeout para redirecionar o usuário
                  setTimeout(function () {
                    window.location.href = 'login.php';
                  }, 2000); // 2000 milissegundos = 2 segundos
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