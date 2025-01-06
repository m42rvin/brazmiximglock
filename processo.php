<?php
session_start();

// Parâmetro da URL
$pa_id = isset($_GET['pa_id']) ? $_GET['pa_id'] : null;

// Variáveis iniciais
$keysFile = 'keys.json';
$validKeys = [];
$processoValido = false;
$chaveInput = isset($_POST['chave_acesso']) ? $_POST['chave_acesso'] : null;

// Carregar as chaves do arquivo keys.json
if (file_exists($keysFile)) {
    $keysData = json_decode(file_get_contents($keysFile), true);

    // Procurar o pa_id e suas chaves associadas
    foreach ($keysData as $entry) {
        if ($entry['id'] === $pa_id) {
            $validKeys = $entry['keys'];
            break;
        }
    }
}

// Lógica de validação da chave
if ($chaveInput && in_array($chaveInput, $validKeys)) {
    $_SESSION['acesso_autorizado_' . $pa_id] = true;
    $processoValido = true;
}

// Verificar se já existe uma sessão de acesso autorizada
if (isset($_SESSION['acesso_autorizado_' . $pa_id])) {
    $processoValido = true;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Processo</title>
    <?php include('header.php');?>
    <style>
        .processo {
            background-color: #fff;
            padding: 20px;
        }
        body{
            padding-bottom:100px;
        }
        .campos-contatos {
            margin-left:20px
        }
    </style>
</head>
<body>
<div class="container mt-5 processo">
    <h2 class="mb-4">Acesso ao Processo</h2>

    <?php if ($pa_id): ?>
        <?php if ($processoValido): ?>
            <div class="alert alert-success">
                Acesso autorizado! Informações do processo ID: <strong><?php echo htmlspecialchars($pa_id); ?></strong>
            </div>

            <?php
            // Carregar informações do processo de processos_auditoria.json
            $processosFile = 'processos_auditoria.json';
            $processo = null;

            if (file_exists($processosFile)) {
                $processosData = json_decode(file_get_contents($processosFile), true);
                foreach ($processosData as $item) {
                    if ($item['id'] === $pa_id) {
                        $processo = $item;
                        break;
                    }
                }
            }
            ?>

            <?php if ($processo): ?>
                <!-- Detalhes do Processo -->
                <h5><strong>Espaço para o Contestante</strong></h5>
                <?php
// Verifique se existe uma resposta para o processo
$respostaEnviada = isset($processo['resposta_processo']) && $processo['resposta_processo'] === true;

// Caso não exista resposta, exibe o formulário
if (!$respostaEnviada):
?>
<form method="POST" action="salva_resposta.php">
    <!-- Opções de Resposta -->
    <div class="form-check">
        <input class="form-check-input" type="radio" name="contestacao" id="concorda_remocao" value="concorda_remocao">
        <label class="form-check-label" for="concorda_remocao">
            Confirmo que irei interromper o uso das imagens envolvidas nesse processo com o prazo de 7 dias.
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="contestacao" id="mais_informacoes" value="mais_informacoes">
        <label class="form-check-label" for="mais_informacoes">
            Preciso de mais informações sobre o processo, solicito contato direto para melhor entendimento.
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="contestacao" id="nao_concordo" value="nao_concordo">
        <label class="form-check-label" for="nao_concordo">
            Não concordo com os apontamentos realizados e manterei o uso das imagens mesmo assim.
        </label>
    </div>

    <!-- Campo de Texto -->
    <div class="d-flex ">
    <div class="mt-4">
        <h5><strong>Responder Contestação</strong></h5>
        <textarea name="texto_resposta" rows="10" cols="50" class="form-control"></textarea><br />
    </div>
    <div class="mt-4 campos-contatos">
        <h5><strong>Email</strong></h5>
        <input class="form-control" name="email" type="email" placeholder="Digite seu E-mail"/>
        <br/>
        <h5><strong>Telefone</strong></h5>
        <input class="form-control" name="telefone" type="text" placeholder="Digite seu telefone"/>
    </div>
    </div>

    <!-- Campo Oculto com o ID do Processo -->
    <input type="hidden" name="id_processo" value="<?php echo $processo['id']; ?>">

    <!-- Botão de Envio -->
    <button type="submit" class="btn btn-success">Enviar Resposta</button>
</form>
<?php
// Caso contrário, exibe uma mensagem informando que a resposta já foi enviada
else:
?>
<p class="alert alert-info">Resposta já enviada para este processo.</p>
<?php endif; ?>


        <!-- Responder Contestação -->
                </div>

            <?php else: ?>
                <div class="alert alert-danger">Nenhuma informação encontrada para o processo.</div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Formulário de chave de acesso -->
            <form method="POST">
                <div class="mb-3">
                    <label for="chave_acesso" class="form-label">Digite a chave de acesso:</label>
                    <input type="text" class="form-control" id="chave_acesso" name="chave_acesso" maxlength="5" required>
                </div>
                <button type="submit" class="btn btn-primary">Acessar</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">
            Nenhum processo foi especificado. Use um parâmetro válido de URL, como <strong>?pa_id=6759d2d62729e</strong>.
        </div>
    <?php endif; ?>
</div>
<?php include('footer.php');?>
</body>
</html>
