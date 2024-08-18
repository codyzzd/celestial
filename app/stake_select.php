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
$conn = getDatabaseConnection();


// Verificar se o usuário está logado e obter o ID do usuário
$user_id = checkUserLogin();
// Guarda a role do usuário
$user_role = checkUserRole($user_id);

// conectando no banco
// require_once ROOT_PATH . '/resources/dbcon.php';

// Consultar o banco de dados para verificar se o id_stake do usuário está vazio
$stmt = $conn->prepare("SELECT id_stake FROM users WHERE id = ?");

if (!$stmt) {
  // Tratar erro ao preparar a consulta
  die("Falha na preparação da consulta: " . $conn->error);
}

$stmt->bind_param('s', $user_id); // 'i' indica que o parâmetro é um inteiro
$stmt->execute();

$result = $stmt->get_result();
if ($result === false) {
  // Tratar erro ao obter o resultado
  die("Falha ao obter o resultado: " . $stmt->error);
}

$id_stake = $result->fetch_assoc()['id_stake'] ?? null;
$stmt->close(); // Fechar a declaração

// Definir uma variável para controle do alerta
$showAlert = empty($id_stake);

// Se $id_stake não estiver vazio, buscar o nome e o código da estaca
$stake_name = '';
if (!empty($id_stake)) {
  $stmt = $conn->prepare("SELECT cod, name FROM stakes WHERE id = ?");
  if (!$stmt) {
    // Tratar erro ao preparar a consulta
    die("Falha na preparação da consulta: " . $conn->error);
  }

  $stmt->bind_param('s', $id_stake);
  $stmt->execute();

  $result = $stmt->get_result();
  if ($result === false) {
    // Tratar erro ao obter o resultado
    die("Falha ao obter o resultado: " . $stmt->error);
  }

  $stake = $result->fetch_assoc();
  $stake_name = $stake ? $stake['name'] . ' - ' . $stake['cod'] : '';
  $stmt->close(); // Fechar a declaração
}
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
    <title>Ativar Estaca - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
    <style>
      .relative-container {
        position: relative;
      }

      #stake {
        width: 100%;
        /* Garante que o input ocupa toda a largura do contêiner */
      }

      #dropdown {
        position: absolute;
        /*top: 100%;*/
        /* Posiciona o dropdown logo abaixo do input */
        left: 0;
        width: 100%;
        /* Garante que o dropdown tenha a mesma largura do input */
        box-sizing: border-box;
        /* Inclui padding e bordas na largura total */
      }
    </style>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">

      <!-- header -->
      <div class="flex flex-col mb-4 md:flex-row space-y-4 md:space-x-4 md:justify-between ">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Selecionar Estaca</h1>
          <p class="text-gray-500">Determine a qual estaca você está vinculado para poder acessar demais funcionalidades.</p>
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
                id="stake_edit">

            <div class="col-span-2">

              <label for="stake"
                     class="block mb-2 text-sm font-medium text-gray-900 dark:text-white col-span-2 ">Estaca atual</label>
              <div class="relative  col-span-2 ">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                  <i class="fa fa-people-group text-base text-gray-500"></i>
                </div>
                <input type="text"
                       id="stake"
                       name="stake"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                       placeholder="Digite para escolher..."
                       value="<?php echo htmlspecialchars($stake_name); ?>"
                       required
                       autocomplete="off">
                <div id="dropdown"
                     class="absolute left-0 z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-full dark:bg-gray-700">
                  <ul id="dropdown-menu"
                      class="py-2 text-sm text-gray-700 dark:text-gray-200"></ul>
                </div>
              </div>

            </div>
            <input type="hidden"
                   id="stake_id"
                   name="stake_id">
            <button type="submit"
                    class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full col-span-2">Ativar esta Estaca</button>
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
        </div>//test -->

        <div id="alert-additional-content-4"
             class="p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800"
             role="alert">
          <div class="flex items-center">
            <i class="fa fa-info-circle text-lg fa-fw me-2"></i>
            <span class="sr-only">Info</span>
            <h3 class="text-lg font-medium">Não encontrou sua estaca?</h3>
          </div>
          <div class="mt-2 mb-4 text-sm">
            Se você não encontrou a sua estaca, é necessário cadastrá-la. Por favor, utilize o botão abaixo para ir à tela de cadastro e adicionar as informações da sua estaca.
          </div>
          <div class="flex">
            <a href="stake_add.php"
               class="text-white bg-yellow-800 hover:bg-yellow-900 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-yellow-300 dark:text-gray-800 dark:hover:bg-yellow-400 dark:focus:ring-yellow-800">

              Cadastrar Estaca
            </a>
            <!-- <button type="button"
                    class="text-yellow-800 bg-transparent border border-yellow-800 hover:bg-yellow-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-yellow-300 dark:border-yellow-300 dark:text-yellow-300 dark:hover:text-gray-800 dark:focus:ring-yellow-800"
                    data-dismiss-target="#alert-additional-content-4"
                    aria-label="Close">
              Dismiss
            </button> -->
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

        // Dropdown e campos relacionados
        const $input = $('#stake');
        const $dropdown = $('#dropdown');
        const $dropdownMenu = $('#dropdown-menu');
        const $stakeId = $('#stake_id'); // Campo oculto para armazenar o data-id
        let timeout;

        $input.on('input', function () {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            const query = $input.val();
            if (query.length < 2) {
              $dropdown.addClass('hidden');
              return;
            }

            $.ajax({
              url: '../resources/fetch_stakes.php',
              data: { term: query },
              dataType: 'json',
              success: function (data) {
                $dropdownMenu.empty();
                data.forEach(item => {
                  const $li = $('<li>');
                  $li.html(`<a href="#" data-id="${item.id}" class="block px-4 py-2 hover:bg-gray-100">${item.name} - ${item.cod}</a>`);
                  $dropdownMenu.append($li);
                });
                $dropdown.toggleClass('hidden', data.length === 0);
              },
              error: function (xhr, status, error) {
                console.error('Error fetching data:', error);
              }
            });
          }, 300); // Timeout para evitar chamadas excessivas
        });

        $dropdownMenu.on('click', 'a', function (event) {
          event.preventDefault();
          $input.val($(this).text());
          $stakeId.val($(this).data('id')); // Atualizar o campo oculto com o data-id
          $dropdown.addClass('hidden');
        });

        $(document).on('click', function (event) {
          if (!$input.is(event.target) && !$dropdown.is(event.target) && !$dropdown.has(event.target).length) {
            $dropdown.addClass('hidden');
          }
        });

        // Editar stake
        $("#stake_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=stake_edit";

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
                    location.reload(); // Recarrega a página
                  }, 2000); // 2000 milissegundos = 3 segundos
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