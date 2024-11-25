<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8'); // Garantir que a página use UTF-8

// Caminho do arquivo JSON
$jsonFile = 'categories.json';

// Função para carregar as categorias
function loadCategories($file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    return json_decode(file_get_contents($file), true);
}

// Função para salvar as categorias
function saveCategories($file, $categories) {
    $json = json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
$categories = loadCategories($jsonFile);

// Lógica de criação, edição e exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $name = $_POST['name'] ?? '';

    if ($action === 'create') {
        // Adicionar uma nova categoria
        if (!empty($slug) && !empty($name)) {
            $categories[] = ["slug" => $slug, "name" => $name];
            if (saveCategories($jsonFile, $categories)) {
                $_SESSION['message'] = "Categoria adicionada com sucesso!";
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
            foreach ($categories as &$category) {
                if ($category['slug'] === $oldSlug) {
                    $category['slug'] = $slug;
                    $category['name'] = $name;
                    break;
                }
            }
            if (saveCategories($jsonFile, $categories)) {
                $_SESSION['message'] = "Categoria atualizada com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao atualizar a categoria.";
            }
        } else {
            $_SESSION['message'] = "Todos os campos são obrigatórios para edição.";
        }
    } elseif ($action === 'delete') {
        // Remover uma categoria
        if (!empty($slug)) {
            $categories = array_filter($categories, fn($category) => $category['slug'] !== $slug);
            if (saveCategories($jsonFile, $categories)) {
                $_SESSION['message'] = "Categoria excluída com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao excluir a categoria.";
            }
        } else {
            $_SESSION['message'] = "Slug é obrigatório para excluir.";
        }
    }

    header('Location: categories.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Categorias</title>
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
        <h1>Gerenciamento de Categorias</h1>
    </div>
    <!-- Exibir mensagens -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <div class="add-categoria">
    <h2>Adicionar Categoria</h2>
    <!-- Formulário para adicionar categorias -->
    <form method="POST" action="categories.php">
        <input type="hidden" name="action" value="create">
        <label for="slug">Slug:</label>
        <input type="text" id="slug" name="slug" required><br>
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" required><br>
        <button type="submit" class="btn btn-success add">Adicionar Categoria</button>
    </form>
    </div>
    <div class="edit-categoria">

   
    <h2>Categorias Existentes</h2>
    <table class="table table-striped table-bordered table-hover table-responsive text-center align-middle">
        <thead>
            <tr>
                <th>Slug</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['slug']) ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td>
                        <!-- Formulário para editar -->
                        <form method="POST" action="categories.php" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="old_slug" value="<?= htmlspecialchars($category['slug']) ?>">
                            <input type="text" name="slug" value="<?= htmlspecialchars($category['slug']) ?>" required>
                            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                            <button type="submit" class="btn btn-success">Editar</button>
                        </form>

                        <!-- Formulário para excluir -->
                        <form method="POST" action="categories.php" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($category['slug']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Deseja realmente excluir esta categoria?')">Excluir</button>
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
