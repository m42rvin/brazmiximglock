<?php 
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}
?>

<?php
// Verifica se o ID do processo foi passado via GET
if (!isset($_GET['pa_id'])) {
    die("Erro: ID do processo não fornecido.");
}

// Obtém o ID do processo
$pa_id = $_GET['pa_id'];

// Caminho do arquivo PDF
$pdf_path = 'pdf/comunicado_processo_' . $pa_id . '.pdf';

// Verifica se o arquivo PDF existe
if (!file_exists($pdf_path)) {
    die("Erro: O arquivo PDF não foi encontrado.");
}

// Caminho de destino para o arquivo JPG
$jpg_path = 'jpg/comunicado_processo_' . $pa_id . '.jpg';

// Converte o PDF em JPG usando ImageMagick
$imagick = new Imagick();


// Define o número de páginas a serem convertidas (aqui, convertendo a primeira página)
$imagick->readImage($pdf_path . '[0]'); // '[0]' para a primeira página, caso queira mais, altere o número
$imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
// Define a resolução (opcional, mas pode ajudar a melhorar a qualidade)
$imagick->setResolution(600, 600); // 300 DPI é um bom valor para qualidade

// Define o formato da imagem para JPG
$imagick->setImageFormat('jpg');

// Salva a imagem na pasta "jpg"
if ($imagick->writeImage($jpg_path)) {
    // echo "PDF convertido com sucesso para JPG.";
} else {
    die("Erro: Falha ao salvar o arquivo JPG.");
}

// Limpeza de memória
$imagick->clear();
$imagick->destroy();

?>
<style>
    img {
        width: 60%;
        margin-left: 20%;
    }
</style>

<?php

// Caminhos para os arquivos JSON
$json_file_processos = 'processos_auditoria.json';
$json_file_uploads = 'uploads.json';
$json_file_respostas = 'resposta_processo.json'; // Novo arquivo para respostas

// Recuperar o ID do processo da URL
$pa_id = isset($_GET['pa_id']) ? $_GET['pa_id'] : null;

// Verificar se o ID foi fornecido
if (!$pa_id) {
    echo "<h1>ID do processo não fornecido.</h1>";
    exit;
}

// Verificar se os arquivos JSON existem
if (!file_exists($json_file_processos) || !file_exists($json_file_uploads) || !file_exists($json_file_respostas)) {
    echo "<h1>Arquivo de processos, uploads ou respostas não encontrado.</h1>";
    exit;
}

// Ler os dados dos JSONs
$json_data_processos = file_get_contents($json_file_processos);
$processos = json_decode($json_data_processos, true);

$json_data_uploads = file_get_contents($json_file_uploads);
$uploads = json_decode($json_data_uploads, true);

$json_data_respostas = file_get_contents($json_file_respostas); // Carregar as respostas
$respostas = json_decode($json_data_respostas, true);

// Procurar pelo processo com o ID correspondente
$processo_encontrado = null;
foreach ($processos as $processo) {
    if ($processo['id'] === $pa_id) {
        $processo_encontrado = $processo;
        break;
    }
}

// Verificar se o processo foi encontrado
if (!$processo_encontrado) {
    echo "<h1>Processo com ID {$pa_id} não encontrado.</h1>";
    exit;
}

// Procurar pela resposta correspondente
$resposta_encontrada = null;
foreach ($respostas as $resposta) {
    if ($resposta['id_processo'] === $pa_id) {
        $resposta_encontrada = $resposta;
        break;
    }
}

// Recuperar a imagem original, se disponível
$original_image = null;
if (!empty($processo_encontrado['original_image'])) {
    foreach ($uploads as $upload) {
        if ($upload['id'] === $processo_encontrado['original_image']) {
            $original_image = $upload;
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo do Processo</title>
    <style>
          @media print {
            .page-break {
                page-break-before: always; /* Força uma quebra antes deste elemento */
            }
            .no-break {
                page-break-inside: avoid; /* Evita que o conteúdo seja dividido */
            }
            body {
                margin: 0; /* Remove margens do corpo do conteúdo */
                padding: 0;
            }
        }
        .logo-brazmix {
            width: 400px;
            text-align: left;
            margin-left: 0px;
        }
        .sysdesc {
            display: inline-block;
            border: 1px solid black;
            padding: 10px;
            text-align: center;
            margin-left: 200px;
            transform: translateY(18px);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <img class="logo-brazmix" src="logo-black.png"/>
        <span class="sysdesc">Sistema de auditoria para segurança e <br/> licenciamento de imagens</span>
        <br/><br/>
        <h3>Resumo do Processo ID: <?php echo htmlspecialchars($processo_encontrado['id']); ?> - Status <?php echo date('d/m/Y'); ?> <?php echo ($processo_encontrado['etapa'] == 4) ? 'Finalizado' : 'Aberto'; ?></h3>
        <div class="card">
            <div class="card-body">
                <p><strong>Nome da Contestação:</strong> <?php echo htmlspecialchars($processo_encontrado['refer_name']); ?></p>
                <p><strong>Data e hora da abertura:</strong> <?php echo htmlspecialchars($processo_encontrado['timestamp']); ?></p>
                <p><strong>Usuário Responsável:</strong> nome_usuario</p>
                <p><strong>Etapa:</strong> <?php echo htmlspecialchars($processo_encontrado['etapa']); ?></p>
                <p><strong>Contatos Conhecidos Sobre a Contestação:</strong> <?php echo htmlspecialchars($processo_encontrado['known_contacts']); ?></p>
                <p><strong>Observações Sobre a Contestação:</strong> <?php echo nl2br(htmlspecialchars($processo_encontrado['observation'])); ?></p>
                <p>
                    <strong>Imagem Capturada para Contestação:</strong><br>
                    <img src="<?php echo htmlspecialchars($processo_encontrado['image']); ?>" alt="Imagem do Processo" style="max-width: 100%; height: auto;">
                </p>
                <p><strong>Endereço de website capturado para contestação:</strong> 
                    <a href="<?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>" target="_blank">
                        <?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>
                    </a>
                </p>
                <h3>Comunicação gerada sobre a contestação:</h3>
                <?php
                    // Substitua com o ID real do processo encontrado
                    $processo_id = $processo_encontrado['id'];
                    $pdf_path = "jpg/comunicado_processo_" . $processo_id . ".jpg";
                ?>
                <img 
                    src="<?php echo $pdf_path; ?>" 
                    title="Visualizar PDF" 
                    width="100%"
                    class="no-break"
                    style="border: none;"/>
                <div class="no-break"></div>
                <br/>
                <div class="page-break"></div>
                <?php if ($resposta_encontrada): ?>
                    <hr>
                    <h3>Resposta ao Processo</h3>
                    <p><strong>Contestação:</strong> 
                    <?php
                        $contestacaoMensagens = [
                            'concorda_remocao' => 'Confirmo que irei interromper o uso das imagens envolvidas nesse processo com o prazo de 7 dias.',
                            'mais_informacoes' => 'Preciso de mais informações sobre o processo, solicito contato direto para melhor entendimento.',
                            'nao_concordo' => 'Não concordo com os apontamentos realizados e manterei o uso das imagens mesmo assim.',
                            'quero_vender' => 'Quero re-vender com autorização do uso de imagens da Brazmix.'
                        ];
                        
                        echo isset($contestacaoMensagens[$resposta_encontrada['contestacao']]) 
                            ? htmlspecialchars($contestacaoMensagens[$resposta_encontrada['contestacao']]) 
                            : 'Contestação não encontrada';
                    ?>
                </p>
            <p><strong>Texto da Resposta:</strong> <?php echo nl2br(htmlspecialchars($resposta_encontrada['texto_resposta'])); ?></p>
                    <p><strong>Data da Resposta:</strong> <?php echo htmlspecialchars($resposta_encontrada['data_resposta']); ?></p>
                    <?php if (!empty($resposta_encontrada['email_resposta'])): ?>
                        <p><strong>Email de Resposta:</strong> <?php echo htmlspecialchars($resposta_encontrada['email_resposta']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($resposta_encontrada['telefone_resposta'])): ?>
                        <p><strong>Telefone de Resposta:</strong> <?php echo htmlspecialchars($resposta_encontrada['telefone_resposta']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><strong>Resposta:</strong> Não disponível.</p>
                <?php endif; ?>
                <br/>
                <?php if ($original_image): ?>
                    <hr>
                    <h3>Dados EXIF da imagem original:	</h3>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($original_image['name']); ?></p>
                    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($original_image['description']); ?></p>
                    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($original_image['category']); ?></p>
                    <p><strong>Dimensões:</strong> <?php echo htmlspecialchars($original_image['width']) . " x " . htmlspecialchars($original_image['height']); ?></p>
                    <p><strong>Data de Criação:</strong> <?php echo htmlspecialchars($original_image['created_at']); ?></p>
                    <p><strong>Fabricante:</strong> <?php echo htmlspecialchars($original_image['make']); ?></p>
                    <p><strong>Modelo:</strong> <?php echo htmlspecialchars($original_image['model']); ?></p>
                    <p><strong>Software:</strong> <?php echo htmlspecialchars($original_image['Software']); ?></p>
                    <p><strong>Imagem:</strong><br>
                        <img src="<?php echo htmlspecialchars($original_image['path']); ?>" alt="Imagem Original" style="max-width: 100%; height: auto;">
                    </p>
                <?php else: ?>
                    <p><strong>Imagem Original:</strong> Não disponível.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
