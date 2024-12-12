<?php
// Iniciar sessão
session_start();



// Caminho para o arquivo JSON
$json_file = 'processos_auditoria.json';

// Verificar se os parâmetros pa_id e pa_key foram passados via GET
if (!isset($_GET['pa_id']) || !isset($_GET['pa_key'])) {
    header("Location: processos_auditoria.php");
    exit;
}

$pa_id = $_GET['pa_id'];
$pa_key = $_GET['pa_key'];

// Verificar se o arquivo JSON existe
if (!file_exists($json_file)) {
    header("Location: processos_auditoria.php");
    exit;
}

// Carregar os processos do arquivo JSON
$processos = json_decode(file_get_contents($json_file), true) ?? [];

// Buscar o processo com o ID passado
$processo = null;
foreach ($processos as $p) {
    if ($p['id'] === $pa_id) {
        $processo = $p;
        break;
    }
}
if($processo['etapa'] !== '1'){
    // Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php"); // Redirecionar para o login se não estiver logado
    exit;
}
}
// Verificar se o processo foi encontrado e se a chave corresponde
if ($processo === null || $processo['pa_key'] !== $pa_key) {
    header("Location: processos_auditoria.php");
    exit;
}

// Carregar o arquivo uploads.json
$uploads_file = 'uploads.json';
$uploads = json_decode(file_get_contents($uploads_file), true) ?? [];

// Buscar a imagem original com base no ID
$original_image_path = null;
foreach ($uploads as $upload) {
    if ($upload['id'] === $processo['original_image']) {
        $original_image_path = $upload['path'];
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualização do Processo - <?php echo htmlspecialchars($processo['id']); ?></title>
    <style>
        html, body {
            background-color: #DEE9FB !important;
            font-family: Arial, sans-serif;
        }
        .processo-visualizacao {
            width: 80vw;
            margin: 10vh auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .processo-visualizacao h5 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .processo-visualizacao p {
            margin: 10px 0;
        }
        .d-flex {
            display: flex;
            justify-content: center;
            gap: 40px;
            align-items: flex-start;
            margin-top: 20px;
        }
        .img-thumbnail {
            max-width: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
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
        .text-center img{
            width: 200px;
        }
        .text-center:hover {
            cursor:pointer;
        }
        .text-center:hover img {
            opacity: 0.5;
        }
        .text-center i {
            font-size: 90px;
            position: absolute;
            transform: translateX(-140px);
            margin-top: 25px;
            opacity:0;
            color: rgba(0, 0, 0, 0.6)
        }
        .text-center:hover i {
            opacity: 100;
        }
    </style>
    <?php include 'header.php'; ?>
</head>
<body>
    <?php (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) ? include 'navbar.php' : ''; ?>

    <?php if($processo['etapa'] == '1'){ ?>
    <div class="processo-visualizacao">
        <!-- Processo de Auditoria -->
        <h5 class="text-secondary mb-3">
            <strong>Processo de Auditoria:</strong> 
            <span class="text-dark"><?php echo htmlspecialchars($processo['id']); ?></span>
        </h5>

        <!-- Informações Gerais -->
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Etapa:</strong> <?php echo htmlspecialchars($processo['etapa']); ?></li>
            <li class="list-group-item"><strong>Nome ou Referência:</strong> <?php echo htmlspecialchars($processo['refer_name']); ?></li>
            <li class="list-group-item">
                <strong>Link contestado:</strong> 
                <a href="<?php echo htmlspecialchars($processo['refer_link']); ?>" target="_blank" class="text-decoration-none">
                    <?php echo htmlspecialchars($processo['refer_link']); ?>
                </a>
            </li>
        </ul>

        <!-- Imagens -->
        <div class="d-flex">
            <!-- Imagem Contestada -->
            <div class="text-center">
                <h5>Imagem Contestada</h5>
                <img src="<?php echo htmlspecialchars($processo['image']); ?>" 
                     alt="Imagem contestada" class="img-thumbnail">
                     <i class="fa-solid fa-magnifying-glass-plus"></i>
            </div>

            <!-- Imagem Original -->
            <div class="text-center">
                <h5>Imagem Original</h5>
                <?php if ($original_image_path): ?>
                    <img src="<?php echo htmlspecialchars($original_image_path); ?>" 
                         alt="Imagem original" class="img-thumbnail">
                         <i class="fa-solid fa-magnifying-glass-plus"></i>
                <?php else: ?>
                    <p class="text-danger">Imagem original não encontrada.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contatos Conhecidos -->
        <div class="mt-4">
            <h5><strong>Contatos Conhecidos</strong></h5>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($processo['known_contacts'])); ?></p>
        </div>

        <!-- Observações -->
        <div class="mt-4">
            <h5><strong>Observações sobre a Contestação</strong></h5>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($processo['observation'])); ?></p>
        </div>

        <!-- Data de Criação -->
        <div class="mt-4">
            <p><strong>Data de Criação:</strong> 
                <span style="color:white" class="badge bg-secondary"><?php echo htmlspecialchars($processo['timestamp']); ?></span>
            </p>
        </div>
        <?php if($processo['archived'] == true){ ?>
            <h3>Processo Arquivado</h3>
        <?php } ?>

        <!-- Botões -->
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <a href="processos_auditoria.php" class="btn-voltar">Voltar para a lista de processos</a>
        <?php endif; ?>
        <?php if($processo['archived'] === false){ ?>
            <a href="pa2.php?aprov=true&pa_id=<?php echo $processo['id']; ?>&pa_key=<?php echo $processo['pa_key']; ?>" class="btn btn-success">Aprovar processo</a>
            <a href="pa2.php?aprov=false&pa_id=<?php echo $processo['id']; ?>&pa_key=<?php echo $processo['pa_key']; ?>" class="btn btn-danger">Reprovar processo e Arquivar</a>
        <?php } ?>
    </div>
    <?php } elseif($processo['etapa'] == '2') { ?>
        <div class="processo-visualizacao">
        <div class="d-flex align-items-start">
        <h1>Comunicação Extra-Oficial</h1>
        </div>
        <div class="d-flex align-items-start">
            <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>#</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de uma linha com dados -->
                <tr>
                    <td>ID</td>
                    <td><?php echo $processo['id'];?></td>
                </tr>
                <tr>
                    <td>Abertura Processo</td>
                    <td><?php echo $processo['timestamp'];?></td>
                </tr>
                <tr>
                    <td>Aprovação Gestor</td>
                    <td><?php echo $processo['aprove_date'];?></td>

                </tr>
                <tr>
                    <td>Status Notificação</td>
                    <td>
                        <?php 
                            echo isset($processo['sinalizar_envio_data']) && !empty($processo['sinalizar_envio_data']) 
                                ? $processo['sinalizar_envio_data'] 
                                : 'Envio Pendente'; 
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
        <div class="d-flex align-items-start ">
            <div class="mb-3">
                <h5>Link para responder contestação:</h5>
                <input readonly class="resposta-link form-control" placeholder="Digite algo..." />
            </div>
            <div>
                <h5>Chave de acesso:</h5>
                <?php 
                function gerarChaveAleatoria($tamanho = 5) {
                    // Gera uma sequência de letras maiúsculas
                    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                
                    // Embaralha os caracteres e retorna os primeiros $tamanho caracteres
                    return substr(str_shuffle($caracteres), 0, $tamanho);
                }
                
                $chave = gerarChaveAleatoria();
                
                
                ?>
                <input readonly class="outra-input form-control" value="<?php echo $chave;?>" placeholder="Digite aqui..." />
            </div>
        </div>
        <form method="POST" action="salva_processo.php">
    <div class="d-flex align-items-center">
        <!-- Botão -->
        <button type="button" class="btn btn-primary gera-processo" id_processo="<?php echo $processo['id']; ?>">
            <i class="fa-solid fa-file-export"></i> Gerar PDF com Comunicado
        </button>

        <!-- Checkbox: Sinalizar Notificação -->
        <div class="form-check ms-3">
            <input 
                class="form-check-input" 
                type="checkbox" 
                id="sinalizar_notificacao" 
                name="sinalizar_notificacao"
                <?php echo isset($processo['sinalizar_notificacao']) && $processo['sinalizar_notificacao'] ? 'checked' : ''; ?>
            >
            <label class="form-check-label" for="sinalizar_notificacao">
                Sinalizar envio de notificação
            </label>
        </div>
    </div>

    <div class="d-flex align-items-center">
        <!-- Checkbox: Seguir Próxima Etapa -->
        <div class="form-check ms-3">
            <input 
                class="form-check-input" 
                type="checkbox" 
                id="seguir_proxima_etapa" 
                name="seguir_proxima_etapa"
                <?php echo isset($processo['seguir_proxima_etapa']) && $processo['seguir_proxima_etapa'] ? 'checked' : ''; ?>
            >
            <label class="form-check-label" for="seguir_proxima_etapa">
                Seguir para próxima etapa
            </label>
        </div>

        <!-- Checkbox: Finalizar e Arquivar -->
        <div class="form-check ms-3">
            <input 
                class="form-check-input" 
                type="checkbox" 
                id="finalizar_arquivar" 
                name="finalizar_arquivar"
                <?php echo isset($processo['finalizar_arquivar']) && $processo['finalizar_arquivar'] ? 'checked' : ''; ?>
            >
            <label class="form-check-label" for="finalizar_arquivar">
                Finalizar e Arquivar
            </label>
        </div>
    </div>

    <div class="d-flex align-items-center">
        <!-- Botão Salvar -->
        <button type="submit" class="btn btn-success" name="id_processo" value="<?php echo $processo['id']; ?>">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
    </div>
</form>


        </div>
    <?php } ?>        
    <?php include 'footer.php'; ?>

    <script>
     document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search); // Pega a string de consulta
        const paId = urlParams.get('pa_id');
        // Pega a URL base do navegador
        const urlBase = window.location.origin + '/processo.php?pa_id=' + paId; 

        // Seleciona o input e seta o valor com a URL base
        document.querySelector('.resposta-link').value = urlBase;

        // Seleciona todos os elementos com a classe .text-center
        const containers = document.querySelectorAll('.text-center');

        containers.forEach(function (container) {
            container.addEventListener('click', function (event) {
                // Procura a imagem <img> dentro do elemento clicado
                const img = container.querySelector('img');
                if (img) {
                    const imgSrc = img.getAttribute('src'); // Obtém o atributo src da imagem
                    window.open(imgSrc, '_blank'); // Abre a imagem em uma nova aba
                }
            });
        });

        const botoes = document.querySelectorAll('.gera-processo');

    botoes.forEach(botao => {
        botao.addEventListener('click', function() {
            // Obtém o ID do processo do atributo 'id_processo'
            const processoId = this.getAttribute('id_processo');
            
            if (!processoId) {
                alert('ID do processo não encontrado!');
                return;
            }

            // Configura os parâmetros GET
            const url = `/gera_pdf_extra_oficial.php?pa_url=` + document.querySelector('.resposta-link').value +`&pa_id=${processoId}&chave=<?php echo $chave;?>`;

            // Realiza a requisição AJAX
            fetch(url, {
                method: 'GET',
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao gerar PDF');
                }
                return response.blob(); // Converte a resposta em um blob (PDF)
            })
            .then(blob => {
                // Cria um link temporário para download do PDF
                const urlBlob = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = urlBlob;
                link.download = `comunicado_processo_${processoId}.pdf`;

                // Simula o clique no link para fazer o download
                link.click();

                // Libera o objeto URL
                window.URL.revokeObjectURL(urlBlob);

            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Houve um problema ao gerar o PDF.');
            });
        });
    });

    });
</script>
</body>
</html>
