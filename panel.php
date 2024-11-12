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
            max-height: 90vh;
            background: white;
            padding: 30px;
            display:inline-block;
            overflow:auto;
            overflow-x: hidden;
        }
        .imgShow, .infoShow {
            display: block;
        }
        .imgShow {
            width:100%;
            overflow: scroll;
        }
        .show {
            display:block;
        }
        .hide {
            display: none;
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
        .imgDetalhes {
            max-width: 60vw;
        }
        .table td.nomearquivo {
            width: 142px;
            display: flow;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        th:hover {
            cursor:pointer;
            opacity:0.9;
        }
    </style>
    
</head>
<body>
    <?php include 'navbar.php';?>
    <div class="container">
    <table id="myTable" class="table table-striped table-bordered table-hover table-responsive text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th onclick="sortTable(0)">Nome do Arquivo</th>
            <th onclick="sortTable(1)">Nome</th>
            <th onclick="sortTable(2, 'date')">Data upload</th>
            <th onclick="sortTable(3, 'date')">Data criação</th>
            <th onclick="sortTable(4)">Categoria</th>
            <th onclick="sortTable(5)">Licença</th>
            <th>Miniatura</th>
            <th>Detalhes</th>
            <th>Seleciona</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($images)) : ?>
            <?php foreach (array_reverse($images) as $img) : ?>
            <tr imgId="<?php echo $img['id']; ?>" class="image-item">
                <td><?php echo !empty($img['custom_name']) ? $img['custom_name'] : 'N/A'; ?></td>
                <td class="nomearquivo"><?php echo $img['name']; ?></td>
                <td><?php echo $img['uploaded_at']; ?></td>
                <td><?php echo $img['created_at']; ?></td>
                <td><?php echo !empty($img['category']) ? $img['category'] : 'N/A'; ?></td>
                <td><?php echo !empty($img['license']) ? $img['license'] : 'N/A'; ?></td>
                <td>
                    <img
                    width="50"
                    height="50"
                    imgId="<?php echo $img['id']; ?>"
                    class="img-thumbnail img-uploaded"
                    src="<?php echo $img['thumb_path']; ?>"
                    path="<?php echo $img['path']; ?>">
                </td>
                <td><a href="#" onclick="abreDetalhes(this)" data='<?php echo json_encode($img);?>'>Abrir Detalhes</a></td>
                    <td>
                        <input value="" type="checkbox"/>
                    </td>
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
    let img = JSON.parse(data.getAttribute('data'))
    
    let displayImg = document.querySelector('.displayImg');
    displayImg.innerHTML="";
    
    displayImg.innerHTML = `<img class="imgDetalhes" src='${img['path']}'/><br/>`;
    
    // console.log(img['extra-image'])
    if(img['extra-image'] ){

        displayImg.innerHTML += `<a href='${img['extra-image']}' target="_blank">Arquivo de Licença</a><br/>`;
    }
    

    
    displayImg.append(renderTable(img));
    
    document.querySelector('.modal-bg').classList.remove('hide')
    displayImg.classList.remove('hide');  // Remove a classe 'hide'
    displayImg.classList.add('show'); 
    
    displayImg.scrollTop = 0;

}
document.querySelector('.modal-bg').addEventListener('click', function() {
    var divElement = document.querySelector('.displayImg');
    document.querySelector('.modal-bg').classList.add('hide')
    divElement.classList.add('hide');  // Remove a classe 'hide'
    divElement.classList.remove('show');  
})
</script>
<script>
function sortTable(columnIndex, type = 'string') {
    const table = document.getElementById("myTable");
    let rows, switching, i, x, y, shouldSwitch, direction, switchcount = 0;
    switching = true;
    direction = "asc"; // Definindo direção inicial como ascendente

    while (switching) {
        switching = false;
        rows = table.rows;

        // Percorre todas as linhas, exceto o cabeçalho
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

            // Comparação para strings ou datas
            if (type === 'string') {
                if ((direction === "asc" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) ||
                    (direction === "desc" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())) {
                    shouldSwitch = true;
                    break;
                }
            } else if (type === 'date') {
                // Função para converter o formato de data
                function parseCustomDate(dateStr) {
                    if (!dateStr) return null;
                    // Substitui ':' por '-' apenas nos primeiros dois pontos para o formato YYYY-MM-DD HH:MM:SS
                    return new Date(dateStr.replace(/^(\d{4}):(\d{2}):(\d{2})/, '$1-$2-$3'));
                }

                const dateX = x.innerHTML ? parseCustomDate(x.innerHTML) : (direction === "asc" ? new Date(0) : new Date(9999, 11, 31));
                const dateY = y.innerHTML ? parseCustomDate(y.innerHTML) : (direction === "asc" ? new Date(0) : new Date(9999, 11, 31));

                // Log para depuração
                console.log("Comparando datas:");
                console.log("Data X:", x.innerHTML, "->", dateX);
                console.log("Data Y:", y.innerHTML, "->", dateY);

                if ((direction === "asc" && dateX > dateY) || (direction === "desc" && dateX < dateY)) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            // Muda a direção se nenhuma troca foi feita
            if (switchcount === 0 && direction === "asc") {
                direction = "desc";
                switching = true;
            }
        }
    }
}


</script>
<?php include 'footer.php';?>
</html>