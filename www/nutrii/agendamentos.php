<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

require_once __DIR__ . '/../conexão/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atender_id'])) {
    $pdo->prepare(
        "UPDATE agenda
         SET status = 'atendido'
         WHERE id_agenda = :id
           AND status = 'pendente'"
    )->execute([':id' => (int) $_POST['atender_id']]);

    header('Location: agendamentos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $pdo->prepare(
        "DELETE FROM agenda
         WHERE id_agenda = :id
           AND status IN ('atendido', 'cancelado')"
    )->execute([':id' => (int) $_POST['excluir_id']]);

    header('Location: agendamentos.php');
    exit;
}

$hoje = date('Y-m-d');

$pendentes = $pdo->query(
    "SELECT a.*, c.email
     FROM agenda a
     LEFT JOIN clientes c ON a.fk_id_cliente = c.id
     WHERE a.status = 'pendente'
     ORDER BY a.data_agendamento ASC, a.horario ASC"
)->fetchAll();

$historico = $pdo->query(
    "SELECT *
     FROM agenda
     WHERE status IN ('atendido', 'cancelado')
     ORDER BY data_agendamento DESC, horario DESC
     LIMIT 50"
)->fetchAll();

$contHojeStmt = $pdo->prepare("SELECT COUNT(*) FROM agenda WHERE data_agendamento = :hoje AND status = 'pendente'");
$contHojeStmt->execute([':hoje' => $hoje]);
$totalHoje = (int) $contHojeStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Agenda | Nutri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="agenda.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="container-painel">
    <div class="main-card-wrapper">
        <div class="header-central">
            <h2><i class="fas fa-calendar-check" style="color: #4CAF50;"></i> Central de consultas</h2>
            <div class="card-stats" style="display: inline-block; width: auto; background: rgba(76, 175, 80, 0.1); border: 1px solid #4CAF50;">
                <span>Pendentes hoje: </span>
                <strong style="color: #4CAF50;"><?= $totalHoje ?></strong>
            </div>
        </div>

        <div class="flex-agendamentos">
            <div class="secao-container">
                <h3>Próximos</h3>
                <div class="busca-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscaP" class="busca-input" placeholder="Filtrar paciente..." onkeyup="filtrar('buscaP', 'card-p', '.paciente-nome')">
                </div>

                <div class="lista-scroll">
                    <?php foreach ($pendentes as $agenda): ?>
                        <?php
                        $tipo = trim($agenda['tipo_atendimento']);
                        $isOnline = $tipo === 'Online';
                        $mostrarMapa = $tipo === 'Domiciliar' || $tipo === 'Clinica';
                        $localExibir = trim(implode(', ', array_filter([$agenda['rua'] ?? '', $agenda['bairro'] ?? '', $agenda['cidade'] ?? ''])));
                        $localExibir = $localExibir !== '' ? $localExibir : 'Endereço não informado';

                        $telefoneLimpo = preg_replace('/\D/', '', $agenda['telefone']);
                        $dataFormatada = date('d/m/Y', strtotime($agenda['data_agendamento']));
                        $horaFormatada = substr($agenda['horario'], 0, 5);
                        $primeiroNome = explode(' ', trim($agenda['nome']))[0];

                        if ($isOnline) {
                            $mensagem = "Olá, {$primeiroNome}. Tudo bem?\n\nEstou disponível para iniciarmos sua consulta online via videochamada aqui pelo WhatsApp. Podemos começar?";
                        } elseif ($tipo === 'Domiciliar') {
                            $mensagem = "Olá, {$primeiroNome}. Como vai?\n\nPassando para confirmar nosso atendimento domiciliar agendado para o dia {$dataFormatada} às {$horaFormatada}. Confirmada a sua disponibilidade?";
                        } else {
                            $mensagem = "Olá, {$primeiroNome}. Tudo bem?\n\nGostaria de confirmar sua consulta em nossa clínica agendada para o dia {$dataFormatada} às {$horaFormatada}. Podemos confirmar?";
                        }

                        $linkWhatsapp = 'https://wa.me/55' . $telefoneLimpo . '?text=' . urlencode($mensagem);
                        ?>
                        <div class="card-agendamento card-p">
                            <strong class="paciente-nome"><?= htmlspecialchars($agenda['nome']) ?></strong>

                            <div class="info-item"><i class="fas fa-calendar"></i> <?= $dataFormatada ?> | <i class="fas fa-clock"></i> <?= $horaFormatada ?></div>
                            <div class="info-item">
                                <i class="<?= $tipo === 'Online' ? 'fas fa-video' : ($tipo === 'Domiciliar' ? 'fas fa-car' : 'fas fa-building') ?>"></i>
                                <?= htmlspecialchars($tipo) ?>
                            </div>

                            <?php if ($mostrarMapa): ?>
                                <div class="endereco-box">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($localExibir) ?>
                                    <br>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($localExibir) ?>" target="_blank" rel="noopener noreferrer" class="btn-mapa">
                                        <i class="fas fa-directions"></i> Ver rota
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="acoes-card">
                                <a href="<?= htmlspecialchars($linkWhatsapp) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp <?= $isOnline ? 'btn-azul' : 'btn-verde' ?>">
                                    <i class="<?= $isOnline ? 'fas fa-video' : 'fab fa-whatsapp' ?>"></i>
                                    <?= $isOnline ? 'Iniciar videochamada' : 'Confirmar presença' ?>
                                </a>

                                <form method="POST">
                                    <input type="hidden" name="atender_id" value="<?= (int) $agenda['id_agenda'] ?>">
                                    <button type="submit" class="btn-acao-admin btn-atendido">
                                        <i class="fas fa-check-circle"></i> Marcar como atendido
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="secao-container">
                <h3>Histórico</h3>
                <div class="busca-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscaH" class="busca-input" placeholder="Filtrar histórico..." onkeyup="filtrar('buscaH', 'card-h', '.paciente-nome-hist')">
                </div>

                <div class="lista-scroll">
                    <?php foreach ($historico as $agenda): ?>
                        <div class="card-agendamento card-h">
                            <strong class="paciente-nome-hist"><?= htmlspecialchars($agenda['nome']) ?></strong>
                            <div class="info-item"><i class="fas fa-calendar-check"></i> <?= date('d/m/Y', strtotime($agenda['data_agendamento'])) ?></div>
                            <div class="info-item">
                                <i class="fas fa-info-circle"></i>
                                <span class="status-tag status-<?= strtolower($agenda['status']) ?>"><?= ucfirst($agenda['status']) ?></span>
                            </div>

                            <?php if (($agenda['status'] ?? '') === 'atendido'): ?>
                                <a href="https://wa.me/55<?= preg_replace('/\D/', '', $agenda['telefone']) ?>?text=Olá, passando para saber como você está se sentindo com o plano alimentar!" target="_blank" rel="noopener noreferrer" class="btn-acompanhamento">
                                    <i class="fas fa-comment-dots"></i> Pós-consulta
                                </a>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Excluir este registro?');">
                                <input type="hidden" name="excluir_id" value="<?= (int) $agenda['id_agenda'] ?>">
                                <button type="submit" class="btn-excluir-txt"><i class="fas fa-trash-alt"></i> Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-btn">
        <a href="painel.php" class="btn-pill">
            <i class="fas fa-arrow-left"></i> Voltar ao painel principal
        </a>
    </div>
</div>

<script>
function filtrar(inputId, cardClass, nameSelector) {
    const input = document.getElementById(inputId).value.toLowerCase();
    const cards = document.getElementsByClassName(cardClass);

    for (let i = 0; i < cards.length; i++) {
        const nome = cards[i].querySelector(nameSelector).innerText.toLowerCase();
        cards[i].style.display = nome.includes(input) ? '' : 'none';
    }
}
</script>
</body>
</html>
