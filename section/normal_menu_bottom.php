<?php
// Definir as permissões para cada tipo de liderança
$stake_roles = ['stake_lider', 'stake_aux'];
$ward_roles = ['ward_lider', 'ward_aux'];
$admin_role = 'admin';

// Determinar o número de colunas com base na role do usuário
if (in_array($user_role, array_merge($stake_roles, $ward_roles)) || $user_role === $admin_role) {
  $grid_cols = 'grid-cols-5';
} else {
  $grid_cols = 'grid-cols-4';
}

// Determinar o URL e o texto da página de liderança com base na role
$lider_page = '';
$lider_text = '';

if (in_array($user_role, $stake_roles)) {
  $lider_page = 'stake.php';
  $lider_text = 'Estaca';
} elseif (in_array($user_role, $ward_roles)) {
  $lider_page = 'ward.php';
  $lider_text = 'Ala';
} elseif ($user_role === $admin_role) {
  $lider_page = 'admin.php';
  $lider_text = 'Admin';
}
?>
<!-- nav bottom -->
<div class="fixed bottom-0 left-0 z-10 w-full h-20 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600 lg:h-16">
  <div class="grid h-full max-w-lg <?php echo $grid_cols; ?> mx-auto font-medium">
    <!-- Link para panel.php -->
    <a href="panel.php"
       class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group pb-4 lg:pb-0">
      <i class="text-gray-500 mb-1 group-hover:text-purple-600 fa fa-gauge-high text-xl"></i>
      <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-500">
        Painel
      </span>
    </a>
    <!-- Link para travels.php -->
    <a href="caravans.php"
       class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group pb-4 lg:pb-0">
      <i class="text-gray-500 mb-1 group-hover:text-purple-600 fa fa-signs-post text-xl"></i>
      <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-500">
        Caravanas
      </span>
    </a>
    <!-- Link para passengers.php -->
    <a href="passengers.php"
       class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group pb-4 lg:pb-0">
      <i class="text-gray-500 mb-1 group-hover:text-purple-600 fa fa-users text-xl"></i>
      <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-500">
        Pessoas
      </span>
    </a>
    <?php if (in_array($user_role, ['stake_lider', 'stake_aux', 'ward_lider', 'ward_aux', 'admin'])): ?>
      <!-- Link dinâmico para a página de liderança -->
      <a href="<?php echo $lider_page; ?>"
         class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group pb-4 lg:pb-0">
        <i class="text-gray-500 mb-1 group-hover:text-purple-600 fa fa-briefcase text-xl"></i>
        <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-500">
          <?php echo $lider_text; ?>
        </span>
      </a>
    <?php endif; ?>
    <!-- Link para profile.php -->
    <a href="profile.php"
       class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group pb-4 lg:pb-0">
      <i class="text-gray-500 mb-1 group-hover:text-purple-600 fa fa-circle-user text-xl"></i>
      <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-500">
        Perfil
      </span>
    </a>
  </div>
</div>