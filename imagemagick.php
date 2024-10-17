<?php
// Define uma função para verificar a instalação do ImageMagick
function verificarImageMagick() {
    // Verifica se a extensão Imagick está carregada
    if (extension_loaded('imagick')) {
        // Cria um novo objeto Imagick para verificar o funcionamento
        $imagick = new Imagick();
        
        // Obtém a versão do ImageMagick instalada
        $versao = $imagick->getVersion();
        
        // Exibe a versão instalada
        echo "ImageMagick está instalado e funcionando.<br>";
        echo "Versão do ImageMagick: " . $versao['versionString'] . "<br>";
        
        // Verifica os formatos suportados
        echo "Formatos de imagem suportados: <br>";
        $formatos = $imagick->queryFormats();
        
        if (!empty($formatos)) {
            foreach ($formatos as $formato) {
                echo $formato . "<br>";
            }
        } else {
            echo "Nenhum formato de imagem suportado foi encontrado.<br>";
        }
    } else {
        // Caso a extensão não esteja carregada, exibe uma mensagem de erro
        echo "ImageMagick não está instalado ou a extensão 'imagick' não está habilitada no PHP.<br>";
    }
}

// Chama a função para realizar a verificação
verificarImageMagick();
?>
