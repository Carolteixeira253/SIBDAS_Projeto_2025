<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">

    <?php include '../../includes/sidebar.php'; ?>
            <main class="main-content-wrapper">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="fw-bold h2 mb-1 text-dark">Gestão de Localizações</h1>
                        <p class="text-muted small mb-0">Controlo de edifícios, pisos, salas e serviços hospitalares.</p>
                    </div>
                    <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalLocalizacao">
                        <i class="fa-solid fa-plus me-2"></i>Adicionar Localização
                    </button>
                </div>

                <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Serviço / Sala</th>
                                    <th>Piso</th>
                                    <th>Edifício</th>
                                    <th>Responsável do Setor</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaLocalizacoes">
                                <tr>
                                    <td class="ps-4 text-muted">UCI-P1</td>
                                    <td><strong>Unidade de Cuidados Intensivos</strong></td>
                                    <td>Piso 1</td>
                                    <td>Edifício Central (A)</td>
                                    <td>Dr. Silva Santos</td>
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

    <div class="modal fade" id="modalLocalizacao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Nova Localização</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formLocalizacao">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Código Interno</label>
                            <input type="text" class="form-control" id="codLocalizacao" placeholder="Ex: UCI-P1, REAB-P0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome do Serviço / Sala</label>
                            <input type="text" class="form-control" id="nomeLocalizacao" placeholder="Ex: Bloco Operatório 2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Piso</label>
                            <input type="text" class="form-control" id="pisoLocalizacao" placeholder="Ex: Piso 0, Piso 1, Cave" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Edifício</label>
                            <input type="text" class="form-control" id="edificioLocalizacao" placeholder="Ex: Edifício de Consultas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Responsável do Setor</label>
                            <input type="text" class="form-control" id="responsavelLocalizacao" placeholder="Ex: Enf. Chefe Maria Lima" required>
                        </div>
                        <button type="submit" class="btn btn-acao-primaria w-100 py-2">Guardar Localização</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

  <script src="/medcare-inventory-solutions/Private/assets/js/garantias.js"></script>

<?php include '../../includes/footer.php'; ?>