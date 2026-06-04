<?php
// private/conexao.php

$host = "localhost";
$dbname = "medinventory_solutions_bd";
$username = "root"; // Padrão do XAMPP
$password = "";     // Padrão do XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Ativar o modo de erros para ajudar no desenvolvimento
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao ligar à base de dados: " . $e->getMessage());
}
?>