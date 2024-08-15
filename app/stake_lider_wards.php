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
// $conn = getDatabaseConnection();

// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();

// Guarda a role do usuário
$user_role = checkUserRole($user_id, 'stake_lider');

// Obter as wards associadas ao usuário
// $wards =  getWardsByUserId($user_id);
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
                data-modal-toggle="ward_add_modal"
                data-modal-target="ward_add_modal"
                class="flex items-center justify-center text-white bg-blue-700 rounded-full w-14 h-14 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:focus:ring-blue-800">
          <i class="fa fa-plus transition-transform  text-2xl"></i>

          <span class="sr-only">Open actions menu</span>
        </button>
      </div>

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Alas da Estaca</h1>
          <p class="text-gray-500">Adicione novas alas e edite para melhorar a organização.</p>
        </div>
        <!-- <button type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 w-full md:w-fit">Criar</button> -->
      </div>

      <!-- modal add -->
      <div id="ward_add_modal"
           data-modal-placement="bottom-center"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5  rounded-t dark:border-gray-600 border-b">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Adicionar Ala
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-toggle="ward_add_modal">
                <svg class="w-3 h-3"
                     aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 14 14">
                  <path stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Fechar Modal</span>
              </button>
            </div>
            <!-- Modal body -->
            <form class=""
                  id="ward_add">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">

                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome da Ala</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                         placeholder="ex: Cascavel"
                         required
                         autocomplete="off" />
                </div>
                <div class="col-span-2">
                  <label for="cod"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código da Ala</label>
                  <input type="text"
                         id="cod"
                         name="cod"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                         placeholder="ex: 12345678"
                         required
                         autocomplete="off" />
                </div>

              </div>
              <!-- <div class="flex justify-end gap-3">

              </div> -->

              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-toggle="ward_add_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-white text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                  Adicionar Ala
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- modal edit -->
      <div id="ward_edit_modal"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                Editar Ala
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="ward_edit_modal">
                <svg class="w-3 h-3"
                     aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 14 14">
                  <path stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
              </button>
            </div>
            <!-- Modal body -->
            <form class=""
                  id="ward_edit">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome da Ala</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                         placeholder="ex: Cascavel"
                         required
                         autocomplete="off" />
                </div>
                <div class="col-span-2">
                  <label for="cod"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código da Ala</label>
                  <input type="text"
                         id="cod"
                         name="cod"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                         placeholder="ex: 12345678"
                         required
                         autocomplete="off" />

                  <input type="hidden"
                         id="id"
                         name="id" />
                </div>
              </div>
              <!-- Modal footer -->
              <div class="flex items-center justify-end gap-3 p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="ward_edit_modal"
                        id="ward_archive"
                        type="button"
                        class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center  dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">Arquivar</button>
                <button data-modal-hide="ward_edit_modal"
                        type="button"
                        class="py-2.5 px-5  text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Salvar</button>

              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-4">

        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y"
             id="ward_list">

        </div>

        <!-- empty state -->
        <div class="p-4  rounded-lg   flex flex-col   w-full sm:max-w-md border-[2px] border-gray-300 border-dashed"
             id="empty_state">
          <i class="fa fa-circle-question text-3xl text-gray-500 mb-2"></i>

          <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Cade as alas?</h5>
          <p class="text-gray-600 dark:text-gray-300 text-base">Vamos lá, não vai deixar as pedras fazerem o trabalho, vai? Comece a cadastrar suas alas e vamos juntos fortalecer o reino de Deus.</p>
        </div>

      </div>

    </section>

    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>

    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
    <script>
      $(document).ready(function () {
        // Caminho da API para stake_edit
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // Mascarar campo 'cod' dentro do formulário com id 'ward_add'
        $('#ward_add #cod').mask('0000000000');
        $('#ward_edit #cod').mask('0000000000');

        //pra poder ouvir os botoes gerados pela lista
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão com data-modal-toggle
          let target = event.target.closest('[data-modal-toggle="ward_edit_modal"]');

          if (target) {
            // Inicializa e exibe o modal
            const wardEditModal = new Modal(document.getElementById('ward_edit_modal'));

            // Obtém os valores dos data-attributes
            const wardName = target.getAttribute('data-name');
            const wardCod = target.getAttribute('data-cod');
            const wardId = target.getAttribute('data-id');

            // Seleciona os inputs dentro do form com ID "ward_edit"
            const form = document.getElementById('ward_edit');
            const inputName = form.querySelector('input[name="name"]');
            const inputCod = form.querySelector('input[name="cod"]');
            const inputId = form.querySelector('input[name="id"]');

            // Define os valores dos inputs
            inputName.value = wardName;
            inputCod.value = wardCod;
            inputId.value = wardId;

            wardEditModal.show();
          }

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão com data-modal-toggle
          let target2 = event.target.closest('[data-modal-hide="ward_edit_modal"]');

          if (target2) {
            // Inicializa e exibe o modal
            const wardEditModal = new Modal(document.getElementById('ward_edit_modal'));
            wardEditModal.hide();
          }

          // Verifica se o botão clicado é o de arquivamento
          if (event.target.id === 'ward_archive') {
            // Obtém o ID da ward que será arquivada
            const form = document.getElementById('ward_edit');
            const wardId = form.querySelector('input[name="id"]').value;

            // Serializar os campos do formulário
            var formData = "id=" + encodeURIComponent(wardId) + "&indicador=ward_archive";

            // Realiza a ação de arquivamento via AJAX
            $.ajax({
              url: apiPath,
              type: 'POST',
              data: formData, // Enviar os dados com o indicador e wardId
              success: function (response) {
                try {
                  var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON
                  if (jsonResponse.status === "success") {
                    toast(jsonResponse.status, jsonResponse.msg);
                    const wardEditModal = new Modal(document.getElementById('ward_edit_modal'));

                    if (wardEditModal) {
                      wardEditModal.hide();
                    }

                    updateWardsList(true);
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
          }
        });

        // Função para salvar os dados
        $("#ward_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=ward_edit";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#ward_edit")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  updateWardsList(true);

                  const wardEditModal = new Modal(document.getElementById('ward_edit_modal'));

                  if (wardEditModal) {
                    wardEditModal.hide();
                  }

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


        // Função para atualizar a lista de wards
        function updateWardsList(notDeleted = false) {
          $.ajax({
            type: "POST",
            url: apiPath,
            cache: false, // Desativa o cache
            data: {
              user_id: userId,
              indicador: "ward_list" // Indicador para buscar as wards
            },
            success: function (response) {
              // Verifique o tipo de conteúdo e o código de status aqui
              // console.log('Tipo de conteúdo:', jqXHR.getResponseHeader('Content-Type'));
              // console.log('Código de status HTTP:', jqXHR.status);
              try {
                var wards = JSON.parse(response);
                var container = $("#ward_list"); // ID do container onde os ward_items serão adicionados

                // Limpar o conteúdo atual do container
                container.empty();

                // Filtrar wards baseado no parâmetro opcional
                if (notDeleted) {
                  wards = wards.filter(function (ward) {
                    return ward.deleted_at === null; // Considera apenas aqueles com deleted_at vazio (NULL)
                  });
                }

                if (wards.length === 0) {
                  // Exibe a mensagem de estado vazio se não houver alas
                  $('#empty_state').removeClass('hidden').addClass('block');
                  $('#ward_list').removeClass('block').addClass('hidden');
                } else {
                  // Oculta a mensagem de estado vazio se houver alas
                  $('#empty_state').removeClass('block').addClass('hidden');
                  $('#ward_list').removeClass('hidden').addClass('block');

                  // Adicionar novos ward_items ao container
                  wards.forEach(function (ward) {
                    var wardItem = `
            <button type="button" data-modal-target="ward_edit_modal" data-modal-toggle="ward_edit_modal" data-id="${ward.id}" data-name="${ward.name}" data-cod="${ward.cod}"
            class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:text-blue-700 flex justify-between">
            <span><i class="fa fa-tent text-lg text-gray-500 fa-fw me-2"></i>
            ${ward.name} - ${ward.cod}</span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
            </button>
            `;
                    container.append(wardItem);
                  });
                }
              } catch (e) {
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        }

        //atualizar uma vez que carrega a pagina
        updateWardsList(true);


        // Adicionar ward
        $("#ward_add").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=ward_add";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#ward_add")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  updateWardsList(true);
                  $('[data-modal-toggle="ward_add_modal"]').click();//fechar modal
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