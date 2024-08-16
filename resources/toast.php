<div class="toast-container fixed p-3 top-0 right-0 w-full flex flex-col items-center md:items-end gap-2"
     id="toasters"
     style="z-index: 9999;">

</div>

</div>
<script>
  function toast(tipo, mensagem, duracaoPersonalizada) {
    const estilos = {
      success: { cor: 'bg-green-700', icone: 'fa-check' },
      loading: { cor: 'bg-green-600', icone: 'fa-spinner fa-spin' }, // Cor ajustada para loading
      error: { cor: 'bg-red-700', icone: 'fa-ban' },
      warning: { cor: 'bg-yellow-700', icone: 'fa-exclamation-triangle' },
      default: { cor: 'bg-white', icone: 'fa-info-circle' }
    };
    var bgCor = estilos[tipo]?.cor || estilos.default.cor;
    var icone = estilos[tipo]?.icone || estilos.default.icone;
    // Configurações padrão
    var duracaoPadrao = 8000;

    // var bgCor = tipo === 'success' ? 'bg-green-700' :
    //   tipo === 'loading' ? 'bg-green-700' :
    //     tipo === 'error' ? 'bg-red-700' :
    //       tipo === 'warning' ? 'bg-yellow-700' :
    //         'bg-white';

    // // Define o ícone baseado no tipo
    // var icone = tipo === 'success' ? 'fa-check' :
    //   tipo === 'loading' ? 'fa-spinner fa-spin' :
    //     tipo === 'error' ? 'fa-ban' :
    //       tipo === 'warning' ? 'fa-exclamation-triangle' :
    //         'fa-info-circle';

    // Criação do elemento toast
    var toastEl = document.createElement('div');
    toastEl.className = `flex items-center w-full  p-2 md:max-w-xs text-white rounded-lg shadow ${bgCor}`;
    toastEl.style.transition = 'opacity 0.5s ease-in-out';
    toastEl.style.opacity = 1;

    toastEl.innerHTML = `
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
      <i class="fa ${icone} text-xl"></i>
    </div>
    <div class="ms-3 text-sm font-normal">${mensagem}</div>
    `;

    // Adiciona o toast ao contêiner
    var toastersEl = document.getElementById('toasters');
    toastersEl.appendChild(toastEl);

    // Define a duração do toast (duração personalizada ou padrão)
    var duracao = duracaoPersonalizada !== undefined ? duracaoPersonalizada : duracaoPadrao;

    // Oculta o toast após o tempo especificado
    setTimeout(function () {
      toastEl.style.opacity = 0;
      setTimeout(function () {
        toastEl.remove();
      }, 500); // Tempo para animação de fade out
    }, duracao);
  }
</script>