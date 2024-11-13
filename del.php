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
    $image_found = false; // Para rastrear se a imagem foi encontrada e excluída

    foreach ($current_images as $img) {
        if ($img['id'] !== $image_id) {
            $updated_images[] = $img;
        } else {
            $image_found = true;
            // Remove o arquivo da pasta _img/ e da pasta _thumb/
            if (isset($img['path']) && file_exists($img['path'])) {
                unlink($img['path']);
            }
            if (isset($img['thumb_path']) && file_exists($img['thumb_path'])) {
                unlink($img['thumb_path']);
            }
        }
    }

    // Atualiza o arquivo JSON apenas se a imagem foi encontrada
    if ($image_found) {
        if (file_put_contents($json_file, json_encode($updated_images, JSON_PRETTY_PRINT), LOCK_EX) === false) {
            die("Erro ao salvar o JSON atualizado.");
        }
    }
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
