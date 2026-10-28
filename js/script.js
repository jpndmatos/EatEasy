// js/script.js
document.addEventListener("DOMContentLoaded", function() {

    const theme = document.querySelector('.theme-toggle');
    const syncTheme = () => {
        const dark = document.documentElement.dataset.mode === 'dark';
        theme.innerHTML = dark ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42m0-14.14-1.42 1.42M6.35 17.65l-1.42 1.42"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg>';
        theme.setAttribute('aria-label', dark ? 'Ativar tema claro' : 'Ativar tema escuro');
        theme.setAttribute('aria-pressed', String(dark));
    };
    if (theme) {
        syncTheme();
        theme.addEventListener('click', () => {
            const mode = document.documentElement.dataset.mode === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.mode = mode;
            try { localStorage.setItem('eateasy-theme', mode); } catch {}
            syncTheme();
        });
    }
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