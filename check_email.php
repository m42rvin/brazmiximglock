<?php
// Caminho para o arquivo JSON
$jsonFile = 'users.json';

// Função para ler o arquivo JSON
function readJsonFile($filename) {
    if (file_exists($filename)) {
        $jsonData = file_get_contents($filename);
        return json_decode($jsonData, true);
    } else {
        return [];
    }
}

// Verifica se o e-mail foi enviado via query string
if (isset($_GET['email'])) {
    $email = $_GET['email'];

    // Lê os dados existentes do arquivo JSON
    $users = readJsonFile($jsonFile);

    // Verifica se o e-mail já existe no arquivo JSON
    $emailExists = false;
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $emailExists = true;
            break;
        }
    }

    // Retorna uma resposta em JSON
    header('Content-Type: application/json');
    echo json_encode(['exists' => $emailExists]);
}
