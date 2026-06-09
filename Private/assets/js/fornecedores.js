document.addEventListener("DOMContentLoaded", function () {
    const formFornecedor = document.getElementById("formFornecedor");
    const tabelaFornecedores = document.getElementById("tabelaFornecedores");

    formFornecedor.addEventListener("submit", function (event) {
        event.preventDefault();

        // Recolher valores dos inputs
        const nome = document.getElementById("nomeFornecedor").value;
        const nif = document.getElementById("nifFornecedor").value;
        const tel = document.getElementById("telFornecedor").value;
        const email = document.getElementById("emailFornecedor").value;
        const pais = document.getElementById("paisFornecedor").value;

        // Criar nova linha
        const novaLinhaHTML = `
            <tr>
                <td class="ps-4 text-muted">${nif}</td>
                <td><strong>${nome}</strong></td>
                <td>${tel}</td>
                <td>${email}</td>
                <td>${pais}</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;

        tabelaFornecedores.insertAdjacentHTML("beforeend", novaLinhaHTML);

        // Limpar e fechar
        formFornecedor.reset();
        const modalElement = document.getElementById("modalFornecedor");
        const modalInstancia = bootstrap.Modal.getInstance(modalElement);
        if (modalInstancia) modalInstancia.hide();
    });
});