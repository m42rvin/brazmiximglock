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
        return json_decode($json_data, true);
    }
    return [];
}

// Função para salvar os detalhes da imagem no arquivo JSON
function saveImageDetails($json_file, $image_details) {
    $current_images = loadImages($json_file);
    $current_images[] = $image_details;
    file_put_contents($json_file, json_encode($current_images, JSON_PRETTY_PRINT));
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
        file_put_contents($json_file, json_encode($updated_images, JSON_PRETTY_PRINT));
    }
}

// Processa o envio de imagem
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $custom_name = isset($_POST['custom_name']) ? $_POST['custom_name'] : ''; // Captura o nome personalizado

    // Verifica se o arquivo enviado é uma imagem
    $check = getimagesize($image['tmp_name']);
    if ($check !== false) {
        $image_name = basename($image['name']);
        $unique_id = uniqid();
        $target_file = $upload_dir . $unique_id . '_' . $image_name; // Gera um ID único
        $thumb_file = $thumb_dir . $unique_id . '_thumb_' . $image_name; // Caminho para a miniatura

        // Move a imagem para a pasta _img/
        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Gera a miniatura usando ImageMagick
            $command = "convert $target_file -resize 150x150 $thumb_file";
            exec($command, $output, $return_var);

            // Verifica se a miniatura foi criada corretamente
            if ($return_var === 0) {
                // Miniatura criada corretamente
                $thumb_path = $thumb_file;
            } else {
                // Se a miniatura não puder ser criada, usa o caminho da imagem original como thumb
                $thumb_path = $target_file;
            }
            
            // Lê os dados EXIF da imagem
            $exif_data = @exif_read_data($target_file);
            $exif_info = !empty($exif_data) ? $exif_data : [];
            
            // Armazena os detalhes da imagem no arquivo JSON, incluindo o caminho da miniatura ou da imagem original
            $image_details = [
                'id' => $unique_id, // Gera um ID único para a imagem
                'name' => $image_name,
                'custom_name' => $custom_name, // Adiciona o nome personalizado
                'path' => $target_file,
                'thumb_path' => $thumb_path, // Caminho da miniatura ou da imagem original
                'type' => $image['type'],
                'size' => $image['size'],
                'uploaded_at' => date('Y-m-d H:i:s'),
                'exif' => $exif_info // Adiciona os dados EXIF
            ];
            
            saveImageDetails($json_file, $image_details);
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1'); // Redireciona para a mesma página (PRG)
            exit(); // Encerra o script para evitar o reenvio do formulário
            
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
            position: absolute;
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
            position: absolute;
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
    </style>
    <?php include "header.php"; ?>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
<div class="uploadImg jumbotron">
<h1>Upload de Imagens</h1>

<!-- Formulário para upload de imagem -->
<form action="" method="POST" enctype="multipart/form-data">
    <label for="image">Escolha uma imagem:</label>
    <input type="file" name="image" id="image" required><br><br>
    
    <label for="custom_name">Nome personalizado:</label>
    <input type="text" name="custom_name" id="custom_name" placeholder="Digite um nome para o arquivo"><br><br>
    
    <button type="submit" class="btn btn-dark">Enviar</button>
</form>
</div>
    
<!-- Exibe as imagens enviadas -->
<div class="image-list">
    <h2>Imagens enviadas:</h2>
    <?php if (!empty($images)) : ?>
        <?php foreach ($images as $img) : ?>
            <div imgId="<?php echo $img['id'];?>" class="image-item">
                <img imgId="<?php echo $img['id'];?>" class="img-uploaded" src="<?php echo $img['thumb_path']; ?>" alt="<?php echo $img['name']; ?>">
                <p><strong>Nome personalizado:</strong> <?php echo !empty($img['custom_name']) ? $img['custom_name'] : 'N/A'; ?></p>
                <table>
                    <tr>
                        <td class="imgInfo" imgId="<?php echo $img['id'];?>">
                            <a class="btn btn-danger" href="?delete=<?php echo urlencode($img['id']); ?>" onclick="return confirm('Tem certeza que deseja excluir esta imagem?');"><i class="fa-solid fa-trash"></i> Excluir</a>
                            <button type="button" class="btn btn-success"><i class="fa-solid fa-share-from-square"></i> Gerar Link</button>
                            <hr/> 
                            <?php if (!empty($img['exif'])) : ?>
                                <?php renderTable($img['exif']); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nenhuma imagem enviada.</p>
    <?php endif; ?>
</div>
<div class="modal-bg hide"></div>
<div class="displayImg hide">
        <div class="imgShow">
            <img src="" alt="">
        </div>
        <div class="infoShow"></div>
</div>
<script>
// Adiciona um evento de clique a todas as imagens com a classe .img-uploaded
document.querySelectorAll('.img-uploaded').forEach(function(img) {
    img.addEventListener('click', function() {
        var imgId= this.getAttribute('imgId');
        var element = document.querySelector('.image-item[imgId="'+imgId+'"] .imgInfo');
        var innerHTML = element.innerHTML; // Pega o HTML interno do elemento
        var targetElement = document.querySelector('.infoShow');
        targetElement.innerHTML = innerHTML;
        var imageElement = document.querySelector('.imgShow > img');
        imageElement.src = this.src;
        //window.open(this.src, '_blank'); // Abre a imagem em uma nova aba
        var divElement = document.querySelector('.displayImg.hide');

// Verifica se o elemento existe
if (divElement) {
    document.querySelector('.modal-bg').classList.remove('hide')
    divElement.classList.remove('hide');  // Remove a classe 'hide'
    divElement.classList.add('show');     // Adiciona a classe 'show'
} else {
    console.log('Elemento não encontrado');
}
    });
});
document.querySelector('.modal-bg').addEventListener('click', function() {
    var divElement = document.querySelector('.displayImg');
    document.querySelector('.modal-bg').classList.add('hide')
    divElement.classList.add('hide');  // Remove a classe 'hide'
    divElement.classList.remove('show');  
})
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