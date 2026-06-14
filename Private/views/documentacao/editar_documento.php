<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: documentacao.php');
    exit;
}

$idEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idEncrypted);

if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: documentacao.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_documento'] ?? '';
    $tipo = $_POST['tipo_documento'] ?? '';
    $ficheiro = $_POST['nome_ficheiro'] ?? '';
    $dataDoc = $_POST['data_documento'] ?? '';
    $dataVal = $_POST['data_validade'] ?? '';

    $erros = validar_nome($nome);
    if (empty($tipo)) $erros[] = "O Tipo é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $stmt = $ligacao->prepare("UPDATE Documentacao SET nomeDocumento = :nome, tipoDocumento = :tipo, nomeFicheiro = :ficheiro, dataDocumento = :dataDoc, dataValidade = :dataVal WHERE idDocumento = :id");
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':ficheiro' => $ficheiro ?: null,
                ':dataDoc' => $dataDoc ?: null,
                ':dataVal' => $dataVal ?: null,
                ':id' => $idDocumento
            ]);
            $ligacao = null;
            header('Location: documentacao.php');
            exit;
        } catch (PDOException $err) {
            $erro = "Erro ao atualizar: " . $err->getMessage();
        }
    }
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $stmt = $ligacao->prepare("SELECT * FROM Documentacao WHERE idDocumento = :id");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$documento) {
        header('Location: documentacao.php');
        exit;
    }
} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $documento = null;
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
                <h1 class="fw-bold h2 mb-1 text-dark">Editar Documento</h1>
            </div>
            <a href="documentacao.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="editar_documento.php?id_documento=<?= $idEncrypted ?>" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome do Documento</label>
                    <input type="text" class="form-control" name="nome_documento" value="<?= htmlspecialchars($documento->nomeDocumento) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Documento</label>
                    <select class="form-select" name="tipo_documento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="manual_utilizador" <?= ($documento->tipoDocumento == 'manual_utilizador') ? 'selected' : '' ?>>Manual de Utilizador</option>
                        <option value="manual_servico" <?= ($documento->tipoDocumento == 'manual_servico') ? 'selected' : '' ?>>Manual de Serviço</option>
                        <option value="certificado_calibracao" <?= ($documento->tipoDocumento == 'certificado_calibracao') ? 'selected' : '' ?>>Certificado de Calibração</option>
                        <option value="contrato_manutencao" <?= ($documento->tipoDocumento == 'contrato_manutencao') ? 'selected' : '' ?>>Contrato de Manutenção</option>
                        <option value="fatura" <?= ($documento->tipoDocumento == 'fatura') ? 'selected' : '' ?>>Fatura</option>
                        <option value="declaracao_conformidade" <?= ($documento->tipoDocumento == 'declaracao_conformidade') ? 'selected' : '' ?>>Declaração de Conformidade</option>
                        <option value="relatorio_tecnico" <?= ($documento->tipoDocumento == 'relatorio_tecnico') ? 'selected' : '' ?>>Relatório Técnico</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome do Ficheiro</label>
                    <input type="text" class="form-control" name="nome_ficheiro" value="<?= htmlspecialchars($documento->nomeFicheiro ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data do Documento</label>
                    <input type="date" class="form-control" name="data_documento" value="<?= htmlspecialchars($documento->dataDocumento ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data de Validade</label>
                    <input type="date" class="form-control" name="data_validade" value="<?= htmlspecialchars($documento->dataValidade ?? '') ?>">
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="documentacao.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>