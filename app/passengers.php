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
// Guarda a role do usuário
$user_role = checkUserRole($user_id);
// Buscar as alas disponíveis para o usuário
$wards = getWardsByUserId($user_id);
$documents = getDocuments();
$sexs = getSexs();
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

    <section class="max-w-screen-lg container mx-auto p-4 pb-20">
      <div class="fixed end-4 bottom-20 group">

        <button type="button"
                data-drawer-target="passengers_add_modal"
                data-drawer-show="passengers_add_modal"
                data-drawer-placement="right"
                aria-controls="passengers_add_modal"
                class="flex items-center justify-center text-white bg-blue-700 rounded-full w-14 h-14 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:focus:ring-blue-800">
          <i class="fa fa-plus transition-transform  text-2xl"></i>

          <span class="sr-only">Open actions menu</span>
        </button>
      </div>

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Fámilia e Amigos</h1>
          <p class="text-gray-500">Administre os passageiros da sua família e amigos de forma simples e eficiente.</p>
        </div>
        <!-- <button type="button"
                data-modal-target="criar-passageiro"
                data-modal-toggle="criar-passageiro"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full md:w-fit">Adicionar Passageiro</button> -->
      </div>

      <!-- tabela -->
      <div class="bg-white rounded-lg overflow-hidden border-gray-200 border-[1px]">
        <div class="divide-y divide-gray-200 [&_h2]:font-bold [&_p]:text-gray-600 [&>_div]:p-4 [&>_div]:flex [&>_div]:flex-row [&>_div]:justify-between [&>_div]:items-center ">

          <div class="">
            <div>
              <h2>Bruno Gonçalves</h2>

            </div>
            <i class="fa-solid fa-chevron-right"></i>
          </div>

          <div class="">
            <div>
              <h2>Jessica Moreira</h2>

            </div>
            <i class="fa-solid fa-chevron-right"></i>
          </div>

        </div>
      </div>

      <!-- drawer component -->
      <div id="passengers_add_modal"
           class="fixed top-0 right-0 z-40 h-screen p-4 overflow-y-auto transition-transform translate-x-full bg-white w-80 dark:bg-gray-800"
           tabindex="-1"
           aria-labelledby="drawer-right-label">
        <h5 id="drawer-label"
            class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">Adicionar Pessoa</h5>
        <button type="button"
                data-drawer-dismiss="passengers_add_modal"
                aria-controls="passengers_add_modal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
          <svg aria-hidden="true"
               class="w-5 h-5"
               fill="currentColor"
               viewBox="0 0 20 20"
               xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"></path>
          </svg>
          <span class="sr-only">Close menu</span>
        </button>
        <form id="passenger_add">
          <div class="space-y-4">

            <div class="col-span-2">
              <label for="name"
                     class="block mb-2 text-sm font-medium text-gray-900">Nome Completo</label>
              <input type="text"
                     id="name"
                     name="name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                     placeholder="ex: Bruno da Silva Gonçalves"
                     required
                     autocomplete="off">
            </div>

            <div class="col-span-2">
              <label for="nasc_date"
                     class="block mb-2 text-sm font-medium text-gray-900">Data de Nascimento</label>
              <input type="text"
                     id="nasc_date"
                     name="nasc_date"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                     placeholder="dd/mm/aaaa"
                     required
                     autocomplete="off">
            </div>

            <div class="col-span-2">
              <label for="inline-radio-group"
                     class="block mb-2 text-sm font-medium text-gray-900">Gênero</label>
              <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                <?php
                // Iterar sobre os sexs
                foreach ($sexs as $sex):
                  // Usar sintaxe ternária para definir ícone e cor
                  $icon = ($sex['slug'] == 'masc') ? 'fa-mars' : (($sex['slug'] == 'femi') ? 'fa-venus' : '');
                  $color = ($sex['slug'] == 'masc') ? 'text-blue-700' : (($sex['slug'] == 'femi') ? 'text-pink-700' : '');
                  ?>
                  <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                    <div class="flex items-center ps-3">
                      <input id="horizontal-list-radio-<?php echo $sex['slug']; ?>"
                             type="radio"
                             value="<?php echo $sex['id']; ?>"
                             name="sex"
                             class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2"
                             required>
                      <label for="horizontal-list-radio-<?php echo $sex['slug']; ?>"
                             class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                        <i class="fa <?php echo $icon; ?> fa-fw <?php echo $color; ?>"></i>
                        <?php echo $sex['name']; ?>
                      </label>
                    </div>
                  </li>
                <?php endforeach; ?>

              </ul>
            </div>

            <div class="col-span-2">
              <label for="id_ward"
                     class="block mb-2 text-sm font-medium text-gray-900">Qual ala pertence?</label>
              <select id="id_ward"
                      name="id_ward"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                      required>
                <option value=""
                        selected>Selecione...</option>
                <?php foreach ($wards as $ward): ?>
                  <option value="<?php echo $ward['id']; ?>"><?php echo $ward['name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-span-2">
              <label for="id_document"
                     class="block mb-2 text-sm font-medium text-gray-900">Tipo de Documento</label>
              <select id="id_document"
                      name="id_document"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                      required>
                <option value=""
                        selected>Selecione...</option>
                <?php foreach ($documents as $document): ?>
                  <option value="<?php echo $document['id']; ?>"><?php echo $document['name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-span-2">
              <label for="document"
                     class="block mb-2 text-sm font-medium text-gray-900">Documento</label>
              <input type="text"
                     id="document"
                     name="document"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                     required
                     autocomplete="off">
            </div>

            <div class="col-span-2">
              <label for="fever_date"
                     class="block mb-2 text-sm font-medium text-gray-900">Data da Vacina da Febre Amarela</label>
              <input type="text"
                     id="fever_date"
                     name="fever_date"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                     placeholder="dd/mm/aaaa"
                     autocomplete="off">
            </div>

            <div class="col-span-2">
              <label for="obs"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
              <textarea id="obs"
                        name="obs"
                        rows="4"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
            </div>

          </div>
          <div class="bottom-0 left-0 flex justify-end w-full pb-4 mt-4 space-x-4 sm:absolute">
            <button type="button"
                    data-drawer-toggle="passengers_add_modal"
                    class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
            <button type="submit"
                    class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-white text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
              Adicionar Pessoa
            </button>
          </div>
        </form>
      </div>

    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>

    <script>
      $(document).ready(function () {
        // Caminho da API para passenger_add
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // Adicionar passenger
        $("#passenger_add").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=passenger_add";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#passenger_add")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // updatePassengersList(true); // Se necessário, descomente esta linha para atualizar a lista
                  $('[data-drawer-toggle="passengers_add_modal"]').click(); // Fechar modal
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