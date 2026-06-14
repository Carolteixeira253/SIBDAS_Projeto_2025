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
    $resultados = $ligacao->query("SELECT * FROM Fornecedor WHERE ativo = 1")->fetchAll(PDO::FETCH_OBJ);
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
                <h1 class="fw-bold h2 mb-1 text-dark">Gestão de Fornecedores</h1>
                <p class="text-muted small mb-0">Registo e contactos de entidades fornecedoras de dispositivos médicos.</p>
            </div>
            <a href="inserir_fornecedor.php" class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Adicionar Fornecedor
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table id="tabela-fornecedores" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">NIF</th>
                            <th>Empresa / Entidade</th>
                            <th>Telemóvel / Contacto</th>
                            <th>Email</th>
                            <th>Morada</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($erro)) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-danger"><?= $erro ?></td>
                            </tr>
                        <?php elseif (count($resultados) == 0) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Não existem fornecedores registados.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($resultados as $fornecedor) : ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= htmlspecialchars($fornecedor->nif ?? 'N/A') ?></td>
                                    <td><strong><?= htmlspecialchars($fornecedor->nomeFornecedor) ?></strong></td>
                                    <td><?= htmlspecialchars($fornecedor->contactoTelefonico ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($fornecedor->enderecoEmail ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($fornecedor->morada ?? 'N/A') ?></td>
                                    <td class="text-end pe-4">
                                        <a href="detalhes_fornecedor.php?id_fornecedor=<?= aes_encrypt($fornecedor->idFornecedor) ?>" class="btn btn-sm btn-outline-secondary me-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="editar_fornecedor.php?id_fornecedor=<?= aes_encrypt($fornecedor->idFornecedor) ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="apagar_fornecedor.php?id_fornecedor=<?= aes_encrypt($fornecedor->idFornecedor) ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
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

<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Novo Fornecedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formFornecedor">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome da Entidade</label>
                        <input type="text" class="form-control" id="nomeFornecedor" placeholder="Ex: Siemens Healthineers" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">NIF</label>
                        <input type="text" class="form-control" id="nifFornecedor" placeholder="Ex: 500000000" maxlength="9" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contacto Telefónico</label>
                        <input type="tel" class="form-control" id="telFornecedor" placeholder="Ex: 910000000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email de Contacto</label>
                        <input type="email" class="form-control" id="emailFornecedor" placeholder="Ex: info@empresa.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Morada</label>
                        <input type="text" class="form-control" id="moradaFornecedor" placeholder="Ex: Rua das Flores, 123, Porto" required>
                    </div>
                    <button type="submit" class="btn btn-acao-primaria w-100 py-2">Guardar Fornecedor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-fornecedores').DataTable({
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