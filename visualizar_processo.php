<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    //header("Location: index.php"); // Redirecionar para o login se não estiver logado
    //exit;
}

// Caminho para o arquivo JSON
$json_file = 'processos_auditoria.json';

// Verificar se os parâmetros pa_id e pa_key foram passados via GET
if (!isset($_GET['pa_id']) || !isset($_GET['pa_key'])) {
    header("Location: processos_auditoria.php");
    exit;
}

$pa_id = $_GET['pa_id'];
$pa_key = $_GET['pa_key'];

// Verificar se o arquivo JSON existe
if (!file_exists($json_file)) {
    header("Location: processos_auditoria.php");
    exit;
}

// Carregar os processos do arquivo JSON
$processos = json_decode(file_get_contents($json_file), true) ?? [];

// Buscar o processo com o ID passado
$processo = null;
foreach ($processos as $p) {
    if ($p['id'] === $pa_id) {
        $processo = $p;
        break;
    }
}

// Verificar se o processo foi encontrado e se a chave corresponde
if ($processo === null || $processo['pa_key'] !== $pa_key) {
    header("Location: processos_auditoria.php");
    exit;
}

// Carregar o arquivo uploads.json
$uploads_file = 'uploads.json';
$uploads = json_decode(file_get_contents($uploads_file), true) ?? [];

// Buscar a imagem original com base no ID
$original_image_path = null;
foreach ($uploads as $upload) {
    if ($upload['id'] === $processo['original_image']) {
        $original_image_path = $upload['path'];
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualização do Processo - <?php echo htmlspecialchars($processo['id']); ?></title>
    <style>
        html, body {
            background-color: #DEE9FB !important;
            font-family: Arial, sans-serif;
        }
        .processo-visualizacao {
            width: 80vw;
            margin: 10vh auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .processo-visualizacao h5 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .processo-visualizacao p {
            margin: 10px 0;
        }
        .d-flex {
            display: flex;
            justify-content: center;
            gap: 40px;
            align-items: flex-start;
            margin-top: 20px;
        }
        .img-thumbnail {
            max-width: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-voltar {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #f39c12;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-voltar:hover {
            background-color: #e67e22;
        }
        .text-center img{
            width: 200px;
        }
        .text-center:hover {
            cursor:pointer;
        }
        .text-center:hover img {
            opacity: 0.5;
        }
        .text-center i {
            font-size: 90px;
            position: absolute;
            transform: translateX(-140px);
            margin-top: 25px;
            opacity:0;
            color: rgba(0, 0, 0, 0.6)
        }
        .text-center:hover i {
            opacity: 100;
        }
    </style>
    <?php include 'header.php'; ?>
</head>
<body>
    <?php (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) ? include 'navbar.php' : ''; ?>

    <div class="processo-visualizacao">
        <!-- Processo de Auditoria -->
        <h5 class="text-secondary mb-3">
            <strong>Processo de Auditoria:</strong> 
            <span class="text-dark"><?php echo htmlspecialchars($processo['id']); ?></span>
        </h5>

        <!-- Informações Gerais -->
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Etapa:</strong> <?php echo htmlspecialchars($processo['etapa']); ?></li>
            <li class="list-group-item"><strong>Nome ou Referência:</strong> <?php echo htmlspecialchars($processo['refer_name']); ?></li>
            <li class="list-group-item">
                <strong>Link contestado:</strong> 
                <a href="<?php echo htmlspecialchars($processo['refer_link']); ?>" target="_blank" class="text-decoration-none">
                    <?php echo htmlspecialchars($processo['refer_link']); ?>
                </a>
            </li>
        </ul>

        <!-- Imagens -->
        <div class="d-flex">
            <!-- Imagem Contestada -->
            <div class="text-center">
                <h5>Imagem Contestada</h5>
                <img src="<?php echo htmlspecialchars($processo['image']); ?>" 
                     alt="Imagem contestada" class="img-thumbnail">
                     <i class="fa-solid fa-magnifying-glass-plus"></i>
            </div>

            <!-- Imagem Original -->
            <div class="text-center">
                <h5>Imagem Original</h5>
                <?php if ($original_image_path): ?>
                    <img src="<?php echo htmlspecialchars($original_image_path); ?>" 
                         alt="Imagem original" class="img-thumbnail">
                         <i class="fa-solid fa-magnifying-glass-plus"></i>
                <?php else: ?>
                    <p class="text-danger">Imagem original não encontrada.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contatos Conhecidos -->
        <div class="mt-4">
            <h5><strong>Contatos Conhecidos</strong></h5>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($processo['known_contacts'])); ?></p>
        </div>

        <!-- Observações -->
        <div class="mt-4">
            <h5><strong>Observações sobre a Contestação</strong></h5>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($processo['observation'])); ?></p>
        </div>

        <!-- Data de Criação -->
        <div class="mt-4">
            <p><strong>Data de Criação:</strong> 
                <span style="color:white" class="badge bg-secondary"><?php echo htmlspecialchars($processo['timestamp']); ?></span>
            </p>
        </div>

        <!-- Botões -->
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <a href="processos_auditoria.php" class="btn-voltar">Voltar para a lista de processos</a>
        <?php endif; ?>
            <a href="pa2.php?aprov=true&pa_id=<?php echo $processo['id']; ?>&pa_key=<?php echo $processo['pa_key']; ?>" class="btn btn-success">Aprovar processo</a>
            <a href="pa2.php?aprov=false&pa_id=<?php echo $processo['id']; ?>&pa_key=<?php echo $processo['pa_key']; ?>" class="btn btn-danger">Reprovar processo e Arquivar</a>
    </div>

    <?php include 'footer.php'; ?>

    <script>
     document.addEventListener("DOMContentLoaded", function () {
        // Seleciona todos os elementos com a classe .text-center
        const containers = document.querySelectorAll('.text-center');

        containers.forEach(function (container) {
            container.addEventListener('click', function (event) {
                // Procura a imagem <img> dentro do elemento clicado
                const img = container.querySelector('img');
                if (img) {
                    const imgSrc = img.getAttribute('src'); // Obtém o atributo src da imagem
                    window.open(imgSrc, '_blank'); // Abre a imagem em uma nova aba
                }
            });
        });
    });
</script>
</body>
</html>
