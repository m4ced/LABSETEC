<?php
require_once "conexao.php";
$pagina_ativa = 'turmas';

$sucesso = '';
$erro = '';

if (isset($_GET['excluir']) && $pdo) {
    $id_del = intval($_GET['excluir']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM turma WHERE cd_turma = ?");
        $stmtDel->execute([$id_del]);
        $sucesso = "Turma excluída com sucesso!";
    } catch (PDOException $e) {
        $erro = "Não foi possível excluir a turma. Ela pode estar vinculada a uma reserva.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_turma']) && $pdo) {
    $nome  = trim($_POST['nm_turma'] ?? '');
    $curso = trim($_POST['ds_curso_turma'] ?? '');

    if (empty($nome)) {
        $erro = "Informe o nome ou sigla da turma.";
    } else {
        try {
            $stmtIns = $pdo->prepare("INSERT INTO turma (nm_turma, ds_curso_turma) VALUES (?, ?)");
            $stmtIns->execute([$nome, $curso]);
            $sucesso = "Turma cadastrada com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar turma: " . $e->getMessage();
        }
    }
}

$turmas = [];
if ($pdo) {
    $turmas = $pdo->query("SELECT * FROM turma ORDER BY cd_turma ASC")->fetchAll();
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
                        <i class="bi bi-plus-circle text-danger me-2"></i>Nova Turma
                    </h5>
                </div>
                <div class="p-3">
                    <form method="POST" action="turmas.php">
                        <div class="mb-3">
                            <label class="form-label">Identificação da Turma *</label>
                            <input type="text" name="nm_turma" class="form-control" placeholder="Ex: 3º TDS - Noite" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Curso</label>
                            <input type="text" name="ds_curso_turma" class="form-control" placeholder="Ex: Desenvolvimento de Sistemas">
                        </div>
                        <button type="submit" name="cadastrar_turma" class="btn btn-red w-100">
                            Salvar Turma
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-light fw-bold">
                        <i class="bi bi-mortarboard text-danger me-2"></i>Turmas Cadastradas
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($turmas) ?> turmas</span>
                </div>
                <div class="p-0 table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Nome da Turma</th>
                                <th>Curso</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($turmas)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Nenhuma turma cadastrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($turmas as $t): ?>
                                    <tr>
                                        <td class="fw-bold text-light">
                                            <i class="bi bi-people text-danger me-1"></i>
                                            <?= htmlspecialchars($t['nm_turma']) ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($t['ds_curso_turma'] ?: '-') ?></td>
                                        <td class="text-end">
                                            <a href="turmas.php?excluir=<?= $t['cd_turma'] ?>" 
                                               class="btn btn-outline-danger btn-sm p-1 px-2"
                                               onclick="return confirm('Deseja excluir esta turma?');"
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
