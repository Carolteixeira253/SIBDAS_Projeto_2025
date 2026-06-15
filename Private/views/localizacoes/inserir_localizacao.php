<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeSala = $_POST["nome_sala"] ?? "";
    $edificio = $_POST["edificio"] ?? "";
    $servico = $_POST["servico"] ?? "";
    $piso = $_POST["piso"] ?? "";

    $erros = [];
    if (empty(trim($nomeSala))) $erros[] = "O Nome da Sala é obrigatório.";
    if (empty(trim($piso))) $erros[] = "O Piso é obrigatório.";

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $ligacao->prepare("INSERT INTO Localizacao (nomeSala, edificio, servico, piso) VALUES (:nomeSala, :edificio, :servico, :piso)");
            $stmt->execute([
                ':nomeSala' => $nomeSala,
                ':edificio' => $edificio ?: null,
                ':servico' => $servico ?: null,
                ':piso' => $piso
            ]);
            $ligacao = null;
            header('Location: localizacoes.php');
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
                <h1 class="fw-bold h2 mb-1 text-dark">Nova Localização</h1>
                <p class="text-muted small mb-0">Preencha os dados da nova localização.</p>
            </div>
            <a href="localizacoes.php" class="btn btn-secondary fw-bold px-3 py-2">
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
                    <label class="form-label fw-semibold">Nome da Sala</label>
                    <input type="text" class="form-control" name="nome_sala" value="<?= $_POST['nome_sala'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Edifício</label>
                    <input type="text" class="form-control" name="edificio" value="<?= $_POST['edificio'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Serviço</label>
                    <input type="text" class="form-control" name="servico" value="<?= $_POST['servico'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Piso</label>
                    <input type="text" class="form-control" name="piso" value="<?= $_POST['piso'] ?? '' ?>" required>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Localização
                    </button>
                    <a href="localizacoes.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>