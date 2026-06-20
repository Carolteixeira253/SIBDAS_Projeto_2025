<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

$idEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia  = aes_decrypt($idEncrypted);
if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: garantias.php');
    exit;
}
$erros        = [];
$erro_sistema = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entidade     = trim($_POST['entidade_responsavel'] ?? '');
    $tipoContrato = $_POST['tipo_contrato'] ?? '';
    $dataInicio   = $_POST['data_inicio']   ?? '';
    $dataFim      = $_POST['data_fim']      ?? '';
    if (empty($dataInicio)) $erros[] = "A Data de Início é obrigatória.";
    if (empty($dataFim))    $erros[] = "A Data de Fim é obrigatória.";
    if (!empty($dataInicio) && !empty($dataFim) && $dataFim < $dataInicio)
        $erros[] = "A Data de Fim não pode ser anterior à Data de Início.";
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                UPDATE Garantia
                SET entidadeResponsavel = :entidade,
                    tipoContrato = :tipoContrato,
                    dataInicio   = :dataInicio,
                    dataFim      = :dataFim
                WHERE idGarantia = :id
            ");
            $stmt->execute([
                ':entidade'     => $entidade ?: null,
                ':tipoContrato' => $tipoContrato ?: null,
                ':dataInicio'   => $dataInicio,
                ':dataFim'      => $dataFim,
                ':id'           => $idGarantia,
            ]);
            $ligacao = null;
            header('Location: garantias.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar as alterações. Verifique a sua ligação à internet.";
        }
    }
}
// Carregar dados atuais da garantia
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("
        SELECT g.*, e.nomeEquipamento, e.codigoInventario
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
} catch (PDOException $err) {
    $erro_sistema = "Erro na ligação à base de dados.";
    $garantia = null;
}
$ligacao = null;
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>Editar Garantia
                </h1>
                <?php if ($garantia): ?>
                <p class="text-muted small mb-0">
                    Equipamento:
                    <?= $garantia->codigoInventario ? '[' . htmlspecialchars($garantia->codigoInventario) . '] ' : '' ?>
                    <strong><?= htmlspecialchars($garantia->nomeEquipamento ?? 'N/D') ?></strong>
                </p>
                <?php endif; ?>
            </div>
            <a href="garantias.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Foram encontrados erros:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($erros as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-warning d-flex align-items-center gap-3">
                    <i class="fa-solid fa-wifi text-warning fs-4"></i>
                    <div><?= htmlspecialchars($erro_sistema) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($garantia): ?>
            <form action="editar_garantia.php?id_garantia=<?= htmlspecialchars($idEncrypted) ?>" method="POST" novalidate>
                <p class="secao-form-titulo mt-0">Dados da Garantia</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Entidade Responsável</label>
                        <input type="text" class="form-control" name="entidade_responsavel"
                               placeholder="Ex: Siemens Healthineers Portugal"
                               value="<?= htmlspecialchars($_POST['entidade_responsavel'] ?? $garantia->entidadeResponsavel ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Contrato</label>
                        <select class="form-select" name="tipo_contrato">
                            <option value="">-- Selecionar --</option>
                            <?php
                            $tipos = [
                                'garantia_fabricante'   => 'Garantia do Fabricante',
                                'contrato_manutencao'   => 'Contrato de Manutenção',
                                'assistencia_tecnica'   => 'Assistência Técnica',
                                'extended_warranty'     => 'Garantia Alargada',
                                'manutencao_preventiva' => 'Manutenção Preventiva',
                                'outro'                 => 'Outro',
                            ];
                            $atual = $_POST['tipo_contrato'] ?? $garantia->tipoContrato ?? '';
                            foreach ($tipos as $v => $l):
                                $sel = ($atual === $v) ? 'selected' : '';
                            ?>
                                <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <p class="secao-form-titulo">Período de Cobertura</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Data de Início <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="data_inicio"
                               value="<?= htmlspecialchars($_POST['data_inicio'] ?? $garantia->dataInicio ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Data de Fim <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="data_fim"
                               value="<?= htmlspecialchars($_POST['data_fim'] ?? $garantia->dataFim ?? '') ?>" required>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="garantias.php" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fa-solid fa-xmark me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>