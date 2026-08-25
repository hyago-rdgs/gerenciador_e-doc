<?php $modulo_atual = $this->uri->segment(1); ?>
<header class="bg-white border-bottom sticky-top">
  <nav class="navbar navbar-expand-lg" aria-label="Navegação principal">
    <section class="container-fluid px-3 px-lg-4">
      <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?= base_url(); ?>"
        aria-label="e-Doc — página inicial">
        <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded p-2"
          aria-hidden="true">
          <i class="fa-solid fa-file-shield"></i>
        </span>
        <span>e-Doc</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navegacao-principal"
        aria-controls="navegacao-principal" aria-expanded="false" aria-label="Abrir navegação"><span
          class="navbar-toggler-icon"></span></button>
      <section class="collapse navbar-collapse" id="navegacao-principal">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
          <li class="nav-item">
            <a class="nav-link <?= $modulo_atual == 'documento' ? 'active fw-semibold' : ''; ?>"
              <?= $modulo_atual == 'documento' ? 'aria-current="page"' : ''; ?>
              href="<?= base_url('documento'); ?>">
              Documentos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $modulo_atual == 'pesquisa' ? 'active fw-semibold' : ''; ?>"
              <?= $modulo_atual == 'pesquisa' ? 'aria-current="page"' : ''; ?>
              href="<?= base_url('pesquisa'); ?>">
              Pesquisa
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $modulo_atual == 'tipo_documento' ? 'active fw-semibold' : ''; ?>"
              <?= $modulo_atual == 'tipo_documento' ? 'aria-current="page"' : ''; ?>
              href="<?= base_url('tipo_documento'); ?>">
              Tipos de documento
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $modulo_atual == 'metadado' ? 'active fw-semibold' : ''; ?>"
              <?= $modulo_atual == 'metadado' ? 'aria-current="page"' : ''; ?>
              href="<?= base_url('metadado'); ?>">
              Metadados
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $modulo_atual == 'localizacao' ? 'active fw-semibold' : ''; ?>"
              <?= $modulo_atual == 'localizacao' ? 'aria-current="page"' : ''; ?>
              href="<?= base_url('localizacao'); ?>">
              Localizações
            </a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button"
              data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-regular fa-circle-user"
                aria-hidden="true"></i><span><?= html_escape($this->controle_acesso->get('nome')); ?></span></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item log-out" href="<?= base_url('autenticacao/logout'); ?>">Sair</a></li>
            </ul>
          </li>
        </ul>
      </section>
    </section>
  </nav>
</header>
