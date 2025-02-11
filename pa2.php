<?php
// Caminho do arquivo JSON
$jsonFile = 'processos_auditoria.json';

// Recebe parâmetros via GET
$aprov = isset($_GET['aprov']) ? $_GET['aprov'] : null;
$pa_id = isset($_GET['pa_id']) ? $_GET['pa_id'] : null;
$pa_key = isset($_GET['pa_key']) ? $_GET['pa_key'] : null;
$motivo = isset($_GET['motivo']) ? $_GET['motivo'] : null;

// Valida os parâmetros obrigatórios
if ($aprov === null || $pa_id === null || $pa_key === null) {
    die("Parâmetros inválidos! Verifique a URL.");
}

// Lê e decodifica o arquivo JSON
$data = json_decode(file_get_contents($jsonFile), true);

$status = null;
$found = false;

// Percorre os processos para encontrar o correto
foreach ($data as &$processo) {
    if ($processo['id'] === $pa_id && $processo['pa_key'] === $pa_key) {
        $found = true;
        
        if ($aprov === 'true') {
            $processo['etapa'] = '2'; // Passa para etapa 2
            $processo['archived'] = false;
            $processo['aprove_date'] = date('Y-m-d H:i:s');
            $status = 'APROVADO';
            $bgColor = 'green';
            $fontColor = 'white';
            $icon = '<i class="fas fa-check-circle"></i>'; // Ícone de check
        } elseif ($aprov === 'false') {
            $processo['etapa'] = '4';
            $processo['archived'] = true; // Arquiva o processo
            $processo['aprove_date'] = date('Y-m-d H:i:s');
            $processo['motivo'] = $motivo;
            $status = 'DESAPROVADO';
            $bgColor = 'red';
            $fontColor = 'white';
            $icon = '<i class="fas fa-times-circle"></i>'; // Ícone de X
        } else {
            die("Parâmetro 'aprov' inválido. Use 'true' ou 'false'.");
        }
        break;
    }
}

// Se o processo não for encontrado
if (!$found) {
    die("Processo não encontrado! Verifique os parâmetros.");
}

// Salva as alterações no arquivo JSON
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

// Exibe a mensagem de status
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Processo</title>
    <!-- FontAwesome CDN -->
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-color: <?php echo $bgColor; ?>;
            color: <?php echo $fontColor; ?>;
            font-family: Arial, sans-serif;
            font-size: 3rem;
        }
        .status-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-sair {
            text-align: center;
        }
    </style>
    <?php include('header.php');?>
    <script>
        // Redireciona para a raiz do site
        function sair() {
            window.location.href = '/';
        }
    </script>
</head>
<body>
    <div>
        <div class="status-icon">
            <?php echo $icon; ?>
        </div>
        <h1><?php echo "PROCESSO $status COM SUCESSO!"; ?></h1>
        <button class="btn btn-danger btn-sair" onclick="sair()"><i class="fa-solid fa-arrow-right-from-bracket"></i> SAIR</button>
    </div>
    <?php include('footer');?>
</body>
</html>
