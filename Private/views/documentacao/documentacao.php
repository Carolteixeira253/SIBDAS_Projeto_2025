<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

// LIGAÇÃO À BASE DE DADOS
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Documentacao")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Erro: " . $err->getMessage();
    $resultados = [];
}
$ligacao = null;
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">

    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">Documentação Técnica</h1>
                <p class="text-muted small mb-0">Repositório de manuais, diretivas e certificados de calibração.</p>
            </div>
            <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                <i class="fa-solid fa-plus me-2"></i>Submeter Documento
            </button>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table id="tabela-documentos" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nome do Documento</th>
                            <th>Tipo</th>
                            <th>Nome do Ficheiro</th>
                            <th>Data</th>
                            <th>Validade</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($erro)) : ?>
                            <tr><td colspan="6" class="text-center text-danger"><?= $erro ?></td></tr>
                        <?php elseif (count($resultados) == 0) : ?>
                            <tr><td colspan="6" class="text-center text-muted">Não existem documentos registados.</td></tr>
                        <?php else : ?>
                            <?php foreach ($resultados as $documento) : ?>
                            <tr>
                                <td class="ps-4"><strong><?= htmlspecialchars($documento->nomeDocumento) ?></strong></td>
                                <td><?= htmlspecialchars($documento->tipoDocumento ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($documento->nomeFicheiro ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($documento->dataDocumento ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($documento->dataValidade ?? 'N/A') ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-download"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Novo Documento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formDocumento">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome do Documento</label>
                        <input type="text" class="form-control" id="nomeDocumento" placeholder="Ex: Manual de Utilização" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo de Documento</label>
                        <select class="form-select" id="tipoDocumento">
                            <option value="Manual Técnico">Manual Técnico</option>
                            <option value="Ficha Técnica">Ficha Técnica</option>
                            <option value="Certificado">Certificado de Calibração</option>
                            <option value="Contrato">Contrato de Manutenção</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome do Ficheiro</label>
                        <input type="text" class="form-control" id="nomeFicheiro" placeholder="Ex: manual_v1.pdf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data do Documento</label>
                        <input type="date" class="form-control" id="dataDocumento" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data de Validade</label>
                        <input type="date" class="form-control" id="dataValidade">
                    </div>
                    <button type="submit" class="btn btn-acao-primaria w-100 py-2">Submeter Documento</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabela-documentos').DataTable({
        pageLength: 5,
        pagingType: "full_numbers",
        language: {
            emptyTable: "Sem dados disponíveis na tabela.",
            info: "Mostrando _START_ até _END_ de _TOTAL_ registos",
            infoEmpty: "Mostrando 0 até 0 de 0 registos",
            infoFiltered: "(Filtrando _MAX_ total de registos)",
            lengthMenu: "Mostrando _MENU_ registos por página.",
            loadingRecords: "Carregando...",
            processing: "Processando...",
            search: "Filtrar:",
            zeroRecords: "Nenhum registo encontrado.",
            paginate: {
                first: "Primeira",
                last: "Última",
                next: "Seguinte",
                previous: "Anterior"
            }
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>