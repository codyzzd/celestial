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
$stake_id = checkStake($user_id);

// Guarda a role do usuário
$user_role = checkUserRole($user_id, ['stake_lider']);

$wards = getWardsByUserId($user_id);

// pegar top 10 viajantes
$top10viajantes = getTop10passengers($stake_id);
$topWardsViajantes = getTopWardsViajantes($stake_id);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0<?php if (isMobile())
            echo ', user-scalable=no'; ?>">
    <title>Analytics - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-50 dark:bg-gray-900">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 mb-20 flex flex-col gap-4">
      <!-- header -->
      <div class="flex flex-col  md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Analytics</h1>
          <p class="text-gray-500">Confira as estatísticas da sua estaca.</p>
        </div>
      </div>
      <!-- gerador de relatorio -->
      <div class="p-4 bg-white rounded-lg shadow  flex flex-col  gap-2 w-full  relative-container">
        <form class="grid gap-4 grid-cols-2"
              id="ward_edit"
              action="stake_analytics_report.php"
              method="GET"
              target="_blank">
          <div class="col-span-2">
            <label for="id_ward"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gerador de Relatório</label>
            <select id="report_type"
                    name="report"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                    required>
              <option value="">Selecione o tipo de relatório...</option>
              <option value="3months">Viajantes nos últimos 3 meses</option>
              <option value="6months">Viajantes nos últimos 6 meses</option>
            </select>
          </div>
          <button type="submit"
                  class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Abrir</button>
        </form>
      </div>
      <!-- Top 10 Passengers Table -->
      <h2 class="text-lg font-semibold text-gray-900">Top 5 Viajantes (últimos 12 meses)</h2>
      <div class="relative overflow-x-auto rounded-lg mb-8 border-gray-700">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
              <th scope="col"
                  class="px-4 py-3">#</th>
              <th scope="col"
                  class="px-4 py-3">Nome</th>
              <th scope="col"
                  class="px-4 py-3">Viagens</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top10viajantes as $index => $passenger): ?>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-4 py-3">
                  <?= $index + 1 ?>
                </td>
                <td class="px-4 py-3">
                  <div class="flex flex-col">
                    <span class="font-medium text-gray-900 dark:text-white">
                      <?php
                      $name_parts = explode(' ', $passenger['passenger_name']);
                      $first = $name_parts[0];
                      $last = end($name_parts);
                      echo htmlspecialchars($first . ' ' . $last);
                      ?>
                    </span>
                    <span class="text-sm text-gray-500">
                      <?= htmlspecialchars($passenger['ward_name']) ?>
                    </span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <?= htmlspecialchars($passenger['total_seats']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Top Alas Table -->
      <h2 class="text-lg font-semibold text-gray-900">Top Alas (últimos 12 meses)</h2>
      <div class="relative overflow-x-auto rounded-lg mb-8">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 ">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
              <th scope="col"
                  class="px-4 py-3">#</th>
              <th scope="col"
                  class="px-4 py-3">Ala</th>
              <th scope="col"
                  class="px-4 py-3">Viajantes</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $maxPassengers = max(array_column($topWardsViajantes, 'total_passengers'));
            foreach ($topWardsViajantes as $index => $ward):
              $percentage = ($ward['total_passengers'] / $maxPassengers) * 100;
              ?>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="px-4 py-3">
                  <?= $index + 1 ?>
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                  <?= htmlspecialchars($ward['ward_name']) ?>
                </td>
                <td class="px-4 py-3">
                  <div class="flex flex-col gap-1">
                    <span><?= htmlspecialchars($ward['total_passengers']) ?></span>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                      <div class="bg-purple-600 h-2 rounded-full"
                           style="width: <?= $percentage ?>%"></div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php require_once ROOT_PATH . '/section/normal_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
  </body>

</html>