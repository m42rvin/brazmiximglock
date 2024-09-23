<?php
// Iniciar sessão
session_start();

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: index.php");
exit;
