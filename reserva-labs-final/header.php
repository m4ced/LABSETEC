<?php
if (!isset($pagina_ativa)) {
    $pagina_ativa = '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabsETEC - Sistema de Reserva de Laboratórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom py-2 shadow-none">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="fw-bold fs-4">Labs<span class="brand-etec">ETEC</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($pagina_ativa == 'inicio') ? 'active' : '' ?>" href="index.php">
                        Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pagina_ativa == 'reservas') ? 'active' : '' ?>" href="reservas.php">
                        Reservas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pagina_ativa == 'professores') ? 'active' : '' ?>" href="professores.php">
                        Professores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pagina_ativa == 'laboratorios') ? 'active' : '' ?>" href="laboratorios.php">
                        Laboratórios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pagina_ativa == 'turmas') ? 'active' : '' ?>" href="turmas.php">
                        Turmas
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="badge bg-dark text-danger border border-danger-subtle py-2 px-3 rounded-pill fw-semibold">
                    <i class="bi bi-clock me-1"></i> Aulas: 18:20 às 23:50 (Intervalo: 20:50 - 21:10)
                </span>
            </div>
        </div>
    </div>
</nav>
