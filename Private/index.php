<?php
// ---------------------------------------------------------------------------
// SEGURANÇA: Impede que o utilizador aceda diretamente a este script.
// Este ficheiro deve ser acedido apenas através de submissão de formulário (POST).
// Se for acedido diretamente (por URL) recebe a informação de Acesso Inválido
// ----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se não for uma submissão do formulário, termina o script
    exit('Acesso inválido');
}

// Mostrar os dados recebidos pelo formulário através do método POST
echo "Utilizador: " . $_POST['text_username'] . "<br>";
echo "Password: " . $_POST['text_password'];
?>
<?php include 'includes/header.php'; ?>
<div class="app-viewport">

    <!-- HEADER /NAVBAR -->
    <?php include 'includes/nav.php'; ?>
    <div class="content-body">
    <?php include 'includes/sidebar.php'; ?>
        <!-- CONTEÚDO PRINCIPAL -->
        <main class="main-content-wrapper">

            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold h2 mb-1">Dashboard</h1>
                    <p class="text-muted small mb-0">Unidade de Saúde Central — Painel Analítico Geral</p>
                </div>
                <a href="views/equipamentos/equipamentos.html" class="btn btn-primary fw-bold px-3 py-2">
                    <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
                </a>
            </div>

            <!-- CARDS DE ESTATÍSTICAS -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-stat p-4 borda-analitica-total">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Equipamentos</small>
                                <h2 class="fw-bold mb-0 mt-1 texto-card-titulo">3</h2>
                            </div>
                            <i class="fa-solid fa-stethoscope text-primary opacity-25 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-stat p-4 borda-analitica-sucesso">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Operacionais</small>
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-sucesso">1</h2>
                            </div>
                            <i class="fa-solid fa-circle-check text-success opacity-25 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-stat p-4 borda-analitica-alerta">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Em Manutenção</small>
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-alerta">1</h2>
                            </div>
                            <i class="fa-solid fa-screwdriver-wrench text-warning opacity-25 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-stat p-4 borda-analitica-perigo">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Avariados</small>
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-perigo">1</h2>
                            </div>
                            <i class="fa-solid fa-triangle-exclamation text-danger opacity-25 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICO + ALERTAS -->
            <div class="row g-4 mb-5">

                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1">Equipamentos por Categoria</h6>
                            <p class="text-muted small mb-3">Distribuição do inventário por tipo clínico</p>
                            <div style="position:relative; height:220px;">
                                <canvas id="graficoCategoria" role="img" aria-label="Gráfico de barras com equipamentos por categoria"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1">
                                <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                                Alertas de Garantia
                            </h6>
                            <p class="text-muted small mb-3">Garantias a expirar nos próximos 30 dias</p>
                            <div class="d-flex flex-column gap-2">
                                <div class="alert alert-warning py-2 px-3 mb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block" style="font-size:0.85rem;">Monitor de Sinais Vitais</strong>
                                        <small class="text-muted">Expira em 12 dias</small>
                                    </div>
                                    <span class="badge bg-warning text-dark">12 dias</span>
                                </div>
                                <div class="alert alert-warning py-2 px-3 mb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block" style="font-size:0.85rem;">Ventilador Pulmonar V500</strong>
                                        <small class="text-muted">Expira em 28 dias</small>
                                    </div>
                                    <span class="badge bg-warning text-dark">28 dias</span>
                                </div>
                                <div class="alert alert-danger py-2 px-3 mb-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block" style="font-size:0.85rem;">Máquina de Raio-X Móvel</strong>
                                        <small class="text-muted">Garantia expirada</small>
                                    </div>
                                    <span class="badge bg-danger">Expirada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABELA DE EQUIPAMENTOS -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0">Últimos Equipamentos Registados</h6>
                            <p class="text-muted small mb-0">Visão rápida do inventário recente</p>
                        </div>
                        <a href="views/equipamentos/equipamentos.html" class="btn btn-sm btn-outline-primary">
                            Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Equipamento</th>
                                    <th>Categoria</th>
                                    <th>Estado</th>
                                    <th>Criticidade</th>
                                    <th class="text-end pe-3">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"><strong>Ventilador Pulmonar V500</strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Ventilação</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">Operacional</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">Alta</span></td>
                                    <td class="text-end pe-3">
                                        <a href="views/equipamentos/equipamentos.html" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><strong>Máquina de Raio-X Móvel</strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Imagem</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">Manutenção</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">Média</span></td>
                                    <td class="text-end pe-3">
                                        <a href="views/equipamentos/equipamentos.html" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3"><strong>Monitor de Sinais Vitais</strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Monitorização</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">Avariado</span></td>
                                    <td><span class="badge bg-light text-muted border">Baixa</span></td>
                                    <td class="text-end pe-3">
                                        <a href="views/equipamentos/equipamentos.html" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted pt-2 pb-3" style="font-size:0.78rem;">
                <?php echo APP_COPYRIGHT; ?> — Módulo de Engenharia Biomédica Hospitalar
            </div>

        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    new Chart(document.getElementById('graficoCategoria'), {
        type: 'bar',
        data: {
            labels: ['Ventilação', 'Imagem', 'Monitorização', 'Terapia', 'Diagnóstico'],
            datasets: [{
                label: 'Nº de Equipamentos',
                data: [1, 1, 1, 0, 0],
                backgroundColor: [
                    'rgba(10,71,138,0.7)',
                    'rgba(10,71,138,0.5)',
                    'rgba(10,71,138,0.35)',
                    'rgba(10,71,138,0.2)',
                    'rgba(10,71,138,0.1)'
                ],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>