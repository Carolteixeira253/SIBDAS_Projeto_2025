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
    // Equipamentos sem nenhum documento associado (indicador obrigatório do guião)
    $semDocumentacao = $ligacao->query("
        SELECT COUNT(*) FROM Equipamento e
        WHERE e.ativo = 1
          AND NOT EXISTS (
              SELECT 1 FROM Documentacao d WHERE d.idEquipamento = e.idEquipamento
          )
    ")->fetchColumn();

    // Garantias já expiradas (indicador obrigatório do guião)
    $garantiasExpiradas = $ligacao->query("
        SELECT COUNT(*) FROM Garantia g
        INNER JOIN Equipamento e ON e.idEquipamento = g.idEquipamento
        WHERE e.ativo = 1 AND g.dataFim < CURDATE()
    ")->fetchColumn();

    $stmtCat = $ligacao->query("SELECT categoria, COUNT(*) AS total FROM Equipamento WHERE ativo = 1 GROUP BY categoria ORDER BY total DESC");
    $catResultado = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    $catLabels  = array_column($catResultado, 'categoria');
    $catValores = array_map('intval', array_column($catResultado, 'total'));

    $stmtGar = $ligacao->query("
        SELECT g.dataFim, e.nomeEquipamento,
               DATEDIFF(g.dataFim, CURDATE()) AS dias_restantes
        FROM Garantia g
        INNER JOIN Equipamento e ON e.idEquipamento = g.idEquipamento
        WHERE e.ativo = 1 AND DATEDIFF(g.dataFim, CURDATE()) <= 30
        ORDER BY g.dataFim ASC LIMIT 5
    ");
    $alertasGarantia = $stmtGar->fetchAll(PDO::FETCH_ASSOC);

    $stmtUlt = $ligacao->query("
        SELECT e.idEquipamento, e.nomeEquipamento, e.categoria, e.estado,
               e.criticidadeClinica, l.nomeSala, l.servico
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.idLocalizacao = l.idLocalizacao
        WHERE e.ativo = 1
        ORDER BY e.idEquipamento DESC LIMIT 5
    ");
    $ultimosEquipamentos = $stmtUlt->fetchAll(PDO::FETCH_ASSOC);

    $erroDashboard = '';
} catch (PDOException $err) {
    $erroDashboard = "Erro ao carregar dados.";
    $totalEquipamentos = $totalOperacional = $totalManutencao = $totalAvariado = 0;
    $semDocumentacao = $garantiasExpiradas = 0;
    $catLabels = $catValores = $alertasGarantia = $ultimosEquipamentos = [];
}
$ligacao = null;
function badgeEstado(string $estado): string
{
    return match ($estado) {
        'operacional' => 'badge-estado-operacional',
        'manutencao'  => 'badge-estado-manutencao',
        'avariado'    => 'badge-estado-avariado',
        default       => 'badge-estado-inativo',
    };
}
function badgeCrit(?string $c): string
{
    return match ($c) {
        'Suporte de vida' => 'badge-crit-vida',
        'Alta'            => 'badge-crit-alta',
        'Media'           => 'badge-crit-media',
        default           => 'badge-crit-baixa',
    };
}
?>
<?php include 'includes/header.php'; ?>
<div class="app-viewport">
    <?php include 'includes/nav.php'; ?>
    <div class="content-body">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content-wrapper">

            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold h2 mb-1">Dashboard</h1>
                    <p class="text-muted small mb-0">Painel Analítico — Inventário Hospitalar</p>
                </div>
                <a href="views/equipamentos/inserir_equipamento.php" class="btn btn-acao-primaria fw-bold px-3 py-2">
                    <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
                </a>
            </div>

            <?php if (!empty($erroDashboard)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erroDashboard) ?></div>
            <?php endif; ?>

            <!-- FILA 1: 3 indicadores principais -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-azul">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Total de Equipamentos</div>
                            <div class="card-indicador-valor"><?= $totalEquipamentos ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-verde">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Operacionais</div>
                            <div class="card-indicador-valor"><?= $totalOperacional ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-amarelo">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Em Manutenção</div>
                            <div class="card-indicador-valor"><?= $totalManutencao ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 2: 3 indicadores de alerta -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-vermelho">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Avariados</div>
                            <div class="card-indicador-valor"><?= $totalAvariado ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-cinza">
                            <i class="fa-solid fa-file-circle-xmark"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Sem Documentação</div>
                            <div class="card-indicador-valor <?= $semDocumentacao > 0 ? 'text-danger' : '' ?>">
                                <?= $semDocumentacao ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-indicador">
                        <div class="card-indicador-icon icon-roxo">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>
                        <div>
                            <div class="card-indicador-label">Garantias Expiradas</div>
                            <div class="card-indicador-valor <?= $garantiasExpiradas > 0 ? 'text-danger' : '' ?>">
                                <?= $garantiasExpiradas ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICO + ALERTAS -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1">Equipamentos por Categoria</h6>
                            <p class="text-muted small mb-3">Distribuição do inventário por tipo clínico</p>
                            <div style="position:relative; height:220px;">
                                <canvas id="graficoCategoria"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1">
                                <i class="fa-solid fa-bell text-warning me-2"></i>Alertas de Garantia
                            </h6>
                            <p class="text-muted small mb-3">Garantias a expirar nos próximos 30 dias</p>
                            <?php if (empty($alertasGarantia)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    <small>Nenhuma garantia prestes a expirar.</small>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($alertasGarantia as $al):
                                        $dias = (int)$al['dias_restantes'];
                                        $exp  = $dias < 0;
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2"
                                            style="background:<?= $exp ? '#fff5f5' : '#fffbeb' ?>; border:1px solid <?= $exp ? '#fecaca' : '#fef08a' ?>">
                                            <div>
                                                <strong style="font-size:0.82rem;"><?= htmlspecialchars($al['nomeEquipamento']) ?></strong>
                                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($al['dataFim'])) ?></small>
                                            </div>
                                            <span class="badge <?= $exp ? 'badge-estado-avariado' : 'badge-estado-manutencao' ?>">
                                                <?= $exp ? 'Expirada' : $dias . ' dias' ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABELA DE ÚLTIMOS EQUIPAMENTOS -->
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
                        <table class="table align-middle mb-0" style="border-radius: 0.75rem; overflow: hidden;">
                            <thead>
                                <tr style="background-color: #063162;">
                                    <th class="ps-3" style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Equipamento</th>
                                    <th style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Categoria</th>
                                    <th style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Localização</th>
                                    <th style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Estado</th>
                                    <th style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Criticidade</th>
                                    <th class="text-end pe-3" style="color:#fff; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; padding: 0.9rem 1rem; border:none;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ultimosEquipamentos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Sem equipamentos registados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ultimosEquipamentos as $eq): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td class="ps-3">
                                                <strong style="color:#0f172a;"><?= htmlspecialchars($eq['nomeEquipamento']) ?></strong>
                                            </td>
                                            <td style="color:#475569;"><?= htmlspecialchars($eq['categoria'] ?? 'N/D') ?></td>
                                            <td style="font-size:0.82rem; color:#64748b;">
                                                <?= $eq['nomeSala']
                                                    ? htmlspecialchars($eq['nomeSala'] . ($eq['servico'] ? ' · ' . $eq['servico'] : ''))
                                                    : '<span style="color:#94a3b8;">N/D</span>' ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= badgeEstado($eq['estado']) ?>">
                                                    <?= htmlspecialchars($eq['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= badgeCrit($eq['criticidadeClinica'] ?? '') ?>">
                                                    <?= htmlspecialchars($eq['criticidadeClinica'] ?? 'N/D') ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="views/equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($eq['idEquipamento']) ?>"
                                                    class="btn btn-sm btn-tabela-ver">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div><!-- fecha card-body -->
            </div><!-- fecha card -->

            <div class="text-center text-muted pb-3" style="font-size:0.78rem;">
                <?= APP_COPYRIGHT ?> — Módulo de Engenharia Biomédica Hospitalar
            </div>

        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
    new Chart(document.getElementById('graficoCategoria'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($catLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Equipamentos',
                data: <?= json_encode($catValores) ?>,
                backgroundColor: [
                    'rgba(6,49,98,0.85)', 'rgba(10,71,138,0.70)',
                    'rgba(10,71,138,0.55)', 'rgba(10,71,138,0.40)',
                    'rgba(10,71,138,0.28)', 'rgba(10,71,138,0.18)'
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