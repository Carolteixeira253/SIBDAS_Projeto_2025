<?php
// Verifica se a sessão ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['utilizador'])) {
    // Se não estiver autenticado, redireciona para o formulário de login
    header('Location: /medcare-inventory-solutions/Public/login.php');
    exit;
}

// A partir daqui, o utilizador está autenticado
$nome = $_SESSION['utilizador'];
?>

<!-- HEADER -->
    <header class="navbar-medcare d-flex align-items-center justify-content-between shadow-sm">
        <a href="/medcare-inventory-solutions/Private/index.php" class="brand-header">
            <img src="/medcare-inventory-solutions/Private/assets/img/logo.png" alt="Logo MedCare" height="32" class="me-2">
            <span><?php echo APP_NAME; ?> <span class="navbar-text-secundario fw-light fs-6 ms-2">| Hospital Backoffice</span></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark border">
                <i class="fa-solid fa-server text-success me-1"></i>Produção
            </span>
            <div class="position-relative" style="cursor:pointer;" data-bs-toggle="tooltip" title="2 alertas de garantia">
                <i class="fa-solid fa-bell text-white fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">2</span>
            </div>
        </div>
    </header>