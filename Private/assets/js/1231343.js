// MedCare Inventory Solutions
// Aluna: Carolina Azevedo Teixeira — 1231343

// ── PAGINAÇÃO GENÉRICA ────────────────────────────────────────────────────────
function iniciarPaginacao(idTabela, idContador, idPaginacao, porPagina) {
    const rows = document.querySelectorAll('#' + idTabela + ' tr[data-texto], #' + idTabela + ' tr[data-estado]');
    let paginaAtual = 1;
    let rowsFiltradas = Array.from(rows);

    function renderPagina() {
        const total = rowsFiltradas.length;
        const totalPaginas = Math.ceil(total / porPagina) || 1;
        rows.forEach(r => r.style.display = 'none');
        rowsFiltradas.slice((paginaAtual - 1) * porPagina, paginaAtual * porPagina).forEach(r => r.style.display = '');
        const contador = document.getElementById(idContador);
        if (contador) contador.textContent = total + ' resultado(s) · Página ' + paginaAtual + ' de ' + totalPaginas;
        const pag = document.getElementById(idPaginacao);
        if (!pag) return;
        pag.innerHTML = '';
        if (totalPaginas <= 1) return;
        const btn = (txt, pg, disabled, active) => {
            const b = document.createElement('button');
            b.textContent = txt;
            b.className = 'btn btn-sm mx-1 ' + (active ? 'btn-acao-primaria' : 'btn-outline-secondary');
            b.disabled = disabled;
            b.onclick = () => { paginaAtual = pg; renderPagina(); window.scrollTo({ top: 0, behavior: 'smooth' }); };
            return b;
        };
        pag.appendChild(btn('«', 1, paginaAtual === 1, false));
        pag.appendChild(btn('‹', paginaAtual - 1, paginaAtual === 1, false));
        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || Math.abs(i - paginaAtual) <= 1)
                pag.appendChild(btn(i, i, false, i === paginaAtual));
            else if (Math.abs(i - paginaAtual) === 2) {
                const s = document.createElement('span');
                s.textContent = '...'; s.className = 'mx-1 text-muted align-self-center';
                pag.appendChild(s);
            }
        }
        pag.appendChild(btn('›', paginaAtual + 1, paginaAtual === totalPaginas, false));
        pag.appendChild(btn('»', totalPaginas, paginaAtual === totalPaginas, false));
    }

    renderPagina();
    return { setRows: (novas) => { rowsFiltradas = novas; paginaAtual = 1; renderPagina(); } };
}

// ── VALIDAÇÃO DE FORMULÁRIOS ──────────────────────────────────────────────────
function validarFormulario(idForm) {
    const form = document.getElementById(idForm);
    if (!form) return;
    form.addEventListener('submit', function (e) {
        let valido = true;
        form.querySelectorAll('.campo-obrigatorio').forEach(function (campo) {
            campo.classList.remove('is-invalid');
            if (!campo.value.trim()) {
                campo.classList.add('is-invalid');
                valido = false;
            }
        });
        if (!valido) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    form.querySelectorAll('.campo-obrigatorio').forEach(function (campo) {
        campo.addEventListener('change', function () {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });
}