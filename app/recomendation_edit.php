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
// Guarda a role do usuário
$user_role = checkUserRole($user_id);
// Guarda o id da estaca
$user_stake = checkStake(user_id: $user_id); //retornar pra estaca senao tiver selecionado


$conn = getDatabaseConnection();

// Preparar a consulta SQL para guardar as pessoas
$sql = "SELECT id,name FROM passengers WHERE created_by = ? and deleted_at is null and barcode is not null and expiration_date is not null";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
// $stmt->bind_result($passengers);
// $stmt->fetch();

// Bind da variável para capturar o nome de cada passageiro
$stmt->bind_result($id, $name);

// Array para armazenar os passageiros
$passengers = [];

// Capturar todos os resultados
while ($stmt->fetch()) {
  $passengers[] = ['id' => $id, 'name' => $name];  // Adiciona cada nome de passageiro ao array
}

// Definir a variável $showAlert com base no id_stake
// $showAlert = empty($id_stake);

// Fechar a declaração e a conexão
$stmt->close();
$conn->close();


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
    <title>Ativar Ala - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <link rel="stylesheet"
          href="../resources/css.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Recomendação Digital</h1>
          <p class="text-gray-500">Carregue sua recomendação digitalmente e tenha acesso a ela em qualquer lugar.</p>
        </div>
        <!-- <button type="button"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full md:w-fit">Criar</button> -->
      </div>
      <!-- tabela -->
      <div class="flex flex-col gap-4">
        <!-- <div class="flex items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600"
             role="alert">
          <i class="fa fa-exclamation-triangle text-lg fa-fw me-3"></i>
          <span class="sr-only">Info</span>
          <div>
            <span class="font-medium">Não encontrou sua estaca?</span>
            <p class="text-gray-600">Se a sua estaca não estiver na lista, por favor, envie uma mensagem pelo WhatsApp para <strong>45 98824-0321</strong> com o nome da estaca e o código.</p>
          </div>
        </div> -->
        <div class="flex justify-center">
          <div class="bg-white rounded-lg shadow gap-4 w-full flex flex-row justify-center">
            <div class="flex flex-col gap-4  w-full max-w-[320px] p-8 sm:p-16"
                 id="recomendation">
              <h2 class="text-2xl serif leading-7"
                  id="name"></h2>
              <div class="flex flex-col space-y-0 text-gray-700">
                <p class=""
                   id="ward"></p>
                <p class=""
                   id="stake"></p>
              </div>
              <div class="flex flex-col space-y-0 text-gray-700">
                <p class="text-sm">Número do registro:</p>
                <p class=""
                   id="id_church"></p>
              </div>
              <div class="flex flex-row space-x-6 text-gray-700">
                <div class="flex flex-col">
                  <p class="text-sm">Válido até:</p>
                  <p class=""
                     id="expiration_date"></p>
                </div>
                <div class="flex flex-col">
                  <p class="text-sm">Sexo:</p>
                  <p class=""
                     id="sex"></p>
                </div>
              </div>
              <p class="hidden">8869481203</p>
              <div class="flex justify-center">
                <svg id="barcode"></svg>
              </div>
            </div>
          </div>
        </div>
        <div class="p-4 bg-white rounded-lg shadow  flex flex-row  gap-3 w-full  relative-container">
          <div class="grid gap-3 grid-cols-2 w-full">
            <div class="col-span-2">
              <label for="member"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pessoas com recomendação preenchida</label>
              <select id="member"
                      name="member"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                      required>
                <option value="">Selecione...</option>
                <?php foreach ($passengers as $passenger): ?>
                  <option value="<?php echo htmlspecialchars($passenger['id']); ?>">
                    <?php echo htmlspecialchars($passenger['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-span-2">
              <button type="button"
                      id="selectPassenger"
                      class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Revelar Recomendação</button>
            </div>
            <div class="col-span-2">
              <button type="button"
                      id="download"
                      class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 w-full text-center">
                <i class="fas fa-download w-3.5 h-3.5 me-2"></i>Baixar
              </button>
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

        $("#download").hide(); $("#recomendation").hide();

        // Evento para capturar o clique no botão de download
        $("#download").on("click", function () {
          const recomendationDiv = $("#recomendation")[0]; // Seleciona a div recomendation

          html2canvas(recomendationDiv).then(function (canvas) {
            // Converte o canvas para um URL de imagem
            const imgData = canvas.toDataURL("image/png");

            // Cria um link temporário para o download
            const $downloadLink = $("<a>")
              .attr("href", imgData)
              .attr("download", "recomendation.png");

            // Adiciona o link ao DOM, aciona o clique e remove o link
            $("body").append($downloadLink);
            $downloadLink[0].click();
            $downloadLink.remove();
          });
        });

        $('#selectPassenger').on('click', function () {
          const memberId = $('#member').val();//pega o value do membro

          if (memberId) {
            $.ajax({
              type: "POST",
              url: apiPath,
              data: {
                memberId: memberId,
                indicador: 'recomendation_get'
              },

              success: function (response) {
                const member = JSON.parse(response);
                // Preenche os campos com os dados recebidos
                $('#name').text(member.name);
                $('#id_church').text(member.id_church);
                $('#ward').text('Ala ' + member.ward_name);
                $('#stake').text('Estaca ' + member.stake_name);
                $('#sex').text(member.sex_name);


                // Converter expiration_date para o formato "MMM. YYYY"
                if (member.expiration_date) {
                  const [year, month] = member.expiration_date.split('-');

                  // Mapeia os meses para suas abreviações
                  const monthNames = [
                    'Jan.', 'Fev.', 'Mar.', 'Abr.', 'Mai.', 'Jun.',
                    'Jul.', 'Ago.', 'Set.', 'Out.', 'Nov.', 'Dez.'
                  ];

                  // Converte o mês (1-based index) para o nome abreviado
                  const formattedMonth = monthNames[parseInt(month, 10) - 1];

                  // Define o texto no elemento #expiration_date
                  $('#expiration_date').text(`${formattedMonth} ${year}`);
                } else {
                  $('#expiration_date').text('');
                }

                $("#recomendation").show();
                $("#download").show();

                // Gerar o código de barras usando JsBarcode
                if (member.barcode) {
                  JsBarcode("#barcode", member.barcode, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2,
                    height: 50,
                    displayValue: true
                  });
                } else {
                  // Se não houver código de barras, limpa o SVG
                  $('#barcode').empty();
                }
              },
              error: function () {
                alert('Erro ao buscar os dados do passageiro.');
              }
            });
          } else {
            // Limpa os campos e o código de barras se nenhum passageiro for selecionado
            // $('#name, #nasc_date, #sex, #id_document, #document, #obs, #mes, #ano').val('');
            $('#barcode').empty();
          }
        });

      });
    </script>
  </body>

</html>