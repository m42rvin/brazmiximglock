<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

// Caminho para o arquivo JSON
$json_file = 'processos_auditoria.json';

// Verificar se o arquivo JSON existe
$processos = [];
if (file_exists($json_file)) {
    $processos = json_decode(file_get_contents($json_file), true) ?? [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processos Auditoria</title>
    <style>
        html, body {
            background-image: url('./logo\ imglock.jpeg');
            background-size: cover;
        }
        .btn-pa {
            position: absolute;
            top: 70px;
            transform: translateX(-50%);
            left: 50%;
        }
        table {
            width: 80vw;
            margin: 10vh auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f39c12;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn-voltar {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #f39c12;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-voltar:hover {
            background-color: #e67e22;
        }
    </style>
    <?php include 'header.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <a href="/pa.php" class="btn-pa btn btn-warning"><i class="fa-solid fa-pen-to-square"></i> Novo Processo</a>
    <div class="table-container">
        <?php if (!empty($processos)): ?>
            <table id="myTable">
                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th onclick="sortTable(1)">ETAPA</th>
                        <th onclick="sortTable(2)">NOME OU REFERÊNCIA</th>
                        <th>LINK CONTESTADO</th>
                        <th>IMAGEM CONTESTADA</th>
                        <th onclick="sortTable(5, 'date')">DATA DE CRIAÇÃO</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                
                <tbody>
                <colgroup>
                    <col></col>
                    <col></col>
                    <col style="width: 300px;"></col>
                    <col></col>
                    <col></col>
                    <col style="width:350px;"></col>
                    <col></col>
                </colgroup>
                    <?php foreach ($processos as $processo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($processo['id']); ?></td>
                            <td><?php echo htmlspecialchars($processo['etapa']); ?></td>
                            <td><?php echo htmlspecialchars($processo['refer_name']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($processo['refer_link']); ?>" target="_blank">Visualizar</a></td>
                            <td><a href="<?php echo htmlspecialchars($processo['image']); ?>" target="_blank">Visualizar</a></td>
                            <td><?php echo htmlspecialchars($processo['timestamp']); ?></td>
                            <td style="display:flex">
                                <a href="visualizar_processo.php?pa_id=<?php echo urlencode($processo['id']); ?>&pa_key=<?php echo urlencode($processo['pa_key']); ?>" class="btn btn-info"><i class="fa-solid fa-eye"></i></a>
                                
                                <?php if ($processo['etapa'] == 4): ?>
                                    <a href="excluir_processo.php?pa_id=<?php echo urlencode($processo['id']); ?>" 
                                        class="btn btn-danger"
                                        onclick="return confirm('Tem certeza que deseja excluir este processo?')">
                                        <i class="fa-solid fa-trash"></i>
                                        </a>
                                <?php else: ?>
                                    <button class="btn btn-danger" disabled><i class="fa-solid fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum processo encontrado.</p>
        <?php endif; ?>
    </div>
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
    <?php include 'footer.php'; ?>
</body>
</html>
