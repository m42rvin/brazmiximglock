<?php

require('fpdf/fpdf.php'); // Caminho para a biblioteca FPDF

// Verifica se o parâmetro 'pa_id' foi fornecido
if (!isset($_GET['pa_id'])) {
    die("Parâmetro 'pa_id' é obrigatório.");
}

$pa_id = $_GET['pa_id'];

// Caminho do arquivo JSON
$jsonFile = 'processos_auditoria.json';

// Verifica se o arquivo existe
if (!file_exists($jsonFile)) {
    die("Arquivo de dados não encontrado.");
}


// Lê e decodifica o JSON
$data = json_decode(file_get_contents($jsonFile), true);

// Filtra o processo pelo ID
$processoEncontrado = null;
foreach ($data as $processo) {
    if ($processo['id'] === $pa_id) {
        $processoEncontrado = $processo;
        break;
    }
}

// Se não encontrar o processo
if (!$processoEncontrado) {
    die("Processo com ID '{$pa_id}' não encontrado.");
}




$keysFile = 'keys.json'; // Caminho para o arquivo JSON

// Função para adicionar uma nova chave ao arquivo JSON
function adicionarChaveAoArquivo($processoId, $novaChave, $arquivo) {
    // Verifica se o arquivo já existe
    if (file_exists($arquivo)) {
        $dados = json_decode(file_get_contents($arquivo), true);
    } else {
        $dados = []; // Inicia um array vazio se o arquivo não existir
    }

    // Procura pelo processo no array
    $processoExiste = false;
    foreach ($dados as &$processo) {
        if ($processo['id'] === $processoId) {
            // Adiciona a nova chave à lista de keys
            $processo['keys'][] = $novaChave;
            $processoExiste = true;
            break;
        }
    }

    // Se o processo não existir, cria um novo registro
    if (!$processoExiste) {
        $dados[] = [
            'id' => $processoId,
            'keys' => [$novaChave]
        ];
    }

    // Salva os dados atualizados no arquivo JSON
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
}

adicionarChaveAoArquivo($processoEncontrado['id'], $_GET['chave'], $keysFile);










// Caminho do arquivo uploads.json
$uploadsFile = 'uploads.json';

// Verifica se o arquivo existe
if (!file_exists($uploadsFile)) {
    die("Arquivo de uploads não encontrado.");
}

// Lê e decodifica o JSON
$uploadsData = json_decode(file_get_contents($uploadsFile), true);

// Procura o caminho da imagem original no uploads.json com base no ID fornecido
$imageOriginalPath = null;
foreach ($uploadsData as $upload) {
    if ($upload['id'] === $processoEncontrado['original_image']) {
        $imageOriginalPath = $upload['path']; // Caminho da imagem original
        break;
    }
}

// Caminho para a pasta _thumb
$thumbDir = '_thumb';

// Certifique-se de que o diretório _thumb existe, caso contrário, crie-o
if (!is_dir($thumbDir)) {
    mkdir($thumbDir, 0777, true); // Cria a pasta com permissões adequadas
}

// Caminho para a imagem reduzida na pasta _thumb
$reducedImagePath = $thumbDir . '/reduced_' . basename($imageOriginalPath);

// Cria a instância do Imagick
$image_ = new Imagick();

// Carrega a imagem original
$image_->readImage($imageOriginalPath);

// Redimensiona a imagem para no máximo 200px de largura, mantendo a proporção
$image_->resizeImage(200, 0, Imagick::FILTER_LANCZOS, 1);

// Remove os dados EXIF
$image_->stripImage();

// Salva a imagem reduzida na pasta _thumb
$image_->writeImage($reducedImagePath);

// Libera os recursos da imagem
$image_->clear();
$image_->destroy();



// Gera o PDF com a biblioteca FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Adiciona o logo da empresa (substituindo o título)
$logoPath = 'logo.png'; // Caminho para o logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 8, 50); // Ajuste a posição e tamanho conforme necessário
} else {
    // Caso o logo não seja encontrado, imprime um texto de fallback
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, utf8_decode('Comunicação Extra-Oficial'), 0, 1, 'C');
}

$pdf->Ln(8); // Reduz o espaçamento entre a imagem e o próximo conteúdo

// Define o título simples com fonte vermelha
$pdf->SetTextColor(220, 20, 60); // Cor do texto: Vermelho (Tom de vermelho chamativo)
$pdf->SetFont('Arial', 'B', 11); // Fonte: Arial, Negrito, Tamanho 16

// Adiciona o título na página
$pdf->MultiCell($pageWidth, 10, utf8_decode("COMUNICAÇÃO FORMAL SOBRE ABERTURA DE PROCESSO DE VERIFICAÇÃO DE USO DE IMAGENS OU RECURSOS DIGITAIS PROTEGIDOS"), 0, 'C');


$pdf->Ln(5); // Reduz o espaçamento após o título

// Adiciona aviso em caixa branca com texto preto
$pdf->SetFillColor(255, 255, 255); // Cor de fundo: Branco
$pdf->SetTextColor(0, 0, 0); // Cor do texto: Preto
$pdf->SetFont('Arial', 'B', 12);

// Texto do aviso
$textoAviso = utf8_decode("PROCESSO DE AUDITORIA\nCódigo: ".$processoEncontrado['id']."\nSituação: Aberto");

// Calcula a largura necessária para o texto
$larguraTexto = $pdf->GetStringWidth($textoAviso) + 10; // Adiciona uma margem de 10

// Centraliza a caixa na página
$larguraPagina = $pdf->GetPageWidth(); // Largura da página
$posicaoX = ($larguraPagina - $larguraTexto) / 2; // Calcula a posição X para centralizar

// Adiciona a caixa com o texto, com a largura ajustada ao texto
$pdf->SetXY($posicaoX, $pdf->GetY()); // Posiciona a célula no X calculado e mantém a posição Y atual
$pdf->MultiCell($larguraTexto, 8, $textoAviso, 1, 'C', true); // Usando altura menor
$pdf->Ln(6); // Reduz o espaçamento entre os blocos de texto

// Adiciona o texto explicativo sobre o uso inadequado de imagens
$pdf->SetFont('Arial', '', 8); // Reduz o tamanho da fonte para caber mais texto

// Texto explicativo
$textoExplicativo = utf8_decode("Prezado, a Brazmix Equipamentos para Mineração Ltda, inscrita sob CNPJ 17.903.283/0001-84 e a Brazmix Comercial Exportadora de Bombas para Mineração, inscrita sob CNPJ 17.895.838/0001-93, entram em contato para iniciar um processo de verificação referente a utilização de imagens e/ou recursos digitais visuais em anúncios de venda de produtos em plataforma que verificamos estar sob sua responsabilidade.\n\n");
$textoExplicativo .= utf8_decode("Em processo rotineiro de auditoria dos diversos recursos digitais que são de propriedade das empresas mencionadas acima, foi verificada a utilização de uma ou mais imagens em anúncios públicos de venda de produtos na rede mundial de computadores.\n\n");
$textoExplicativo .= utf8_decode("Informamos que as imagens em questão, constam atualmente registradas como de uso restrito de nossas empresas ou parceiros, por serem de nossa autoria e não terem sido liberadas para livre cópia ou reprodução de terceiros sem prévia autorização.\n\n");
$textoExplicativo .= utf8_decode("Essa etapa tem por objetivo verificar junto com você essa ocorrência e no caso de que seja procedente, lhe deixar saber sobre o controle do uso e reprodução destas imagens ou recursos, dando a oportunidade para que possamos seguir com a mais rápida resolução possível para esse assunto.\n\n");
$textoExplicativo .= utf8_decode("Deste modo, é possível conferir abaixo, os dados de o que estamos verificando nesse processo e também um link de acesso ao nosso sistema de auditoria, para que você possa entrar e registrar sua posição quanto aos apontamentos que estamos fazendo nesse momento.");
//$textoExplicativo .= utf8_decode("Este aviso é dado para assegurar que todos estejam cientes das implicações legais e éticas do uso não autorizado, e para prevenir futuros conflitos. Em caso de dúvidas ou para solicitar permissão para o uso de qualquer material, entre em contato com o setor responsável da Brazmix.");

// Adiciona o texto explicativo no PDF
$pdf->MultiCell(0, 3, $textoExplicativo); // Usando altura menor para as linhas


// Adiciona o subtítulo "Imagem Contestada"
$pdf->Ln(10); // Espaço entre o texto e o subtítulo
$pdf->SetFont('Arial', 'B', 14); // Fonte para o subtítulo
$pdf->Cell(0, 10, utf8_decode("Imagem Contestada"), 0, 1, 'C'); // Alinhado à esquerda

// Adiciona a imagem no final como miniatura
$imagePath = $processoEncontrado['image']; // Caminho da imagem
if (file_exists($imagePath)) {
    // Define a largura da miniatura (por exemplo, 40mm), mantendo a proporção da altura automaticamente
    $pdf->Image($imagePath, 55, $pdf->GetY(), 100); 
    //$pdf->Image($reducedImagePath, 100, $pdf->GetY(), 80); 
    $pdf->Ln(50); // Adiciona um espaço após a imagem para não sobrepor o conteúdo
} else {
    $pdf->Cell(0, 10, utf8_decode("Imagem não encontrada"), 0, 1, 'L');
}
// Adiciona o link contestado
$pdf->Ln(10); // Espaço antes do link
$pdf->SetTextColor(0, 0, 255); // Cor do texto para o link: azul

$link = $processoEncontrado['refer_link']; // Obtém o link contestado
$pdf->SetFont('Arial', 'B', 14); // Fonte sublinhada (para o link)
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, utf8_decode("Acesso para anúncio ou site contestado:* "), 0, 1, 'C'); // Texto do link
$pdf->SetTextColor(0, 0, 255);
$pdf->SetFont('Arial', 'U', 11); // Fonte sublinhada

// Obtém a largura da página
$pageWidth = $pdf->GetPageWidth();

// Define o link que será exibido
$linkText = utf8_decode($link);

// Obtém a largura do texto do link
$textWidth = $pdf->GetStringWidth($linkText);

// Calcula a posição X para centralizar
$xPosition = ($pageWidth - $textWidth) / 2;

// Define a posição X manualmente antes de escrever o link
$pdf->SetX($xPosition);
$pdf->Cell($textWidth, 5, $linkText, 0, 1, 'C', false, $link); // Clicável e centralizado

$pdf->SetFont('Arial', '', 8); // Fonte para os cabeçalhos
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(15);

$pdf->MultiCell(0, 3, utf8_decode("* A retirada deste conteúdo do ar em plataformas de venda ou site não finaliza esse processo de auditoria, o qual deve ser respondido no período estabelecido para que nossa equipe responsável faça as verificações necessárias e finalize o processo. Importante salientar também que a única via de contato necessária e possível nesse momento para este assunto é pelo sistema com os dados abaixo")); 





$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(15); // Espaço entre o texto e o subtítulo
$link_ = $_GET['pa_url']; // Obtém o link contestado
$códigoAcesso = $_GET['chave']; // Obtém o código de acesso

// Definindo o layout da tabela
$pdf->SetFont('Arial', 'B', 11); // Fonte para os cabeçalhos
$pdf->SetTextColor(0, 0, 0);

// Cabeçalhos da tabela
$pdf->Cell(95, 10, utf8_decode("Link de acesso ao sistema:"), 1, 0, 'L'); 
$pdf->Cell(95, 10, utf8_decode("Código de Acesso ao Processo"), 1, 1, 'L'); 
// Configurações do FPDF
$pdf->SetFont('Arial', '', 11);

// Largura das colunas
$larguraLink = 95; // Largura para o link
$larguraCodigo = 95; // Largura para o código de acesso
$alturaLinha = 6; // Altura da célula

// Dados da tabela
$pdf->SetTextColor(0, 0, 255);
$pdf->SetFont('Arial', 'U', 10); // Fonte para o link

// Salva a posição inicial
$x = $pdf->GetX();
$y = $pdf->GetY();

// Exibe o link com quebra automática
$pdf->MultiCell($larguraLink, $alturaLinha, utf8_decode($link_), 1, 'L');

// Restaura a posição para o início da linha e ajusta para a próxima célula
$pdf->SetXY($x + $larguraLink, $y);

// Exibe o código de acesso com quebra automática
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 11); // Fonte para o código de acesso
$pdf->MultiCell($larguraCodigo, $alturaLinha, utf8_decode($códigoAcesso), 1, 'L');


$pdf->Ln(10); // Espaço após a tabela

// Link clicável (após a tabela)
// $pdf->SetFont('Arial', 'U', 11); // Fonte sublinhada
// $pdf->Write(5, utf8_decode("Clique aqui para responder à contestação"), $link_); // Define o link clicável


// Salva o PDF na pasta "pdf"
$outputPath = 'pdf/comunicado_processo_'.$processoEncontrado['id'].'.pdf';
$pdf->Output('F', $outputPath); // Salva o arquivo no servidor

// Agora, você pode enviar o PDF para download ou qualquer outra operação que precise ser realizada
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="comunicado_processo_'.$processoEncontrado['id'].'.pdf"');
readfile($outputPath); // Envia o arquivo para o navegador
?>
