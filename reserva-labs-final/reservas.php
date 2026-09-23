<?php
require_once "conexao.php";
$pagina_ativa = 'reservas';

$sucesso = '';
$erro = '';

if (isset($_GET['cancelar']) && $pdo) {
    $id_cancelar = intval($_GET['cancelar']);
    try {
        $stmtCancel = $pdo->prepare("UPDATE reserva SET fl_ativo = FALSE WHERE cd_reserva = ? AND fl_ativo = TRUE");
        $stmtCancel->execute([$id_cancelar]);
        $sucesso = $stmtCancel->rowCount() > 0
            ? "Reserva cancelada com sucesso!"
            : "Essa reserva já está cancelada.";
    } catch (Exception $e) {
        $erro = "Erro ao cancelar reserva: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_reserva']) && $pdo) {
    $id_laboratorio = intval($_POST['id_laboratorio'] ?? 0);
    $id_professor   = intval($_POST['id_professor'] ?? 0);
    $id_turma       = intval($_POST['id_turma'] ?? 0);
    $dt_reserva     = trim($_POST['dt_reserva'] ?? '');
    
    $slot_horario   = trim($_POST['slot_horario'] ?? '');
    $hr_inicio      = '';
    $hr_termino     = '';

    if (!empty($slot_horario) && str_contains($slot_horario, '|')) {
        $partes = explode('|', $slot_horario);
        $hr_inicio  = trim($partes[0]);
        $hr_termino = trim($partes[1]);
    }

    if (empty($id_laboratorio) || empty($id_professor) || empty($id_turma) || empty($dt_reserva) || empty($hr_inicio) || empty($hr_termino)) {
        $erro = "Por favor, preencha todos os campos da reserva.";
    } 
    elseif ($dt_reserva < date('Y-m-d')) {
        $erro = "A data da reserva não pode ser anterior a hoje.";
    } 
    elseif ($hr_inicio < '18:20' || $hr_termino > '23:50') {
        $erro = "Horário inválido. As 5 aulas acontecem exclusivamente entre 18:20 e 23:50.";
    } 
    elseif ($hr_inicio >= $hr_termino) {
        $erro = "O horário de término deve ser posterior ao horário de início.";
    } 
    else {
        $sqlConflito = "
            SELECT r.*, p.nm_professor, t.nm_turma, l.nm_laboratorio 
            FROM reserva r
            JOIN professor p ON r.id_professor = p.cd_professor
            JOIN turma t ON r.id_turma = t.cd_turma
            JOIN laboratorio l ON r.id_laboratorio = l.cd_laboratorio
            WHERE r.id_laboratorio = ? 
              AND r.dt_reserva = ?
              AND r.fl_ativo = TRUE
              AND (? < r.hr_termino AND ? > r.hr_inicio)
            LIMIT 1
        ";
        $stmtConf = $pdo->prepare($sqlConflito);
        $stmtConf->execute([$id_laboratorio, $dt_reserva, $hr_inicio, $hr_termino]);
        $conflito = $stmtConf->fetch();

        if ($conflito) {
            $erro = "CONFLITO DE HORÁRIO: O " . htmlspecialchars($conflito['nm_laboratorio']) 
                  . " já está reservado das " . substr($conflito['hr_inicio'], 0, 5) 
                  . " às " . substr($conflito['hr_termino'], 0, 5) 
                  . " pelo professor " . htmlspecialchars($conflito['nm_professor']) 
                  . " (Turma: " . htmlspecialchars($conflito['nm_turma']) . "). "
                  . "Você pode reservá-lo a partir das " . substr($conflito['hr_termino'], 0, 5) . ".";
        } else {
            try {
                $sqlInsert = "INSERT INTO reserva (id_laboratorio, id_professor, id_turma, dt_reserva, hr_inicio, hr_termino) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtIns = $pdo->prepare($sqlInsert);
                $stmtIns->execute([$id_laboratorio, $id_professor, $id_turma, $dt_reserva, $hr_inicio, $hr_termino]);
                $sucesso = "Reserva agendada com sucesso!";
            } catch (Exception $e) {
                $erro = "Erro ao salvar reserva: " . $e->getMessage();
            }
        }
    }
}

$labs = $pdo ? $pdo->query("SELECT * FROM laboratorio ORDER BY cd_laboratorio ASC")->fetchAll() : [];
$professores = $pdo ? $pdo->query("SELECT * FROM professor ORDER BY nm_professor ASC")->fetchAll() : [];
$turmas = $pdo ? $pdo->query("SELECT * FROM turma ORDER BY nm_turma ASC")->fetchAll() : [];

$reservas = [];
if ($pdo) {
    $sqlLista = "
        SELECT r.*, l.nm_laboratorio, p.nm_professor, t.nm_turma
        FROM reserva r
        JOIN laboratorio l ON r.id_laboratorio = l.cd_laboratorio
        JOIN professor p ON r.id_professor = p.cd_professor
        JOIN turma t ON r.id_turma = t.cd_turma
        ORDER BY r.fl_ativo DESC, r.dt_reserva DESC, r.hr_inicio ASC
    ";
    $reservas = $pdo->query($sqlLista)->fetchAll();
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
                        <i class="bi bi-calendar-plus text-danger me-2"></i>Agendar Reserva
                    </h5>
                </div>
                <div class="p-3">
                    <form method="POST" action="reservas.php">
                        <div class="mb-3">
                            <label class="form-label">Laboratório *</label>
                            <select name="id_laboratorio" class="form-select" required>
                                <option value="">Selecione o laboratório...</option>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= $lab['cd_laboratorio'] ?>" <?= (isset($_GET['id_lab']) && $_GET['id_lab'] == $lab['cd_laboratorio']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lab['nm_laboratorio']) ?> (<?= htmlspecialchars($lab['ds_laboratorio']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Professor *</label>
                            <select name="id_professor" class="form-select" required>
                                <option value="">Selecione o professor...</option>
                                <?php foreach ($professores as $prof): ?>
                                    <option value="<?= $prof['cd_professor'] ?>">
                                        <?= htmlspecialchars($prof['nm_professor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Turma *</label>
                            <select name="id_turma" class="form-select" required>
                                <option value="">Selecione a turma...</option>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?= $turma['cd_turma'] ?>">
                                        <?= htmlspecialchars($turma['nm_turma']) ?> - <?= htmlspecialchars($turma['ds_curso_turma']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data da Reserva *</label>
                            <input type="date" name="dt_reserva" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horário da Aula *</label>
                            <select name="slot_horario" class="form-select" required>
                                <option value="">Selecione a aula...</option>
                                <optgroup label="Aulas antes do Intervalo">
                                    <option value="18:20|19:10">1ª Aula: 18:20 às 19:10</option>
                                    <option value="19:10|20:00">2ª Aula: 19:10 às 20:00</option>
                                    <option value="20:00|20:50">3ª Aula: 20:00 às 20:50</option>
                                    <option value="18:20|20:50">1º Bloco Completo: 18:20 às 20:50 (Aulas 1 a 3)</option>
                                </optgroup>
                                <optgroup label="Intervalo: 20:50 às 21:10" disabled></optgroup>
                                <optgroup label="Aulas após o Intervalo">
                                    <option value="21:10|23:00">4ª Aula: 21:10 às 23:00</option>
                                    <option value="23:00|23:50">5ª Aula: 23:00 às 23:50</option>
                                    <option value="21:10|23:50">2º Bloco Completo: 21:10 às 23:50 (Aulas 4 e 5)</option>
                                </optgroup>
                                <optgroup label="Período Completo">
                                    <option value="18:20|23:50">Todas as 5 Aulas: 18:20 às 23:50</option>
                                </optgroup>
                            </select>
                        </div>

                        <button type="submit" name="cadastrar_reserva" class="btn btn-red w-100">
                            Confirmar Reserva
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-light fw-bold">
                        <i class="bi bi-list-check text-danger me-2"></i>Lista de Reservas
                    </h5>
                    <span class="badge bg-danger rounded-pill"><?= count($reservas) ?> reservas</span>
                </div>
                <div class="p-0 table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Laboratório</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Professor</th>
                                <th>Turma</th>
                                <th>Status</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhuma reserva cadastrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservas as $r): ?>
                                    <tr class="<?= !$r['fl_ativo'] ? 'opacity-75' : '' ?>">
                                        <td class="fw-bold text-light">
                                            <i class="bi bi-display text-danger me-1"></i>
                                            <?= htmlspecialchars($r['nm_laboratorio']) ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($r['dt_reserva'])) ?></td>
                                        <td>
                                            <span class="badge bg-dark text-light border border-secondary">
                                                <?= substr($r['hr_inicio'], 0, 5) ?> às <?= substr($r['hr_termino'], 0, 5) ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold"><?= htmlspecialchars($r['nm_professor']) ?></td>
                                        <td><?= htmlspecialchars($r['nm_turma']) ?></td>
                                        <td>
                                            <?php if ($r['fl_ativo']): ?>
                                                <span class="badge bg-success">Ativa</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Cancelada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($r['fl_ativo']): ?>
                                                <a href="reservas.php?cancelar=<?= $r['cd_reserva'] ?>" 
                                                   class="btn btn-outline-danger btn-sm p-1 px-2"
                                                   onclick="return confirm('Deseja realmente cancelar esta reserva?');"
                                                   title="Cancelar Reserva">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
