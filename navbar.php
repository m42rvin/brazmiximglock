<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="#">
    <img src="https://www.brazmix.com/www/imagens/site/logo.png?1" alt="Logo">
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="home.php">Home <span class="sr-only">(current)</span></a>
      </li>
      
      <!-- Primeiro Dropdown -->
      <?php if ($_SESSION["role"] == "admin") : ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="config" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Configurações
        </a>
        <div class="dropdown-menu" aria-labelledby="config">
          <a class="dropdown-item" href="/create_user.php">Usuários</a>
          <a class="dropdown-item" href="/categories.php">Categorias</a>
          <a class="dropdown-item" href="/licenses.php">Licenças</a>
        </div>
      </li>
      <?php endif; ?>
      
      <!-- Segundo Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="paginas" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Páginas
        </a>
        <div class="dropdown-menu" aria-labelledby="paginas">
          <a class="dropdown-item" href="/processos_auditoria.php">Processos Auditoria</a>
          <a class="dropdown-item" href="/panel.php">Painel</a>
          <a class="dropdown-item" href="/upload.php">Upload</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/logout.php">Sair</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
