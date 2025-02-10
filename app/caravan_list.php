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

// organizando seats
// Supondo que $seats é um array com todos os assentos
$groupedSeats = [];

// Agrupa os assentos por veículo
foreach ($seats as $seat) {
  $vehicleName = $seat['vehicle_name'];

  if (!isset($groupedSeats[$vehicleName])) {
    $groupedSeats[$vehicleName] = [];
  }

  $groupedSeats[$vehicleName][] = $seat;
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
      <!-- tabela -->
      <?php foreach ($groupedSeats as $vehicleName => $vehicleSeats): ?>
        <div class="header-list flex flex-col md:flex-row md:justify-between mb-2 gap-2 md:items-center">
          <p class="text-sm text-gray-500">Lista: <?= htmlspecialchars($vehicleName) ?></p>
          <!-- <div class=" flex flex-row gap-3"
               id="downs">
            <button class="downxls text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 w-fit"
                    data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                    data-file="xls">
              <i class="fa fa-download me-2"></i>XLS</button>
            <button class="downpdf text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 w-fit"
                    data-cv="<?= htmlspecialchars($seat['vehicle_id']) ?>"
                    data-file="pdf">
              <i class="fa fa-download me-2"></i>PDF</button>
          </div> -->
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
                    class="p-3 text-nowrap">#</th>
                <th scope="col"
                    class="p-3 text-nowrap text-left">
                  <div class="d-flex justify-content-between align-items-center">
                    Nome <a href="#"><i class="fa fa-sort"></i></a>
                  </div>
                </th>
                <th scope="col"
                    class="p-3 text-nowrap">Idade <a href="#"><svg class="w-3 h-3 ms-1.5"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">
                      <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                    </svg></a></th>
                <th scope="col"
                    class="p-3 text-nowrap ">Ala<a href="#"><svg class="w-3 h-3 ms-1.5"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">
                      <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                    </svg></a></th>
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

          var vehicleId = $(this).data('cv');    // ID do veículo
          var fileType = $(this).data('file');   // Tipo de arquivo (xls/pdf)
          var reportType = $(this).data('type'); // Tipo de relatório (completo/simples)
          var vehicleName = $(this).data('vehicle-name');

          // Captura a data atual formatada
          var currentDate = getCurrentDateFormatted();

          // Requisição AJAX para gerar o arquivo
          $.ajax({
            url: apiPath,
            type: 'POST', // Mudar para POST se for necessário enviar dados
            data: {
              vehicleId: vehicleId,
              reportType: reportType,
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