<?php
require_once __DIR__ . '/../conexão/conexao.php';
session_start();

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];

$stmt = $pdo->prepare(
    'SELECT *
     FROM agenda
     WHERE fk_id_cliente = :cliente
     ORDER BY data_agendamento DESC, horario DESC'
);
$stmt->execute([':cliente' => $clienteId]);
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="minha_agenda.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

    <h1>Meus agendamentos</h1>

    <?php if (empty($agendamentos)): ?>
        <div class="card-agenda" style="text-align: center;">
            <p>Você ainda não possui agendamentos cadastrados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($agendamentos as $row): ?>
            <div class="card-agenda">
                <p><strong><i class="fas fa-calendar-day"></i> Data:</strong> <?= date('d/m/Y', strtotime($row['data_agendamento'])) ?></p>
                <p><strong><i class="fas fa-clock"></i> Hora:</strong> <?= substr($row['horario'], 0, 5) ?></p>
                <p>
                    <strong><i class="fas fa-map-marker-alt"></i> Local:</strong>
                    <?= htmlspecialchars($row['tipo_atendimento']) ?>
                    <?php if ($row['tipo_atendimento'] === 'Domiciliar' && !empty($row['cep'])): ?>
                        <span class="cep-info">(CEP: <?= htmlspecialchars($row['cep']) ?>)</span>
                    <?php endif; ?>
                </p>
                <p><strong><i class="fas fa-concierge-bell"></i> Serviço:</strong> <?= htmlspecialchars($row['nome_servico']) ?></p>
                <p>
                    <strong><i class="fas fa-info-circle"></i> Status:</strong>
                    <span class="status-<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </p>

                <?php if (($row['status'] ?? '') === 'pendente'): ?>
                    <div class="container-acoes">
                        <a class="btn-pill verde-musgo" href="editar_agenda.php?id=<?= (int) $row['id_agenda'] ?>">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a
                            class="btn-pill vermelho-cancelar"
                            href="excluir_agenda.php?id=<?= (int) $row['id_agenda'] ?>"
                            onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')"
                        >
                            <i class="fas fa-trash-alt"></i> Cancelar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="container-botoes-final">
        <a href="../pagina/index.php" class="btn-pill outline-verde">
            <i class="fas fa-reply"></i> Voltar ao menu
        </a>
        <a href="../pagina/index_logado.php#agenda" class="btn-pill outline-verde">
            <i class="fas fa-calendar-plus"></i> Novo agendamento
        </a>
    </div>

</body>
</html>
