<?php
// Iniciar sessão
session_start();

// Caminho para o arquivo JSON onde os usuários são armazenados
$users_file = 'users.json';

// Verificar se o arquivo JSON existe
if (!file_exists($users_file)) {
    die("Arquivo de usuários não encontrado.");
}

// Função para carregar os usuários do arquivo JSON
function load_users($file) {
    $users_json = file_get_contents($file);
    return json_decode($users_json, true);
}

// Função para buscar um usuário pelo nome
function find_user_by_username($users, $username) {
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

// Carregar os usuários do arquivo JSON
$users = load_users($users_file);

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Buscar o usuário pelo nome
    $user = find_user_by_username($users, $username);

    if ($user) {
        // Gerar o hash SHA-256 da senha inserida para comparar com o hash salvo
        $password_hash = hash('sha256', $password);

        // Verificar se a senha inserida bate com a senha do arquivo JSON
        if ($password_hash === $user['password']) {
            // Login bem-sucedido
            $user_id = $user['id'];

            // Verificar se já existe uma sessão ativa com o mesmo ID
            if (isset($_SESSION['active_users'][$user_id])) {
                // Destruir a sessão ativa anterior do usuário
                session_id($_SESSION['active_users'][$user_id]);
                session_start();
                session_destroy();
                
                // Voltar para a sessão atual
                session_id(session_create_id());
                session_start();
            }

            // Salvar informações na sessão atual
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $user['username'];
            $_SESSION["role"] = $user['role'];
            $_SESSION["id"] = $user_id;
            $_SESSION['active_users'][$user_id] = session_id(); // Registra a sessão ativa do usuário

            header("Location: dashboard.php"); // Redirecionar para a página de dashboard
            exit;
        } else {
            // Senha incorreta
            $error = "Senha incorreta.";
        }
    } else {
        // Usuário não encontrado
        $error = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login</title>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href='./index.css'/>
</head>
<body>
    <div class="container">
        
    <form class="form-login" method="post" action="index.php">
        <div class="form-group">
            <img src="https://www.brazmix.com/www/imagens/site/logo.png?1" alt="">
        </div>
        <div class="form-group">
            <label for="username">Seu nome de Usuário</label>
            <input type="text" class="form-control" name="username" aria-describedby="usernamelHelp" placeholder="Seu nome de Usuário" required>
            <small id="usernamelHelp" class="form-text text-muted">Preencha com o nome do usuário desejado</small>
        </div>
        <div class="form-group">
            <label for="password">Sua Senha</label>
            <input type="password" class="form-control" name="password" placeholder="Sua Senha" required>
        </div>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <button type="submit" class="btn btn-dark"><i class="fa-solid fa-right-to-bracket"></i> Entrar</button>
    </form>
    </div>
    <?php include 'footer.php';?>
</body>
</html>
