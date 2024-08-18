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

// Filtra os wards para excluir os que têm deleted_at
$wards = getWardsByUserId($user_id);
$activeWards = array_filter($wards, function ($ward) {
  return $ward['deleted_at'] === NULL; // Filtra as wards onde deleted_at é NULL
});

$conn = getDatabaseConnection();

// Preparar a consulta SQL para verificar os campos id_stake e id_ward
$sql = "SELECT id_stake, id_ward FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($id_stake, $id_ward);
$stmt->fetch();

// Definir a variável $showAlert com base no id_stake
$showAlert = empty($id_stake);

// Fechar a declaração e a conexão
$stmt->close();
$conn->close();
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
    <title>Ativar Ala - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Selecionar Ala</h1>
          <p class="text-gray-500">Determine qual ala você pertence para poder acessar demais funcionalidades de lider de ala.</p>
        </div>
        <!-- <button type="button"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full md:w-fit">Criar</button> -->
      </div>

      <?php if ($showAlert): ?>
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
             role="alert">
          <i class="fa fa-exclamation-triangle text-lg fa-fw me-3"></i>
          <span class="sr-only">Info</span>
          <div>
            <span class="font-medium">Estaca não ativada!</span> Para acessar os recursos do app, por favor, selecione uma estaca e ative-a.
          </div>
        </div>
      <?php endif; ?>

      <!-- tabela -->
      <div class="flex flex-col gap-4">

        <div class="p-4 bg-white rounded-lg shadow  flex flex-col  gap-2 w-full  relative-container">
          <form class="grid gap-4 grid-cols-2"
                id="ward_edit">

            <div class="col-span-2">
              <label for="id_ward"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ala atual</label>
              <select id="id_ward"
                      name="id_ward"
                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                      required>
                <option value=""
                        <?php echo empty($id_ward) ? 'selected' : ''; ?>>Selecione...</option>
                <?php foreach ($activeWards as $ward): ?>
                  <option value="<?php echo htmlspecialchars($ward['id']); ?>"
                          <?php echo $ward['id'] == $id_ward ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ward['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit"
                    class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Ativar Ala</button>
          </form>
        </div>

        <!-- <div class="flex items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600"
             role="alert">
          <i class="fa fa-exclamation-triangle text-lg fa-fw me-3"></i>
          <span class="sr-only">Info</span>
          <div>
            <span class="font-medium">Não encontrou sua estaca?</span>
            <p class="text-gray-600">Se a sua estaca não estiver na lista, por favor, envie uma mensagem pelo WhatsApp para <strong>45 98824-0321</strong> com o nome da estaca e o código.</p>
          </div>
        </div> -->

        <div id="alert-additional-content-4"
             class="p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800"
             role="alert">
          <div class="flex items-center">
            <i class="fa fa-info-circle text-lg fa-fw me-2"></i>
            <span class="sr-only">Info</span>
            <h3 class="text-lg font-medium">Não encontrou sua ala?</h3>
          </div>
          <div class="mt-2 text-sm">
            Se sua ala não estiver listada, informe aos líderes da estaca para garantir o registro correto.
          </div>
          <!--  <div class="flex">
             <a href="stake_lider_wards.php"
               class="text-white bg-yellow-800 hover:bg-yellow-900 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-yellow-300 dark:text-gray-800 dark:hover:bg-yellow-400 dark:focus:ring-yellow-800">
              Cadastrar Ala
            </a>
             <button type="button"
                    class="text-yellow-800 bg-transparent border border-yellow-800 hover:bg-yellow-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-yellow-300 dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-gray-800 dark:focus:ring-yellow-800"
                    data-dismiss-target="#alert-additional-content-4"
                    aria-label="Close">
              Dismiss
            </button>
          </div>-->
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

        // Editar ward
        $("#ward_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=ward_edit_user";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "loading") {
                  // $("#stake_edit")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // console.log(jsonResponse.status);
                  // Após 2 segundos (2000 milissegundos), recarregar a página
                  setTimeout(function () {
                    window.location.href = 'profile.php'; // Redireciona para a página profile.php
                  }, 2000); // 2000 milissegundos = 2 segundos
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