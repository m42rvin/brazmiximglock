<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8'); // Garantir que a página use UTF-8

// Caminho do arquivo uploads.json
$uploadsFile = 'uploads.json';

// Verifica se o arquivo existe
if (!file_exists($uploadsFile)) {
    die("Arquivo de uploads não encontrado.");
}

// Lê e decodifica o JSON
$uploads = json_decode(file_get_contents($uploadsFile), true);

function renderPDFContent($data) {

    // Exibe as outras informações
    return [
        'Custom Name' => $data['custom_name'] ?? 'Informação não informada',
        'Categoria' => $data['category'] ?? 'Informação não informada',
        'Licença' => $data['license'] ?? 'Informação não informada',
        //'Extra Image' => $data['extra-image'] ?? 'Informação não informada',
        'Largura' => $data['width'] ?? 'Informação não informada',
        'Altura' => $data['height'] ?? 'Informação não informada',
        'Criado em' => $data['created_at'] ?? 'Informação não informada',
        'Câmera' => $data['make'] ?? 'Informação não informada',
        'Modelo' => $data['model'] ?? 'Informação não informada',
        'DPI' => $data['dpi'] ?? 'Informação não informada',
        'GPS Latitude' => $data['GPSLatitude'] ?? 'Informação não informada',
        'GPS Longitude' => $data['GPSLongitude'] ?? 'Informação não informada',
        'Software' => $data['Software'] ?? 'Informação não informada',
        'Data e Hora' => $data['DateTime'] ?? 'Informação não informada',
        'Tamanho do Arquivo' => $data['size'] ?? 'Informação não informada',
        'Data de Upload' => $data['uploaded_at'] ?? 'Informação não informada',
        'Imagem' => $data['path'] ?? ''
    ];

    
}


// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    // IDs enviados via POST
    $ids = json_decode($_POST['generate']); // Deve ser um array
    if (!is_array($ids)) {
        die("Erro: IDs devem ser enviados como um array.");
    }

    // Filtra os dados pelo array de IDs recebidos
    $filteredData = array_filter($uploads, function ($item) use ($ids) {
        return in_array($item['id'], $ids);
    });

    if (empty($filteredData)) {
        die("Erro: Nenhum dado encontrado para os IDs fornecidos.");
    }
?>
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
        table {
            font-size: 10px;
        }
        .sysdesc {
            display: inline-block;
            border: 1px solid black;
            padding: 10px;
            text-align: center;
            position: absolute;
            right: 20%;
            top: 40px;
            font-size: 10px !important; 
        }

</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
<img src="logo-black.png" style="width: 200px; margin-left: 10%; margin-bottom: 50px;margin-top: 50px"/>
<div class="sysdesc">
Brazmix Equipamentos para Mineração Ltda,<br/> inscrita sob CNPJ 17.903.283/0001-84
</div>
<table style="width:80%; margin-left:10%">
<tbody>
    <?php foreach ($filteredData as $data): ?>
        <?php $content = renderPDFContent($data); ?>
        <?php foreach ($content as $key => $value): ?>
            <tr class="border-bottom border-dark">
                <td class="align-top"><?php echo htmlspecialchars($key); ?></td>
                <td>
                    <?php if ($key === 'Imagem'): ?>
                        <img src="<?php echo htmlspecialchars($value); ?>" alt="Imagem" style="max-width: 300px;margin-bottom:50px">
                    <?php else: ?>
                        <?php echo htmlspecialchars($value); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</tbody>

</table>
<?php
} else {
    die("Erro: Nenhum dado enviado.");
}

?>
