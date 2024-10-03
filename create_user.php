<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

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

// Função para salvar dados no arquivo JSON
function saveJsonFile($filename, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $jsonData);
}

// Função para gerar hash de senha
function hashPassword($password) {
    return hash('sha256', $password);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validação básica (você pode adicionar mais validações se necessário)
    if (empty($username) || empty($password) || empty($role)) {
        echo "Por favor, preencha todos os campos.";
    } else {
        // Lê os dados existentes do arquivo JSON
        $users = readJsonFile($jsonFile);

        // Gera um novo ID baseado no último usuário
        $lastUser = end($users);
        $newId = $lastUser ? $lastUser['id'] + 1 : 1;

        // Cria um novo usuário
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => hashPassword($password), // Salva a senha com hash
            'role' => $role
        ];

        // Adiciona o novo usuário à lista
        $users[] = $newUser;

        // Salva a lista atualizada no arquivo JSON
        saveJsonFile($jsonFile, $users);

        echo "Usuário criado com sucesso!";
    }
}
?>

<!-- Formulário para criar um novo usuário -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário</title>
    <?php include "header.php"; ?>
</head>
<body>
    <div class="container">
        <?php include "navbar.php"; ?>
        <h1>Criar Usuário</h1>
        <form action="create_user.php" method="post">
            <label for="username">Nome de usuário:</label><br>
            <input type="text" id="username" name="username"><br><br>

            <label for="password">Senha:</label><br>
            <input type="password" id="password" name="password"><br><br>

            <label for="role">Função (role):</label><br>
            <select id="role" name="role">
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select><br><br>

            <input type="submit" value="Criar Usuário">
        </form>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
