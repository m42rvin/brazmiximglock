<?php
require './fpdf/fpdf.php';

function renderTablePDF($pdf, $data, $x = 10, $y = null) {
    // Define a posição inicial
    if ($y !== null) {
        $pdf->SetY($y);
    }
    $pdf->SetX($x);

    // Cores e estilos
    $headerColor = [220, 220, 220];
    $rowColor1 = [245, 245, 245];
    $rowColor2 = [255, 255, 255];
    $currentRowColor = $rowColor1;

    // Percorre os dados e desenha a tabela
    foreach ($data as $key => $value) {
        // Define alternância de cor para as linhas
        $currentRowColor = ($currentRowColor === $rowColor1) ? $rowColor2 : $rowColor1;
        $pdf->SetFillColor(...$currentRowColor);

        // Configura fonte para os títulos das linhas
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 10, ucfirst($key), 1, 0, 'L', true); // Fundo colorido no título

        if (is_array($value)) {
            // Fundo para sub-tabelas
            $pdf->Cell(130, 10, "Sub-tabela ->", 1, 1, 'L', true);

            // Chama recursivamente se o valor for um array
            renderTablePDF($pdf, $value, $x + 10, $pdf->GetY());
        } else {
            // Exibe o valor
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(130, 10, $value, 1, 1, 'L', true);
        }

        $pdf->SetX($x); // Reseta a posição horizontal
    }
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
    $uploads = json_decode($jsonData, true);

    if (!$uploads) {
        die("Erro: Não foi possível ler o arquivo uploads.json.");
    }

    // Filtra os dados pelo array de IDs recebidos
    $filteredData = array_filter($uploads, function ($item) use ($ids) {
        return in_array($item['id'], $ids);
    });

    if (empty($filteredData)) {
        die("Erro: Nenhum dado encontrado para os IDs fornecidos.");
    }

    // Cria o PDF
    $pdf = new FPDF();
    $pdf->SetFont('Arial', '', 10);

    foreach ($filteredData as $data) {
        $pdf->AddPage();

        // Exibe a imagem como thumbnail (se disponível)
        if (!empty($data['path']) && file_exists($data['path'])) {
            $pdf->Image($data['path'], 15, $pdf->GetY() + 20, 180); // Imagem com largura fixa de 100
            $imageHeight = $pdf->GetY() + 60; // Altura ajustada para a imagem
            $pdf->SetY($imageHeight); // Move para abaixo da imagem
        } else {
            $pdf->Cell(0, 10, "Imagem: Nao encontrado.", 0, 1);
        }

        // Renderiza a tabela abaixo da imagem
        renderTablePDF($pdf, $data, 10, $pdf->GetY() + 100);
    }

    // Envia o PDF para download
    $pdf->Output('D', 'relatorio.pdf');
    exit;
} else {
    die("Erro: Nenhum dado enviado.");
}
