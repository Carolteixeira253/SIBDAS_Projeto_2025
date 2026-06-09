document.addEventListener("DOMContentLoaded", function () {
    const formDocumento = document.getElementById("formDocumento");
    const tabelaDocumentos = document.getElementById("tabelaDocumentos");

    formDocumento.addEventListener("submit", function (event) {
        event.preventDefault();

        // Recolher valores dos inputs
        const nome = document.getElementById("nomeDocumento").value;
        const tipo = document.getElementById("tipoDocumento").value;
        const equip = document.getElementById("equipamentoAssociado").value;
        
        // Obter nome do ficheiro (ex: arquivo.pdf)
        const ficheiroInput = document.getElementById("ficheiroDocumento");
        const nomeFicheiro = ficheiroInput.files.length > 0 ? ficheiroInput.files[0].name : "sem_nome.pdf";
        
        // Data atual automática
        const dataHoje = new Date().toLocaleDateString('pt-PT');

        // Criar a nova linha na tabela
        const novaLinhaHTML = `
            <tr>
                <td class="ps-4"><strong>${nome}</strong></td>
                <td>${tipo}</td>
                <td>${equip}</td>
                <td>${dataHoje}</td>
                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">PDF</span></td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-download"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;

        // Inserir na tabela
        tabelaDocumentos.insertAdjacentHTML("beforeend", novaLinhaHTML);

        // Limpar formulário e fechar modal
        formDocumento.reset();
        const modalElement = document.getElementById("modalDocumento");
        const modalInstancia = bootstrap.Modal.getInstance(modalElement);
        if (modalInstancia) modalInstancia.hide();
    });
});