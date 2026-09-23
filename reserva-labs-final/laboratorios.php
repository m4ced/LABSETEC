<?php
require_once "conexao.php";
$pagina_ativa = 'laboratorios';

$sucesso = '';
$erro = '';

if (isset($_GET['excluir']) && $pdo) {
    $id_del = intval($_GET['excluir']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM laboratorio WHERE cd_laboratorio = ?");
        $stmtDel->execute([$id_del]);
        $sucesso = "Laboratório excluído com sucesso!";
    } catch (PDOException $e) {
        $erro = "Não foi possível excluir o laboratório. Ele pode estar vinculado a uma reserva.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_laboratorio']) && $pdo) {
    $nm_lab = trim($_POST['nm_laboratorio'] ?? '');
    $qtd_pcs = trim($_POST['qtd_computadores'] ?? '30');

    if (empty($nm_lab)) {
        $erro = "Informe o nome do laboratório.";
    } else {
        try {
            $ds_lab = $qtd_pcs . " computadores";
            $stmtIns = $pdo->prepare("INSERT INTO laboratorio (nm_laboratorio, ds_laboratorio) VALUES (?, ?)");
            $stmtIns->execute([$nm_lab, $ds_lab]);
            $sucesso = "Laboratório cadastrado com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar laboratório: " . $e->getMessage();
        }
    }
}

$laboratorios = [];
if ($pdo) {
    $laboratorios = $pdo->query("SELECT * FROM laboratorio ORDER BY cd_laboratorio ASC")->fetchAll();
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
                        <i class="bi bi-plus-circle text-danger me-2"></i>Novo Laboratório
                    </h5>
                </div>
                <div class="p-3">
                    <form method="POST" action="laboratorios.php">
                        <div class="mb-3">
                            <label class="form-label">Nome do Laboratório *</label>
                            <input type="text" name="nm_laboratorio" class="form-control" placeholder="Ex: Laboratório 5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantidade de Computadores *</label>
                            <input type="number" name="qtd_computadores" class="form-control" value="30" min="1" required>
                        </div>
                        <button type="submit" name="cadastrar_laboratorio" class="btn btn-red w-100">
                            Salvar Laboratório
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-light fw-bold">
                        <i class="bi bi-pc-display text-danger me-2"></i>Laboratórios
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($laboratorios) ?> laboratórios</span>
                </div>
                <div class="p-0 table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Nome do Laboratório</th>
                                <th>Capacidade</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laboratorios)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Nenhum laboratório cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($laboratorios as $l): ?>
                                    <tr>
                                        <td class="fw-bold text-light">
                                            <i class="bi bi-display text-danger me-1"></i>
                                            <?= htmlspecialchars($l['nm_laboratorio']) ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($l['ds_laboratorio']) ?></td>
                                        <td class="text-end">
                                            <a href="laboratorios.php?excluir=<?= $l['cd_laboratorio'] ?>" 
                                               class="btn btn-outline-danger btn-sm p-1 px-2"
                                               onclick="return confirm('Deseja realmente remover este laboratório?');"
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
