<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="recuparar.css">
</head>
<body>

<div class="login-box">
    <h2>Recuperar senha</h2>

    <?php if (!empty($_SESSION['erro_recuperar'])): ?>
        <div class="alerta erro"><?= htmlspecialchars($_SESSION['erro_recuperar']); ?></div>
    <?php unset($_SESSION['erro_recuperar']); endif; ?>

    <?php if (!isset($_SESSION['email_validado'])): ?>
        <p>Informe seu e-mail cadastrado.</p>
        <form action="processo.php" method="POST">
            <input type="hidden" name="acao" value="verificar">
            <input type="email" name="email" placeholder="E-mail" required>

            <div class="botoes-login">
                <button type="submit" class="btn-pill verde-musgo">Verificar</button>
                <a href="login.php" class="btn-pill outline-verde">Voltar ao login</a>
            </div>
        </form>

    <?php else: ?>
        <p>Defina sua nova senha para:<br><strong><?= htmlspecialchars($_SESSION['email_validado']); ?></strong></p>
        <form action="processo.php" method="POST">
            <input type="hidden" name="acao" value="mudar_senha">
            <input type="password" name="nova_senha" placeholder="Nova senha" required minlength="4">
            <input type="password" name="confirmar_senha" placeholder="Confirmar senha" required minlength="4">

            <div class="botoes-login">
                <button type="submit" class="btn-pill verde-musgo">Atualizar senha</button>
                <a href="processo.php?limpar=1" class="btn-pill outline-verde">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
