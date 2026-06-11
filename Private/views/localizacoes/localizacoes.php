<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Localizações</title>

    <link rel="icon" type="image/png" href="../Private/assets/img/logo.png">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/1231343.css">
</head>
<body>

    <div class="app-viewport">
        <header class="navbar-medcare d-flex align-items-center justify-content-between shadow-sm">
            <a href="../../index.php" class="brand-header">
                <i class="fa-solid fa-heart-pulse me-2"></i>
                <span>MedCare Inventory <span class="navbar-text-secundario">| Hospital Backoffice</span></span>
            </a>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark border me-3">
                    <i class="fa-solid fa-server text-success me-1"></i>Produção
                </span>
                <i class="fa-solid fa-bell text-muted fs-5 pointer me-2"></i>
            </div>
        </header>

        <div class="content-body">
            <nav class="sidebar-medcare p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="sidebar-section-title">Navegação Principal</div>
                    <div class="nav flex-column">
                        <a href="../../index.php" class="nav-link">
                            <i class="fa-solid fa-table-columns me-3"></i>Dashboard
                        </a>
                    </div>

                    <div class="sidebar-section-title">Gestão de Ativos</div>
                    <div class="nav flex-column">
                        <a href="../equipamentos/equipamentos.php" class="nav-link">
                            <i class="fa-solid fa-stethoscope me-3"></i>Equipamentos
                        </a>
                        <a href="../fornecedores/fornecedores.php" class="nav-link">
                            <i class="fa-solid fa-truck-field me-3"></i>Fornecedores
                        </a>
                        <a href="../documentacao/documentacao.php" class="nav-link">
                            <i class="fa-solid fa-bell me-3"></i>Documentação
                        </a>
                        <a href="../garantias/garantias.php" class="nav-link">
                            <i class="fa-solid fa-file-invoice me-3"></i>Garantias
                        </a>
                        <a href="localizacoes.php" class="nav-link active">
                            <i class="fa-solid fa-file-lines me-3"></i>Localizações
                        </a>
                    </div>
                </div>

                <div>
                    <div class="sidebar-section-title">Sessão</div>
                    <a href="../../../Public/index.php" class="nav-link mb-3 link-frontoffice">
                        <i class="fa-solid fa-arrow-right-from-bracket me-3"></i>Ir para Front-Office
                    </a>
                    <div class="sidebar-user-zone border-top border-light border-opacity-25 pt-3 d-flex align-items-center justify-content-between">
                        <div>
                            <small>Utilizador</small>
                            <strong>Eng. Biomédico</strong>
                        </div>
                        <i class="fa-solid fa-power-off fs-5"></i>
                    </div>
                </div>
            </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/localizacoes.js"></script>
</body>
</html>