<?php
require_once __DIR__ . '/../conexão/conexao.php';
session_start();

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];

$stmt = $pdo->prepare('SELECT * FROM avaliacoes WHERE cliente_id = :cliente_id ORDER BY id_avaliacao DESC');
$stmt->execute([':cliente_id' => $clienteId]);
$avaliacoes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Avaliações</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="minha_avalia.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<h1>Minhas avaliações</h1>

<div class="container-central">
    <?php if (count($avaliacoes) === 0): ?>
        <div class="card" style="text-align: center;">
            <p>Você ainda não fez nenhuma avaliação.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($avaliacoes as $avaliacao): ?>
        <div class="card">
            <p><strong><i class="fas fa-user"></i> Nome:</strong> <?= htmlspecialchars($avaliacao['nome']) ?></p>

            <p class="nota-estrelas">
                <strong><i class="fas fa-star"></i> Nota:</strong>
                <?php $nota = (int) $avaliacao['nota']; ?>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?= $i <= $nota ? "<i class='fas fa-star' style='color:#f0ad4e;'></i>" : "<i class='far fa-star'></i>" ?>
                <?php endfor; ?>
                <span class="nota-numero">(<?= $nota ?>/5)</span>
            </p>

            <div class="comentario-texto">
                <i class="fas fa-quote-left"></i>
                <?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?>
            </div>

            <div class="container-acoes">
                <a class="btn-pill verde-musgo" href="editar_avalia.php?id=<?= (int) $avaliacao['id_avaliacao'] ?>">
                    <i class="fas fa-edit"></i> Editar
                </a>

                <a
                    class="btn-pill vermelho-cancelar"
                    href="excluir_avalia.php?id=<?= (int) $avaliacao['id_avaliacao'] ?>"
                    onclick="return confirm('Deseja excluir esta avaliação?')"
                >
                    <i class="fas fa-trash-alt"></i> Excluir
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="container-botoes-final">
        <a href="../pagina/index.php" class="btn-pill outline-verde">
            <i class="fas fa-reply"></i> Voltar ao menu
        </a>
        <a href="../pagina/index_logado.php#avalia" class="btn-pill outline-verde">
            <i class="fas fa-plus-circle"></i> Nova avaliação
        </a>
    </div>
</div>

</body>
</html>
