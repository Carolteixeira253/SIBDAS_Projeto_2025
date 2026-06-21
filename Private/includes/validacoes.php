<?php
/**
 * Valida um nome genérico (ex: nome de utilizador, fornecedor).
 */
function validar_nome(string $nome): array {
    $erros = [];
    if (empty(trim($nome))) {
        $erros[] = "O campo Nome é obrigatório.";
    } elseif (preg_match('/\d/', $nome)) {
        $erros[] = "O campo Nome não pode conter números.";
    }
    return $erros;
}
/**
 * Valida o nome de um equipamento.
 * Pode conter letras, números e símbolos (ex: "Ventilador PB980-V2").
 */
function validar_nome_equipamento(string $nome): array
{
    $erros = [];
    if (empty(trim($nome))) {
        $erros[] = "O campo Designação é obrigatório.";
    } elseif (mb_strlen(trim($nome)) < 3) {
        $erros[] = "O nome do equipamento deve ter pelo menos 3 caracteres.";
    }
    return $erros;
}
/**
 * Valida um email.
 */
function validar_email(string $email): array
{
    $erros = [];
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email introduzido não é válido.";
    }
    return $erros;
}
/**
 * Valida uma data no formato YYYY-MM-DD (vinda de input date HTML).
 */
function validar_data(string $data, string $campo = 'Data'): array
{
    $erros = [];
    if (!empty($data)) {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        if (!$d || $d->format('Y-m-d') !== $data) {
            $erros[] = "$campo inválida.";
        }
    }
    return $erros;
}
function validar_ano_fabrico($ano) {
    $erros = [];
    if (!empty($ano)) {
        if (!is_numeric($ano) || $ano < 1900 || $ano > date('Y')) {
            $erros[] = "O Ano de Fabrico deve estar entre 1900 e " . date('Y') . ".";
        }
    }
    return $erros;
}

function validar_custo($custo) {
    $erros = [];
    if (!empty($custo)) {
        if (!is_numeric($custo) || $custo < 0) {
            $erros[] = "O Custo de Aquisição não pode ser negativo.";
        }
    }
    return $erros;
}
?>