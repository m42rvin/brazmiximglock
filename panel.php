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
        .displayImg table {
            width: 100%;
            border: 2px solid black;
            margin-top: 20px;
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
            width: 20vw;
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
        .imgDetalhes {
            display: inline-block;
            float: left;
        }
        .doc-file {
            display: inline-block;
            float: right;
            text-align: justify;
            position: absolute;
            right: 500px;
            top: 130px;
        }
        .doc-file i {
            font-size: 150px;
        }
        .link-image {
            display: grid;
        }
        #myTable {
            background: #fff;
        }
    </style>
    
</head>
<body>
    <?php include 'navbar.php';?>
    <div class="container">
    <div>
        <br>
        <button type="button" disabled class="btn-action btn btn-success">Gerar Processo de Auditoria</button>
        <button type="button" disabled class="btn-action btn-generate btn btn-success">Gerar Arquivo de Auditoria</button>
        <button type="button" disabled class="btn-action btn-download btn btn-info">Download do Arquivo</button>
        <button type="button" disabled class="btn-action btn-del btn btn-danger">Deletar Arquivo</button>    
        <br>
        <br>
    </div>
    
    
    
    
    <table id="myTable" class="table table-striped table-bordered table-hover table-responsive text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th onclick="sortTable(0)">NOME DO ARQUIVO</th>
            <th onclick="sortTable(1)">NOME</th>
            <th onclick="sortTable(2, 'date')">DATA UPLOAD</th>
            <th onclick="sortTable(3, 'date')">DATA CRIAÇÃO</th>
            <th onclick="sortTable(4)">CATEGORIA</th>
            <th onclick="sortTable(5)">LICENÇA</th>
            <th>MINIATURA</th>
            <th>DETALHES</th>
            <th>SELECIONA</th>
        </tr>
    </thead>
    <tbody>
        <?php
        
                // Carrega e decodifica o JSON
        $categories = json_decode(file_get_contents('categories.json'), true);
        $_categories = file_get_contents('categories.json');

        ?>
        <script>
        let _categories = <?php echo $_categories; ?>
        </script>
        <?php


        // Função para buscar o nome da categoria pelo slug
        function getCategoryName($slug, $categories) {
            foreach ($categories as $category) {
                if ($category['slug'] === $slug) {
                    return $category['name'];
                }
            }
            return 'Categoria não encontrada'; // Retorno padrão caso o slug não exista
        }
        
        
        ?>
        <?php if (!empty($images)) : ?>
            <?php foreach (array_reverse($images) as $img) : ?>
            <tr imgId="<?php echo $img['id']; ?>" class="image-item">
                <td><?php echo !empty($img['custom_name']) ? $img['custom_name'] : 'N/A'; ?></td>
                <td class="nomearquivo"><?php echo $img['name']; ?></td>
                <td><?php echo $img['uploaded_at']; ?></td>
                <td><?php echo $img['created_at']; ?></td>

                <td>
                <?php 
                
                echo !empty($img['category']) 
                ? getCategoryName($img['category'], $categories) 
                : 'N/A';
                
                ?>
                
                </td>
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
                        <input class="select-checkbox" value="<?php echo $img['id'];?>" type="checkbox"/>
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
        if(key === 'path'
        || key === 'thumb_path'
        || key === 'height'
        || key === 'extra-image'){
            continue;
        }
        console.log(key, data[key])
        if(data[key] === null){
            data[key] = "Informação não disponível"
        }
        if (data.hasOwnProperty(key)) {
            const row = document.createElement('tr');

            // Cria a célula da chave
            const keyCell = document.createElement('td');
            const keyMap = {
                'id': 'Código ImgLock',
                'name': 'Nome da Imagem',
                'custom_name': 'Nome do Cadastro da Imagem',
                'description': 'Descrição da Imagem',
                'category': 'Categoria da Imagem',
                'link_ativo': 'Link de Publicação',
                'license': 'Tipo de Licenciamento da Imagem',
                'width': 'Largura e Altura da Imagem',
                'created_at': 'Data Criação da Imagem',
                'make': 'Fabricante da Câmera',
                'model': 'Modelo da Câmera',
                'dpi': 'DPI – Resolução da Imagem',
                'Software': 'Software da Edição da Imagem',
                'DateTime': 'Data da Edição da Imagem',
                'type': 'Formato da Imagem',
                'size': 'Tamanho do Arquivo da Imagem',
                'uploaded_at': 'Data do Cadastro da Imagem Imglock'
            };

            let newKey = keyMap[key] || key;

            
            keyCell.innerHTML = `<strong>${newKey}</strong>`;
            if(key === 'category'){
                let tmpcat = _categories.filter(e => {
                    if(e.slug == [data[key]]){
                        return true;
                    } 
                })
                data[key] = tmpcat[0].name;
            }
            row.appendChild(keyCell);

            // Cria a célula de valor
            const valueCell = document.createElement('td');
            if (Array.isArray(data[key]) || typeof data[key] === 'object') {
                // Se o valor é um array ou objeto, chama a função recursivamente
                
                valueCell.appendChild(renderTable(data[key]));
            } else {
                let tc = '';
                if(data[key] == null || data[key].length === 0){
                    tc = "Informação não disponível"
                } else {
                    tc = data[key]
                    if(key === 'width'){
                        tc = data['width'] + ' * ' + data['height'] ;
                    }
                }
                valueCell.textContent = tc;
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
    
    displayImg.innerHTML = `<a class="link-image" href="${img['path']}" target="_blank"><img class="imgDetalhes" src='${img['thumb_path']}'/> Ver Imagem</a><br/>`;
    
    // console.log(img['extra-image'])
    if(img['extra-image'] ){

        displayImg.innerHTML += `<a class="doc-file" href='${img['extra-image']}' target="_blank"><i class="fa-solid fa-folder-open"></i> <br>Arquivo de Licença</a><br/>`;
    }
    
    displayImg.innerHTML += "<h2>Informações Cadastrais da Imagem</h2>";
    



    let ordemDesejada = [
        'custom_name',
        'name',
        'id',
        'uploaded_at',
        'created_at',
        'type',
        'size',
        'width',
        'height',
        'dpi',
        'category',
        'description',
        'license',
        'link_ativo',
        'Software',
        'DateTime',
        'make',
        'model',
        'GPSLatitude',
        'GPSLongitude'
    ];



    let dadosCadastrais = Object.fromEntries(
    Object.entries(img)
        .filter(([key, value]) => key !== 'exif') // Filtra os itens
        .filter(([key, value]) => key !== 'xmp')
        .sort(([keyA], [keyB]) => ordemDesejada.indexOf(keyA) - ordemDesejada.indexOf(keyB)) // Ordena pela ordem desejada
);





   
    displayImg.append(renderTable(dadosCadastrais));

    displayImg.innerHTML += "<h2><br>Metadados do Arquivo - Exif<br></h2>";

    displayImg.append(renderTable(img["exif"]));
    displayImg.innerHTML += "<h2><br>Metadados do Arquivo - XMP<br></h2>";

    displayImg.append(renderTable(img["xmp"]));
    
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

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.select-checkbox');
    const actionButtons = document.querySelectorAll('.btn-action');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            // Verifica se algum checkbox está selecionado
            const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            
            // Habilita ou desabilita todos os botões com a classe 'btn-action'
            actionButtons.forEach(button => {
                button.disabled = !anyChecked;
            });
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.select-checkbox');
    const actionButtons = document.querySelectorAll('.btn-action');
    const deleteButton = document.querySelector('.btn-del');
    const downloadButton = document.querySelector('.btn-download');
    const generateButton = document.querySelector('.btn-generate'); // Novo botão

    // Função para coletar IDs selecionados
    function getSelectedIds() {
        return Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
    }

    // Função para atualizar o estado dos botões
    function updateButtonState() {
        const anyChecked = getSelectedIds().length > 0;
        actionButtons.forEach(button => {
            button.disabled = !anyChecked;
        });
    }

    // Função para enviar o formulário com os IDs selecionados
    function submitForm(action, dataKey) {
        const selectedIds = getSelectedIds();

        if (selectedIds.length > 0) {
            // Exibe uma caixa de confirmação se a ação for "excluir"
            if (action === 'del.php') {
                const confirmation = confirm('Tem certeza que deseja excluir os itens selecionados?');
                if (!confirmation) {
                    return; // Cancela a ação se o usuário não confirmar
                }
            }

            // Cria um formulário temporário para envio via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;

            // Adiciona o array de IDs como um campo hidden
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = dataKey;
            input.value = JSON.stringify(selectedIds); // Converte o array em uma string JSON

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        } else {
            alert('Selecione pelo menos um item para executar esta ação.');
        }
    }

    // Habilita ou desabilita botões ao alterar seleção dos checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateButtonState);
    });

    // Event listener para o botão de exclusão
    deleteButton.addEventListener('click', function () {
        submitForm('del.php', 'delete');
    });

    // Event listener para o botão de download
    downloadButton.addEventListener('click', function () {
        submitForm('download.php', 'download');
    });

    // Event listener para o botão de gerar PDF
    generateButton.addEventListener('click', function () {
        submitForm('generate.php', 'generate'); // Envia IDs para o PHP externo que gera o PDF
    });
});



</script>
<?php include 'footer.php';?>
</html>