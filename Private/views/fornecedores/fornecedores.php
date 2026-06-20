<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../../config/config.php';
redirect_if_not_logged();
$_perfil = $_SESSION['perfil'] ?? 'tecnico';

$erro = '';
$resultados = [];
$totalFabricante = $totalDistribuidor = $totalAssistencia = $totalGeral = 0;

try {
    $ligacao = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultados = $ligacao->query("SELECT * FROM Fornecedor WHERE ativo = 1 ORDER BY nomeFornecedor ASC")->fetchAll(PDO::FETCH_OBJ);
    $totalGeral       = $ligacao->query("SELECT COUNT(*) FROM Fornecedor WHERE ativo = 1")->fetchColumn();
    $totalFabricante  = $ligacao->query("SELECT COUNT(*) FROM Fornecedor WHERE ativo = 1 AND tipoFornecedor = 'fabricante'")->fetchColumn();
    $totalDistribuidor = $ligacao->query("SELECT COUNT(*) FROM Fornecedor WHERE ativo = 1 AND tipoFornecedor = 'distribuidor'")->fetchColumn();
    $totalAssistencia = $ligacao->query("SELECT COUNT(*) FROM Fornecedor WHERE ativo = 1 AND tipoFornecedor = 'assistencia_tecnica'")->fetchColumn();
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
            <h1 class="fw-bold h2 mb-1">Gestão de Fornecedores</h1>
            <p class="text-muted small mb-0">Registo e contactos de entidades fornecedoras de dispositivos médicos.</p>
        </div>
        <?php if ($_perfil === 'administrador'): ?>
            <a href="inserir_fornecedor.php" class="btn btn-acao-primaria fw-bold px-3 py-2">
                <i class="fa-solid fa-plus me-2"></i>Adicionar
            </a>
        <?php endif; ?>
    </div>

    <?php if ($erro === 'bd'): ?>
        <?= mensagem_erro_bd() ?>
    <?php else: ?>

        <!-- Contadores -->
        <div class="d-flex align-items-center gap-4 mb-3 px-1" style="font-size:0.875rem;">
            <span><strong style="color:#0f172a;"><?= $totalGeral ?></strong> <span class="text-muted">Total</span></span>
            <span class="text-muted">·</span>
            <span><strong style="color:#15803d;"><?= $totalFabricante ?></strong> <span class="text-muted">Fabricantes</span></span>
            <span class="text-muted">·</span>
            <span><strong style="color:#a16207;"><?= $totalDistribuidor ?></strong> <span class="text-muted">Distribuidores</span></span>
            <span class="text-muted">·</span>
            <span><strong style="color:#1d4ed8;"><?= $totalAssistencia ?></strong> <span class="text-muted">Assistência Técnica</span></span>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm rounded-3 mb-3 px-4 py-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="position-relative flex-grow-1" style="min-width:200px; max-width:320px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute"
                       style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                    <input type="text" id="filtro-texto" class="form-control ps-4"
                           placeholder="Pesquisar fornecedor..."
                           style="border-color:#d0e1fd; border-radius:0.5rem;">
                </div>
                <select id="filtro-tipo" class="form-select" style="width:auto; border-color:#d0e1fd; border-radius:0.5rem;">
                    <option value="">Tipo</option>
                    <option value="fabricante">Fabricante</option>
                    <option value="distribuidor">Distribuidor</option>
                    <option value="assistencia_tecnica">Assistência Técnica</option>
                    <option value="consumiveis">Consumíveis</option>
                </select>
                <button id="limpar-filtros" class="btn btn-outline-secondary btn-sm" style="border-radius:0.5rem;">
                    <i class="fa-solid fa-xmark me-1"></i>Limpar
                </button>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Fornecedor</th>
                            <th>Tipo</th>
                            <th>Contacto</th>
                            <th>Email</th>
                            <th>Pessoa de Contacto</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpo-tabela">
                        <?php if (count($resultados) == 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-inbox fs-3 d-block mb-2 opacity-25"></i>
                                    Não existem fornecedores registados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $f):
                                $badgeTipo = match($f->tipoFornecedor ?? '') {
                                    'fabricante'        => ['bg-success-subtle text-success border-success-subtle', 'Fabricante'],
                                    'distribuidor'      => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'Distribuidor'],
                                    'assistencia_tecnica' => ['bg-primary-subtle text-primary border-primary-subtle', 'Assistência Técnica'],
                                    'consumiveis'       => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'Consumíveis'],
                                    default             => ['bg-secondary-subtle text-secondary border-secondary-subtle', ucfirst($f->tipoFornecedor ?? 'N/D')],
                                };
                            ?>
                                <tr data-texto="<?= strtolower(htmlspecialchars($f->nomeFornecedor . ' ' . ($f->nif ?? '') . ' ' . ($f->pessoaContacto ?? ''))) ?>"
                                    data-tipo="<?= htmlspecialchars($f->tipoFornecedor ?? '') ?>">
                                    <td class="ps-4">
                                        <strong style="color:#0f172a;"><?= htmlspecialchars($f->nomeFornecedor) ?></strong>
                                        <?php if ($f->nif): ?>
                                            <br><small class="text-muted">NIF: <?= htmlspecialchars($f->nif) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge border <?= $badgeTipo[0] ?>">
                                            <?= $badgeTipo[1] ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.85rem; color:#64748b;"><?= htmlspecialchars($f->contactoTelefonico ?? 'N/D') ?></td>
                                    <td style="font-size:0.85rem; color:#64748b;"><?= htmlspecialchars($f->enderecoEmail ?? 'N/D') ?></td>
                                    <td style="font-size:0.85rem; color:#64748b;"><?= htmlspecialchars($f->pessoaContacto ?? 'N/D') ?></td>
                                    <td class="text-end pe-4">
                                        <a href="detalhes_fornecedor.php?id_fornecedor=<?= aes_encrypt($f->idFornecedor) ?>"
                                            class="btn btn-sm btn-tabela-ver me-1" title="Ver detalhes">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($_perfil === 'administrador'): ?>
                                            <a href="editar_fornecedor.php?id_fornecedor=<?= aes_encrypt($f->idFornecedor) ?>"
                                                class="btn btn-sm btn-tabela-editar me-1" title="Editar">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="apagar_fornecedor.php?id_fornecedor=<?= aes_encrypt($f->idFornecedor) ?>"
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
const rows      = document.querySelectorAll('#corpo-tabela tr[data-texto]');
const inTexto   = document.getElementById('filtro-texto');
const inTipo    = document.getElementById('filtro-tipo');
const btnLimp   = document.getElementById('limpar-filtros');
const contador  = document.getElementById('contador-resultados');
const paginacao = document.getElementById('paginacao');

const POR_PAGINA = 10;
let paginaAtual = 1;
let rowsFiltradas = [];

function filtrar() {
    const txt  = inTexto.value.toLowerCase().trim();
    const tipo = inTipo.value;
    rowsFiltradas = [];
    rows.forEach(r => {
        const ok = (!txt  || r.dataset.texto.includes(txt))
                && (!tipo || r.dataset.tipo === tipo);
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
    rows.forEach(r => r.style.display = 'none');
    rowsFiltradas.slice(inicio, inicio + POR_PAGINA).forEach(r => r.style.display = '');
    contador.textContent = total + ' fornecedor(es) encontrado(s) · Página ' + paginaAtual + ' de ' + totalPaginas;
    paginacao.innerHTML = '';
    if (totalPaginas <= 1) return;
    const btn = (txt, pg, disabled, active) => {
        const b = document.createElement('button');
        b.textContent = txt;
        b.className = 'btn btn-sm mx-1 ' + (active ? 'btn-acao-primaria' : 'btn-outline-secondary');
        b.disabled = disabled;
        b.onclick = () => { paginaAtual = pg; renderPagina(); window.scrollTo({top:0,behavior:'smooth'}); };
        return b;
    };
    paginacao.appendChild(btn('«', 1, paginaAtual===1, false));
    paginacao.appendChild(btn('‹', paginaAtual-1, paginaAtual===1, false));
    for (let i = 1; i <= totalPaginas; i++) {
        if (i===1 || i===totalPaginas || Math.abs(i-paginaAtual)<=1) {
            paginacao.appendChild(btn(i, i, false, i===paginaAtual));
        } else if (Math.abs(i-paginaAtual)===2) {
            const s = document.createElement('span');
            s.textContent = '...'; s.className = 'mx-1 text-muted align-self-center';
            paginacao.appendChild(s);
        }
    }
    paginacao.appendChild(btn('›', paginaAtual+1, paginaAtual===totalPaginas, false));
    paginacao.appendChild(btn('»', totalPaginas, paginaAtual===totalPaginas, false));
}

inTexto.addEventListener('input', filtrar);
inTipo.addEventListener('change', filtrar);
btnLimp.addEventListener('click', () => { inTexto.value = inTipo.value = ''; filtrar(); });
filtrar();
</script>

<?php include '../../includes/footer.php'; ?>