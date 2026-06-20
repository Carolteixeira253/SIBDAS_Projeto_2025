<?php
start_session();
$_nome_utilizador   = $_SESSION['nome']   ?? 'Utilizador';
$_perfil_utilizador = $_SESSION['perfil'] ?? 'tecnico';
?>

<!-- SIDEBAR -->
<nav class="sidebar-medcare p-4 d-flex flex-column justify-content-between">
    <div>
        <div class="sidebar-section-title">Navegação Principal</div>
        <div class="nav flex-column">
            <a href="/medcare-inventory-solutions/Private/index.php" class="nav-link active">
                <i class="fa-solid fa-table-columns me-3"></i>Dashboard
            </a>
        </div>

        <div class="sidebar-section-title">Gestão de Ativos</div>
        <div class="nav flex-column">
            <a href="/medcare-inventory-solutions/Private/views/equipamentos/equipamentos.php" class="nav-link">
                <i class="fa-solid fa-stethoscope me-3"></i>Equipamentos
            </a>
            <a href="/medcare-inventory-solutions/Private/views/fornecedores/fornecedores.php" class="nav-link">
                <i class="fa-solid fa-truck-field me-3"></i>Fornecedores
            </a>
            <a href="/medcare-inventory-solutions/Private/views/localizacoes/localizacoes.php" class="nav-link">
                <i class="fa-solid fa-location-dot me-3"></i>Localizações
            </a>
            <a href="/medcare-inventory-solutions/Private/views/documentacao/documentacao.php" class="nav-link">
                <i class="fa-solid fa-file-lines me-3"></i>Documentação
            </a>
            <a href="/medcare-inventory-solutions/Private/views/garantias/garantias.php" class="nav-link">
                <i class="fa-solid fa-file-invoice me-3"></i>Garantias
            </a>
        </div>

        <?php if (is_admin()): ?>
            <div class="sidebar-section-title">Configurações</div>
            <div class="nav flex-column">
                <a href="/medcare-inventory-solutions/Private/views/backoffice/conteudos.php" class="nav-link">
                    <i class="fa-solid fa-pen-to-square me-3"></i>Editar Site Público
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="sidebar-section-title">Sessão</div>
        <a href="/medcare-inventory-solutions/Public/logout.php" class="nav-link mb-3 text-danger">
            <i class="fa-solid fa-arrow-right-from-bracket me-3"></i>Terminar Sessão
        </a>
        <div class="sidebar-user-zone border-top pt-3 d-flex align-items-center justify-content-between">
            <div>
                <small><?= $_perfil_utilizador === 'administrador' ? 'Administrador' : 'Técnico' ?></small><br>
                <strong><?= htmlspecialchars($_nome_utilizador) ?></strong>
            </div>
            <a href="/medcare-inventory-solutions/Public/logout.php">
                <i class="fa-solid fa-power-off fs-5"></i>
            </a>
        </div>
    </div>
</nav>