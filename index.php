<?php
// Iniciar sessão
session_start();

// Username e senha fixos
$valid_username = "admin";
$valid_password = "123456";

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Verificar se os dados de login são válidos
    if ($username == $valid_username && $password == $valid_password) {
        // Login bem-sucedido, armazenar na sessão
        $_SESSION["loggedin"] = true;
        header("Location: dashboard.php"); // Redirecionar para uma página após o login
        exit;
    } else {
        $error = "Username ou password incorretos.";
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
            <input type="text" class="form-control" name="username" aria-describedby="usernamelHelp" placeholder="Seu nome de Usuário">
            <small id="usernamelHelp" class="form-text text-muted">Preencha com o nome do usuário desejado</small>
        </div>
        <div class="form-group">
            <label for="password">Sua Senha</label>
            <input type="password" class="form-control" name="password" placeholder="Sua Senha">
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
