<?php
//pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
//echo ROOT_PATH;
$apiPath = "../resources/api_reserv.php";
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
$user_stake = checkStake(user_id: $user_id);
// Pega as caravana
$caravan = getCaravan(caravan_id: $id_caravan);
//pega os veiculos da caravana
// $vehicles = getCaravanVehicles($id_caravan);
//pega os passageiros disponiveis do usuario
$passengers = getPassengers($user_id);
//pega os passageiros que tem até 6 anos
$kids = getPassengers($user_id, $caravan['start_date']);
//descobrir qual banco esta ocupado por quem
$reserveds = getSeatsReserved($id_caravan, true);


//calcular capacity
$totalCapacity = $caravan['total_seats'];
// foreach ($vehicles as $vehicle) {
//   $totalCapacity += $vehicle['capacity'];
// }
// Contar o número de assentos reservados
$totalReservedSeats = count($reserveds);

// Calcular a capacidade disponível
$availableSeats = $totalCapacity - $totalReservedSeats;

// Calcular a porcentagem de ocupação com proteção
$occupiedPercentage = ($totalCapacity - $availableSeats) / $totalCapacity * 100;

// Formatar a porcentagem com duas casas decimais
$formattedPercentage = number_format($occupiedPercentage, 2);
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
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto pb-2 md:p-4 mb-20">
      <form id="reserva_bancos">
        <div class="flex flex-col gap-1 md:gap-2">
          <div class="bg-white  dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  overflow-auto "
               id="header">
            <div class="bg-cover bg-center bg-no-repeat aspect-video"
                 id="foto"
                 style="background-image: url('../resources/i/destinations/<?= $caravan['photo'] ?>');"></div>
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
              <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['start_date'], 'date_EN_dMY'); ?></p>
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
              <p class="text-2xl"><?= $availableSeats ?></p>
              <p class="text-sm text-gray-500 mb-[2px]">Assentos disponíveis</p>
            </div>
            <div class="w-full bg-gray-200 h-2 rounded-full overflow-auto flex flex-row ">
              <div class="h-2 <?= $formattedPercentage >= 80 ? 'bg-red-600' : 'bg-purple-600' ?>"
                   style="width:<?= $formattedPercentage ?>%;"></div>
            </div>
            <?php if ($user_role == 'stake_lider' || $user_role == 'ward_lider'): ?>
              <div class="mt-3"
                   id="caravan_list">
                <a href="caravan_list.php?id=<?= $id_caravan ?>"
                   class="block w-full px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 text-center">
                  <i class="fa fa-table me-2"></i>
                  Lista de passageiros
                </a>
              </div>
            <?php endif; ?>
          </div>
          <?php if (!empty($caravan['obs'])): ?>
            <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  "
                 id="obs">
              <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Observação</p>
              <p class="text-sm text-gray-500"><?= $caravan['obs']; ?></p>
            </div>
          <?php endif; ?>
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Reservas Atuais</h2>
          <div class="md:w-full md:mx-0 text-gray-900 bg-white border border-gray-200 rounded-lg divide-y overflow-auto mx-4"
               id="reserv_list">
          </div>
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Adicionar Reserva</h2>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow flex flex-col gap-4">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox"
                     id="noSeatToggle"
                     value=""
                     class="sr-only peer">
              <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
              <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Criança de colo? (Não vai ocupar banco)</span>
            </label>
            <select name="passenger"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    required>
              <option value=""
                      disabled
                      selected>Selecione...</option>
              <?php foreach ($passengers as $passenger): ?>
                <?php
                $birthDate = new DateTime($passenger['nasc_date']);
                $today = new DateTime();
                $age = $birthDate->diff($today)->y;
                $isKid = $age < 6 ? '1' : '0';
                ?>
                <option value="<?= $passenger['id']; ?>"
                        is-kid="<?= $isKid; ?>"><?= $passenger['name']; ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit"
                    class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800 w-full justify-center">
              <i class="fa fa-plus me-2"></i>Adicionar Reserva
            </button>
          </div>
        </div>
        <!-- <input type="hidden"
               id="reservations_data"
               name="reservations_data"
               value=""> -->
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

        // Guarda a caravana id
        const caravana_id = "<?php echo $id_caravan; ?>";


        // toggle kids$(document).ready(function () {
        const $checkbox = $('input[type="checkbox"]');
        const $select = $('select[name="passenger"]');
        const $allOptions = $select.find('option').clone();

        $checkbox.on('change', function () {
          const showOnlyKids = $(this).is(':checked');
          $select.empty().append($allOptions.filter(function () {
            const isKid = $(this).attr('is-kid');
            return !showOnlyKids || isKid === '1' || !isKid;
          }));
        });

        $('#reserva_bancos').on('submit', function (event) {
          event.preventDefault();

          // Obter valores necessários
          // var userId = encodeURIComponent($('#user_id').val()); // Obtém o user_id do formulário
          // var reservaData = encodeURIComponent($('#reservations_data').val()); // Obtém os dados de reservas
          // var idCaravan = encodeURIComponent($('#id_caravan').val()); // Obtém o id_caravan do formulário

          // Criar a string de consulta com os dados necessários
          const passengerId = $('select[name="passenger"]').val();
          const noSeat = $('#noSeatToggle').is(':checked') ? 1 : 0;

          const data = $.param({
            user_id: userId,
            indicador: 'seat_add_novehicle',
            id_caravan: caravana_id,
            passenger_id: passengerId,
            no_seat: noSeat
          });

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data,
            dataType: "json", // Aqui o jQuery já espera um JSON
            success: function (jsonResponse) {
              if (jsonResponse.status === "success") {
                // $("#caravan_add")[0].reset();
                toast(jsonResponse.status, jsonResponse.msg);
                updateReservList();
              } else if (jsonResponse.status === "error") {
                toast(jsonResponse.status, jsonResponse.msg);
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a reserva: ' + error);
            }
          });
        });

        // Função para atualizar a lista de reservas
        function updateReservList() {

          // Criar o objeto data com os parâmetros padrão
          let data = {
            user_id: userId,
            caravan_id: caravana_id,
            indicador: "reserv_list", // Indicador para buscar as pessoas
          };

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data, // Passa o objeto data no request
            success: function (response) {

              var reservas = JSON.parse(response);
              $('#reserv_list').empty();

              // Adicionar novos reserv_list aos containers com base na relação
              reservas.forEach(function (reserva) {
                var reservaItem = `
<div class="block w-full px-4 py-2 border-gray-200 flex justify-between items-center">
<div class="flex flex-row text-start truncate items-center">
<i class="text-lg text-gray-500 me-2 fa min-w-[20px] text-center ${reserva.is_approved ? 'fa-circle-check text-green-600' : 'fa-hourglass-start text-yellow-600'}"></i>
<div class="flex flex-col truncate">
<p class="truncate seat-name">${reserva.name}</p>
<p class="truncate text-sm text-gray-500 hidden">Banco ${reserva.seat}</p>
<p class="text-sm text-gray-500 truncate">${reserva.is_approved ? 'Aprovado' : 'Pendente'}</p>
</div>
</div>
<div class="flex flex-row gap-2 ms-2 hidden">
<button type="button" data-id="${reserva.id}"
class="approve-btn border font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center
${reserva.is_approved ? 'text-yellow-700 border-yellow-700 hover:bg-yellow-700 hover:text-white focus:ring-yellow-300' : 'text-green-700 border-green-700 hover:bg-green-700 hover:text-white focus:ring-green-300'}">
<i class="text-lg fa text-center ${reserva.is_approved ? 'fa-thumbs-down' : 'fa-thumbs-up'}"></i>
</button>
<button type="button" data-id="${reserva.id}" data-modal-target="reserv_archive" data-modal-toggle="reserv_archive"
class="delete-btn text-red-700 border border-red-700 hover:bg-red-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center">
<i class="text-lg fa text-center fa-trash"></i>
</button>
<button type="button" data-id="${reserva.id}" data-modal-target="reserv_switch" data-modal-toggle="reserv_switch"
class="switch-btn text-blue-700 border border-blue-700 hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center">
<i class="text-lg fa text-center fa-right-left"></i>
</button>
</div>
</div>
`;
                $('#reserv_list').append(reservaItem);
              });



            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        }

        updateReservList();

      });


    </script>
  </body>

</html>