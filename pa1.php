<?php
// Iniciar sessão
session_start();

// Configurações
$json_file = 'processos_auditoria.json'; // Arquivo JSON para salvar os dados
$upload_dir = 'img_auditoria/'; // Diretório para salvar as imagens

// Garantir que o diretório de upload exista
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Cria o diretório com permissões apropriadas
}

// Verificar se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar os dados enviados
    $id = $_POST['id'] ?? uniqid(); // ID único do processo, se não for fornecido
    $etapa = $_POST['etapa'] ?? '1';
    $refer_name = $_POST['refer_name'] ?? '';
    $refer_link = $_POST['refer_link'] ?? '';
    $known_contacts = $_POST['known_contacts'] ?? '';
    $observation = $_POST['observation'] ?? '';
    $original_image = $_POST['original_image'] ?? '';

    // Gerar a chave única (pa_key) para acesso ao processo
    $pa_key = uniqid('pa_', true); // Geração de chave única para o processo

    // Processar o upload da imagem
    if (!empty($_FILES['image']['name'])) {
        $image_name = basename($_FILES['image']['name']);
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION); // Extensão do arquivo
        $new_image_name = $id . '_' . $image_ext; // Novo nome da imagem com o ID
        $image_path = $upload_dir . $new_image_name;

        // Mover o arquivo para o diretório especificado
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            die("Erro ao fazer upload da imagem.");
        }
    } else {
        die("Nenhuma imagem enviada.");
    }

    // Dados a serem salvos no JSON
    $new_data = [
        'id' => $id,
        'pa_key' => $pa_key, // Adicionar a chave única
        'etapa' => $etapa,
        'refer_name' => $refer_name,
        'refer_link' => $refer_link,
        'image' => $image_path,
        'known_contacts' => $known_contacts,
        'observation' => $observation,
        'timestamp' => date('Y-m-d H:i:s'), // Timestamp da submissão
        'original_image' => $original_image,
    ];

    // Carregar o conteúdo existente do JSON
    $json_data = [];
    if (file_exists($json_file)) {
        $json_data = json_decode(file_get_contents($json_file), true) ?? [];
    }

    // Adicionar os novos dados
    $json_data[] = $new_data;

    // Salvar de volta no arquivo JSON
    if (file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Redirecionar para processos_auditoria.php em caso de sucesso
        header("Location: processos_auditoria.php");
        exit();
    } else {
        die("Erro ao salvar os dados no arquivo JSON.");
    }
} else {
    echo "Método de requisição inválido.";
}
?>
