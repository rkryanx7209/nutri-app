<?php
session_start();
require_once __DIR__ . '/../conexão/conexao.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

function formatarRefeicao($valor): string
{
    $texto = trim((string) ($valor ?? ''));

    if ($texto === '') {
        return 'Nenhuma orientacao cadastrada para esta refeicao.';
    }

    return nl2br(htmlspecialchars($texto));
}

$clienteId = (int) $_SESSION['cliente_id'];
$dieta = false;
$erroDieta = '';

try {
    $stmt = $pdo->prepare('SELECT * FROM dietas WHERE cliente_id = :cliente_id LIMIT 1');
    $stmt->execute([':cliente_id' => $clienteId]);
    $dieta = $stmt->fetch();
} catch (PDOException $e) {
    $erroDieta = 'Nao foi possivel carregar sua dieta agora.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Dieta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="minha_dieta.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="container-dieta">
    <h2><i class="fas fa-apple-alt"></i> Minha dieta</h2>

    <?php if ($erroDieta !== ''): ?>
        <p class="sem-dieta"><?= htmlspecialchars($erroDieta) ?></p>
    <?php elseif ($dieta): ?>
        <div class="lista-dieta">
            <div class="card-refeicao">
                <strong><i class="fas fa-mug-hot"></i> Cafe da manha</strong>
                <p><?= formatarRefeicao($dieta['cafe_manha'] ?? '') ?></p>
            </div>
            <div class="card-refeicao">
                <strong><i class="fas fa-utensils"></i> Almoco</strong>
                <p><?= formatarRefeicao($dieta['almoco'] ?? '') ?></p>
            </div>
            <div class="card-refeicao">
                <strong><i class="fas fa-cookie"></i> Cafe da tarde</strong>
                <p><?= formatarRefeicao($dieta['cafe_tarde'] ?? '') ?></p>
            </div>
            <div class="card-refeicao">
                <strong><i class="fas fa-moon"></i> Janta</strong>
                <p><?= formatarRefeicao($dieta['janta'] ?? '') ?></p>
            </div>
        </div>
    <?php else: ?>
        <p class="sem-dieta">Voce ainda nao tem uma dieta cadastrada.</p>
    <?php endif; ?>

    <div class="container-botoes">
        <a href="../pagina/index.php" class="btn-pill escuro">
            <i class="fas fa-arrow-left"></i> Voltar ao menu
        </a>
    </div>
</div>

</body>
</html>
