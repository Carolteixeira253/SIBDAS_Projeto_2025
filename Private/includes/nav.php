<?php
// Verifica se a sessão ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['utilizador'])) {
    // Se não estiver autenticado, redireciona para o formulário de login
    header('Location: /sibdas/1231343/medcare-inventory-solutions/Public/login.php');
    exit;
}

// A partir daqui, o utilizador está autenticado
$nome = $_SESSION['utilizador'];
?>

<!-- HEADER -->
<header class="navbar-medcare d-flex align-items-center justify-content-between shadow-sm">
    <a href="/sibdas/1231343/medcare-inventory-solutions/Private/index.php" class="brand-header">
        <img src="/sibdas/1231343/medcare-inventory-solutions/Private/assets/img/logo.png" alt="Logo MedCare" height="32" class="me-2">
        <span><?php echo APP_NAME; ?> <span class="navbar-text-secundario fw-light fs-6 ms-2">| Hospital Backoffice</span></span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-link text-white d-lg-none p-0" id="btn-sidebar-toggle">
            <i class="fa-solid fa-bars fs-5"></i>
        </button>
        <span class="badge bg-light text-dark border">
            <i class="fa-solid fa-server text-success me-1"></i>Produção
        </span>
        <div class="position-relative" style="cursor:pointer;" data-bs-toggle="tooltip" title="2 alertas de garantia">
            <i class="fa-solid fa-bell text-white fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">2</span>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-sidebar-toggle');
    if (btn) {
        btn.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar-medcare');
            if (!sidebar) return;
            sidebar.style.display = sidebar.style.display === 'flex' ? 'none' : 'flex';
        });
    }
});
</script>