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
$imagick->setResolution(300, 300); // 300 DPI é um bom valor para qualidade

// Define o formato da imagem para JPG
$imagick->setImageFormat('jpg');

// Salva a imagem na pasta "jpg"
if ($imagick->writeImage($jpg_path)) {
    echo "PDF convertido com sucesso para JPG.";
} else {
    die("Erro: Falha ao salvar o arquivo JPG.");
}

// Limpeza de memória
$imagick->clear();
$imagick->destroy();
?>
