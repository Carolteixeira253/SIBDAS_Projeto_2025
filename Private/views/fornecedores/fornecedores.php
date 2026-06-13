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
                        <h1 class="fw-bold h2 mb-1 text-dark">Gestão de Fornecedores</h1>
                        <p class="text-muted small mb-0">Registo e contactos de entidades fornecedoras de dispositivos médicos.</p>
                    </div>
                    <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFornecedor">
                        <i class="fa-solid fa-plus me-2"></i>Adicionar Fornecedor
                    </button>
                </div>

                <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">NIF</th>
                                    <th>Empresa / Entidade</th>
                                    <th>Telemóvel / Contacto</th>
                                    <th>Email</th>
                                    <th>País</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaFornecedores">
                                <tr>
                                    <td class="ps-4 text-muted">501234567</td>
                                    <td><strong>Philips Medical Systems</strong></td>
                                    <td>+351 210 000 000</td>
                                    <td>pt.support@philips.com</td>
                                    <td>Portugal</td>
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
                            <label class="form-label fw-semibold">País</label>
                            <input type="text" class="form-control" id="paisFornecedor" placeholder="Ex: Portugal" required>
                        </div>
                        <button type="submit" class="btn btn-acao-primaria w-100 py-2">Guardar Fornecedor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/medcare-inventory-solutions/Private/assets/js/fornecedores.js"></script>

<?php include '../../includes/footer.php'; ?>