<?php
require_once "conexao.php";
$pagina_ativa = 'inicio';

$data_atual = date('Y-m-d');
$hora_atual = date('H:i:s');

$labs_ocupados = [];
$labs_livres   = [];

if ($pdo) {
    $stmtLabs = $pdo->query("SELECT * FROM laboratorio ORDER BY cd_laboratorio ASC");
    $todos_labs = $stmtLabs->fetchAll();

    foreach ($todos_labs as $lab) {
        $id_lab = $lab['cd_laboratorio'];

        $stmtStatus = $pdo->prepare("
            SELECT r.*, p.nm_professor, t.nm_turma 
            FROM reserva r
            JOIN professor p ON r.id_professor = p.cd_professor
            JOIN turma t ON r.id_turma = t.cd_turma
            WHERE r.id_laboratorio = ? 
              AND r.dt_reserva = ?
              AND r.fl_ativo = TRUE
              AND (? >= r.hr_inicio AND ? < r.hr_termino)
            LIMIT 1
        ");
        $stmtStatus->execute([$id_lab, $data_atual, $hora_atual, $hora_atual]);
        $ocupacao = $stmtStatus->fetch();

        if ($ocupacao) {
            $labs_ocupados[] = [
                'lab'      => $lab,
                'reserva'  => $ocupacao
            ];
        } else {
            $labs_livres[] = [
                'lab'      => $lab
            ];
        }
    }
}

require_once "header.php";
?>

<div class="container my-4">
    <?php if (isset($erro_conexao)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($erro_conexao) ?>
        </div>
    <?php endif; ?>

    <div class="schedule-info-box mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h5 class="mb-1 text-danger fw-bold">Horário de Aulas - Noturno</h5>
            <div class="text-secondary small">
                5 Aulas das <strong class="text-light">18:20 às 23:50</strong>. 
                Intervalo das <span class="badge bg-danger-subtle text-danger border border-danger-subtle">20:50 às 21:10</span>.
            </div>
        </div>
        <div class="text-md-end">
            <span class="text-secondary small">Data:</span> 
            <strong class="text-light"><?= date('d/m/Y') ?></strong> | 
            <span class="text-secondary small">Horário:</span> 
            <strong class="text-danger"><?= date('H:i') ?></strong>
        </div>
    </div>

    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-danger-subtle">
            <h4 class="fw-bold mb-0 text-danger">
                Laboratórios Ocupados
            </h4>
            <span class="badge-ocupado"><?= count($labs_ocupados) ?> ocupado(s)</span>
        </div>

        <?php if (empty($labs_ocupados)): ?>
            <div class="card-custom p-4 text-center text-muted">
                Nenhum laboratório está ocupado no momento.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($labs_ocupados as $item): 
                    $l = $item['lab'];
                    $r = $item['reserva'];
                ?>
                    <div class="col-md-6">
                        <div class="card-custom card-ocupado p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-light"><?= htmlspecialchars($l['nm_laboratorio']) ?></h5>
                                    <span class="text-muted small"><?= htmlspecialchars($l['ds_laboratorio']) ?></span>
                                </div>
                                <span class="badge-ocupado">
                                    Ocupado até <?= substr($r['hr_termino'], 0, 5) ?>
                                </span>
                            </div>
                            <hr class="my-2 border-danger-subtle">
                            <div class="small">
                                <div class="mb-1"><strong>Professor:</strong> <?= htmlspecialchars($r['nm_professor']) ?></div>
                                <div class="mb-1"><strong>Turma:</strong> <?= htmlspecialchars($r['nm_turma']) ?></div>
                                <div class="text-muted"><strong>Horário da Aula:</strong> <?= substr($r['hr_inicio'], 0, 5) ?> às <?= substr($r['hr_termino'], 0, 5) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-success-subtle">
            <h4 class="fw-bold mb-0 text-success">
                Laboratórios Livres
            </h4>
            <span class="badge-livre"><?= count($labs_livres) ?> livre(s)</span>
        </div>

        <?php if (empty($labs_livres)): ?>
            <div class="card-custom p-4 text-center text-muted">
                Todos os laboratórios estão ocupados no momento.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($labs_livres as $item): 
                    $l = $item['lab'];
                ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card-custom card-livre p-3 d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold mb-0 text-light"><?= htmlspecialchars($l['nm_laboratorio']) ?></h5>
                                    <span class="badge-livre">Livre</span>
                                </div>
                                <div class="text-muted small mb-3"><?= htmlspecialchars($l['ds_laboratorio']) ?></div>
                            </div>
                            <a href="reservas.php?id_lab=<?= $l['cd_laboratorio'] ?>" class="btn btn-outline-red btn-sm w-100">
                                Reservar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "footer.php"; ?>
