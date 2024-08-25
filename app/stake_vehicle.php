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
$stake_id = getStake($user_id);
// echo $stake_id;
// Guarda a role do usuário
$user_role = checkUserRole($user_id, 'stake_lider');

$vehicle = getVehicle($_GET['id']);
// Dados do veículo
$name = isset($vehicle['name']) ? htmlspecialchars($vehicle['name']) : '';
$obs = isset($vehicle['obs']) ? htmlspecialchars($vehicle['obs']) : '';
$seat_map = isset($vehicle['seat_map']) ? htmlspecialchars($vehicle['seat_map']) : '';
$capacity = isset($vehicle['capacity']) ? htmlspecialchars($vehicle['capacity']) : '';
$isUsed = isset($vehicle['used']) && $vehicle['used'] === 'yes' ? 'readonly' : ''; // Define o atributo readonly se 'used' for 'yes'
$seat_map = isset($vehicle['seat_map']) ? htmlspecialchars($vehicle['seat_map']) : '';
// echo $seat_map;

// $isUsed = "readonly";
?>
<!DOCTYPE html>
<html lang="pt-BR">
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
  <title>Veículo <?php echo $name; ?> - Caravana Celestial</title>
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
        <div class="bg-white   dark:bg-gray-800 dark:border-gray-700 flex flex-row justify-between gap-4 md:rounded-lg md:shadow  "
             id="detail">
          <form class=" w-full"
                id="vehicle_edit">
            <div class="grid gap-4 grid-cols-2 p-4">
              <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Veículo</h5>
              <div class="col-span-2">
                <label for="name"
                       class="block mb-2 text-sm font-medium text-gray-900">Nome</label>
                <input type="text"
                       id="name"
                       name="name"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                       required
                       value="<?php echo $name; ?>"
                       placeholder="ex: Itaipu Travel Double Deck 60"
                       autocomplete="off"
                       <?php echo $isUsed; ?>>
              </div>
              <div class="col-span-2">
                <label for="obs"
                       class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
                <textarea id="obs"
                          name="obs"
                          rows="4"
                          placeholder="ex: Bancos 50 a 60 são reservados para idosos e pessoas com necessidades especiais."
                          class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                          <?php echo $isUsed; ?>><?php echo $obs; ?></textarea>
              </div>
              <div class="col-span-2 flex space-x-4">
                <button type="button"
                        id="add-row"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 w-full">+ Fila</button>
                <button type="button"
                        id="add-hr"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 w-full">+ Separador</button>
              </div>
              <div class="col-span-2">
                <label for="seat-layout"
                       class="block mb-2 text-sm font-medium text-gray-900">Configuração do Layout dos Assentos</label>
                <div id="seat-layout"
                     class="bus-layout">
                  <!-- Fileiras serão adicionadas dinamicamente aqui -->
                </div>
              </div>
              <!-- Campo oculto para armazenar o layout dos assentos em JSON -->
              <input type="hidden"
                     id="id"
                     name="id"
                     value="<?php echo $_GET['id']; ?>">
              <input type="hidden"
                     id="seat_map"
                     name="seat_map"
                     value="<?php echo $seat_map; ?>">
              <input type="hidden"
                     id="capacity"
                     name="capacity"
                     value="<?php echo $capacity; ?>">
            </div>
            <!-- Modal footer -->
            <div class="flex items-center  p-4 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
              <button id="vehicle_archive"
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
        // Caminho da API para passenger_add
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        var stakeId = "<?php echo $stake_id; ?>";

        // mascarar campos
        // $('#vehicle_add #capacity').mask('00');
        $('#vehicle_edit #capacity').mask('00');

        // Salvar passenger
        $("#vehicle_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() +
            "&user_id=" + encodeURIComponent(userId) +
            "&indicador=vehicle_edit" +
            "&id=" + encodeURIComponent($('#id').val()) +
            "&seat_map=" + encodeURIComponent($('#seat_map').val()) +
            "&capacity=" + encodeURIComponent($('#capacity').val());

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  // $("#vehicle_edit")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // updatePassengersList(true); // Se necessário, descomente esta linha para atualizar a lista
                  // Fechar o modal diretamente
                  // $("#close_edit").trigger('click');

                  // updatePeopleList();
                  // updateVehicleList('not_deleted');
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

        // Função para renderizar o mapa de assentos
        function renderSeatMap(seatMap) {
          var seatLayout = $('#seat-layout');
          seatLayout.empty(); // Limpar o conteúdo existente

          seatMap.forEach(function (row) {
            if (row === 'separator') {
              addSeparator();
            } else {
              let seatRow = $('<div>').addClass('seat-row');

              row.forEach(function (seat) {
                let seatElement = $('<div>').addClass('seat');
                if (seat === 'space' || !seat) {
                  seatElement.addClass('empty').attr('data-seat', '');
                  // Não define nenhum texto para o assento vazio
                } else {
                  seatElement.attr('data-seat', seat).text(seat);
                }

                seatElement.on('click', function () {
                  if ($(this).hasClass('empty')) {
                    let seatNumber = prompt("Digite o número do assento ou deixe em branco para espaço vazio:");
                    if (seatNumber && !isSeatNumberDuplicate(seatNumber)) {
                      $(this).removeClass('empty').text(seatNumber).attr('data-seat', seatNumber);
                    } else if (seatNumber) {
                      alert("Este número de assento já foi usado! Escolha outro número.");
                    }
                  } else {
                    $(this).addClass('empty').text('').attr('data-seat', '');
                  }
                  updateSeatMapInput();
                });

                seatRow.append(seatElement);
              });

              addRemoveButton(seatRow);
              seatLayout.append(seatRow);
            }
          });

          updateSeatMapInput(); // Atualizar o valor do campo e a capacidade total
        }

        // Função para atualizar o valor do mapa de assentos e a capacidade total
        function updateSeatMapInput() {
          let seatMap = [];
          let totalCapacity = 0;

          $('#seat-layout').children().each(function () {
            if ($(this).hasClass('separator-row')) {
              seatMap.push("separator");
            } else if ($(this).hasClass('seat-row')) {
              let rowData = [];
              let rowCapacity = 0;

              $(this).find('.seat').each(function () {
                let seatNumber = $(this).attr('data-seat');
                rowData.push(seatNumber ? seatNumber : "space");

                if (seatNumber) {
                  rowCapacity++;
                }
              });

              seatMap.push(rowData);
              totalCapacity += rowCapacity;
            }
          });

          $('#seat_map').val(JSON.stringify(seatMap));
          if ($('#capacity').length) {
            $('#capacity').val(totalCapacity);
          }
        }

        // Função para verificar se o número do assento já está em uso
        function isSeatNumberDuplicate(seatNumber) {
          return $('.seat').filter(function () {
            return $(this).attr('data-seat') === seatNumber;
          }).length > 0;
        }

        // Função para adicionar uma nova linha de assentos
        function addSeatRow() {
          let row = $('<div>').addClass('seat-row');

          for (let i = 0; i < 4; i++) {
            let seat = $(`
      <div class="seat empty" data-seat=""></div>
    `);

            seat.on('click', function () {
              if ($(this).hasClass('empty')) {
                let seatNumber = prompt("Digite o número do assento ou deixe em branco para espaço vazio:");
                if (seatNumber && !isSeatNumberDuplicate(seatNumber)) {
                  $(this).removeClass('empty').text(seatNumber).attr('data-seat', seatNumber);
                } else if (seatNumber) {
                  alert("Este número de assento já foi usado! Escolha outro número.");
                }
              } else {
                $(this).addClass('empty').text('').attr('data-seat', '');
              }
              updateSeatMapInput();
            });

            row.append(seat);
          }

          addRemoveButton(row);
          $('#seat-layout').append(row);
        }

        // Função para adicionar um separador
        function addSeparator() {
          let separatorRow = $('<div>').addClass('separator-row');

          let separator = $(`
    <div class="separator flex-1">separator</div>
  `);

          let removeBtn = $(`
          <button class="w-10 h-10 text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm remove-btn">
    <i class="fa fa-times text-xl"></i>
</button>
  `);
          removeBtn.on('click', function () {
            $(this).parent().remove();
            updateSeatMapInput();
          });

          separatorRow.append(separator).append(removeBtn);
          $('#seat-layout').append(separatorRow);
        }

        // Função para adicionar um botão de remoção à linha de assentos
        function addRemoveButton(row) {
          let removeBtn = $(`
          <button class="w-10 h-10 text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm remove-btn">
    <i class="fa fa-times text-xl"></i>
</button>
  `);
          removeBtn.on('click', function () {
            $(this).parent().remove();
            updateSeatMapInput();
          });
          row.append(removeBtn);
        }

        // Renderizar o mapa de assentos com base no valor inicial do campo #seat_map
        var seatMapData = $('#seat_map').val();
        if (seatMapData) {
          try {
            var seatMap = JSON.parse(seatMapData);
            renderSeatMap(seatMap);
          } catch (e) {
            console.error('Erro ao analisar o mapa de assentos:', e);
          }
        }

        // Adicionar nova linha de assentos
        $(document).on('click', '#add-row', function () {
          addSeatRow();
          updateSeatMapInput();
        });

        // Adicionar separador
        $(document).on('click', '#add-hr', function () {
          addSeparator();
          updateSeatMapInput();
        });


        // Supondo que você tenha uma variável JavaScript que indica o estado readonly
        var isUsed = '<?php echo $isUsed; ?>'; // Captura a variável PHP no JavaScript

        if (isUsed === 'readonly') {
          // Desabilitar botões de adicionar fila e separador
          $('#add-row').prop('disabled', true);
          $('#add-hr').prop('disabled', true);

          // Desabilitar a possibilidade de adicionar assentos ou editar os existentes
          $('.seat').addClass('readonly-seat'); // Adiciona uma classe para aplicar estilos ou comportamentos

          // Desabilitar eventos de clique nos assentos usando delegação de eventos
          $('#seat-layout').on('click', '.seat', function (event) {
            event.preventDefault(); // Impede a ação padrão de clique
            return false; // Impede qualquer outra ação
          });

          // Desabilitar e esconder botões de remoção
          $('.remove-btn').prop('disabled', true).addClass('hidden');
        }



        // Verifica se o botão clicado é o de arquivamento
        $(document).on('click', '#vehicle_archive', function (event) {
          event.preventDefault(); // Previne o comportamento padrão do botão
          // console.log("clicou no #passenger_archive")

          // Obtém o ID do passageiro que será arquivado
          const form = $('#vehicle_edit');
          const vehicleId = form.find('input[name="id"]').val();

          // Cria um objeto com os dados a serem enviados
          const formData = {
            id: vehicleId,
            indicador: 'archive_something',
            bd: 'vehicles'
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
                    window.location.href = "stake_vehicles.php";
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




      });
    </script>
  </body>

</html>