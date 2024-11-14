<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$json_file = 'uploads.json';
$zip_file = 'downloads.zip';

// Função para carregar as informações das imagens
function loadImages($json_file) {
    if (!file_exists($json_file)) {
        return [];
    }

    $json_data = file_get_contents($json_file);
    $images = json_decode($json_data, true);

    if ($images === null && json_last_error() !== JSON_ERROR_NONE) {
        die("Erro ao decodificar JSON: " . json_last_error_msg());
    }

    return $images;
}

// Verifica se o POST contém dados
if (isset($_POST['download'])) {
    $selected_ids = json_decode($_POST['download'], true);

    // Verifica se a decodificação do JSON foi bem-sucedida
    if ($selected_ids === null || !is_array($selected_ids)) {
        die("Erro ao decodificar IDs de imagens para download.");
    }

    // Carrega as informações das imagens do arquivo JSON
    $images = loadImages($json_file);

    // Verifica se foi possível carregar as imagens
    if (empty($images)) {
        die("Nenhuma imagem encontrada no arquivo JSON.");
    }

    // Cria um novo arquivo ZIP
    $zip = new ZipArchive();
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die("Não foi possível criar o arquivo ZIP.");
    }

    // Variável para rastrear se arquivos foram adicionados ao ZIP
    $files_added = 0;

    // Adiciona cada imagem selecionada ao ZIP
    foreach ($images as $image) {
        if (in_array($image['id'], $selected_ids)) {
            $file_path = $image['path'];
            $file_name = $image['name'];

            // Verifica se o arquivo existe antes de adicioná-lo
            if (file_exists($file_path)) {
                $zip->addFile($file_path, $file_name);
                $files_added++;
            }
        }
    }

    // Fecha o arquivo ZIP após adicionar todos os arquivos
    $zip->close();

    // Verifica se algum arquivo foi adicionado ao ZIP
    if ($files_added === 0) {
        unlink($zip_file); // Remove o ZIP vazio
        die("Nenhum arquivo válido encontrado para download.");
    }

    // Envia o arquivo ZIP para o navegador para download
    if (file_exists($zip_file)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="downloads.zip"');
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);

        // Remove o arquivo ZIP temporário do servidor após o download
        unlink($zip_file);
        exit();
    } else {
        die("Erro: Arquivo ZIP não encontrado para download.");
    }
} else {
    echo "Nenhuma imagem foi selecionada para download.";
}
