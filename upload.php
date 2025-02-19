<?php
try {

// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

// Caminho para a pasta onde as imagens serão salvas
$upload_dir = '_img/';
// Caminho para a pasta de miniaturas
$thumb_dir = '_thumb/';
// Arquivo JSON para armazenar os detalhes das imagens
$json_file = 'uploads.json';

// Cria a pasta _img/ e _thumb/ se não existirem
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_dir($thumb_dir)) {
    mkdir($thumb_dir, 0777, true);
}

// Função para carregar as imagens já enviadas do arquivo JSON
function loadImages($json_file) {
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $images = json_decode($json_data, true);

        // Verifica se a decodificação do JSON foi bem-sucedida
        if (json_last_error() === JSON_ERROR_NONE) {
            return $images;
        } else {
            return []; // Retorna um array vazio se o JSON estiver corrompido
        }
    }
    return [];
}

// Função para salvar os detalhes da imagem no arquivo JSON com bloqueio de arquivo
function saveImageDetails($json_file, $image_details) {
    // Carregar as imagens existentes
    $current_images = loadImages($json_file);

    // Adiciona os novos detalhes da imagem ao array existente
    $current_images[] = $image_details;

    // Escreve o JSON atualizado no arquivo com LOCK_EX para evitar acesso concorrente
    $json_data = json_encode($current_images, JSON_PRETTY_PRINT);
    
    if ($json_data !== false) { // Verifica se a conversão do JSON foi bem-sucedida
        file_put_contents($json_file, $json_data, LOCK_EX);
    } else {
        echo "Erro ao codificar dados JSON.";
    }
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
            if (file_exists($img['path'])) {
                unlink($img['path']);
            }
            if (file_exists($img['thumb_path'])) {
                unlink($img['thumb_path']);
            }
        }
    }

    // Atualiza o arquivo JSON apenas se a imagem foi encontrada
    if ($image_found) {
        file_put_contents($json_file, json_encode($updated_images, JSON_PRETTY_PRINT), LOCK_EX);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {

    $extraImage = '';
    if (isset($_FILES['image-extra'])) {
        $imageExtra = $_FILES['image-extra'];
        $extraImageName = basename($imageExtra['name']);
        $extraunique_id = uniqid();
        $extratarget_file = $upload_dir . $extraunique_id . '_' . $extraImageName; // Gera um ID único
        $extrathumb_file = $thumb_dir . $extraunique_id . '_thumb_' . $extraImageName; // Caminho para a miniatura

        // Move a imagem extra para a pasta de upload
        if (move_uploaded_file($imageExtra['tmp_name'], $extratarget_file)) {
            $extraImage = $extratarget_file;
        }
    }

    $image = $_FILES['image'];
    $custom_name = isset($_POST['custom_name']) ? $_POST['custom_name'] : ''; // Captura o nome personalizado
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $license = isset($_POST['license']) ? $_POST['license'] : '';
    $linkAtivo = $_POST['link_ativo'];

    // Verifica se o arquivo enviado é uma imagem
    $check = getimagesize($image['tmp_name']);
    if ($check !== false) {
        $image_name = basename($image['name']);
        $unique_id = uniqid();
        $target_file = $upload_dir . $unique_id . '_' . $image_name; // Gera um ID único
        $thumb_file = $thumb_dir . $unique_id . '_thumb_' . $image_name; // Caminho para a miniatura

        // Move a imagem para a pasta de upload
        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Gera a miniatura usando Imagick
            try {
                $imagick = new Imagick($target_file);
                $dpi = $imagick->getImageResolution();
                $imagick->resizeImage(150, 150, Imagick::FILTER_LANCZOS, 1);
                $imagick->writeImage($thumb_file);
                $imagick->clear();
                $imagick->destroy();

                $thumb_path = $thumb_file;
            } catch (ImagickException $e) {
                $thumb_path = $target_file;
            }

            // Lê os dados EXIF da imagem
            $exif_data = @exif_read_data($target_file);
            $exif_info = !empty($exif_data) ? $exif_data : [];

            // Extrai dados XMP usando exiftool
            $xmp_data = [];
            $output = [];
            exec("exiftool -b -XMP " . escapeshellarg($target_file), $output, $exec_return);
            if ($exec_return === 0 && !empty($output)) {
                $xmp_string = implode("\n", $output);
                preg_match_all('/(\w+)="([^"]*)"/', $xmp_string, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $xmp_data[$match[1]] = $match[2];
                }
            }

            // Armazena os detalhes da imagem no arquivo JSON, incluindo os metadados XMP
            $image_details = [
                'id' => $unique_id,
                'name' => $image_name,
                'custom_name' => $custom_name,
                'description' => $description,
                'category' => $category,
                'link_ativo' => $linkAtivo,
                'path' => $target_file,
                'license' => $license,
                'extra-image' => $extraImage,
                'width' => $exif_info['COMPUTED']['Width'] ?? null,
                'height' => $exif_info['COMPUTED']['Height'] ?? null,
                'created_at' => $exif_info['DateTimeOriginal'] ?? null,
                'make' => $exif_info['Make'] ?? null,
                'model' => $exif_info['Model'] ?? null,
                'dpi' => $dpi['x'] . "x" . $dpi['y'],
                'GPSLatitude' => $exif_info['GPSLatitude'][1] ?? null,
                'GPSLongitude' => $exif_info['GPSLongitude'][1] ?? null,
                'Software' => $exif_info['Software'] ?? null,
                'DateTime' => $exif_info['DateTime'] ?? null,
                'thumb_path' => $thumb_path,
                'type' => $image['type'],
                'size' => round($image['size'] / (1024 * 1024), 2) . " Mb",
                'uploaded_at' => date('Y-m-d H:i:s'),
                'exif' => $exif_info,
                'xmp' => $xmp_data
            ];

            saveImageDetails($json_file, $image_details);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
            exit();
        } else {
            echo "Erro ao fazer o upload da imagem.";
        }
    } else {
        echo "O arquivo enviado não é uma imagem válida.";
    }
}


// Processa a exclusão de imagem
if (isset($_GET['delete'])) {
    $image_id = $_GET['delete'];
    deleteImage($json_file, $image_id);
    header('Location: ' . $_SERVER['PHP_SELF']); // Redireciona para a mesma página
    exit();
}

// Carrega todas as imagens do arquivo JSON
$images = loadImages($json_file);

// Função para renderizar uma tabela recursivamente
function renderTable($data, $title = null) {
    echo '<table border="1" cellpadding="10" cellspacing="0">';
    
    if ($title) {
        echo "<caption><strong>$title</strong></caption>";
    }
    
    foreach ($data as $key => $value) {
        echo '<tr>';
        echo "<td><strong>$key</strong></td>";
        
        if (is_array($value)) {
            echo '<td>';
            renderTable($value); // Chama recursivamente se o valor for um array
            echo '</td>';
        } else {
            echo "<td>$value</td>";
        }
        
        echo '</tr>';
    }
    
    echo '</table>';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Imagens</title>
    <style>
        body {
            display: block;
        }
        .image-list {
        }
        .image-item {
            margin: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            width: 160px;
            display: inline-block;
        }
        .image-item img {
            width: 100%;
            display: block;
        }
        .uploadImg {
        }
        .imgInfo{
            display:none;
        }
        .displayImg {
            position: fixed;
            top: 30px;
            left: 30px;
            width: 90vw;
            height: 90vh;
            background: white;
            padding: 30px;
            display: flex;
        }
        .imgShow, .infoShow {
            display: block;
        }
        .imgShow {
            width:52vw;
        }
        .infoShow {
            width: 34vw;
            display: flow; /* Flexbox para garantir que o conteúdo se ajuste corretamente */
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            overflow: auto;
        }
        .imgShow img{
            width: auto;
            max-width: 50vw;
            height: 80vh;
        }
        .show {
            display:flex;
        }
        .hide {
            display: none;
        }
        /* A tabela dentro da div */
        .infoShow table {
            width: 100%; /* Faz com que a tabela ocupe 100% da largura da div */
            height: 100%; /* Faz com que a tabela ocupe 100% da altura da div */
            font-size: 8pt;
        }

        .infoShow th, .infoShow td {
            word-wrap: break-word; /* Faz as palavras quebrarem, se necessário, para evitar overflow */
            text-align: left; /* Alinha o texto à esquerda nas células (pode ajustar conforme necessário) */
        }
        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            display:block;
            
        }
        .modal-bg.hide {
            display:none;
        }
        .form-div {
            width: 50%;
            float: left;
            padding: 5px;
        }
        .form-div-br {
            height: 2px;
            width: 100%;
            display: inline-block;
        }
    </style>
    <?php include "header.php"; ?>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
<div class="uploadImg jumbotron">
<h1>Upload de Imagens</h1>
<?php 

$jsonFile = 'categories.json';
$licenseFile = 'licenses.json';

// Função para carregar as categorias
function loadCategories($file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    return json_decode(file_get_contents($file), true);
}

// Carrega as categorias existentes
$categories = loadCategories($jsonFile);
$licenses = loadCategories($licenseFile);

?>
<!-- Formulário para upload de imagem -->
<form action="" method="POST" enctype="multipart/form-data">
    <div class="form-div">

    <label for="image">Escolha uma imagem:</label>
    <input class="form-control" type="file" name="image" id="image" required><br>

    <label for="custom_name">Nome</label>
    <input required class="form-control" type="text" name="custom_name" id="custom_name" placeholder="Digite um nome para o arquivo"><br>
    
    <label for="category">Categoria</label>
    <select id="category" name="category" class="form-control" required>
        <option value="" disabled selected>Selecione uma opção</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= htmlspecialchars($category['slug']) ?>">
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>
    


    
    
<label for="description">Descrição </label>
<textarea class="form-control" id="description" name="description" rows="4" cols="50" placeholder="Descreva a imagem aqui..."></textarea>
<br>
<label for="license">Licença</label>
    <select id="license" name="license" class="form-control" required>
        <option value="" disabled selected>Selecione uma opção</option>
        <?php foreach ($licenses as $license): ?>
            <option value="<?= htmlspecialchars($license['slug']) ?>">
                <?= htmlspecialchars($license['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>
<label for="image">Arquivo de Licença (Opcional)</label>
    <input class="form-control" type="file" name="image-extra" id="image-extra"><br>   
    <label for="link_ativo">Link de Uso (opcional):</label>
    <input class="form-control" type="text" name="link_ativo" id="link_ativo" placeholder="Link ativo da imagem"><br>
</div>
<div class="form-div-br"></div>
    <button type="submit" class="btn btn-dark">Enviar</button>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form'); // Seleciona o formulário
    const submitButton = form.querySelector('button[type="submit"]'); // Seleciona o botão de envio
    const loadingMessage = document.createElement('p'); // Cria um elemento para a mensagem de carregamento
    loadingMessage.textContent = "Carregando, por favor, aguarde...";
    loadingMessage.style.display = "none"; // Inicialmente, a mensagem está oculta
    form.appendChild(loadingMessage); // Adiciona a mensagem ao formulário

    form.addEventListener('submit', function (event) {
        // Desabilita o botão de envio para evitar múltiplos envios
        submitButton.disabled = true;
        // Mostra a mensagem de carregamento
        loadingMessage.style.display = "block";

        // Evitar múltiplos envios via tecla Enter ou clique no botão
        event.preventDefault();

        // Cria um objeto FormData para processar o envio do formulário via AJAX
        const formData = new FormData(form);

        // Envia o formulário usando Fetch API para manter o comportamento assíncrono
        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.ok) {
                // Se o upload for bem-sucedido, redireciona ou exibe a confirmação
                window.location.href = window.location.href + '?success=1';
            } else {
                // Se houver erro, reabilita o botão de envio
                submitButton.disabled = false;
                loadingMessage.textContent = "Erro no upload, tente novamente.";
            }
        }).catch(error => {
            // Se ocorrer um erro de rede, reabilita o botão de envio
            console.error('Erro:', error);
            submitButton.disabled = false;
            loadingMessage.textContent = "Erro no upload, tente novamente.";
        });
    });
});
</script>

</div>
</body>
<?php include "footer.php"; ?>
</html>
<?php

} catch (Exception $e) {
    // Capturar exceções e tratar o erro
    echo "Erro capturado: " . $e->getMessage();
} catch (ErrorException $e) {
    // Capturar erros tratados como exceções
    echo "Erro de execução capturado: " . $e->getMessage();
}
