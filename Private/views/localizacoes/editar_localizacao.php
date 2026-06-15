<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: localizacoes.php');
    exit;
}

$idEncrypted = $_GET['id_localizacao'] ?? null;
$idLocalizacao = aes_decrypt($idEncrypted);

if (!$idLocalizacao || !is_numeric($idLocalizacao)) {
    header('Location: localizacoes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeSala = $_POST['nome_sala'] ?? '';
    $edificio = $_POST['edificio'] ?? '';
    $servico = $_POST['servico'] ?? '';
    $piso = $_POST['piso'] ?? '';

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
            $stmt = $ligacao->prepare("UPDATE Localizacao SET nomeSala = :nomeSala, edificio = :edificio, servico = :servico, piso = :piso WHERE idLocalizacao = :id");
            $stmt->execute([
                ':nomeSala' => $nomeSala,
                ':edificio' => $edificio ?: null,
                ':servico' => $servico ?: null,
                ':piso' => $piso,
                ':id' => $idLocalizacao
            ]);
            $ligacao = null;
            header('Location: localizacoes.php');
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
    $stmt = $ligacao->prepare("SELECT * FROM Localizacao WHERE idLocalizacao = :id");
    $stmt->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmt->execute();
    $localizacao = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$localizacao) {
        header('Location: localizacoes.php');
        exit;
    }
} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $localizacao = null;
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
                <h1 class="fw-bold h2 mb-1 text-dark">Editar Localização</h1>
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
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="editar_localizacao.php?id_localizacao=<?= $idEncrypted ?>" method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome da Sala</label>
                    <input type="text" class="form-control" name="nome_sala" value="<?= htmlspecialchars($localizacao->nomeSala) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Edifício</label>
                    <input type="text" class="form-control" name="edificio" value="<?= htmlspecialchars($localizacao->edificio ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Serviço</label>
                    <input type="text" class="form-control" name="servico" value="<?= htmlspecialchars($localizacao->servico ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Piso</label>
                    <input type="text" class="form-control" name="piso" value="<?= htmlspecialchars($localizacao->piso) ?>" required>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-acao-primaria fw-bold px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Alterações
                    </button>
                    <a href="localizacoes.php" class="btn btn-secondary px-4 py-2">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>