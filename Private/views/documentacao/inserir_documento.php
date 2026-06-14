<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome_documento"] ?? "";
    $tipo = $_POST["tipo_documento"] ?? "";
    $ficheiro = $_POST["nome_ficheiro"] ?? "";
    $dataDoc = $_POST["data_documento"] ?? "";
    $dataVal = $_POST["data_validade"] ?? "";
    $idEquipamento = $_POST["id_equipamento"] ?? "";

    $erros = validar_nome($nome);
    if (empty($tipo)) $erros[] = "O Tipo é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Documentacao (nomeDocumento, tipoDocumento, nomeFicheiro, dataDocumento, dataValidade, idEquipamento) VALUES (:nome, :tipo, :ficheiro, :dataDoc, :dataVal, :idEquipamento)");
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':ficheiro' => $ficheiro ?: null,
                ':dataDoc' => !empty($dataDoc) ? $dataDoc : null,
                ':dataVal' => !empty($dataVal) ? $dataVal : null,
                ':idEquipamento' => $idEquipamento
            ]);
            $ligacao = null;
            header('Location: documentacao.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar: " . $err->getMessage();
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="content-body">
    <?php include '../../includes/sidebar.php'; ?>
    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">Novo Documento</h1>
                <p class="text-muted small mb-0">Preencha os dados do novo documento.</p>
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
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <form action="#" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome do Documento</label>
                    <input type="text" class="form-control" name="nome_documento" value="<?= $_POST['nome_documento'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Documento</label>
                    <select class="form-select" name="tipo_documento" required>
                        <option value="">-- Selecionar --</option>
                        <option value="manual_utilizador" <?= (($_POST['tipo_documento'] ?? '') == 'manual_utilizador') ? 'selected' : '' ?>>Manual de Utilizador</option>
                        <option value="manual_servico" <?= (($_POST['tipo_documento'] ?? '') == 'manual_servico') ? 'selected' : '' ?>>Manual de Serviço</option>
                        <option value="certificado_calibracao" <?= (($_POST['tipo_documento'] ?? '') == 'certificado_calibracao') ? 'selected' : '' ?>>Certificado de Calibração</option>
                        <option value="contrato_manutencao" <?= (($_POST['tipo_documento'] ?? '') == 'contrato_manutencao') ? 'selected' : '' ?>>Contrato de Manutenção</option>
                        <option value="fatura" <?= (($_POST['tipo_documento'] ?? '') == 'fatura') ? 'selected' : '' ?>>Fatura</option>
                        <option value="declaracao_conformidade" <?= (($_POST['tipo_documento'] ?? '') == 'declaracao_conformidade') ? 'selected' : '' ?>>Declaração de Conformidade</option>
                        <option value="relatorio_tecnico" <?= (($_POST['tipo_documento'] ?? '') == 'relatorio_tecnico') ? 'selected' : '' ?>>Relatório Técnico</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ID do Equipamento</label>
                    <input type="number" class="form-control" name="id_equipamento" value="<?= $_POST['id_equipamento'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Data do Documento</label>
                    <input type="date" class="form-control" name="data_documento" value="<?= $_POST['data_documento'] ?? '' ?>">
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Documento
                    </button>
                    <a href="documentacao.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>