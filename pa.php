<?php

// Iniciar sessão
session_start();

?>

<?php
// Função para gerar um ID único
function generateUniqueId() {
    return uniqid(); // Gera um ID único
}

// Verifica se há parâmetros GET
if (empty($_SERVER['QUERY_STRING'])) {
    // Não há parâmetros, então adicionamos o pa_id
    $pa_id = generateUniqueId();
    $new_url = "pa.php?pa_id=$pa_id"; // Caminho relativo para redirecionamento

    // Redireciona para a nova URL com o parâmetro
    header("Location: $new_url");
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processo de Auditoria</title>
    <style>
        html,body{
            background-image: url('./logo\ imglock.jpeg');
            background-size: cover;
        }
        .pa_disclaimer {
            border:1px solid #000;
            width: fit-content;
            padding: 10px 100px;
            text-align: center;
            margin-left: 50%;
            transform: translateX(-50%);
        }
        .title {
            text-align: center;
            margin-top: 20px;   
        }
    </style>
    <?php include 'header.php'; ?>
    
</head>
<body>
    <?php include 'navbar.php';?>
    <div class="container">
    <div class="jumbotron">

    <div class="pa_disclaimer">
        <h5>Processo de Auditoria</h5>
        <p>Código: <?php echo $_GET['pa_id'];?></p>
        <p>Situação: Aberto</p>
    </div>
        
    <div class="title">
        <h5>DADOS DA PARTE CONTESTADA SOBRE O USO INDEVIDO DAS IMAGENS REGISTRADAS:</h5>
    </div>
<!-- Formulário para upload de imagem -->
    <form action="pa1.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $_GET['pa_id'];?>"/>
    <input type="hidden" name="etapa" value="1"/>
    <div class="form-div">
    <label for="refer_name">Nome ou referência conhecidos</label>
    <input required class="form-control" type="text" name="refer_name" id="refer_name" placeholder="Nome ou referência"><br>
    
    <label for="refer_link">Link objeto de contestação</label>
    <input required class="form-control" type="text" name="refer_link" id="refer_link" placeholder="Link para o objeto contestado"><br>
     

    <label for="image">Imagem objeto de contestação:</label>
    <input class="form-control" type="file" name="image" id="image" required><br>

    <label for="known_contacts">Contatos conhecidos: </label>
    <textarea class="form-control" id="known_contacts" name="known_contacts" rows="4" cols="50" placeholder="Coloque as formas de contato conhecidas aqui"></textarea>
    
    <label for="observation">Observações sobre a contestação: </label>
    <textarea class="form-control" id="observation" name="observation" rows="4" cols="50" placeholder="Observações sobre a contestação"></textarea>
<div class="form-div-br"></div>
    <button type="submit" class="btn btn-dark">Enviar</button>
</form>
</div>
    
<?php include 'footer.php';?>
</html>