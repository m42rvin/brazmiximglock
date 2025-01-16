<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Arquivo JSON onde os processos são armazenados
$json_file = 'processos_auditoria.json';
$upload_dir = '_uploads/'; // Diretório para salvar os uploads

// Certifique-se de que o diretório de uploads existe
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ler o conteúdo atual do arquivo JSON
$processos = json_decode(file_get_contents($json_file), true);

// Obter os dados do formulário
$processo_id = $_POST['processo_id'] ?? '';
$etapa = $_POST['etapa'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$arquivo = $_FILES['arquivo'] ?? null;

if ($processo_id && $etapa && $descricao) {
    // Localizar o processo no arquivo JSON
    foreach ($processos as &$processo) {
        if ($processo['id'] === $processo_id) {
            // Salvar a descrição de acordo com a etapa
            $processo["descricao_{$etapa}"] = $descricao;

            // Processar o upload do arquivo
            if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
                $new_filename = $etapa . '_' . $processo_id . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($arquivo['tmp_name'], $destination)) {
                    $processo["arquivo_{$etapa}"] = $destination; // Salvar o caminho no JSON
                } else {
                    // Log ou mensagem de erro para falha no upload
                    error_log("Falha ao mover o arquivo enviado para {$destination}");
                }
            }

            $processo['last_update'] = date('Y-m-d H:i:s');
            break;
        }
    }

    // Salvar os dados atualizados no arquivo JSON
    file_put_contents($json_file, json_encode($processos, JSON_PRETTY_PRINT));

    // Redirecionar com mensagem de sucesso
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    // Redirecionar com mensagem de erro
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=1');
    exit;
}
?>
