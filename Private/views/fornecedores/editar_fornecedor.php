<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
redirect_if_not_admin();

$idEncrypted  = $_GET['id_fornecedor'] ?? null;
$idFornecedor = aes_decrypt($idEncrypted);

if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: fornecedores.php');
    exit;
}

$erros        = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome_fornecedor']    ?? '');
    $nif      = trim($_POST['nif_fornecedor']     ?? '');
    $tipo     = $_POST['tipo_fornecedor']          ?? '';
    $telefone = trim($_POST['telefone_fornecedor'] ?? '');
    $email    = trim($_POST['email_fornecedor']    ?? '');
    $morada   = trim($_POST['morada_fornecedor']   ?? '');

    if (empty($nome))  $erros[] = "O Nome da Entidade é obrigatório.";
    if (strlen($nome) < 2) $erros[] = "O Nome deve ter pelo menos 2 caracteres.";
    if (empty($nif))   $erros[] = "O NIF é obrigatório.";
    if (!preg_match('/^\d{9}$/', $nif)) $erros[] = "O NIF deve ter exatamente 9 dígitos.";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erros[] = "O Email introduzido não é válido.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("
                UPDATE Fornecedor
                SET nomeFornecedor    = :nome,
                    nif               = :nif,
                    tipoFornecedor    = :tipo,
                    contactoTelefonico = :telefone,
                    enderecoEmail     = :email,
                    morada            = :morada
                WHERE idFornecedor = :id
            ");
            $stmt->execute([
                ':nome'     => $nome,
                ':nif'      => $nif,
                ':tipo'     => $tipo ?: null,
                ':telefone' => $telefone ?: null,
                ':email'    => $email ?: null,
                ':morada'   => $morada ?: null,
                ':id'       => $idFornecedor,
            ]);
            $ligacao = null;
            header('Location: fornecedores.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Não foi possível guardar as alterações. Verifique a sua ligação à internet.";
        }
    }
}

// Carregar dados atuais
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("SELECT * FROM Fornecedor WHERE idFornecedor = :id");
    $stmt->bindParam(':id', $idFornecedor, PDO::PARAM_INT);
    $stmt->execute();
    $fornecedor = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$fornecedor) {
        header('Location: fornecedores.php');
        exit;
    }
} catch (PDOException $err) {
    $erro_sistema = "Erro na ligação à base de dados.";
    $fornecedor = null;
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
                    <i class="fa-solid fa-truck text-primary me-2"></i>Editar Fornecedor
                </h1>
                <p class="text-muted small mb-0">Atualizar os dados do fornecedor.</p>
            </div>
            <a href="fornecedores.php" class="btn btn-secondary fw-bold px-3 py-2">
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

            <?php if ($fornecedor): ?>
            <form action="editar_fornecedor.php?id_fornecedor=<?= htmlspecialchars($idEncrypted) ?>" method="POST" novalidate>

                <p class="secao-form-titulo mt-0">Identificação</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome da Entidade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome_fornecedor"
                               value="<?= htmlspecialchars($_POST['nome_fornecedor'] ?? $fornecedor->nomeFornecedor) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">NIF <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nif_fornecedor" maxlength="9"
                               value="<?= htmlspecialchars($_POST['nif_fornecedor'] ?? $fornecedor->nif ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tipo de Fornecedor</label>
                        <select class="form-select" name="tipo_fornecedor">
                            <option value="">-- Selecionar --</option>
                            <?php
                            $tipos = [
                                'fabricante'         => 'Fabricante',
                                'distribuidor'       => 'Distribuidor',
                                'assistencia_tecnica'=> 'Assistência Técnica',
                                'manutencao'         => 'Manutenção',
                                'locacao'            => 'Locação / Leasing',
                                'consultoria'        => 'Consultoria',
                                'outro'              => 'Outro',
                            ];
                            $atual = $_POST['tipo_fornecedor'] ?? $fornecedor->tipoFornecedor ?? '';
                            foreach ($tipos as $v => $l):
                                $sel = ($atual === $v) ? 'selected' : '';
                            ?>
                                <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <p class="secao-form-titulo">Contactos</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Contacto Telefónico</label>
                        <input type="tel" class="form-control" name="telefone_fornecedor"
                               value="<?= htmlspecialchars($_POST['telefone_fornecedor'] ?? $fornecedor->contactoTelefonico ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="email_fornecedor"
                               value="<?= htmlspecialchars($_POST['email_fornecedor'] ?? $fornecedor->enderecoEmail ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Morada</label>
                        <input type="text" class="form-control" name="morada_fornecedor"
                               value="<?= htmlspecialchars($_POST['morada_fornecedor'] ?? $fornecedor->morada ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="fornecedores.php" class="btn btn-outline-secondary px-4 py-2">
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