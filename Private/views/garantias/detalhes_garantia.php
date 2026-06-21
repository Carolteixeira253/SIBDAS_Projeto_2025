<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$idEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia  = aes_decrypt($idEncrypted);
if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: garantias.php');
    exit;
}

$garantia = null;
$erro = '';

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("
        SELECT g.*, e.nomeEquipamento, e.codigoInventario, e.idEquipamento as idEq,
               e.estado as estadoEq, e.criticidadeClinica
        FROM Garantia g
        LEFT JOIN Equipamento e ON g.idEquipamento = e.idEquipamento
        WHERE g.idGarantia = :id
    ");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();
    $garantia = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$garantia) {
        header('Location: garantias.php');
        exit;
    }
    $ligacao = null;
} catch (PDOException $e) {
    $erro = 'bd';
}

$diasRestantes = $garantia ? (strtotime($garantia->dataFim) - time()) / 86400 : 0;
$expirada  = $diasRestantes < 0;
$expiraBreve = !$expirada && $diasRestantes <= 30;
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <?php if ($erro === 'bd'): ?>
        <?= mensagem_erro_bd() ?>
    <?php else: ?>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">Garantia — <?= htmlspecialchars($garantia->nomeEquipamento ?? 'N/D') ?></h1>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($garantia->entidadeResponsavel ?? 'N/D') ?>
                <?= $garantia->tipoContrato ? ' · ' . htmlspecialchars($garantia->tipoContrato) : '' ?>
            </p>
        </div>
        <a href="garantias.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Detalhes da Garantia</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Entidade Responsável</small>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($garantia->entidadeResponsavel ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tipo de Contrato</small>
                        <p class="mb-0"><?= htmlspecialchars($garantia->tipoContrato ?? 'Sem contrato') ?></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data de Início</small>
                        <p class="mb-0"><?= date('d/m/Y', strtotime($garantia->dataInicio)) ?></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data de Fim</small>
                        <p class="mb-0 <?= $expirada ? 'text-danger fw-bold' : ($expiraBreve ? 'text-warning fw-bold' : '') ?>">
                            <?= date('d/m/Y', strtotime($garantia->dataFim)) ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Periodicidade</small>
                        <p class="mb-0"><?= htmlspecialchars($garantia->periodicidade ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tem Contrato</small>
                        <p class="mb-0">
                            <?php if ($garantia->temContrato): ?>
                                <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($garantia->observacoes): ?>
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Observações</small>
                        <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($garantia->observacoes)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Equipamento Associado -->
            <?php if ($garantia->nomeEquipamento): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="secao-form-titulo mt-0 mb-0">Equipamento Associado</p>
                    <a href="../equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($garantia->idEq) ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver Equipamento
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Código</small>
                        <p class="mb-0"><?= htmlspecialchars($garantia->codigoInventario ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-8">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Equipamento</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($garantia->nomeEquipamento) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Estado</p>
                <?php if ($expirada): ?>
                    <span class="badge bg-danger fs-6">Expirada</span>
                    <p class="text-muted small mt-2 mb-0">Expirou há <?= abs(round($diasRestantes)) ?> dias</p>
                <?php elseif ($expiraBreve): ?>
                    <span class="badge bg-warning text-dark fs-6">Expira em breve</span>
                    <p class="text-muted small mt-2 mb-0">Expira em <?= round($diasRestantes) ?> dias</p>
                <?php else: ?>
                    <span class="badge bg-success fs-6">Ativa</span>
                    <p class="text-muted small mt-2 mb-0"><?= round($diasRestantes) ?> dias restantes</p>
                <?php endif; ?>
            </div>

            <?php if ($_perfil === 'administrador'): ?>
            <div class="d-grid gap-2">
                <a href="editar_garantia.php?id_garantia=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-acao-primaria fw-bold py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar Garantia
                </a>
                <a href="apagar_garantia.php?id_garantia=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-danger py-2">
                    <i class="fa-solid fa-trash me-2"></i>Apagar Garantia
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