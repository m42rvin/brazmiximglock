<?php

// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}



// Verifica se o parâmetro 'pa_id' foi passado
if (!isset($_GET['pa_id'])) {
    die("Erro: ID do processo não fornecido.");
}

// Obtém o ID do processo a ser excluído
$pa_id = $_GET['pa_id'];

// Caminho do arquivo JSON
$arquivo_json = 'processos_auditoria.json';

// Verifica se o arquivo existe antes de tentar abrir
if (!file_exists($arquivo_json)) {
    die("Erro: Arquivo JSON não encontrado.");
}

// Lê o conteúdo do arquivo
$conteudo = file_get_contents($arquivo_json);
$processos = json_decode($conteudo, true);

// Verifica se a decodificação foi bem-sucedida
if ($processos === null) {
    die("Erro: Falha ao decodificar JSON.");
}

// Filtra o array removendo o item com o ID correspondente
$processos_filtrados = array_filter($processos, fn($processo) => $processo['id'] !== $pa_id);

// Se o número de processos não mudou, significa que o ID não foi encontrado
if (count($processos) === count($processos_filtrados)) {
    die("Erro: Processo não encontrado.");
}

// Reindexa o array para evitar problemas com índices
$processos_filtrados = array_values($processos_filtrados);

// Salva o novo JSON sem o item excluído
if (file_put_contents($arquivo_json, json_encode($processos_filtrados, JSON_PRETTY_PRINT)) === false) {
    die("Erro: Falha ao salvar o arquivo JSON.");
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
?>
