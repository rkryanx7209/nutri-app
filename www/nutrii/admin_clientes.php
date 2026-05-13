<?php
session_start();
require_once __DIR__ . '/../conexão/conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clientes = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolher Cliente - Área Administrativa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin_clientes.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="container-dieta">
    <h2>Escolha um cliente</h2>

    <div class="busca-container" style="margin-bottom: 20px; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #8fa081;"></i>
        <input type="text" id="inputBusca" placeholder="Pesquisar nome do paciente..." onkeyup="filtrarClientes()"
        style="width: 100%; padding: 12px 15px 12px 45px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.1); background: #2a2a2a; color: #fff; outline: none;">
    </div>

    <div class="lista-clientes" id="listaClientes">
        <?php if (empty($clientes)): ?>
            <p style="opacity: 0.6; font-size: 14px;">Nenhum cliente cadastrado.</p>
        <?php else: ?>
            <?php foreach ($clientes as $cliente): ?>
                <div class="card-cliente">
                    <span class="nome-cliente"><?= htmlspecialchars($cliente['nome']) ?></span>
                    <a href="admin_dieta.php?cliente_id=<?= (int) $cliente['id'] ?>" class="btn-pill-sm">
                        Criar/editar dieta
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="footer-lista">
        <button onclick="window.location.href='painel.php'" class="btn-pill-voltar">
            <i class="fas fa-arrow-left"></i> Voltar ao painel
        </button>
    </div>
</div>

<script>
function filtrarClientes() {
    const input = document.getElementById('inputBusca').value.toLowerCase();
    const cards = document.getElementsByClassName('card-cliente');

    for (let i = 0; i < cards.length; i++) {
        const nome = cards[i].querySelector('.nome-cliente').innerText.toLowerCase();
        cards[i].style.display = nome.includes(input) ? '' : 'none';
    }
}
</script>

</body>
</html>
