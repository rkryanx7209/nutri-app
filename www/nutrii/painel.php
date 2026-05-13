<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

require_once __DIR__ . '/../conexão/conexao.php';

$hoje = date('Y-m-d');
$agora = date('H:i:s');
$vinteMinutosDepois = date('H:i:s', strtotime('+20 minutes'));

$stmtHoje = $pdo->prepare("SELECT COUNT(*) FROM agenda WHERE data_agendamento = :hoje AND status = 'pendente'");
$stmtHoje->execute([':hoje' => $hoje]);
$totalHoje = (int) $stmtHoje->fetchColumn();

$totalClientes = (int) $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();

$stmtAlerta = $pdo->prepare(
    "SELECT COUNT(*)
     FROM agenda
     WHERE data_agendamento = :hoje
       AND tipo_atendimento = 'Online'
       AND status = 'pendente'
       AND horario BETWEEN :agora AND :depois"
);
$stmtAlerta->execute([
    ':hoje' => $hoje,
    ':agora' => $agora,
    ':depois' => $vinteMinutosDepois,
]);
$temConsultaProxima = (int) $stmtAlerta->fetchColumn() > 0;

$nomeAdmin = $_SESSION['admin_nome'] ?? 'Dra. Daniele';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Dra. Daniele França</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="painel.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

    <div class="container-painel">
        <i class="fas fa-user-shield main-icon"></i>
        <h2>Bem-vinda, <?= htmlspecialchars($nomeAdmin) ?>!</h2>
        <p>Gestão de consultório e pacientes</p>

        <div class="resumo-estatisticas">
            <div class="card-mini">
                <span>Pacientes hoje</span>
                <strong><?= $totalHoje ?></strong>
            </div>
            <div class="card-mini">
                <span>Total de clientes</span>
                <strong><?= $totalClientes ?></strong>
            </div>
        </div>

        <nav class="menu-painel">
            <a href="agendamentos.php" class="btn-pill verde <?= $temConsultaProxima ? 'alerta-pulsante' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Agendamentos</span>
                <?php if ($temConsultaProxima): ?>
                    <span class="badge-alerta"><i class="fas fa-video"></i> Consulta próxima</span>
                <?php endif; ?>
            </a>

            <a href="avaliacoes.php" class="btn-pill verde">
                <i class="fas fa-star"></i>
                <span>Avaliações</span>
            </a>

            <a href="admin_clientes.php" class="btn-pill verde">
                <i class="fas fa-apple-whole"></i>
                <span>Gerenciar dietas</span>
            </a>

            <a href="../acesso/logout.php?destino=site" class="btn-pill vermelho">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair do sistema</span>
            </a>
        </nav>
    </div>

</body>
</html>
