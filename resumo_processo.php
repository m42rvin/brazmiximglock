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
        iframe {
            width: 100%;
            height: 190vh; /* O iframe ocupará toda a altura visível */
            border: none; /* Remove a borda do iframe */
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Resumo do Processo</h1>
        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($processo_encontrado['refer_name']); ?></h2>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?php echo htmlspecialchars($processo_encontrado['id']); ?></p>
                <p><strong>Chave:</strong> <?php echo htmlspecialchars($processo_encontrado['pa_key']); ?></p>
                <p><strong>Etapa:</strong> <?php echo htmlspecialchars($processo_encontrado['etapa']); ?></p>
                <p><strong>Link de Referência:</strong> 
                    <a href="<?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>" target="_blank">
                        <?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>
                    </a>
                </p>
                <p><strong>Contatos Conhecidos:</strong> <?php echo htmlspecialchars($processo_encontrado['known_contacts']); ?></p>
                <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($processo_encontrado['observation'])); ?></p>
                <p><strong>Data e Hora:</strong> <?php echo htmlspecialchars($processo_encontrado['timestamp']); ?></p>
                <p><strong>Arquivado:</strong> <?php echo $processo_encontrado['archived'] ? 'Sim' : 'Não'; ?></p>
                <p>
                    <strong>Imagem do Processo:</strong><br>
                    <img src="<?php echo htmlspecialchars($processo_encontrado['image']); ?>" alt="Imagem do Processo" style="max-width: 100%; height: auto;">
                </p>
                <div class="page-break"></div>
                <?php if ($original_image): ?>
                    <hr>
                    <h3>Informações da Imagem Original</h3>
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
                <div class="page-break"></div>
                <h5>Comunicado Gerado</h5>
                <?php
                    // Substitua com o ID real do processo encontrado
                    $processo_id = $processo_encontrado['id'];
                    $pdf_path = "pdf/comunicado_processo_" . $processo_id . ".pdf";
                ?>
                <iframe 
                    src="<?php echo $pdf_path; ?>#toolbar=0&navpanes=0&scrollbar=0" 
                    title="Visualizar PDF" 
                    width="100%"
                    class="no-break"
                    style="border: none;">
                </iframe>
                <div class="no-break"></div>
                <br/>
                <?php if ($resposta_encontrada): ?>
                    <hr>
                    <h3>Resposta ao Processo</h3>
                    <p><strong>Contestação:</strong> <?php echo htmlspecialchars($resposta_encontrada['contestacao']); ?></p>
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
            </div>
        </div>
    </div>
</body>
</html>
