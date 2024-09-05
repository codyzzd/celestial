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
$user_role = checkUserRole($user_id, 'stake_lider');

$wards = getWardsByUserId($user_id);
$activeWards = array_filter($wards, function ($ward) {
  return $ward['deleted_at'] === NULL; // Filtra as wards onde deleted_at é NULL
});
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
    <title>Estaca - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>
    <section class="max-w-lg container mx-auto p-4 pb-20">
      <div class="fixed end-4 bottom-24 group hidden">
      </div>
      <!-- header -->
      <div class="flex flex-col mb-4 gap-4">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Líderes</h1>
          <p class="text-gray-500">Gerencie permissões e controle acessos e funções dos usuários.</p>
        </div>
        <button type="button"
                data-modal-toggle="user_add_modal"
                data-modal-target="user_add_modal"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full ">Criar Link de Permissão</button>
      </div>
      <!-- tabela -->
      <div class="flex flex-col gap-4">
        <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y overflow-auto hidden"
             id="user_list">
          <!-- <button class="block w-full px-4 py-2  border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between items-center text-left">
            <div class="flex flex-row items-center">
              <i class="fa fa-user text-lg text-gray-500 fa-fw me-2"></i>
              <div class="flex flex-col ">
                <p>Bruno Gonçalves</p>
                <p class="text-sm text-gray-500">Lider da Estaca</p>
              </div>
            </div>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </button> -->
        </div>
        <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed hidden"
             id="empty_state">
          <i class="fa fa-user-gear text-3xl text-gray-500 mb-2"></i>
          <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Nenhum líder registrado ainda!</h5>
          <p class="text-gray-600 dark:text-gray-300 text-base">Lembre-se, até os apóstolos precisaram de ajuda para espalhar a palavra. Está na hora de escolher e cadastrar aqueles que caminharão ao seu lado!</p>
        </div>
      </div>
      <!-- modal add -->
      <div id="user_add_modal"
           data-modal-placement="bottom-center"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-lg max-h-full">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 rounded-t dark:border-gray-600 border-b">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Link de Permissão
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="user_add_modal">
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
            <form id="role_alt">
              <div class="grid gap-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <div class="flex items-start p-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-400 dark:border-yellow-800"
                       role="alert">
                    <i class="fa fa-exclamation-triangle text-lg fa-fw me-3"></i>
                    <span class="sr-only">Info</span>
                    <div>
                      O link é válido por até 7 dias e pode ser usado apenas uma vez, tanto por usuários com conta quanto por aqueles que ainda não têm uma conta.
                    </div>
                  </div>
                </div>
                <div class="col-span-2"
                     id="level_select">
                  <label for="inline-radio-group"
                         class="block mb-2 text-sm font-medium text-gray-900">Nivel de Hierarquia</label>
                  <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                    <!-- Primeiro item: Estaca -->
                    <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                      <div class="flex items-center ps-3">
                        <input id="horizontal-list-radio-estaca"
                               type="radio"
                               required
                               value="estaca"
                               name="level"
                               data-target="stake_form"
                               class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2">
                        <label for="horizontal-list-radio-estaca"
                               class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                          Estaca
                        </label>
                      </div>
                    </li>
                    <!-- Segundo item: Ala -->
                    <li class="w-full">
                      <div class="flex items-center ps-3">
                        <input id="horizontal-list-radio-ala"
                               type="radio"
                               required
                               value="ala"
                               name="level"
                               data-target="ward_form"
                               class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2">
                        <label for="horizontal-list-radio-ala"
                               class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                          Ala
                        </label>
                      </div>
                    </li>
                  </ul>
                </div>
                <div class="col-span-2">
                  <label for="id_ward"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ala Destino</label>
                  <select id="id_ward"
                          name="id_ward"
                          required
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5">
                    <option value=""
                            disabled
                            selected>Selecione...</option>
                    <?php foreach ($activeWards as $ward): ?>
                      <option value="<?php echo htmlspecialchars($ward['id']); ?>">
                        <?php echo htmlspecialchars($ward['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div id="stake_form"
                     class="col-span-2 hidden flex flex-col gap-4 ">
                  <div class="col-span-2">
                    <h3 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipo de Permissão</h3>
                    <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200"
                        aria-labelledby="dropdownHelperRadioButton"
                        id="stakes">
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="stake_lider-radio"
                                   name="permission-radio"
                                   type="radio"
                                   value="stake_lider"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="stake_lider-radio"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Líder da Estaca</div>
                              <p id="stake_lider-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Permissão total: adicionar usuários no nível de ala e estaca.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="stake_aux-radio"
                                   name="permission-radio"
                                   type="radio"
                                   value="stake_aux"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="stake_aux-radio"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Auxiliar da Estaca</div>
                              <p id="stake_aux-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Pode resetar senhas, gerar relatórios de nível estaca, administrar caravanas e veículos.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div id="ward_form"
                     class="col-span-2 hidden flex flex-col gap-4 ">
                  <div class="col-span-2 flex flex-col gap-4 ">
                    <div class="col-span-2 flex flex-col">
                      <h3 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipo de Permissão</h3>
                      <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200"
                          aria-labelledby="dropdownHelperRadioButton"
                          id="wards">
                        <li>
                          <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center h-5">
                              <input id="ward_lider-radio"
                                     name="permission-radio"
                                     type="radio"
                                     value="ward_lider"
                                     class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            </div>
                            <div class="ms-2 text-sm">
                              <label for="ward_lider-radio"
                                     class="font-medium text-gray-900 dark:text-gray-300">
                                <div>Líder da Ala</div>
                                <p id="ward_lider-description"
                                   class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                  Pode resetar senhas, gerar relatórios de nível ala e adicionar usuários no nível de ala.
                                </p>
                              </label>
                            </div>
                          </div>
                        </li>
                        <li>
                          <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center h-5">
                              <input id="ward_aux-radio"
                                     name="permission-radio"
                                     type="radio"
                                     value="ward_aux"
                                     class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            </div>
                            <div class="ms-2 text-sm">
                              <label for="ward_aux-radio"
                                     class="font-medium text-gray-900 dark:text-gray-300">
                                <div>Auxiliar da Ala</div>
                                <p id="ward_aux-description"
                                   class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                  Pode resetar senhas e gerar relatórios de nível ala.
                                </p>
                              </label>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-span-2 p-4 text-green-700 bg-green-100 hidden rounded-lg space-y-4 flex flex-col"
                     id="link_box">
                  <p id="link_role"
                     class="break-words text-xs"></p>
                  <button type="button"
                          id="copy_link"
                          class="px-5 py-2.5 text-sm font-medium text-green-900 focus:outline-none bg-white rounded-lg border border-green-200 hover:bg-green-100 hover:text-green-700 focus:z-10 focus:ring-4 focus:ring-green-100  w-full">Copiar link</button>
                </div>
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4
                          md:p-5
                          border-t
                          border-gray-200
                          rounded-b
                          dark:border-gray-600
                          justify-end
                          gap-3">
                <button type="button"
                        data-modal-toggle="user_add_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                  Criar Link
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- modal edit -->
      <div id="user_edit_modal"
           data-modal-placement="bottom-center"
           tabindex="-1"
           aria-hidden="true"
           class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-lg max-h-full">
          <!-- Modal content -->
          <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 rounded-t dark:border-gray-600 border-b">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Editar Permissão
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="user_edit_modal">
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
            <form id="role_alt_edit">
              <div class="grid gap-4 grid-cols-2 p-4">
                <div id="user_form_edit"
                     class="col-span-2 flex flex-col gap-4">
                  <div class="col-span-2">
                    <h3 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipo de Permissão</h3>
                    <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200"
                        aria-labelledby="dropdownHelperRadioButton">
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="stake_lider-radio_edit"
                                   name="permission-radio"
                                   type="radio"
                                   value="stake_lider"
                                   data-slug="stake_lider"
                                   required
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="stake_lider-radio_edit"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Líder da Estaca</div>
                              <p id="stake_lider-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Permissão total: adicionar usuários no nível de ala e estaca.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="stake_aux-radio_edit"
                                   name="permission-radio"
                                   type="radio"
                                   value="stake_aux"
                                   data-slug="stake_aux"
                                   required
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="stake_aux-radio_edit"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Auxiliar da Estaca</div>
                              <p id="stake_aux-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Pode resetar senhas, gerar relatórios de nível estaca, administrar caravanas e veículos.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="ward_lider-radio_edit"
                                   name="permission-radio"
                                   type="radio"
                                   value="ward_lider"
                                   data-slug="ward_lider"
                                   required
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="ward_lider-radio_edit"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Líder da Ala</div>
                              <p id="ward_lider-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Pode resetar senhas, gerar relatórios de nível ala e adicionar usuários no nível de ala.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="ward_aux-radio_edit"
                                   name="permission-radio"
                                   type="radio"
                                   value="ward_aux"
                                   required
                                   data-slug="ward_aux"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="ward_aux-radio_edit"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Auxiliar da Ala</div>
                              <p id="ward_aux-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Pode resetar senhas e gerar relatórios de nível ala.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="flex p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                          <div class="flex items-center h-5">
                            <input id="member-radio_edit"
                                   name="permission-radio"
                                   type="radio"
                                   value="member"
                                   required
                                   data-slug="null"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                          </div>
                          <div class="ms-2 text-sm">
                            <label for="member-radio_edit"
                                   class="font-medium text-gray-900 dark:text-gray-300">
                              <div>Membro</div>
                              <p id="member-description"
                                 class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                Pode se inscrever em caravanas e cadastrar pessoas.
                              </p>
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <input type="hidden"
                     id="id"
                     name="id" />
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-hide="user_edit_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                  Salvar
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
        // Esconde ambos os formulários inicialmente e remove o required dos campos
        $('#stake_form').hide().find('input, select').prop('required', false);
        $('#ward_form').hide().find('input, select').prop('required', false);

        // Função que exibe/esconde os formulários e alterna o atributo required
        $('input[name="level"]').on('change', function () {
          var target = $(this).data('target');

          // Esconde todos os formulários e remove o required dos campos
          $('#stake_form').hide().find('input, select').prop('required', false);
          $('#ward_form').hide().find('input, select').prop('required', false);

          // Mostra o formulário selecionado e adiciona required aos seus campos
          $('#' + target).show().find('input, select').prop('required', true);
        });

        // Se houver um nível já selecionado ao carregar a página, exibe o formulário correspondente
        var selectedLevel = $('input[name="level"]:checked').data('target');
        if (selectedLevel) {
          $('#' + selectedLevel).show().find('input, select').prop('required', true);
        }


        // Caminho da API para stake_edit
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";
        var stakeId = "<?php echo $stake_id; ?>";

        // gerar link de permissão
        $("#role_alt").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&stake_id=" + encodeURIComponent(stakeId) + "&indicador=role_alt";

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
                  $('#link_box').show();
                  const link = 'https://caravanacelestial.com.br/app/role_alt.php?id=' + jsonResponse.uuid;
                  $('#link_role').text(link);

                  // Copiar o link automaticamente para o clipboard
                  // navigator.clipboard.writeText(link)
                  //   .then(function () {
                  //     toast('success', 'Link copiado para o clipboard automaticamente!');
                  //   })
                  //   .catch(function (error) {
                  //     toast('error', 'Erro ao copiar o link: ' + error);
                  //   });

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

        // alterar role de usuário
        $("#role_alt_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=role_alt_edit";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            dataType: "json", // Esperar resposta JSON
            success: function (response) {
              // Verificar o status da resposta e mostrar o toast apropriado
              if (response.status === "success") {
                toast(response.status, response.msg);

                const userEditModal = new Modal(document.getElementById('user_edit_modal'));
                userEditModal.hide();

                updateUsersList();
              } else if (response.status === "error") {
                toast(response.status, response.msg);
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        });

        // Adiciona um ouvinte de eventos para o documento
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para mostrar o modal
          const showModalTarget = event.target.closest('[data-modal-target="user_edit_modal"]');

          if (showModalTarget) {
            const userIdedit = showModalTarget.getAttribute('data-id');

            $.ajax({
              type: "POST",
              url: apiPath,
              data: {
                user_id: userIdedit,
                indicador: 'user_get'
              },
              success: function (response) {
                // Parse the response to JSON if needed (if it's not already an object)
                const userData = typeof response === 'string' ? JSON.parse(response) : response;

                // Preencher o campo id com o valor userData.id
                $('#role_alt_edit #id').val(userData.id);

                // Encontrar o botão de rádio com o data-slug correspondente e marcá-lo
                const slug = userData.slug;
                const $radioButton = $(`input[type="radio"][data-slug="${slug}"]`);
                if ($radioButton.length) {
                  $radioButton.prop('checked', true);
                }

                // Inicializa e exibe o modal após os dados terem sido carregados
                const userEditModal = new Modal(document.getElementById('user_edit_modal'));
                userEditModal.show();
              }
            });
          }

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para ocultar o modal
          const hideModalTarget = event.target.closest('[data-modal-hide="user_edit_modal"]');
          if (hideModalTarget) {
            // Inicializa e oculta o modal
            const passengerEditModal = new Modal(document.getElementById('user_edit_modal'));
            passengerEditModal.hide();
          }
        });


        $('#copy_link').on('click', function () {
          const linkText = $('#link_role').text();

          // Copiar o texto para o clipboard
          navigator.clipboard.writeText(linkText)
            .then(function () {
              // Sucesso ao copiar
              toast('success', 'Link copiado para o clipboard!');
            })
            .catch(function (error) {
              // Erro ao copiar
              toast('error', 'Erro ao copiar o link: ' + error);
            });
        });

        function updateUsersList() {
          // Serializar os campos do formulário
          var formData = "user_id=" + encodeURIComponent(userId) + "&stake_id=" + encodeURIComponent(stakeId) + "&indicador=user_list_stake";

          $.ajax({
            type: "POST",
            url: apiPath,  // Substitua pelo caminho da sua API
            data: formData,
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                var userListHTML = '';

                // Verifica se a resposta é de sucesso e há pelo menos 1 usuário
                if (jsonResponse.status === "success" && jsonResponse.data && jsonResponse.data.length > 0) {
                  var users = jsonResponse.data;

                  users.forEach(function (user) {
                    userListHTML += `
                            <button class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between items-center text-left" data-modal-target="user_edit_modal" data-id="${user.id}">
                                <div class="flex flex-row items-center truncate">
                                    <div class="flex flex-col truncate w-full">
                                        <p class="truncate">${user.user_name}</p>
                                        <p class="text-sm text-gray-500">${user.role_name}</p>
                                    </div>
                                </div>
                                <i class="fa fa-chevron-right text-lg text-gray-500"></i>
                            </button>`;
                  });

                  // Esconde o `#empty_state` se houver 2 ou mais usuários
                  if (users.length >= 2) {
                    $('#empty_state').hide();
                  } else {
                    $('#empty_state').show();
                  }

                  // Exibe a lista de usuários
                  $('#user_list').html(userListHTML).show();
                } else {
                  // Se não houver usuários, esconde a lista de usuários e mostra o estado vazio
                  $('#user_list').hide();
                  $('#empty_state').show();
                }
              } catch (e) {
                // Em caso de erro no parsing do JSON, exibe uma mensagem de erro e mostra o estado vazio
                toast('error', 'Erro ao processar a resposta do servidor.');
                $('#user_list').hide();
                $('#empty_state').show();
              }
            },
            error: function () {
              // Em caso de erro na solicitação AJAX, exibe uma mensagem de erro e mostra o estado vazio
              toast('error', 'Erro ao enviar a solicitação.');
              $('#user_list').hide();
              $('#empty_state').show();
            }
          });
        }

        updateUsersList();

      });
    </script>
  </body>

</html>