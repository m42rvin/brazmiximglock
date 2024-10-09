<?php
// Função para ler o arquivo JSON
function readJsonFile($filename) {
    if (file_exists($filename)) {
        $jsonData = file_get_contents($filename);
        return json_decode($jsonData, true);
    } else {
        return [];
    }
}

// Verificar se o parâmetro 'username' foi enviado via GET
if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $jsonFile = 'users.json'; // Caminho para o arquivo JSON
    $users = readJsonFile($jsonFile);

    // Verifica se o nome de usuário já existe
    $userExists = false;
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $userExists = true;
            break;
        }
    }

    // Retorna a resposta em formato JSON
    echo json_encode(['exists' => $userExists]);
} else {
    // Se não houver 'username' no GET, retorna um erro
    echo json_encode(['error' => 'Parâmetro username não fornecido']);
}
?>
