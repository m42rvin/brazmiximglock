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
        <h4>Resumo do Processo ID: <?php echo htmlspecialchars($processo_encontrado['id']); ?> - Status <?php echo date('d/m/Y'); ?> <?php echo ($processo_encontrado['etapa'] == 4) ? 'Finalizado' : 'Aberto'; ?></h4>
        <table>
            <tbody>
                <colgroup>
                    <col style="width: 200px;">
                    <col>
                </colgroup>
                <tr class="border-bottom">
                    <td><strong>Nome da Contestação:</strong> </td>
                    <td><p><?php echo htmlspecialchars($processo_encontrado['refer_name']); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Data e hora da abertura:</strong> </td>
                    <td><p><?php echo htmlspecialchars($processo_encontrado['timestamp']); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Usuário Responsável:</strong> </td>
                    <td><p>nome_usuario</p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Contatos Conhecidos Sobre a Contestação:</strong> </td>
                    <td><p><?php echo htmlspecialchars($processo_encontrado['known_contacts']); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Observações Sobre a Contestação:</strong> </td>
                    <td><p><?php echo nl2br(htmlspecialchars($processo_encontrado['observation'])); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Imagem Capturada para Contestação:</strong></td>
                    <td><img src="<?php echo htmlspecialchars($processo_encontrado['image']); ?>" alt="Imagem do Processo" style="max-width: 100%; height: auto;"></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Endereço de website capturado para contestação:</strong> </td>
                    <td><a href="<?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>" target="_blank">
                        <?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>
                    </a></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Comunicação gerada sobre a contestação:</strong></td>
                    <td><?php
                    // Substitua com o ID real do processo encontrado
                    $processo_id = $processo_encontrado['id'];
                    $pdf_path = "jpg/comunicado_processo_" . $processo_id . ".jpg";
                ?>
                <img 
                    src="<?php echo $pdf_path; ?>" 
                    title="Visualizar PDF" 
                    width="100%"
                    class="no-break"
                    style="border: none;"/></td>
                </tr>
                <?php if ($resposta_encontrada): ?>
                <tr class="border-bottom">
                    <td><strong>Resposta ao Processo</strong></td>
                    <td>
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
                    </td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Texto da Resposta:</strong></td>
                    <td><p> <?php echo nl2br(htmlspecialchars($resposta_encontrada['texto_resposta'])); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Data da Resposta:</strong></td>
                    <td><p> <?php echo htmlspecialchars($resposta_encontrada['data_resposta']); ?></p></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Email de Resposta:</strong></td>
                    <td><?php echo htmlspecialchars($resposta_encontrada['email_resposta']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Telefone de Resposta:</strong> </td>
                    <td><?php echo htmlspecialchars($resposta_encontrada['telefone_resposta']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($original_image): ?>
                <tr class="border-bottom">
                    <td></td>
                    <td><h3>Dados EXIF da imagem original:</h3></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Nome:</strong></td>
                    <td><?php echo htmlspecialchars($original_image['name']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Descrição:</strong> </td>
                    <td><?php echo htmlspecialchars($original_image['description']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Categoria:</strong> </td>
                    <td><?php echo htmlspecialchars($original_image['category']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Dimensões:</strong> </td>
                    <td><?php echo htmlspecialchars($original_image['width']) . " x " . htmlspecialchars($original_image['height']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Data de Criação:</strong></td>
                    <td><?php echo htmlspecialchars($original_image['created_at']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Fabricante:</strong></td>
                    <td><?php echo htmlspecialchars($original_image['make']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Modelo:</strong> </td>
                    <td><?php echo htmlspecialchars($original_image['model']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Software:</strong> </td>
                    <td><?php echo htmlspecialchars($original_image['Software']); ?></td>
                </tr>
                <tr class="border-bottom">
                    <td><strong>Imagem:</strong></td>
                    <td><img src="<?php echo htmlspecialchars($original_image['path']); ?>" alt="Imagem Original" style="max-width: 100%; height: auto;"></td>
                </tr>
                <?php endif; ?>
                </tbody>
                </table>
    </div>
</body>
</html>
