<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/../conexão/config.php';

date_default_timezone_set('America/Sao_Paulo');

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pagina/index_logado.php');
    exit;
}

$idCliente = (int) $_SESSION['cliente_id'];
$nome = trim($_POST['nome'] ?? '');
$data = trim($_POST['data_agendamento'] ?? '');
$horario = trim($_POST['horario'] ?? '');
$tipoAtendimento = trim($_POST['tipo_atendimento'] ?? '');
$telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
$servico = trim($_POST['nome_servico'] ?? '');
$idade = (int) ($_POST['idade'] ?? 0);
$genero = trim($_POST['genero'] ?? '');
$cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
$rua = trim($_POST['rua'] ?? '');
$bairro = trim($_POST['bairro'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');

if ($tipoAtendimento === 'Online') {
    $cep = '';
    $rua = '';
    $bairro = '';
    $cidade = '';
} elseif ($tipoAtendimento === 'Clinica') {
    $cep = APP_CLINIC_CEP;
    $rua = APP_CLINIC_STREET;
    $bairro = APP_CLINIC_NEIGHBORHOOD;
    $cidade = APP_CLINIC_CITY;
}

try {
    $erro = null;

    if ($nome === '' || $data === '' || $horario === '' || $tipoAtendimento === '' || $telefone === '' || $servico === '' || $idade <= 0 || $genero === '') {
        $erro = 'Preencha todos os campos obrigatorios.';
    }

    if (!$erro && !in_array($tipoAtendimento, ['Online', 'Clinica', 'Domiciliar'], true)) {
        $erro = 'Tipo de atendimento invalido.';
    }

    if (!$erro && strlen($telefone) < 10) {
        $erro = 'Informe um telefone valido com DDD.';
    }

    if (!$erro && $idade > 120) {
        $erro = 'Informe uma idade valida.';
    }

    if (!$erro && $data < date('Y-m-d')) {
        $erro = 'Voce nao pode agendar para uma data que ja passou.';
    }

    if (!$erro && in_array(date('w', strtotime($data)), ['0', '6'], true)) {
        $erro = 'A Dra. Daniele nao realiza atendimentos aos sabados e domingos.';
    }

    if (!$erro && $tipoAtendimento === 'Domiciliar' && strlen($cep) !== 8) {
        $erro = 'Informe um CEP valido para atendimento domiciliar.';
    }

    if (!$erro) {
        $novoTs = strtotime($data . ' ' . $horario);
        $margem = 40 * 60;

        $stmtHorarios = $pdo->prepare(
            "SELECT horario
             FROM agenda
             WHERE data_agendamento = :data
               AND status != 'cancelado'"
        );
        $stmtHorarios->execute([':data' => $data]);
        $agendados = $stmtHorarios->fetchAll();

        foreach ($agendados as $agendado) {
            $horarioExistente = strtotime($data . ' ' . $agendado['horario']);
            if (abs($novoTs - $horarioExistente) < $margem) {
                $erro = 'Este horario esta muito proximo de outro agendamento. E necessario um intervalo de 40 minutos.';
                break;
            }
        }
    }

    if ($erro) {
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="agendar.css">
        </head>
        <body>
            <div class="box box-erro">
                <div style="font-size: 60px; margin-bottom: 10px;">⚠️</div>
                <h2 class="titulo-erro">Horario indisponivel</h2>
                <p><?= htmlspecialchars($erro) ?></p>
                <a href="javascript:history.back()" class="btn btn-voltar">Voltar e corrigir</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    $stmtUsuario = $pdo->prepare('SELECT email FROM clientes WHERE id = :id LIMIT 1');
    $stmtUsuario->execute([':id' => $idCliente]);
    $emailCliente = $stmtUsuario->fetchColumn() ?: '';

    $stmtInsert = $pdo->prepare(
        "INSERT INTO agenda (
            nome,
            data_agendamento,
            horario,
            nome_servico,
            tipo_atendimento,
            telefone,
            idade,
            genero,
            cep,
            rua,
            bairro,
            cidade,
            fk_id_cliente,
            status
        ) VALUES (
            :nome,
            :data,
            :horario,
            :servico,
            :tipo,
            :telefone,
            :idade,
            :genero,
            :cep,
            :rua,
            :bairro,
            :cidade,
            :cliente,
            'pendente'
        )"
    );

    $stmtInsert->execute([
        ':nome' => $nome,
        ':data' => $data,
        ':horario' => $horario,
        ':servico' => $servico,
        ':tipo' => $tipoAtendimento,
        ':telefone' => $telefone,
        ':idade' => $idade,
        ':genero' => $genero,
        ':cep' => $cep !== '' ? $cep : null,
        ':rua' => $rua !== '' ? $rua : null,
        ':bairro' => $bairro !== '' ? $bairro : null,
        ':cidade' => $cidade !== '' ? $cidade : null,
        ':cliente' => $idCliente,
    ]);

    if ($emailCliente !== '' && APP_RESEND_API_KEY !== '' && function_exists('curl_init')) {
        $corpoEmail = [
            'from' => 'Daniele Franca Nutri <onboarding@resend.dev>',
            'to' => [$emailCliente],
            'subject' => 'Agendamento confirmado - ' . $nome,
            'html' => '<h3>Ola, ' . htmlspecialchars($nome) . '!</h3>'
                . '<p>Seu agendamento para <strong>' . date('d/m/Y', strtotime($data)) . '</strong> as <strong>' . substr($horario, 0, 5) . '</strong> foi realizado.</p>',
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . APP_RESEND_API_KEY,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corpoEmail));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    $localMensagem = $tipoAtendimento === 'Online'
        ? "\nAtendimento online"
        : "\nEndereco: {$rua}, {$bairro}, {$cidade}";

    $mensagemWhatsapp =
        "NOVO AGENDAMENTO\n\n" .
        "Paciente: {$nome}\n" .
        'Data: ' . date('d/m/Y', strtotime($data)) . "\n" .
        'Hora: ' . substr($horario, 0, 5) . "\n" .
        'Servico: ' . $servico . "\n" .
        'Atendimento: ' . $tipoAtendimento .
        $localMensagem;

    $urlWhatsapp = 'https://wa.me/' . APP_NUTRI_WHATSAPP . '?text=' . urlencode($mensagemWhatsapp);
    $urlWhatsappJs = json_encode($urlWhatsapp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Agendamento salvo</title>
        <link rel="stylesheet" href="agendar.css">
        <link rel="icon" href="../fotos/images-removebg-preview copy.png">
        <script>
            function concluir(url) {
                window.open(url, '_blank');
                setTimeout(() => {
                    window.location.href = '../pagina/index_logado.php';
                }, 2200);
            }
        </script>
    </head>
    <body>
        <div class="box">
            <div class="loader"></div>
            <h2>Agendamento salvo!</h2>
            <p>
                Tudo pronto, <strong><?= htmlspecialchars(explode(' ', $nome)[0]) ?></strong>.<br>
                Agora avise a Dra. Daniele clicando abaixo.
            </p>
            <a href="javascript:void(0)" onclick='concluir(<?= $urlWhatsappJs ?>)' class="btn">
                Enviar para WhatsApp
            </a>
            <span class="msg-footer">Voce voltara ao site automaticamente apos o clique.</span>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo "<link rel='stylesheet' href='agendar.css'><div class='box box-erro'><h2>Erro</h2><p>Nao foi possivel salvar o agendamento agora.</p></div>";
}
?>
