<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome_fornecedor"] ?? "";
    $nif = $_POST["nif_fornecedor"] ?? "";
    $telefone = $_POST["telefone_fornecedor"] ?? "";
    $email = $_POST["email_fornecedor"] ?? "";
    $morada = $_POST["morada_fornecedor"] ?? "";

    $erros = validar_nome($nome);

    if (empty($nif)) $erros[] = "O NIF é obrigatório.";
    if (empty($email)) $erros[] = "O Email é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $ligacao->prepare("INSERT INTO Fornecedor (nomeFornecedor, nif, contactoTelefonico, enderecoEmail, morada) VALUES (:nome, :nif, :telefone, :email, :morada)");
            $stmt->execute([
                ':nome' => $nome,
                ':nif' => $nif,
                ':telefone' => $telefone,
                ':email' => $email,
                ':morada' => $morada
            ]);
            $ligacao = null;
            header('Location: fornecedores.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">Novo Fornecedor</h1>
                <p class="text-muted small mb-0">Preencha os dados do novo fornecedor.</p>
            </div>
            <a href="fornecedores.php" class="btn btn-secondary fw-bold px-3 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 p-4">
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger" role="alert">
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
                    <label class="form-label fw-semibold">Nome da Entidade</label>
                    <input type="text" class="form-control" name="nome_fornecedor" value="<?= $_POST['nome_fornecedor'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">NIF</label>
                    <input type="text" class="form-control" name="nif_fornecedor" maxlength="9" value="<?= $_POST['nif_fornecedor'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Contacto Telefónico</label>
                    <input type="tel" class="form-control" name="telefone_fornecedor" value="<?= $_POST['telefone_fornecedor'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" name="email_fornecedor" value="<?= $_POST['email_fornecedor'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Morada</label>
                    <input type="text" class="form-control" name="morada_fornecedor" value="<?= $_POST['morada_fornecedor'] ?? '' ?>">
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Fornecedor
                    </button>
                    <a href="fornecedores.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>