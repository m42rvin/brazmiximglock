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
    header("Location: processos_auditoria.php"); // Redirecionar para a página de processos se parâmetros não forem fornecidos
    exit;
}

$pa_id = $_GET['pa_id'];
$pa_key = $_GET['pa_key'];

// Verificar se o arquivo JSON existe
if (!file_exists($json_file)) {
    header("Location: processos_auditoria.php"); // Redirecionar se o arquivo JSON não existir
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
    header("Location: processos_auditoria.php"); // Redirecionar caso o processo não seja encontrado ou a chave não corresponda
    exit;
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
            background-image: url('./logo\ imglock.jpeg');
            background-size: cover;
        }
        .processo-visualizacao {
            width: 80vw;
            margin: 10vh auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
        }
        .processo-visualizacao h5 {
            margin-bottom: 20px;
        }
        .processo-visualizacao p {
            margin: 10px 0;
        }
        .processo-visualizacao img {
            max-width: 50%;
            margin-top: 20px;
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
    </style>
    <?php include 'header.php'; ?>
</head>
<body>
<?php (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) ? include 'navbar.php' : ''; ?>


    <?php if ($processo['etapa'] == '1'){ ?>
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

                <!-- Imagem Contestada -->
                <div class="mt-4 text-center">
                    <h5>Imagem Contestada</h5>
                    <img src="<?php echo htmlspecialchars($processo['image']); ?>" 
                         alt="Imagem do processo" class="img-thumbnail" style="max-width: 300px;">
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
        <?php 
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        ?>
        <a href="processos_auditoria.php" class="btn-voltar">Voltar para a lista de processos</a>
        <?php } else { ?>
            <a href="pa2.php?aprov=true&pa_id=<?php echo $processo['id']?>&pa_key=<?php echo $processo['pa_key']?>" class="btn btn-success">Aprovar processo</a>
            <a href="pa2.php?aprov=false&pa_id=<?php echo $processo['id']?>&pa_key=<?php echo $processo['pa_key']?>" class="btn btn-danger">Reprovar processo e Arquivar</a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php include 'footer.php'; ?>
</body>
</html>
