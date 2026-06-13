<?php
// Inicia a sessão para aceder e manipular os dados da $_SESSION
session_start();
// TERMINAR A SESSÃO
// Remove todas as variáveis da sessão
session_unset();

// Destroi completamente a sessão no servidor
session_destroy();

// REDIRECIONAMENTO PARA O LOGIN
header('Location: /medcare-inventory-solutions/Public/login.php');
return;
?>