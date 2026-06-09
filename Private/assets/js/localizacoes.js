document.addEventListener("DOMContentLoaded", function () {
    const formLocalizacao = document.getElementById("formLocalizacao");
    const tabelaLocalizacoes = document.getElementById("tabelaLocalizacoes");

    formLocalizacao.addEventListener("submit", function (event) {
        event.preventDefault();

        // Recolher valores dos inputs
        const codigo = document.getElementById("codLocalizacao").value;
        const nome = document.getElementById("nomeLocalizacao").value;
        const piso = document.getElementById("pisoLocalizacao").value;
        const edificio = document.getElementById("edificioLocalizacao").value;
        const responsavel = document.getElementById("responsavelLocalizacao").value;

        // Criar a nova linha na tabela
        const novaLinhaHTML = `
            <tr>
                <td class="ps-4 text-muted">${codigo}</td>
                <td><strong>${nome}</strong></td>
                <td>${piso}</td>
                <td>${edificio}</td>
                <td>${responsavel}</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;

        // Inserir na tabela
        tabelaLocalizacoes.insertAdjacentHTML("beforeend", novaLinhaHTML);

        // Limpar formulário e fechar modal
        formLocalizacao.reset();
        const modalElement = document.getElementById("modalLocalizacao");
        const modalInstancia = bootstrap.Modal.getInstance(modalElement);
        if (modalInstancia) modalInstancia.hide();
    });
});