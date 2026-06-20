<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$mostrarInativos = isset($_GET['inativos']) && $_GET['inativos'] == '1';
$filtroAtivo = $mostrarInativos ? 0 : 1;

$erro = '';
$resultados = [];
$totalOperacional = $totalManutencao = $totalAvariado = $totalGeral = 0;

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
    $totalGeral       = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1")->fetchColumn();
    $totalOperacional = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'operacional'")->fetchColumn();
    $totalManutencao  = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'manutencao'")->fetchColumn();
    $totalAvariado    = $ligacao->query("SELECT COUNT(*) FROM Equipamento WHERE ativo = 1 AND estado = 'avariado'")->fetchColumn();
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
                    <a href="exportar_csv.php" class="btn btn-outline-secondary px-3 py-2">
                        <i class="fa-solid fa-file-csv me-2"></i>Exportar CSV
                    </a>
                </div>
            </div>

            <?php if ($erro === 'bd'): ?>
                <?= mensagem_erro_bd() ?>
            <?php else: ?>

                <?php if (!$mostrarInativos): ?>
                    <div class="d-flex align-items-center gap-4 mb-3 px-1" style="font-size:0.875rem;">
                        <span><strong style="color:#0f172a;"><?= $totalGeral ?></strong> <span class="text-muted">Total</span></span>
                        <span class="text-muted">·</span>
                        <span><strong style="color:#15803d;"><?= $totalOperacional ?></strong> <span class="text-muted">Operacionais</span></span>
                        <span class="text-muted">·</span>
                        <span><strong style="color:#a16207;"><?= $totalManutencao ?></strong> <span class="text-muted">Em Manutenção</span></span>
                        <span class="text-muted">·</span>
                        <span><strong style="color:#b91c1c;"><?= $totalAvariado ?></strong> <span class="text-muted">Avariados</span></span>
                    </div>
                <?php endif; ?>

                <!-- Barra de filtros -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 px-4 py-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="position-relative flex-grow-1" style="min-width:200px; max-width:300px;">
                            <i class="fa-solid fa-magnifying-glass position-absolute"
                                style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                            <input type="text" id="filtro-texto" class="form-control ps-4"
                                placeholder="Pesquisar equipamento..."
                                style="border-color:#d0e1fd; border-radius:0.5rem;">
                        </div>
                        <select id="filtro-estado" class="form-select" style="width:auto; border-color:#d0e1fd; border-radius:0.5rem;">
                            <option value="">Estado</option>
                            <option value="operacional">Operacional</option>
                            <option value="manutencao">Em Manutenção</option>
                            <option value="avariado">Avariado</option>
                            <option value="inativo">Inativo</option>
                        </select>
                        <select id="filtro-criticidade" class="form-select" style="width:auto; border-color:#d0e1fd; border-radius:0.5rem;">
                            <option value="">Criticidade</option>
                            <option value="Suporte de vida">Suporte de vida</option>
                            <option value="Alta">Alta</option>
                            <option value="Media">Média</option>
                            <option value="Baixa">Baixa</option>
                        </select>
                        <select id="filtro-categoria" class="form-select" style="width:auto; border-color:#d0e1fd; border-radius:0.5rem;">
                            <option value="">Categoria</option>
                            <?php
                            $cats = array_unique(array_filter(array_map(fn($e) => $e->categoria, $resultados)));
                            sort($cats);
                            foreach ($cats as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button id="limpar-filtros" class="btn btn-outline-secondary btn-sm" style="border-radius:0.5rem;">
                            <i class="fa-solid fa-xmark me-1"></i>Limpar
                        </button>
                        <div class="ms-auto d-flex gap-2">
                            <a href="equipamentos.php" class="btn btn-sm <?= !$mostrarInativos ? 'btn-acao-primaria' : 'btn-outline-secondary' ?>">
                                <i class="fa-solid fa-circle-check me-1"></i>Ativos
                            </a>
                            <a href="equipamentos.php?inativos=1" class="btn btn-sm <?= $mostrarInativos ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                                <i class="fa-solid fa-ban me-1"></i>Inativos
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabela -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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
                            <tbody id="corpo-tabela">
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
                                        <tr data-estado="<?= htmlspecialchars($eq->estado) ?>"
                                            data-criticidade="<?= htmlspecialchars($eq->criticidadeClinica ?? '') ?>"
                                            data-categoria="<?= htmlspecialchars($eq->categoria ?? '') ?>"
                                            data-texto="<?= strtolower(htmlspecialchars($eq->nomeEquipamento . ' ' . ($eq->marca ?? '') . ' ' . ($eq->modelo ?? '') . ' ' . $codigo)) ?>">
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
                                            <td><span class="badge <?= $badgeEstado ?>"><?= htmlspecialchars($eq->estado) ?></span></td>
                                            <td style="font-size:0.85rem; color:#64748b;"><?= $localizacao ?></td>
                                            <td><span class="badge <?= $badgeCrit ?>"><?= htmlspecialchars($eq->criticidadeClinica ?? 'N/D') ?></span></td>
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
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top"
                        style="background:#f8fafc; font-size:0.82rem; color:#64748b;">
                        <span id="contador-resultados"></span>
                        <div id="paginacao" class="d-flex align-items-center"></div>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>
</div>

<script>
    const rows = document.querySelectorAll('#corpo-tabela tr[data-estado]');
    const inTexto = document.getElementById('filtro-texto');
    const inEstado = document.getElementById('filtro-estado');
    const inCrit = document.getElementById('filtro-criticidade');
    const inCat = document.getElementById('filtro-categoria');
    const btnLimp = document.getElementById('limpar-filtros');
    const contador = document.getElementById('contador-resultados');

    const POR_PAGINA = 10;
    let paginaAtual = 1;
    let rowsFiltradas = [];

    function filtrar() {
        const txt = inTexto.value.toLowerCase().trim();
        const estado = inEstado.value;
        const crit = inCrit.value;
        const cat = inCat.value;

        rowsFiltradas = [];
        rows.forEach(r => {
            const ok = (!txt || r.dataset.texto.includes(txt)) &&
                (!estado || r.dataset.estado === estado) &&
                (!crit || r.dataset.criticidade === crit) &&
                (!cat || r.dataset.categoria === cat);
            r.style.display = 'none';
            if (ok) rowsFiltradas.push(r);
        });

        paginaAtual = 1;
        renderPagina();
    }

    function renderPagina() {
        const total = rowsFiltradas.length;
        const totalPaginas = Math.ceil(total / POR_PAGINA) || 1;
        const inicio = (paginaAtual - 1) * POR_PAGINA;
        const fim = inicio + POR_PAGINA;

        rows.forEach(r => r.style.display = 'none');
        rowsFiltradas.slice(inicio, fim).forEach(r => r.style.display = '');

        contador.textContent = total + ' equipamento(s) encontrado(s)';

        // Paginação
        const pag = document.getElementById('paginacao');
        pag.innerHTML = '';

        if (totalPaginas <= 1) return;

        const btn = (txt, pg, disabled, active) => {
            const b = document.createElement('button');
            b.textContent = txt;
            b.className = 'btn btn-sm ' + (active ? 'btn-acao-primaria' : 'btn-outline-secondary') + ' mx-1';
            b.disabled = disabled;
            b.onclick = () => {
                paginaAtual = pg;
                renderPagina();
            };
            return b;
        };

        pag.appendChild(btn('«', 1, paginaAtual === 1, false));
        pag.appendChild(btn('‹', paginaAtual - 1, paginaAtual === 1, false));

        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || Math.abs(i - paginaAtual) <= 1) {
                pag.appendChild(btn(i, i, false, i === paginaAtual));
            } else if (Math.abs(i - paginaAtual) === 2) {
                const span = document.createElement('span');
                span.textContent = '...';
                span.className = 'mx-1 text-muted';
                pag.appendChild(span);
            }
        }

        pag.appendChild(btn('›', paginaAtual + 1, paginaAtual === totalPaginas, false));
        pag.appendChild(btn('»', totalPaginas, paginaAtual === totalPaginas, false));
    }

    inTexto.addEventListener('input', filtrar);
    inEstado.addEventListener('change', filtrar);
    inCrit.addEventListener('change', filtrar);
    inCat.addEventListener('change', filtrar);
    btnLimp.addEventListener('click', () => {
        inTexto.value = inEstado.value = inCrit.value = inCat.value = '';
        filtrar();
    });

    filtrar(); // inicializar
</script>
<?php include '../../includes/footer.php'; ?>