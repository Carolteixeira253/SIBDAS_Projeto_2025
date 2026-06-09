document.addEventListener("DOMContentLoaded", function () {
    const formGarantia = document.getElementById("formGarantia");
    const tabelaGarantias = document.getElementById("tabelaGarantias");

    formGarantia.addEventListener("submit", function (event) {
        event.preventDefault();

        // Recolher valores dos inputs
        const equip = document.getElementById("equipamentoGarantia").value;
        const fornecedor = document.getElementById("fornecedorGarantia").value;
        const dataInicio = document.getElementById("dataInicioGarantia").value;
        const dataFim = document.getElementById("dataFimGarantia").value;

        // Criar a nova linha na tabela
        const novaLinhaHTML = `
            <tr>
                <td class="ps-4"><strong>${equip}</strong></td>
                <td>${fornecedor}</td>
                <td>${dataInicio}</td>
                <td>${dataFim}</td>
                <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3">Ativa</span></td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;

        // Inserir na tabela
        tabelaGarantias.insertAdjacentHTML("beforeend", novaLinhaHTML);

        // Limpar formulário e fechar modal
        formGarantia.reset();
        const modalElement = document.getElementById("modalGarantia");
        const modalInstancia = bootstrap.Modal.getInstance(modalElement);
        if (modalInstancia) modalInstancia.hide();
    });
});