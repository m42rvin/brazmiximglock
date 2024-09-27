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
    $image_found = false; // Para rastrear se a imagem foi encontrada e excluída

    foreach ($current_images as $img) {
        if ($img['id'] !== $image_id) {
            $updated_images[] = $img;
        } else {
            $image_found = true;
            // Remove o arquivo da pasta _img/
            if (file_exists($img['path'])) {
                unlink($img['path']);
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
        }
    } else {
        echo "Formato de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.";
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
<div class="container">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="#"><img src="https://www.brazmix.com/www/imagens/site/logo.png?1" alt=""></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="/dashboard.php">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Opções
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="/logout.php">Sair</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
<div class="uploadImg jumbotron">
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
            <div imgId="<?php echo $img['id'];?>" class="image-item">
                <img imgId="<?php echo $img['id'];?>" class="img-uploaded" src="<?php echo $img['path']; ?>" alt="<?php echo $img['name']; ?>">
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
            <img src="http://localhost:8000/_img/66f2ba6f22658_IMG_20210316_141444023.jpg" alt="">
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
