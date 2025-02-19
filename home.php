<?php

// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        html,body{
            background-image: url('./logo\ imglock.jpeg') !important;
            background-size: cover !important;
        }
    </style>
    <?php include 'header.php'; ?>
    
</head>
<body>
    <?php include 'navbar.php';?>
    
<?php include 'footer.php';?>
</html>