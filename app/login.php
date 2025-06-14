<?php
// Pega caminho da pasta
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
$apiPath = "../resources/api_user.php";

// Inicia ou continua a sessão PRIMEIRO
session_start();

// Inclui as funções necessárias
require_once ROOT_PATH . '/resources/functions.php';

// Conectar ao banco de dados
$conn = getDatabaseConnection();

// Verifica se já está logado via sessão
if (isset($_SESSION['user_id'])) {
  header("Location: panel.php");
  exit();
}

// Verifica se o cookie de token está definido
if (isset($_COOKIE['caravana_remember_token'])) {
  $token = $_COOKIE['caravana_remember_token'];

  // Preparar a consulta para verificar se o token é válido
  $stmt = $conn->prepare("SELECT id FROM users WHERE remember_token = ? AND remember_token IS NOT NULL AND remember_token != ''");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // Token válido, obtém o ID do usuário
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Regenera a sessão por segurança
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;

    // Opcional: Gerar um novo token para maior segurança
    $newToken = hash('sha256', uniqid(bin2hex(random_bytes(16)), true) . time());
    $updateStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newToken, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    // Atualizar o cookie com o novo token
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    $domain = $_SERVER['HTTP_HOST'];

    if (strpos($domain, 'localhost') !== false || strpos($domain, '127.0.0.1') !== false) {
      $domain = '';
    }

    $cookieOptions = [
      'expires' => time() + (86400 * 30),
      'path' => '/',
      'httponly' => true,
      'samesite' => 'Lax'
    ];

    if ($isSecure) {
      $cookieOptions['secure'] = true;
    }
    if (!empty($domain)) {
      $cookieOptions['domain'] = $domain;
    }

    setcookie('caravana_remember_token', $newToken, $cookieOptions);

    // Redireciona para a página do painel
    header("Location: panel.php");
    exit();
  } else {
    // Token inválido, remove o cookie
    setcookie('caravana_remember_token', '', [
      'expires' => time() - 3600,
      'path' => '/',
      'httponly' => true
    ]);
  }
  $stmt->close();
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
    <title>Login - Caravana Celestial</title>
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="flex flex-col h-dvh p-4 items-center justify-end md:justify-center">
      <!-- caixa -->
      <div class="p-4 bg-white shadow  flex flex-col gap-4 w-full sm:max-w-md rounded-lg mb-4">
        <form class="flex flex-col gap-4"
              id="login"
              autocomplete="on">
          <div>
            <label for="email"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                   placeholder="ex: bruno@email.com"
                   autocomplete="email"
                   required />
          </div>
          <div>
            <label for="password"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Senha</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                   placeholder="•••••••••"
                   autocomplete="current-password"
                   required />
          </div>
          <div class="flex justify-between">
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input id="remember_token"
                       type="checkbox"
                       name="remember_token"
                       value="remember_token"
                       checked
                       class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-purple-300 dark:bg-gray-600 dark:border-gray-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" />
              </div>
              <label for="remember_token"
                     data-tooltip-target="tooltip-remember-token"
                     class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Permancer logado</label>
              <div id="tooltip-remember-token"
                   role="tooltip"
                   class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Ao deixar ativado, você permanecerá logado neste dispositivo ao menos que você de SAIR na tela de perfil.
                <div class="tooltip-arrow"
                     data-popper-arrow></div>
              </div>
            </div>
            <a href="reset_password.php"
               class="text-sm text-purple-700 hover:underline dark:text-purple-500">Esqueci a senha</a>
          </div>
          <button type="submit"
                  class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full ">Entrar</button>
          <p class="text-sm font-light text-gray-500 dark:text-gray-400">
            Não tem conta ainda? <a href="signup.php"
               class="font-medium text-purple-600 hover:underline dark:text-primary-500">Criar Conta</a>
          </p>
        </form>
      </div>
    </section>
    <?php //require_once ROOT_PATH . '/section/advanced_menu_bottom.php'; ?>
    <?php
    require_once ROOT_PATH . '/resources/body_flowbitejs.php';
    ?>
    <script>
      $(document).ready(function () {
        // Caminho da API passado pelo PHP
        var apiPath = "<?php echo $apiPath; ?>";

        // Login do usuário
        $("#login").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente
          var formData = $(this).serialize(); // Serializar os campos do formulário

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData + "&indicador=user_login", // Incluir os campos serializados e o indicador para login
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "loading") {
                  toast(jsonResponse.status, jsonResponse.msg);
                  // Esperar 2 segundos antes de redirecionar (reduzido de 3 para 2)
                  setTimeout(function () {
                    window.location.href = "panel.php"; // Redirecionar para a página após o login
                  }, 2000); // 2000 milissegundos = 2 segundos
                } else if (jsonResponse.status === "error") {
                  toast(jsonResponse.status, jsonResponse.msg);
                }
              } catch (e) {
                console.error("Erro ao processar resposta:", e);
                console.log("Resposta recebida:", response);
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
            },
            error: function (xhr, status, error) {
              console.error("Erro AJAX:", error);
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        });
      });
    </script>
  </body>

</html>