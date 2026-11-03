// js/script.js
document.addEventListener("DOMContentLoaded", function() {

    const botoesRemover = document.querySelectorAll('.btn-remover');
    botoesRemover.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm("Tem a certeza que deseja continuar?")) { e.preventDefault(); }
        });
    });

    const inputData = document.querySelector('input[name="data_hora"]');
    if (inputData) {
        inputData.addEventListener('change', function() {
            if (!this.value) return;

            const dataEscolhida = new Date(this.value);
            const hora = dataEscolhida.getHours();
            const isAlmoco = hora >= 12 && hora < 15;
            const isJantar = hora >= 19 && hora < 23;

            if (!isAlmoco && !isJantar) {
                alert("Restaurante fechado a essa hora.\nHorário: 12:00-15:00 e 19:00-23:00.");
                this.value = "";
            }
        });
    }
});

function togglePessoas(select) {
    const inputReal = document.getElementById('input_pessoas');

    if (select.value === 'custom') {
        select.style.display = 'none';
        inputReal.style.display = 'block';
        inputReal.value = '';
        inputReal.focus();
    } else {
        inputReal.value = select.value;
    }
}