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
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Etapa</th>
                        <th>Nome ou Referência</th>
                        <th>Link Contestado</th>
                        <th>Imagem Contestada</th>
                        <th>Data de Criação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processos as $processo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($processo['id']); ?></td>
                            <td><?php echo htmlspecialchars($processo['etapa']); ?></td>
                            <td><?php echo htmlspecialchars($processo['refer_name']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($processo['refer_link']); ?>" target="_blank">Visualizar</a></td>
                            <td><a href="<?php echo htmlspecialchars($processo['image']); ?>" target="_blank">Visualizar</a></td>
                            <td><?php echo htmlspecialchars($processo['timestamp']); ?></td>
                            <td style="display:grid">
                                <a href="visualizar_processo.php?pa_id=<?php echo urlencode($processo['id']); ?>&pa_key=<?php echo urlencode($processo['pa_key']); ?>" class="btn btn-info">Ver Processo</a>
                                
                                <?php if ($processo['etapa'] == 7): ?>
                                    <a href="excluir_processo.php?pa_id=<?php echo urlencode($processo['id']); ?>" 
                                        class="btn btn-danger"
                                        onclick="return confirm('Tem certeza que deseja excluir este processo?')">
                                        Excluir
                                        </a>
                                <?php else: ?>
                                    <button class="btn btn-danger" disabled>Excluir</button>
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
    <?php include 'footer.php'; ?>
</body>
</html>
