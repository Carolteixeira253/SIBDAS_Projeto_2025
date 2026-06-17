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
        DB_USER,
        DB_PASS
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
<?php include '../../includes/nav.php'; ?>

<div class="content-body">

    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold h2 mb-1 text-dark">Inventário de Equipamentos</h1>
                <p class="text-muted small mb-0">Gestão e monitorização de dispositivos médicos.</p>
            </div>
            <?php if ($_perfil === 'administrador'): ?>
                <a href="inserir_equipamento.php" class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm">
                    <i class="fa-solid fa-plus me-2"></i>Adicionar Equipamento
                </a>
            <?php endif; ?>
        </div>
        <!-- Filtros Ativo / Inativo -->
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="equipamentos.php"
                class="btn <?= !$mostrarInativos ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fa-solid fa-circle-check me-1"></i> Ativos
            </a>
            <a href="equipamentos.php?inativos=1"
                class="btn <?= $mostrarInativos ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                <i class="fa-solid fa-ban me-1"></i> Eliminados / Inativos
            </a>
        </div>
        <?php if ($erro === 'bd'): ?>
            <?= mensagem_erro_bd() ?>
        <?php else: ?>
            <div class="card-stat border-0 shadow-sm rounded-3 overflow-hidden">
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
                        <tbody class="table-group-divider">
                            <?php if (count($resultados) == 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <?= $mostrarInativos
                                            ? 'Não existem equipamentos eliminados.'
                                            : 'Não existem equipamentos registados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($resultados as $eq): ?>
                                    <?php
                                    $badgeEstado = match ($eq->estado) {
                                        'operacional' => 'bg-success-subtle text-success border-success-subtle',
                                        'manutencao'  => 'bg-warning-subtle text-warning border-warning-subtle',
                                        'avariado'    => 'bg-danger-subtle text-danger border-danger-subtle',
                                        'inativo'     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        default       => 'bg-secondary-subtle text-secondary'
                                    };
                                    $badgeCrit = match ($eq->criticidadeClinica ?? '') {
                                        'Suporte de vida' => 'bg-danger text-white',
                                        'Alta'   => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'Media'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        'Baixa'  => 'bg-light text-muted border',
                                        default  => 'bg-light text-muted border'
                                    };
                                    $localizacao = $eq->nomeSala
                                        ? htmlspecialchars($eq->nomeSala . ($eq->servico ? ' — ' . $eq->servico : ''))
                                        : 'N/D';
                                    $codigo = $eq->codigoInventario
                                        ?? '#' . str_pad($eq->idEquipamento, 3, '0', STR_PAD_LEFT);
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted" style="font-size:0.82rem;">
                                            <?= htmlspecialchars($codigo) ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($eq->nomeEquipamento) ?></strong>
                                            <?php if ($eq->marca): ?>
                                                <br><small class="text-muted">
                                                    <?= htmlspecialchars($eq->marca) ?>
                                                    <?= $eq->modelo ? ' · ' . htmlspecialchars($eq->modelo) : '' ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($eq->categoria ?? 'N/D') ?></td>
                                        <td>
                                            <span class="badge <?= $badgeEstado ?> border px-2">
                                                <?= htmlspecialchars($eq->estado) ?>
                                            </span>
                                        </td>
                                        <td style="font-size:0.85rem;"><?= $localizacao ?></td>
                                        <td>
                                            <span class="badge <?= $badgeCrit ?> px-2">
                                                <?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="detalhes_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                                class="btn btn-sm btn-outline-secondary me-1" title="Ver detalhes">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <?php if ($_perfil === 'administrador' && !$mostrarInativos): ?>
                                                <a href="editar_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                                    class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="apagar_equipamento.php?id_equipamento=<?= aes_encrypt($eq->idEquipamento) ?>"
                                                    class="btn btn-sm btn-outline-danger" title="Desativar">
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
<script>
    $(document).ready(function() {
        $('#tabela-equipamentos').DataTable({
            pageLength: 10,
            pagingType: "full_numbers",
            language: {
                emptyTable: "Sem dados disponíveis na tabela.",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registos",
                infoEmpty: "Mostrando 0 até 0 de 0 registos",
                infoFiltered: "(Filtrando _MAX_ total de registos)",
                lengthMenu: "Mostrar _MENU_ registos por página.",
                loadingRecords: "A carregar...",
                processing: "A processar...",
                search: "Filtrar:",
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