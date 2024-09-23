<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

// Caminho para a pasta onde as imagens serão salvas
$upload_dir = '_img/';
// Arquivo JSON para armazenar os detalhes das imagens
$json_file = 'uploads.json';

// Cria a pasta _img/ se não existir
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
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

    foreach ($current_images as $img) {
        if ($img['id'] !== $image_id) {
            $updated_images[] = $img;
        } else {
            // Remove o arquivo da pasta _img/
            if (file_exists($img['path'])) {
                unlink($img['path']);
            }
        }
    }

    file_put_contents($json_file, json_encode($updated_images, JSON_PRETTY_PRINT));
}

// Processa o envio de imagem
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Verifica se o arquivo é uma imagem válida
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($image['type'], $allowed_types)) {
        $image_name = basename($image['name']);
        $target_file = $upload_dir . uniqid() . '_' . $image_name; // Gera um ID único

        // Move a imagem para a pasta _img/
        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Lê os dados EXIF da imagem
            $exif_data = @exif_read_data($target_file);
            $exif_info = !empty($exif_data) ? $exif_data : [];

            // Armazena os detalhes da imagem no arquivo JSON
            $image_details = [
                'id' => uniqid(), // Gera um ID único para a imagem
                'name' => $image_name,
                'path' => $target_file,
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
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        echo "Formato de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Imagens</title>
    <style>
        body {
            display: flex;
        }
        .image-list {
            flex: 1;
        }
        .image-item {
            margin: 10px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .image-item img {
            width: 150px;
            display: block;
        }
        .uploadImg {
            flex: 1;
        }
    </style>
    <?php include "header.php"; ?>
</head>
<body>
<div class="uploadImg">
<h1>Upload de Imagens</h1>

<!-- Formulário para upload de imagem -->
<form action="" method="POST" enctype="multipart/form-data">
    <label for="image">Escolha uma imagem:</label>
    <input type="file" name="image" id="image" required>
    <button type="submit" class="btn btn-dark">Enviar</button>
</form>
</div>
    
<!-- Exibe as imagens enviadas -->
<div class="image-list">
    <h2>Imagens enviadas:</h2>
    <?php if (!empty($images)) : ?>
        <?php foreach ($images as $img) : ?>
            <div class="image-item">
                <table>
                    <tr>
                        <td><img class="img-uploaded" src="<?php echo $img['path']; ?>" alt="<?php echo $img['name']; ?>"></td>
                        <td>
                            <p><strong>Nome:</strong> <?php echo $img['name']; ?></p>
                            <p><strong>Enviado em:</strong> <?php echo $img['uploaded_at']; ?></p>
                            <?php if (!empty($img['exif'])) : ?>
                                <p><strong>EXIF:</strong> <?php echo json_encode($img['exif'], JSON_PRETTY_PRINT); ?></p>
                            <?php endif; ?>
                            <a href="?delete=<?php echo urlencode($img['id']); ?>" onclick="return confirm('Tem certeza que deseja excluir esta imagem?');">Excluir</a>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nenhuma imagem enviada.</p>
    <?php endif; ?>
</div>

<script>
// Adiciona um evento de clique a todas as imagens com a classe .img-uploaded
document.querySelectorAll('.img-uploaded').forEach(function(img) {
    img.addEventListener('click', function() {
        window.open(this.src, '_blank'); // Abre a imagem em uma nova aba
    });
});
</script>
</body>
<?php include "footer.php"; ?>
</html>
