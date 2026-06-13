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
                        <h1 class="fw-bold h2 mb-1 text-dark">Documentação Técnica</h1>
                        <p class="text-muted small mb-0">Repositório de manuais, diretivas e certificados de calibração.</p>
                    </div>
                    <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                        <i class="fa-solid fa-plus me-2"></i>Submeter Documento
                    </button>
                </div>

                <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nome do Documento</th>
                                    <th>Tipo</th>
                                    <th>Equipamento Alvo</th>
                                    <th>Data de Upload</th>
                                    <th>Formato</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaDocumentos">
                                <tr>
                                    <td class="ps-4"><strong>Manual_Utilizador_V1.pdf</strong></td>
                                    <td>Manual Técnico</td>
                                    <td>Monitor Multiparamétrico</td>
                                    <td>08/06/2026</td>
                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">PDF</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-download"></i></button>
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
                            <label class="form-label fw-semibold">Título/Nome do Ficheiro</label>
                            <input type="text" class="form-control" id="nomeDocumento" placeholder="Ex: Certificado_Calibracao_2026" required>
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
                            <label class="form-label fw-semibold">Equipamento Associado</label>
                            <select class="form-select" id="equipamentoAssociado">
                                <option value="Monitor Multiparamétrico">Monitor Multiparamétrico</option>
                                </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Selecionar Ficheiro</label>
                            <input type="file" class="form-control" id="ficheiroDocumento" required>
                        </div>
                        <button type="submit" class="btn btn-acao-primaria w-100 py-2">Submeter Ficheiro</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="/medcare-inventory-solutions/Private/assets/js/documentacao.js"></script>

<?php include '../../includes/footer.php'; ?>