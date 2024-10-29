<?php

// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

// Função para carregar as imagens já enviadas do arquivo JSON
function loadImages($json_file) {
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $images = json_decode($json_data, true);

        // Verifica se a decodificação do JSON foi bem-sucedida
        if (json_last_error() === JSON_ERROR_NONE) {
            return $images;
        } else {
            return []; // Retorna um array vazio se o JSON estiver corrompido
        }
    }
    return [];
}

$json_file = 'uploads.json';

$images = loadImages($json_file);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel</title>
    <?php include 'header.php'; ?>
    <style>
        /* Mantém o fundo preto do thead ao passar o mouse */
        .table thead th {
            background-color: #343a40 !important; /* Fundo preto */
            color: #fff; /* Texto branco */
            border-color: #454d55; /* Borda um pouco mais clara */
        }

        /* Remove o efeito de hover do thead */
        .table thead:hover th {
            background-color: #343a40 !important; /* Garante que a cor não mude */
        }

        .imgInfo{
            display:none;
        }
        .displayImg {
            position: fixed;
            top: 30px;
            left: 30px;
            width: 90vw;
            height: 90vh;
            background: white;
            padding: 30px;
            display: flex;
        }
        .imgShow, .infoShow {
            display: block;
        }
        .imgShow {
            width:52vw;
        }
        .infoShow {
            width: 34vw;
            display: flow; /* Flexbox para garantir que o conteúdo se ajuste corretamente */
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            overflow: auto;
        }
        .imgShow img{
            width: auto;
            max-width: 50vw;
            height: 80vh;
        }
        .show {
            display:flex;
        }
        .hide {
            display: none;
        }
        /* A tabela dentro da div */
        .infoShow table {
            width: 100%; /* Faz com que a tabela ocupe 100% da largura da div */
            height: 100%; /* Faz com que a tabela ocupe 100% da altura da div */
            font-size: 8pt;
        }

        .infoShow th, .infoShow td {
            word-wrap: break-word; /* Faz as palavras quebrarem, se necessário, para evitar overflow */
            text-align: left; /* Alinha o texto à esquerda nas células (pode ajustar conforme necessário) */
        }
        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            display:block;
            
        }
        .modal-bg.hide {
            display:none;
        }

    </style>
</head>
<body>
    <?php include 'navbar.php';?>
    <div class="container">
    <table class="table table-striped table-bordered table-hover table-responsive text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>Seleciona</th>
            <th>Miniatura</th>
            <th>Nome</th>
            <th>Nome do Arquivo</th>
            <th>Detalhes</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($images)) : ?>
            <?php foreach ($images as $img) : ?>
                <tr imgId="<?php echo $img['id']; ?>" class="image-item">
                    <td>
                        <input value="" type="checkbox"/>
                    </td>
                    <td>
                        <img
                            width="50"
                            height="50"
                            imgId="<?php echo $img['id']; ?>"
                            class="img-thumbnail img-uploaded"
                            src="<?php echo $img['thumb_path']; ?>"
                            path="<?php echo $img['path']; ?>">
                    </td>
                    <td><?php echo !empty($img['custom_name']) ? $img['custom_name'] : 'N/A'; ?></td>
                    <td><?php echo $img['name']; ?></td>
                    <td><a href="#" onclick="abreDetalhes(this)" data='<?php echo json_encode($img);?>'>Abrir Detalhes</a></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="3">Nenhuma imagem enviada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="modal-bg hide"></div>
<div class="displayImg hide">
        <div class="infoShow"></div>
</div>
    </div>
</body>
<script>

function renderTable(data, title = null) {
    // Cria o elemento da tabela
    const table = document.createElement('table');
    table.border = "1";
    table.cellPadding = "10";
    table.cellSpacing = "0";

    // Se o título for fornecido, cria um elemento caption
    if (title) {
        const caption = document.createElement('caption');
        caption.innerHTML = `<strong>${title}</strong>`;
        table.appendChild(caption);
    }

    // Itera sobre o objeto ou array de dados
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const row = document.createElement('tr');

            // Cria a célula da chave
            const keyCell = document.createElement('td');
            keyCell.innerHTML = `<strong>${key}</strong>`;
            row.appendChild(keyCell);

            // Cria a célula de valor
            const valueCell = document.createElement('td');
            if (Array.isArray(data[key]) || typeof data[key] === 'object') {
                // Se o valor é um array ou objeto, chama a função recursivamente
                valueCell.appendChild(renderTable(data[key]));
            } else {
                valueCell.textContent = data[key];
            }
            row.appendChild(valueCell);

            // Adiciona a linha na tabela
            table.appendChild(row);
        }
    }

    return table;
}


function abreDetalhes(data){
    let img = data.getAttribute('data')
    console.log(img)
}

</script>
<?php include 'footer.php';?>
</html>