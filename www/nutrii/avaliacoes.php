<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

require_once __DIR__ . '/../conexão/conexao.php';

$todas = $pdo->query('SELECT * FROM avaliacoes ORDER BY id_avaliacao DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações - Área Restrita</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="avalia.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

    <div class="container-painel" style="max-width: 900px;">
        <h1>Avaliações recebidas</h1>

        <div class="busca-container" style="margin-bottom: 25px; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #7b8d72;"></i>
            <input
                type="text"
                id="inputBusca"
                placeholder="Pesquisar por nome do paciente..."
                onkeyup="filtrarAvaliacoes()"
                style="width: 100%; padding: 12px 15px 12px 45px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.1); background: #2a2a2a; color: #fff; outline: none; font-family: 'Poppins', sans-serif;"
            >
        </div>

        <div class="colunas-avaliacoes">
            <div class="coluna">
                <h2 style="color: #7b8d72;"><i class="fas fa-smile"></i> Feedback positivo</h2>
                <div class="lista-avaliacoes">
                    <?php $temBoa = false; ?>
                    <?php foreach ($todas as $avaliacao): ?>
                        <?php if ((int) $avaliacao['nota'] >= 4): ?>
                            <?php $temBoa = true; ?>
                            <div class="card-comentario card-bom">
                                <div class="card-header">
                                    <strong class="nome-paciente"><?= htmlspecialchars($avaliacao['nome']); ?></strong>
                                    <span class="nota-tag"><?= (int) $avaliacao['nota']; ?>/5</span>
                                </div>
                                <p class="texto-comentario"><?= htmlspecialchars($avaliacao['comentario']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$temBoa): ?>
                        <p class="msg-vazia" style="opacity: 0.5; font-size: 13px;">Sem avaliações positivas.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="coluna">
                <h2 style="color: #ff6b6b;"><i class="fas fa-frown"></i> Feedback negativo</h2>
                <div class="lista-avaliacoes">
                    <?php $temRuim = false; ?>
                    <?php foreach ($todas as $avaliacao): ?>
                        <?php if ((int) $avaliacao['nota'] <= 3): ?>
                            <?php $temRuim = true; ?>
                            <div class="card-comentario card-ruim">
                                <div class="card-header">
                                    <strong class="nome-paciente"><?= htmlspecialchars($avaliacao['nome']); ?></strong>
                                    <span style="background: #ff6b6b; color: white; padding: 1px 6px; border-radius: 8px; font-size: 11px; font-weight: bold;"><?= (int) $avaliacao['nota']; ?>/5</span>
                                </div>
                                <p class="texto-comentario"><?= htmlspecialchars($avaliacao['comentario']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$temRuim): ?>
                        <p class="msg-vazia" style="opacity: 0.5; font-size: 13px;">Sem críticas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer-avaliacoes">
            <a href="painel.php" class="btn-pill" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Voltar ao painel
            </a>
        </div>
    </div>

    <script>
    function filtrarAvaliacoes() {
        const termoBusca = document.getElementById('inputBusca').value.toLowerCase();
        const cards = document.getElementsByClassName('card-comentario');

        for (let i = 0; i < cards.length; i++) {
            const nomePaciente = cards[i].querySelector('.nome-paciente').innerText.toLowerCase();
            cards[i].style.display = nomePaciente.includes(termoBusca) ? '' : 'none';
        }
    }
    </script>

</body>
</html>
