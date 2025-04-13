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
$user_stake = checkStake(user_id: $user_id);
// Pega as caravana
$caravan = getCaravan(caravan_id: $id_caravan);
// Pega a lista total da situação da caravana
$seats = getCaravanList($id_caravan);
// print_r($seats);

// Verifica se a primeira linha tem a chave 'vehicle_name'
$novehicles = !isset($seats[0]['vehicle_name']);
// echo $novehicles;

if (!$novehicles) {
  // Organiza os seats por veículo
  $groupedSeats = [];

  foreach ($seats as $seat) {
    $vehicleName = $seat['vehicle_name'];

    if (!isset($groupedSeats[$vehicleName])) {
      $groupedSeats[$vehicleName] = [];
    }

    $groupedSeats[$vehicleName][] = $seat;
  }
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
    <title><?= $caravan['name']; ?> - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
    <!-- libs para criar arquivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".sort").forEach(function (element) {
          element.addEventListener("click", function (e) {
            e.preventDefault();

            let table = document.querySelector("table tbody");
            let rows = Array.from(table.querySelectorAll("tr"));
            let column = this.getAttribute("data-column");
            let order = this.getAttribute("data-order") === "asc" ? "desc" : "asc"; // Alterna a ordem

            // Atualiza o atributo para refletir a nova ordem
            this.setAttribute("data-order", order);

            // Encontra o índice da coluna
            let columnIndex;
            switch (column) {
              case "banco": columnIndex = 0; break; // Adicionado banco
              case "nome": columnIndex = 1; break;
              case "idade": columnIndex = 2; break;
              case "ala": columnIndex = 3; break;
              default: return;
            }

            // Ordenação lógica
            rows.sort((a, b) => {
              let cellA = a.children[columnIndex].textContent.trim().toLowerCase();
              let cellB = b.children[columnIndex].textContent.trim().toLowerCase();

              // Converte para número se for a coluna "banco" ou "idade"
              if (column === "banco" || column === "idade") {
                cellA = parseInt(cellA) || 0;
                cellB = parseInt(cellB) || 0;
              }

              return order === "asc" ? (cellA > cellB ? 1 : -1) : (cellA < cellB ? 1 : -1);
            });

            // Atualiza os ícones de ordenação
            document.querySelectorAll(".sort i").forEach(i => i.classList.remove("fa-sort-up", "fa-sort-down"));
            let icon = this.querySelector("i");
            icon.classList.add(order === "asc" ? "fa-sort-up" : "fa-sort-down");

            // Reinsere as linhas na tabela
            table.innerHTML = "";
            rows.forEach(row => table.appendChild(row));
          });
        });
      });
    </script>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-8xl container mx-auto pb-2 p-4 mb-20">
      <!-- header -->
      <div class="flex flex-col  md:flex-row space-y-4 md:space-x-4 md:justify-between mb-3">
        <div class="flex-col gap-1">
          <p class="text-xs font-medium tracking-wider text-gray-600 uppercase truncate">Caravana</p>
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900"><?= $caravan['name']; ?></h1>
        </div>
      </div>
      <!-- tabela com veiculos -->
      <?php if (!$novehicles): ?>
        <?php foreach ($groupedSeats as $vehicleName => $vehicleSeats): ?>
          <div class="header-list flex flex-col md:flex-row md:justify-between mb-2 gap-2 md:items-center">
            <p class="text-sm text-gray-500">Lista: <?= htmlspecialchars($vehicleName) ?></p>
            <button id="dropdownDefaultButton-<?= htmlspecialchars($vehicleName) ?>"
                    data-dropdown-toggle="multi-dropdown-<?= htmlspecialchars($vehicleName) ?>"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-fit"
                    type="button">Baixar <svg class="w-2.5 h-2.5 ms-3"
                   aria-hidden="true"
                   xmlns="http://www.w3.org/2000/svg"
                   fill="none"
                   viewBox="0 0 10 6">
                <path stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="m1 1 4 4 4-4" />
              </svg>
            </button>
            <!-- Dropdown menu -->
            <div id="multi-dropdown-<?= htmlspecialchars($vehicleName) ?>"
                 class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
              <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                  aria-labelledby="multiLevelDropdownButton">
                <!-- Completo -->
                <li>
                  <button id="completeDropdownButton-<?= htmlspecialchars($vehicleName) ?>"
                          data-dropdown-toggle="completeDropdown-<?= htmlspecialchars($vehicleName) ?>"
                          data-dropdown-placement="right-start"
                          type="button"
                          class="flex items-center justify-between w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                    Completo
                    <svg class="w-2.5 h-2.5 ms-3 rtl:rotate-180"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 6 10">
                      <path stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                  </button>
                  <div id="completeDropdown-<?= htmlspecialchars($vehicleName) ?>"
                       class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                        aria-labelledby="completeDropdownButton-<?= htmlspecialchars($vehicleName) ?>">
                      <li>
                        <a href="#"
                           data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                           data-file="xls"
                           data-type="completo"
                           data-vehicle-name="<?= htmlspecialchars($vehicleName) ?>"
                           class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">XLS</a>
                      </li>
                      <li>
                        <a href="#"
                           data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                           data-file="pdf"
                           data-type="completo"
                           data-vehicle-name="<?= htmlspecialchars($vehicleName) ?>"
                           class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">PDF</a>
                      </li>
                    </ul>
                  </div>
                </li>
                <!-- Simples -->
                <li>
                  <button id="simpleDropdownButton-<?= htmlspecialchars($vehicleName) ?>"
                          data-dropdown-toggle="simpleDropdown-<?= htmlspecialchars($vehicleName) ?>"
                          data-dropdown-placement="right-start"
                          type="button"
                          class="flex items-center justify-between w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                    Simples
                    <svg class="w-2.5 h-2.5 ms-3 rtl:rotate-180"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 6 10">
                      <path stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                  </button>
                  <div id="simpleDropdown-<?= htmlspecialchars($vehicleName) ?>"
                       class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                        aria-labelledby="simpleDropdownButton-<?= htmlspecialchars($vehicleName) ?>">
                      <li>
                        <a href="#"
                           data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                           data-file="xls"
                           data-type="simples"
                           data-vehicle-name="<?= htmlspecialchars($vehicleName) ?>"
                           class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">XLS</a>
                      </li>
                      <li>
                        <a href="#"
                           data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                           data-file="pdf"
                           data-type="simples"
                           data-vehicle-name="<?= htmlspecialchars($vehicleName) ?>"
                           class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">PDF</a>
                      </li>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <div class="relative overflow-x-auto rounded-lg mb-8">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th scope="col"
                      class="p-3 text-nowrap text-left">
                    <div class="d-flex justify-content-between align-items-center">
                      # <a href="#"
                         class="sort"
                         data-column="banco"
                         data-order="asc"><i class="fa fa-sort"></i></a>
                    </div>
                  </th>
                  <th scope="col"
                      class="p-3 text-nowrap text-left">
                    <div class="d-flex justify-content-between align-items-center">
                      Nome <a href="#"
                         class="sort"
                         data-column="nome"
                         data-order="asc"><i class="fa fa-sort"></i></a>
                    </div>
                  </th>
                  <th scope="col"
                      class="p-3 text-nowrap text-left">
                    <div class="d-flex justify-content-between align-items-center">
                      Idade <a href="#"
                         class="sort"
                         data-column="idade"
                         data-order="asc"><i class="fa fa-sort"></i></a>
                    </div>
                  </th>
                  <th scope="col"
                      class="p-3 text-nowrap text-left">
                    <div class="d-flex justify-content-between align-items-center">
                      Ala <a href="#"
                         class="sort"
                         data-column="ala"
                         data-order="asc"><i class="fa fa-sort"></i></a>
                    </div>
                  </th>
                  <th scope="col"
                      class="p-3 text-nowrap ">Tipo Doc.</th>
                  <th scope="col"
                      class="p-3 text-nowrap ">Doc.</th>
                  <th scope="col"
                      class="p-3 text-nowrap ">Tel.</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($vehicleSeats as $index => $seat): ?>
                  <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="p-3 text-nowrap min-w-4 w-4 max-w-4"><?= htmlspecialchars($seat['seat']) ?></td>
                    <th scope="row"
                        class="p-3 font-medium text-gray-900 whitespace-nowrap dark:text-white text-nowrap min-w-24 w-24 max-w-24 truncate">
                      <?= htmlspecialchars($seat['passenger_name']) ?>
                    </th>
                    <td class="p-3 text-nowrap min-w-16 w-16 max-w-16"><?= htmlspecialchars($seat['age']) ?></td>
                    <td class="p-3 text-nowrap min-w-24 w-24 max-w-24 truncate"><?= htmlspecialchars($seat['ward_name']) ?></td>
                    <td class="p-3 text-nowrap min-w-16 w-16 max-w-16 truncate"><?= htmlspecialchars($seat['document_name']) ?></td>
                    <td class="p-3 text-nowrap min-w-32 w-32 max-w-32"><?= htmlspecialchars($seat['document']) ?></td>
                    <td class="p-3 text-nowrap min-w-32 w-32 max-w-32"><?= htmlspecialchars($seat['obs']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <!-- tabela sem veiculos -->
      <?php if ($novehicles): ?>
        <div class="header-list flex flex-col md:flex-row md:justify-between mb-2 gap-2 md:items-center">
          <p class="text-sm text-gray-500">Lista</p>
          <button id="dropdownDefaultButton-veiculo"
                  data-dropdown-toggle="multi-dropdown-veiculo"
                  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-fit"
                  type="button">Baixar <svg class="w-2.5 h-2.5 ms-3"
                 aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 10 6">
              <path stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m1 1 4 4 4-4" />
            </svg>
          </button>
          <!-- Dropdown menu -->
          <div id="multi-dropdown-veiculo"
               class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                aria-labelledby="multiLevelDropdownButton">
              <!-- Completo -->
              <li>
                <button id="completeDropdownButton-veiculo"
                        data-dropdown-toggle="completeDropdown-veiculo"
                        data-dropdown-placement="right-start"
                        type="button"
                        class="flex items-center justify-between w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                  Completo
                  <svg class="w-2.5 h-2.5 ms-3 rtl:rotate-180"
                       aria-hidden="true"
                       xmlns="http://www.w3.org/2000/svg"
                       fill="none"
                       viewBox="0 0 6 10">
                    <path stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="m1 9 4-4-4-4" />
                  </svg>
                </button>
                <div id="completeDropdown-veiculo"
                     class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                  <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                      aria-labelledby="completeDropdownButton-veiculo">
                    <li>
                      <a href="#"
                         data-cv="<?= htmlspecialchars($id_caravan) ?>"
                         data-vh
                         data-file="xls"
                         data-type="completo"
                         data-vehicle-name="veiculo"
                         class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">XLS</a>
                    </li>
                    <li>
                      <a href="#"
                         data-cv="<?= htmlspecialchars($id_caravan) ?>"
                         data-vh
                         data-file="pdf"
                         data-type="completo"
                         data-vehicle-name="veiculo"
                         class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">PDF</a>
                    </li>
                  </ul>
                </div>
              </li>
              <!-- Simples -->
              <li>
                <button id="simpleDropdownButton-veiculo"
                        data-dropdown-toggle="simpleDropdown-veiculo"
                        data-dropdown-placement="right-start"
                        type="button"
                        class="flex items-center justify-between w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                  Simples
                  <svg class="w-2.5 h-2.5 ms-3 rtl:rotate-180"
                       aria-hidden="true"
                       xmlns="http://www.w3.org/2000/svg"
                       fill="none"
                       viewBox="0 0 6 10">
                    <path stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="m1 9 4-4-4-4" />
                  </svg>
                </button>
                <div id="simpleDropdown-veiculo"
                     class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                  <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                      aria-labelledby="simpleDropdownButton-veiculo">
                    <li>
                      <a href="#"
                         data-cv="<?= htmlspecialchars($id_caravan) ?>"
                         data-vh
                         data-file="xls"
                         data-type="simples"
                         data-vehicle-name="veiculo"
                         class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">XLS</a>
                    </li>
                    <li>
                      <a href="#"
                         data-cv="<?= htmlspecialchars($id_caravan) ?>"
                         data-vh
                         data-file="pdf"
                         data-type="simples"
                         data-vehicle-name="veiculo"
                         class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">PDF</a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="relative overflow-x-auto rounded-lg mb-8">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col"
                    class="p-3 text-nowrap text-left">
                  <div class="d-flex justify-content-between align-items-center">
                    # <a href="#"
                       class="sort"
                       data-column="banco"
                       data-order="asc"><i class="fa fa-sort"></i></a>
                  </div>
                </th>
                <th scope="col"
                    class="p-3 text-nowrap text-left">
                  <div class="d-flex justify-content-between align-items-center">
                    Nome <a href="#"
                       class="sort"
                       data-column="nome"
                       data-order="asc"><i class="fa fa-sort"></i></a>
                  </div>
                </th>
                <th scope="col"
                    class="p-3 text-nowrap text-left">
                  <div class="d-flex justify-content-between align-items-center">
                    Idade <a href="#"
                       class="sort"
                       data-column="idade"
                       data-order="asc"><i class="fa fa-sort"></i></a>
                  </div>
                </th>
                <th scope="col"
                    class="p-3 text-nowrap text-left">
                  <div class="d-flex justify-content-between align-items-center">
                    Ala <a href="#"
                       class="sort"
                       data-column="ala"
                       data-order="asc"><i class="fa fa-sort"></i></a>
                  </div>
                </th>
                <th scope="col"
                    class="p-3 text-nowrap ">Tipo Doc.</th>
                <th scope="col"
                    class="p-3 text-nowrap ">Doc.</th>
                <th scope="col"
                    class="p-3 text-nowrap ">Tel.</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($seats as $index => $seat): ?>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                  <td class="p-3 text-nowrap min-w-4 w-4 max-w-4"><?= $index + 1 ?></td>
                  <th scope="row"
                      class="p-3 font-medium text-gray-900 whitespace-nowrap dark:text-white text-nowrap min-w-24 w-24 max-w-24 truncate">
                    <?= htmlspecialchars($seat['passenger_name']) ?>
                  </th>
                  <td class="p-3 text-nowrap min-w-16 w-16 max-w-16"><?= htmlspecialchars($seat['age']) ?></td>
                  <td class="p-3 text-nowrap min-w-24 w-24 max-w-24 truncate"><?= htmlspecialchars($seat['ward_name']) ?></td>
                  <td class="p-3 text-nowrap min-w-16 w-16 max-w-16 truncate"><?= htmlspecialchars($seat['document_name']) ?></td>
                  <td class="p-3 text-nowrap min-w-32 w-32 max-w-32"><?= htmlspecialchars($seat['document']) ?></td>
                  <td class="p-3 text-nowrap min-w-32 w-32 max-w-32"><?= htmlspecialchars($seat['obs']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
    <script>
      $(document).ready(function () {

        function getCurrentDateFormatted() {
          var today = new Date();
          var day = String(today.getDate()).padStart(2, '0');
          var month = String(today.getMonth() + 1).padStart(2, '0'); // Janeiro é 0!
          var year = today.getFullYear();
          return `${year}-${month}-${day}`;
        }

        // Caminho da API para stake_edit
        var apiPath = "<?php echo $apiPath; ?>";

        // Caminho da API para stake_edit
        $('a[data-cv]').on('click', function (e) {
          e.preventDefault(); // Evita o comportamento padrão do link

          var vehicleId = $(this).data('cv');    // ID do veículo ou caravana
          var fileType = $(this).data('file');   // Tipo de arquivo (xls/pdf)
          var reportType = $(this).data('type'); // Tipo de relatório (completo/simples)
          var vehicleName = $(this).data('vehicle-name');
          var hasVH = $(this).is('[data-vh]');

          // Captura a data atual formatada
          var currentDate = getCurrentDateFormatted();

          // Requisição AJAX para gerar o arquivo
          $.ajax({
            url: apiPath,
            type: 'POST', // Mudar para POST se for necessário enviar dados
            data: {
              vehicleId: vehicleId,
              reportType: reportType,
              novehicles: hasVH,
              indicador: 'download_list'
            },
            success: function (data) {
              toast('loading', 'Preparando lista para baixar...');

              var filename = `${reportType}_${currentDate}_${vehicleName}`; // Definir o nome do arquivo sem extensão

              if (fileType === 'xls') {
                // Criar uma nova planilha
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.json_to_sheet(data);
                XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');

                // Criar um URL para o blob e forçar o download
                var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                var blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename + '.xlsx'; // Adiciona a extensão .xlsx
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
              } else if (fileType === 'pdf') {
                // Criar uma nova planilha temporária e adicionar dados
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.json_to_sheet(data);
                XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');

                // Criar o arquivo XLSX em memória
                var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });

                // Criar o PDF a partir do XLSX usando o jsPDF e autoTable
                const { jsPDF } = window.jspdf;
                var doc = new jsPDF({ orientation: reportType === 'completo' ? 'landscape' : 'portrait' });

                // Converter XLSX para JSON
                var dataAsArray = XLSX.read(wbout, { type: 'array' });
                var json = XLSX.utils.sheet_to_json(dataAsArray.Sheets['Sheet1']);

                if (json.length > 0) {
                  // Adicionar o conteúdo ao PDF
                  doc.autoTable({
                    head: [Object.keys(json[0])],
                    body: json.map(row => Object.values(row)),
                    startY: 20, // Ajuste a posição inicial se necessário
                    theme: 'grid', // Ajuste o tema conforme necessário
                    columnStyles: {
                      0: { halign: 'right' }, // Ajuste o alinhamento das colunas conforme necessário
                      1: { halign: 'left' },
                      // Adicione mais estilos de coluna se necessário
                    }
                  });

                  // Criar um URL para o blob e forçar o download
                  var blob = doc.output('blob');
                  var link = document.createElement('a');
                  link.href = window.URL.createObjectURL(blob);
                  link.download = filename + '.pdf'; // Adiciona a extensão .pdf
                  document.body.appendChild(link);
                  link.click();
                  document.body.removeChild(link);
                } else {
                  toast('error', 'Nenhum dado disponível para gerar o PDF.');
                }
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
              // console.error('Erro ao gerar arquivo:', error);
            }
          });
        });
      });
    </script>
  </body>

</html>