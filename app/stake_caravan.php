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
$user_role = checkUserRole($user_id, ['stake_lider', 'stake_aux']);


// Guarda o id da estaca
$user_stake = checkStake($user_id);

$caravan = getCaravan($_GET['id']);

$name = isset($caravan['name']) ? htmlspecialchars($caravan['name']) : '';
$start_date = isset($caravan['start_date']) ? formatDateOrTime($caravan['start_date'], 'date_EN_BR') : '';
$start_time = isset($caravan['start_time']) ? formatDateOrTime($caravan['start_time'], 'time_Hi') : '';
$return_date = isset($caravan['return_date']) ? formatDateOrTime($caravan['return_date'], 'date_EN_BR') : '';
$return_time = isset($caravan['return_time']) ? formatDateOrTime($caravan['return_time'], 'time_Hi') : '';
$obs = isset($caravan['obs']) ? htmlspecialchars($caravan['obs']) : '';

//get vehicles
$vehicles = getVehicles($user_stake);
$vehicles_used = getVehiclesUsed($caravan['id']);

//get destinations
$destinations = getDestinations();
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
    <title>Caravana - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto pb-2 md:p-4 mb-20">
      <div class="flex flex-col gap-1 md:gap-2">
        <div class="bg-white   dark:bg-gray-800 dark:border-gray-700 flex flex-row justify-between gap-4 md:rounded-lg md:shadow  "
             id="detail">
          <form class=" w-full"
                id="caravan_edit">
            <div class="grid gap-4 grid-cols-2 p-4">
              <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Caravana</h5>
              <div class="col-span-2">
                <label for="name"
                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome da Caravana</label>
                <input type="text"
                       id="name"
                       name="name"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                       placeholder="ex: Templo Curitiba"
                       required
                       value="<?php echo $name; ?>"
                       autocomplete="off" />
              </div>
              <div class="col-span-2">
                <label for="destination"
                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Destino</label>
                <select id="destination"
                        name="destination"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                        required>
                  <option value=""
                          disabled
                          selected>Selecione...</option>
                  <?php foreach ($destinations as $destination): ?>
                    <option value="<?= $destination['id'] ?>"
                            <?= $destination['id'] === $caravan['destination'] ? 'selected' : '' ?>><?= $destination['name'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="">
                <label for="start_date"
                       class="block mb-2 text-sm font-medium text-gray-900">Data de Partida</label>
                <input type="text"
                       id="start_date"
                       name="start_date"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                       placeholder="dd/mm/aaaa"
                       value="<?php echo $start_date; ?>"
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
                       value="<?php echo $start_time; ?>"
                       required
                       autocomplete="off">
              </div>
              <div class="">
                <label for="return_date"
                       class="block mb-2 text-sm font-medium text-gray-900">Data de Retorno</label>
                <input type="text"
                       id="return_date"
                       name="return_date"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                       placeholder="dd/mm/aaaa"
                       value="<?php echo $return_date; ?>"
                       required
                       autocomplete="off">
              </div>
              <div class="">
                <label for="return_time"
                       class="block mb-2 text-sm font-medium text-gray-900">Horário de Retorno</label>
                <input type="text"
                       id="return_time"
                       name="return_time"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                       placeholder="00:00"
                       value="<?php echo $return_time; ?>"
                       required
                       autocomplete="off">
              </div>
              <div class="col-span-2">
                <label for="obs"
                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
                <textarea id="obs"
                          name="obs"
                          rows="4"
                          class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"><?php echo $obs; ?></textarea>
              </div>
              <div class="col-span-2 flex flex-row gap-3 items-end justify-end">
                <div class="w-full">
                  <label for="vehicles"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adicionar Veículo</label>
                  <select id="vehicles"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <!-- <option selected>Selecione...</option> -->
                    <?php foreach ($vehicles as $vehicle): ?>
                      <option value="<?php echo htmlspecialchars($vehicle['id']); ?>"
                              data-capacity="<?php echo htmlspecialchars($vehicle['capacity']); ?>">
                        <?php echo htmlspecialchars($vehicle['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <a href="stake_vehicles.php"
                   class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 w-fit h-fit text-center">Gestão de Veículo</a>
              </div>
              <div class="col-span-2">
                <button type="button"
                        id="addVehicle"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 w-full">Adicionar Veículo</button>
              </div>
              <div class="col-span-2">
                <div class="relative overflow-x-auto">
                  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-900 uppercase dark:text-gray-400">
                      <tr>
                        <th scope="col"
                            class="p-2">
                          Lista de Veículo
                        </th>
                        <th scope="col"
                            class="p-2">
                        </th>
                        <th scope="col"
                            class="p-2">
                        </th>
                      </tr>
                    </thead>
                    <tbody id="vehicleTableBody">
                    </tbody>
                    <tfoot>
                      <tr class="font-semibold text-gray-900 dark:text-white">
                        <th scope="row"
                            class="p-2 text-base">Total</th>
                        <td class="p-2"
                            id="totalCapacity">0</td>
                        <td class="p-2"></td>
                      </tr>
                    </tfoot>
                  </table>
                  <div class="col-span-2">
                    <a href="caravan_list.php?id=<?= $caravan['id'] ?>" target="_blank"
                       class="block w-full px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 text-center">
                      <i class="fa fa-table me-2"></i>
                      Lista de passageiros
                    </a>
                  </div>
                </div>
              </div>
              <!-- Campo oculto para armazenar o layout dos assentos em JSON -->
              <input type="hidden"
                     id="id"
                     name="id"
                     value="<?php echo $_GET['id']; ?>">
            </div>
            <!-- Modal footer -->
            <div class="flex items-center  p-4 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
              <button id="caravan_archive"
                      type="button"
                      class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center  dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">Arquivar</button>
              <a href="javascript:history.back()"
                 class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Voltar</a>
              <button type="submit"
                      class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                Salvar
              </button>
            </div>
          </form>
        </div>
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
        // Caminho da API para stake_edit
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // Defina o user_id a partir do PHP
        var userStake = "<?php echo $user_stake; ?>";

        // Mascarar campo 'cod' dentro do formulário com id 'caravan_edit'
        $('#caravan_edit #start_date').mask('00/00/0000');
        $('#caravan_edit #start_time').mask('00:00');
        $('#caravan_edit #return_date').mask('00/00/0000');
        $('#caravan_edit #return_time').mask('00:00');

        // Handle the add vehicle button click
        $('#addVehicle').on('click', function () {
          var select = $('#vehicles');
          var selectedOption = select.find('option:selected');
          var vehicleId = selectedOption.val();
          var vehicleName = selectedOption.text();
          var vehicleCapacity = selectedOption.data('capacity');

          // Create a new row for the vehicle
          var row = $('<tr>').attr('data-id', vehicleId)
            .addClass('bg-white border-b hover:bg-gray-50')
            .html(`
          <th scope="row" class="p-2 font-medium text-gray-900 w-full">${vehicleName}</th>
          <td class="p-2">${vehicleCapacity}</td>
          <td class="p-2">
            <a href="#" class="font-medium text-blue-600 hover:underline remove-vehicle">
              <i class="fa fa-close text-lg fa-fw me-2 text-red-700"></i>
            </a>
          </td>
        `);
          $('#vehicleTableBody').append(row);
          updateTotalCapacity();
        });

        // Handle removal of vehicle rows
        $('#vehicleTableBody').on('click', '.remove-vehicle', function (event) {
          event.preventDefault();
          var $row = $(this).closest('tr');

          // Check if the row is added dynamically or is part of the initial load
          if (!$row.hasClass('initial')) {
            $row.remove();
            updateTotalCapacity();
          }
        });

        // Update total capacity
        function updateTotalCapacity() {
          var totalCapacity = 0;
          $('#vehicleTableBody tr').each(function () {
            var capacityText = $(this).find('td').first().text().trim();
            var capacity = parseInt(capacityText);
            if (!isNaN(capacity)) {
              totalCapacity += capacity;
            }
          });
          $('#totalCapacity').text(totalCapacity);
        }

        // Pass the PHP JSON data to JavaScript
        var vehiclesUsed = <?php echo json_encode($vehicles_used); ?>;

        // Load vehicles from the PHP variable
        function loadVehicles() {
          var $tableBody = $('#vehicleTableBody');
          $tableBody.empty(); // Clear the table body
          $.each(vehiclesUsed, function (index, vehicle) {
            var row = $('<tr>').attr('data-id', vehicle.id)
              .addClass('bg-white border-b hover:bg-gray-50 initial') // Mark initial rows
              .html(`
            <th scope="row" class="p-2 font-medium text-gray-900 w-full">${vehicle.name}</th>
            <td class="p-2">${vehicle.capacity}</td>
            <td class="p-2">
              <!-- Button to remove vehicle is hidden for initial vehicles -->
              <a href="#" class="font-medium text-blue-600 hover:underline remove-vehicle" style="display: none;">
                <i class="fa fa-close text-lg fa-fw me-2 text-red-700"></i>
              </a>
            </td>
          `);
            $tableBody.append(row);
          });
          updateTotalCapacity();
        }

        // Adicionar ward
        $("#caravan_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Colete os IDs dos veículos da tabela, ignorando os que têm a classe 'initial'
          var vehicleData = [];
          $('#vehicleTableBody tr').each(function () {
            if (!$(this).hasClass('initial')) { // Verifique se a linha não tem a classe 'initial'
              var vehicleId = $(this).data('id'); // Pegue o id do veículo
              if (vehicleId) {
                vehicleData.push({ id: vehicleId });
              }
            }
          });

          // Pegar o valor do campo id no formulário
          var formId = $("#caravan_edit #id").val();

          // Serializar os campos do formulário e adicionar as variáveis user_id, stake_id e o id do formulário
          var formData = $(this).serialize() +
            "&user_id=" + encodeURIComponent(userId) +
            "&indicador=caravan_edit" +
            "&stake_id=" + encodeURIComponent(userStake) +
            "&vehicle_ids=" + encodeURIComponent(JSON.stringify(vehicleData)) + // Adicione os IDs dos veículos
            "&id=" + encodeURIComponent(formId); // Adicione o id do formulário

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  toast(jsonResponse.status, jsonResponse.msg);

                  // Aguarda 2 segundos (2000 milissegundos) antes de redirecionar
                  setTimeout(function () {
                    window.location.href = "stake_caravans.php";
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


        // Verifica se o botão clicado é o de arquivamento
        $(document).on('click', '#caravan_archive', function (event) {
          event.preventDefault(); // Previne o comportamento padrão do botão
          // console.log("clicou no #passenger_archive")

          // Obtém o ID do passageiro que será arquivado
          const form = $('#caravan_edit');
          const caravanId = form.find('input[name="id"]').val();

          // Cria um objeto com os dados a serem enviados
          const formData = {
            id: caravanId,
            indicador: 'archive_something',
            bd: 'caravans'
          };

          // Realiza a ação de arquivamento via AJAX
          $.ajax({
            url: apiPath,
            type: 'POST',
            data: formData,
            success: function (response) {
              try {
                const jsonResponse = JSON.parse(response);
                if (jsonResponse.status === "success") {
                  toast(jsonResponse.status, jsonResponse.msg);

                  // Aguarda 2 segundos (2000 milissegundos) antes de redirecionar
                  setTimeout(function () {
                    window.location.href = "stake_caravans.php";
                  }, 3000);
                } else if (jsonResponse.status === "error") {
                  toast(jsonResponse.status, jsonResponse.msg);
                }
              } catch (e) {
                console.error('Erro ao processar resposta:', e);
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
            },
            error: function (xhr, status, error) {
              console.error('Erro AJAX:', error);
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        });



        // Load vehicles on page load
        loadVehicles();
      });
    </script>
  </body>

</html>