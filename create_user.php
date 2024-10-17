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
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1'); 
        exit();
    }
}

// Verifica se o formulário foi enviado para criar um novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email']; // Captura o e-mail
    $role = $_POST['role'];

    // Lê os dados existentes do arquivo JSON
    $users = readJsonFile($jsonFile);

    // Verifica se o nome de usuário ou o e-mail já estão em uso
    $userExists = false;
    foreach ($users as $user) {
        if ($user['username'] === $username || $user['email'] === $email) {
            $userExists = true;
            break;
        }
    }

    if ($userExists) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1'); 
        exit();
    } elseif (empty($username) || empty($password) || empty($role) || empty($email)) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1'); 
        exit();
    } else {
        // Gera um novo ID único, baseado no maior ID existente
        $newId = generateNextId($users);

        // Cria um novo usuário
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => hashPassword($password), // Salva a senha com hash
            'email' => $email, // Adiciona o e-mail
            'role' => $role
        ];

        // Adiciona o novo usuário à lista
        $users[] = $newUser;

        // Salva a lista atualizada no arquivo JSON
        saveJsonFile($jsonFile, $users);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1'); 
        exit();
    }
}

// Exibe a lista de usuários
$users = readJsonFile($jsonFile);

// Caminho para o arquivo JSON
$jsonFile = 'users.json';

// Ler o conteúdo do arquivo JSON
$jsonData = file_get_contents($jsonFile);

// Converter o conteúdo JSON para um array associativo do PHP
$usersArray = json_decode($jsonData, true);

// Filtrar apenas username e email
$filteredUsers = [];
foreach ($usersArray as $user) {
    $filteredUsers[] = [
        'username' => $user['username'],
        'email' => $user['email']
    ];
}

// Converter o array filtrado para um JSON que será usado no JavaScript
$filteredUsersJson = json_encode($filteredUsers);
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
        #password-error {
            display:inline-block;
            width: 20vw;
        }
        #eye {
            margin:0;
            padding:0;
            margin-top: 4px;
        }
        #eye button {
            background: transparent;
            color: #333;
            border:none;
        }
    </style>
</head>
<body>
    <?php include "navbar.php"; ?>
    <div class="container">
        <div>
            <h2>Criar Usuário</h2>
            <form action="create_user.php" method="post" class="form-group" onsubmit="return validateForm()">
                <label for="username">Nome de usuário:</label><br>
                <div class="input-group">
                    <input required type="text" id="username" name="username" class="form-control form-text text-muted" onkeyup="checkUsername()">
                </div>
                <span id="username-error" style="color: red;"></span>
                <span id="username-success" style="color: green;"></span>
                <br>
                
                
                <label for="password">Senha:</label><br>
                <div class="input-group">
                    <input required type="password" id="password" name="password" onkeyup="validatePassword()" class="form-control form-text text-muted" >
                    <div class="input-group-append">
                        <span class="input-group-text" id="eye">
                            <button class="btn btn-info" id="button_eye">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <span id="password-error" style="color: red;"></span><br>


                <label for="email">E-mail:</label><br>
                <input required type="email" id="email" name="email" class="form-control form-text text-muted" onkeyup="validateEmail()">
                <span id="email-error" style="color: red;"></span>
                <br>

                <label for="role">Função (role):</label><br>
                <select required class="form-control" id="role" name="role">
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
                    <th scope="col">E-mail</th> <!-- Nova coluna de e-mail -->
                    <th scope="col">Função</th>
                    <th scope="col">Ações</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td> <!-- Exibir o e-mail -->
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

    <!-- Verificação do nome de usuário e e-mail com JavaScript -->
    <script>
        document.users = <?php echo $filteredUsersJson; ?>;
        console.log(document.users)
        // Impede o comportamento padrão do segundo botão
document.getElementById("button_eye").addEventListener("click", function(event) {
    event.preventDefault(); // Previne a ação padrão do botão

    var passwordField = document.getElementById("password");
    if (passwordField.type === "password") {
        passwordField.type = "text"; // Mostra a senha
        document.getElementById("button_eye").innerHTML = '<i class="fa-solid fa-eye"></i>';
    } else {
        passwordField.type = "password"; // Esconde a senha
        document.getElementById("button_eye").innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    }
});

function validatePassword() {
    var password = document.getElementById('password').value;
    var errorElement = document.getElementById('password-error');
    // Regex atualizado para incluir caractere especial
    var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!regex.test(password)) {
        errorElement.textContent = 'A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma letra minúscula, um número e um caractere especial (@$!%*?&).';
        return false;
    } else {
        errorElement.textContent = ''; // Limpa a mensagem de erro
        return true;
    }
}


function validateEmail() {
    var email = document.getElementById('email').value.trim();
    console.log(email)
    var emailErrorElement = document.getElementById('email-error');
    var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;


    if (!emailRegex.test(email)) {
        emailErrorElement.textContent = 'Por favor, insira um e-mail válido.';
        return false;
    } else {
        if(verifyExist('email', email)){
            emailErrorElement.textContent = 'E-mail em uso!';
            return false;
        } else {
            emailErrorElement.textContent = ''; // Limpa a mensagem de erro
            return true;
        }
    }
}

function checkUsername() {
    var username = document.getElementById('username').value;
    var usernameErrorElement = document.getElementById('username-error');
    var usernameSuccessElement = document.getElementById('username-success');

    if (username.length < 3) {
        usernameErrorElement.textContent = 'O nome de usuário deve ter pelo menos 3 caracteres.';
        usernameSuccessElement.textContent = '';
        return false;
    } else {
        usernameErrorElement.textContent = '';
        usernameSuccessElement.textContent = 'Nome de usuário válido.';
        return true;
    }
}

function verifyExist(type, value){
    try {
        for(var i = 0; document.users.length; i++){
            if(document.users[i][type] === value){
                return true;
            }
        }
    } catch(e){
        return false
    }
    return false;
}

// Função para validar o formulário antes de enviar
function validateForm() {
    return validatePassword() && validateEmail() && checkUsername();
}
    </script>
</body>
</html>
