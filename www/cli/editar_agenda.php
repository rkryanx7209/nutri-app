<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/../conexão/config.php';

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$idCliente = (int) $_SESSION['cliente_id'];
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM agenda WHERE id_agenda = :id AND fk_id_cliente = :cliente');
$stmt->execute([
    ':id' => $id,
    ':cliente' => $idCliente,
]);
$agendamento = $stmt->fetch();
$erroAtualizacao = '';

if (!$agendamento) {
    header('Location: minha_agenda.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = trim($_POST['data_agendamento'] ?? '');
    $hora = trim($_POST['horario'] ?? '');
    $servico = trim($_POST['nome_servico'] ?? '');
    $tipo = trim($_POST['tipo_atendimento'] ?? '');
    $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');

    if ($tipo === 'Online') {
        $cep = '';
        $rua = '';
        $bairro = '';
        $cidade = '';
    } elseif ($tipo === 'Clinica') {
        $cep = APP_CLINIC_CEP;
        $rua = APP_CLINIC_STREET;
        $bairro = APP_CLINIC_NEIGHBORHOOD;
        $cidade = APP_CLINIC_CITY;
    }

    if ($data < date('Y-m-d')) {
        $erroAtualizacao = 'Escolha uma data atual ou futura.';
    } elseif (in_array(date('w', strtotime($data)), ['0', '6'], true)) {
        $erroAtualizacao = 'Nao ha atendimento aos sabados e domingos.';
    } elseif ($tipo === 'Domiciliar' && strlen($cep) !== 8) {
        $erroAtualizacao = 'Informe um CEP valido para atendimento domiciliar.';
    } else {
        $novoTs = strtotime($data . ' ' . $hora);
        $margem = 40 * 60;

        $stmtConflito = $pdo->prepare(
            "SELECT horario
             FROM agenda
             WHERE data_agendamento = :data
               AND id_agenda != :id
               AND status != 'cancelado'"
        );
        $stmtConflito->execute([
            ':data' => $data,
            ':id' => $id,
        ]);

        $conflito = false;
        foreach ($stmtConflito->fetchAll() as $item) {
            if (abs($novoTs - strtotime($data . ' ' . $item['horario'])) < $margem) {
                $conflito = true;
                $erroAtualizacao = 'Ja existe outro horario muito proximo desse agendamento.';
                break;
            }
        }

        if (!$conflito) {
            $stmtUpdate = $pdo->prepare(
                'UPDATE agenda
                 SET data_agendamento = :data,
                     horario = :hora,
                     nome_servico = :servico,
                     tipo_atendimento = :tipo,
                     cep = :cep,
                     rua = :rua,
                     bairro = :bairro,
                     cidade = :cidade
                 WHERE id_agenda = :id
                   AND fk_id_cliente = :cliente'
            );

            $stmtUpdate->execute([
                ':data' => $data,
                ':hora' => $hora,
                ':servico' => $servico,
                ':tipo' => $tipo,
                ':cep' => $cep !== '' ? $cep : null,
                ':rua' => $rua !== '' ? $rua : null,
                ':bairro' => $bairro !== '' ? $bairro : null,
                ':cidade' => $cidade !== '' ? $cidade : null,
                ':id' => $id,
                ':cliente' => $idCliente,
            ]);

            header('Location: minha_agenda.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Agendamento</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
    <link rel="stylesheet" href="editar_agenda.css">
</head>
<body>
    <section class="secao">
        <div class="confirmacao-box">
            <h2><i class="fas fa-edit"></i> Editar consulta</h2>

            <?php if ($erroAtualizacao !== ''): ?>
                <p style="margin-bottom: 16px; color: #ffd2d2; background: rgba(169, 68, 66, 0.16); border: 1px solid rgba(169, 68, 66, 0.3); padding: 12px; border-radius: 12px;">
                    <?= htmlspecialchars($erroAtualizacao) ?>
                </p>
            <?php endif; ?>

            <form method="POST">
                <div class="row-dupla">
                    <div class="campo-edicao">
                        <label><i class="fas fa-calendar-day"></i> Nova data</label>
                        <input type="date" name="data_agendamento" value="<?= htmlspecialchars($agendamento['data_agendamento']) ?>" required>
                    </div>
                    <div class="campo-edicao">
                        <label><i class="fas fa-clock"></i> Novo horario</label>
                        <input type="time" name="horario" value="<?= htmlspecialchars($agendamento['horario']) ?>" required>
                    </div>
                </div>

                <div class="campo-edicao">
                    <label><i class="fas fa-hand-holding-medical"></i> Servico</label>
                    <select name="nome_servico" class="select-editar" required>
                        <option value="Emagrecimento" <?= ($agendamento['nome_servico'] ?? '') === 'Emagrecimento' ? 'selected' : '' ?>>Emagrecimento</option>
                        <option value="Reeducação alimentar" <?= ($agendamento['nome_servico'] ?? '') === 'Reeducação alimentar' ? 'selected' : '' ?>>Reeducacao alimentar</option>
                        <option value="Tratamento nutricional" <?= ($agendamento['nome_servico'] ?? '') === 'Tratamento nutricional' ? 'selected' : '' ?>>Tratamento nutricional</option>
                        <option value="Qualidade de vida" <?= ($agendamento['nome_servico'] ?? '') === 'Qualidade de vida' ? 'selected' : '' ?>>Qualidade de vida</option>
                    </select>
                </div>

                <div class="campo-edicao">
                    <label><i class="fas fa-map-marker-alt"></i> Local do atendimento</label>
                    <select name="tipo_atendimento" id="tipo_atendimento" required class="select-editar">
                        <option value="Online" <?= ($agendamento['tipo_atendimento'] ?? '') === 'Online' ? 'selected' : '' ?>>Atendimento online</option>
                        <option value="Clinica" <?= ($agendamento['tipo_atendimento'] ?? '') === 'Clinica' ? 'selected' : '' ?>>Presencial (clinica)</option>
                        <option value="Domiciliar" <?= ($agendamento['tipo_atendimento'] ?? '') === 'Domiciliar' ? 'selected' : '' ?>>Domiciliar</option>
                    </select>
                </div>

                <div id="sessao-endereco" style="display: none;">
                    <div class="campo-edicao">
                        <label><i class="fas fa-search-location"></i> CEP</label>
                        <input type="text" name="cep" id="cep" value="<?= htmlspecialchars($agendamento['cep'] ?? '') ?>" maxlength="8">
                    </div>
                    <div class="campo-edicao">
                        <label><i class="fas fa-road"></i> Rua</label>
                        <input type="text" name="rua" id="rua" class="readonly-style" value="<?= htmlspecialchars($agendamento['rua'] ?? '') ?>" readonly>
                    </div>
                    <div class="row-dupla">
                        <div class="campo-edicao">
                            <label><i class="fas fa-map-signs"></i> Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="readonly-style" value="<?= htmlspecialchars($agendamento['bairro'] ?? '') ?>" readonly>
                        </div>
                        <div class="campo-edicao">
                            <label><i class="fas fa-city"></i> Cidade</label>
                            <input type="text" name="cidade" id="cidade" class="readonly-style" value="<?= htmlspecialchars($agendamento['cidade'] ?? '') ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="confirmacao-botoes">
                    <button type="submit" class="btn-pill btn-confirmar">
                        <i class="fas fa-save"></i> Salvar alteracoes
                    </button>
                    <a href="minha_agenda.php" class="btn-pill btn-voltar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </section>

    <script>
        const enderecoClinica = <?= json_encode([
            'cep' => APP_CLINIC_CEP,
            'rua' => APP_CLINIC_STREET,
            'bairro' => APP_CLINIC_NEIGHBORHOOD,
            'cidade' => APP_CLINIC_CITY,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        const tipoAtendimento = document.getElementById('tipo_atendimento');
        const sessaoEndereco = document.getElementById('sessao-endereco');
        const campoCep = document.getElementById('cep');
        const campoRua = document.getElementById('rua');
        const campoBairro = document.getElementById('bairro');
        const campoCidade = document.getElementById('cidade');

        function atualizarEndereco() {
            const tipo = tipoAtendimento.value;

            if (tipo === 'Online') {
                sessaoEndereco.style.display = 'none';
                campoCep.readOnly = false;
                campoCep.classList.remove('readonly-style');
                campoCep.value = '';
                campoRua.value = '';
                campoBairro.value = '';
                campoCidade.value = '';
                return;
            }

            sessaoEndereco.style.display = 'block';

            if (tipo === 'Clinica') {
                campoCep.value = enderecoClinica.cep;
                campoRua.value = enderecoClinica.rua;
                campoBairro.value = enderecoClinica.bairro;
                campoCidade.value = enderecoClinica.cidade;
                campoCep.readOnly = true;
                campoCep.classList.add('readonly-style');
                return;
            }

            campoCep.readOnly = false;
            campoCep.classList.remove('readonly-style');
        }

        tipoAtendimento.addEventListener('change', atualizarEndereco);

        campoCep.addEventListener('input', function () {
            const tipo = tipoAtendimento.value;
            const cep = this.value.replace(/\D/g, '');

            if (tipo !== 'Domiciliar' || cep.length !== 8) {
                return;
            }

            fetch(`https://brasilapi.com.br/api/cep/v2/${cep}`)
                .then((res) => {
                    if (!res.ok) {
                        throw new Error('CEP nao encontrado');
                    }
                    return res.json();
                })
                .then((data) => {
                    campoRua.value = data.street || '';
                    campoBairro.value = data.neighborhood || '';
                    campoCidade.value = data.city || '';
                })
                .catch(() => {
                    campoRua.value = '';
                    campoBairro.value = '';
                    campoCidade.value = '';
                });
        });

        atualizarEndereco();
    </script>
</body>
</html>
