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
$user_stake = checkStake($user_id);
// Pega as caravana
$caravan = getCaravan($id_caravan);

$total_seats_status = ($caravan['total_seats'] !== null && $caravan['total_seats'] > 0);
// echo $total_seats_status;
//pega os veiculos da caravana
// $vehicles = getCaravanVehicles($id_caravan);
//pega os passageiros disponiveis do usuario
// $passengers = getPassengers($user_id);
//pega os passageiros que tem até 6 anos
// $kids = getPassengers($user_id, $caravan['start_date']);
//descobrir qual banco esta ocupado por quem
$reserveds = getSeatsReserved($id_caravan);

// //calcular capacity
// $totalCapacity = 0;
// foreach ($vehicles as $vehicle) {
//   $totalCapacity += $vehicle['capacity'];
// }
// // Contar o número de assentos reservados
// $totalReservedSeats = count($reserveds);

// // Calcular a capacidade disponível
// $availableSeats = $totalCapacity - $totalReservedSeats;

// // Calcular a porcentagem de ocupação
// $occupiedPercentage = ($totalCapacity - $availableSeats) / $totalCapacity * 100;

// // Formatar a porcentagem com duas casas decimais
// $formattedPercentage = number_format($occupiedPercentage, 2);
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
            <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
          </div>
          <div class="flex-1 text-left">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Retorno</p>
            <p class="text-2xl"><?= formatDateOrTime($caravan['return_time'], 'time_Hi'); ?></p>
            <p class="text-sm text-gray-500"><?= formatDateOrTime($caravan['return_date'], 'date_EN_dMY'); ?></p>
          </div>
        </div>
        <!-- <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 flex flex-col gap-1 md:rounded-lg md:shadow"
               id="capacity">
            <div class="flex flex-row gap-1 items-end">
              <p class="text-2xl"><?= $availableSeats ?></p>
              <p class="text-sm text-gray-500 mb-[2px]">Assentos disponíveis</p>
            </div>
            <div class="w-full bg-gray-200 h-2 rounded-full overflow-auto flex flex-row ">
              <div class="h-2 <?= $formattedPercentage >= 80 ? 'bg-red-600' : 'bg-purple-600' ?>"
                   style="width:<?= $formattedPercentage ?>%;"></div>
            </div>
          </div> -->
        <?php if (!empty($caravan['obs'])): ?>
          <div class="bg-white  p-4 dark:bg-gray-800 dark:border-gray-700 md:rounded-lg md:shadow  "
               id="obs">
            <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Observação</p>
            <p class="text-sm text-gray-500"><?= $caravan['obs']; ?></p>
          </div>
        <?php endif; ?>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mx-4 mt-4">Reservas</h2>
        <div class="md:w-full md:mx-0 text-gray-900 bg-white border border-gray-200 rounded-lg divide-y overflow-auto mx-4"
             id="reserv_list">
        </div>
      </div>
      <!-- modal edit -->
      <div id="reserv_archive_modal"
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
                Apagar Reserva?
              </h3>
              <button type="button"
                      id="close_edit"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="reserv_archive_modal">
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
                  id="reserv_edit">
              <div class="grid gap-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <div class="flex items-start p-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
                       role="alert">
                    <i class="fa fa-exclamation-triangle text-lg fa-fw me-3"></i>
                    <span class="sr-only">Info</span>
                    <div>
                      Essa ação é permanente e não poderá ser revertida.
                    </div>
                  </div>
                </div>
                <div class="col-span-2">
                  <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Nome da Reserva</p>
                  <p id="seat-name-form"></p>
                </div>
                <input type="hidden"
                       id="id"
                       name="id" />
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-hide="reserv_archive_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        id="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-white text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                  Apagar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- modal edit -->
      <div id="reserv_switch_modal"
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
                Trocar de banco?
              </h3>
              <button type="button"
                      id="close_edit"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="reserv_switch_modal">
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
                  id="reserv_switch_edit">
              <div class="grid gap-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <label for="id_new_seat"
                         class="block mb-2 text-sm font-medium text-gray-900">Selecionar passageiro para troca</label>
                  <select id="id_new_seat"
                          name="id_new_seat"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                          required>
                    <option value=""
                            selected>Selecione...</option>
                    <?php foreach ($reserveds as $reserved): ?>
                      <option value="<?php echo $reserved['seat_number']; ?>">
                        <?php echo $reserved['seat_number'] . ' - ' . $reserved['passenger_name']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <input type="hidden"
                       id="id_old_seat"
                       name="id_old_seat" />
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-hide="reserv_switch_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        id="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                  Trocar
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

        // Caminho da API para stake_edit
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // Defina o id_caravan a partir do PHP
        var caravanId = "<?php echo $id_caravan; ?>";

        // Adiciona um ouvinte de eventos para o documento
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para mostrar o modal
          const showModalTarget = event.target.closest('.delete-btn');
          const showModalSwitch = event.target.closest('.switch-btn');

          if (showModalTarget) { //processo de clicar em deletar
            const seatId = showModalTarget.getAttribute('data-id');

            // Pega o elemento com a classe 'seat-name' relacionado ao 'showModalTarget'
            const seatNameElement = showModalTarget.closest('.block').querySelector('.seat-name');
            // console.log(seatNameElement);

            // Popula o campo de formulário com o nome do assento
            $('#seat-name-form').val('');
            $('#seat-name-form').text(seatNameElement.textContent.trim());

            // Limpa o campo de input 'id' e define o valor com 'seatId'
            $('#reserv_edit #id').val(''); // Limpa o campo de input 'id'
            $('#reserv_edit #id').val(seatId); // Define o valor de 'seatId'

            // Mostra o Modal
            const passengerEditModal = new Modal(document.getElementById('reserv_archive_modal'));
            passengerEditModal.show();
          }

          if (showModalSwitch) { //processo de clicar em trocar
            const seatId = showModalSwitch.getAttribute('data-id');

            // Mostra o Modal
            const passengerSwitchModal = new Modal(document.getElementById('reserv_switch_modal'));
            passengerSwitchModal.show();

            // Limpa o campo de input 'id' e define o valor com 'seatId'
            $('#reserv_switch_edit #id_old_seat').val(''); // Limpa o campo de input 'id'
            $('#reserv_switch_edit #id_old_seat').val(seatId); // Define o valor de 'seatId'
          }

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para ocultar o modal
          const hideModalTarget = event.target.closest('[data-modal-hide="reserv_archive_modal"]');
          const hideModalSwitch = event.target.closest('[data-modal-hide="reserv_switch_modal"]');

          if (hideModalTarget) {
            // Inicializa e oculta o modal
            const passengerEditModal = new Modal(document.getElementById('reserv_archive_modal'));
            passengerEditModal.hide();
          }
          if (hideModalSwitch) {
            // Inicializa e oculta o modal
            const passengerSwitchModal = new Modal(document.getElementById('reserv_switch_modal'));
            passengerSwitchModal.hide();
          }


        });

        // Apagar Reserva
        $("#reserv_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize();

          // Adicionar o parâmetro adicional 'indicador=seat_delete'
          formData += '&indicador=reserv_delete';

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
                  updateReservList();

                  const seatEditModal = new Modal(document.getElementById('reserv_archive_modal'));
                  seatEditModal.hide();


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

        // Trocar Switch Reserva
        $("#reserv_switch_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize();

          // Adicionar o parâmetro adicional 'indicador=seat_delete'
          formData += '&indicador=reserv_switch';
          formData += '&caravan_id=' + encodeURIComponent(caravanId);

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
                  updateReservList();

                  const seatSwitchModal = new Modal(document.getElementById('reserv_switch_modal'));
                  seatSwitchModal.hide();


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

        // atualizar aprovacao
        $('#reserv_list').on('click', '.approve-btn', function () {
          var seatId = $(this).data('id');

          // Lógica para aprovar/desaprovar passageiro
          // console.log("Aprovação/Desaprovação para passageiro com ID: " + seatId);

          // Criar o objeto data com os parâmetros padrão
          let data = {
            seat_id: seatId,
            indicador: "reserv_toggle", // Indicador para buscar as pessoas
          };

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data,
            success: function (response) {
              // console.log(response);
              // var jsonResponse = JSON.parse(response);
              // toast(jsonResponse.status, jsonResponse.msg);
              updateReservList();
            },
            error: function (error) {

              // console.log("Erro ao aprovar/desaprovar passageiro: " + error);
            }
          });
        });

        // atualizar aprovacao
        $('#reserv_list').on('click', '.pay-btn', function () {
          var seatId = $(this).data('id');

          // Lógica para aprovar/desaprovar passageiro
          // console.log("Aprovação/Desaprovação para passageiro com ID: " + seatId);

          // Criar o objeto data com os parâmetros padrão
          let data = {
            seat_id: seatId,
            indicador: "pay_toggle", // Indicador para buscar as pessoas
          };

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data,
            success: function (response) {
              // console.log(response);

              updateReservList();
            },
            error: function (error) {

              // console.log("Erro ao aprovar/desaprovar passageiro: " + error);
            }
          });
        });

        // Função para atualizar a lista de reservas
        function updateReservList() {

          // Criar o objeto data com os parâmetros padrão
          let data = {
            user_id: userId,
            caravan_id: caravanId,
            indicador: "reserv_list", // Indicador para buscar as pessoas
          };

          $.ajax({
            type: "POST",
            url: apiPath,
            data: data, // Passa o objeto data no request
            success: function (response) {
              try {
                var reservas = JSON.parse(response);
                $('#reserv_list').empty();

                // Adicionar novos reserv_list aos containers com base na relação
                reservas.forEach(function (reserva) {
                  var nameParts = reserva.name.trim().split(' ');
                  var formattedName = nameParts[0] + ' ' + nameParts[nameParts.length - 1];

                  var reservaItem = `
  <div class="block w-full px-4 py-2 border-gray-200 flex justify-between items-center">
    <div class="flex flex-row text-start truncate items-center">
      <i class="text-lg text-gray-500 me-2 fa min-w-[20px] text-center ${reserva.is_approved ? 'fa-circle-check text-green-600' : 'fa-hourglass-start text-yellow-600'}"></i>
      <div class="flex flex-col truncate">
    <p class="truncate seat-name">${formattedName}</p>
       <p class="truncate text-sm text-gray-500">
  ${reserva.seat ? `Banco ${reserva.seat}` : 'Qualquer banco'}
</p>
        <p class="text-sm text-gray-500 truncate">${reserva.is_approved ? 'Aprovado' : 'Pendente'}</p>
      </div>
    </div>
    <div class="flex flex-row gap-2 ms-2">
      <button type="button" data-id="${reserva.id}"
        class="approve-btn border font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center
        ${reserva.is_approved ? 'text-white border-green-700 bg-green-700 hover:bg-green-800 focus:ring-green-300' : 'text-gray-700 border-gray-700 hover:bg-gray-700 hover:text-white focus:ring-gray-300'}">
        <i class="text-lg fa text-center ${reserva.is_approved ? 'fa-thumbs-up' : 'fa-thumbs-up'}"></i>
      </button>
      <button type="button" data-id="${reserva.id}"
        class="pay-btn border font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center
        ${reserva.is_payed ? 'text-white border-green-700 bg-green-700 hover:bg-green-800 focus:ring-green-300' : 'text-gray-700 border-gray-700 hover:bg-gray-700 hover:text-white focus:ring-gray-300'}">
        <i class="text-lg fa text-center ${reserva.is_payed ? 'fa-dollar-sign' : 'fa-dollar-sign'}"></i>
      </button>
      <?php if (!$total_seats_status): ?>
  <button type="button" data-id="<?= $reserva['id'] ?>" data-modal-target="reserv_switch" data-modal-toggle="reserv_switch"
        class="switch-btn text-blue-700 border border-blue-700 hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center">
        <i class="text-lg fa text-center fa-right-left"></i>
  </button>
   <button type="button" data-id="${reserva.id}" data-modal-target="reserv_archive" data-modal-toggle="reserv_archive"
        class="delete-btn text-red-700 border border-red-700 hover:bg-red-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center h-[40px] w-[40px] justify-center">
        <i class="text-lg fa text-center fa-trash"></i>
      </button>
<?php endif; ?>
    </div>
  </div>
`;
                  $('#reserv_list').append(reservaItem);
                });


              } catch (e) {
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
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