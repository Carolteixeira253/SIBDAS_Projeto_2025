// assets/js/equipamentos.js

// Garante que o código só corre após o HTML estar 100% carregado
document.addEventListener("DOMContentLoaded", function () {
    
    // Captura os elementos principais do HTML (Passo 2)
    const formEquipamento = document.getElementById("formEquipamento");
    const tabelaEquipamentos = document.getElementById("tabelaEquipamentos");

    // Contador simples para gerar novos IDs automaticamente (ex: #002, #003...)
    let proximoId = 2;

    // PASSO 3: Escutar quando o utilizador submete o formulário (clica em Guardar)
    formEquipamento.addEventListener("submit", function (event) {
        
        // 3.1: Impede a página de recarregar e perder os dados (comportamento padrão do HTML)
        event.preventDefault();

        // 3.2: Recolhe os valores que foram escritos nos inputs do Modal
        const nome = document.getElementById("nomeEquip").value;
        const categoria = document.getElementById("catEquip").value;

        // 3.3: Formata o ID com três dígitos (ex: transforma o número 2 em "#002")
        const idFormatado = "#" + String(proximoId).padStart(3, '0');

        // 3.4: Cria uma nova linha (<tr>) em formato de texto HTML idêntica à que já tinhas
        const novaLinhaHTML = `
            <tr>
                <td class="ps-4 text-muted">${idFormatado}</td>
                <td><strong>${nome}</strong></td>
                <td>${categoria}</td>
                <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3">Operacional</span></td>
                <td>Piso 1 - UCI</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;

        // 3.5: Injeta a nova linha diretamente no final da tua tabela
        tabelaEquipamentos.insertAdjacentHTML("beforeend", novaLinhaHTML);

        // 3.6: Incrementa o contador para o próximo equipamento ter o ID seguinte
        proximoId++;

        // 3.7: Limpa os campos do formulário para a próxima utilização
        formEquipamento.reset();

        // 3.8: Fecha o Modal do Bootstrap de forma automática
        const modalElement = document.getElementById("modalEquipamento");
        const modalInstancia = bootstrap.Modal.getInstance(modalElement);
        if (modalInstancia) {
            modalInstancia.hide();
        }
    });

});