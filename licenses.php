<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}
if ($_SESSION["role"] !== "admin") {
    header("Location: upload.php"); // Redirecionar para o dashboard se não for admin
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8'); // Garantir que a página use UTF-8

// Caminho do arquivo JSON
$jsonFile = 'licenses.json';

// Função para carregar as categorias
function loadCategories($file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    return json_decode(file_get_contents($file), true);
}

// Função para salvar as categorias
function saveLicenses($file, $licenses) {
    $json = json_encode($licenses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        error_log("Erro ao codificar JSON: " . json_last_error_msg());
        return false;
    }
    if (file_put_contents($file, $json) === false) {
        error_log("Erro ao escrever no arquivo JSON: {$file}");
        return false;
    }
    return true;
}

// Carrega as categorias existentes
$licenses = loadCategories($jsonFile);

// Lógica de criação, edição e exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $name = $_POST['name'] ?? '';

    if ($action === 'create') {
        // Adicionar uma nova categoria
        if (!empty($slug) && !empty($name)) {
            $licenses[] = ["slug" => $slug, "name" => $name];
            if (saveLicenses($jsonFile, $licenses)) {
                $_SESSION['message'] = "Licença adicionada com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao salvar a categoria.";
            }
        } else {
            $_SESSION['message'] = "Slug e Nome são obrigatórios.";
        }
    } elseif ($action === 'update') {
        // Atualizar uma categoria existente
        $oldSlug = $_POST['old_slug'] ?? '';
        if (!empty($oldSlug) && !empty($slug) && !empty($name)) {
            foreach ($licenses as &$license) {
                if ($license['slug'] === $oldSlug) {
                    $license['slug'] = $slug;
                    $license['name'] = $name;
                    break;
                }
            }
            if (saveLicenses($jsonFile, $licenses)) {
                $_SESSION['message'] = "Licença atualizada com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao atualizar a Licença.";
            }
        } else {
            $_SESSION['message'] = "Todos os campos são obrigatórios para edição.";
        }
    } elseif ($action === 'delete') {
        // Remover uma categoria
        if (!empty($slug)) {
            $licenses = array_filter($licenses, fn($license) => $license['slug'] !== $slug);
            if (saveLicenses($jsonFile, $licenses)) {
                $_SESSION['message'] = "Licença excluída com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao excluir a Licença.";
            }
        } else {
            $_SESSION['message'] = "Slug é obrigatório para excluir.";
        }
    }

    header('Location: licenses.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Licenças</title>
    <style>
        table { width: 80vw; border-collapse: collapse; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        .message { margin: 10px 0; padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        label {
            width:60px;
        }
        .add {
            margin-left: 60px;
        }
        .add-categoria, .edit-categoria {
            display: inline-grid;
        }
        .edit-categoria {
            margin-left: 20px;
            border-let: 1px solid gray;
            width: 70vw;
        }
    </style>
    <?php include "header.php"; ?>
</head>
<body>
<?php include "navbar.php"; ?>
    <div class="container-fluid">
    <div class="jumbotron">
        <h1>Gerenciamento de Licenças</h1>
    </div>
    <!-- Exibir mensagens -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <div class="add-categoria">
    <h2>Adicionar Licença</h2>
    <!-- Formulário para adicionar categorias -->
    <form method="POST" action="licenses.php">
        <input type="hidden" name="action" value="create">
        <label for="slug">Slug:</label>
        <input type="text" id="slug" name="slug" required><br>
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" required><br>
        <button type="submit" class="btn btn-success add"><i class="fa-solid fa-plus"></i> Adicionar Categoria</button>
    </form>
    </div>
    <div class="edit-categoria">

   
    <h2>Licenças Existentes</h2>
    <table class="table table-striped table-bordered table-hover table-responsive text-center align-middle">
        <thead>
            <tr>
                <th>Slug</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($licenses as $license): ?>
                <tr>
                    <td><?= htmlspecialchars($license['slug']) ?></td>
                    <td><?= htmlspecialchars($license['name']) ?></td>
                    <td>
                        <!-- Formulário para editar -->
                        <form method="POST" action="licenses.php" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="old_slug" value="<?= htmlspecialchars($license['slug']) ?>">
                            <input type="text" name="slug" value="<?= htmlspecialchars($license['slug']) ?>" required>
                            <input type="text" name="name" value="<?= htmlspecialchars($license['name']) ?>" required>
                            <button type="submit" class="btn btn-success"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                        </form>

                        <!-- Formulário para excluir -->
                        <form method="POST" action="licenses.php" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($license['slug']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Deseja realmente excluir esta categoria?')"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>
</body>
<?php include "footer.php"; ?>
</html>
