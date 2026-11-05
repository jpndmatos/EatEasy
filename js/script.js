// js/script.js
document.addEventListener("DOMContentLoaded", function() {
    const day = document.getElementById('dia');
    const hour = document.getElementById('hora');
    const people = document.getElementById('pessoas');
    const summary = document.querySelector('.booking-summary');
    if (day && hour && people && summary) {
        const update = () => {
            const today = new Date();
            const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {timeZone:'Europe/Lisbon', year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).formatToParts(today).map(part=>[part.type,part.value]));
            const localDate = `${parts.year}-${parts.month}-${parts.day}`;
            const now = `${parts.hour}:${parts.minute}`;
            for (const option of hour.options) {
                if (option.value) option.disabled = day.value === localDate && option.value <= now;
            }
            if (hour.selectedOptions[0]?.disabled && hour.value) hour.value = '';
            summary.textContent = day.value && hour.value
                ? `${new Intl.DateTimeFormat('pt-PT', {day:'numeric',month:'long'}).format(new Date(day.value + 'T12:00'))} · ${hour.value} · ${people.value} ${people.value === '1' ? 'pessoa' : 'pessoas'}`
                : '';
        };
        [day, hour, people].forEach(field => field.addEventListener('change', update));
        update();
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
