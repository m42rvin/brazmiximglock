<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}
if ($_SESSION["role"] !== "admin") {
    header("Location: dashboard.php"); // Redirecionar para o dashboard se não for admin
    exit;
}

// Caminho para o arquivo JSON
$jsonFile = 'users.json';

// Função para ler o arquivo JSON
function readJsonFile($filename) {
    if (file_exists($filename)) {
        $jsonData = file_get_contents($filename);
        return json_decode($jsonData, true);
    } else {
        return [];
    }
}

// Função para salvar dados no arquivo JSON
function saveJsonFile($filename, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $jsonData);
}

// Função para gerar hash de senha
function hashPassword($password) {
    return hash('sha256', $password);
}

// Função para gerar o próximo ID único
function generateNextId($users) {
    $maxId = 0;
    foreach ($users as $user) {
        if ($user['id'] > $maxId) {
            $maxId = $user['id'];
        }
    }
    return $maxId + 1;
}

// Função para deletar usuário
function deleteUser($id, $users) {
    foreach ($users as $key => $user) {
        if ($user['id'] == $id) {
            unset($users[$key]); // Remove o usuário
            return $users; // Retorna a lista atualizada
        }
    }
    return $users;
}

// Verifica se a requisição é para deletar um usuário
if (isset($_GET['delete'])) {
    $idToDelete = $_GET['delete'];
    
    // Impedir que o usuário exclua a si próprio
    if ($idToDelete == $_SESSION['id']) {
        echo "Você não pode excluir seu próprio usuário!";
    } else {
        $users = readJsonFile($jsonFile);
        $users = deleteUser($idToDelete, $users);
        saveJsonFile($jsonFile, $users);
        header("Location: create_user.php"); // Redireciona após deletar para atualizar a lista
        exit;
    }
}

// Verifica se o formulário foi enviado para criar um novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Lê os dados existentes do arquivo JSON
    $users = readJsonFile($jsonFile);

    // Verifica se o nome de usuário já está em uso
    $userExists = false;
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $userExists = true;
            break;
        }
    }

    if ($userExists) {
        echo "Nome de usuário já está em uso. Escolha outro.";
    } elseif (empty($username) || empty($password) || empty($role)) {
        echo "Por favor, preencha todos os campos.";
    } else {
        // Gera um novo ID único, baseado no maior ID existente
        $newId = generateNextId($users);

        // Cria um novo usuário
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => hashPassword($password), // Salva a senha com hash
            'role' => $role
        ];

        // Adiciona o novo usuário à lista
        $users[] = $newUser;

        // Salva a lista atualizada no arquivo JSON
        saveJsonFile($jsonFile, $users);

        echo "Usuário criado com sucesso!";
    }
}

// Exibe a lista de usuários
$users = readJsonFile($jsonFile);
?>

<!-- Formulário para criar um novo usuário -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <?php include "header.php"; ?>
    <style>
        .container {
            display: flex;
        }
        .container > div {
            padding: 15px;
        }
        #table-users {
            flex-grow: 2;
        }
        #table-users table {
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include "navbar.php"; ?>
    <div class="container">
        <div>
            <h2>Criar Usuário</h2>
            <form action="create_user.php" method="post" class="form-group">
                <label for="username">Nome de usuário:</label><br>
                <input type="text" id="username" name="username" class="form-text text-muted" onkeyup="checkUsername()">
                <span id="username-error" style="color: red;"></span>
                <span id="username-success" style="color: green;"></span>
                <br>

                <label for="password">Senha:</label><br>
                <input type="password" id="password" name="password" class="form-text text-muted"><br>

                <label for="role">Função (role):</label><br>
                <select class="form-control" id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select><br><br>

                <input class="btn btn-success" type="submit" value="Criar Usuário">
            </form>
        </div>
        <div id="table-users">
            <h2>Lista de Usuários</h2>
            <table class="table">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome de Usuário</th>
                    <th scope="col">Função</th>
                    <th scope="col">Ações</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td>
                        <?php if ($user['id'] == $_SESSION['id']): ?>
                            <button class="btn btn-dark" disabled>Não pode excluir</button>
                        <?php else: ?>
                            <a href="create_user.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja deletar este usuário?');">Deletar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php include "footer.php"; ?>

    <!-- Verificação do nome de usuário com JavaScript -->
    <script>
function checkUsername() {
    var username = document.getElementById("username").value;
    var errorMessage = document.getElementById("username-error");
    var successMessage = document.getElementById("username-success");
    
    // Obter referências aos campos de senha, função e botão de envio
    var passwordField = document.getElementById("password");
    var roleField = document.getElementById("role");
    var submitButton = document.querySelector("input[type='submit']");

    if (username.length === 0) {
        errorMessage.style.display = "none";
        successMessage.style.display = "none";

        // Habilitar campos e botão se o campo de usuário estiver vazio
        passwordField.disabled = false;
        roleField.disabled = false;
        submitButton.disabled = false;
    } else {
        errorMessage.style.display = "block";
        successMessage.style.display = "block";
    }

    // Faz uma requisição para o servidor para verificar o nome de usuário
    fetch('check_username.php?username=' + encodeURIComponent(username))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                errorMessage.textContent = "Nome de usuário já está em uso!";
                successMessage.textContent = "";
                successMessage.style.display = "none";

                // Desabilitar campos e botão se o nome de usuário já existir
                passwordField.disabled = true;
                roleField.disabled = true;
                submitButton.disabled = true;
            } else {
                errorMessage.textContent = "";
                errorMessage.style.display = "none";
                successMessage.textContent = "Nome de usuário disponível";

                // Habilitar campos e botão se o nome de usuário estiver disponível
                passwordField.disabled = false;
                roleField.disabled = false;
                submitButton.disabled = false;
            }
        })
        .catch(error => console.error('Erro:', error));
}
</script>

</body>
</html>
