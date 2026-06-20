<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$idEncrypted  = $_GET['id_fornecedor'] ?? null;
$idFornecedor = aes_decrypt($idEncrypted);
if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: fornecedores.php');
    exit;
}

$fornecedor  = null;
$equipamentos = [];
$erro = '';

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

    $stmtEq = $ligacao->prepare("
        SELECT e.idEquipamento, e.codigoInventario, e.nomeEquipamento, e.estado, e.criticidadeClinica, e.categoria
        FROM Equipamento e
        WHERE e.idFornecedor = :id AND e.ativo = 1
        ORDER BY e.nomeEquipamento
    ");
    $stmtEq->execute([':id' => $idFornecedor]);
    $equipamentos = $stmtEq->fetchAll(PDO::FETCH_OBJ);
    $ligacao = null;
} catch (PDOException $e) {
    $erro = 'bd';
}

$badgeTipo = match($fornecedor->tipoFornecedor ?? '') {
    'fabricante'          => ['bg-success-subtle text-success border-success-subtle', 'Fabricante'],
    'distribuidor'        => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'Distribuidor'],
    'assistencia_tecnica' => ['bg-primary-subtle text-primary border-primary-subtle', 'Assistência Técnica'],
    'consumiveis'         => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'Consumíveis'],
    default               => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'N/D'],
};
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
            <h1 class="fw-bold h2 mb-1">
                <?= htmlspecialchars($fornecedor->nomeFornecedor) ?>
                <span class="badge border <?= $badgeTipo[0] ?> ms-2 fs-6"><?= $badgeTipo[1] ?></span>
            </h1>
            <p class="text-muted small mb-0">
                NIF: <?= htmlspecialchars($fornecedor->nif ?? 'N/D') ?>
                <?php if ($fornecedor->website): ?>
                    · <a href="https://<?= htmlspecialchars($fornecedor->website) ?>" target="_blank" class="text-primary">
                        <i class="fa-solid fa-globe me-1"></i><?= htmlspecialchars($fornecedor->website) ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
        <a href="fornecedores.php" class="btn btn-outline-secondary px-3 py-2">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Informação Geral</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Nome da Entidade</small>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($fornecedor->nomeFornecedor) ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">NIF</small>
                        <p class="mb-0"><?= htmlspecialchars($fornecedor->nif ?? 'N/D') ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Tipo</small>
                        <p class="mb-0">
                            <span class="badge border <?= $badgeTipo[0] ?>"><?= $badgeTipo[1] ?></span>
                        </p>
                    </div>
                    <?php if ($fornecedor->morada): ?>
                    <div class="col-md-12">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Morada</small>
                        <p class="mb-0"><?= htmlspecialchars($fornecedor->morada) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($fornecedor->website): ?>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Website</small>
                        <p class="mb-0">
                            <a href="https://<?= htmlspecialchars($fornecedor->website) ?>" target="_blank" class="text-primary">
                                <?= htmlspecialchars($fornecedor->website) ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php if ($fornecedor->observacoes): ?>
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Observações</small>
                        <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($fornecedor->observacoes)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Equipamentos Associados -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="secao-form-titulo mt-0 mb-0">
                        Equipamentos Associados
                        <span class="badge bg-primary ms-2"><?= count($equipamentos) ?></span>
                    </p>
                </div>
                <?php if (empty($equipamentos)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="fa-solid fa-inbox d-block fs-3 mb-2 opacity-25"></i>
                        Nenhum equipamento associado a este fornecedor.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Código</th>
                                    <th>Equipamento</th>
                                    <th>Categoria</th>
                                    <th>Estado</th>
                                    <th>Criticidade</th>
                                    <th class="text-end pe-3">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipamentos as $eq):
                                    $badgeEstado = match ($eq->estado) {
                                        'operacional' => 'badge-estado-operacional',
                                        'manutencao'  => 'badge-estado-manutencao',
                                        'avariado'    => 'badge-estado-avariado',
                                        default       => 'badge-estado-inativo',
                                    };
                                    $badgeCrit = match ($eq->criticidadeClinica ?? '') {
                                        'Suporte de vida' => 'badge-crit-vida',
                                        'Alta'            => 'badge-crit-alta',
                                        'Media'           => 'badge-crit-media',
                                        default           => 'badge-crit-baixa',
                                    };
                                ?>
                                <tr>
                                    <td class="ps-3" style="font-size:0.82rem; color:#64748b;">
                                        <?= htmlspecialchars($eq->codigoInventario ?? '#' . str_pad($eq->idEquipamento, 3, '0', STR_PAD_LEFT)) ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($eq->nomeEquipamento) ?></strong></td>
                                    <td style="font-size:0.85rem; color:#475569;"><?= htmlspecialchars($eq->categoria ?? 'N/D') ?></td>
                                    <td><span class="badge <?= $badgeEstado ?>"><?= htmlspecialchars($eq->estado) ?></span></td>
                                    <td><span class="badge <?= $badgeCrit ?>"><?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?></span></td>
                                    <td class="text-end pe-3">
                                        <a href="../equipamentos/detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                            class="btn btn-sm btn-tabela-ver">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <p class="secao-form-titulo mt-0">Contactos</p>
                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Telefone</small>
                    <p class="mb-0">
                        <a href="tel:<?= htmlspecialchars($fornecedor->contactoTelefonico ?? '') ?>" class="text-dark">
                            <i class="fa-solid fa-phone me-2 text-primary"></i>
                            <?= htmlspecialchars($fornecedor->contactoTelefonico ?? 'N/D') ?>
                        </a>
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Email</small>
                    <p class="mb-0">
                        <a href="mailto:<?= htmlspecialchars($fornecedor->enderecoEmail ?? '') ?>" class="text-dark">
                            <i class="fa-solid fa-envelope me-2 text-primary"></i>
                            <?= htmlspecialchars($fornecedor->enderecoEmail ?? 'N/D') ?>
                        </a>
                    </p>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Pessoa de Contacto</small>
                    <p class="mb-0">
                        <i class="fa-solid fa-user me-2 text-primary"></i>
                        <?= htmlspecialchars($fornecedor->pessoaContacto ?? 'N/D') ?>
                    </p>
                </div>
            </div>

            <?php if ($_perfil === 'administrador'): ?>
            <div class="d-grid gap-2">
                <a href="editar_fornecedor.php?id_fornecedor=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-acao-primaria fw-bold py-2">
                    <i class="fa-solid fa-pen me-2"></i>Editar Fornecedor
                </a>
                <a href="apagar_fornecedor.php?id_fornecedor=<?= htmlspecialchars($idEncrypted) ?>"
                    class="btn btn-danger py-2">
                    <i class="fa-solid fa-trash me-2"></i>Desativar Fornecedor
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