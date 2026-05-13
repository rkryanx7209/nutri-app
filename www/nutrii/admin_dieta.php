<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clienteId = (int) ($_REQUEST['cliente_id'] ?? 0);

if ($clienteId <= 0) {
    die('Erro: cliente inválido.');
}

$stmtCliente = $pdo->prepare('SELECT id, nome FROM clientes WHERE id = :id LIMIT 1');
$stmtCliente->execute([':id' => $clienteId]);
$cliente = $stmtCliente->fetch();

if (!$cliente) {
    die('Erro: cliente não encontrado.');
}

$msg = '';
$dieta = null;

if (isset($_POST['excluir'])) {
    $stmt = $pdo->prepare('DELETE FROM dietas WHERE cliente_id = :cliente_id');
    $stmt->execute([':cliente_id' => $clienteId]);
    $msg = 'Dieta excluída com sucesso.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $cafeManha = trim($_POST['cafe_manha'] ?? '');
    $almoco = trim($_POST['almoco'] ?? '');
    $cafeTarde = trim($_POST['cafe_tarde'] ?? '');
    $janta = trim($_POST['janta'] ?? '');

    if ($cafeManha !== '' && $almoco !== '' && $cafeTarde !== '' && $janta !== '') {
        $check = $pdo->prepare('SELECT id FROM dietas WHERE cliente_id = :cliente_id');
        $check->execute([':cliente_id' => $clienteId]);

        if ($check->fetch()) {
            $sql = 'UPDATE dietas SET cafe_manha = :cafe_manha, almoco = :almoco, cafe_tarde = :cafe_tarde, janta = :janta WHERE cliente_id = :cliente_id';
            $pdo->prepare($sql)->execute([
                ':cafe_manha' => $cafeManha,
                ':almoco' => $almoco,
                ':cafe_tarde' => $cafeTarde,
                ':janta' => $janta,
                ':cliente_id' => $clienteId,
            ]);
            $msg = 'Dieta atualizada com sucesso.';
        } else {
            $sql = 'INSERT INTO dietas (cliente_id, cafe_manha, almoco, cafe_tarde, janta) VALUES (:cliente_id, :cafe_manha, :almoco, :cafe_tarde, :janta)';
            $pdo->prepare($sql)->execute([
                ':cliente_id' => $clienteId,
                ':cafe_manha' => $cafeManha,
                ':almoco' => $almoco,
                ':cafe_tarde' => $cafeTarde,
                ':janta' => $janta,
            ]);
            $msg = 'Nova dieta cadastrada.';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM dietas WHERE cliente_id = :cliente_id');
$stmt->execute([':cliente_id' => $clienteId]);
$dieta = $stmt->fetch();

if (isset($_POST['novo'])) {
    $dieta = null;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Dieta | Dra. Daniele França</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin_dieta.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="container-form">
    <h2><i class="fas fa-utensils"></i> Dieta de <?= htmlspecialchars($cliente['nome']) ?></h2>

    <?php if ($msg !== ''): ?>
        <p class="success-msg"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form method="POST" action="admin_dieta.php?cliente_id=<?= $clienteId ?>">
        <div class="input-group">
            <label><i class="fas fa-coffee"></i> Café da manhã</label>
            <textarea name="cafe_manha" placeholder="Opções para o café..." required><?= htmlspecialchars($dieta['cafe_manha'] ?? '') ?></textarea>
        </div>

        <div class="input-group">
            <label><i class="fas fa-utensils"></i> Almoço</label>
            <textarea name="almoco" placeholder="Opções para o almoço..." required><?= htmlspecialchars($dieta['almoco'] ?? '') ?></textarea>
        </div>

        <div class="input-group">
            <label><i class="fas fa-apple-alt"></i> Café da tarde</label>
            <textarea name="cafe_tarde" placeholder="Opções para o lanche..." required><?= htmlspecialchars($dieta['cafe_tarde'] ?? '') ?></textarea>
        </div>

        <div class="input-group">
            <label><i class="fas fa-moon"></i> Janta</label>
            <textarea name="janta" placeholder="Opções para a janta..." required><?= htmlspecialchars($dieta['janta'] ?? '') ?></textarea>
        </div>

        <div class="buttons-group">
            <button type="submit" name="salvar" class="btn-pill">
                <i class="fas fa-save"></i> Salvar dieta
            </button>

            <button type="submit" name="novo" class="btn-pill">
                <i class="fas fa-plus"></i> Limpar formulário
            </button>

            <?php if ($dieta): ?>
                <button type="submit" name="excluir" class="btn-pill btn-danger" onclick="return confirm('Excluir esta dieta?')">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            <?php endif; ?>

            <a href="admin_clientes.php" class="btn-pill btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </form>
</div>

</body>
</html>
