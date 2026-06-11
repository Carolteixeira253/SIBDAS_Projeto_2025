<!-- HEADER -->
    <header class="navbar-medcare d-flex align-items-center justify-content-between shadow-sm">
        <a href="index.php" class="brand-header">
            <i class="fa-solid fa-heart-pulse me-2"></i>
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