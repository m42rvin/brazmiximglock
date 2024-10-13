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
        // Impede o comportamento padrão do segundo botão
document.getElementById("button_eye").addEventListener("click", function(event) {
    event.preventDefault(); // Previne a ação padrão do botão

    var passwordField = document.getElementById("password");
    if (passwordField.type === "password") {
        passwordField.type = "text"; // Mostra a senha
        document.getElementById("button_eye").innerHTML = '<i class="fa-solid fa-eye"></i>';
    } else {
        passwordField.type = "password"; // Oculta a senha
        document.getElementById("button_eye").innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    }
});

function checkUsername() {
    var username = document.getElementById("username").value;
    var errorMessage = document.getElementById("username-error");
    var successMessage = document.getElementById("username-success");
    
    var passwordField = document.getElementById("password");
    var roleField = document.getElementById("role");
    var emailField = document.getElementById("email");
    var submitButton = document.querySelector("input[type='submit']");

    if (username.length === 0) {
        errorMessage.style.display = "none";
        successMessage.style.display = "none";
        passwordField.disabled = false;
        roleField.disabled = false;
        submitButton.disabled = false;
    } else {
        errorMessage.style.display = "block";
        successMessage.style.display = "block";
    }

    fetch('check_username.php?username=' + encodeURIComponent(username))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                errorMessage.textContent = "Nome de usuário já está em uso!";
                successMessage.textContent = "";
                successMessage.style.display = "none";
                passwordField.disabled = true;
                roleField.disabled = true;
                submitButton.disabled = true;
                emailField.disabled = true;
            } else {
                errorMessage.style.display = "none";
                successMessage.textContent = "Nome de usuário disponível!";
                successMessage.style.display = "block";
                passwordField.disabled = false;
                roleField.disabled = false;
                submitButton.disabled = false;
                emailField.disabled = false;
            }
        })
        .catch(error => {
            console.error("Erro na verificação do nome de usuário:", error);
            errorMessage.textContent = "Erro ao verificar nome de usuário.";
        });
}

function validatePassword() {
    var password = document.getElementById("password").value;
    var errorMessage = document.getElementById("password-error");
    
    var hasUpperCase = /[A-Z]/.test(password);
    var hasLowerCase = /[a-z]/.test(password);
    var hasNumbers = /\d/.test(password);
    var hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (password.length < 8 || !hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChars) {
        errorMessage.textContent = "A senha deve ter no mínimo 8 caracteres, incluindo letra maiúscula, minúscula, número e caractere especial.";
        return false;
    } else {
        errorMessage.textContent = ""; // Limpa a mensagem de erro
        return true;
    }
}

function validateEmail() {
    var email = document.getElementById("email").value;
    var errorMessage = document.getElementById("email-error");

    if (email === '') {
        errorMessage.textContent = "Por favor, insira um e-mail válido.";
        return false;
    }

    fetch('check_email.php?email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                errorMessage.textContent = "Este e-mail já está em uso.";
                document.querySelector("input[type='submit']").disabled = true;
            } else {
                errorMessage.textContent = ""; // Limpa a mensagem de erro
                document.querySelector("input[type='submit']").disabled = false;
            }
        })
        .catch(error => {
            console.error("Erro ao verificar o e-mail:", error);
            errorMessage.textContent = "Erro ao verificar o e-mail.";
        });
}

function validateForm() {
    return validatePassword(); // Chama a validação de senha
}
</script>
</body>
</html>
