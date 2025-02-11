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
        .ver-resumo {
            position: fixed;
            top: 100px;
            right: 100px;
            cursor:pointer;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;   
        }
    </style>
    <?php include 'header.php'; ?>
</head>
<body>
    <?php (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) ? include 'navbar.php' : ''; ?>

    <?php if($processo['etapa'] == '1'){ ?>
        <h1>ETAPA 1 – APROVAÇÃO INTERNA</h1>
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
                <a href="<?php echo htmlspecialchars($processo['image']); ?>" target="_blank">
                <img src="<?php echo htmlspecialchars($processo['image']); ?>" 
                     alt="Imagem contestada" class="img-thumbnail">
                     <i class="fa-solid fa-magnifying-glass-plus"></i>
                </a>
            </div>

            <!-- Imagem Original -->
            <div class="text-center">
                <h5>Imagem Original</h5>
                <?php if ($original_image_path): ?>
                    <a target="_blank" href="<?php echo htmlspecialchars($original_image_path); ?>">
                    <img src="<?php echo htmlspecialchars($original_image_path); ?>" 
                         alt="Imagem original" class="img-thumbnail">
                         <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </a>
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

        <!-- Botões -->
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <!-- Botão para copiar a URL -->
        <button id="copy-url-btn" class="btn btn-primary" data-clipboard-text="">
            Copiar URL Atual
        </button>

        <!-- Mensagem de confirmação -->
        <div id="copy-message" style="display: none; color: green; margin-top: 10px;">
            URL copiada com sucesso!
        </div>
        <br>
            
            <a href="processos_auditoria.php" class="btn-voltar">Voltar para a lista de processos</a>
        <!-- Botao de copiar url -->

        

<!-- Script para funcionalidade -->
<script>
    // Configura o botão para copiar a URL atual
    document.addEventListener("DOMContentLoaded", function () {
        const button = document.getElementById('copy-url-btn');
        button.setAttribute('data-clipboard-text', window.location.href);

        const clipboard = new ClipboardJS('#copy-url-btn');

        clipboard.on('success', function () {
            // Exibe mensagem de confirmação
            const message = document.getElementById('copy-message');
            message.style.display = 'block';
            setTimeout(() => {
                message.style.display = 'none';
            }, 2000); // Oculta a mensagem após 2 segundos
        });

        clipboard.on('error', function () {
            alert("Falha ao copiar a URL. Tente novamente.");
        });
    });
</script>





        <?php endif; ?>
        <?php if($processo['archived'] === false){ ?>
            <a href="pa2.php?aprov=true&pa_id=<?php echo $processo['id']; ?>&pa_key=<?php echo $processo['pa_key']; ?>" class="btn btn-success">Aprovar processo</a>
            <a href="#" class="btn btn-danger" onclick="reprovarProcesso(event, '<?php echo $processo['id']; ?>', '<?php echo $processo['pa_key']; ?>')">
    Reprovar processo e Arquivar
</a>

    <script>
    function reprovarProcesso(event, pa_id, pa_key) {
        event.preventDefault(); // Impede o redirecionamento imediato

        var motivo = prompt("Digite o motivo da reprovação:");

        if (motivo !== null && motivo.trim() !== "") {
            // Redireciona para o link com o motivo adicionado como parâmetro
            window.location.href = `pa2.php?aprov=false&pa_id=${pa_id}&pa_key=${pa_key}&motivo=${encodeURIComponent(motivo)}`;
        } else {
            alert("É necessário informar um motivo para reprovar o processo.");
        }
    }
    </script>
        <?php } ?>
    </div>
    <?php } elseif($processo['etapa'] == '2') { ?>
        <h1>ETAPA 2 – COMUNICAÇÃO EXTERNA</h1>
        <div class="processo-visualizacao">
        <div class="d-flex align-items-start">
        </div>
        <div class="d-flex align-items-start">
        <?php
// Carrega o arquivo JSON
$json_file = 'processos_auditoria.json';
$auditoria_data = json_decode(file_get_contents($json_file), true);

// Obtém o ID atual
$processo_id = $processo['id'] ?? '';

// Busca os dados correspondentes ao ID
$processo_encontrado = null;
foreach ($auditoria_data as $processo) {
    if ($processo['id'] === $processo_id) {
        $processo_encontrado = $processo;
        break;
    }
}

// Se encontrou os dados, exibe a tabela
if ($processo_encontrado): ?>
    <table class="table table-bordered text-center" style="width:100%" border="1">
        <tr>
            <th>Nome Referência</th>
            <td><?php echo htmlspecialchars($processo_encontrado['refer_name']); ?></td>
        </tr>
        <tr>
            <th>Link Referência</th>
            <td><a href="<?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>" target="_blank"><?php echo $processo_encontrado['refer_link'];?></a></td>
        </tr>
        <tr>
            <th>Contatos Conhecidos</th>
            <td><?php echo htmlspecialchars($processo_encontrado['known_contacts']); ?></td>
        </tr>
        <tr>
            <th>Observação</th>
            <td><?php echo htmlspecialchars($processo_encontrado['observation']); ?></td>
        </tr>
        <tr>
            <th>Data e Hora</th>
            <td><?php echo htmlspecialchars($processo_encontrado['timestamp']); ?></td>
        </tr>
    </table>
</div>
<div class="d-flex align-items-start">
        
    <br />
<?php else: ?>
    <p>Erro: Processo não encontrado.</p>
<?php endif; ?>

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
                <?php
                    if(isset($processo['sinalizar_envio_data'])){
                        ?>
                            <tr>
                                <td>Prazo Resposta</td>
                                <td>
                        <?php
                        if(isset($processo['resposta_processo']) && $processo['resposta_processo'] === true ){
                           echo 'Respondido dentro do prazo.';
                        } else {
                            
                        // Data de sinalização de envio (formato: YYYY-MM-DD)
                        $sinalizarEnvioData = $processo['sinalizar_envio_data'];

                        // Converter a data para um objeto DateTime
                        $dataInicial = new DateTime($sinalizarEnvioData);

                        // Adicionar 15 dias à data inicial
                        $dataFinal = clone $dataInicial;
                        $dataFinal->modify('+15 days');

                        // Obter a data atual
                        $dataAtual = new DateTime();

                        // Calcular a diferença entre a data final e a data atual
                        $diferenca = $dataAtual->diff($dataFinal);

                        // Verificar se o prazo já expirou
                        if ($dataAtual > $dataFinal) {
                            echo "O prazo de 15 dias já expirou.";
                        } else {
                            // Exibir o contador regressivo
                            echo "Faltam " . $diferenca->days . " dias para esgotar o prazo de resposta deste processo.";
                        }
                    }

                        
                        
                        ?>

                                </td>
                            </tr>

                        <?php
                    }
                
                ?>
                <tr>
                    <td>Status da Resposta</td>
                    <td>
                        <?php 
                            echo isset($processo['resposta_processo']) && $processo['resposta_processo'] === true 
                                ? 'Respondido' 
                                : 'Aguardando Resposta';
                        ?>
                    </td>
                </tr>
            </tbody>
            </table>
                <?php
// Carregar o conteúdo do arquivo JSON de respostas
$respostasJson = 'resposta_processo.json';
$respostas = file_exists($respostasJson) ? json_decode(file_get_contents($respostasJson), true) : [];

// Inicializar variáveis de resposta
$respostaEncontrada = null;
foreach ($respostas as $resposta) {
    if ($resposta['id_processo'] === $processo['id']) {
        $respostaEncontrada = $resposta;
        break;
    }
}

// Mapear os textos de contestação
$contestacaoMensagens = [
    'concorda_remocao' => 'Confirmo que irei interromper o uso das imagens envolvidas nesse processo com o prazo de 7 dias.',
    'mais_informacoes' => 'Preciso de mais informações sobre o processo, solicito contato direto para melhor entendimento.',
    'nao_concordo' => 'Não concordo com os apontamentos realizados e manterei o uso das imagens mesmo assim.',
    'quero_vender' => 'Quero re-vender com autorização do uso de imagens da Brazmix.'
];
?>
</tbody>
</table>
        </div>
        <!-- Exibir a linha da tabela se a resposta existir -->
        <?php if ($respostaEncontrada) : ?>
    <div class="container">
    <table class="table table-bordered text-center" style="width: 100%; margin-left:0%">
    <tbody>
    <tr>
        <td>Data Resposta</td>
        <td><?php echo $respostaEncontrada['data_resposta']; ?></td>
    </tr>
    <tr>
        <td>Contestação</td>
        <td style="max-width: 200px; word-wrap: break-word;">
            <?php
                $contestacao = $respostaEncontrada['contestacao'];
                echo isset($contestacaoMensagens[$contestacao]) 
                    ? $contestacaoMensagens[$contestacao] 
                    : 'Resposta não reconhecida';
            ?>
        </td>
    </tr>
    <tr>
        <td>Texto Resposta</td>
        <td style="max-width: 200px; word-wrap: break-word;"><?php echo htmlspecialchars($respostaEncontrada['texto_resposta']); ?></td>
    </tr>
    <tr>
        <td>Email Contato</td>
        <td><?php echo $respostaEncontrada['email_resposta'];?></td>
    </tr>
    <tr>
        <td>Telefone Contato</td>
        <td><?php echo $respostaEncontrada['telefone_resposta'];?></td>
    </tr>
    </tbody>
    <table>
</div>
    <?php endif; ?>
        <div class="d-flex align-items-start ">
        <?php if (!isset($processo['resposta_processo']) || !$processo['resposta_processo']) : ?>
        <!-- Botão -->
        <button style="margin-top:45px" type="button" class="btn btn-primary gera-processo" id_processo="<?php echo $processo['id']; ?>">
            <i class="fa-solid fa-file-export"></i> Gerar PDF com Comunicado
        </button>
    <?php endif; ?>
        <?php if (!isset($processo['resposta_processo']) || !$processo['resposta_processo']) : ?>
            <div class="mb-3">
                <h5>Link para responder contestação:</h5>
                <input style="width:200px" readonly class="resposta-link form-control" placeholder="Digite algo..." />
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
                
                function obterOuGerarChave($processoId, $arquivo = 'keys.json') {
                    // Lê o arquivo keys.json
                    $conteudo = file_get_contents($arquivo);
                    $dados = json_decode($conteudo, true);
                
                    // Procura se o processo já tem uma chave associada
                    foreach ($dados as &$registro) {
                        if ($registro['id'] === $processoId) {
                            // Retorna a última chave associada a este ID, se existir
                            if (!empty($registro['keys'])) {
                                return end($registro['keys']); // Pega a última chave do array
                            }
                        }
                    }
                
                    // Se não encontrou o ID ou não há chave, cria uma nova
                    $novaChave = gerarChaveAleatoria();
                
                    // Adiciona a chave ao registro ou cria um novo registro para este ID
                    $chaveEncontrada = false;
                    foreach ($dados as &$registro) {
                        if ($registro['id'] === $processoId) {
                            $registro['keys'][] = $novaChave;
                            $chaveEncontrada = true;
                            break;
                        }
                    }
                    if (!$chaveEncontrada) {
                        $dados[] = [
                            'id' => $processoId,
                            'keys' => [$novaChave]
                        ];
                    }
                
                    // Salva as alterações no arquivo
                    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
                
                    return $novaChave;
                }
                
                // Exemplo de uso
                $processoId = $processo['id']; // ID do processo atual
                $chave = obterOuGerarChave($processoId);
                
                ?>
                <input readonly style="width:100px" class="outra-input form-control" value="<?php echo $chave;?>" placeholder="Digite aqui..." />
            </div>
            <?php endif; ?>
        </div>
        <form method="POST" action="salva_processo.php">
    <div class="align-items-center">
    

    <div class="check-items ">
    <input type="hidden" name="processo_id" value="<?php echo $processo['id']; ?>">
        <!-- Checkbox: Seguir Próxima Etapa -->
        <?php
        $desabilitar = !(isset($respostaEncontrada) || ($dataAtual > $dataFinal)) ? 'disabled' : '';
        ?>

        <button style="margin-left:100px" type="submit" name="seguir_proxima_etapa" value="1" class="btn btn-primary" <?php echo $desabilitar; ?>>
            Seguir para próxima etapa
        </button>

        <button type="submit" name="finalizar_arquivar" value="1" class="btn btn-danger" <?php echo $desabilitar; ?> onclick="return confirmarReprovacao(this)">
            Finalizar e Arquivar
        </button>

<input type="hidden" name="motivo_reprovacao" id="motivo_reprovacao">

<script>
function confirmarReprovacao(botao) {
    if (!botao.disabled) { // Verifica se o botão está habilitado
        var motivo = prompt("Digite o motivo da finalização:");

        if (motivo !== null && motivo.trim() !== "") {
            document.getElementById("motivo_reprovacao").value = motivo;
            return true; // Permite o envio do formulário
        } else {
            alert("É necessário informar um motivo para reprovar o processo.");
            return false; // Impede o envio do formulário
        }
    }
}
</script>


        
    </div>
        <!-- Checkbox: Sinalizar Notificação -->
        <div class="form-check ms-3">
        <?php if (!isset($processo['resposta_processo']) || !$processo['resposta_processo']) : ?>
            <br/>
            <input 
            class="form-check-input" 
            type="checkbox" 
            id="sinalizar_notificacao" 
            name="sinalizar_notificacao"
            style="margin-left:100px;transform:translateY(5px)" 
            <?php 
                echo isset($processo['sinalizar_notificacao']) && $processo['sinalizar_notificacao'] ? 'checked disabled' : ''; 
            ?>
        >
        <label style="margin-left:130px" class="form-check-label" for="sinalizar_notificacao">
            Sinalizar envio de notificação
        </label>
        <button style="margin-left: 20px" type="submit" class="btn btn-success" name="id_processo" value="<?php echo $processo['id']; ?>">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
        <br/><br/><br/>
        <?php endif; ?>
        </div>
    </div>

    

    <div class="d-flex align-items-center">
        <!-- Botão Salvar -->
    </div>
</form>


        </div>
    <?php } elseif ($processo['etapa'] === "3") { ?>

<h1>ETAPA 3 – ENCAMINHAMENTO JURÍDICO</h1>

        <?php
// Carregar o conteúdo do arquivo JSON
$processos = json_decode(file_get_contents('processos_auditoria.json'), true);

// Obter o ID do processo da URL
$processo_id = $_GET['pa_id'] ?? null;
$processo = null;

// Encontrar o processo correspondente no JSON
if ($processo_id) {
    foreach ($processos as $p) {
        if ($p['id'] === $processo_id) {
            $processo = $p;
            break;
        }
    }
}

// Valores padrão para os campos
$analise_juridica = $processo['descricao_analise_juridica'] ?? '';
$comunicacao_extra = $processo['descricao_comunicacao_extra'] ?? '';
$processo_juridico = $processo['descricao_processo_juridico'] ?? '';

$json_file = 'processos_auditoria.json';
$processos = [];

if (file_exists($json_file) && filesize($json_file) > 0) {
    $processos = json_decode(file_get_contents($json_file), true);
}

// Função para exibir os arquivos de uma etapa específica
function listarArquivos($processos, $processo_id, $etapa)
{
    echo "<ul class='list-group'>";
    foreach ($processos as $processo) {
        if (isset($processo["arquivo_{$etapa}"]) && $processo['id'] == $processo_id) {
            $arquivo = $processo["arquivo_{$etapa}"];
            $nome_arquivo = basename($arquivo);
            echo "<li class='list-group-item'>";
            echo "<a href='{$arquivo}' target='_blank'>{$nome_arquivo}</a>";
            echo "</li>";
        }
    }
    echo "</ul>";
}


?>
<?php
// Carrega o arquivo JSON
$json_file = 'processos_auditoria.json';
$auditoria_data = json_decode(file_get_contents($json_file), true);

// Obtém o ID atual
$processo_id = $processo['id'] ?? '';

// Busca os dados correspondentes ao ID
$processo_encontrado = null;
foreach ($auditoria_data as $processo) {
    if ($processo['id'] === $processo_id) {
        $processo_encontrado = $processo;
        break;
    }
}

// Se encontrou os dados, exibe a tabela
if ($processo_encontrado): ?>
    <table class="table table-bordered text-center" style="width:60%; margin-left:20%" border="1">
        <tr>
            <th>Nome Referência</th>
            <td><?php echo htmlspecialchars($processo_encontrado['refer_name']); ?></td>
        </tr>
        <tr>
            <th>Link Referência</th>
            <td><a href="<?php echo htmlspecialchars($processo_encontrado['refer_link']); ?>" target="_blank"><?php echo $processo_encontrado['refer_link'];?></a></td>
        </tr>
        <tr>
            <th>Contatos Conhecidos</th>
            <td><?php echo htmlspecialchars($processo_encontrado['known_contacts']); ?></td>
        </tr>
        <tr>
            <th>Observação</th>
            <td><?php echo htmlspecialchars($processo_encontrado['observation']); ?></td>
        </tr>
        <tr>
            <th>Data e Hora</th>
            <td><?php echo htmlspecialchars($processo_encontrado['timestamp']); ?></td>
        </tr>
    </table>
</div>
<div class="d-flex align-items-start">
        
    <br />
<?php else: ?>
    <p>Erro: Processo não encontrado.</p>
<?php endif; ?>

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
                <?php
                    if(isset($processo['sinalizar_envio_data'])){
                        ?>
                            <tr>
                                <td>Prazo Resposta</td>
                                <td>
                        <?php
                        if(isset($processo['resposta_processo']) && $processo['resposta_processo'] === true ){
                           echo 'Respondido dentro do prazo.';
                        } else {
                            
                        // Data de sinalização de envio (formato: YYYY-MM-DD)
                        $sinalizarEnvioData = $processo['sinalizar_envio_data'];

                        // Converter a data para um objeto DateTime
                        $dataInicial = new DateTime($sinalizarEnvioData);

                        // Adicionar 15 dias à data inicial
                        $dataFinal = clone $dataInicial;
                        $dataFinal->modify('+15 days');

                        // Obter a data atual
                        $dataAtual = new DateTime();

                        // Calcular a diferença entre a data final e a data atual
                        $diferenca = $dataAtual->diff($dataFinal);

                        // Verificar se o prazo já expirou
                        if ($dataAtual > $dataFinal) {
                            echo "O prazo de 15 dias já expirou.";
                        } else {
                            // Exibir o contador regressivo
                            echo "Faltam " . $diferenca->days . " dias para esgotar o prazo de resposta deste processo.";
                        }
                    }

                        
                        
                        ?>

                                </td>
                            </tr>

                        <?php
                    }
                
                ?>
                <tr>
                    <td>Status da Resposta</td>
                    <td>
                        <?php 
                            echo isset($processo['resposta_processo']) && $processo['resposta_processo'] === true 
                                ? 'Respondido' 
                                : 'Aguardando Resposta';
                        ?>
                    </td>
                </tr>
            </tbody>
            </table>
                <?php
// Carregar o conteúdo do arquivo JSON de respostas
$respostasJson = 'resposta_processo.json';
$respostas = file_exists($respostasJson) ? json_decode(file_get_contents($respostasJson), true) : [];

// Inicializar variáveis de resposta
$respostaEncontrada = null;
foreach ($respostas as $resposta) {
    if ($resposta['id_processo'] === $processo['id']) {
        $respostaEncontrada = $resposta;
        break;
    }
}

// Mapear os textos de contestação
$contestacaoMensagens = [
    'concorda_remocao' => 'Confirmo que irei interromper o uso das imagens envolvidas nesse processo com o prazo de 7 dias.',
    'mais_informacoes' => 'Preciso de mais informações sobre o processo, solicito contato direto para melhor entendimento.',
    'nao_concordo' => 'Não concordo com os apontamentos realizados e manterei o uso das imagens mesmo assim.',
    'quero_vender' => 'Quero re-vender com autorização do uso de imagens da Brazmix.'
];
?>
</tbody>
</table>
        </div>
        <!-- Exibir a linha da tabela se a resposta existir -->
        <?php if ($respostaEncontrada) : ?>
    <div class="container">
    <table class="table table-bordered text-center" style="width: 100%; margin-left:0%">
    <tbody>
    <tr>
        <td>Data Resposta</td>
        <td><?php echo $respostaEncontrada['data_resposta']; ?></td>
    </tr>
    <tr>
        <td>Contestação</td>
        <td style="max-width: 200px; word-wrap: break-word;">
            <?php
                $contestacao = $respostaEncontrada['contestacao'];
                echo isset($contestacaoMensagens[$contestacao]) 
                    ? $contestacaoMensagens[$contestacao] 
                    : 'Resposta não reconhecida';
            ?>
        </td>
    </tr>
    <tr>
        <td>Texto Resposta</td>
        <td style="max-width: 200px; word-wrap: break-word;"><?php echo htmlspecialchars($respostaEncontrada['texto_resposta']); ?></td>
    </tr>
    <tr>
        <td>Email Contato</td>
        <td><?php echo $respostaEncontrada['email_resposta'];?></td>
    </tr>
    <tr>
        <td>Telefone Contato</td>
        <td><?php echo $respostaEncontrada['telefone_resposta'];?></td>
    </tr>
    </tbody>
    <table>
</div>
    <?php endif; ?>
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Análise Jurídica e Administrativa
    </div>
    <div class="card-body">
        <form action="salvar_formulario.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="processo_id" value="<?php echo $processo['id']; ?>">
            <input type="hidden" name="etapa" value="analise_juridica">
            <div class="mb-3">
                <label for="descricao_analise" class="form-label">Descrição da Análise</label>
                <textarea 
                    class="form-control" 
                    id="descricao_analise" 
                    name="descricao" 
                    rows="4" 
                    placeholder="Descreva o andamento do processo nesta etapa..."
                    required><?php echo htmlspecialchars($analise_juridica); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="arquivo_analise" class="form-label">Anexar Arquivo</label>
                <input type="file" class="form-control" id="arquivo_analise" name="arquivo">
            </div>
            <div class="mb-3">
                <?php listarArquivos($processos, $processo['id'], 'analise_juridica'); ?>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <button onclick="enviarRequisicao('<?php echo $processo['id']; ?>')" type="button" class="btn btn-success">Finalizar Processo</button>
        </form>
    </div>
</div>


<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        Comunicação Extra Judicial
    </div>
    <div class="card-body">
        <form action="salvar_formulario.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="processo_id" value="<?php echo $processo['id']; ?>">
            <input type="hidden" name="etapa" value="comunicacao_extra">
            <div class="mb-3">
                <label for="descricao_comunicacao" class="form-label">Descrição da Comunicação</label>
                <textarea 
                    class="form-control" 
                    id="descricao_comunicacao" 
                    name="descricao" 
                    rows="4" 
                    placeholder="Descreva o andamento do processo nesta etapa..."
                    required><?php echo htmlspecialchars($comunicacao_extra); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="arquivo_comunicacao" class="form-label">Anexar Arquivo</label>
                <input type="file" class="form-control" id="arquivo_comunicacao" name="arquivo">
            </div>
            <div class="mb-3">
                <?php listarArquivos($processos, $processo['id'], 'comunicacao_extra'); ?>
            </div>
            <button type="submit" class="btn btn-warning">Salvar</button>
            <button onclick="enviarRequisicao('<?php echo $processo['id']; ?>')" type="button" class="btn btn-success">Finalizar Processo</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-danger text-white">
        Processo Jurídico
    </div>
    <div class="card-body">
        <form action="salvar_formulario.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="processo_id" value="<?php echo $processo['id']; ?>">
            <input type="hidden" name="etapa" value="processo_juridico">
            <div class="mb-3">
                <label for="descricao_processo" class="form-label">Descrição do Processo</label>
                <textarea 
                    class="form-control" 
                    id="descricao_processo" 
                    name="descricao" 
                    rows="4" 
                    placeholder="Descreva o andamento do processo nesta etapa..."
                    required><?php echo htmlspecialchars($processo_juridico); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="arquivo_processo" class="form-label">Anexar Arquivo</label>
                <input type="file" class="form-control" id="arquivo_processo" name="arquivo">
            </div>
            <div class="mb-3">
                <!-- Lista de Arquivos para Análise Jurídica -->
                <?php listarArquivos($processos, $processo['id'], 'processo_juridico'); ?>
            </div>
            <button type="submit" class="btn btn-danger">Salvar</button>
            <button onclick="enviarRequisicao('<?php echo $processo['id']; ?>')" type="button" class="btn btn-success">Finalizar Processo</button>
        </form>
    </div>
</div>
<script>
    function enviarRequisicao(idProcesso) {
    // Previne o comportamento padrão do clique no botão
    if (event) {
        event.preventDefault();
    }


    // Configura os dados do POST
    const dados = new URLSearchParams({
        processo_id: idProcesso,
        finalizar_arquivar: true
    });

    // Envia a requisição usando fetch
    fetch('salva_processo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: dados.toString(),
    })
    .then(response => response.text()) // Processa a resposta como texto
    .then(resultado => {
        // Exibe o resultado no console ou faça outra ação
        console.log(resultado);
        location.reload();
    })
    .catch(erro => {
        console.error('Erro ao enviar requisição:', erro);
        alert('Ocorreu um erro ao enviar a requisição.');
    });
}

</script>
<a target="_blank" href="resumo_processo.php?pa_id=<?php echo $processo['id'];?>" class="btn btn-success ver-resumo">Ver Resumo do Processo</a>
<?php } elseif($processo['etapa'] == '4') {?>
<!-- Página Final -->
 <h1>ETAPA 4 – FINALIZAÇÃO DO PROCESSO</h1>
<div class="processo-visualizacao">
        <div class="d-flex align-items-start">
        <h1>Processo Finalizado</h1>
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
                <?php
                    if(isset($processo['sinalizar_envio_data'])){
                        ?>
                            <tr>
                                <td>Prazo Resposta</td>
                                <td>
                        <?php
                        if(isset($processo['resposta_processo']) && $processo['resposta_processo'] === true ){
                           echo 'Respondido dentro do prazo.';
                        } else {
                            
                        // Data de sinalização de envio (formato: YYYY-MM-DD)
                        $sinalizarEnvioData = $processo['sinalizar_envio_data'];

                        // Converter a data para um objeto DateTime
                        $dataInicial = new DateTime($sinalizarEnvioData);

                        // Adicionar 15 dias à data inicial
                        $dataFinal = clone $dataInicial;
                        $dataFinal->modify('+15 days');

                        // Obter a data atual
                        $dataAtual = new DateTime();

                        // Calcular a diferença entre a data final e a data atual
                        $diferenca = $dataAtual->diff($dataFinal);

                        // Verificar se o prazo já expirou
                        if ($dataAtual > $dataFinal) {
                            echo "O prazo de 15 dias já expirou.";
                        } else {
                            // Exibir o contador regressivo
                            echo "Faltam " . $diferenca->days . " dias para esgotar o prazo de resposta deste processo.";
                        }
                    }

                        
                        
                        ?>

                                </td>
                            </tr>

                        <?php
                    }
                
                ?>
                <tr>
                    <td>Status da Resposta</td>
                    <td>
                        <?php 
                            echo isset($processo['resposta_processo']) && $processo['resposta_processo'] === true 
                                ? 'Respondido' 
                                : 'Aguardando Resposta';
                        ?>
                    </td>
                </tr>
            </tbody>
            </table>
            

                <?php
// Carregar o conteúdo do arquivo JSON de respostas
$respostasJson = 'resposta_processo.json';
$respostas = file_exists($respostasJson) ? json_decode(file_get_contents($respostasJson), true) : [];

// Inicializar variáveis de resposta
$respostaEncontrada = null;
foreach ($respostas as $resposta) {
    if ($resposta['id_processo'] === $processo['id']) {
        $respostaEncontrada = $resposta;
        break;
    }
}

// Mapear os textos de contestação
$contestacaoMensagens = [
    'concorda_remocao' => 'Confirmo que irei interromper o uso das imagens envolvidas nesse processo com o prazo de 7 dias.',
    'mais_informacoes' => 'Preciso de mais informações sobre o processo, solicito contato direto para melhor entendimento.',
    'nao_concordo' => 'Não concordo com os apontamentos realizados e manterei o uso das imagens mesmo assim.',
    'quero_vender' => 'Quero re-vender com autorização do uso de imagens da Brazmix.'
];
?>
</tbody>
</table>
        </div>
        <!-- Exibir a linha da tabela se a resposta existir -->
        <?php if ($respostaEncontrada) : ?>
    <div class="container">
    <table class="table table-bordered text-center" style="width: 100%; margin-left:0%">
    <tbody>
    <tr>
        <td>Data Resposta</td>
        <td><?php echo $respostaEncontrada['data_resposta']; ?></td>
    </tr>
    <tr>
        <td>Contestação</td>
        <td style="max-width: 200px; word-wrap: break-word;">
            <?php
                $contestacao = $respostaEncontrada['contestacao'];
                echo isset($contestacaoMensagens[$contestacao]) 
                    ? $contestacaoMensagens[$contestacao] 
                    : 'Resposta não reconhecida';
            ?>
        </td>
    </tr>
    <tr>
        <td>Texto Resposta</td>
        <td style="max-width: 200px; word-wrap: break-word;"><?php echo htmlspecialchars($respostaEncontrada['texto_resposta']); ?></td>
    </tr>
    <tr>
        <td>Email Contato</td>
        <td><?php echo $respostaEncontrada['email_resposta'];?></td>
    </tr>
    <tr>
        <td>Telefone Contato</td>
        <td><?php echo $respostaEncontrada['telefone_resposta'];?></td>
    </tr>
    </tbody>
    <table>
    <?php
         $itens = [
            "Análise Jurídica" => [
                "descricao" => $processo["descricao_analise_juridica"] ?? '',
                "arquivo" => $processo["arquivo_analise_juridica"] ?? ''
            ],
            "Comunicação Extra" => [
                "descricao" => $processo["descricao_comunicacao_extra"] ?? '',
                "arquivo" => $processo["arquivo_comunicacao_extra"] ?? ''
            ],
            "Processo Jurídico" => [
                "descricao" => $processo["descricao_processo_juridico"] ?? '',
                "arquivo" => $processo["arquivo_processo_juridico"] ?? ''
            ]
        ];
        
        // Filtrando os itens vazios
        $itens = array_filter($itens, fn($item) => !empty(trim($item["descricao"])));
        
        if (!empty($itens)) {
            echo "<table class='table table-bordered text-center' border='1'>";
            echo "<tr><th>Tipo</th><th>Descrição</th></tr>";
            
            foreach ($itens as $tipo => $dados) {
                echo "<tr><td>{$tipo}</td><td>" . nl2br(htmlspecialchars($dados["descricao"]));
        
                // Se o arquivo existir e não for vazio, exibir link de download
                if (!empty(trim($dados["arquivo"]))) {
                    echo "<br><a href='{$dados["arquivo"]}' download>Baixar Arquivo</a>";
                }
        
                echo "</td></tr>";
            }
        
            echo "</table>";
        } else {
            echo "Nenhuma informação disponível.";
        }
            ?>
</div>

    <?php endif; ?>

        <div class="d-flex align-items-start ">
        <?php if (!isset($processo['resposta_processo']) || !$processo['resposta_processo']) : ?>
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
                
                function obterOuGerarChave($processoId, $arquivo = 'keys.json') {
                    // Lê o arquivo keys.json
                    $conteudo = file_get_contents($arquivo);
                    $dados = json_decode($conteudo, true);
                
                    // Procura se o processo já tem uma chave associada
                    foreach ($dados as &$registro) {
                        if ($registro['id'] === $processoId) {
                            // Retorna a última chave associada a este ID, se existir
                            if (!empty($registro['keys'])) {
                                return end($registro['keys']); // Pega a última chave do array
                            }
                        }
                    }
                
                    // Se não encontrou o ID ou não há chave, cria uma nova
                    $novaChave = gerarChaveAleatoria();
                
                    // Adiciona a chave ao registro ou cria um novo registro para este ID
                    $chaveEncontrada = false;
                    foreach ($dados as &$registro) {
                        if ($registro['id'] === $processoId) {
                            $registro['keys'][] = $novaChave;
                            $chaveEncontrada = true;
                            break;
                        }
                    }
                    if (!$chaveEncontrada) {
                        $dados[] = [
                            'id' => $processoId,
                            'keys' => [$novaChave]
                        ];
                    }
                
                    // Salva as alterações no arquivo
                    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
                
                    return $novaChave;
                }
                
                // Exemplo de uso
                $processoId = $processo['id']; // ID do processo atual
                $chave = obterOuGerarChave($processoId);
                
                ?>
                <input readonly class="outra-input form-control" value="<?php echo $chave;?>" placeholder="Digite aqui..." />
            </div>
            <?php endif; ?>
        </div>
        <form method="POST" action="salva_processo.php">
    <div class="align-items-center">
    <?php if (!isset($processo['resposta_processo']) || !$processo['resposta_processo']) : ?>
        <!-- Botão -->
        <button type="button" class="btn btn-primary gera-processo" id_processo="<?php echo $processo['id']; ?>">
            <i class="fa-solid fa-file-export"></i> Ver PDF com Comunicado
        </button>
    <?php endif; ?>


        <!-- Checkbox: Sinalizar Notificação -->
        <div class="form-check ms-3">

        <?php
        if(isset($processo['sinalizar_notificacao']) && $processo['sinalizar_notificacao']){
        // Mapear mensagens para cada situação
        $titulosSituacao = [
            'concorda_remocao' => 'Sugerido: Finalizar o Processo',
            'mais_informacoes' => 'Sugerido: Entrar em Contato Pessoal',
            'nao_concordo'     => 'Recomendado: Prosseguir para a Próxima Etapa'
        ];

        // Verifica se a contestação existe e gera o título correspondente
        if (isset($respostaEncontrada['contestacao'])) {
            $contestacao = $respostaEncontrada['contestacao'];
            $tituloSituacao = isset($titulosSituacao[$contestacao]) 
                ? $titulosSituacao[$contestacao] 
                : 'Situação Desconhecida';
        } else {
            $tituloSituacao = '';
        }
        ?>

        <!-- Exibir o título -->
        <h3 class="titulo-situacao">
            <?php echo $tituloSituacao; ?>
        </h3>
        <?php } ?>


        </div>
    </div>

   
</form>


        </div>
        <a target="_blank" href="resumo_processo.php?pa_id=<?php echo $processo['id'];?>" class="btn btn-success ver-resumo">Ver Resumo do Processo</a>

<!-- Página Final -->
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
