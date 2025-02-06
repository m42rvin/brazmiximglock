<?php

$json_file = 'uploads.json';

// Função para carregar as imagens do arquivo JSON
function loadImages($json_file) {
    if (!file_exists($json_file)) {
        return [];
    }

    $json_data = file_get_contents($json_file);
    $images = json_decode($json_data, true);

    // Verifica se o JSON foi decodificado corretamente
    if ($images === null && json_last_error() !== JSON_ERROR_NONE) {
        die("Erro ao decodificar o JSON: " . json_last_error_msg());
    }

    return $images;
}

// Função para excluir uma imagem
function deleteImage($json_file, $image_id) {
    $current_images = loadImages($json_file);
    $updated_images = [];
    $image_found = false;

    // Carrega o arquivo processos_auditoria.json
    $auditoria_file = 'processos_auditoria.json';
    if (!file_exists($auditoria_file)) {
        return "Erro: O arquivo de auditoria não foi encontrado.";
    }

    $auditoria_data = json_decode(file_get_contents($auditoria_file), true);
    if (!is_array($auditoria_data)) {
        return "Erro: Falha ao ler os dados do arquivo de auditoria.";
    }

    // Verifica se a imagem está em uso
    foreach ($auditoria_data as $processo) {
        if (!empty($processo['original_image']) && $processo['original_image'] == $image_id) {
            return "Erro: A imagem está em uso e não pode ser deletada.";
        }
    }

    // Loop para excluir a imagem se não estiver em uso
    foreach ($current_images as $img) {
        if ($img['id'] !== $image_id) {
            $updated_images[] = $img;
        } else {
            $image_found = true;
            if (!empty($img['path']) && file_exists($img['path'])) {
                unlink($img['path']);
            }
            if (!empty($img['thumb_path']) && file_exists($img['thumb_path'])) {
                unlink($img['thumb_path']);
            }
        }
    }

    // Atualiza o JSON de imagens
    file_put_contents($json_file, json_encode($updated_images, JSON_PRETTY_PRINT));

    return $image_found ? "Imagem deletada com sucesso." : "Erro: Imagem não encontrada.";
}

// Verifica se o POST contém dados
if (isset($_POST['delete'])) {
    $images_id = json_decode($_POST['delete'], true);

    // Verifica se a decodificação do JSON foi bem-sucedida
    if ($images_id === null && json_last_error() !== JSON_ERROR_NONE) {
        die("Erro ao decodificar IDs de imagens para exclusão.");
    }

    // Percorre os IDs e exclui as imagens correspondentes
    foreach ($images_id as $image_id) {
        deleteImage($json_file, $image_id);
    }

    // Redireciona após a exclusão
    header('Location: panel.php');
    exit();
}
