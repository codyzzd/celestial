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

//pega o id da url
$id_caravan = $_GET['id'];
// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();
// Guarda a role do usuário
$user_role = checkUserRole($user_id);
// Guarda o id da estaca
$user_stake = checkStake($user_id);
// Pega as caravanas
$caravan = getCaravan($id_caravan);
//pega os veiculos da caravana
$vehicles = getCaravanVehicles($id_caravan);
// echo json_encode($vehicles);
$passengers = getPassengers($user_id);
// echo json_encode($passengers);
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
    <title><?= $caravan['name']; ?> - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
    <style>
      /* .seat {
        width: 50px;
        height: 50px;
        background-color: #ccc;
        border: 1px solid #000;
        display: inline-block;
        margin: 5px;
        text-align: center;
        line-height: 50px;
        cursor: pointer;
      } */
      .seat-active {
        background-color: rgb(216 180 254) !important;
        color: rgb(107 33 168) !important;
      }

      .seat.empty {
        background-color: rgba(0, 0, 0, 0.05) !important;
      }
    </style>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <section class="max-w-lg container mx-auto pb-2 md:p-4 mb-20">
      <form id="reserva_bancos">
        <div class="flex flex-col gap-1 md:gap-2">
          <div class="bg-white  dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  overflow-auto "
               id="header">
            <div class="destinationphoto aspect-video"></div>
            <div class="p-4">
              <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Caravana</p>
              <h5 class="text-xl font-semibold text-gray-900 dark:text-white"><?= $caravan['name']; ?></h5>
            </div>
          </div>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-row justify-between gap-4 md:rounded-lg md:shadow  "
               id="start_finish">
            <div class="flex-1 text-left">
              <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Partida</p>
              <p class="text-2xl"><?= formatDateOrTime($caravan['start_time'], 'time_Hi'); ?></p>
              <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
            </div>
            <div class="flex-1 text-left">
              <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Retorno</p>
              <p class="text-2xl"><?= formatDateOrTime($caravan['return_time'], 'time_Hi'); ?></p>
              <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
            </div>
          </div>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-col gap-1 md:rounded-lg md:shadow"
               id="capacity">
            <div class="flex flex-row gap-1 items-end">
              <p class="text-2xl">52</p>
              <p class="text-sm text-gray-500 mb-[2px]">Assentos disponíveis</p>
            </div>
            <div class="w-full bg-gray-200 h-2 rounded-full overflow-auto flex flex-row ">
              <div class="bg-purple-600 h-2 "
                   style="width: 15%;"></div>
            </div>
          </div>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  "
               id="obs">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Observação</p>
            <p class="text-sm text-gray-500"><?= $caravan['obs']; ?></p>
          </div>
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Escolha os lugares</h2>
          <?php foreach ($vehicles as $index => $vehicle): ?>
            <div class="bg-white p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow flex flex-col gap-2">
              <!-- <p class="text-sm text-gray-500 mb-4"> -->
              <div class="flex flex-row gap-2"><i class="fa fa-car-side text-lg text-gray-500 fa-fw "></i>
                <?= htmlspecialchars($vehicle['name']); ?></div>
              <div id="seat-layout-<?= $index ?>"
                   class="bus-layout"></div>
            </div>
          <?php endforeach; ?>
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Lugares escolhidos</h2>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow flex flex-col gap-4">
            <div id="reserva"
                 class="flex flex-col gap-4"> <!-- Campos gerados dinamicamente serão inseridos aqui --></div>
            <!-- empty state -->
            <div class="p-4  rounded-lg   flex flex-col   w-full  border-[2px] border-gray-300 border-dashed "
                 id="empty_state">
              <i class="fa fa-person-circle-question text-3xl text-gray-500 mb-2"></i>
              <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Nenhum banco reservado ainda!</h5>
              <p class="text-gray-600 dark:text-gray-300 text-base">Assim como o bom pastor não deixaria uma ovelha para trás, nós também não queremos deixar ninguém sem lugar!</p>
            </div>
            <button type="submit"
                    class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800 w-full justify-center">
              Confirmar Reservas
            </button>
          </div>
        </div>
        <input type="hidden"
               id="reservations_data"
               name="reservations_data"
               value="">
      </form>
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

        // Dados dos passageiros em formato JSON
        const passengers = <?= json_encode($passengers); ?>;

        // Array para armazenar as reservas
        let reservations = [];

        const reservationContainer = $('#reserva');

        // Função para criar uma nova linha de reserva
        function createReservationRow(vehicleName, vehicleId, seatNumber) {
          const reservationHTML = `
          <div class="flex flex-col gap-2 banco_reservado">
        <div class="flex flex-row">
            <p class="truncate flex-1" data-vehicle="${vehicleId}">${vehicleName}</p>
            <span class="" data-seat="${seatNumber}">Banco: ${seatNumber}</span>

        </div>
        <div class="w-full flex flex-row gap-3 justify-end items-end">
            <select name="passenger[]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="" disabled selected>Selecione...</option>
                ${passengers.map(passenger => `
                    <option required value="${passenger.id}">${passenger.name}</option>
                `).join('')}
            </select>
            <button class="remove-reservation w-10 h-10 text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm remove-btn">
    <i class="fa fa-times text-xl"></i>
</button>
        </div>
    </div>`;

          reservationContainer.append(reservationHTML);

          reservations.push({ vehicleId, seatNumber, passengerId: null });

          $('#reservations_data').val(JSON.stringify(reservations));
        }

        $(document).on('click', '.seat', function () {
          // Verifica se o assento já está ativo ou se é um assento vazio
          if ($(this).hasClass('seat-active') || $(this).hasClass('empty')) {
            return; // Impede o código abaixo de ser executado se o assento já estiver ativo ou for vazio
          }

          // Adiciona a classe 'seat-active' ao assento clicado
          $(this).addClass('seat-active');
          // $(this).get(0).offsetHeight; // Força o repaint, se necessário

          const seatNumber = $(this).data('seat');
          const vehicleId = $(this).data('vehicle-id');
          const vehicleName = $(this).data('vehicle-name');

          // Cria a linha de reserva
          createReservationRow(vehicleName, vehicleId, seatNumber);

          // Atualizar opções após a criação da reserva
          updateOptions();
          updateSubmitButtonState();
        });

        $('#reserva').on('change', 'select[name="passenger[]"]', function () {
          const index = $(this).closest('.banco_reservado').index();
          const passengerId = $(this).val();
          reservations[index].passengerId = passengerId;
          $('#reservations_data').val(JSON.stringify(reservations));
        });

        $(document).on('click', '.remove-reservation', function () {
          // Encontra a linha de reserva correspondente ao botão clicado
          const $reservationRow = $(this).closest('.banco_reservado');

          const seatNumber = $reservationRow.find('span').data('seat');
          const vehicleId = $reservationRow.find('p').data('vehicle');

          // Remove a linha de reserva do DOM
          $reservationRow.remove();

          // Atualiza o array de reservas removendo a reserva correspondente
          reservations = reservations.filter(reservation =>
            !(reservation.seatNumber === seatNumber && reservation.vehicleId === vehicleId)
          );

          // Atualiza o valor do campo de dados das reservas
          $('#reservations_data').val(JSON.stringify(reservations));

          // Remove a classe 'seat-active' do assento correspondente
          $(`.seat[data-seat="${seatNumber}"][data-vehicle-id="${vehicleId}"]`).removeClass('seat-active');

          // Atualizar opções após a remoção
          updateOptions();
          updateSubmitButtonState();
        });

        <?php foreach ($vehicles as $index => $vehicle): ?>
          var seatMapJson<?= $index ?> = <?= json_encode($vehicle['seat_map'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
          var seatMap<?= $index ?> = JSON.parse(seatMapJson<?= $index ?>);

          function renderSeatMap(seatMap, layoutId, vehicleId, vehicleName) {
            var seatLayout = $(layoutId);
            seatLayout.empty();
            seatMap.forEach(function (row) {
              let seatRow = $('<div>').addClass('seat-row');
              row.forEach(function (seat) {
                let seatElement = $('<div>').addClass('seat');
                seatElement.attr('data-vehicle-id', vehicleId);
                seatElement.attr('data-vehicle-name', vehicleName);
                if (seat === 'space' || !seat) {
                  seatElement.addClass('empty').attr('data-seat', '');
                } else {
                  seatElement.attr('data-seat', seat).text(seat);
                }
                seatRow.append(seatElement);
              });
              seatLayout.append(seatRow);
            });
          }

          renderSeatMap(seatMap<?= $index ?>, '#seat-layout-<?= $index ?>', '<?= $vehicle['id'] ?>', '<?= $vehicle['name'] ?>');
        <?php endforeach; ?>

        $('#reserva_bancos').on('submit', function (event) {
          event.preventDefault();

          // Serializar os dados do formulário
          var formData = $(this).serialize();

          // Adicionar outros parâmetros à string de consulta
          formData += "&user_id=" + encodeURIComponent(userId);
          formData += "&indicador=seat_reserv"; // Corrigido para o valor correto
          formData += "&reserva=" + encodeURIComponent($('#reservations_data').val());

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              console.log(response);
              // Lógica adicional, como redirecionamento ou mensagens de sucesso
            },
            error: function (xhr, status, error) {
              console.error('Erro ao enviar a reserva:', error);
            }
          });
        });

        // Função para atualizar a lista de opções disponíveis
        function updateOptions() {
          const selectedValues = $('select[name="passenger[]"]').map(function () {
            return $(this).val();
          }).get();

          $('select[name="passenger[]"]').each(function () {
            const $select = $(this);
            const selectedValue = $select.val();

            // Filtrar opções disponíveis
            $select.find('option').each(function () {
              const $option = $(this);
              if (selectedValues.includes($option.val()) && $option.val() !== selectedValue) {
                $option.prop('disabled', true);
              } else {
                $option.prop('disabled', false);
              }
            });
          });
        }

        // Evento de alteração para todos os selects
        $(document).on('change', 'select[name="passenger[]"]', function () {
          updateOptions();

        });

        // Inicializar as opções ao carregar a página
        updateOptions();

        // Função para verificar e atualizar o estado do botão de remoção
        function updateSubmitButtonState() {
          var reservationsData = $('#reservations_data').val().trim();

          if (reservationsData !== '[]' && reservationsData !== null && reservationsData !== undefined && reservationsData.trim() !== '') {
            // Remove a classe 'hidden' e ativa o botão se #reservations_data não for igual a '[]'
            $('#reserva_bancos button[type="submit"]').removeClass('cursor-not-allowed').prop('disabled', false);
          } else {
            // Adiciona a classe 'hidden' e desativa o botão se #reservations_data for igual a '[]'
            $('#reserva_bancos button[type="submit"]').addClass('cursor-not-allowed').prop('disabled', true);
          }
        }
        updateSubmitButtonState();
      });
    </script>
  </body>

</html>