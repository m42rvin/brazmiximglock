<?php
// Arquivos JSON
$respostasFile = 'resposta_processo.json';
$processosFile = 'processos_auditoria.json';

// Carregar dados existentes
$respostasData = file_exists($respostasFile) ? json_decode(file_get_contents($respostasFile), true) : [];
$processosData = file_exists($processosFile) ? json_decode(file_get_contents($processosFile), true) : [];

// Recuperar dados do formulário
$processoId = $_POST['id_processo'] ?? null;
$contestacao = $_POST['contestacao'] ?? null;
$textoResposta = $_POST['texto_resposta'] ?? null;

// Validar dados
if ($processoId && $contestacao && $textoResposta) {
    // Adicionar a resposta ao arquivo resposta_processo.json
    $novaResposta = [
        'id_processo' => $processoId,
        'contestacao' => $contestacao,
        'texto_resposta' => $textoResposta,
        'data_resposta' => date('Y-m-d H:i:s')
    ];

    $respostasData[] = $novaResposta;

    // Salvar as respostas atualizadas
    file_put_contents($respostasFile, json_encode($respostasData, JSON_PRETTY_PRINT));

    // Atualizar o campo resposta_processo no processos_auditoria.json
    foreach ($processosData as &$processo) {
        if ($processo['id'] === $processoId) {
            $processo['resposta_processo'] = true;
            break;
        }
    }

    // Salvar os processos atualizados
    file_put_contents($processosFile, json_encode($processosData, JSON_PRETTY_PRINT));

    // Redirecionar para a página anterior
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit();
} else {
    echo "Dados incompletos! Verifique o formulário.";
}
?>
