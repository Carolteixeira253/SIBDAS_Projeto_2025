<?php
require_once '../../includes/funcoes.php';
redirect_if_not_logged();
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">

    <?php include '../../includes/sidebar.php'; ?>

            <main class="main-content-wrapper">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="fw-bold h2 mb-1 text-dark">Controlo de Garantias</h1>
                        <p class="text-muted small mb-0">Gestão de prazos de cobertura e assistência técnica contratada.</p>
                    </div>
                    <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGarantia">
                        <i class="fa-solid fa-plus me-2"></i>Registar Garantia
                    </button>
                </div>

                <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Equipamento</th>
                                    <th>Fornecedor</th>
                                    <th>Início da Cobertura</th>
                                    <th>Fim da Cobertura</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaGarantias">
                                <tr>
                                    <td class="ps-4"><strong>Monitor Multiparamétrico</strong></td>
                                    <td>Philips Medical Systems</td>
                                    <td>01/01/2025</td>
                                    <td>01/01/2028</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3">Ativa</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalGarantia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Nova Garantia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formGarantia">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Equipamento</label>
                            <select class="form-select" id="equipamentoGarantia" required>
                                <option value="Monitor Multiparamétrico">Monitor Multiparamétrico</option>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fornecedor Responsável</label>
                            <select class="form-select" id="fornecedorGarantia" required>
                                <option value="Philips Medical Systems">Philips Medical Systems</option>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data de Início</label>
                            <input type="date" class="form-control" id="dataInicioGarantia" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data de Término</label>
                            <input type="date" class="form-control" id="dataFimGarantia" required>
                        </div>
                        <button type="submit" class="btn btn-acao-primaria w-100 py-2">Guardar Garantia</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/medcare-inventory-solutions/Private/assets/js/garantias.js"></script>

<?php include '../../includes/footer.php'; ?>