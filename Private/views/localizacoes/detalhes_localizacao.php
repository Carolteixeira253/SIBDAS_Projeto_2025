<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$idEncrypted   = $_GET['id_localizacao'] ?? null;
$idLocalizacao = aes_decrypt($idEncrypted);
if (!$idLocalizacao || !is_numeric($idLocalizacao)) {
    header('Location: localizacoes.php');
    exit;
}

$localizacao  = null;
$equipamentos = [];
$erro = '';

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM Localizacao WHERE idLocalizacao = :id");
    $stmt->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmt->execute();
    $localizacao = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$localizacao) {
        header('Location: localizacoes.php');
        exit;
    }

    $stmtEq = $ligacao->prepare("
        SELECT e.idEquipamento, e.codigoInventario, e.nomeEquipamento, e.estado, e.criticidadeClinica, e.categoria
        FROM Equipamento e
        WHERE e.idLocalizacao = :id AND e.ativo = 1
        ORDER BY e.nomeEquipamento
    ");
    $stmtEq->execute([':id' => $idLocalizacao]);
    $equipamentos = $stmtEq->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $e) {
    $erro = 'bd';
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>
<div class="app-viewport">
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <?php if ($erro === 'bd'): ?>
        <?= mensagem_erro_bd() ?>
    <?php else: ?>

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">
                <?= htmlspecialchars($localizacao->nomeSala) ?>
            </h1>
            <p class="text-muted small mb-0">
                <i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($localizacao->edificio ?? 'N/D') ?>
                · <i class="fa-solid fa-briefcase-medical me-1 ms-1"></i><?= htmlspecialchars($localizacao->servico ?? 'N/D') ?>
                · <i class="fa-solid fa-layer-group me-1 ms-1"></i>Piso <?= htmlspecialchars($localizacao->piso) ?>
            </p>
        </div>
        <a href="localizacoes.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row g-4">

        <!-- COLUNA ESQUERDA -->
        <div class="col-12 col-lg-8">

            <!-- Informação Geral -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Informação Geral</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Nome da Sala</small>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($localizacao->nomeSala) ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Edifício</small>
                        <p class="mb-0"><?= htmlspecialchars($localizacao->edificio ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Serviço</small>
                        <p class="mb-0"><?= htmlspecialchars($localizacao->servico ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Piso</small>
                        <p class="mb-0">Piso <?= htmlspecialchars($localizacao->piso) ?></p>
                    </div>
                </div>
            </div>

            <!-- Equipamentos nesta localização -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="secao-form-titulo mt-0 mb-0">
                        Equipamentos nesta Localização
                        <span class="badge bg-primary ms-2"><?= count($equipamentos) ?></span>
                    </p>
                </div>
                <?php if (empty($equipamentos)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="fa-solid fa-inbox d-block fs-3 mb-2 opacity-25"></i>
                        Nenhum equipamento associado a esta localização.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Código</th>
                                    <th>Equipamento</th>
                                    <th>Categoria</th>
                                    <th>Estado</th>
                                    <th>Criticidade</th>
                                    <th class="text-end pe-3">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipamentos as $eq):
                                    $badgeEstado = match ($eq->estado) {
                                        'operacional' => 'badge-estado-operacional',
                                        'manutencao'  => 'badge-estado-manutencao',
                                        'avariado'    => 'badge-estado-avariado',
                                        default       => 'badge-estado-inativo',
                                    };
                                    $badgeCrit = match ($eq->criticidadeClinica ?? '') {
                                        'Suporte de vida' => 'badge-crit-vida',
                                        'Alta'            => 'badge-crit-alta',
                                        'Media'           => 'badge-crit-media',
                                        default           => 'badge-crit-baixa',
                                    };
                                ?>
                                <tr>
                                    <td class="ps-3" style="font-size:0.82rem; color:#64748b;">
                                        <?= htmlspecialchars($eq->codigoInventario ?? '#' . str_pad($eq->idEquipamento, 3, '0', STR_PAD_LEFT)) ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($eq->nomeEquipamento) ?></strong></td>
                                    <td style="font-size:0.85rem; color:#475569;"><?= htmlspecialchars($eq->categoria ?? 'N/D') ?></td>
                                    <td><span class="badge <?= $badgeEstado ?>"><?= htmlspecialchars($eq->estado) ?></span></td>
                                    <td><span class="badge <?= $badgeCrit ?>"><?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="../equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                            class="btn btn-sm btn-tabela-ver" title="Ver equipamento">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- COLUNA DIREITA -->
        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Resumo</p>
                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total de Equipamentos</small>
                    <p class="fw-bold fs-4 mb-0 text-primary"><?= count($equipamentos) ?></p>
                </div>
                <?php
                $operacionais = count(array_filter($equipamentos, fn($e) => $e->estado === 'operacional'));
                $manutencao   = count(array_filter($equipamentos, fn($e) => $e->estado === 'manutencao'));
                $avariados    = count(array_filter($equipamentos, fn($e) => $e->estado === 'avariado'));
                ?>
                <div class="d-flex gap-3">
                    <div>
                        <small class="text-muted" style="font-size:0.72rem;">Operacionais</small>
                        <p class="fw-bold mb-0" style="color:#15803d;"><?= $operacionais ?></p>
                    </div>
                    <div>
                        <small class="text-muted" style="font-size:0.72rem;">Manutenção</small>
                        <p class="fw-bold mb-0" style="color:#a16207;"><?= $manutencao ?></p>
                    </div>
                    <div>
                        <small class="text-muted" style="font-size:0.72rem;">Avariados</small>
                        <p class="fw-bold mb-0" style="color:#b91c1c;"><?= $avariados ?></p>
                    </div>
                </div>
            </div>

            <?php if ($_perfil === 'administrador'): ?>
            <div class="d-grid gap-2">
                <a href="editar_localizacao.php?id_localizacao=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-acao-primaria fw-bold py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar Localização
                </a>
                <a href="apagar_localizacao.php?id_localizacao=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-danger py-2">
                    <i class="fa-solid fa-trash me-2"></i>Desativar
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php endif; ?>

</main>
</div>
</div>

<?php include '../../includes/footer.php'; ?>