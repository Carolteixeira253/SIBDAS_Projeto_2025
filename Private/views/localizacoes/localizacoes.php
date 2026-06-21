<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

// LIGAÇÃO À BASE DE DADOS
try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Localizacao WHERE ativo = 1")->fetchAll(PDO::FETCH_OBJ);
    $erro = '';
} catch (PDOException $err) {
    $erro = 'bd';
    $resultados = [];
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
                    <h1 class="fw-bold h2 mb-1 text-dark">Gestão de Localizações</h1>
                    <p class="text-muted small mb-0">Controlo de edifícios, pisos, salas e serviços hospitalares.</p>
                </div>
                <?php if ($_perfil === 'administrador'): ?>
                    <a href="inserir_localizacao.php" class="btn btn-acao-primaria fw-bold px-3 py-2 shadow-sm">
                        <i class="fa-solid fa-plus me-2"></i>Adicionar Localização
                    </a>
                <?php endif; ?>
            </div>

            <!-- Barra de pesquisa -->
            <div class="card border-0 shadow-sm rounded-3 mb-3 px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="position-relative flex-grow-1" style="max-width:320px;">
                        <i class="fa-solid fa-magnifying-glass position-absolute"
                            style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                        <input type="text" id="filtro-texto" class="form-control ps-4"
                            placeholder="Pesquisar sala ou serviço..."
                            style="border-color:#d0e1fd; border-radius:0.5rem;">
                    </div>
                    <button id="limpar-filtros" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('filtro-texto').value=''; filtrar();">
                        <i class="fa-solid fa-xmark me-1"></i>Limpar
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table id="tabela-localizacoes" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Sala</th>
                                <th>Edifício</th>
                                <th>Serviço</th>
                                <th>Piso</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="corpo-tabela">
                            <?php if (!empty($erro)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-danger"><?= $erro ?></td>
                                </tr>
                            <?php elseif (count($resultados) == 0) : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Não existem localizações registadas.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($resultados as $localizacao) : ?>
                                    <tr data-texto="<?= strtolower(htmlspecialchars($localizacao->nomeSala . ' ' . ($localizacao->edificio ?? '') . ' ' . ($localizacao->servico ?? ''))) ?>">
                                        <td><strong><?= htmlspecialchars($localizacao->nomeSala) ?></strong></td>
                                        <td><?= htmlspecialchars($localizacao->edificio ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($localizacao->servico ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($localizacao->piso) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="detalhes_localizacao.php?id_localizacao=<?= aes_encrypt($localizacao->idLocalizacao) ?>" class="btn btn-sm btn-tabela-ver me-1">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="editar_localizacao.php?id_localizacao=<?= aes_encrypt($localizacao->idLocalizacao) ?>" class="btn btn-sm btn-tabela-editar me-1">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="apagar_localizacao.php?id_localizacao=<?= aes_encrypt($localizacao->idLocalizacao) ?>" class="btn btn-sm btn-tabela-apagar">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
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
        </main>
    </div>
</div>

<script>
    const rows = document.querySelectorAll('#corpo-tabela tr');
    const inTexto = document.getElementById('filtro-texto');
    const contador = document.getElementById('contador-resultados');
    const paginacao = document.getElementById('paginacao');

    const POR_PAGINA = 10;
    let paginaAtual = 1;
    let rowsFiltradas = [];

    function filtrar() {
        const txt = inTexto.value.toLowerCase().trim();
        rowsFiltradas = [];
        rows.forEach(r => {
            const ok = !txt || r.dataset.texto?.includes(txt);
            r.style.display = 'none';
            if (ok) rowsFiltradas.push(r);
        });
        paginaAtual = 1;
        renderPagina();
    }

    function renderPagina() {
        const total = rowsFiltradas.length;
        const totalPaginas = Math.ceil(total / POR_PAGINA) || 1;
        rows.forEach(r => r.style.display = 'none');
        rowsFiltradas.slice((paginaAtual - 1) * POR_PAGINA, paginaAtual * POR_PAGINA).forEach(r => r.style.display = '');
        contador.textContent = total + ' localização(ões) encontrada(s)';
        paginacao.innerHTML = '';
        if (totalPaginas <= 1) return;
        const btn = (txt, pg, disabled, active) => {
            const b = document.createElement('button');
            b.textContent = txt;
            b.className = 'btn btn-sm mx-1 ' + (active ? 'btn-acao-primaria' : 'btn-outline-secondary');
            b.disabled = disabled;
            b.onclick = () => {
                paginaAtual = pg;
                renderPagina();
            };
            return b;
        };
        paginacao.appendChild(btn('«', 1, paginaAtual === 1, false));
        paginacao.appendChild(btn('‹', paginaAtual - 1, paginaAtual === 1, false));
        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || Math.abs(i - paginaAtual) <= 1)
                paginacao.appendChild(btn(i, i, false, i === paginaAtual));
            else if (Math.abs(i - paginaAtual) === 2) {
                const s = document.createElement('span');
                s.textContent = '...';
                s.className = 'mx-1 text-muted';
                paginacao.appendChild(s);
            }
        }
        paginacao.appendChild(btn('›', paginaAtual + 1, paginaAtual === totalPaginas, false));
        paginacao.appendChild(btn('»', totalPaginas, paginaAtual === totalPaginas, false));
    }

    inTexto.addEventListener('input', filtrar);
    filtrar();
</script>

<?php include '../../includes/footer.php'; ?>