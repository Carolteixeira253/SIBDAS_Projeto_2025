<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

// Filtro ativo / inativo
$mostrarInativos = isset($_GET['inativos']) && $_GET['inativos'] == '1';
$filtroAtivo = $mostrarInativos ? 0 : 1;

// LIGAÇÃO À BASE DE DADOS
$erro = '';
$resultados = [];
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $ligacao->prepare("
        SELECT e.*, l.nomeSala, l.servico
        FROM Equipamento e
        LEFT JOIN Localizacao l ON e.idLocalizacao = l.idLocalizacao
        WHERE e.ativo = :ativo
        ORDER BY e.idEquipamento DESC
    ");
    $stmt->execute([':ativo' => $filtroAtivo]);
    $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $err) {
    $erro = 'bd';
}
$ligacao = null;
?>
<?php include '../../includes/header.php'; ?>
<div class="app-viewport">
<?php include '../../includes/nav.php'; ?>
<div class="content-body">
<?php include '../../includes/sidebar.php'; ?>

<main class="main-content-wrapper">

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold h2 mb-1">Inventário de Equipamentos</h1>
            <p class="text-muted small mb-0">Gestão e monitorização de dispositivos médicos.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($_perfil === 'administrador'): ?>
                <a href="inserir_equipamento.php" class="btn btn-acao-primaria fw-bold px-3 py-2">
                    <i class="fa-solid fa-plus me-2"></i>Adicionar
                </a>
            <?php endif; ?>
            <a href="exportar_csv.php" class="btn btn-outline-secondary px-3 py-2" title="Exportar CSV">
                <i class="fa-solid fa-file-csv me-2"></i>Exportar CSV
            </a>
        </div>
    </div>

    <!-- Filtros Ativo / Inativo -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="equipamentos.php"
            class="btn btn-sm <?= !$mostrarInativos ? 'btn-acao-primaria' : 'btn-outline-secondary' ?>">
            <i class="fa-solid fa-circle-check me-1"></i> Ativos
        </a>
        <a href="equipamentos.php?inativos=1"
            class="btn btn-sm <?= $mostrarInativos ? 'btn-secondary' : 'btn-outline-secondary' ?>">
            <i class="fa-solid fa-ban me-1"></i> Inativos
        </a>
    </div>

    <?php if ($erro === 'bd'): ?>
        <?= mensagem_erro_bd() ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table id="tabela-equipamentos" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Equipamento</th>
                            <th>Categoria</th>
                            <th>Estado</th>
                            <th>Localização</th>
                            <th>Criticidade</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resultados) == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-inbox fs-3 d-block mb-2 opacity-25"></i>
                                    <?= $mostrarInativos ? 'Não existem equipamentos inativos.' : 'Não existem equipamentos registados.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $eq): ?>
                                <?php
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
                                $localizacao = $eq->nomeSala
                                    ? htmlspecialchars($eq->nomeSala . ($eq->servico ? ' — ' . $eq->servico : ''))
                                    : 'N/D';
                                $codigo = $eq->codigoInventario
                                    ?? '#' . str_pad($eq->idEquipamento, 3, '0', STR_PAD_LEFT);
                                ?>
                                <tr>
                                    <td class="ps-4" style="font-size:0.82rem; color:#64748b;">
                                        <?= htmlspecialchars($codigo) ?>
                                    </td>
                                    <td>
                                        <strong style="color:#0f172a;"><?= htmlspecialchars($eq->nomeEquipamento) ?></strong>
                                        <?php if ($eq->marca): ?>
                                            <br><small class="text-muted">
                                                <?= htmlspecialchars($eq->marca) ?>
                                                <?= $eq->modelo ? ' · ' . htmlspecialchars($eq->modelo) : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#475569;"><?= htmlspecialchars($eq->categoria ?? 'N/D') ?></td>
                                    <td>
                                        <span class="badge <?= $badgeEstado ?>">
                                            <?= htmlspecialchars($eq->estado) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.85rem; color:#64748b;"><?= $localizacao ?></td>
                                    <td>
                                        <span class="badge <?= $badgeCrit ?>">
                                            <?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                            class="btn btn-sm btn-tabela-ver me-1" title="Ver detalhes">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($_perfil === 'administrador' && !$mostrarInativos): ?>
                                            <a href="editar_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                                class="btn btn-sm btn-tabela-editar me-1" title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="apagar_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                                class="btn btn-sm btn-tabela-apagar" title="Desativar">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>
</div>
</div>

<script>
$(document).ready(function () {
    $('#tabela-equipamentos').DataTable({
        pageLength: 10,
        pagingType: "full_numbers",
        language: {
            emptyTable: "Sem dados disponíveis.",
            info: "A mostrar _START_ a _END_ de _TOTAL_ registos",
            infoEmpty: "A mostrar 0 registos",
            infoFiltered: "(filtrado de _MAX_ registos)",
            lengthMenu: "Mostrar _MENU_ registos",
            loadingRecords: "A carregar...",
            processing: "A processar...",
            search: "Pesquisar:",
            zeroRecords: "Nenhum registo encontrado.",
            paginate: {
                first: "Primeira",
                last: "Última",
                next: "Seguinte",
                previous: "Anterior"
            }
        }
    });
});
</script>
<?php include '../../includes/footer.php'; ?>