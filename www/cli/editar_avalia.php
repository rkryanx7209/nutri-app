<?php
require_once __DIR__ . '/../conexão/conexao.php';
session_start();

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$clienteId = (int) $_SESSION['cliente_id'];

$stmt = $pdo->prepare('SELECT * FROM avaliacoes WHERE id_avaliacao = :id AND cliente_id = :cliente_id');
$stmt->execute([
    ':id' => $id,
    ':cliente_id' => $clienteId,
]);
$avaliacao = $stmt->fetch();

if (!$avaliacao) {
    header('Location: minha_avalia.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = trim($_POST['comentario'] ?? '');
    $nota = (int) ($_POST['nota'] ?? 0);

    if ($comentario !== '' && $nota >= 1 && $nota <= 5) {
        $stmtUpdate = $pdo->prepare(
            'UPDATE avaliacoes
             SET comentario = :comentario, nota = :nota
             WHERE id_avaliacao = :id AND cliente_id = :cliente_id'
        );
        $stmtUpdate->execute([
            ':comentario' => $comentario,
            ':nota' => $nota,
            ':id' => $id,
            ':cliente_id' => $clienteId,
        ]);

        header('Location: minha_avalia.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Avaliação</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="editar_avalia.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>
    <section class="secao">
        <div class="confirmacao-box">
            <h2><i class="fas fa-star-half-alt"></i> Editar avaliação</h2>
            <p class="texto-centro">Altere sua opinião sobre o atendimento abaixo:</p>

            <form method="post">
                <div class="campo-edicao">
                    <label><i class="fas fa-comment-dots"></i> Comentário:</label>
                    <textarea name="comentario" required><?= htmlspecialchars($avaliacao['comentario']) ?></textarea>
                </div>

                <div class="campo-edicao">
                    <label><i class="fas fa-award"></i> Sua nota:</label>
                    <select name="nota" required>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (int) $avaliacao['nota'] === $i ? 'selected' : '' ?>>
                                <?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="confirmacao-botoes">
                    <button type="submit" class="btn-pill btn-confirmar">
                        <i class="fas fa-check"></i> Salvar alterações
                    </button>
                    <a href="minha_avalia.php" class="btn-pill btn-voltar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </section>
</body>
</html>
