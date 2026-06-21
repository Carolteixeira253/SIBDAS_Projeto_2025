<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

define('PASTA_DOCUMENTOS', __DIR__ . '/../../../Private/documentos/');

$idEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idEncrypted);
if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: documentacao.php');
    exit;
}

$erros        = [];
$erro_sistema = '';
$documento    = null;
$equipamentos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim($_POST['nome_documento'] ?? '');
    $tipo    = $_POST['tipo_documento'] ?? '';
    $dataDoc = $_POST['data_documento'] ?? '';
    $dataVal = $_POST['data_validade'] ?? '';
    $idEquip = $_POST['id_equipamento'] ?? '';

    if (empty($nome)) $erros[] = "O Nome do Documento é obrigatório.";
    if (empty($tipo)) $erros[] = "O Tipo é obrigatório.";

    $nomeFicheiro = $_POST['nome_ficheiro_atual'] ?? null;

    if (!empty($_FILES['ficheiro_pdf']['name'])) {
        $ficheiro  = $_FILES['ficheiro_pdf'];
        $extensao  = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
        if ($extensao !== 'pdf') {
            $erros[] = "Apenas são aceites ficheiros PDF.";
        } elseif ($ficheiro['size'] > 10 * 1024 * 1024) {
            $erros[] = "O ficheiro não pode ultrapassar 10 MB.";
        } elseif ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            $erros[] = "Erro ao carregar o ficheiro.";
        } else {
            $nomeBase     = preg_replace('/[^a-zA-Z0-9_\-]/', '', pathinfo($ficheiro['name'], PATHINFO_FILENAME));
            $nomeFicheiro = 'doc_' . time() . '_' . $nomeBase . '.pdf';
        }
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                UPDATE Documentacao SET
                    nomeDocumento = :nome,
                    tipoDocumento = :tipo,
                    nomeFicheiro  = :ficheiro,
                    dataDocumento = :dataDoc,
                    dataValidade  = :dataVal,
                    idEquipamento = :idEquip
                WHERE idDocumento = :id
            ");
            $stmt->execute([
                ':nome'     => $nome,
                ':tipo'     => $tipo,
                ':ficheiro' => $nomeFicheiro,
                ':dataDoc'  => $dataDoc ?: null,
                ':dataVal'  => $dataVal ?: null,
                ':idEquip'  => $idEquip ?: null,
                ':id'       => $idDocumento,
            ]);
            $ligacao = null;

            if (!empty($_FILES['ficheiro_pdf']['tmp_name']) && $nomeFicheiro) {
                if (!is_dir(PASTA_DOCUMENTOS)) mkdir(PASTA_DOCUMENTOS, 0755, true);
                move_uploaded_file($_FILES['ficheiro_pdf']['tmp_name'], PASTA_DOCUMENTOS . $nomeFicheiro);
            }

            header('Location: documentacao.php?sucesso=editado');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao guardar alterações.";
        }
    }
}

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("SELECT * FROM Documentacao WHERE idDocumento = :id");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$documento) {
        header('Location: documentacao.php');
        exit;
    }
    $equipamentos = $ligacao->query("
        SELECT idEquipamento, nomeEquipamento, codigoInventario
        FROM Equipamento WHERE ativo = 1
        ORDER BY nomeEquipamento
    ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os dados.";
}

function val($campo, $bd) {
    return htmlspecialchars($_POST[$campo] ?? $bd ?? '');
}
function sel($campo, $bd, $valor) {
    $atual = $_POST[$campo] ?? $bd ?? '';
    return $atual == $valor ? 'selected' : '';
}
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">Editar Documento</h1>
            <p class="text-muted small mb-0">
                Actualize os dados do documento. Campos obrigatórios <span class="text-danger">*</span>
            </p>
        </div>
        <a href="documentacao.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger mb-4">
            <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Corrige os seguintes erros:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($erros as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($erro_sistema)): ?>
        <div class="alert alert-warning mb-4">
            <i class="fa-solid fa-wifi me-2"></i><?= htmlspecialchars($erro_sistema) ?>
        </div>
    <?php endif; ?>

    <?php if ($documento): ?>
    <form id="formEditarDocumento"
          action="editar_documento.php?id_documento=<?= htmlspecialchars($idEncrypted) ?>"
          method="POST" enctype="multipart/form-data" novalidate>

        <input type="hidden" name="nome_ficheiro_atual" value="<?= htmlspecialchars($documento->nomeFicheiro ?? '') ?>">

        <div class="row g-4">

            <!-- COLUNA ESQUERDA -->
            <div class="col-12 col-lg-8">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Identificação</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nome do Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control campo-obrigatorio" name="nome_documento"
                                value="<?= val('nome_documento', $documento->nomeDocumento) ?>">
                            <div class="invalid-feedback">O nome é obrigatório.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select campo-obrigatorio" name="tipo_documento">
                                <option value="">Selecione...</option>
                                <?php foreach ([
                                    'manual_utilizador'       => 'Manual de Utilizador',
                                    'manual_servico'          => 'Manual de Serviço',
                                    'certificado_calibracao'  => 'Certificado de Calibração',
                                    'contrato_manutencao'     => 'Contrato de Manutenção',
                                    'fatura'                  => 'Fatura',
                                    'declaracao_conformidade' => 'Declaração de Conformidade',
                                    'relatorio_tecnico'       => 'Relatório Técnico',
                                ] as $v => $l): ?>
                                    <option value="<?= $v ?>" <?= sel('tipo_documento', $documento->tipoDocumento, $v) ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione o tipo.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Equipamento Associado</label>
                            <select class="form-select" name="id_equipamento">
                                <option value="">Sem equipamento associado</option>
                                <?php foreach ($equipamentos as $eq):
                                    $lbl = htmlspecialchars($eq->nomeEquipamento);
                                    if ($eq->codigoInventario) $lbl = '[' . htmlspecialchars($eq->codigoInventario) . '] ' . $lbl;
                                ?>
                                    <option value="<?= $eq->idEquipamento ?>" <?= sel('id_equipamento', $documento->idEquipamento, $eq->idEquipamento) ?>>
                                        <?= $lbl ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Data do Documento</label>
                            <input type="date" class="form-control" name="data_documento"
                                value="<?= val('data_documento', $documento->dataDocumento) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Data de Validade</label>
                            <input type="date" class="form-control" name="data_validade"
                                value="<?= val('data_validade', $documento->dataValidade) ?>">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Ficheiro PDF</p>
                    <?php if ($documento->nomeFicheiro): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="fa-solid fa-file-pdf text-danger fs-2"></i>
                            <div>
                                <p class="fw-semibold mb-1"><?= htmlspecialchars($documento->nomeFicheiro) ?></p>
                                <a href="/sibdas/1231343/medcare-inventory-solutions/Private/documentos/<?= htmlspecialchars($documento->nomeFicheiro) ?>"
                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-file-pdf me-1"></i>Abrir PDF actual
                                </a>
                            </div>
                        </div>
                        <label class="form-label fw-semibold">Substituir PDF</label>
                    <?php else: ?>
                        <label class="form-label fw-semibold">Carregar PDF</label>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="ficheiro_pdf" accept=".pdf">
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Apenas ficheiros <strong>.pdf</strong>. Tamanho máximo: <strong>10 MB</strong>.
                    </div>
                </div>

            </div>

            <!-- COLUNA DIREITA -->
            <div class="col-12 col-lg-4">

                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <p class="secao-form-titulo mt-0">Documento Actual</p>
                    <p class="fw-bold mb-1"><?= htmlspecialchars($documento->nomeDocumento) ?></p>
                    <p class="text-muted small mb-0">
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $documento->tipoDocumento ?? 'N/D'))) ?>
                    </p>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-acao-primaria fw-bold py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="documentacao.php" class="btn btn-outline-secondary py-2">
                        <i class="fa-solid fa-xmark me-2"></i>Cancelar
                    </a>
                </div>

            </div>
        </div>
    </form>
    <?php endif; ?>

</main>
</div>
</div>

<script>
document.getElementById('formEditarDocumento').addEventListener('submit', function(e) {
    let valido = true;
    document.querySelectorAll('.campo-obrigatorio').forEach(function(campo) {
        campo.classList.remove('is-invalid');
        if (!campo.value.trim()) {
            campo.classList.add('is-invalid');
            valido = false;
        }
    });
    if (!valido) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>