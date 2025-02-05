<?php
// Carregar o conteúdo atual do arquivo JSON
$jsonFile = 'processos_auditoria.json';
$data = json_decode(file_get_contents($jsonFile), true);

// Recuperar os dados do formulário (valores dos checkboxes e id do processo)
$processoId = $_POST['id_processo'];
$notificacao = isset($_POST['sinalizar_notificacao']) ? true : false;
$proximaEtapa = isset($_POST['seguir_proxima_etapa']) ? true : false;
$finalizarArquivar = isset($_POST['finalizar_arquivar']) ? true : false;

// Atualizar o item correto no array
foreach ($data as &$processo) {
    if ($processo['id'] == $processoId) { // Usar comparação "==" para evitar problemas de tipo
        $processo['sinalizar_notificacao'] = $notificacao;

        // Verificar se a data ainda não foi definida antes de salvar
        if ($notificacao && (!isset($processo['sinalizar_envio_data']) || empty($processo['sinalizar_envio_data']))) {
            $processo['sinalizar_envio_data'] = date('Y-m-d H:i:s');
        }

        $processo['seguir_proxima_etapa'] = $proximaEtapa;
        if($proximaEtapa) {
            $processo['etapa'] = '3';
        }
        if($finalizarArquivar) {
            $processo['etapa'] = '4';
        }
        $processo['finalizar_arquivar'] = $finalizarArquivar;
        break;
    }
}


// Salvar o conteúdo atualizado de volta no JSON
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));


// Redirecionar de volta para a página anterior
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
