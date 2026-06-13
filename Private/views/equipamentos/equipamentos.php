<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();

// LIGAÇÃO À BASE DE DADOS
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Equipamento")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = "Erro: " . $err->getMessage();
    $resultados = [];
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
                <h1 class="fw-bold h2 mb-1 text-dark">Inventário de Equipamentos</h1>
                <p class="text-muted small mb-0">Gestão e monitorização de dispositivos médicos.</p>
            </div>
            <button class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEquipamento">
                <i class="fa-solid fa-plus me-2"></i>Adicionar Equipamento
            </button>
        </div>

        <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Equipamento</th>
                            <th>Categoria</th>
                            <th>Estado</th>
                            <th>Localização</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider" id="tabelaEquipamentos">
                        <?php if (!empty($erro)) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-danger"><?= $erro ?></td>
                            </tr>
                        <?php elseif (count($resultados) == 0) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Não existem equipamentos registados.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($resultados as $equipamento) : ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?= str_pad($equipamento->idEquipamento, 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><strong><?= htmlspecialchars($equipamento->nomeEquipamento) ?></strong></td>
                                    <td><?= htmlspecialchars($equipamento->categoria) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($equipamento->estado) {
                                            'operacional' => 'bg-success-subtle text-success border-success-subtle',
                                            'manutencao' => 'bg-warning-subtle text-warning border-warning-subtle',
                                            'avariado' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            default => 'bg-secondary-subtle text-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?> border px-3"><?= htmlspecialchars($equipamento->estado) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($equipamento->idLocalizacao) ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalEquipamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Novo Equipamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEquipamento">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome do Equipamento</label>
                        <input type="text" class="form-control" id="nomeEquip" placeholder="Ex: Ventilador" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Categoria</label>
                        <select class="form-select" id="catEquip" required>
                            <option value="Monitorização">Monitorização</option>
                            <option value="Suporte de Vida">Suporte de Vida</option>
                            <option value="Diagnóstico">Diagnóstico</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Equipamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/medcare-inventory-solutions/Private/assets/js/equipamentos.js"></script>

<?php include '../../includes/footer.php'; ?>