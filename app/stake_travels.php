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

// Guarda o id da estaca
$user_stake = checkStake($user_id);

// Obter as wards associadas ao usuário
// $wards =  getWardsByUserId($user_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">

  <head>
    <meta charset="utf-8" />

    <?php
    require_once ROOT_PATH . '/resources/functions.php';
    require_once ROOT_PATH . '/resources/head_tailwind.php';
    require_once ROOT_PATH . '/resources/head_flowbite.php';
    require_once ROOT_PATH . '/resources/head_fontawesome.php';
    require_once ROOT_PATH . '/resources/head_jquery.php';
    ?>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0<?php if (isMobile())
            echo ', user-scalable=no'; ?>">
    <title>Alas - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">
      <div class="fixed end-4 bottom-24 group md:hidden">

        <button type="button"
                data-modal-toggle="caravan_add_modal"
                data-modal-target="caravan_add_modal"
                class="flex items-center justify-center text-white bg-purple-700 rounded-full w-14 h-14 hover:bg-purple-800 dark:bg-purple-600 dark:hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 focus:outline-none dark:focus:ring-purple-800">
          <i class="fa fa-plus transition-transform  text-2xl"></i>

          <span class="sr-only">Open actions menu</span>
        </button>
      </div>

      <!-- header -->
      <div class="flex flex-col mb-4 gap-4 ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Caravanas da Estaca</h1>
          <p class="text-gray-500">Adicione e faça a gestão das caravanas da estaca.</p>
        </div>
        <button type="button"
                data-modal-toggle="caravan_add_modal"
                data-modal-target="caravan_add_modal"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full hidden md:block">Adicionar Caravana</button>
      </div>

      <!-- modal add -->
      <div id="caravan_add_modal"
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
                Adicionar Caravana
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="caravan_add_modal">
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
                  id="caravan_add">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">

                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome da Caravana</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                         placeholder="ex: Agosto 15 2024"
                         required
                         autocomplete="off" />
                </div>
                <div class="">
                  <label for="start_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data de Partida</label>
                  <input type="text"
                         id="start_date"
                         name="start_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         required
                         autocomplete="off">
                </div>
                <div class="">
                  <label for="start_time"
                         class="block mb-2 text-sm font-medium text-gray-900">Horário de Partida</label>
                  <input type="text"
                         id="start_time"
                         name="start_time"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="00:00"
                         required
                         autocomplete="off">

                </div>
                <div class="">
                  <label for="return_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data de Partida</label>
                  <input type="text"
                         id="return_date"
                         name="return_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         required
                         autocomplete="off">
                </div>
                <div class="">
                  <label for="return_time"
                         class="block mb-2 text-sm font-medium text-gray-900">Horário de Partida</label>
                  <input type="text"
                         id="return_time"
                         name="return_time"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="00:00"
                         required
                         autocomplete="off">

                </div>
                <div class="col-span-2">
                  <label for="obs"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
                  <textarea id="obs"
                            name="obs"
                            rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"></textarea>
                </div>

              </div>

              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-hide="caravan_add_modal"
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

      <!-- modal edit -->
      <div id="ward_edit_modal"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-lg max-h-full">
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
                  id="caravan_edit">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome da Ala</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
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
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
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
                        class="py-2.5 px-5  text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">Salvar</button>

              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-4">
        <div>

          <select id="perpage"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

            <option value="4"
                    selected>4 por página</option>
            <option value="8">8 por página</option>
            <option value="16">16 por página</option>
            <option value="24">24 por página</option>
          </select>
        </div>

        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600  divide-y"
             id="caravan_list">

        </div>

        <div class="flex flex-col items-start">
          <span class="text-sm text-gray-700 "
                id="pagination_text">
            <!-- Mostrando <span class="font-semibold text-gray-900 ">1</span> até <span class="font-semibold text-gray-900 ">10</span> de <span class="font-semibold text-gray-900 ">100</span> -->
          </span>
          <div class="inline-flex mt-2 xs:mt-0">
            <button class="pagination-prev flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-l-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 rounded-r-none"
                    id="pagination-prev">
              <svg class="w-3.5 h-3.5 me-2 rtl:rotate-180"
                   aria-hidden="true"
                   xmlns="http://www.w3.org/2000/svg"
                   fill="none"
                   viewBox="0 0 14 10">
                <path stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 5H1m0 0 4 4M1 5l4-4" />
              </svg>
              Anterior
            </button>
            <button class="pagination-next flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-r-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 rounded-l-none"
                    id="pagination-next">
              Próximo
              <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180"
                   aria-hidden="true"
                   xmlns="http://www.w3.org/2000/svg"
                   fill="none"
                   viewBox="0 0 14 10">
                <path stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M1 5h12m0 0L9 1m4 4L9 9" />
              </svg>
            </button>
          </div>
        </div>

        <!-- empty state -->
        <div class="p-4  rounded-lg   flex flex-col   w-full  border-[2px] border-gray-300 border-dashed hidden"
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

        // Defina o user_id a partir do PHP
        var userStake = "<?php echo $user_stake; ?>";

        // Variáveis de controle de paginação
        var currentPage = 1;
        // const itemsPerPage = 4; // Número de itens por página
        var itemsPerPage = parseInt($('#perpage').val());

        // Mascarar campo 'cod' dentro do formulário com id 'ward_add'
        $('#caravan_add #start_date').mask('00/00/0000');
        $('#caravan_add #start_time').mask('00:00');
        $('#caravan_edit #start_date').mask('00/00/0000');
        $('#caravan_edit #start_time').mask('00:00');

        $('#caravan_add #return_date').mask('00/00/0000');
        $('#caravan_add #return_time').mask('00:00');
        $('#caravan_edit #return_date').mask('00/00/0000');
        $('#caravan_edit #return_time').mask('00:00');

        // Adiciona o evento de clique ao botão usando jQuery
        $('#pagination-prev').on('click', function () {
          changePage(currentPage - 1);
        });

        // Adiciona o evento de clique ao botão usando jQuery
        $('#pagination-next').on('click', function () {
          changePage(currentPage + 1);
        });

        //pra poder ouvir os botoes gerados pela lista
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão com data-modal-hide
          let target = event.target.closest('[data-modal-hide="ward_edit_modal"]');

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

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão com data-modal-hide
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
            // var formData = "id=" + encodeURIComponent(wardId) + "&indicador=ward_archive";

            const formData = {
              id: wardId,
              indicador: 'archive_something',
              bd: 'wards'
            };

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
        function updateCaravansList(notDeleted = false) {
          $.ajax({
            type: "POST",
            url: apiPath,

            data: {
              user_id: userId,
              stake_id: userStake,

              indicador: "caravan_list" // Indicador para buscar as wards
            },
            success: function (response) {
              try {
                var Caravans = JSON.parse(response);
                var container = $("#caravan_list");

                // Limpar o conteúdo atual do container
                container.empty();

                // Filtrar Caravans baseado no parâmetro opcional
                if (notDeleted) {
                  Caravans = Caravans.filter(function (caravan) {
                    return caravan.deleted_at === null;
                  });
                }

                if (Caravans.length === 0) {
                  $('#empty_state').removeClass('hidden').addClass('block');
                  $('#caravan_list').removeClass('block').addClass('hidden');
                } else {
                  $('#empty_state').removeClass('block').addClass('hidden');
                  $('#caravan_list').removeClass('hidden').addClass('block');

                  function formatDate(dateString) {
                    const options = { day: '2-digit', month: 'short', year: 'numeric' };
                    const date = new Date(dateString);
                    return date.toLocaleDateString('pt-BR', options).replace(/de /, '').replace('.', '');
                  }

                  function formatTime(timeString) {
                    return timeString.slice(0, 5);
                  }

                  function displayPage(page) {
                    const startIndex = (page - 1) * itemsPerPage;
                    const endIndex = page * itemsPerPage;
                    const paginatedItems = Caravans.slice(startIndex, endIndex);

                    container.empty();

                    paginatedItems.forEach(function (caravan) {
                      var formattedStartDate = formatDate(caravan.start_date);
                      var formattedStartTime = formatTime(caravan.start_time);
                      var formattedReturnDate = formatDate(caravan.return_date);
                      var formattedReturnTime = formatTime(caravan.return_time);

                      var caravanItem = `
                <button type="button" data-modal-target="caravan_edit_modal" data-modal-toggle="caravan_edit_modal" data-id="${caravan.id}"
                  class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between items-center">
                  <div class="text-left w-full">
                    <p class="mb-2 truncate text-sm">${caravan.name}</p>
                    <div class="flex justify-between items-center">
                      <div class="flex-1 text-left">
                        <p class="text-xl">${formattedStartTime}</p>
                        <p class="text-sm text-gray-500">${formattedStartDate}</p>
                      </div>
                      <div class="mx-2 flex-shrink-0">
                        <i class="fa fa-circle-right text-xl text-gray-500"></i>
                      </div>
                      <div class="flex-1 text-right">
                        <p class="text-xl">${formattedReturnTime}</p>
                        <p class="text-sm text-gray-500">${formattedReturnDate}</p>
                      </div>
                    </div>
                  </div>
                </button>
              `;
                      container.append(caravanItem);
                    });

                    // Atualizar as informações e botões de paginação
                    updatePaginationControls(page, Caravans.length);
                  }

                  displayPage(currentPage);
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

        function updateItemsPerPage() {
          itemsPerPage = parseInt($('#perpage').val());
          // console.log('Items per page:', itemsPerPage); // Apenas para depuração
          // Você pode chamar outras funções aqui para atualizar a lista com base no novo valor
          updateCaravansList(); // Exemplo de chamada de função
        }

        // Configurar o evento change no select
        $('#perpage').on('change', function () {
          updateItemsPerPage();
        });

        // updateItemsPerPage();

        function updatePaginationControls(page, totalItems) {
          const totalPages = Math.ceil(totalItems / itemsPerPage);

          // Atualizar texto de paginação
          const startEntry = (page - 1) * itemsPerPage + 1;
          const endEntry = Math.min(page * itemsPerPage, totalItems);
          const paginationText = `Mostrando <span class="font-semibold text-gray-900 ">${startEntry}</span> até <span class="font-semibold text-gray-900 ">${endEntry}</span> de <span class="font-semibold text-gray-900 ">${totalItems}</span> Caravanas`;

          $('#pagination_text').html(paginationText);

          // Atualizar botões de navegação
          const prevButton = $('.pagination-prev');
          const nextButton = $('.pagination-next');

          if (page === 1) {
            prevButton.prop('disabled', true).addClass('cursor-not-allowed opacity-50');
          } else {
            prevButton.prop('disabled', false).removeClass('cursor-not-allowed opacity-50');
          }

          if (page === totalPages) {
            nextButton.prop('disabled', true).addClass('cursor-not-allowed opacity-50');
          } else {
            nextButton.prop('disabled', false).removeClass('cursor-not-allowed opacity-50');
          }
        }

        function changePage(page) {
          currentPage = page;
          updateCaravansList(true);
        }

        //atualizar uma vez que carrega a pagina
        updateCaravansList(true);


        // Adicionar ward
        $("#caravan_add").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário e adicionar as variáveis user_id e stake_id
          var formData = $(this).serialize() +
            "&user_id=" + encodeURIComponent(userId) +
            "&indicador=caravan_add" +
            "&stake_id=" + encodeURIComponent(userStake);

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#caravan_add")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  updateCaravansList(true);
                  $('[data-modal-hide="caravan_add_modal"]').click();//fechar modal
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