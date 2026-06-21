<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$idEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idEncrypted);
if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: documentacao.php');
    exit;
}

$documento  = null;
$erro = '';

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("
        SELECT d.*, e.nomeEquipamento, e.codigoInventario, e.idEquipamento as idEq
        FROM Documentacao d
        LEFT JOIN Equipamento e ON d.idEquipamento = e.idEquipamento
        WHERE d.idDocumento = :id
    ");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$documento) {
        header('Location: documentacao.php');
        exit;
    }
    $ligacao = null;
} catch (PDOException $e) {
    $erro = 'bd';
}

$expirado    = $documento->dataValidade && strtotime($documento->dataValidade) < time();
$expiraBreve = $documento->dataValidade && !$expirado && (strtotime($documento->dataValidade) - time()) / 86400 <= 30;
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
            <h1 class="fw-bold h2 mb-1"><?= htmlspecialchars($documento->nomeDocumento) ?></h1>
            <p class="text-muted small mb-0">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $documento->tipoDocumento ?? 'N/D'))) ?>
                </span>
            </p>
        </div>
        <a href="documentacao.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row g-4">

        <!-- COLUNA ESQUERDA -->
        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Informação Geral</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Nome do Documento</small>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($documento->nomeDocumento) ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tipo</small>
                        <p class="mb-0">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $documento->tipoDocumento ?? 'N/D'))) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data do Documento</small>
                        <p class="mb-0"><?= $documento->dataDocumento ? date('d/m/Y', strtotime($documento->dataDocumento)) : 'N/D' ?></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Data de Validade</small>
                        <p class="mb-0">
                            <?php if ($documento->dataValidade): ?>
                                <span class="<?= $expirado ? 'text-danger fw-bold' : ($expiraBreve ? 'text-warning fw-bold' : '') ?>">
                                    <?= date('d/m/Y', strtotime($documento->dataValidade)) ?>
                                    <?php if ($expirado): ?>
                                        <span class="badge bg-danger ms-1">Expirado</span>
                                    <?php elseif ($expiraBreve): ?>
                                        <span class="badge bg-warning text-dark ms-1">Expira em breve</span>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sem validade</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Equipamento Associado -->
            <?php if ($documento->nomeEquipamento): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="secao-form-titulo mt-0 mb-0">Equipamento Associado</p>
                    <a href="../equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($documento->idEq) ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver Equipamento
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Código</small>
                        <p class="mb-0"><?= htmlspecialchars($documento->codigoInventario ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-8">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Equipamento</small>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($documento->nomeEquipamento) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ficheiro PDF -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Ficheiro</p>
                <?php if ($documento->nomeFicheiro): ?>
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-file-pdf text-danger fs-2"></i>
                        <div>
                            <p class="fw-semibold mb-2"><?= htmlspecialchars($documento->nomeFicheiro) ?></p>
                            <div class="d-flex gap-2">
                                <a href="/sibdas/1231343/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($documento->nomeFicheiro) ?>"
                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-eye me-1"></i>Abrir PDF
                                </a>
                                <a href="/sibdas/1231343/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($documento->nomeFicheiro) ?>"
                                    download class="btn btn-sm btn-acao-primaria">
                                    <i class="fa-solid fa-download me-1"></i>Descarregar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-file-circle-xmark me-2 opacity-50"></i>
                        Nenhum ficheiro associado a este documento.
                    </p>
                    <?php if ($_perfil === 'administrador'): ?>
                        <a href="editar_documento.php?id_documento=<?= htmlspecialchars($idEncrypted) ?>"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-upload me-1"></i>Carregar PDF
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- COLUNA DIREITA -->
        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Estado</p>
                <?php if ($expirado): ?>
                    <span class="badge bg-danger fs-6">Expirado</span>
                <?php elseif ($expiraBreve): ?>
                    <span class="badge bg-warning text-dark fs-6">Expira em breve</span>
                <?php else: ?>
                    <span class="badge bg-success fs-6">Válido</span>
                <?php endif; ?>
            </div>

            <?php if ($_perfil === 'administrador'): ?>
            <div class="d-grid gap-2">
                <a href="editar_documento.php?id_documento=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-acao-primaria fw-bold py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar Documento
                </a>
                <a href="apagar_documento.php?id_documento=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-danger py-2">
                    <i class="fa-solid fa-trash me-2"></i>Apagar Documento
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