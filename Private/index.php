<?php
require_once 'includes/funcoes.php';
require_once __DIR__ . '/../config/config.php';
redirect_if_not_logged();

// LIGAÇÃO À BASE DE DADOS E CÁLCULO DE ESTATÍSTICAS REAIS
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cards de estatísticas (apenas equipamentos ativos)
    $totalEquipamentos = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1")->fetchColumn();
    $totalOperacional = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'operacional'")->fetchColumn();
    $totalManutencao = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'manutencao'")->fetchColumn();
    $totalAvariado = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'avariado'")->fetchColumn();

    // Dados para o gráfico: equipamentos por categoria
    $stmtCategorias = $ligacao->query("SELECT categoria, COUNT(*) AS total FROM Equipamento WHERE ativo = 1 GROUP BY categoria ORDER BY total DESC");
    $categoriasResultado = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
    $categoriasLabels = [];
    $categoriasValores = [];
    foreach ($categoriasResultado as $linha) {
        $categoriasLabels[] = $linha['categoria'];
        $categoriasValores[] = (int) $linha['total'];
    }

    // Alertas de garantia: garantias a expirar nos próximos 30 dias ou já expiradas
    $stmtGarantias = $ligacao->query(
        "SELECT g.dataFim, e.nomeEquipamento,
                DATEDIFF(g.dataFim, CURDATE()) AS dias_restantes
         FROM Garantia g
         INNER JOIN Equipamento e ON e.idEquipamento = g.idEquipamento
         WHERE g.ativo = 1 AND e.ativo = 1
           AND DATEDIFF(g.dataFim, CURDATE()) <= 30
         ORDER BY g.dataFim ASC
         LIMIT 5"
    );
    $alertasGarantia = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);

    // Últimos equipamentos registados
    $stmtUltimos = $ligacao->query(
        "SELECT idEquipamento, nomeEquipamento, categoria, estado, criticidadeClinica
         FROM Equipamento
         WHERE ativo = 1
         ORDER BY idEquipamento DESC
         LIMIT 5"
    );
    $ultimosEquipamentos = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

    $erroDashboard = '';
} catch (PDOException $err) {
    $erroDashboard = "Erro ao carregar dados do dashboard.";
    $totalEquipamentos = $totalOperacional = $totalManutencao = $totalAvariado = 0;
    $categoriasLabels = [];
    $categoriasValores = [];
    $alertasGarantia = [];
    $ultimosEquipamentos = [];
}
$ligacao = null;
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
                <a href="views/equipamentos/inserir_equipamento.php" class="btn btn-primary fw-bold px-3 py-2">
                    <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
                </a>
            </div>

            <?php if (!empty($erroDashboard)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erroDashboard) ?></div>
            <?php endif; ?>

            <!-- CARDS DE ESTATÍSTICAS -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card-stat p-4 borda-analitica-total">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Equipamentos</small>
                                <h2 class="fw-bold mb-0 mt-1 texto-card-titulo"><?= $totalEquipamentos ?></h2>
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
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-sucesso"><?= $totalOperacional ?></h2>
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
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-alerta"><?= $totalManutencao ?></h2>
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
                                <h2 class="fw-bold mb-0 mt-1 texto-estado-perigo"><?= $totalAvariado ?></h2>
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
                                <?php if (empty($alertasGarantia)): ?>
                                    <p class="text-muted small mb-0">Não existem garantias a expirar.</p>
                                <?php else : ?>
                                    <?php foreach ($alertasGarantia as $alerta): ?>
                                        <?php
                                        $diasRestantes = (int) $alerta['dias_restantes'];
                                        $expirada = $diasRestantes < 0;
                                        $classeAlerta = $expirada ? 'alert-danger' : 'alert-warning';
                                        $textoBadge = $expirada ? 'Expirada' : $diasRestantes . ' dias';
                                        $classeBadge = $expirada ? 'bg-danger' : 'bg-warning text-dark';
                                        ?>
                                        <div class="alert <?= $classeAlerta ?> py-2 px-3 mb-0 d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="d-block" style="font-size:0.85rem;"><?= htmlspecialchars($alerta['nomeEquipamento']) ?></strong>
                                                <small class="text-muted"><?= $expirada ? 'Garantia expirada' : 'Expira em ' . $diasRestantes . ' dias' ?></small>
                                            </div>
                                            <span class="badge <?= $classeBadge ?>"><?= $textoBadge ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                        <a href="views/equipamentos/equipamentos.php" class="btn btn-sm btn-outline-primary">
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
                                <?php if (empty($ultimosEquipamentos)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Não existem equipamentos registados.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($ultimosEquipamentos as $equip): ?>
                                        <?php
                                        $badgeEstado = match ($equip['estado']) {
                                            'operacional' => 'bg-success-subtle text-success border-success-subtle',
                                            'manutencao' => 'bg-warning-subtle text-warning border-warning-subtle',
                                            'avariado' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            default => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                                        };
                                        $badgeCriticidade = match ($equip['criticidadeClinica']) {
                                            'Alta' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            'Media' => 'bg-warning-subtle text-warning border-warning-subtle',
                                            'Baixa' => 'bg-light text-muted border',
                                            default => 'bg-light text-muted border'
                                        };
                                        ?>
                                        <tr>
                                            <td class="ps-3"><strong><?= htmlspecialchars($equip['nomeEquipamento']) ?></strong></td>
                                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($equip['categoria'] ?? 'N/A') ?></span></td>
                                            <td><span class="badge <?= $badgeEstado ?> border"><?= htmlspecialchars($equip['estado']) ?></span></td>
                                            <td><span class="badge <?= $badgeCriticidade ?> border"><?= htmlspecialchars($equip['criticidadeClinica'] ?? 'N/A') ?></span></td>
                                            <td class="text-end pe-3">
                                                <a href="views/equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($equip['idEquipamento']) ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
            labels: <?= json_encode($categoriasLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Nº de Equipamentos',
                data: <?= json_encode($categoriasValores) ?>,
                backgroundColor: [
                    'rgba(10,71,138,0.7)',
                    'rgba(10,71,138,0.55)',
                    'rgba(10,71,138,0.4)',
                    'rgba(10,71,138,0.25)',
                    'rgba(10,71,138,0.15)',
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