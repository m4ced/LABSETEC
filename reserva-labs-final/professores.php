<?php
require_once "conexao.php";
$pagina_ativa = 'professores';

$sucesso = '';
$erro = '';

if (isset($_GET['excluir']) && $pdo) {
    $id_del = intval($_GET['excluir']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM professor WHERE cd_professor = ?");
        $stmtDel->execute([$id_del]);
        $sucesso = "Professor removido com sucesso!";
    } catch (PDOException $e) {
        $erro = "Não foi possível remover o professor. Ele pode estar vinculado a uma reserva.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_professor']) && $pdo) {
    $rm   = trim($_POST['rm_professor'] ?? '');
    $nome = trim($_POST['nm_professor'] ?? '');
    $email = trim($_POST['email_professor'] ?? '');

    if (empty($nome)) {
        $erro = "Informe o nome do professor.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } else {
        try {
            if (empty($rm)) {
                $lastRm = $pdo->query("SELECT MAX(cd_professor) FROM professor")->fetchColumn();
                $rm = ($lastRm && $lastRm >= 25079) ? ($lastRm + 1) : 25079;
            }
            $stmtIns = $pdo->prepare("INSERT INTO professor (rm_professor, nm_professor, email_professor) VALUES (?, ?, ?)");
            $stmtIns->execute([$rm, $nome, $email ?: null]);
            $sucesso = "Professor cadastrado com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar professor: " . $e->getMessage();
        }
    }
}

$professores = [];
if ($pdo) {
    $professores = $pdo->query("SELECT * FROM professor ORDER BY cd_professor ASC")->fetchAll();
}

require_once "header.php";
?>

<div class="container my-4">
    <?php if (!empty($sucesso)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($sucesso) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="mb-0 text-light fw-bold">
                        <i class="bi bi-person-plus text-danger me-2"></i>Novo Professor
                    </h5>
                </div>
                <div class="p-3">
                    <form method="POST" action="professores.php">
                        <div class="mb-3">
                            <label class="form-label">RM do Professor</label>
                            <input type="text" name="rm_professor" class="form-control" placeholder="Ex: 25084">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome do Professor *</label>
                            <input type="text" name="nm_professor" class="form-control" placeholder="Ex: Matheus Calixto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email_professor" class="form-control" placeholder="Ex: professor@etec.sp.gov.br">
                        </div>
                        <button type="submit" name="cadastrar_professor" class="btn btn-red w-100">
                            Salvar Professor
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-light fw-bold">
                        <i class="bi bi-person-badge text-danger me-2"></i>Professores Cadastrados
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($professores) ?> professores</span>
                </div>
                <div class="p-0 table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 140px;">RM</th>
                                <th>Nome do Professor</th>
                                <th>E-mail</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($professores)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Nenhum professor cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($professores as $p): 
                                    $rm = !empty($p['rm_professor']) ? $p['rm_professor'] : $p['cd_professor'];
                                ?>
                                    <tr>
                                        <td class="fw-bold text-danger"><?= htmlspecialchars($rm) ?></td>
                                        <td class="fw-semibold text-light"><?= htmlspecialchars($p['nm_professor']) ?></td>
                                        <td><?= !empty($p['email_professor']) ? htmlspecialchars($p['email_professor']) : '<span class="text-muted">Não informado</span>' ?></td>
                                        <td class="text-end">
                                            <a href="professores.php?excluir=<?= $p['cd_professor'] ?>" 
                                               class="btn btn-outline-danger btn-sm p-1 px-2"
                                               onclick="return confirm('Deseja realmente remover este professor?');"
                                               title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?>
