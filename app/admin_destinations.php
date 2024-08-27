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
// $stake_id = getStake($user_id);
// echo $stake_id;
// Guarda a role do usuário
$user_role = checkUserRole($user_id, 'admin');

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
    <title>Veículos - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <div class="fixed end-4 bottom-24 group md:hidden">
        <button type="button"
                id="destination_add_modal_fob"
                data-modal-toggle="destination_add_modal"
                data-modal-target="destination_add_modal"
                class="flex items-center justify-center text-white bg-purple-700 rounded-full w-14 h-14 hover:bg-purple-800 dark:bg-purple-600 dark:hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 focus:outline-none dark:focus:ring-purple-800">
          <i class="fa fa-plus transition-transform text-2xl"></i>
          <span class="sr-only">Open actions menu</span>
        </button>
      </div>
      <!-- header -->
      <div class="flex flex-col mb-4 gap-4">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Destinos</h1>
          <p class="text-gray-500">Gerencie os destinos possiveis das caravanas.</p>
        </div>
        <button type="button"
                data-modal-toggle="destination_add_modal"
                data-modal-target="destination_add_modal"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full hidden md:block">Adicionar Destino</button>
      </div>
      <div class="flex flex-col gap-4">
        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y overflow-auto"
             id="vehicle_list_items">
        </div>
      </div>
      <!-- modal add -->
      <div id="destination_add_modal"
           data-modal-placement="bottom-center"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-lg max-h-full">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5  rounded-t dark:border-gray-600 border-b">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Adicionar Destino
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="destination_add_modal">
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
                  id="destination_add">
              <div class="grid gap-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900">Nome</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         required
                         placeholder="ex: Templo de Curitiba"
                         autocomplete="off">
                </div>
                <div class="col-span-2 overflow-auto">
                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                         for="file_input">Upload file</label>
                  <input class="w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 overflow-auto"
                         id="file_input"
                         name="file_upload"
                         type="file">
                </div>
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-toggle="destination_add_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                  Adicionar
                </button>
              </div>
            </form>
          </div>
        </div>
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

        // Adiciona um ouvinte de eventos para o documento
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para mostrar o modal
          const showModalTarget = event.target.closest('[data-modal-toggle="vehicle_edit_modal"]');

          if (showModalTarget) {
            const vehicleId = showModalTarget.getAttribute('data-id');

            $.ajax({
              type: "POST",
              url: apiPath,
              data: {
                vehicle_id: vehicleId,
                indicador: 'vehicle_get'
              },

              success: function (response) {
                // Assumindo que passengerData é um array
                const vehicle = JSON.parse(response);

                $('#vehicle_edit #name').val(vehicle.name || '');
                $('#vehicle_edit #capacity').val(vehicle.capacity || '');
                $('#vehicle_edit #obs').val(vehicle.obs || '');
                $('#vehicle_edit #id').val(vehicle.id || '');

                // Verifique se `vehicle.seat_map` é uma string JSON e faça o parse se necessário
                let seatMap = vehicle.seat_map;
                if (typeof seatMap === 'string') {
                  seatMap = JSON.parse(seatMap);
                }

                // Carrega o mapa dos assentos usando a propriedade correta
                loadSeatMap(seatMap || []);

                const passengerEditModal = new Modal(document.getElementById('vehicle_edit_modal'));
                passengerEditModal.show();
              }
            });

          }

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para ocultar o modal
          const hideModalTarget = event.target.closest('[data-modal-hide="vehicle_edit_modal"]');
          if (hideModalTarget) {
            // Inicializa e oculta o modal
            const passengerEditModal = new Modal(document.getElementById('vehicle_edit_modal'));
            passengerEditModal.hide();
          }
        });

        // Adicionar destino
        $("#destination_add").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Criar um objeto FormData para incluir arquivos e dados do formulário
          var formData = new FormData(this);

          // Adicionar a variável extra
          formData.append('indicador', 'destination_add');

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            contentType: false, // Importante para enviar arquivos
            processData: false, // Importante para enviar arquivos
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#destination_add")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // Fechar o modal diretamente
                  $('[data-modal-hide="destination_add_modal"]').trigger('click');
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

        // Salvar passenger
        $("#vehicle_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=vehicle_edit";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#vehicle_edit")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // updatePassengersList(true); // Se necessário, descomente esta linha para atualizar a lista
                  // Fechar o modal diretamente
                  $("#close_edit").trigger('click');

                  // updatePeopleList();
                  updateVehicleList('not_deleted');
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


        // Função para atualizar a lista de veículos
        function updateVehicleList(status = 'all') {
          // Criar o objeto data com os parâmetros padrão
          let data = {
            user_id: userId,
            indicador: "vehicle_list",
            id_stake: stakeId, // Indicador para buscar os veículos
            status: status // Incluir o status diretamente no objeto data
          };

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data, // Passa o objeto data no request
            success: function (response) {
              try {
                var vehiclejson = JSON.parse(response);
                var vehicleList = $("#vehicle_list_items"); // ID do container para a lista de veículos
                var emptyState = $("#empty_state"); // ID do container para a mensagem de estado vazio

                // Limpar o conteúdo atual dos containers
                vehicleList.empty();

                // Adicionar novos vehicle_items aos containers com base na relação
                vehiclejson.forEach(function (vehicle) {
                  var vehicleItem = `
                  <a href="stake_vehicle.php?id=${vehicle.id}"
                  class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex items-center justify-between">
                  <span class="text-left truncate  justify-between w-full"> ${vehicle.name}</span>
                  <div class="flex flex-row gap-2 items-center">
                  <span class="bg-purple-100 text-purple-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded w-10 h-fit text-center">${vehicle.capacity}</span>
                  <i class="fa fa-chevron-right text-lg text-gray-500"></i></div>

                  </a>
                  `;
                  vehicleList.append(vehicleItem);
                });

                // Exibir ou ocultar os containers e a mensagem de estado vazio
                if (vehicleList.children().length === 0) {
                  vehicleList.addClass('hidden'); // Oculta o container de veículos
                  emptyState.removeClass('hidden'); // Exibe a mensagem de estado vazio
                } else {
                  vehicleList.removeClass('hidden'); // Exibe o container de veículos
                  emptyState.addClass('hidden'); // Oculta a mensagem de estado vazio
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
    </script>
  </body>

</html>