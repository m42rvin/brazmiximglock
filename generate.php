<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8'); // Garantir que a página use UTF-8

require './fpdf/fpdf.php';

class PDF extends FPDF
{
    // Sobrescrever o método header para garantir que a fonte seja UTF-8
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Relatório de Imagens'), 0, 1, 'C');
        $this->Ln(5);
    }

    // Sobrescrever o método footer para ajustar a posição do rodapé
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

function renderPDFContent($pdf, $data, $x = 10, $y = null) {
    // Define a posição inicial
    if ($y !== null) {
        $pdf->SetY($y);
    }
    $pdf->SetX($x);

    // Verifica se é necessário adicionar uma nova página antes de começar a renderizar conteúdo
    if ($pdf->GetY() > 250) {
        $pdf->AddPage(); // Adiciona nova página se a altura do conteúdo ultrapassar o limite
    }

    // Exibe a imagem como thumbnail no início
    if (!empty($data['thumb_path']) && file_exists($data['thumb_path'])) {
        $pdf->Image($data['thumb_path'], 15, $pdf->GetY() + 10, 50); // Ajuste tamanho da thumbnail
        $imageHeight = $pdf->GetY() + 60; // Altura ajustada para a imagem
        $pdf->SetY($imageHeight); // Move para abaixo da imagem
    } else {
        $pdf->Cell(0, 10, "Imagem: Não encontrada.", 0, 1);
    }

    // Cores e estilos
    $headerColor = [220, 220, 220];
    $rowColor1 = [245, 245, 245];
    $rowColor2 = [255, 255, 255];
    $currentRowColor = $rowColor1;

    // Adicionando título do documento
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Relatório de Imagens'), 0, 1, 'C');
    $pdf->Ln(5);

    // Exibe o nome do arquivo
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode("Nome do Arquivo: " . $data['name']), 0, 1);
    $pdf->SetFont('Arial', '', 10);

    // Exibe a descrição
    $pdf->MultiCell(0, 10, utf8_decode("Descrição: " . $data['description']));
    $pdf->Ln(5);

    // Exibe o link ativo
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode("Link Ativo: " . $data['link_ativo']), 0, 1);
    $pdf->Ln(5);

    // Exibe as informações do EXIF
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode('Informações EXIF'), 0, 1);
    $pdf->SetFont('Arial', '', 10);

    // EXIF Subsections
    foreach ($data['exif'] as $exifKey => $exifValue) {
        if (is_array($exifValue)) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, utf8_decode("$exifKey: " . implode(", ", $exifValue)), 0, 1);
        } else {
            $pdf->Cell(0, 10, utf8_decode("$exifKey: $exifValue"), 0, 1);
        }
    }

    $pdf->Ln(10); // Espaço entre as seções
}

// Função para garantir que os dados JSON estejam em UTF-8
function utf8_encode_array($data) {
    if (is_array($data)) {
        return array_map('utf8_encode_array', $data);
    } elseif (is_string($data)) {
        return utf8_encode($data); // Codifica cada string individualmente para UTF-8
    }
    return $data;
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    // IDs enviados via POST
    $ids = json_decode($_POST['generate']); // Deve ser um array
    if (!is_array($ids)) {
        die("Erro: IDs devem ser enviados como um array.");
    }

    // Carrega o arquivo uploads.json
    $jsonData = file_get_contents('uploads.json');
    $jsonData = utf8_encode($jsonData); // Forçar UTF-8 na leitura do arquivo
    $uploads = json_decode($jsonData, true);

    if (!$uploads) {
        die("Erro: Não foi possível ler o arquivo uploads.json.");
    }

    // Força a codificação UTF-8 em todo o conteúdo dos uploads
    $uploads = utf8_encode_array($uploads);

    // Filtra os dados pelo array de IDs recebidos
    $filteredData = array_filter($uploads, function ($item) use ($ids) {
        return in_array($item['id'], $ids);
    });

    if (empty($filteredData)) {
        die("Erro: Nenhum dado encontrado para os IDs fornecidos.");
    }

    // Cria o PDF
    $pdf = new PDF();
    $pdf->AddPage();

    foreach ($filteredData as $data) {
        // Renderiza conteúdo de cada item
        renderPDFContent($pdf, $data, 10, $pdf->GetY());
    }

    // Envia o PDF para download
    $pdf->Output('D', 'relatorio.pdf');
    exit;
} else {
    die("Erro: Nenhum dado enviado.");
}

?>