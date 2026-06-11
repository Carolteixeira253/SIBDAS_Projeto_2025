<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - MedCare Inventory Solutions</title>

    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/1231343.css">
</head>

<body>

    <header>
        <nav class="bng-navbar">
            <div class="logo-area">
                <img src="assets/img/logo.png" alt="MedCare Logo">
                <h3>MedCare</h3>
            </div>

            <div class="container-navegacao">
                <a href="index.php">Início</a>
                <a href="quem_somos.php">Quem Somos</a>
                <a href="equipamentos.php">Equipamentos</a>
                <a href="servicos.php">Serviços</a>
                <a href="contacto.php">Contacto</a>
            </div>

            <div class="nav-cliente">
                <a href="login.php"><i class="fas fa-user"></i> Entrar</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="lg-seccao">
            
            <div class="lg-card">
                
                <div class="lg-header">
                    <div class="lg-icon-circle">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>Portal Clínico</h2>
                    <p>Introduza as suas credenciais para aceder ao inventário da unidade de saúde.</p>
                </div>

                <form action="../private/index.php" method="GET" class="lg-form">
                    
                    <div class="lg-group">
                        <label for="username">Utilizador ou E-mail</label>
                        <div class="lg-input-wrapper">
                            <i class="fas fa-user lg-field-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Ex: engenharia@medcare.pt" required>
                        </div>
                    </div>

                    <div class="lg-group">
                        <label for="password">Palavra-passe</label>
                        <div class="lg-input-wrapper">
                            <i class="fas fa-key lg-field-icon"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="lg-opcoes">
                        <label class="lg-lembrar">
                            <input type="checkbox" name="remember"> Lembrar-me
                        </label>
                        <a href="#" class="lg-esqueceu">Esqueceu-se da senha?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Aceder ao Sistema <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

            </div>

        </section>
    </main>

    <footer class="footer-container">
        <div class="footer-section">
            <p>&copy; 2026 MedCare Inventory Solutions. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>