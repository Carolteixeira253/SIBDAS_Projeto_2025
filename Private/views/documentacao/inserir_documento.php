<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
redirect_if_not_admin();

$erros        = [];
$erro_sistema = '';
$equipamentos = [];

// Pasta onde os PDFs ficam guardados
define('PASTA_DOCUMENTOS', __DIR__ . '/../../../Private/documentos/');

// Carregar equipamentos para o dropdown
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $equipamentos = $ligacao->query("
        SELECT idEquipamento, nomeEquipamento, codigoInventario
        FROM Equipamento WHERE ativo = 1
        ORDER BY nomeEquipamento
    ")->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $err) {
    $erro_sistema = "Não foi possível carregar os equipamentos. Verifique a sua ligação à internet.";
}

// Pré-seleccionar equipamento se vier da página de detalhes
$idEquipPresel = '';
if (isset($_GET['id_equipamento'])) {
    $idEquipPresel = aes_decrypt($_GET['id_equipamento']);
}

// Processar POST (Ficha 12)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim($_POST['nome_documento'] ?? '');
    $tipo    = $_POST['tipo_documento']     ?? '';
    $dataDoc = $_POST['data_documento']     ?? '';
    $dataVal = $_POST['data_validade']      ?? '';
    $idEquip = $_POST['id_equipamento']     ?? '';

    // Validação ($erros[])
    if (empty($nome))    $erros[] = "O Nome do Documento é obrigatório.";
    if (strlen($nome) < 2) $erros[] = "O Nome do Documento deve ter pelo menos 2 caracteres.";
    if (empty($tipo))    $erros[] = "O Tipo de Documento é obrigatório.";
    if (empty($idEquip)) $erros[] = "O Equipamento associado é obrigatório.";
    if (!empty($dataDoc) && !strtotime($dataDoc)) $erros[] = "A Data do Documento é inválida.";
    if (!empty($dataVal) && !strtotime($dataVal)) $erros[] = "A Data de Validade é inválida.";

    // Validação do ficheiro PDF
    $nomeFicheiro = null;
    if (!empty($_FILES['ficheiro_pdf']['name'])) {
        $ficheiro = $_FILES['ficheiro_pdf'];
        $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));

        if ($extensao !== 'pdf') {
            $erros[] = "Apenas são aceites ficheiros PDF (.pdf).";
        } elseif ($ficheiro['size'] > 10 * 1024 * 1024) {
            $erros[] = "O ficheiro não pode ultrapassar 10 MB.";
        } elseif ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            $erros[] = "Ocorreu um erro ao carregar o ficheiro. Tente novamente.";
        } else {
            $nomeBase     = preg_replace('/[^a-zA-Z0-9_\-]/', '', pathinfo($ficheiro['name'], PATHINFO_FILENAME));
            $nomeFicheiro = 'doc_' . time() . '_' . $nomeBase . '.pdf';
        }
    }

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                INSERT INTO Documentacao
                    (nomeDocumento, tipoDocumento, nomeFicheiro, dataDocumento, dataValidade, idEquipamento)
                VALUES
                    (:nome, :tipo, :ficheiro, :dataDoc, :dataVal, :idEquip)
            ");
            $stmt->execute([
                ':nome'     => $nome,
                ':tipo'     => $tipo,
                ':ficheiro' => $nomeFicheiro,
                ':dataDoc'  => !empty($dataDoc) ? $dataDoc : null,
                ':dataVal'  => !empty($dataVal) ? $dataVal : null,
                ':idEquip'  => $idEquip,
            ]);
            $ligacao = null;

            // Mover o PDF para a pasta de documentos
            if ($nomeFicheiro && !empty($_FILES['ficheiro_pdf']['tmp_name'])) {
                if (!is_dir(PASTA_DOCUMENTOS)) {
                    mkdir(PASTA_DOCUMENTOS, 0755, true);
                }
                move_uploaded_file($_FILES['ficheiro_pdf']['tmp_name'], PASTA_DOCUMENTOS . $nomeFicheiro);
            }

            header('Location: documentacao.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar o documento. Verifique a sua ligação à internet.";
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
                <h1 class="fw-bold h2 mb-1 text-dark">
                    <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>Novo Documento
                </h1>
                <p class="text-muted small mb-0">
                    Associe um ficheiro PDF a um equipamento do inventário.
                    Os campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
            </div>
            <a href="documentacao.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger" role="alert">
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

            <form id="formInserirDoc" action="" method="POST" enctype="multipart/form-data" novalidate>

                <!-- SECÇÃO 1: Identificação do documento -->
                <p class="secao-form-titulo mt-0">Identificação</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Nome do Documento <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control campo-obrigatorio"
                               name="nome_documento"
                               placeholder="Ex: Manual de Utilização PB980"
                               value="<?= htmlspecialchars($_POST['nome_documento'] ?? '') ?>">
                        <div class="invalid-feedback">O nome do documento é obrigatório.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Tipo de Documento <span class="text-danger">*</span>
                        </label>
                        <select class="form-select campo-obrigatorio" name="tipo_documento">
                            <option value="">-- Selecionar --</option>
                            <?php
                            $tipos = [
                                'manual_utilizador'       => 'Manual de Utilizador',
                                'manual_servico'          => 'Manual de Serviço',
                                'certificado_calibracao'  => 'Certificado de Calibração',
                                'contrato_manutencao'     => 'Contrato de Manutenção',
                                'fatura'                  => 'Fatura',
                                'declaracao_conformidade' => 'Declaração de Conformidade',
                                'relatorio_tecnico'       => 'Relatório Técnico',
                            ];
                            foreach ($tipos as $v => $l):
                                $sel = (($_POST['tipo_documento'] ?? '') === $v) ? 'selected' : '';
                            ?>
                                <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione o tipo de documento.</div>
                    </div>
                </div>

                <!-- SECÇÃO 2: Equipamento e datas -->
                <p class="secao-form-titulo">Equipamento e Datas</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Equipamento Associado <span class="text-danger">*</span>
                        </label>
                        <select class="form-select campo-obrigatorio" name="id_equipamento">
                            <option value="">-- Selecionar Equipamento --</option>
                            <?php
                            $idEqAtual = $_POST['id_equipamento'] ?? $idEquipPresel ?? '';
                            foreach ($equipamentos as $eq):
                                $lbl = htmlspecialchars($eq->nomeEquipamento);
                                if ($eq->codigoInventario) $lbl = '[' . htmlspecialchars($eq->codigoInventario) . '] ' . $lbl;
                                $sel = ($idEqAtual == $eq->idEquipamento) ? 'selected' : '';
                            ?>
                                <option value="<?= $eq->idEquipamento ?>" <?= $sel ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione o equipamento.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Data do Documento</label>
                        <input type="date" class="form-control" name="data_documento"
                               value="<?= htmlspecialchars($_POST['data_documento'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Data de Validade</label>
                        <input type="date" class="form-control" name="data_validade"
                               value="<?= htmlspecialchars($_POST['data_validade'] ?? '') ?>">
                    </div>
                </div>

                <!-- SECÇÃO 3: Upload do PDF -->
                <p class="secao-form-titulo">Ficheiro PDF</p>
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>Carregar Ficheiro PDF
                    </label>
                    <input type="file" class="form-control" name="ficheiro_pdf" accept=".pdf">
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Aceita apenas ficheiros <strong>.pdf</strong>. Tamanho máximo: <strong>10 MB</strong>.
                        O upload é opcional — pode ser adicionado mais tarde.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="documentacao.php" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fa-solid fa-xmark me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Documento
                    </button>
                </div>

            </form>
        </div>
    </main>
</div>

<!-- Validação JS lado cliente -->
<script>
document.getElementById('formInserirDoc').addEventListener('submit', function(e) {
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
document.querySelectorAll('.campo-obrigatorio').forEach(function(campo) {
    campo.addEventListener('input', function() {
        if (this.value.trim()) this.classList.remove('is-invalid');
    });
    campo.addEventListener('change', function() {
        if (this.value.trim()) this.classList.remove('is-invalid');
    });
});
</script>

<?php include '../../includes/footer.php'; ?>