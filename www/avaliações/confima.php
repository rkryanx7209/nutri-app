<?php
session_start();

function contemPalavrao(string $texto, array $lista): bool
{
    $texto = mb_strtolower($texto, 'UTF-8');

    foreach ($lista as $palavrao) {
        if (preg_match('/\b' . preg_quote($palavrao, '/') . '\b/u', $texto)) {
            return true;
        }
    }

    return false;
}

if (!isset($_SESSION['cliente_id'])) {
    echo "
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #1a1a1a; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .aviso-container { background-color: #2a2a2a; padding: 35px 25px; border-radius: 25px; text-align: center; max-width: 380px; width: 100%; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); }
        .aviso-container i.main-icon { font-size: 55px; color: #8fa081; margin-bottom: 20px; display: block; }
        .aviso-container h2 { color: #ffffff; font-size: 20px; margin-bottom: 12px; }
        .aviso-container p { color: #aaaaaa; font-size: 14px; line-height: 1.5; margin-bottom: 25px; }
        .botoes-aviso { display: flex; flex-direction: column; gap: 10px; align-items: center; }
        .btn-pill { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 50px; font-weight: 600; font-size: 13px; text-decoration: none !important; transition: all 0.3s ease; width: 100%; }
        .verde { background-color: #8fa081; color: #ffffff !important; }
        .cinza { background-color: #4a4a4a; color: #ffffff !important; }
        .escuro { background-color: #333333; color: #ffffff !important; }
    </style>

    <div class='aviso-container'>
        <i class='fas fa-user-circle main-icon'></i>
        <h2>Aviso</h2>
        <p>Faça login ou cadastre-se para enviar sua avaliação.</p>
        <div class='botoes-aviso'>
            <a href='../acesso/login.php' class='btn-pill verde'><i class='fas fa-sign-in-alt'></i> Fazer login</a>
            <a href='../acesso/cadrastocliente.php' class='btn-pill cinza'><i class='fas fa-user-plus'></i> Cadastrar-se</a>
            <a href='javascript:history.back()' class='btn-pill escuro'><i class='fas fa-arrow-left'></i> Voltar</a>
        </div>
    </div>";
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$nota = trim($_POST['nota'] ?? '');

$palavroes = ['porra', 'caralho', 'puta', 'merda', 'bosta', 'foder', 'fodase', 'fdp', 'viado', 'desgraça'];
$temPalavrao = contemPalavrao($comentario, $palavroes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Avaliação</title>
    <link rel="stylesheet" href="ava.css">
</head>
<body>

<section class="secao">
    <div class="conteudo">
        <div class="confirmacao-box">
            <h2>Confirmar avaliação</h2>

            <?php if ($temPalavrao): ?>
                <div style="color: #f75656; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Atenção:</strong> Detectamos palavras impróprias no seu comentário.
                    Volte e corrija o texto para continuar.
                </div>

                <p><strong>Seu comentário atual:</strong> "<?= htmlspecialchars($comentario) ?>"</p>

                <div class="confirmacao-botoes">
                    <button onclick="window.history.back()" class="btn-voltar">Voltar e editar</button>
                </div>

            <?php else: ?>
                <p class="texto-centro">Deseja confirmar sua avaliação antes de enviar?</p>

                <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
                <p><strong>Nota:</strong> <?= htmlspecialchars($nota) ?></p>
                <p><strong>Comentário:</strong> <?= htmlspecialchars($comentario) ?></p>

                <div class="confirmacao-botoes">
                    <form action="avaliar.php" method="POST">
                        <input type="hidden" name="nome" value="<?= htmlspecialchars($nome) ?>">
                        <input type="hidden" name="nota" value="<?= htmlspecialchars($nota) ?>">
                        <input type="hidden" name="comentario" value="<?= htmlspecialchars($comentario) ?>">

                        <button type="submit" class="btn-confirmar">Confirmar e enviar</button>
                    </form>

                    <form action="../pagina/index_logado.php" method="get">
                        <button type="submit" class="btn-voltar">Cancelar</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
