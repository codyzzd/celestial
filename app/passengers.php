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


// Select documentos
$documents = getDocuments();

// Select generos
$sexs = getSexs();

// Select relations
$relations = getRelations();
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
    <title>Pessoas - Caravana Celestial</title>
    <link rel="manifest"
          href="manifest.json">
  </head>

  <body class="bg-gray-100">
    <?php require_once ROOT_PATH . '/resources/body_removedark.php'; ?>
    <?php require_once ROOT_PATH . '/resources/toast.php'; ?>

    <?php //require_once ROOT_PATH . '/section/nav.php'; ?>

    <section class="max-w-lg container mx-auto p-4 pb-20">
      <div class="fixed end-4 bottom-24 group md:hidden">

        <?php
        $showButton = false;

        if (!empty($wards)) {
          // Verifica se há pelo menos uma ward com deleted_at NULL
          foreach ($wards as $ward) {
            if ($ward['deleted_at'] === NULL) {
              $showButton = true;
              break; // Não precisa verificar mais, já encontrou uma ward válida
            }
          }
        }

        if ($showButton): ?>
          <button type="button"
                  id="passenger_add_modal_fob"
                  data-modal-toggle="passenger_add_modal"
                  data-modal-target="passenger_add_modal"
                  class="flex items-center justify-center text-white bg-purple-700 rounded-full w-14 h-14 hover:bg-purple-800 dark:bg-purple-600 dark:hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 focus:outline-none dark:focus:ring-purple-800">
            <i class="fa fa-plus transition-transform text-2xl"></i>
            <span class="sr-only">Open actions menu</span>
          </button>
        <?php endif; ?>
      </div>

      <!-- header -->
      <div class="flex flex-col mb-4 gap-4">
        <div class="flex-col gap-1">
          <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Família e Amigos</h1>
          <p class="text-gray-500">Administre os passageiros da sua família e amigos de forma simples e eficiente.</p>
        </div>
        <button type="button"
                data-modal-toggle="passenger_add_modal"
                data-modal-target="passenger_add_modal"
                class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800 w-full hidden md:block">Adicionar Passageiro</button>
      </div>

      <div class="flex flex-col gap-4">

        <div class="flex flex-col gap-2"
             id="family_list">
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white">Família</h2>

          <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y"
               id="passenger_list_family">

          </div>
        </div>

        <div class="flex flex-col gap-2"
             id="friend_list">
          <h2 class=" text-lg font-semibold text-gray-900 dark:text-white">Amigos</h2>

          <div class="w-full text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white divide-y"
               id="passenger_list_friend">

          </div>

        </div>

        <!-- empty state de alas -->
        <?php
        $showAla = false;

        if (!empty($wards)) {
          // Verifica se há pelo menos uma ward com deleted_at NULL
          foreach ($wards as $ward) {
            if ($ward['deleted_at'] === NULL) {
              $showAla = true;
              break; // Não precisa verificar mais, encontrou uma ward válida
            }
          }
        }

        // Exibe o estado vazio se não houver wards ou se todas as wards estiverem com deleted_at preenchido
        if (!$showAla): ?>
          <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed"
               id="empty_state_ala">
            <i class="fa fa-tents text-3xl text-gray-500 mb-2"></i>

            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Sem alas por aqui!</h5>
            <p class="text-gray-600 dark:text-gray-300 text-base">Parece que os líderes ainda não adicionaram as alas. Vamos torcer para que eles não estejam esperando um sinal, como Moisés no deserto!</p>
          </div>
        <?php endif; ?>

        <!-- empty state de pessoas -->
        <div class="p-4 rounded-lg flex flex-col w-full  border-[2px] border-gray-300 border-dashed hidden"
             id="empty_state">
          <i class="fa fa-people-roof text-3xl text-gray-500 mb-2"></i>

          <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Ninguém por aqui!</h5>
          <p class="text-gray-600 dark:text-gray-300 text-base">Parece que você ainda não cadastrou nenhum familiar ou amigo para a caravana. Como você vai encher o ônibus assim? Nem Noé deixou de chamar a família para a arca!</p>
        </div>
      </div>

      <!-- modal add -->
      <div id="passenger_add_modal"
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
                Adicionar Pessoa
              </h3>
              <button type="button"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="passenger_add_modal">
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
                  id="passenger_add">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">

                <div class="col-span-2">
                  <label for="inline-radio-group"
                         class="block mb-2 text-sm font-medium text-gray-900">Relação</label>
                  <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                    <?php
                    // Iterar sobre os relations
                    foreach ($relations as $relation): ?>
                      <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                        <div class="flex items-center ps-3">
                          <input id="horizontal-list-radio-<?php echo $relation['slug']; ?>"
                                 type="radio"
                                 value="<?php echo $relation['id']; ?>"
                                 name="id_relationship"
                                 class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2"
                                 required>
                          <label for="horizontal-list-radio-<?php echo $relation['slug']; ?>"
                                 class="w-full py-3 ms-2 text-sm font-medium text-gray-900">

                            <?php echo $relation['name']; ?>
                          </label>
                        </div>
                      </li>
                    <?php endforeach; ?>

                  </ul>
                </div>
                <div class="col-span-2">
                  <label for="church"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Número de Membro</label>
                  <input type="text"
                         id="id_church"
                         name="id_church"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                         placeholder="ex: 123-1234-1234"
                         autocomplete="mrn"
                         style="text-transform: uppercase;"
                         required />
                </div>

                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900">Nome Completo</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="ex: Bruno da Silva Gonçalves"
                         required
                         autocomplete="off">
                </div>

                <div class="col-span-2">
                  <label for="nasc_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data de Nascimento</label>
                  <input type="text"
                         id="nasc_date"
                         name="nasc_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         required
                         autocomplete="off">
                </div>

                <div class="col-span-2">
                  <label for="inline-radio-group"
                         class="block mb-2 text-sm font-medium text-gray-900">Gênero</label>
                  <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                    <?php
                    // Iterar sobre os sexs
                    foreach ($sexs as $sex):
                      // Usar sintaxe ternária para definir ícone e cor
                      $icon = ($sex['slug'] == 'masc') ? 'fa-mars' : (($sex['slug'] == 'femi') ? 'fa-venus' : '');
                      $color = ($sex['slug'] == 'masc') ? 'text-blue-700' : (($sex['slug'] == 'femi') ? 'text-pink-700' : '');
                      ?>
                      <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                        <div class="flex items-center ps-3">
                          <input id="horizontal-list-radio-<?php echo $sex['slug']; ?>"
                                 type="radio"
                                 value="<?php echo $sex['id']; ?>"
                                 name="sex"
                                 class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2"
                                 required>
                          <label for="horizontal-list-radio-<?php echo $sex['slug']; ?>"
                                 class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                            <i class="fa <?php echo $icon; ?> fa-fw <?php echo $color; ?>"></i>
                            <?php echo $sex['name']; ?>
                          </label>
                        </div>
                      </li>
                    <?php endforeach; ?>

                  </ul>
                </div>

                <div class="col-span-2">
                  <label for="id_ward"
                         class="block mb-2 text-sm font-medium text-gray-900">Qual ala pertence?</label>
                  <select id="id_ward"
                          name="id_ward"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                          required>
                    <option value=""
                            selected>Selecione...</option>
                    <?php foreach ($activeWards as $ward): ?>
                      <option value="<?php echo $ward['id']; ?>"><?php echo $ward['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-span-2">
                  <label for="id_document"
                         class="block mb-2 text-sm font-medium text-gray-900">Tipo de Documento</label>
                  <select id="id_document"
                          name="id_document"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                          required>
                    <option value=""
                            selected>Selecione...</option>
                    <?php foreach ($documents as $document): ?>
                      <option value="<?php echo $document['id']; ?>"><?php echo $document['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-span-2">
                  <label for="document"
                         class="block mb-2 text-sm font-medium text-gray-900">Documento</label>
                  <input type="text"
                         id="document"
                         name="document"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         required
                         autocomplete="off">
                </div>

                <!-- <div class="col-span-2">
                  <label for="fever_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data da Vacina da Febre Amarela</label>
                  <input type="text"
                         id="fever_date"
                         name="fever_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         autocomplete="off">
                         <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">We’ll never share your details. Read our <a href="#" class="font-medium text-purple-600 hover:underline dark:text-purple-500">Privacy Policy</a>.</p>
                </div> -->

                <div class="col-span-2">
                  <label for="obs"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
                  <textarea id="obs"
                            name="obs"
                            rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"></textarea>
                </div>

              </div>

              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button type="button"
                        data-modal-toggle="passenger_add_modal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
                  Adicionar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- modal edit -->
      <div id="passenger_edit_modal"
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
                Editar Pessoa
              </h3>
              <button type="button"
                      id="close_edit"
                      class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                      data-modal-hide="passenger_edit_modal">
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
                  id="passenger_edit">
              <div class="grid gap-4 mb-4 grid-cols-2 p-4">
                <div class="col-span-2">
                  <label for="inline-radio-group"
                         class="block mb-2 text-sm font-medium text-gray-900">Relação</label>
                  <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                    <?php
                    // Iterar sobre os relations
                    foreach ($relations as $relation): ?>
                      <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                        <div class="flex items-center ps-3">
                          <input id="horizontal-list-edit-radio-<?php echo $relation['slug']; ?>"
                                 type="radio"
                                 value="<?php echo $relation['id']; ?>"
                                 name="id_relationship"
                                 class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2"
                                 required>
                          <label for="horizontal-list-edit-radio-<?php echo $relation['slug']; ?>"
                                 class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                            <?php echo $relation['name']; ?>
                          </label>
                        </div>
                      </li>
                    <?php endforeach; ?>

                  </ul>
                </div>
                <div class="col-span-2">
                  <label for="church"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Número de Membro</label>
                  <input type="text"
                         id="id_church"
                         name="id_church"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"
                         placeholder="ex: 123-1234-1234"
                         autocomplete="mrn"
                         style="text-transform: uppercase;"
                         required />
                </div>
                <div class="col-span-2">
                  <label for="name"
                         class="block mb-2 text-sm font-medium text-gray-900">Nome Completo</label>
                  <input type="text"
                         id="name"
                         name="name"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="ex: Bruno da Silva Gonçalves"
                         required
                         autocomplete="off">
                </div>

                <div class="col-span-2">
                  <label for="nasc_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data de Nascimento</label>
                  <input type="text"
                         id="nasc_date"
                         name="nasc_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         required
                         autocomplete="off">
                </div>

                <div class="col-span-2">
                  <label for="inline-radio-group"
                         class="block mb-2 text-sm font-medium text-gray-900">Gênero</label>
                  <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                    <?php
                    // Iterar sobre os sexs
                    foreach ($sexs as $sex):
                      // Usar sintaxe ternária para definir ícone e cor
                      $icon = ($sex['slug'] == 'masc') ? 'fa-mars' : (($sex['slug'] == 'femi') ? 'fa-venus' : '');
                      $color = ($sex['slug'] == 'masc') ? 'text-blue-700' : (($sex['slug'] == 'femi') ? 'text-pink-700' : '');
                      ?>
                      <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                        <div class="flex items-center ps-3">
                          <input id="horizontal-list-edit-radio-<?php echo $sex['slug']; ?>"
                                 type="radio"
                                 value="<?php echo $sex['id']; ?>"
                                 name="sex"
                                 class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 focus:ring-2"
                                 required>
                          <label for="horizontal-list-edit-radio-<?php echo $sex['slug']; ?>"
                                 class="w-full py-3 ms-2 text-sm font-medium text-gray-900">
                            <i class="fa <?php echo $icon; ?> fa-fw <?php echo $color; ?>"></i>
                            <?php echo $sex['name']; ?>
                          </label>
                        </div>
                      </li>
                    <?php endforeach; ?>

                  </ul>
                </div>

                <div class="col-span-2">
                  <label for="id_ward"
                         class="block mb-2 text-sm font-medium text-gray-900">Qual ala pertence?</label>
                  <select id="id_ward"
                          name="id_ward"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                          required>
                    <option value=""
                            selected>Selecione...</option>
                    <?php foreach ($activeWards as $ward): ?>
                      <option value="<?php echo $ward['id']; ?>"><?php echo $ward['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-span-2">
                  <label for="id_document"
                         class="block mb-2 text-sm font-medium text-gray-900">Tipo de Documento</label>
                  <select id="id_document"
                          name="id_document"
                          class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                          required>
                    <option value=""
                            selected>Selecione...</option>
                    <?php foreach ($documents as $document): ?>
                      <option value="<?php echo $document['id']; ?>"><?php echo $document['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-span-2">
                  <label for="document"
                         class="block mb-2 text-sm font-medium text-gray-900">Documento</label>
                  <input type="text"
                         id="document"
                         name="document"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         required
                         autocomplete="off">
                </div>

                <!-- <div class="col-span-2">
                  <label for="fever_date"
                         class="block mb-2 text-sm font-medium text-gray-900">Data da Vacina da Febre Amarela</label>
                  <input type="text"
                         id="fever_date"
                         name="fever_date"
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5"
                         placeholder="dd/mm/aaaa"
                         autocomplete="off">
                         <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">We’ll never share your details. Read our <a href="#" class="font-medium text-purple-600 hover:underline dark:text-purple-500">Privacy Policy</a>.</p>
                </div> -->

                <div class="col-span-2">
                  <label for="obs"
                         class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Observação</label>
                  <textarea id="obs"
                            name="obs"
                            rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-purple-500 dark:focus:border-purple-500"></textarea>
                </div>
                <input type="hidden"
                       id="id"
                       name="id" />

              </div>

              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600 justify-end gap-3">
                <button data-modal-hide="passenger_edit_modal"
                        id="passenger_archive"
                        type="button"
                        class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center  dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">Arquivar</button>
                <button type="button"
                        data-modal-hide="passenger_edit_modal"
                        id="cancel"
                        class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancelar</button>
                <button type="submit"
                        id="submit"
                        class=" px-5 py-2.5 text-sm font-medium inline-flex items-center bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-white text-center dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800">
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
        // Caminho da API para passenger_add
        var apiPath = "<?php echo $apiPath; ?>";

        // Defina o user_id a partir do PHP
        var userId = "<?php echo $user_id; ?>";

        // mascarar campos
        $('#passenger_add #nasc_date').mask('00/00/0000');
        $('#passenger_edit #nasc_date').mask('00/00/0000');
        $('#passenger_add #id_church').mask('AAA-AAAA-AAAA');
        $('#passenger_edit #id_church').mask('AAA-AAAA-AAAA');

        // Adiciona um ouvinte de eventos para o documento
        document.addEventListener('click', function (event) {
          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para mostrar o modal
          const showModalTarget = event.target.closest('[data-modal-toggle="passenger_edit_modal"]');

          if (showModalTarget) {
            const passengerId = showModalTarget.getAttribute('data-id');

            $.ajax({
              type: "POST",
              url: apiPath,
              data: {
                passenger_id: passengerId,
                indicador: 'passenger_get'
              },

              success: function (response) {
                // Assumindo que passengerData é um array
                const passenger = JSON.parse(response)[0];
                // console.log(passenger.name);

                // Selecionando o rádio button correspondente à relação
                const relationRadio = $('#passenger_edit input[name="id_relationship"][value="' + passenger.id_relationship + '"]');
                relationRadio.prop('checked', true);

                $('#passenger_edit #name').val(passenger.name || '');

                // Verifica se a propriedade 'nasc_date' existe antes de formatar
                const birthDate = passenger.nasc_date ? passenger.nasc_date : '';

                if (birthDate) {
                  const [year, month, day] = birthDate.split('-');
                  const formattedDate = `${day}/${month}/${year}`;
                  $('#passenger_edit #nasc_date').val(formattedDate);
                }

                // Selecionando o rádio button correspondente à relação
                const sexRadio = $('#passenger_edit input[name="sex"][value="' + passenger.sex + '"]');
                sexRadio.prop('checked', true);

                // Selecionando a opção do select correspondente à ala
                $('#passenger_edit #id_ward').val(passenger.id_ward || '');

                $('#passenger_edit #id_document').val(passenger.id_document || '');

                $('#passenger_edit #document').val(passenger.document || '');
                $('#passenger_edit #obs').val(passenger.obs || '');
                $('#passenger_edit #id').val(passenger.id || '');
                $('#passenger_edit #id_church').val(passenger.id_church || '');

                // console.log('Dados dos passageiros:', passenger);

                // Inicializa e exibe o modal após os dados terem sido carregados
                const passengerEditModal = new Modal(document.getElementById('passenger_edit_modal'));
                passengerEditModal.show();
              }
            });

          }

          // Verifica se o elemento clicado, ou algum dos seus pais, é o botão para ocultar o modal
          const hideModalTarget = event.target.closest('[data-modal-hide="passenger_edit_modal"]');
          if (hideModalTarget) {
            // Inicializa e oculta o modal
            const passengerEditModal = new Modal(document.getElementById('passenger_edit_modal'));
            passengerEditModal.hide();
          }
        });

        // Adicionar passenger
        $("#passenger_add").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente

          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=passenger_add";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#passenger_add")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // updatePassengersList(true); // Se necessário, descomente esta linha para atualizar a lista
                  // Fechar o modal diretamente
                  $('[data-modal-hide="passenger_add_modal"]').trigger('click');
                  updatePeopleList('', 'not_deleted');
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

        // Salvar passenger
        $("#passenger_edit").submit(function (event) {
          event.preventDefault(); // Impedir que o formulário seja enviado tradicionalmente


          // Serializar os campos do formulário
          var formData = $(this).serialize() + "&user_id=" + encodeURIComponent(userId) + "&indicador=passenger_edit";

          $.ajax({
            type: "POST",
            url: apiPath,
            data: formData, // Enviar os dados com o indicador e user_id
            success: function (response) {
              try {
                var jsonResponse = JSON.parse(response); // Tentar fazer o parsing do JSON

                // Verificar o status da resposta e mostrar o toast apropriado
                if (jsonResponse.status === "success") {
                  $("#passenger_edit")[0].reset(); // Reseta o formulário
                  toast(jsonResponse.status, jsonResponse.msg);
                  // updatePassengersList(true); // Se necessário, descomente esta linha para atualizar a lista
                  // Fechar o modal diretamente
                  $("#close_edit").trigger('click');

                  // updatePeopleList();
                  updatePeopleList('', 'not_deleted');
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

        // Verifica se o botão clicado é o de arquivamento
        $(document).on('click', '#passenger_archive', function (event) {
          event.preventDefault(); // Previne o comportamento padrão do botão
          // console.log("clicou no #passenger_archive")

          // Obtém o ID do passageiro que será arquivado
          const form = $('#passenger_edit');
          const passengerId = form.find('input[name="id"]').val();

          // Cria um objeto com os dados a serem enviados
          const formData = {
            id: passengerId,
            indicador: 'archive_something',
            bd: 'passengers'
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

                  const passengerEditModal = new Modal(document.getElementById('passenger_edit_modal'));
                  passengerEditModal.hide();

                  updatePeopleList('', 'not_deleted');
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

        // Função para atualizar a lista de pessoas
        function updatePeopleList(relation = '', status = 'all') {

          // Criar o objeto data com os parâmetros padrão
          let data = {
            user_id: userId,
            indicador: "passenger_list", // Indicador para buscar as pessoas
            status: status // Incluir o status diretamente no objeto data
          };

          // Se a variável relation for fornecida, adicioná-la ao objeto data
          if (relation !== '') {
            data.relation = relation;
          }

          $.ajax({
            type: "POST",
            url: apiPath,

            data: data, // Passa o objeto data no request
            success: function (response) {
              try {
                var people = JSON.parse(response);
                var container_family = $("#passenger_list_family"); // ID do container para família
                var family_list = $("#family_list");
                var container_friend = $("#passenger_list_friend"); // ID do container para amigos
                var friend_list = $("#friend_list");
                var emptyState = $("#empty_state"); // ID do container para mensagem de estado vazio

                // Limpar o conteúdo atual dos containers
                container_family.empty();
                container_friend.empty();

                // Adicionar novos people_items aos containers com base na relação
                people.forEach(function (person) {
                  var iconClass = '';
                  var textColorClass = '';

                  if (person.sex_slug === 'masc') {
                    iconClass = 'fa-mars';
                    textColorClass = 'text-blue-700';
                  } else if (person.sex_slug === 'femi') {
                    iconClass = 'fa-venus';
                    textColorClass = 'text-pink-700';
                  }
                  var personItem = `
          <button type="button"
                  data-modal-target="passenger_edit_modal"
                  data-modal-toggle="passenger_edit_modal"
                  data-id="${person.id}"
                  class="block w-full px-4 py-2 border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:text-purple-700 flex justify-between">
            <span class="text-left truncate me-2">
              <i class="fa ${iconClass} text-lg ${textColorClass} fa-fw me-2"></i>
              ${person.name}
            </span>
            <i class="fa fa-chevron-right text-lg text-gray-500"></i>
          </button>
        `;

                  // Adicionar ao container apropriado baseado na relação
                  if (person.relation_slug === 'family') {
                    container_family.append(personItem);
                  } else if (person.relation_slug === 'friend') {
                    container_friend.append(personItem);
                  }
                });

                // Exibir ou ocultar os containers e a mensagem de estado vazio
                if (container_family.children().length === 0) {
                  family_list.addClass('hidden'); // Oculta o container de família
                } else {
                  family_list.removeClass('hidden'); // Exibe o container de família
                }

                if (container_friend.children().length === 0) {
                  friend_list.addClass('hidden'); // Oculta o container de amigos
                } else {
                  friend_list.removeClass('hidden'); // Exibe o container de amigos
                }

                // Verificar se ambos os containers estão vazios para exibir a mensagem de estado vazio
                if (container_family.children().length === 0 && container_friend.children().length === 0) {
                  emptyState.removeClass('hidden'); // Exibe a mensagem de estado vazio
                } else {
                  emptyState.addClass('hidden'); // Oculta a mensagem de estado vazio
                }

              } catch (e) {
                toast('error', 'Erro ao processar a resposta do servidor.');
              }
            },
            error: function (xhr, status, error) {
              toast('error', 'Erro ao enviar a solicitação: ' + error);
            }
          });
        }

        updatePeopleList('', 'not_deleted');
      });
    </script>
  </body>

</html>